<?php
/* =====================================================
   API.PHP — backend AJAX: register, login, logout, checkout
   ===================================================== */
require __DIR__ . '/config.php';

$in = json_decode(file_get_contents('php://input'), true) ?: [];
$a  = $_GET['action'] ?? '';

/* Checkout dengan bukti pembayaran dikirim sebagai multipart:
   field "data" = JSON pesanan, field "proof" = foto bukti. */
if (isset($_POST['data'])) $in = json_decode($_POST['data'], true) ?: [];
/* Jika ukuran kiriman melebihi post_max_size, PHP mengosongkan $_POST dan $_FILES */
if ($a === 'checkout' && empty($in) && empty($_POST) && (int)($_SERVER['CONTENT_LENGTH'] ?? 0) > 0)
    out(['ok' => false, 'msg' => 'Foto terlalu besar. Pilih foto yang lebih kecil (maksimal 5 MB).']);

if ($a === 'register') {
    $name = trim($in['name'] ?? ''); $email = strtolower(trim($in['email'] ?? '')); $pass = $in['password'] ?? '';
    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($pass) < 6)
        out(['ok' => false, 'msg' => 'Isi nama, email valid, dan password minimal 6 karakter.']);
    try {
        $pdo = db();
        $stmt = $pdo->prepare('INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)');
        $stmt->execute([$name, $email, password_hash($pass, PASSWORD_DEFAULT)]);
    } catch (PDOException $e) {
        if ($e->getCode() === '23000') out(['ok' => false, 'msg' => 'Email sudah terdaftar.']);
        error_log($e->getMessage());
        out(['ok' => false, 'msg' => 'Database belum siap. Periksa konfigurasi MySQL.']);
    }
    $_SESSION['user'] = ['name' => $name, 'email' => $email];
    $_SESSION['intro_done'] = true;
    out(['ok' => true, 'name' => $name]);
}

if ($a === 'login') {
    $email = strtolower(trim($in['email'] ?? '')); $pass = $in['password'] ?? '';
    try {
        $stmt = db()->prepare('SELECT name, password_hash FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $account = $stmt->fetch();
    } catch (PDOException $e) {
        error_log($e->getMessage());
        out(['ok' => false, 'msg' => 'Database belum siap. Periksa konfigurasi MySQL.']);
    }
    if (!$account || !password_verify($pass, $account['password_hash']))
        out(['ok' => false, 'msg' => 'Email atau password salah.']);
    $_SESSION['user'] = ['name' => $account['name'], 'email' => $email];
    $_SESSION['intro_done'] = true;
    out(['ok' => true, 'name' => $account['name']]);
}

if ($a === 'logout') {
    unset($_SESSION['user']);
    out(['ok' => true]);
}

if ($a === 'rate') {
    if (empty($_SESSION['user'])) out(['ok' => false, 'login' => true, 'msg' => 'Masuk dulu untuk memberi rating.']);
    $pid = (int)($in['product_id'] ?? 0); $rating = (int)($in['rating'] ?? 0);
    $comment = mb_substr(trim($in['comment'] ?? ''), 0, 500);
    if (!in_array($pid, array_column($products, 'id'), true) || $rating < 1 || $rating > 5)
        out(['ok' => false, 'msg' => 'Pilih rating 1 sampai 5 bintang.']);
    try {
        $pdo = db();
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$_SESSION['user']['email']]);
        $uid = $stmt->fetchColumn();
        if (!$uid) out(['ok' => false, 'msg' => 'Akun tidak ditemukan, silakan masuk ulang.']);
        $pdo->prepare('INSERT INTO ratings (product_id, user_id, rating, comment) VALUES (?, ?, ?, ?)
                       ON DUPLICATE KEY UPDATE rating = VALUES(rating), comment = VALUES(comment)')
            ->execute([$pid, $uid, $rating, $comment]);
        $stmt = $pdo->prepare('SELECT ROUND(AVG(rating), 1) AS a, COUNT(*) AS c FROM ratings WHERE product_id = ?');
        $stmt->execute([$pid]);
        $r = $stmt->fetch();
    } catch (PDOException $e) {
        error_log($e->getMessage());
        out(['ok' => false, 'msg' => 'Rating gagal disimpan. Pastikan update_rating_stock.sql sudah dijalankan.']);
    }
    out(['ok' => true, 'rating' => (float)$r['a'], 'count' => (int)$r['c']]);
}

