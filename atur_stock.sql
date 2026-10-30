-- =====================================================
-- ATUR STOCK / UNSTOCK  (jalankan di phpMyAdmin > tab SQL)
-- Tidak perlu restart server; refresh halaman website saja.
-- Daftar id produk: 1 Original, 2 Cokelat Lumer, 3 Keju, 4 Oreo,
--                   5 Green Tea, 6 White Choco, 7 Taro
-- =====================================================
USE piscok_lumer;

-- Lihat stok sekarang
SELECT id, name AS produk, stock,
       IF(stock = 0, 'UNSTOCK (habis)', 'Tersedia') AS status
FROM products;

-- UNSTOCK satu produk (tombol "+ Tambah" hilang, tampil "Stok Habis")
UPDATE products SET stock = 0 WHERE id = 4;            -- Piscok Oreo
-- atau pakai nama:
-- UPDATE products SET stock = 0 WHERE name = 'Piscok Oreo';

-- STOCK-kan lagi / isi stok (contoh 25 buah)
UPDATE products SET stock = 25 WHERE id = 4;

-- Tambah stok +10 dari yang sekarang
-- UPDATE products SET stock = stock + 10 WHERE id = 4;

-- Unstock SEMUA produk (mis. toko tutup)
-- UPDATE products SET stock = 0;

-- Isi ulang SEMUA produk jadi 20
-- UPDATE products SET stock = 20;

-- Unstock beberapa sekaligus
-- UPDATE products SET stock = 0 WHERE id IN (1, 3, 5);

-- =====================================================
-- LIHAT RATING PELANGGAN
-- =====================================================
SELECT * FROM v_rating_detail;                          -- semua rating + komentar
SELECT * FROM v_rating_summary;                         -- rata-rata per produk
-- SELECT * FROM v_rating_detail WHERE produk = 'Piscok Oreo';
-- DELETE FROM ratings WHERE id = 1;                    -- hapus rating tertentu
