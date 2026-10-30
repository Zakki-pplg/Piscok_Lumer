<?php require __DIR__ . '/config.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Selamat Datang — <?= STORE_NAME ?></title>
<link rel="icon" type="image/png" href="assets/img/logo.png">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;800&family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css">
</head>
<body class="p-pilihan">
 <div class="choice">
  <img src="assets/img/logo.png" alt="Logo <?= STORE_NAME ?>">
  <h1>Selamat Datang<?= $user ? ', ' . htmlspecialchars($user['name']) : '' ?>!</h1>
  <p>Mau langsung lihat-lihat atau masuk ke akun dulu?</p>
  <div class="choice-grid">
   <a class="choice-card" href="index.php?masuk=1">
    <div class="ic">🍌</div>
    <h2>Masuk ke Website</h2>
    <p>Lihat menu, promo, dan mulai pesan piscok lumer favoritmu.</p>
    <span class="btn btn-primary">Lanjut ke Beranda →</span>
   </a>
   <?php if ($user): ?>
   <a class="choice-card dark" href="index.php?masuk=1">
    <div class="ic">✅</div>
    <h2>Sudah Login</h2>
    <p>Akun <?= htmlspecialchars($user['email']) ?> sudah aktif.</p>
    <span class="btn btn-light">Masuk Sekarang</span>
   </a>
   <?php else: ?>
   <a class="choice-card dark" href="login.php">
    <div class="ic">🔑</div>
    <h2>Login / Daftar</h2>
    <p>Masuk atau buat akun agar checkout pesanan lebih cepat.</p>
    <span class="btn btn-light">Login Sekarang</span>
   </a>
   <?php endif; ?>
  </div>
 </div>
</body>
</html>