if ($a === 'checkout') {
    $map = array_column($products, null, 'id');
    $total = 0; $lines = [];
    foreach (($in['items'] ?? []) as $it) {          // harga selalu dihitung ulang di server
        $id = (int)($it['id'] ?? 0); $q = max(0, min(100, (int)($it['qty'] ?? 0)));
        if (!isset($map[$id]) || $q < 1) continue;
        $sub = $map[$id]['price'] * $q; $total += $sub;
        $lines[] = ['id' => $id, 'name' => $map[$id]['name'], 'qty' => $q, 'price' => $map[$id]['price'], 'sub' => $sub];
    }
    $nama = trim($in['nama'] ?? ''); $hp = trim($in['hp'] ?? ''); $alamat = trim($in['alamat'] ?? '');
    if (!$lines) out(['ok' => false, 'msg' => 'Keranjang kosong.']);
    if ($nama === '' || $hp === '' || $alamat === '') out(['ok' => false, 'msg' => 'Lengkapi nama, no. HP, dan alamat.']);
    /* ---- bukti pembayaran: wajib untuk Transfer Bank, QRIS, E-Wallet ---- */
    $bayar = trim($in['bayar'] ?? 'COD (Bayar di tempat)');
    $metodeQr = ['Transfer Bank', 'QRIS', 'E-Wallet'];
    if ($bayar !== 'COD (Bayar di tempat)' && !in_array($bayar, $metodeQr, true))
        out(['ok' => false, 'msg' => 'Metode pembayaran tidak valid.']);
    $perluBukti = in_array($bayar, $metodeQr, true);
    $proofTmp = null; $proofExt = null;
    if ($perluBukti) {
        $f = $_FILES['proof'] ?? null;
        if (!$f || $f['error'] === UPLOAD_ERR_NO_FILE)
            out(['ok' => false, 'proof' => true, 'msg' => 'Upload foto bukti pembayaran dulu ya.']);
        if ($f['error'] === UPLOAD_ERR_INI_SIZE || $f['error'] === UPLOAD_ERR_FORM_SIZE || $f['size'] > 5 * 1024 * 1024)
            out(['ok' => false, 'proof' => true, 'msg' => 'Foto terlalu besar. Pilih foto yang lebih kecil (maksimal 5 MB).']);
        if ($f['error'] !== UPLOAD_ERR_OK)
            out(['ok' => false, 'proof' => true, 'msg' => 'Foto gagal diupload. Coba pilih foto lagi.']);
        $info = @getimagesize($f['tmp_name']);
        $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        if (!$info || !isset($allowed[$info['mime']]))
            out(['ok' => false, 'proof' => true, 'msg' => 'Bukti harus berupa foto JPG, PNG, atau WEBP.']);
        $proofTmp = $f['tmp_name']; $proofExt = $allowed[$info['mime']];
    }

    $kode = 'PL' . date('ymdHis') . rand(10, 99);
    $proofRel = $perluBukti ? 'uploads/bukti/bukti-' . $kode . '-' . bin2hex(random_bytes(6)) . '.' . $proofExt : null;
    $savedFile = null;
    $order = [
        'kode' => $kode, 'waktu' => date('Y-m-d H:i:s'),
        'akun' => $_SESSION['user']['email'] ?? null, 'nama' => $nama, 'hp' => $hp, 'alamat' => $alamat,
        'bayar' => $bayar, 'catatan' => trim($in['catatan'] ?? ''),
        'items' => $lines, 'total' => $total, 'bukti' => $perluBukti,
        'status' => $perluBukti ? 'Menunggu verifikasi pembayaran' : 'Menunggu konfirmasi',
    ];
    try {
        $pdo = db();
        $pdo->beginTransaction();
        /* cek & kurangi stock (baris dikunci supaya tidak oversell) */
        $lock = $pdo->prepare('SELECT stock FROM products WHERE id = ? FOR UPDATE');
        $cut  = $pdo->prepare('UPDATE products SET stock = stock - ? WHERE id = ?');
        foreach ($lines as $line) {
            $lock->execute([$line['id']]);
            $stock = $lock->fetchColumn();
            if ($stock === false) continue;                       // produk belum terdaftar di tabel stock: tidak dibatasi
            if ((int)$stock < $line['qty']) {
                $pdo->rollBack();
                out(['ok' => false, 'stock' => true, 'msg' => (int)$stock === 0
                    ? $line['name'] . ' sedang habis (unstock). Hapus dari keranjang ya.'
                    : 'Stok ' . $line['name'] . ' tinggal ' . (int)$stock . '. Kurangi jumlah pesanan.']);
            }
            $cut->execute([$line['qty'], $line['id']]);
        }
        $accountId = null;
        if (!empty($_SESSION['user']['email'])) {
            $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
            $stmt->execute([$_SESSION['user']['email']]);
            $accountId = $stmt->fetchColumn() ?: null;
        }
        /* kolom payment_proof hanya dipakai bila ada bukti, jadi COD tetap jalan walau update_pembayaran.sql belum dijalankan */
        $cols = 'order_code, ordered_at, user_id, recipient_name, phone, address, payment_method, notes, total, status';
        $vals = [$order['kode'], $order['waktu'], $accountId, $nama, $hp, $alamat, $order['bayar'], $order['catatan'], $total, $order['status']];
        if ($proofRel) { $cols .= ', payment_proof'; $vals[] = $proofRel; }
        $stmt = $pdo->prepare('INSERT INTO orders (' . $cols . ') VALUES (' . implode(', ', array_fill(0, count($vals), '?')) . ')');
        $stmt->execute($vals);
        $orderId = $pdo->lastInsertId();
        $stmt = $pdo->prepare('INSERT INTO order_items (order_id, product_id, product_name, quantity, unit_price, subtotal) VALUES (?, ?, ?, ?, ?, ?)');
        foreach ($lines as $line) {
            $stmt->execute([$orderId, $line['id'], $line['name'], $line['qty'], $line['price'], $line['sub']]);
        }
        /* simpan file bukti tepat sebelum commit; kalau gagal, pesanan dibatalkan */
        if ($proofTmp) {
            $dir = __DIR__ . '/uploads/bukti';
            if (!is_dir($dir)) @mkdir($dir, 0755, true);
            $target = __DIR__ . '/' . $proofRel;
            if (!move_uploaded_file($proofTmp, $target)) {
                $pdo->rollBack();
                out(['ok' => false, 'proof' => true, 'msg' => 'Foto bukti gagal disimpan di server. Pastikan folder uploads/bukti bisa ditulis.']);
            }
            $savedFile = $target;
        }
        $pdo->commit();
    } catch (PDOException $e) {
        if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
        if ($savedFile && is_file($savedFile)) @unlink($savedFile);
        error_log($e->getMessage());
        out(['ok' => false, 'msg' => $e->getCode() === '42S22'
            ? 'Kolom bukti pembayaran belum ada. Jalankan update_pembayaran.sql di phpMyAdmin.'
            : 'Pesanan gagal disimpan. Periksa koneksi database.']);
    }
    out(['ok' => true, 'order' => $order]);
}

out(['ok' => false, 'msg' => 'Aksi tidak dikenal.']);
