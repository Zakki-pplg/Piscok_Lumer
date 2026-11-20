<footer>
 <div class="foot-inner">
  <div class="foot-brand">
   <img src="assets/img/logo.png" alt="Logo <?= STORE_NAME ?>">
   <p><?= STORE_NAME ?> menyajikan piscok yang digoreng saat dipesan, dengan cokelat lumer dan kulit renyah.<br>Kami terus menjaga kualitas bahan, kebersihan, dan pengantaran agar piscok sampai dalam keadaan hangat.</p>
   <div class="foot-pay" aria-label="Metode pembayaran">
    <span><i class="bx bx-qr-scan"></i> QRIS</span>
    <span><i class="bx bx-money"></i> COD</span>
    <em>dll</em>
   </div>
  </div>
  <div class="foot-col">
   <h4>Menu</h4>
   <?php foreach ($products as $fp): ?><a href="detail.php?id=<?= $fp['id'] ?>"><?= htmlspecialchars($fp['name']) ?></a><?php endforeach; ?>
  </div>
  <div class="foot-col">
   <h4>Informasi</h4>
   <a href="index.php">Beranda</a>
   <a href="menu.php">Daftar Menu</a>
   <a href="tentang.php">Tentang Kami</a>
   <a href="kontak.php">Hubungi Kami</a>
   <a href="login.php">Masuk / Daftar</a>
   <h4 class="h-sub">Bantuan</h4>
   <a href="https://wa.me/<?= WA_NUMBER ?>" target="_blank" rel="noopener">WhatsApp +<?= WA_NUMBER ?></a>
   <a href="<?= IG_URL ?>" target="_blank" rel="noopener">Instagram @<?= IG_USER ?></a>
  </div>
  <div class="foot-col">
   <h4>Toko</h4>
   <span>Ciawi, Kab. Bogor, Jawa Barat</span>
   <span>Setiap hari, 09.00 – 21.00 WIB</span>
   <span>Gratis ongkir area Ciawi, min. order Rp 50.000</span>
   <h4 class="h-sub">Tim kami</h4>
   <?php foreach (TEAM as [$nm, $role]): ?><span class="who"><?= htmlspecialchars($nm) ?><small><?= htmlspecialchars($role) ?></small></span><?php endforeach; ?>
  </div>
 </div>
 <div class="foot-copy">© <?= date('Y') ?> <?= STORE_NAME ?>. Seluruh hak cipta dilindungi.</div>
</footer>

<a class="wa-float" target="_blank" rel="noopener" href="https://wa.me/<?= WA_NUMBER ?>?text=Halo%20kak%2C%20saya%20mau%20order%20piscok%20lumer" title="Chat WhatsApp" aria-label="Chat WhatsApp"><i class="bx bxl-whatsapp"></i></a>
<div class="toast" id="toast"></div>

<script>
const PRODUCTS = <?= json_encode($products, JSON_UNESCAPED_UNICODE) ?>;
const WA = '<?= WA_NUMBER ?>';
const LOGGED = <?= $user ? 'true' : 'false' ?>;
</script>
<script src="assets/app.js"></script>
