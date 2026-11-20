<?php
/* =====================================================
   CONFIG.PHP — pengaturan & fungsi bersama semua halaman
   ===================================================== */
if (session_status() === PHP_SESSION_NONE) session_start();

const WA_NUMBER  = '6289522082746';          // nomor WhatsApp toko (format 62...)
const STORE_NAME = 'Tungtung Piscok Lumer';
const IG_USER    = 'tungtungpiscoklumer';    // username Instagram toko (tanpa @)
const IG_URL     = 'https://www.instagram.com/tungtungpiscoklumer?cnvx=azhwZHN1MGQxY24z';

/* Zona waktu toko: dipakai untuk "hari ini" pada ringkasan penjualan di halaman admin */
date_default_timezone_set('Asia/Jakarta');

/* ---------- LOGIN HALAMAN ADMIN (admin.php) ----------
   Hanya 2 akun yang boleh masuk: Developer dan Admin (sesuai daftar TEAM di bawah).
   Password disimpan sebagai hash. Ganti password:
     php -r "echo password_hash('PASSWORD_BARU', PASSWORD_DEFAULT);"
   lalu tempel hasilnya pada 'hash' akun terkait.
   Password bawaan:  admin -> Piscok#Admin26   |   developer -> Piscok#Dev26   (SEGERA GANTI sebelum online) */
const ADMIN_ACCOUNTS = [
    'admin'     => ['name' => 'Muhammad Alfan',        'role' => 'Admin',
                    'hash' => '$2y$10$ozN.b2ArhjXmtGSDx71JueR/KQSEXlONOEFsLBQjWO3JtCXC8f4xS'],
    'developer' => ['name' => 'Sulthan Fakhri Muzakki', 'role' => 'Developer',
                    'hash' => '$2y$10$Hh8y9oR9Gv8.Fqv6vQrfuOqpCK7PJcY2hq5RIPhpgfK9BK7YOONTm'],
];
/* Isi otomatis username & password di form login admin.
   'local'  = hanya saat website dibuka dari komputer sendiri (localhost / XAMPP)  <- aman, disarankan
   'always' = selalu terisi, termasuk di hosting online (siapa pun bisa melihat password di kode halaman!)
   'off'    = tidak pernah diisi otomatis */
const ADMIN_AUTOFILL = 'local';
/* Teks password yang ditampilkan otomatis (hanya dipakai jika ADMIN_AUTOFILL tidak 'off'). Samakan jika password diganti. */
const ADMIN_AUTOFILL_PASS = ['admin' => 'Piscok#Admin26', 'developer' => 'Piscok#Dev26'];

/* Nama kelompok (tampil di footer) */
const TEAM = [
    ['Sulthan Fakhri Muzakki',       'Developer'],
    ['Muhammad Alfan',               'Admin'],
    ['Muhammad Daffa Hamdanu',       'UI & UX'],
    ['Shatya Imeyda',                'UI & UX'],
    ['Muhammad Faiq Azikra Efendi',  'UI & UX'],
    ['Muhammad Alfathan',            'Project Management'],
    ['Muhammad Aditya Nurzaeni',     'Project Management'],
];
define('DATA_DIR',    __DIR__ . '/data');
define('USERS_FILE',  DATA_DIR . '/users.json');
define('ORDERS_FILE', DATA_DIR . '/orders.json');
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'piscok_lumer');
define('DB_USER', 'root');
define('DB_PASS', '');

/* Produk: foto ada di assets/img/
   'desc'   = deskripsi singkat (daftar menu)
   'detail' = deskripsi lengkap (halaman detail)
   'poin'   = poin keunggulan (halaman detail)  */
