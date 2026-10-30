-- =====================================================
-- UPDATE: fitur RATING + STOCK / UNSTOCK
-- Jalankan di phpMyAdmin (tab SQL) pada database piscok_lumer
-- yang SUDAH ada. Aman dijalankan berulang kali.
-- =====================================================
USE piscok_lumer;

-- ---------- STOCK ----------
-- stock = jumlah tersedia. stock = 0  ->  UNSTOCK (tombol "+ Tambah" hilang)
CREATE TABLE IF NOT EXISTS products (
  id INT UNSIGNED NOT NULL,
  name VARCHAR(160) NOT NULL,
  stock INT UNSIGNED NOT NULL DEFAULT 0,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB;

-- stok awal 20 per produk (stok yang sudah ada TIDAK ditimpa saat dijalankan ulang)
INSERT INTO products (id, name, stock) VALUES
  (1, 'Piscok Original',      20),
  (2, 'Piscok Cokelat Lumer', 20),
  (3, 'Piscok Keju',          20),
  (4, 'Piscok Oreo',          20),
  (5, 'Piscok Green Tea',     20),
  (6, 'Piscok White Choco',   20),
  (7, 'Piscok Taro',          20)
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- ---------- RATING ----------
-- satu akun hanya punya satu rating per produk (rating ulang = memperbarui)
CREATE TABLE IF NOT EXISTS ratings (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  product_id INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NOT NULL,
  rating TINYINT UNSIGNED NOT NULL,
  comment VARCHAR(500) NOT NULL DEFAULT '',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_rating_user_product (product_id, user_id),
  CONSTRAINT chk_rating_range CHECK (rating BETWEEN 1 AND 5),
  CONSTRAINT fk_ratings_product FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE,
  CONSTRAINT fk_ratings_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Lihat rating orang: tinggal   SELECT * FROM v_rating_detail;
CREATE OR REPLACE VIEW v_rating_detail AS
SELECT r.id, p.name AS produk, u.name AS pelanggan, u.email,
       r.rating, r.comment AS komentar, r.created_at AS waktu
FROM ratings r
JOIN products p ON p.id = r.product_id
JOIN users u    ON u.id = r.user_id
ORDER BY r.created_at DESC;

-- Ringkasan rata-rata rating per produk:   SELECT * FROM v_rating_summary;
CREATE OR REPLACE VIEW v_rating_summary AS
SELECT p.id, p.name AS produk, p.stock,
       COUNT(r.id) AS jumlah_rating,
       ROUND(AVG(r.rating), 2) AS rata_rata
FROM products p
LEFT JOIN ratings r ON r.product_id = p.id
GROUP BY p.id, p.name, p.stock
ORDER BY p.id;
