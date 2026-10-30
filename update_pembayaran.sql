-- =====================================================
-- UPDATE: BUKTI PEMBAYARAN (QRIS / Transfer / E-Wallet)
-- Jalankan di phpMyAdmin (tab SQL) pada database piscok_lumer
-- yang SUDAH ada. Aman dijalankan berulang kali.
-- Kalau baru import database.sql versi terbaru, file ini tidak perlu.
-- =====================================================
USE piscok_lumer;

-- Tambah kolom payment_proof ke tabel orders (hanya kalau belum ada)
SET @sql = (
  SELECT IF(COUNT(*) = 0,
            'ALTER TABLE orders ADD COLUMN payment_proof VARCHAR(255) NULL AFTER payment_method',
            'SELECT ''kolom payment_proof sudah ada'' AS info')
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'orders' AND COLUMN_NAME = 'payment_proof'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Daftar pembayaran + bukti. Cara cek:   SELECT * FROM v_pembayaran;
-- Kolom link_bukti bisa di-copy ke browser untuk melihat fotonya.
-- (Ganti 'piscok-lumer' jika nama folder website Anda berbeda.)
CREATE OR REPLACE VIEW v_pembayaran AS
SELECT o.order_code AS kode,
       o.ordered_at AS waktu,
       o.recipient_name AS penerima,
       o.phone AS no_hp,
       o.payment_method AS metode,
       o.total,
       o.status,
       o.payment_proof AS file_bukti,
       IF(o.payment_proof IS NULL, '-', CONCAT('http://localhost/piscok-lumer/', o.payment_proof)) AS link_bukti
FROM orders o
ORDER BY o.ordered_at DESC;