$products = [
    ['id' => 1, 'name' => 'Piscok Original',      'price' => 12000, 'img' => 'piscok-original.jpg',   'cat' => 'klasik',  'desc' => 'Pisang manis dibalut kulit renyah keemasan, cita rasa piscok asli.',
     'detail' => 'Piscok Original adalah versi paling klasik dan paling pas untuk pertama kali mencoba. Pisang matang pilihan dipadukan dengan cokelat, dibungkus kulit lumpia tipis, lalu digoreng saat pesanan masuk hingga garing keemasan. Luarnya renyah, dalamnya lembut, dan manisnya pas tanpa terasa berlebihan.',
     'poin' => ['Pisang matang pilihan, manis alami', 'Kulit tipis yang garing keemasan', 'Cocok untuk yang baru pertama mencoba']],
    ['id' => 2, 'name' => 'Piscok Cokelat Lumer', 'price' => 14000, 'img' => 'piscok-cokelat.jpg',    'cat' => 'klasik',  'desc' => 'Lelehan cokelat premium yang meleleh di setiap gigitan.',
     'detail' => 'Piscok Cokelat Lumer adalah menu andalan kami. Setiap gigitan menghadirkan lelehan cokelat premium yang kental dan hangat, berpadu dengan pisang manis dan kulit yang garing. Paling nikmat disantap selagi hangat, saat cokelatnya benar-benar lumer di mulut.',
     'poin' => ['Lelehan cokelat premium yang kental', 'Disajikan hangat, lumer saat digigit', 'Pas untuk pencinta cokelat']],
    ['id' => 3, 'name' => 'Piscok Keju',          'price' => 15000, 'img' => 'piscok-keju.jpg',       'cat' => 'klasik',  'desc' => 'Taburan keju parut melimpah, gurih dan manis berpadu.',
     'detail' => 'Piscok Keju memadukan manis pisang dan cokelat dengan gurihnya keju parut yang ditabur melimpah di atasnya. Perpaduan rasa manis dan gurih ini membuatnya sulit berhenti pada satu potong, cocok sebagai teman santai sore hari.',
     'poin' => ['Taburan keju parut yang melimpah', 'Manis dan gurih berpadu seimbang', 'Cocok untuk yang tidak terlalu suka manis']],
    ['id' => 4, 'name' => 'Piscok Oreo',          'price' => 17000, 'img' => 'piscok-oreo.jpg',       'cat' => 'spesial', 'desc' => 'Remahan biskuit Oreo dengan krim lembut, crunchy di setiap gigitan.',
     'detail' => 'Piscok Oreo adalah varian spesial dengan remahan biskuit Oreo dan krim yang lembut di atas piscok hangat. Tekstur renyah dari remahan biskuit bertemu lembutnya pisang dan cokelat, menghasilkan sensasi crunchy di setiap gigitan.',
     'poin' => ['Remahan biskuit Oreo yang crunchy', 'Krim lembut di setiap gigitan', 'Varian spesial dengan topping crunchy']],
    ['id' => 5, 'name' => 'Piscok Green Tea',     'price' => 17000, 'img' => 'piscok-greentea.jpg',   'cat' => 'spesial', 'desc' => 'Siraman cokelat green tea yang harum dan lembut.',
     'detail' => 'Piscok Green Tea disiram cokelat rasa green tea yang harum dan lembut. Sedikit sentuhan khas teh hijau menyeimbangkan manisnya pisang, sehingga rasanya tidak terlalu manis dan tetap terasa istimewa.',
     'poin' => ['Aroma green tea yang harum', 'Rasa lembut dan seimbang', 'Pilihan tepat untuk pencinta teh hijau']],
    ['id' => 6, 'name' => 'Piscok White Choco',   'price' => 17000, 'img' => 'piscok-whitechoco.jpg', 'cat' => 'spesial', 'desc' => 'Cokelat putih creamy dengan choco chips di atasnya.',
     'detail' => 'Piscok White Choco dilumuri cokelat putih yang creamy dan ditaburi choco chips di atasnya. Rasanya manis, lembut, dan creamy, dengan sedikit kerenyahan dari choco chips yang membuat setiap gigitan lebih seru.',
     'poin' => ['Cokelat putih yang creamy', 'Taburan choco chips di atasnya', 'Manis lembut, favorit pencinta rasa creamy']],
    ['id' => 7, 'name' => 'Piscok Taro',          'price' => 17000, 'img' => 'piscok-taro.jpg',       'cat' => 'spesial', 'desc' => 'Siraman cokelat rasa taro berwarna ungu, manis dan wangi.',
     'detail' => 'Piscok Taro disiram cokelat rasa taro berwarna ungu yang manis dan wangi. Warnanya yang cantik menjadikannya menu yang menarik untuk difoto, sementara rasanya yang lembut berpadu pas dengan pisang hangat di dalamnya.',
     'poin' => ['Siraman rasa taro yang manis dan wangi', 'Warna ungu yang cantik', 'Varian spesial yang beda dari yang lain']],
];

