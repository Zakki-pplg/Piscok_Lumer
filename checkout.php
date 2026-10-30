<?php
/* CHECKOUT — wajib login */
require_once __DIR__ . '/config.php';
$page = 'checkout'; $title = 'Checkout';
include __DIR__ . '/includes/header.php';
?>

<div class="co-head">
 <h1>Checkout 📦</h1>
 <div class="steps-bar"><span>1. Pilih Menu</span><span class="on" id="stForm">2. Data Pengiriman</span><span id="stDone">3. Selesai</span></div>
</div>

<div class="container">
 <div class="co-grid" id="coGrid">
  <div class="co-card">
   <h2>Data Pengiriman</h2>
   <div class="msg" id="coMsg"></div>
  <div class="field"><label>Nama Penerima</label><input id="coNama" value="<?= htmlspecialchars($user['name'] ?? '') ?>"></div>
   <div class="field"><label>No. HP / WhatsApp</label><input id="coHp" placeholder="08xxxxxxxx" inputmode="tel"></div>
   <div class="field"><label>Alamat Lengkap</label><textarea id="coAlamat" rows="3" placeholder="Jalan, nomor rumah, RT/RW, kecamatan"></textarea></div>
   <div class="field"><label>Catatan</label><input id="coCatatan" placeholder="Contoh: cokelat banyak ya"></div>

   <div class="field"><label>Metode Pembayaran</label>
    <div class="paygrid">
     <label><input type="radio" name="bayar" value="COD (Bayar di tempat)" checked> 💵 COD</label>
     <label><input type="radio" name="bayar" value="Transfer Bank"> 🏦 Transfer</label>
     <label><input type="radio" name="bayar" value="QRIS"> 📱 QRIS</label>
     <label><input type="radio" name="bayar" value="E-Wallet"> 👛 E-Wallet</label>
    </div>

    <!-- Muncul otomatis saat memilih Transfer / QRIS / E-Wallet -->
    <div class="paybox" id="payBox" hidden>
     <div class="paybox-head"><b id="payTitle">Bayar via QRIS</b><span class="paytotal" id="payTotal">Rp 0</span></div>
     <p class="payhint" id="payHint"></p>
     <div class="paybody">
      <div class="qris-wrap">
       <img class="qris-img" src="assets/img/qris.jpg" alt="Kode QR pembayaran <?= htmlspecialchars(STORE_NAME) ?>">
       <a class="btn btn-outline btn-sm" href="assets/img/qris.jpg" download="QR-Pembayaran-Tungtung-Piscok-Lumer.jpg">⬇ Simpan QR</a>
      </div>
      <ol class="paysteps">
       <li>Scan kode QR dan bayar <b>sesuai total</b>.</li>
       <li>Screenshot atau foto bukti pembayaran.</li>
       <li>Upload bukti di bawah, lalu tekan <b>Pesan Sekarang</b>.</li>
      </ol>
     </div>
     <div class="upload">
      <button type="button" class="btn btn-primary btn-block" id="uploadBtn" onclick="$('proofFile').click()">📤 Upload Bukti Pembayaran</button>
      <input type="file" id="proofFile" accept="image/*" hidden>
      <div class="proof-prev" id="proofPrev" hidden>
       <img id="proofImg" alt="Bukti pembayaran">
       <div><b id="proofName"></b><small id="proofSize"></small><button type="button" class="linkbtn" onclick="clearProof()">Hapus / ganti foto</button></div>
      </div>
     </div>
    </div>
   </div>
  </div>

  <div class="co-card co-summary">
   <h2>Ringkasan Pesanan</h2>
   <div id="sumList"></div>
   <div class="total"><span>Total Bayar</span><span id="sumTotal">Rp 0</span></div>
   <button class="btn btn-primary btn-block" id="btnPesan" onclick="submitOrder()">Pesan Sekarang</button>
   <a class="btn btn-outline btn-block" style="margin-top:10px" href="order.php">← Ubah Pesanan</a>
  </div>
 </div>

 <div class="co-card done-box" id="coDone" style="display:none;margin:34px 0 80px">
  <div class="ck">✅</div>
  <h2>Pesanan Diterima!</h2>
  <p>Kode pesanan: <b id="doneKode"></b></p>
  <div class="receipt" id="doneReceipt"></div>
  <a class="btn btn-wa" id="doneWA" target="_blank" rel="noopener">💬 Konfirmasi via WhatsApp</a>
  <p id="doneWANote" style="display:none;max-width:480px;margin:12px auto;font-size:.85rem;opacity:.8">
   Di HP/browser yang mendukung berbagi file, pilih WhatsApp dan chat penerima agar foto bukti ikut terlampir. Jika fitur ini tidak tersedia, WhatsApp terbuka dengan pesan saja; bukti tetap tersimpan pada pesanan.
  </p>
  <a class="btn btn-outline" href="menu.php">Pesan Lagi</a>
 </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
<script>
const PAY_QR = ['Transfer Bank', 'QRIS', 'E-Wallet'];     // metode yang menampilkan QR + wajib bukti
const PAY_HINT = {
  'Transfer Bank': 'Buka m-banking Anda, pilih fitur scan / transfer QR, lalu scan kode di bawah.',
  'QRIS': 'Scan kode QR di bawah lewat aplikasi pembayaran Anda.',
  'E-Wallet': 'Buka aplikasi e-wallet Anda, pilih Scan / Bayar, lalu scan kode di bawah.'
};
let proofFile = null;

const selectedPay = () => document.querySelector('input[name=bayar]:checked').value;

