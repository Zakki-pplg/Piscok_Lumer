<?php
/* =====================================================
   CONFIG.PHP — pengaturan & fungsi bersama semua halaman
   ===================================================== */
if (session_status() === PHP_SESSION_NONE) session_start();

const WA_NUMBER  = '6289522082746';          // nomor WhatsApp toko (format 62...)
const STORE_NAME = 'Tungtung Piscok Lumer';
define('DATA_DIR',    __DIR__ . '/data');
define('USERS_FILE',  DATA_DIR . '/users.json');
define('ORDERS_FILE', DATA_DIR . '/orders.json');
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'piscok_lumer');
define('DB_USER', 'root');
define('DB_PASS', '');

/* Produk: foto ada di assets/img/ */
$products = [
    ['id' => 1, 'name' => 'Piscok Original',      'price' => 12000, 'img' => 'piscok-original.jpg',   'cat' => 'klasik',  'desc' => 'Pisang manis dibalut kulit renyah keemasan, cita rasa piscok asli.'],
    ['id' => 2, 'name' => 'Piscok Cokelat Lumer', 'price' => 14000, 'img' => 'piscok-cokelat.jpg',    'cat' => 'klasik',  'desc' => 'Lelehan cokelat premium yang meleleh di setiap gigitan.'],
    ['id' => 3, 'name' => 'Piscok Keju',          'price' => 15000, 'img' => 'piscok-keju.jpg',       'cat' => 'klasik',  'desc' => 'Taburan keju parut melimpah, gurih dan manis berpadu.'],
    ['id' => 4, 'name' => 'Piscok Oreo',          'price' => 17000, 'img' => 'piscok-oreo.jpg',       'cat' => 'spesial', 'desc' => 'Remahan biskuit Oreo dengan krim lembut, crunchy di setiap gigitan.'],
    ['id' => 5, 'name' => 'Piscok Green Tea',     'price' => 17000, 'img' => 'piscok-greentea.jpg',   'cat' => 'spesial', 'desc' => 'Siraman cokelat green tea yang harum dan lembut.'],
    ['id' => 6, 'name' => 'Piscok White Choco',   'price' => 17000, 'img' => 'piscok-whitechoco.jpg', 'cat' => 'spesial', 'desc' => 'Cokelat putih creamy dengan choco chips di atasnya.'],
    ['id' => 7, 'name' => 'Piscok Taro',          'price' => 17000, 'img' => 'piscok-taro.jpg',       'cat' => 'spesial', 'desc' => 'Siraman cokelat rasa taro berwarna ungu, manis dan wangi.'],
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
    foreach ($products as &$p) { $p['stock'] = null; $p['rating'] = 0; $p['rating_count'] = 0; }
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
}
load_product_meta($products);

function stock_html($p) {
    if ($p['stock'] === null) return '';
    if ($p['stock'] <= 0) return '<span class="stock out">Stok habis</span>';
    return '<span class="stock' . ($p['stock'] <= 5 ? ' low' : '') . '">' . $p['stock'] . ' stock</span>';
}
function rating_html($p) {
    return $p['rating_count']
        ? '<span class="rate-avg" data-pid="' . $p['id'] . '">★ ' . number_format($p['rating'], 1) . ' <small>(' . $p['rating_count'] . ')</small></span>'
        : '<span class="rate-avg none" data-pid="' . $p['id'] . '">☆ Belum ada rating</span>';
}

function out($arr) { header('Content-Type: application/json'); echo json_encode($arr); exit; }

$user = $_SESSION['user'] ?? null;

/* Tombol "Masuk ke Website" di pilihan.php mengirim ?masuk=1 */
if (isset($_GET['masuk'])) $_SESSION['intro_done'] = true;

/* Semua halaman utama wajib lewat loading.php → pilihan.php dulu.
   Halaman memanggil: require_intro();  */
function require_intro() {
    if (empty($_SESSION['intro_done'])) { header('Location: loading.php'); exit; }
}
/* Halaman yang butuh login (checkout) */
function require_login($next) {
    if (empty($_SESSION['user'])) { header('Location: login.php?next=' . urlencode($next)); exit; }
}
