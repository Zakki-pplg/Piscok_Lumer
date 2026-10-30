<?php
/* ORDER — pilih produk & atur keranjang */
$page = 'order'; $title = 'Order';
include __DIR__ . '/includes/header.php';
?>

<div class="container order-head">
 <h1>🛒 Order Piscok Lumer</h1>
 <p>Pilih rasa favoritmu, atur jumlahnya, lalu lanjut ke checkout.</p>
</div>

<div class="container order-grid">
 <div class="o-products">
  <?php foreach ($products as $p): ?>
  <?php $sold = $p['stock'] !== null && $p['stock'] <= 0; ?>
  <div class="ocard<?= $sold ? ' soldout' : '' ?>">
   <img src="assets/img/<?= $p['img'] ?>" alt="<?= htmlspecialchars($p['name']) ?>" loading="lazy">
   <div class="in">
    <h3><?= $p['name'] ?></h3>
    <div class="pr"><?= rp($p['price']) ?></div>
    <div class="meta"><?= stock_html($p) ?><?= rating_html($p) ?></div>
    <?php if ($sold): ?><span class="btn-soldout">Stok Habis</span>
    <?php else: ?><button class="btn btn-primary" onclick="addToCart(<?= $p['id'] ?>)">+ Tambah</button><?php endif; ?>
   </div>
  </div>
  <?php endforeach; ?>
 </div>

 <aside class="cart-panel">
  <h2>Pesanan Anda</h2>
  <div id="cartList"></div>
  <div class="total"><span>Total</span><span id="cartTotal">Rp 0</span></div>
  <button class="btn btn-light btn-block" onclick="toCheckout()">Lanjut ke Checkout →</button>
 </aside>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
<script>
function renderCart() {
  const ids = Object.keys(cart).filter(id => byId(id));
  $('cartList').innerHTML = ids.length ? ids.map(id => {
    const p = byId(id);
    return `<div class="oitem"><img src="assets/img/${p.img}" alt="">
      <div class="n">${p.name}<small>${rp(p.price)}</small></div>
      <div class="qty"><button onclick="changeQty(${id},-1)">−</button><b>${cart[id]}</b><button onclick="changeQty(${id},1)" ${cart[id] >= maxQty(id) ? 'disabled' : ''}>+</button></div></div>`;
  }).join('') : '<div class="empty">Keranjang masih kosong 🍌<br>Klik <b>+ Tambah</b> pada menu.</div>';
  $('cartTotal').textContent = rp(cartTotal());
}
function toCheckout() {
  if (!Object.keys(cart).length) { toast('Keranjang masih kosong'); return; }
  location.href = 'checkout.php';
}
renderCart();
</script>
</body>
</html>
