<?php
require __DIR__ . '/config.php';
/* halaman tujuan setelah login (hanya yang diizinkan) */
$allowed = ['index.php', 'menu.php', 'order.php', 'checkout.php', 'kontak.php', 'tentang.php'];
$next = in_array($_GET['next'] ?? '', $allowed, true) ? $_GET['next'] : 'index.php';
if ($user) { $_SESSION['intro_done'] = true; header('Location: ' . $next); exit; }
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Login — <?= STORE_NAME ?></title>
<link rel="icon" type="image/png" href="assets/img/logo.png">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;800&family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css">
</head>
<body class="p-login">
 <div class="login-wrap">
  <div class="login-left">
   <div>
    <img src="assets/img/logo.png" alt="Logo <?= STORE_NAME ?>">
    <h2>Halo, Pecinta Piscok! 🍫</h2>
    <p>Masuk untuk checkout pesanan dengan cepat dan simpan riwayat order Anda.</p>
   </div>
  </div>
  <div class="login-right">
   <div class="login-card">
    <h1>Akun Anda</h1>
    <p>Masuk atau daftar akun baru.</p>
    <div class="tabs"><button class="on" id="tabL" onclick="authTab('l')">Masuk</button><button id="tabR" onclick="authTab('r')">Daftar</button></div>
    <div class="msg" id="authMsg"></div>
    <div class="field" id="fName" style="display:none"><label>Nama</label><input id="aName" placeholder="Nama lengkap"></div>
    <div class="field"><label>Email</label><input id="aEmail" type="email" placeholder="email@contoh.com"></div>
    <div class="field"><label>Password</label><input id="aPass" type="password" placeholder="Minimal 6 karakter"></div>
    <button class="btn btn-primary btn-block" id="authBtn" onclick="submitAuth()">Masuk</button>
    <a class="back" href="index.php?masuk=1">← Lanjut tanpa login</a>
   </div>
  </div>
 </div>

<script>
const NEXT = <?= json_encode($next) ?>;
const $ = id => document.getElementById(id);
let mode = 'l';
function authTab(m) {
  mode = m;
  $('tabL').classList.toggle('on', m == 'l'); $('tabR').classList.toggle('on', m == 'r');
  $('fName').style.display = m == 'r' ? 'block' : 'none';
  $('authBtn').textContent = m == 'r' ? 'Daftar Akun' : 'Masuk';
  $('authMsg').className = 'msg';
}
async function submitAuth() {
  const res = await fetch('api.php?action=' + (mode == 'r' ? 'register' : 'login'), {
    method: 'POST', headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ email: $('aEmail').value, password: $('aPass').value, name: $('aName').value })
  });
  const r = await res.json(), m = $('authMsg');
  if (r.ok) { m.className = 'msg ok'; m.textContent = 'Berhasil! Mengalihkan…'; setTimeout(() => location.href = NEXT, 600); }
  else { m.className = 'msg err'; m.textContent = r.msg; }
}
document.addEventListener('keydown', e => { if (e.key === 'Enter') submitAuth(); });
</script>
</body>
</html>
