<?php
/* MENU — daftar menu; gambar & nama produk membuka halaman detail */
$page = 'menu'; $title = 'Menu';
include __DIR__ . '/includes/header.php';
?>

<div class="menu-head"><div class="container"><h1><i class="bx bx-food-menu"></i> Daftar Menu</h1><p>Pilih piscok lumer favoritmu, lalu klik gambar atau nama menu untuk membaca deskripsi, melihat ulasan, dan langsung memesan.</p></div></div>
<div class="container">
 <div class="chips">
  <button class="chip on" data-cat="all">Semua</button>
  <button class="chip" data-cat="klasik">Klasik</button>
  <button class="chip" data-cat="spesial">Spesial</button>
 </div>
 <div class="menu-list">
  <?php foreach ($products as $p): ?>
  <?php $sold = $p['stock'] !== null && $p['stock'] <= 0; $url = 'detail.php?id=' . $p['id']; ?>
  <article class="mcard<?= $sold ? ' soldout' : '' ?>" data-cat="<?= $p['cat'] ?>">
   <a href="<?= $url ?>" class="mc-img" aria-label="Lihat detail <?= htmlspecialchars($p['name']) ?>">
    <img src="assets/img/<?= $p['img'] ?>" alt="<?= htmlspecialchars($p['name']) ?>" loading="lazy">
    <span class="mc-cat"><?= $p['cat'] === 'klasik' ? 'Klasik' : 'Spesial' ?></span>
    <?php if ($sold): ?><span class="mc-out">Stok Habis</span><?php endif; ?>
   </a>
   <div class="mc-body">
    <h3><a href="<?= $url ?>"><?= htmlspecialchars($p['name']) ?></a></h3>
    <p class="mc-desc"><?= htmlspecialchars($p['desc']) ?></p>
    <div class="mc-meta"><?= rating_html($p) ?></div>
    <div class="mc-meta mc-stat"><?= sold_html($p) ?><?= stock_html($p) ?></div>
    <div class="mc-price"><?= rp($p['price']) ?></div>
    <?php if ($sold): ?>
    <span class="btn-soldout btn-block">Stok Habis</span>
    <?php else: ?>
    <a class="btn btn-primary btn-block" href="<?= $url ?>"><i class="bx bx-shopping-bag"></i> Pesan</a>
    <?php endif; ?>
   </div>
  </article>
  <?php endforeach; ?>
 </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
<script>
document.querySelectorAll('.chip').forEach(c => c.addEventListener('click', () => {
  document.querySelectorAll('.chip').forEach(x => x.classList.remove('on')); c.classList.add('on');
  document.querySelectorAll('.mcard').forEach(r => r.style.display = (c.dataset.cat == 'all' || r.dataset.cat == c.dataset.cat) ? 'flex' : 'none');
}));
</script>
</body>
</html>
