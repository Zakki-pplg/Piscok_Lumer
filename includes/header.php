<?php
/* Header + navbar bersama.
   Sebelum include, set:  $page = 'beranda'; $title = 'Judul';  */
require_once __DIR__ . '/../config.php';
require_intro();
$page  = $page  ?? 'beranda';
$title = $title ?? STORE_NAME;
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= htmlspecialchars($title) ?> — <?= STORE_NAME ?></title>
<link rel="icon" type="image/png" href="assets/img/logo.png">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;800&family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css">
</head>
<body class="p-<?= $page ?>">

<nav>
 <div class="container">
  <a href="index.php" class="brand"><img src="assets/img/logo.png" alt="Logo <?= STORE_NAME ?>"><span>Tungtung<br>Piscok Lumer</span></a>
  <div class="menu-links">
   <?php foreach (['beranda' => ['index.php', 'Beranda'], 'menu' => ['menu.php', 'Menu'], 'tentang' => ['tentang.php', 'Tentang'], 'kontak' => ['kontak.php', 'Kontak']] as $k => [$href, $label]): ?>
    <a href="<?= $href ?>" class="<?= $page === $k ? 'active' : '' ?>"><?= $label ?></a>
   <?php endforeach; ?>
  </div>
  <div class="nav-right">
   <a class="btn btn-light" href="order.php">🛒 Order<span class="cart-badge" id="cartCount">0</span></a>
   <?php if ($user): ?>
    <span class="hello">Hai, <?= htmlspecialchars($user['name']) ?></span>
    <button class="btn btn-primary" onclick="logout()">Keluar</button>
   <?php else: ?>
    <a class="btn btn-primary" href="login.php">Masuk</a>
   <?php endif; ?>
  </div>
 </div>
</nav>
