<?php
/* =====================================================
   ADMIN_API.PHP — backend AJAX khusus halaman admin
   login, logout, daftar stok, ubah stok, daftar pesanan, ubah status pesanan
   ===================================================== */
require __DIR__ . '/config.php';
header('Cache-Control: no-store');

$in = json_decode(file_get_contents('php://input'), true) ?: [];
$a  = $_GET['action'] ?? '';

const ORDER_STATUSES = ['Menunggu verifikasi pembayaran', 'Menunggu konfirmasi', 'Pembayaran terverifikasi', 'Diproses', 'Dikirim', 'Selesai', 'Dibatalkan'];

/* Semua error tidak terduga dikembalikan sebagai JSON (bukan halaman error PHP) */
set_exception_handler(function ($e) {
    error_log($e->getMessage());
    out(['ok' => false, 'msg' => 'Server tidak dapat membaca database. Pastikan MySQL (XAMPP) menyala dan database.sql sudah diimpor.']);
});

/* ---------- login: hanya akun Developer & Admin (dibatasi 5 percobaan salah / 60 detik) ---------- */
if ($a === 'login') {
    $now = time();
    if (($_SESSION['adm_lock'] ?? 0) > $now)
        out(['ok' => false, 'msg' => 'Terlalu banyak percobaan. Coba lagi dalam ' . ($_SESSION['adm_lock'] - $now) . ' detik.']);
    $u   = strtolower(trim((string)($in['username'] ?? '')));
    $p   = (string)($in['password'] ?? '');
    $acc = ADMIN_ACCOUNTS[$u] ?? null;
    /* password_verify tetap dijalankan walau username salah, supaya waktu respons sama */
    $ok  = password_verify($p, $acc['hash'] ?? '$2y$10$usesomesillystringforsaltuYtqhLMNq2KgS4cD1zJ9b0yQ7s8mW');
    if ($acc && $ok) {
        session_regenerate_id(true);
        $_SESSION['admin']      = true;
        $_SESSION['admin_user'] = $u;
        $_SESSION['admin_name'] = $acc['name'];
        $_SESSION['admin_role'] = $acc['role'];
        $_SESSION['csrf']       = bin2hex(random_bytes(16));
        unset($_SESSION['adm_fail'], $_SESSION['adm_lock']);
        out(['ok' => true]);
    }
    $_SESSION['adm_fail'] = ($_SESSION['adm_fail'] ?? 0) + 1;
    if ($_SESSION['adm_fail'] >= 5) { $_SESSION['adm_lock'] = $now + 60; $_SESSION['adm_fail'] = 0; }
    usleep(400000);
    out(['ok' => false, 'msg' => 'Username atau password salah.']);
}

/* ---------- semua aksi lain wajib Developer/Admin + token CSRF ---------- */
if (!is_admin()) out(['ok' => false, 'auth' => true, 'msg' => 'Sesi admin berakhir. Silakan login lagi.']);
$tok = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
if (empty($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $tok))
    out(['ok' => false, 'auth' => true, 'msg' => 'Token tidak valid. Muat ulang halaman.']);

if ($a === 'logout') {
    unset($_SESSION['admin'], $_SESSION['admin_user'], $_SESSION['admin_name'], $_SESSION['admin_role'], $_SESSION['csrf']);
    out(['ok' => true]);
}

/* ---------- RINGKASAN PENJUALAN ----------
   Pesanan berstatus "Dibatalkan" tidak dihitung. "Terjual" = angka awal manual (sold_awal) + isi pesanan. */
