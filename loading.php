<?php require __DIR__ . '/config.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Memuat… — <?= STORE_NAME ?></title>
<link rel="icon" type="image/png" href="assets/img/logo.png">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;800&family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css">
<noscript><meta http-equiv="refresh" content="4;url=pilihan.php"></noscript>
</head>
<body class="p-loading">
 <div class="load-box">
  <img src="assets/img/logo.png" alt="Logo <?= STORE_NAME ?>">
  <div class="vbars"><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i></div>
  <p>Menyiapkan piscok lumer… <b id="pct">0%</b></p>
 </div>
<script>
// persentase naik selama ±3 detik, lalu pindah ke halaman pilihan
let n = 0;
const el = document.getElementById('pct');
const t = setInterval(() => {
  n += Math.ceil(Math.random() * 6);
  if (n >= 100) { n = 100; clearInterval(t); setTimeout(() => location.href = 'pilihan.php', 350); }
  el.textContent = n + '%';
}, 110);
</script>
</body>
</html>
