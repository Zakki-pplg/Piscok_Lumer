-- =====================================================
-- ATUR JUMLAH TERJUAL  (jalankan di phpMyAdmin > tab SQL)
-- Jumlah terjual di website = sold_awal (diatur manual) + total pesanan asli
-- dari tabel order_items. Jadi angka awal bisa Anda tentukan, lalu naik
-- otomatis setiap ada pesanan baru. Refresh website setelah menjalankan.
-- Daftar id produk: 1 Original, 2 Cokelat Lumer, 3 Keju, 4 Oreo,
--                   5 Green Tea, 6 White Choco, 7 Taro
-- =====================================================
USE piscok_lumer;

-- LANGKAH 1 (cukup sekali): tambah kolom sold_awal bila belum ada
SET @ada := (SELECT COUNT(*) FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'products' AND COLUMN_NAME = 'sold_awal');
SET @q := IF(@ada = 0,
             'ALTER TABLE products ADD COLUMN sold_awal INT UNSIGNED NOT NULL DEFAULT 0 AFTER stock',
             'SELECT ''kolom sold_awal sudah ada'' AS info');
PREPARE s FROM @q; EXECUTE s; DEALLOCATE PREPARE s;

-- LANGKAH 2: atur angka awal terjual per produk (ubah angkanya sesuai keinginan)
UPDATE products SET sold_awal = 120 WHERE id = 1;      -- Piscok Original
UPDATE products SET sold_awal = 250 WHERE id = 2;      -- Piscok Cokelat Lumer
UPDATE products SET sold_awal = 90  WHERE id = 3;      -- Piscok Keju
UPDATE products SET sold_awal = 150 WHERE id = 4;      -- Piscok Oreo
UPDATE products SET sold_awal = 60  WHERE id = 5;      -- Piscok Green Tea
UPDATE products SET sold_awal = 75  WHERE id = 6;      -- Piscok White Choco
UPDATE products SET sold_awal = 40  WHERE id = 7;      -- Piscok Taro

-- Contoh lain
-- UPDATE products SET sold_awal = sold_awal + 10 WHERE id = 2;   -- tambah 10 dari yang sekarang
-- UPDATE products SET sold_awal = 0;                              -- reset: hanya hitung pesanan asli
-- UPDATE products SET sold_awal = 100 WHERE name = 'Piscok Oreo'; -- pakai nama produk

-- LIHAT hasil: awal + pesanan asli = yang tampil di website
SELECT p.id, p.name AS produk, p.sold_awal,
       COALESCE(SUM(oi.quantity), 0) AS dari_pesanan,
       p.sold_awal + COALESCE(SUM(oi.quantity), 0) AS tampil_di_website
FROM products p
LEFT JOIN order_items oi ON oi.product_id = p.id
GROUP BY p.id, p.name, p.sold_awal
ORDER BY p.id;
