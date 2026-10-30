<?php
/* KONTAK */
$page = 'kontak'; $title = 'Kontak';
include __DIR__ . '/includes/header.php';
?>

<div class="container contact">
 <div class="c-left">
  <h1>Hubungi Kami</h1>
  <p>Ada pertanyaan, pesanan dalam jumlah besar, atau ingin kerja sama? Chat langsung lewat WhatsApp, kami balas cepat!</p>
  <div class="c-item"><span>📞</span><div><b>WhatsApp</b><br>+<?= WA_NUMBER ?></div></div>
  <div class="c-item"><span>📍</span><div><b>Lokasi</b><br>Ciawi, Kab.Bogor, Jawa Barat</div></div>
  <div class="c-item"><span>🕘</span><div><b>Jam Buka</b><br>Setiap hari, 09.00 – 21.00 WIB</div></div>
  <a class="btn btn-wa" target="_blank" rel="noopener" href="https://wa.me/<?= WA_NUMBER ?>?text=Halo%20kak%2C%20saya%20mau%20tanya%20piscok%20lumer">💬 Chat via WhatsApp</a>
 </div>
 <div class="c-right">
  <div class="form-card">
   <h2>Kirim Pesan</h2>
   <div class="field"><label>Nama</label><input id="kNama" placeholder="Nama Anda"></div>
   <div class="field"><label>Topik</label><select id="kTopik"><option>Tanya menu</option><option>Pesanan banyak / acara</option><option>Kerja sama</option><option>Saran &amp; kritik</option></select></div>
   <div class="field"><label>Pesan</label><textarea id="kPesan" rows="4" placeholder="Tulis pesan Anda..."></textarea></div>
   <button class="btn btn-wa btn-block" onclick="kirimWA()">Kirim lewat WhatsApp</button>
  </div>
 </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
<script>
function kirimWA() {
  const t = 'Halo, saya ' + ($('kNama').value || '-') + '\nTopik: ' + $('kTopik').value + '\nPesan: ' + $('kPesan').value;
  window.open('https://wa.me/' + WA + '?text=' + encodeURIComponent(t), '_blank');
}
</script>
</body>
</html>
