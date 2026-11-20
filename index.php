<?php
/* BERANDA */
$page = 'beranda'; $title = 'Beranda';
include __DIR__ . '/includes/header.php';
?>

<div class="dark-zone">
<div class="container banner-wrap">
 <div class="banner-track" id="track">
  <div class="banner b1">
   <div><span class="tag"><i class="bx bxs-hot"></i> PROMO GRAND OPENING</span><h2>Diskon 20% Piscok Cokelat Lumer!</h2><p>Cokelat meleleh di setiap gigitan. Pesan sekarang dan nikmati kehangatannya.</p><a class="btn btn-light" href="detail.php?id=2">Pesan Sekarang</a></div>
   <img class="pic" src="assets/img/piscok-cokelat.jpg" alt="Piscok cokelat lumer">
  </div>
  <div class="banner b2">
   <div><span class="tag"><i class="bx bx-gift"></i> PAKET HEMAT</span><h2>Beli 10 Gratis 2 Piscok Oreo!</h2><p>Cocok untuk arisan, ulang tahun, dan kumpul keluarga. Stok terbatas minggu ini.</p><a class="btn btn-primary" href="detail.php?id=4">Ambil Promo</a></div>
   <img class="pic" src="assets/img/piscok-oreo.jpg" alt="Piscok oreo">
  </div>
  <div class="banner b3">
   <div><span class="tag"><i class="bx bx-package"></i> GRATIS ONGKIR</span><h2>Gratis Ongkir Area Ciawi!</h2><p>Minimal order Rp 50.000, piscok lumer diantar masih hangat ke depan pintu Anda.</p><a class="btn btn-wa" target="_blank" rel="noopener" href="https://wa.me/<?= WA_NUMBER ?>?text=Halo%20kak%2C%20saya%20mau%20order%20piscok%20lumer"><i class="bx bxl-whatsapp"></i> Chat WhatsApp</a></div>
   <img class="pic" src="assets/img/piscok-whitechoco.jpg" alt="Piscok white choco">
  </div>
 </div>
 <div class="dots" id="dots"><span class="on"></span><span></span><span></span></div>
</div>

<div class="container hero-txt">
 <h1>Piscok <em>Lumer</em> Paling Meleleh di Kota</h1>
 <p>Pisang pilihan, cokelat premium, dan kulit renyah emas. Digoreng saat dipesan, disajikan hangat.</p>
 <a href="menu.php" class="btn btn-primary">Lihat Menu</a>
</div>
</div><!-- /dark-zone -->

<section class="section">
 <div class="container">
  <div class="section-title"><h2>Kenapa Memilih Kami?</h2><p>Dibuat dengan cinta dan cokelat berkualitas</p></div>
  <div class="feat">
   <div><div class="ic"><i class="bx bxs-cookie"></i></div><h3>Cokelat Premium</h3><p>Lelehan cokelat asli yang kaya rasa.</p></div>
   <div><div class="ic"><i class="bx bxs-hot"></i></div><h3>Selalu Hangat</h3><p>Digoreng setelah pesanan masuk.</p></div>
   <div><div class="ic"><i class="bx bx-timer"></i></div><h3>Antar Cepat</h3><p>Pengiriman cepat ke rumah Anda.</p></div>
   <div><div class="ic"><i class="bx bx-purchase-tag"></i></div><h3>Harga Bersahabat</h3><p>Mulai <?= rp($products[0]['price']) ?> saja per porsi.</p></div>
  </div>
 </div>
</section>

<section class="section" style="padding-top:0">
 <div class="container">
  <div class="section-title"><h2>Menu Favorit</h2><p>Paling sering dipesan pelanggan</p></div>
  <div class="best-grid">
   <?php foreach (array_slice($products, 1, 3) as $p): ?>
   <?php $sold = $p['stock'] !== null && $p['stock'] <= 0; $url = 'detail.php?id=' . $p['id']; ?>
   <div class="bcard<?= $sold ? ' soldout' : '' ?>">
    <a href="<?= $url ?>" class="bimg" aria-label="Lihat detail <?= htmlspecialchars($p['name']) ?>"><img src="assets/img/<?= $p['img'] ?>" alt="<?= htmlspecialchars($p['name']) ?>"></a>
    <div class="in"><h3><a href="<?= $url ?>"><?= $p['name'] ?></a></h3><div class="pr"><?= rp($p['price']) ?></div>
    <div class="meta"><?= stock_html($p) ?><?= rating_html($p) ?></div></div>
   </div>
   <?php endforeach; ?>
  </div>
  <div style="text-align:center;margin-top:30px"><a class="btn btn-outline" href="menu.php">Lihat Semua Menu <i class="bx bx-right-arrow-alt"></i></a></div>
 </div>
</section>

<?php $best = top_rated(3); ?>
<section class="section top-rated" style="padding-top:10px">
 <div class="container">
  <div class="section-title"><h2>Rating Terbaik</h2><p>Menu dengan nilai tertinggi dari para pembeli</p></div>
  <?php if ($best): ?>
  <div class="rank-grid">
   <?php foreach ($best as $i => $p): $url = 'detail.php?id=' . $p['id']; ?>
   <div class="rcard rank-<?= $i + 1 ?>">
    <span class="rank-badge"><i class="bx bxs-trophy"></i> #<?= $i + 1 ?></span>
    <a href="<?= $url ?>" class="rimg" aria-label="Lihat detail <?= htmlspecialchars($p['name']) ?>"><img src="assets/img/<?= $p['img'] ?>" alt="<?= htmlspecialchars($p['name']) ?>" loading="lazy"></a>
    <div class="rin">
     <h3><a href="<?= $url ?>"><?= $p['name'] ?></a></h3>
     <div class="rscore"><?= stars_html($p['rating']) ?><b><?= number_format($p['rating'], 1) ?></b><small>(<?= $p['rating_count'] ?> ulasan)</small></div>
     <div class="pr"><?= rp($p['price']) ?></div>
    </div>
   </div>
   <?php endforeach; ?>
  </div>
  <?php else: ?>
  <div class="rank-empty"><i class="bx bx-star"></i><p>Belum ada rating. Jadilah yang pertama memberi ulasan di halaman detail menu.</p><a class="btn btn-outline" href="menu.php">Lihat Menu</a></div>
  <?php endif; ?>
 </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
<script>
/* Banner promo: 3 slide, auto-scroll horizontal */
const track = $('track'), dots = [...document.querySelectorAll('#dots span')];
let cur = 0, timer;
const go = n => { cur = (n + 3) % 3; track.scrollTo({ left: track.clientWidth * cur, behavior: 'smooth' }); dots.forEach((d, k) => d.classList.toggle('on', k == cur)); };
const start = () => { clearInterval(timer); timer = setInterval(() => go(cur + 1), 3500); };
const stop = () => clearInterval(timer);
dots.forEach((d, k) => d.addEventListener('click', () => { go(k); start(); }));
track.addEventListener('mouseenter', stop); track.addEventListener('mouseleave', start);
track.addEventListener('touchstart', stop, { passive: true }); track.addEventListener('touchend', start);
track.addEventListener('scroll', () => { const n = Math.round(track.scrollLeft / track.clientWidth); cur = n; dots.forEach((d, k) => d.classList.toggle('on', k == n)); });
start();
</script>
</body>
</html>