function renderSummary() {
  const ids = Object.keys(cart).filter(id => byId(id));
  $('sumList').innerHTML = ids.length ? ids.map(id => {
    const p = byId(id);
    return `<div class="line"><img src="assets/img/${p.img}" alt=""><div><b>${p.name}</b><br><small>${cart[id]} × ${rp(p.price)}</small></div><b>${rp(p.price * cart[id])}</b></div>`;
  }).join('') : '<p style="text-align:center;padding:16px;opacity:.7">Keranjang kosong. <a href="order.php" style="color:var(--caramel);font-weight:600">Pilih menu dulu</a></p>';
  $('sumTotal').textContent = rp(cartTotal());
  updatePayBox(false);
}

/* tampilkan / sembunyikan panel QR sesuai metode yang dipilih */
function updatePayBox(scroll) {
  const v = selectedPay(), show = PAY_QR.includes(v);
  $('payBox').hidden = !show;
  if (show) {
    $('payTitle').textContent = 'Bayar via ' + v;
    $('payHint').textContent = PAY_HINT[v];
    $('payTotal').textContent = rp(cartTotal());
    if (scroll) $('payBox').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
  }
}
document.querySelectorAll('input[name=bayar]').forEach(r => r.addEventListener('change', () => updatePayBox(true)));

/* ---- upload bukti: kecilkan foto di browser (maks 1600px) agar cepat & tidak kena batas ukuran server ---- */
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
async function sendCheckoutOrder(formData) {
  const response = await fetch('api.php?action=checkout', { method: 'POST', body: formData });
  const text = await response.text();
  try {
    return JSON.parse(text);
  } catch (e) {
    if (!response.ok) throw new Error(`Server gagal memproses pesanan (HTTP ${response.status}). Periksa konfigurasi PHP/API.`);
    throw new Error('Respons server tidak valid. Periksa log PHP/Apache.');
  }
}
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

$('doneWA').addEventListener('click', async e => {
  if (!proofFile || !navigator.share || !navigator.canShare) return;
  try {
    if (!navigator.canShare({ files: [proofFile] })) return;
    e.preventDefault();
    await navigator.share({
      files: [proofFile],
      title: 'Bukti pembayaran pesanan',
      text: $('doneWA').dataset.message
    });
  } catch (error) {
    if (error.name === 'AbortError') return;
    e.preventDefault();
    window.location.assign($('doneWA').href);
  }
});

async function submitOrder() {
  const bayar = selectedPay(), needProof = PAY_QR.includes(bayar), m = $('coMsg'), btn = $('btnPesan');
  if (needProof && !proofFile) { showMsg('Upload foto bukti pembayaran dulu ya.'); return; }

  const data = {
    items: Object.entries(cart).map(([id, qty]) => ({ id: +id, qty })),
    nama: $('coNama').value, hp: $('coHp').value, alamat: $('coAlamat').value, bayar, catatan: $('coCatatan').value
  };
  const fd = new FormData();
  fd.append('data', JSON.stringify(data));
  if (needProof) fd.append('proof', proofFile, proofFile.name || 'bukti.jpg');

  btn.disabled = true; btn.textContent = 'Mengirim…';
  let r;
  try {
    r = await sendCheckoutOrder(fd);
  } catch (e) {
    r = {
      ok: false,
      msg: e instanceof TypeError
        ? 'Tidak dapat terhubung ke server. Periksa koneksi internet atau server, lalu coba lagi.'
        : e.message
    };
  }
  btn.disabled = false; btn.textContent = 'Pesan Sekarang';

  if (!r.ok) {
    showMsg(r.msg);
    if (r.login) location.href = 'login.php?next=checkout.php';
    if (r.stock) setTimeout(() => location.href = 'order.php', 2200);
    return;
  }
  const o = r.order;
  $('doneKode').textContent = o.kode;
  $('doneReceipt').innerHTML = o.items.map(i => `${i.qty}× ${i.name} — ${rp(i.sub)}`).join('<br>')
    + `<hr style="margin:8px 0;border:0;border-top:1px dashed #a8683a"><b>Total: ${rp(o.total)}</b><br>Bayar: ${o.bayar}`
    + (o.bukti ? '<br>Bukti pembayaran: ✅ terkirim' : '') + `<br>Status: ${o.status}`;
  const txt = `Halo kak, saya mau konfirmasi pesanan *${o.kode}*\n`
    + o.items.map(i => `- ${i.qty}x ${i.name}`).join('\n')
    + `\nTotal: ${rp(o.total)}\nNama: ${o.nama}\nAlamat: ${o.alamat}\nPembayaran: ${o.bayar}`
    + (o.bukti ? '\nBukti pembayaran sudah saya upload di website.' : '')
    + (o.catatan ? `\nCatatan: ${o.catatan}` : '');
  $('doneWA').href = 'https://wa.me/' + WA + '?text=' + encodeURIComponent(txt);
  $('doneWA').dataset.message = txt;
  $('doneWANote').style.display = o.bukti ? 'block' : 'none';
  $('doneWA').textContent = o.bukti
    ? '💬 Kirim pesanan + bukti ke WhatsApp'
    : '💬 Konfirmasi via WhatsApp';
  $('coGrid').style.display = 'none'; $('coDone').style.display = 'block';
  $('stForm').classList.remove('on'); $('stDone').classList.add('on');
  cart = {}; saveCart(); window.scrollTo({ top: 0, behavior: 'smooth' });
}
renderSummary();
</script>
</body>
</html>
