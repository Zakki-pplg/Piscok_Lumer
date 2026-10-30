<?php
/* MENU */
$page = 'menu'; $title = 'Menu';
include __DIR__ . '/includes/header.php';
?>

<div class="menu-head"><div class="container"><h1>🍌 Daftar Menu</h1><p>Pilih piscok lumer favoritmu, lalu klik <b>Tambah</b> untuk masuk keranjang. Masuk ke akunmu untuk memberi rating.</p></div></div>
<div class="container">
 <div class="chips">
  <button class="chip on" data-cat="all">Semua</button>
  <button class="chip" data-cat="klasik">Klasik</button>
  <button class="chip" data-cat="spesial">Spesial</button>
 </div>
 <div class="menu-list">
  <?php foreach ($products as $p): ?>
  <div class="mrow<?= ($p['stock'] !== null && $p['stock'] <= 0) ? ' soldout' : '' ?>" data-cat="<?= $p['cat'] ?>">
   <img src="assets/img/<?= $p['img'] ?>" alt="<?= htmlspecialchars($p['name']) ?>" loading="lazy">
   <div class="info"><h3><?= $p['name'] ?></h3><p><?= $p['desc'] ?></p>
    <div class="pr"><?= rp($p['price']) ?></div>
    <div class="meta"><?= stock_html($p) ?><?= rating_html($p) ?><button class="rate-btn" onclick="openRate(<?= $p['id'] ?>)">⭐ Beri rating</button></div></div>
   <?php if ($p['stock'] !== null && $p['stock'] <= 0): ?>
   <span class="btn-soldout">Stok Habis</span>
   <?php else: ?>
   <button class="btn btn-primary" onclick="addToCart(<?= $p['id'] ?>)">+ Tambah</button>
   <?php endif; ?>
  </div>
  <?php endforeach; ?>
 </div>
 <div style="text-align:center;padding-bottom:80px"><a class="btn btn-outline" href="order.php">Lihat Keranjang &amp; Order →</a></div>
</div>

<div class="modal" id="rateModal" onclick="if(event.target===this)closeRate()">
 <div class="modal-box">
  <h3 id="rateTitle">Beri Rating</h3>
  <div class="stars" id="rateStars"><?php for ($i = 1; $i <= 5; $i++): ?><button type="button" data-v="<?= $i ?>" aria-label="<?= $i ?> bintang">★</button><?php endfor; ?></div>
  <textarea id="rateComment" rows="3" maxlength="500" placeholder="Tulis komentar (opsional)"></textarea>
  <div class="msg" id="rateMsg"></div>
  <button class="btn btn-primary btn-block" onclick="submitRate()">Kirim Rating</button>
  <button class="btn btn-outline btn-block" style="margin-top:8px" onclick="closeRate()">Batal</button>
 </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
<script>
/* ---- rating ---- */
let ratePid = 0, rateVal = 0;
const paint = v => document.querySelectorAll('#rateStars button').forEach(b => b.classList.toggle('on', +b.dataset.v <= v));
document.querySelectorAll('#rateStars button').forEach(b => b.addEventListener('click', () => { rateVal = +b.dataset.v; paint(rateVal); }));
function openRate(id) {
  if (!LOGGED) { location.href = 'login.php?next=menu.php'; return; }
  ratePid = id; rateVal = 0; paint(0);
  $('rateTitle').textContent = 'Rating: ' + byId(id).name;
  $('rateComment').value = ''; $('rateMsg').className = 'msg'; $('rateMsg').textContent = '';
  $('rateModal').classList.add('show');
}
function closeRate() { $('rateModal').classList.remove('show'); }
async function submitRate() {
  const m = $('rateMsg');
  if (!rateVal) { m.className = 'msg err'; m.textContent = 'Pilih jumlah bintang dulu.'; return; }
  const r = await api('rate', { product_id: ratePid, rating: rateVal, comment: $('rateComment').value });
  if (!r.ok) { if (r.login) { location.href = 'login.php?next=menu.php'; return; } m.className = 'msg err'; m.textContent = r.msg; return; }
  const el = document.querySelector('.rate-avg[data-pid="' + ratePid + '"]');
  if (el) { el.classList.remove('none'); el.innerHTML = '★ ' + r.rating.toFixed(1) + ' <small>(' + r.count + ')</small>'; }
  closeRate(); toast('✓ Terima kasih atas ratingnya!');
}

document.querySelectorAll('.chip').forEach(c => c.addEventListener('click', () => {
  document.querySelectorAll('.chip').forEach(x => x.classList.remove('on')); c.classList.add('on');
  document.querySelectorAll('.mrow').forEach(r => r.style.display = (c.dataset.cat == 'all' || r.dataset.cat == c.dataset.cat) ? 'flex' : 'none');
}));
</script>
</body>
</html>