if ($a === 'summary') {
    $pdo = db();
    $today = date('Y-m-d');
    $days = [];                                   // 14 hari terakhir, termasuk hari ini
    for ($i = 13; $i >= 0; $i--) $days[date('Y-m-d', strtotime("-$i day"))] = ['date' => date('Y-m-d', strtotime("-$i day")), 'orders' => 0, 'revenue' => 0, 'units' => 0];

    $ord = $pdo->query("SELECT id, DATE(ordered_at) AS d, total, status, payment_method FROM orders")->fetchAll();
    $statusCount = []; $payCount = []; $tot = ['orders' => 0, 'revenue' => 0, 'pending' => 0, 'cancelled' => 0];
    $win = ['today' => ['orders' => 0, 'revenue' => 0], 'week' => ['orders' => 0, 'revenue' => 0], 'month' => ['orders' => 0, 'revenue' => 0]];
    $weekFrom = date('Y-m-d', strtotime('-6 day')); $monthFrom = date('Y-m-01');
    $dayOf = [];
    foreach ($ord as $o) {
        $statusCount[$o['status']] = ($statusCount[$o['status']] ?? 0) + 1;
        if (strpos($o['status'], 'Menunggu') === 0) $tot['pending']++;
        if ($o['status'] === 'Dibatalkan') { $tot['cancelled']++; continue; }
        $t = (int)$o['total']; $d = $o['d']; $dayOf[(int)$o['id']] = $d;
        $tot['orders']++; $tot['revenue'] += $t;
        $payCount[$o['payment_method']] = ($payCount[$o['payment_method']] ?? ['orders' => 0, 'revenue' => 0]);
        $payCount[$o['payment_method']]['orders']++; $payCount[$o['payment_method']]['revenue'] += $t;
        if (isset($days[$d])) { $days[$d]['orders']++; $days[$d]['revenue'] += $t; }
        if ($d === $today)     { $win['today']['orders']++; $win['today']['revenue'] += $t; }
        if ($d >= $weekFrom)   { $win['week']['orders']++;  $win['week']['revenue']  += $t; }
        if ($d >= $monthFrom)  { $win['month']['orders']++; $win['month']['revenue'] += $t; }
    }

    $prod = [];
    foreach ($products as $p) $prod[$p['id']] = ['id' => $p['id'], 'name' => $p['name'], 'img' => $p['img'], 'stock' => $p['stock'], 'initial' => 0, 'units' => 0, 'revenue' => 0];
    try { foreach ($pdo->query('SELECT id, sold_awal FROM products') as $r) if (isset($prod[$r['id']])) $prod[$r['id']]['initial'] = (int)$r['sold_awal']; }
    catch (Throwable $e) { /* kolom sold_awal belum ada */ }
    $unitsTotal = 0;
    foreach ($pdo->query("SELECT oi.order_id, oi.product_id, oi.quantity AS q, oi.subtotal AS s FROM order_items oi JOIN orders o ON o.id = oi.order_id WHERE o.status <> 'Dibatalkan'") as $r) {
        $unitsTotal += (int)$r['q'];
        if (isset($prod[$r['product_id']])) { $prod[$r['product_id']]['units'] += (int)$r['q']; $prod[$r['product_id']]['revenue'] += (int)$r['s']; }
        $d = $dayOf[(int)$r['order_id']] ?? null;
        if ($d !== null && isset($days[$d])) $days[$d]['units'] += (int)$r['q'];
    }
    $list = array_values($prod);
    foreach ($list as &$p) $p['sold'] = $p['initial'] + $p['units'];
    unset($p);
    usort($list, fn($x, $y) => [$y['sold'], $y['id']] <=> [$x['sold'], $x['id']]);

    out(['ok' => true, 'summary' => [
        'orders' => $tot['orders'], 'revenue' => $tot['revenue'], 'pending' => $tot['pending'], 'cancelled' => $tot['cancelled'],
        'units' => $unitsTotal, 'units_all' => $unitsTotal + array_sum(array_column($list, 'initial')),
        'avg' => $tot['orders'] ? (int)round($tot['revenue'] / $tot['orders']) : 0,
        'windows' => $win, 'days' => array_values($days), 'products' => $list,
        'status' => $statusCount, 'payments' => $payCount,
        'out_of_stock' => count(array_filter($list, fn($p) => $p['stock'] !== null && $p['stock'] <= 0)),
    ]]);
}

/* ---------- STOK ---------- */
if ($a === 'stock_list') {
    $rows = [];
    foreach ($products as $p)
        $rows[] = ['id' => $p['id'], 'name' => $p['name'], 'price' => $p['price'], 'img' => $p['img'], 'stock' => $p['stock'], 'sold' => $p['sold']];
    out(['ok' => true, 'products' => $rows]);
}

