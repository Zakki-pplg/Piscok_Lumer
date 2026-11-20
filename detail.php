<?php
/* DETAIL MENU — deskripsi, order + checkout, rating & ulasan (khusus 1 menu)
   Dibuka dari gambar / nama menu:  detail.php?id=<id produk> */
require_once __DIR__ . '/config.php';

$id = (int)($_GET['id'] ?? 0);
$p  = find_product($id);
if (!$p) { header('Location: menu.php'); exit; }

$page = 'detail'; $navActive = 'menu'; $title = $p['name'];
$loginNext = 'detail.php?id=' . $id;
$sold   = $p['stock'] !== null && $p['stock'] <= 0;
$catLbl = $p['cat'] === 'klasik' ? 'Klasik' : 'Spesial';

/* ---- ulasan HANYA untuk menu ini (product_id = $id) ---- */
$reviews = []; $dist = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0]; $mine = null; $reviewErr = false;
try {
    $pdo = db();
    $st = $pdo->prepare('SELECT r.rating, r.comment, r.updated_at, u.name, u.email
                         FROM ratings r JOIN users u ON u.id = r.user_id
                         WHERE r.product_id = ? ORDER BY r.updated_at DESC, r.id DESC LIMIT 100');
    $st->execute([$id]);
    $reviews = $st->fetchAll();
    $st = $pdo->prepare('SELECT rating, COUNT(*) AS c FROM ratings WHERE product_id = ? GROUP BY rating');
    $st->execute([$id]);
    foreach ($st->fetchAll() as $r) if (isset($dist[(int)$r['rating']])) $dist[(int)$r['rating']] = (int)$r['c'];
    if (!empty($user['email'])) {
        $st = $pdo->prepare('SELECT r.rating, r.comment FROM ratings r JOIN users u ON u.id = r.user_id WHERE r.product_id = ? AND u.email = ?');
        $st->execute([$id, $user['email']]);
        $mine = $st->fetch() ?: null;
    }
} catch (Throwable $e) { error_log($e->getMessage()); $reviewErr = true; }
$totalRv = array_sum($dist);

include __DIR__ . '/includes/header.php';
?>

<div class="container detail-wrap">

 <div class="crumbs">
  <a class="btn-back" id="backBtn" href="menu.php"><i class="bx bx-arrow-back"></i> Kembali</a>
  <div class="breadcrumb"><a href="index.php">Beranda</a><i class="bx bx-chevron-right"></i><a href="menu.php">Menu</a><i class="bx bx-chevron-right"></i><span><?= htmlspecialchars($p['name']) ?></span></div>
 </div>

 <!-- ===== Foto + info + panel jumlah ===== -->
 <section class="d-hero">
  <div class="d-left">
   <div class="d-photo<?= $sold ? ' soldout' : '' ?>"><img src="assets/img/<?= $p['img'] ?>" alt="<?= htmlspecialchars($p['name']) ?>"></div>

  </div>

  <div class="d-info">
   <div class="d-tags"><span class="tag-cat"><?= $catLbl ?></span><?= stock_html($p) ?></div>
   <h1><?= htmlspecialchars($p['name']) ?></h1>
   <a class="d-rate" href="#ulasan">
    <?php if ($p['rating_count']): ?>
     <?= stars_html($p['rating']) ?><b><?= number_format($p['rating'], 1) ?></b><span><?= $p['rating_count'] ?> ulasan</span>
    <?php else: ?>
     <?= stars_html(0) ?><span>Belum ada ulasan</span>
    <?php endif; ?>
   </a>
   <div class="d-price"><?= rp($p['price']) ?></div>

   <div class="d-tab"><span>Detail Produk</span></div>
   <dl class="d-spec">
    <div><dt>Kategori</dt><dd><?= $catLbl ?></dd></div>
    <div><dt>Penyajian</dt><dd>Hangat, digoreng saat dipesan</dd></div>
    <div><dt>Harga</dt><dd><?= rp($p['price']) ?> per porsi</dd></div>
   </dl>

   <!-- Deskripsi: teks biasa di kolom tengah (bukan pop up) -->
   <div class="d-about" id="deskripsi">
    <h2 class="d-h">Deskripsi</h2>
    <p class="d-desc"><?= htmlspecialchars($p['detail']) ?></p>
    <ul class="d-points">
     <?php foreach ($p['poin'] as $pt): ?><li><i class="bx bxs-check-circle"></i><?= htmlspecialchars($pt) ?></li><?php endforeach; ?>
    </ul>
   </div>
  </div>

  <aside class="d-order">
   <h2>Atur jumlah</h2>
   <div class="d-mini"><img src="assets/img/<?= $p['img'] ?>" alt=""><div><b><?= htmlspecialchars($p['name']) ?></b><small><?= rp($p['price']) ?></small></div></div>
   <?php if ($sold): ?>
    <span class="btn-soldout btn-block">Stok Habis</span>
   <?php else: ?>
    <div class="qty-row">
     <div class="qty big"><button type="button" id="qMinus" aria-label="Kurangi"><i class="bx bx-minus"></i></button><b id="qVal">1</b><button type="button" id="qPlus" aria-label="Tambah"><i class="bx bx-plus"></i></button></div>
     <?php if ($p['stock'] !== null): ?><span class="qty-stock">Stok: <b><?= $p['stock'] ?></b></span><?php endif; ?>
    </div>
    <div class="d-sub"><span>Subtotal</span><b id="subTotal"><?= rp($p['price']) ?></b></div>
    <button type="button" class="btn btn-primary btn-block" id="btnOpenOrder"><i class="bx bx-shopping-bag"></i> Pesan Sekarang</button>
   <?php endif; ?>
   <a class="btn btn-outline btn-block" target="_blank" rel="noopener" href="https://wa.me/<?= WA_NUMBER ?>?text=<?= rawurlencode('Halo kak, saya mau tanya tentang ' . $p['name']) ?>"><i class="bx bxl-whatsapp"></i> Tanya via WhatsApp</a>
   <ul class="d-notes">
    <li><i class="bx bxs-hot"></i>Digoreng setelah pesanan masuk</li>
    <li><i class="bx bx-package"></i>Gratis ongkir area Ciawi, min. order Rp 50.000</li>
   </ul>
  </aside>
 </section>

 <!-- ===== Form order + checkout (muncul setelah klik "Pesan Sekarang") ===== -->
 <section class="d-checkout" id="orderSec" hidden>
  <div class="co-grid" id="coGrid">
   <div class="co-card">
    <h2>Data Pengiriman</h2>
    <div class="msg" id="coMsg"></div>
    <div class="field"><label for="coNama">Nama Penerima</label><input id="coNama" value="<?= htmlspecialchars($user['name'] ?? '') ?>"></div>
    <div class="field"><label for="coHp">No. HP / WhatsApp</label><input id="coHp" placeholder="08xxxxxxxx" inputmode="numeric" pattern="[0-9]*" maxlength="15" autocomplete="tel-national"></div>
    <div class="field"><label for="coAlamat">Alamat Lengkap</label><textarea id="coAlamat" rows="3" placeholder="Jalan, nomor rumah, RT/RW, kecamatan"></textarea></div>
    <div class="field"><label for="coCatatan">Catatan</label><input id="coCatatan" placeholder="Contoh: cokelat banyak ya"></div>

    <div class="field"><label>Metode Pembayaran</label>
     <div class="paygrid">
      <label><input type="radio" name="bayar" value="COD (Bayar di tempat)" checked> <i class="bx bx-money"></i> COD (Bayar di tempat)</label>
      <label><input type="radio" name="bayar" value="QRIS"> <i class="bx bx-qr-scan"></i> QRIS</label>
     </div>

     <!-- Muncul otomatis saat memilih QRIS -->
     <div class="paybox" id="payBox" hidden>
      <div class="paybox-head"><b>Bayar via QRIS</b><span class="paytotal" id="payTotal">Rp 0</span></div>
      <p class="payhint">Scan kode QR di bawah lewat aplikasi pembayaran Anda.</p>
      <div class="paybody">
       <div class="qris-wrap">
        <img class="qris-img" src="assets/img/qris.jpg" alt="Kode QR pembayaran <?= htmlspecialchars(STORE_NAME) ?>">
        <a class="btn btn-outline btn-sm" href="assets/img/qris.jpg" download="QR-Pembayaran-Tungtung-Piscok-Lumer.jpg"><i class="bx bx-download"></i> Simpan QR</a>
       </div>
       <ol class="paysteps">
        <li>Scan kode QR dan bayar <b>sesuai total</b>.</li>
        <li>Screenshot atau foto bukti pembayaran.</li>
        <li>Upload bukti di bawah, lalu tekan <b>Pesan Sekarang</b>.</li>
       </ol>
      </div>
      <div class="upload">
       <button type="button" class="btn btn-primary btn-block" id="uploadBtn"><i class="bx bx-upload"></i> Upload Bukti Pembayaran</button>
       <input type="file" id="proofFile" accept="image/*" hidden>
       <div class="proof-prev" id="proofPrev" hidden>
        <img id="proofImg" alt="Bukti pembayaran">
        <div><b id="proofName"></b><small id="proofSize"></small><button type="button" class="linkbtn" id="proofClear">Hapus / ganti foto</button></div>
       </div>
      </div>
     </div>
    </div>
   </div>

   <div class="co-card co-summary">
    <h2>Ringkasan Pesanan</h2>
    <div class="line"><img src="assets/img/<?= $p['img'] ?>" alt=""><div><b><?= htmlspecialchars($p['name']) ?></b><br><small id="sumQty"></small></div><b id="sumLine"></b></div>
    <div class="total"><span>Total Bayar</span><span id="sumTotal">Rp 0</span></div>
    <button class="btn btn-primary btn-block" id="btnPesan"><i class="bx bx-send"></i> Pesan Sekarang</button>
    <button type="button" class="btn btn-outline btn-block" style="margin-top:10px" id="btnCloseOrder"><i class="bx bx-arrow-back"></i> Ubah Pesanan</button>
   </div>
  </div>

  <div class="co-card done-box" id="coDone" hidden>
   <div class="ck"><i class="bx bxs-check-circle"></i></div>
   <h2>Pesanan Diterima!</h2>
   <p>Kode pesanan: <b id="doneKode"></b></p>
   <div class="receipt" id="doneReceipt"></div>
   <a class="btn btn-wa" id="doneWA" target="_blank" rel="noopener"><i class="bx bxl-whatsapp"></i> <span id="doneWAtxt">Konfirmasi via WhatsApp</span></a>
   <p id="doneWANote" hidden>
    Di HP/browser yang mendukung berbagi file, pilih WhatsApp dan chat penerima agar foto bukti ikut terlampir. Jika fitur ini tidak tersedia, WhatsApp terbuka dengan pesan saja; bukti tetap tersimpan pada pesanan.
   </p>
   <a class="btn btn-outline" href="menu.php"><i class="bx bx-food-menu"></i> Pesan Menu Lain</a>
  </div>
 </section>

 <!-- ===== Rating & ulasan: khusus <?= htmlspecialchars($p['name']) ?> ===== -->
 <section class="d-reviews" id="ulasan">
  <h2 class="rv-title">Ulasan Pembeli</h2>
  <p class="rv-sub">Penilaian untuk <b><?= htmlspecialchars($p['name']) ?></b></p>

  <div class="rv-summary">
   <div class="rv-score">
    <div class="big"><i class="bx bxs-star"></i><b><?= $p['rating_count'] ? number_format($p['rating'], 1) : '0.0' ?></b><span>/ 5.0</span></div>
    <?= stars_html($p['rating_count'] ? $p['rating'] : 0) ?>
    <small><?= $p['rating_count'] ? $p['rating_count'] . ' ulasan' : 'Belum ada ulasan' ?></small>
   </div>
   <div class="rv-bars">
    <?php for ($i = 5; $i >= 1; $i--): $w = $totalRv ? round($dist[$i] / $totalRv * 100) : 0; ?>
    <div class="rv-bar"><span><i class="bx bxs-star"></i> <?= $i ?></span><div class="track"><i style="width:<?= $w ?>%"></i></div><em><?= $dist[$i] ?></em></div>
    <?php endfor; ?>
   </div>
   <div class="rv-cta">
    <p><?= $mine ? 'Terima kasih sudah memberi ulasan!' : 'Sudah mencoba menu ini?' ?></p>
    <button type="button" class="btn btn-primary" id="btnRate"><i class="bx bx-edit"></i> <?= $mine ? 'Ubah Ulasan Saya' : 'Tulis Ulasan' ?></button>
   </div>
  </div>

  <?php if ($reviewErr): ?>
   <div class="rv-empty"><i class="bx bx-error-circle"></i><p>Ulasan belum dapat dimuat. Pastikan database sudah siap.</p></div>
  <?php elseif (!$reviews): ?>
   <div class="rv-empty"><i class="bx bx-message-square-dots"></i><p>Belum ada ulasan untuk <?= htmlspecialchars($p['name']) ?>. Jadilah yang pertama!</p></div>
  <?php else: ?>
   <div class="rv-filter" id="rvFilter">
    <button type="button" class="chip on" data-star="0">Semua (<?= $totalRv ?>)</button>
    <?php for ($i = 5; $i >= 1; $i--): if (!$dist[$i]) continue; ?>
    <button type="button" class="chip" data-star="<?= $i ?>"><i class="bx bxs-star"></i> <?= $i ?> (<?= $dist[$i] ?>)</button>
    <?php endfor; ?>
   </div>
   <div class="rv-list" id="rvList">
    <?php foreach ($reviews as $r): $isMe = !empty($user['email']) && strtolower($r['email']) === strtolower($user['email']); ?>
    <article class="rv-item" data-star="<?= (int)$r['rating'] ?>">
     <div class="rv-avatar"><i class="bx bxs-user"></i></div>
     <div class="rv-body">
      <div class="rv-head">
       <b><?= $isMe ? htmlspecialchars($r['name']) : htmlspecialchars(mask_name($r['name'])) ?></b>
       <?php if ($isMe): ?><span class="rv-me">Ulasan Anda</span><?php endif; ?>
       <span class="rv-date"><?= tgl_id($r['updated_at']) ?></span>
      </div>
      <?= stars_html((int)$r['rating']) ?>
      <?php if (trim($r['comment']) !== ''): ?><p><?= nl2br(htmlspecialchars($r['comment'])) ?></p><?php endif; ?>
     </div>
    </article>
    <?php endforeach; ?>
   </div>
   <div style="text-align:center;margin-top:18px"><button type="button" class="btn btn-outline" id="rvMore" hidden>Tampilkan lebih banyak</button></div>
  <?php endif; ?>
 </section>
</div>

<!-- modal tulis ulasan -->
<div class="modal" id="rateModal">
 <div class="modal-box">
  <h3>Ulasan: <?= htmlspecialchars($p['name']) ?></h3>
  <div class="stars" id="rateStars"><?php for ($i = 1; $i <= 5; $i++): ?><button type="button" data-v="<?= $i ?>" aria-label="<?= $i ?> bintang"><i class="bx bxs-star"></i></button><?php endfor; ?></div>
  <textarea id="rateComment" rows="3" maxlength="500" placeholder="Tulis komentar (opsional)"></textarea>
  <div class="msg" id="rateMsg"></div>
  <button class="btn btn-primary btn-block" id="rateSend">Kirim Ulasan</button>
  <button class="btn btn-outline btn-block" style="margin-top:8px" id="rateCancel">Batal</button>
 </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
<script>
const PID = <?= $id ?>;
const P = byId(PID);
const MAXQ = (P.stock === null || P.stock === undefined) ? 100 : P.stock;
const MINE = <?= json_encode($mine ? ['rating' => (int)$mine['rating'], 'comment' => $mine['comment']] : null) ?>;
const PAY_QR = ['QRIS'];                       // metode yang menampilkan QR + wajib bukti
let qty = 1, proofFile = null;

/* ---------- tombol kembali: pakai riwayat bila asalnya beranda/menu ---------- */
$('backBtn').addEventListener('click', e => {
  try {
    const r = new URL(document.referrer);
    if (r.origin === location.origin && /(\/|index\.php|menu\.php)$/.test(r.pathname)) { e.preventDefault(); history.back(); }
  } catch (_) {}
});

/* ---------- jumlah ---------- */
function renderQty() {
  if (!$('qVal')) return;
  $('qVal').textContent = qty;
  $('qMinus').disabled = qty <= 1; $('qPlus').disabled = qty >= MAXQ;
  const total = P.price * qty;
  $('subTotal').textContent = rp(total);
  $('sumQty').textContent = qty + ' × ' + rp(P.price);
  $('sumLine').textContent = rp(total);
  $('sumTotal').textContent = rp(total);
  $('payTotal').textContent = rp(total);
}
function setQty(d) {
  if (d > 0 && qty >= MAXQ) { toast('Stok ' + P.name + ' hanya ' + MAXQ, 'bx-error-circle'); return; }
  qty = Math.max(1, qty + d); renderQty();
}
if ($('qVal')) { $('qMinus').onclick = () => setQty(-1); $('qPlus').onclick = () => setQty(1); }

/* ---------- buka / tutup form order ---------- */
function openOrder() {
  $('orderSec').hidden = false; renderQty(); updatePayBox(false);
  $('orderSec').scrollIntoView({ behavior: 'smooth', block: 'start' });
}
function closeOrder() { $('orderSec').hidden = true; window.scrollTo({ top: 0, behavior: 'smooth' }); }
if ($('btnOpenOrder')) $('btnOpenOrder').onclick = openOrder;
$('btnCloseOrder').onclick = closeOrder;

/* ---------- pembayaran ---------- */
const selectedPay = () => document.querySelector('input[name=bayar]:checked').value;
function updatePayBox(scroll) {
  const show = PAY_QR.includes(selectedPay());
  $('payBox').hidden = !show;
  if (show && scroll) $('payBox').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}
document.querySelectorAll('input[name=bayar]').forEach(r => r.addEventListener('change', () => updatePayBox(true)));

/* ---------- upload bukti: kecilkan foto di browser (maks 1600px) ---------- */
function compressImage(file, maxSide = 1600, quality = 0.85) {
  return new Promise(resolve => {
    const url = URL.createObjectURL(file), img = new Image();
    img.onload = () => {
      URL.revokeObjectURL(url);
      const s = Math.min(1, maxSide / Math.max(img.width, img.height));
      const w = Math.round(img.width * s), h = Math.round(img.height * s);
      const c = document.createElement('canvas'); c.width = w; c.height = h;
      const x = c.getContext('2d'); x.fillStyle = '#fff'; x.fillRect(0, 0, w, h); x.drawImage(img, 0, 0, w, h);
      c.toBlob(b => resolve(b && b.size < file.size ? new File([b], 'bukti.jpg', { type: 'image/jpeg' }) : file), 'image/jpeg', quality);
    };
    img.onerror = () => { URL.revokeObjectURL(url); resolve(file); };   // format tak terbaca browser: kirim apa adanya
    img.src = url;
  });
}
const fmtSize = n => n < 1048576 ? Math.max(1, Math.round(n / 1024)) + ' KB' : (n / 1048576).toFixed(1) + ' MB';
function showMsg(t) {
  const m = $('coMsg'); m.className = 'msg err'; m.textContent = t;
  m.scrollIntoView({ behavior: 'smooth', block: 'center' });
}
function clearProof() {
  proofFile = null; $('proofFile').value = '';
  $('proofPrev').hidden = true; $('uploadBtn').hidden = false;
}
$('uploadBtn').onclick = () => $('proofFile').click();
$('proofClear').onclick = clearProof;
$('proofFile').addEventListener('change', async e => {
  const f = e.target.files[0]; if (!f) return;
  if (!f.type.startsWith('image/')) { clearProof(); showMsg('Pilih file foto (JPG, PNG, atau WEBP).'); return; }
  proofFile = await compressImage(f);
  $('proofImg').src = URL.createObjectURL(proofFile);
  $('proofName').textContent = f.name;
  $('proofSize').textContent = fmtSize(proofFile.size);
  $('proofPrev').hidden = false; $('uploadBtn').hidden = true;
  $('coMsg').className = 'msg';
});

async function sendCheckoutOrder(formData) {
  const response = await fetch('api.php?action=checkout', { method: 'POST', body: formData });
  const text = await response.text();
  try { return JSON.parse(text); }
  catch (e) {
    if (!response.ok) throw new Error(`Server gagal memproses pesanan (HTTP ${response.status}). Periksa konfigurasi PHP/API.`);
    throw new Error('Respons server tidak valid. Periksa log PHP/Apache.');
  }
}

/* kirim bukti lewat menu berbagi bila browser mendukung */
$('doneWA').addEventListener('click', async e => {
  if (!proofFile || !navigator.share || !navigator.canShare) return;
  try {
    if (!navigator.canShare({ files: [proofFile] })) return;
    e.preventDefault();
    await navigator.share({ files: [proofFile], title: 'Bukti pembayaran pesanan', text: $('doneWA').dataset.message });
  } catch (error) {
    if (error.name === 'AbortError') return;
    e.preventDefault();
    window.location.assign($('doneWA').href);
  }
});

/* No. HP / WhatsApp: hanya angka (huruf & simbol ditolak, termasuk saat paste) */
(function () {
  const hp = $('coHp'); if (!hp) return;
  hp.addEventListener('keypress', e => { if (e.key && e.key.length === 1 && !/[0-9]/.test(e.key) && !e.ctrlKey && !e.metaKey) e.preventDefault(); });
  hp.addEventListener('input', () => { const v = hp.value.replace(/\D/g, '').slice(0, 15); if (v !== hp.value) hp.value = v; });
})();

async function submitOrder() {
  const bayar = selectedPay(), needProof = PAY_QR.includes(bayar), btn = $('btnPesan');
  if (needProof && !proofFile) { showMsg('Upload foto bukti pembayaran dulu ya.'); return; }

  const data = {
    items: [{ id: PID, qty }],
    nama: $('coNama').value, hp: $('coHp').value.replace(/\D/g, ''), alamat: $('coAlamat').value, bayar, catatan: $('coCatatan').value
  };
  const fd = new FormData();
  fd.append('data', JSON.stringify(data));
  if (needProof) fd.append('proof', proofFile, proofFile.name || 'bukti.jpg');

  btn.disabled = true; btn.innerHTML = '<i class="bx bx-loader-alt bx-spin"></i> Mengirim…';
  let r;
  try { r = await sendCheckoutOrder(fd); }
  catch (e) {
    r = { ok: false, msg: e instanceof TypeError ? 'Tidak dapat terhubung ke server. Periksa koneksi internet atau server, lalu coba lagi.' : e.message };
  }
  btn.disabled = false; btn.innerHTML = '<i class="bx bx-send"></i> Pesan Sekarang';

  if (!r.ok) {
    showMsg(r.msg);
    if (r.stock) setTimeout(() => location.reload(), 2200);
    return;
  }
  const o = r.order;
  $('doneKode').textContent = o.kode;
  $('doneReceipt').innerHTML = o.items.map(i => `${i.qty}× ${i.name} — ${rp(i.sub)}`).join('<br>')
    + `<hr style="margin:8px 0;border:0;border-top:1px dashed #a8683a"><b>Total: ${rp(o.total)}</b><br>Bayar: ${o.bayar}`
    + (o.bukti ? '<br>Bukti pembayaran: <i class="bx bxs-check-circle ok-ic"></i> terkirim' : '') + `<br>Status: ${o.status}`;
  const txt = `Halo kak, saya mau konfirmasi pesanan *${o.kode}*\n`
    + o.items.map(i => `- ${i.qty}x ${i.name}`).join('\n')
    + `\nTotal: ${rp(o.total)}\nNama: ${o.nama}\nAlamat: ${o.alamat}\nPembayaran: ${o.bayar}`
    + (o.bukti ? '\nBukti pembayaran sudah saya upload di website.' : '')
    + (o.catatan ? `\nCatatan: ${o.catatan}` : '');
  $('doneWA').href = 'https://wa.me/' + WA + '?text=' + encodeURIComponent(txt);
  $('doneWA').dataset.message = txt;
  $('doneWANote').hidden = !o.bukti;
  $('doneWAtxt').textContent = o.bukti ? 'Kirim pesanan + bukti ke WhatsApp' : 'Konfirmasi via WhatsApp';
  $('coGrid').hidden = true; $('coDone').hidden = false;
  $('orderSec').scrollIntoView({ behavior: 'smooth', block: 'start' });
}
$('btnPesan').onclick = submitOrder;

/* ---------- ulasan: filter bintang + "tampilkan lebih banyak" ---------- */
const PER_PAGE = 5;
let rvShown = PER_PAGE, rvStar = 0;
function applyReviews() {
  const items = [...document.querySelectorAll('#rvList .rv-item')];
  if (!items.length) return;
  const match = items.filter(i => !rvStar || +i.dataset.star === rvStar);
  items.forEach(i => i.hidden = true);
  match.forEach((i, k) => i.hidden = k >= rvShown);
  $('rvMore').hidden = match.length <= rvShown;
}
document.querySelectorAll('#rvFilter .chip').forEach(c => c.addEventListener('click', () => {
  document.querySelectorAll('#rvFilter .chip').forEach(x => x.classList.remove('on')); c.classList.add('on');
  rvStar = +c.dataset.star; rvShown = PER_PAGE; applyReviews();
}));
if ($('rvMore')) $('rvMore').onclick = () => { rvShown += PER_PAGE; applyReviews(); };
applyReviews();

/* ---------- tulis / ubah ulasan ---------- */
let rateVal = 0;
const paint = v => document.querySelectorAll('#rateStars button').forEach(b => b.classList.toggle('on', +b.dataset.v <= v));
document.querySelectorAll('#rateStars button').forEach(b => b.addEventListener('click', () => { rateVal = +b.dataset.v; paint(rateVal); }));
function openRate() {
  if (!LOGGED) { location.href = 'login.php?next=' + encodeURIComponent('detail.php?id=' + PID); return; }
  rateVal = MINE ? MINE.rating : 0; paint(rateVal);
  $('rateComment').value = MINE ? MINE.comment : '';
  $('rateMsg').className = 'msg'; $('rateMsg').textContent = '';
  $('rateModal').classList.add('show');
}
function closeRate() { $('rateModal').classList.remove('show'); }
$('btnRate').onclick = openRate;
$('rateCancel').onclick = closeRate;
$('rateModal').addEventListener('click', e => { if (e.target === $('rateModal')) closeRate(); });
$('rateSend').onclick = async () => {
  const m = $('rateMsg');
  if (!rateVal) { m.className = 'msg err'; m.textContent = 'Pilih jumlah bintang dulu.'; return; }
  const r = await api('rate', { product_id: PID, rating: rateVal, comment: $('rateComment').value });
  if (!r.ok) {
    if (r.login) { location.href = 'login.php?next=' + encodeURIComponent('detail.php?id=' + PID); return; }
    m.className = 'msg err'; m.textContent = r.msg; return;
  }
  history.replaceState(null, '', '#ulasan');
  location.reload();
};

renderQty();
</script>
</body>
</html>
