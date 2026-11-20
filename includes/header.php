<?php
/* Header + navbar bersama.
   Sebelum include, set:  $page = 'beranda'; $title = 'Judul';
   Opsional: $navActive (menu yang disorot, default = $page), $loginNext (halaman tujuan setelah login) */
require_once __DIR__ . '/../config.php';
require_intro();
$page      = $page  ?? 'beranda';
$title     = $title ?? STORE_NAME;
$navActive = $navActive ?? $page;
$loginHref = 'login.php' . (!empty($loginNext) ? '?next=' . urlencode($loginNext) : '');
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= htmlspecialchars($title) ?> — <?= STORE_NAME ?></title>
<link rel="icon" type="image/png" href="assets/img/logo.png">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;800&family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@2.1.4/css/boxicons.min.css">
<link rel="stylesheet" href="assets/style.css">
</head>
<body class="p-<?= $page ?>">

<nav>
 <div class="container">
  <a href="index.php" class="brand"><img src="assets/img/logo.png" alt="Logo <?= STORE_NAME ?>"><span>Tungtung<br>Piscok Lumer</span></a>
  <div class="menu-links">
   <?php foreach (['beranda' => ['index.php', 'Beranda'], 'menu' => ['menu.php', 'Menu'], 'tentang' => ['tentang.php', 'Tentang'], 'kontak' => ['kontak.php', 'Kontak']] as $k => [$href, $label]): ?>
    <a href="<?= $href ?>" class="<?= $navActive === $k ? 'active' : '' ?>"><?= $label ?></a>
   <?php endforeach; ?>
  </div>
  <div class="nav-right">
   <?php if ($user): ?>
    <span class="hello">Hai, <?= htmlspecialchars($user['name']) ?></span>
    <button class="btn btn-primary" onclick="logout()">Keluar</button>
   <?php else: ?>
    <a class="btn btn-primary" href="<?= htmlspecialchars($loginHref) ?>">Masuk</a>
   <?php endif; ?>
  </div>
 </div>
</nav>
