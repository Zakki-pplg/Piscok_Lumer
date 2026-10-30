<?php
/* TENTANG */
$page = 'tentang'; $title = 'Tentang Kami';
include __DIR__ . '/includes/header.php';
?>

<div class="container">
 <div class="about-hero">
  <div>
   <h1>Cerita Di Balik Lumernya Cokelat</h1>
   <p>Berawal dari dapur rumahan, kami ingin menghadirkan camilan pisang cokelat yang benar-benar lumer, renyah di luar dan lembut di dalam. Dari rasa original sampai oreo, green tea, dan taro, kini ribuan porsi telah memanjakan lidah pelanggan setia kami.</p><br>
   <a href="menu.php" class="btn btn-light">Cicipi Sekarang</a>
  </div>
  <div class="about-art"><img src="assets/img/logo.png" alt="Logo <?= STORE_NAME ?>"></div>
 </div>
 <div class="section-title" style="color:var(--gold)"><h2>Proses Pembuatan</h2></div>
 <div class="steps">
  <div class="step"><b>1</b><h3>Pilih Pisang</h3><p>Pisang matang sempurna, manis alami.</p></div>
  <div class="step"><b>2</b><h3>Isi Cokelat</h3><p>Cokelat premium dibungkus kulit lumpia tipis.</p></div>
  <div class="step"><b>3</b><h3>Goreng Emas</h3><p>Digoreng saat dipesan hingga garing keemasan.</p></div>
  <div class="step"><b>4</b><h3>Sajikan Hangat</h3><p>Dikemas rapi, langsung diantar ke Anda.</p></div>
 </div>
 <div class="stat">
  <div><strong>5.000+</strong>Pelanggan Puas</div>
  <div><strong><?= count($products) ?></strong>Varian Rasa</div>
  <div><strong>4.9★</strong>Rating Rata-rata</div>
  <div><strong>100%</strong>Bahan Segar</div>
 </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