function rp($n) { return 'Rp ' . number_format($n, 0, ',', '.'); }
function load_json($f) { return file_exists($f) ? (json_decode(file_get_contents($f), true) ?: []) : []; }
function save_json($f, $d) {
    if (!is_dir(DATA_DIR)) mkdir(DATA_DIR, 0755, true);
    file_put_contents($f, json_encode($d, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
}
function db() {
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO(
            'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
            DB_USER,
            DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
        );
    }
    return $pdo;
}
/* Stock & rating dibaca dari MySQL (tabel products & ratings).
   stock = null  -> database belum siap / produk belum terdaftar: dianggap tersedia, teks stok disembunyikan.
   stock = 0     -> UNSTOCK: tombol "+ Tambah" diganti label "Stok Habis". */
function load_product_meta(array &$products) {
    foreach ($products as &$p) { $p['stock'] = null; $p['rating'] = 0; $p['rating_count'] = 0; $p['sold'] = 0; }
    unset($p);
    try { $pdo = db(); } catch (Throwable $e) { error_log($e->getMessage()); return; }
    $idx = array_flip(array_column($products, 'id'));
    try {
        foreach ($pdo->query('SELECT id, stock FROM products') as $r)
            if (isset($idx[$r['id']])) $products[$idx[$r['id']]]['stock'] = (int)$r['stock'];
    } catch (Throwable $e) { error_log($e->getMessage()); }
    try {
        foreach ($pdo->query('SELECT product_id, AVG(rating) AS a, COUNT(*) AS c FROM ratings GROUP BY product_id') as $r)
            if (isset($idx[$r['product_id']])) {
                $products[$idx[$r['product_id']]]['rating'] = round((float)$r['a'], 1);
                $products[$idx[$r['product_id']]]['rating_count'] = (int)$r['c'];
            }
    } catch (Throwable $e) { error_log($e->getMessage()); }
    /* jumlah terjual = sold_awal (diatur manual lewat atur_terjual.sql) + total quantity di order_items */
    try {
        foreach ($pdo->query('SELECT id, sold_awal FROM products') as $r)
            if (isset($idx[$r['id']])) $products[$idx[$r['id']]]['sold'] = (int)$r['sold_awal'];
    } catch (Throwable $e) { /* kolom sold_awal belum dibuat: abaikan, hitung dari pesanan saja */ }
    try {
        foreach ($pdo->query("SELECT oi.product_id, SUM(oi.quantity) AS q FROM order_items oi JOIN orders o ON o.id = oi.order_id WHERE oi.product_id IS NOT NULL AND o.status <> 'Dibatalkan' GROUP BY oi.product_id") as $r)
            if (isset($idx[$r['product_id']])) $products[$idx[$r['product_id']]]['sold'] += (int)$r['q'];
    } catch (Throwable $e) { error_log($e->getMessage()); }
}
load_product_meta($products);

function stock_html($p) {
    if ($p['stock'] === null) return '';
    if ($p['stock'] <= 0) return '<span class="stock out"><i class="bx bx-error-circle"></i> Stok habis</span>';
    return '<span class="stock' . ($p['stock'] <= 5 ? ' low' : '') . '"><i class="bx bx-package"></i> ' . $p['stock'] . ' stock</span>';
}
/* ringkas: ikon bintang + angka rata-rata (dipakai di kartu menu) */
function rating_html($p) {
    return $p['rating_count']
        ? '<span class="rate-avg"><i class="bx bxs-star"></i> ' . number_format($p['rating'], 1) . ' <small>(' . $p['rating_count'] . ')</small></span>'
        : '<span class="rate-avg none"><i class="bx bx-star"></i> Belum ada rating</span>';
}
/* "12 terjual" / "1,2 rb terjual" (dipakai di kartu menu) */
function sold_html($p) {
    $n = (int)($p['sold'] ?? 0);
    $t = $n >= 1000 ? rtrim(rtrim(number_format($n / 1000, 1, ',', ''), '0'), ',') . ' rb' : (string)$n;
    return '<span class="sold-count"><i class="bx bx-shopping-bag"></i> ' . $t . ' terjual</span>';
}
/* deretan 5 bintang sesuai nilai rata-rata (mendukung setengah bintang) */
function stars_html($avg) {
    $h = '<span class="stars-row" aria-label="Rating ' . number_format($avg, 1) . ' dari 5">';
    for ($i = 1; $i <= 5; $i++) {
        if ($avg >= $i - 0.25)      $h .= '<i class="bx bxs-star"></i>';
        elseif ($avg >= $i - 0.75)  $h .= '<i class="bx bxs-star-half"></i>';
        else                        $h .= '<i class="bx bx-star"></i>';
    }
    return $h . '</span>';
}
function find_product($id) {
    global $products;
    foreach ($products as $p) if ($p['id'] === (int)$id) return $p;
    return null;
}
/* menu dengan rating tertinggi (rata-rata, lalu jumlah ulasan terbanyak); hanya yang sudah punya rating */
function top_rated($n = 3) {
    global $products;
    $rated = array_values(array_filter($products, fn($p) => $p['rating_count'] > 0));
    usort($rated, fn($a, $b) => [$b['rating'], $b['rating_count'], $a['id']] <=> [$a['rating'], $a['rating_count'], $b['id']]);
    return array_slice($rated, 0, $n);
}
/* "Budi Santoso" -> "B***o" (privasi pemberi ulasan) */
function mask_name($name) {
    $name = trim($name);
    $len = mb_strlen($name);
    if ($len <= 1) return $name . '***';
    return mb_strtoupper(mb_substr($name, 0, 1)) . '***' . mb_strtolower(mb_substr($name, -1));
}
function tgl_id($datetime) {
    $bln = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    $t = strtotime($datetime);
    return $t ? date('j', $t) . ' ' . $bln[(int)date('n', $t) - 1] . ' ' . date('Y', $t) : '';
}

function out($arr) { header('Content-Type: application/json'); echo json_encode($arr); exit; }

$user = $_SESSION['user'] ?? null;
/* Admin = sudah login lewat admin.php sebagai Developer atau Admin (akun pelanggan biasa TIDAK cukup) */
function is_admin() { return !empty($_SESSION['admin']) && isset(ADMIN_ACCOUNTS[$_SESSION['admin_user'] ?? '']); }
function admin_local() { return in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true); }
function admin_autofill_on() { return ADMIN_AUTOFILL === 'always' || (ADMIN_AUTOFILL === 'local' && admin_local()); }

/* Tombol "Masuk ke Website" di pilihan.php mengirim ?masuk=1 */
if (isset($_GET['masuk'])) $_SESSION['intro_done'] = true;

/* Semua halaman utama wajib lewat loading.php → pilihan.php dulu.
   Halaman memanggil: require_intro();  */
function require_intro() {
    if (empty($_SESSION['intro_done'])) { header('Location: loading.php'); exit; }
}
/* Halaman yang butuh login */
function require_login($next) {
    if (empty($_SESSION['user'])) { header('Location: login.php?next=' . urlencode($next)); exit; }
}