if ($a === 'stock_set') {
    $id = (int)($in['id'] ?? 0); $stock = $in['stock'] ?? null;
    $p = find_product($id);
    if (!$p) out(['ok' => false, 'msg' => 'Menu tidak ditemukan.']);
    if (!is_numeric($stock) || (int)$stock != $stock || $stock < 0 || $stock > 9999)
        out(['ok' => false, 'msg' => 'Isi stok dengan angka 0 sampai 9999.']);
    $stock = (int)$stock;
    try {
        db()->prepare('INSERT INTO products (id, name, stock) VALUES (?, ?, ?)
                       ON DUPLICATE KEY UPDATE stock = VALUES(stock)')
            ->execute([$id, $p['name'], $stock]);
    } catch (PDOException $e) {
        error_log($e->getMessage());
        out(['ok' => false, 'msg' => 'Stok gagal disimpan. Pastikan tabel products sudah dibuat (database.sql).']);
    }
    out(['ok' => true, 'id' => $id, 'stock' => $stock]);
}

/* ---------- PESANAN ---------- */
if ($a === 'orders') {
    $pdo = db();
    try {
        $rows = $pdo->query('SELECT o.id, o.order_code, o.ordered_at, o.recipient_name, o.phone, o.address, o.payment_method,
                                    o.payment_proof, o.notes, o.total, o.status, u.email
                             FROM orders o LEFT JOIN users u ON u.id = o.user_id
                             ORDER BY o.ordered_at DESC, o.id DESC LIMIT 200')->fetchAll();
    } catch (PDOException $e) {            // kolom payment_proof belum ada (update_pembayaran.sql belum dijalankan)
        try {
            $rows = $pdo->query('SELECT o.id, o.order_code, o.ordered_at, o.recipient_name, o.phone, o.address, o.payment_method,
                                        NULL AS payment_proof, o.notes, o.total, o.status, u.email
                                 FROM orders o LEFT JOIN users u ON u.id = o.user_id
                                 ORDER BY o.ordered_at DESC, o.id DESC LIMIT 200')->fetchAll();
        } catch (PDOException $e2) {
            error_log($e2->getMessage());
            out(['ok' => false, 'msg' => 'Tabel pesanan belum bisa dibaca. Periksa koneksi database.']);
        }
    }
    $items = [];
    if ($rows) {
        $ids = implode(',', array_map('intval', array_column($rows, 'id')));
        foreach ($pdo->query("SELECT order_id, product_name, quantity, unit_price, subtotal FROM order_items WHERE order_id IN ($ids) ORDER BY id") as $r)
            $items[(int)$r['order_id']][] = ['name' => $r['product_name'], 'qty' => (int)$r['quantity'], 'price' => (int)$r['unit_price'], 'sub' => (int)$r['subtotal']];
    }
    $list = [];
    foreach ($rows as $r) {
        $proof = null; $proofMissing = false;
        if (!empty($r['payment_proof'])) {
            if (preg_match('~^uploads/bukti/[A-Za-z0-9._-]+$~', $r['payment_proof']) && is_file(__DIR__ . '/' . $r['payment_proof'])) $proof = $r['payment_proof'];
            else $proofMissing = true;
        }
        $list[] = [
            'id' => (int)$r['id'], 'kode' => $r['order_code'], 'waktu' => $r['ordered_at'],
            'nama' => $r['recipient_name'], 'hp' => $r['phone'], 'alamat' => $r['address'], 'catatan' => $r['notes'],
            'bayar' => $r['payment_method'], 'total' => (int)$r['total'], 'status' => $r['status'], 'email' => $r['email'],
            'bukti' => $proof, 'bukti_hilang' => $proofMissing, 'items' => $items[(int)$r['id']] ?? [],
        ];
    }
    out(['ok' => true, 'orders' => $list, 'statuses' => ORDER_STATUSES]);
}

if ($a === 'order_status') {
    $id = (int)($in['id'] ?? 0); $status = (string)($in['status'] ?? '');
    if (!in_array($status, ORDER_STATUSES, true)) out(['ok' => false, 'msg' => 'Status tidak valid.']);
    $pdo = db();
    try {
        $pdo->beginTransaction();
        $st = $pdo->prepare('SELECT status FROM orders WHERE id = ? FOR UPDATE');
        $st->execute([$id]);
        $old = $st->fetchColumn();
        if ($old === false) { $pdo->rollBack(); out(['ok' => false, 'msg' => 'Pesanan tidak ditemukan.']); }
        if ($old === 'Dibatalkan' && $status !== 'Dibatalkan') { $pdo->rollBack(); out(['ok' => false, 'msg' => 'Pesanan yang sudah dibatalkan tidak bisa diubah lagi.']); }
        if ($status === 'Dibatalkan' && $old !== 'Dibatalkan') {       // kembalikan stok
            $it = $pdo->prepare('SELECT product_id, quantity FROM order_items WHERE order_id = ? AND product_id IS NOT NULL');
            $it->execute([$id]);
            $back = $pdo->prepare('UPDATE products SET stock = stock + ? WHERE id = ?');
            foreach ($it->fetchAll() as $r) $back->execute([(int)$r['quantity'], (int)$r['product_id']]);
        }
        $pdo->prepare('UPDATE orders SET status = ? WHERE id = ?')->execute([$status, $id]);
        $pdo->commit();
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        error_log($e->getMessage());
        out(['ok' => false, 'msg' => 'Status gagal diubah.']);
    }
    out(['ok' => true, 'status' => $status]);
}

out(['ok' => false, 'msg' => 'Aksi tidak dikenal.']);
