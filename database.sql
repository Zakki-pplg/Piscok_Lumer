CREATE DATABASE IF NOT EXISTS piscok_lumer
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE piscok_lumer;

CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS orders (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  order_code VARCHAR(32) NOT NULL,
  ordered_at DATETIME NOT NULL,
  user_id INT UNSIGNED NULL,
  recipient_name VARCHAR(120) NOT NULL,
  phone VARCHAR(40) NOT NULL,
  address TEXT NOT NULL,
  payment_method VARCHAR(100) NOT NULL,
  payment_proof VARCHAR(255) NULL,
  notes TEXT NOT NULL,
  total INT UNSIGNED NOT NULL,
  status VARCHAR(100) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_orders_code (order_code),
  KEY idx_orders_user_date (user_id, ordered_at),
  CONSTRAINT fk_orders_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS order_items (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  order_id BIGINT UNSIGNED NOT NULL,
  product_id INT UNSIGNED NULL,
  product_name VARCHAR(160) NOT NULL,
  quantity SMALLINT UNSIGNED NOT NULL,
  unit_price INT UNSIGNED NOT NULL,
  subtotal INT UNSIGNED NOT NULL,
  PRIMARY KEY (id),
  KEY idx_order_items_order (order_id),
  CONSTRAINT fk_order_items_order FOREIGN KEY (order_id) REFERENCES orders (id) ON DELETE CASCADE
) ENGINE=InnoDB;


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

-- ---------- BUKTI PEMBAYARAN ----------
-- Cek pembayaran + foto bukti:   SELECT * FROM v_pembayaran;
-- (kolom link_bukti bisa di-copy ke browser; ganti 'piscok-lumer' jika nama folder berbeda)
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
