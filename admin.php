<?php
/* =====================================================
   ADMIN.PHP — halaman admin: ringkasan penjualan, pesanan & bukti bayar, stok menu
   Buka lewat:  alamat-website/admin.php
   Hanya akun Developer dan Admin (lihat ADMIN_ACCOUNTS di config.php) yang bisa masuk.
   ===================================================== */
require __DIR__ . '/config.php';
header('Cache-Control: no-store');
$admin = is_admin();
if ($admin && empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));

/* data isi-otomatis form login (hanya bila diizinkan oleh ADMIN_AUTOFILL di config.php) */
$fill = [];
if (!$admin && admin_autofill_on())
    foreach (ADMIN_ACCOUNTS as $u => $a) $fill[$u] = ['user' => $u, 'pass' => ADMIN_AUTOFILL_PASS[$u] ?? ''];
$defUser = isset($fill['admin']) ? 'admin' : '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>Admin — <?= STORE_NAME ?></title>
<link rel="icon" type="image/png" href="assets/img/logo.png">
<link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,600;12..96,800&family=Figtree:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@2.1.4/css/boxicons.min.css">
<link rel="stylesheet" href="assets/admin.css">
</head>
<body class="p-admin">

<?php if (!$admin): ?>
<!-- ================= LOGIN ================= -->
<div class="lg">
 <div class="lg-side">
  <div class="lg-brand"><img src="assets/img/logo.png" alt="Logo"> <?= STORE_NAME ?></div>
  <div>
   <h2>Pantau penjualan piscok dari satu layar.</h2>
   <p>Lihat omzet, menu terlaris, pesanan yang perlu diverifikasi, dan atur stok menu.</p>
  </div>
 </div>
 <div class="lg-form">
  <form class="lg-card" id="lgForm" onsubmit="return adminLogin(event)">
   <h1>Masuk panel admin</h1>
   <p>Khusus Developer dan Admin.</p>
   <div class="seg" role="group" aria-label="Masuk sebagai">
    <button type="button" id="rAdmin" class="on" onclick="pickRole('admin')">Admin<small>Muhammad Alfan</small></button>
    <button type="button" id="rDev" onclick="pickRole('developer')">Developer<small>Sulthan Fakhri</small></button>
   </div>
   <div class="msg" id="lgMsg" role="alert"></div>
   <?php if ($fill): ?><div class="note"><i class="bx bx-check-circle"></i><span>Username dan password terisi otomatis. Tekan <b>Masuk</b>.</span></div><?php endif; ?>
   <div class="fld"><label for="lgUser">Username</label><input id="lgUser" autocomplete="username" autocapitalize="none" spellcheck="false" value="<?= htmlspecialchars($fill[$defUser]['user'] ?? '') ?>" required></div>
   <div class="fld"><label for="lgPass">Password</label>
    <div class="pw"><input id="lgPass" type="password" autocomplete="current-password" value="<?= htmlspecialchars($fill[$defUser]['pass'] ?? '') ?>" required>
     <button type="button" onclick="togglePw()" aria-label="Tampilkan password"><i class="bx bx-show" id="pwIc"></i></button></div></div>
   <button class="ab ab-main ab-block" id="lgBtn" type="submit">Masuk</button>
   <a class="back" href="index.php?masuk=1">Kembali ke website</a>
  </form>
 </div>
</div>
<script>
const $ = id => document.getElementById(id);
const FILL = <?= json_encode($fill ?: new stdClass()) ?>;
function pickRole(r) {
  $('rAdmin').classList.toggle('on', r === 'admin'); $('rDev').classList.toggle('on', r === 'developer');
  const f = FILL[r];
  $('lgUser').value = f ? f.user : r; $('lgPass').value = f ? f.pass : '';
  (f ? $('lgBtn') : $('lgPass')).focus();
}
function togglePw() {
  const p = $('lgPass'), show = p.type === 'password';
  p.type = show ? 'text' : 'password'; $('pwIc').className = 'bx ' + (show ? 'bx-hide' : 'bx-show');
}
async function adminLogin(e) {
  e.preventDefault();
  const b = $('lgBtn'), m = $('lgMsg'); b.disabled = true; m.className = 'msg';
  try {
    const res = await fetch('admin_api.php?action=login', { method: 'POST', headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ username: $('lgUser').value, password: $('lgPass').value }) });
    const r = await res.json();
    if (r.ok) { location.reload(); return false; }
    m.className = 'msg err'; m.textContent = r.msg;
  } catch (err) { m.className = 'msg err'; m.textContent = 'Tidak dapat terhubung ke server. Pastikan Apache/XAMPP menyala.'; }
  b.disabled = false; return false;
}
</script>

<?php else: ?>
<?php $nm = $_SESSION['admin_name'] ?? ''; $role = $_SESSION['admin_role'] ?? ''; ?>
<!-- ================= DASHBOARD ================= -->
<div class="app">
 <aside class="side">
  <a class="brand" href="admin.php"><img src="assets/img/logo.png" alt="Logo"><span>Panel Admin<small><?= STORE_NAME ?></small></span></a>
  <nav>
   <button class="nav on" id="nvSummary" onclick="showTab('summary')"><i class="bx bx-line-chart"></i><span>Ringkasan</span></button>
   <button class="nav" id="nvOrders" onclick="showTab('orders')"><i class="bx bx-receipt"></i><span>Pesanan</span><b class="badge" id="nvBadge" hidden>0</b></button>
   <button class="nav" id="nvStock" onclick="showTab('stock')"><i class="bx bx-package"></i><span>Stok menu</span></button>
  </nav>
  <div class="side-foot">
   <div class="who"><i><?= htmlspecialchars(mb_strtoupper(mb_substr($nm, 0, 1))) ?></i><div><b><?= htmlspecialchars($nm) ?></b><small><?= htmlspecialchars($role) ?></small></div></div>
   <a class="ab ab-line ab-sm hide-m" href="index.php?masuk=1" target="_blank" rel="noopener"><i class="bx bx-link-external"></i><span>Lihat website</span></a>
   <button class="ab ab-line ab-sm" onclick="adminLogout()"><i class="bx bx-log-out"></i><span>Keluar</span></button>
  </div>
 </aside>

 <main class="main">
  <!-- ===== Ringkasan ===== -->
  <section id="paneSummary">
   <div class="page-h">
    <div><h1>Ringkasan penjualan</h1><p id="sumNote">Pesanan yang dibatalkan tidak dihitung.</p></div>
    <div class="acts"><button class="ab ab-line ab-sm" onclick="loadAll(true)"><i class="bx bx-refresh"></i> Muat ulang</button></div>
   </div>
   <div id="sumBody"><div class="card empty">Memuat ringkasan…</div></div>
  </section>

  <!-- ===== Pesanan ===== -->
  <section id="paneOrders" hidden>
   <div class="page-h">
    <div><h1>Pesanan</h1><p>Verifikasi bukti pembayaran dan ubah status pesanan.</p></div>
    <div class="acts"><button class="ab ab-line ab-sm" onclick="loadAll(true)"><i class="bx bx-refresh"></i> Muat ulang</button></div>
   </div>
   <div class="filter" id="ordFilter"></div>
   <div id="ordList" class="ord-list"><div class="card empty">Memuat pesanan…</div></div>
  </section>

  <!-- ===== Stok ===== -->
  <section id="paneStock" hidden>
   <div class="page-h">
    <div><h1>Stok menu</h1><p>Atur jumlah stok tiap menu.</p></div>
    <div class="acts"><button class="ab ab-line ab-sm" onclick="loadAll(true)"><i class="bx bx-refresh"></i> Muat ulang</button></div>
   </div>
   <div class="hint"><i class="bx bx-info-circle"></i><span>Isi jumlah stok lalu tekan <b>Simpan</b>. Tekan <b>Habiskan</b> untuk menandai menu "Stok habis" di website.</span></div>
   <div id="stockList" class="stock-list"></div>
  </section>
 </main>
</div>

<div class="lightbox" id="lightbox" hidden onclick="closeProof()">
 <button class="lb-close" aria-label="Tutup"><i class="bx bx-x"></i></button>
 <img id="lbImg" alt="Bukti pembayaran" onclick="event.stopPropagation()">
 <a id="lbOpen" class="ab ab-main ab-sm" target="_blank" rel="noopener" onclick="event.stopPropagation()"><i class="bx bx-link-external"></i> Buka ukuran penuh</a>
</div>
<div class="toast" id="toast" role="status"></div>

<script>
const CSRF = <?= json_encode($_SESSION['csrf']) ?>;
const $ = id => document.getElementById(id);
const rp = n => 'Rp ' + Number(n || 0).toLocaleString('id-ID');
const short = n => n >= 1e6 ? (n / 1e6).toFixed(n % 1e6 ? 1 : 0).replace('.', ',') + ' jt' : n >= 1e3 ? Math.round(n / 1e3) + ' rb' : String(n);
const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
let ORDERS = [], STATUSES = [], PRODS = [], SUM = null, FILTER = 'semua';

function toast(t) { const e = $('toast'); e.textContent = t; e.classList.add('show'); clearTimeout(toast._t); toast._t = setTimeout(() => e.classList.remove('show'), 2200); }

async function call(action, data) {
  let r;
  try {
    const res = await fetch('admin_api.php?action=' + action, { method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF }, body: JSON.stringify(data || {}) });
    r = await res.json();
  } catch (e) { return { ok: false, msg: 'Tidak dapat terhubung ke server.' }; }
  if (r.auth) { alert(r.msg); location.reload(); }
  return r;
}
async function adminLogout() { await call('logout'); location.href = 'admin.php'; }

const TABS = { summary: 'Summary', orders: 'Orders', stock: 'Stock' };
function showTab(t) {
  for (const k in TABS) { $('pane' + TABS[k]).hidden = k !== t; $('nv' + TABS[k]).classList.toggle('on', k === t); }
  history.replaceState(null, '', '#' + t); scrollTo(0, 0);
}

/* ---------------- ringkasan ---------------- */
const HARI = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
const BLN = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
function renderSummary() {
  if (!SUM) return;
  const s = SUM, w = s.windows, max = Math.max(1, ...s.days.map(d => d.revenue));
  const cols = s.days.map((d, i) => {
    const dt = new Date(d.date + 'T00:00:00'), h = Math.round(d.revenue / max * 100);
    return `<div class="col${d.revenue ? '' : ' zero'}" tabindex="0" aria-label="${HARI[dt.getDay()]} ${dt.getDate()} ${BLN[dt.getMonth()]}: ${d.orders} pesanan, ${rp(d.revenue)}">
      <div class="tip"><b>${HARI[dt.getDay()]}, ${dt.getDate()} ${BLN[dt.getMonth()]}</b><br>${rp(d.revenue)}<br>${d.orders} pesanan · ${d.units} porsi</div>
      <i style="height:${d.revenue ? Math.max(h, 4) : 2}%"></i></div>`;
  }).join('');
  const ticks = s.days.map((d, i) => `<span class="${i === s.days.length - 1 ? 'today' : ''}">${new Date(d.date + 'T00:00:00').getDate()}</span>`).join('');
  const last14 = s.days.reduce((a, d) => a + d.revenue, 0);
  const topMax = Math.max(1, ...s.products.map(p => p.sold));
  const rank = s.products.map((p, i) => `
    <li class="${i === 0 && p.sold ? 'first' : ''}">
     <img src="assets/img/${esc(p.img)}" alt="">
     <div class="nm">${esc(p.name)}<small>${p.revenue ? rp(p.revenue) + ' dari pesanan' : 'Belum ada pesanan lewat website'}</small></div>
     <div class="qty">${p.sold}<small>terjual</small></div>
     <div class="bar"><i style="width:${Math.round(p.sold / topMax * 100)}%"></i></div>
    </li>`).join('');
  const stCls = k => k === 'Dibatalkan' ? 'bad' : /^Menunggu/.test(k) ? 'warn' : '';
  const stRows = Object.entries(s.status).map(([k, v]) => `<div class="row"><span><i class="dot ${stCls(k)}"></i>${esc(k)}</span><b>${v}</b></div>`).join('') || '<div class="row"><small>Belum ada pesanan</small></div>';
  const payRows = Object.entries(s.payments).map(([k, v]) => `<div class="row"><span>${esc(k)}<br><small>${v.orders} pesanan</small></span><b>${rp(v.revenue)}</b></div>`).join('') || '<div class="row"><small>Belum ada pembayaran</small></div>';
  $('sumBody').innerHTML = `
   <div class="hero">
    <div class="hero-l">
     <span class="lbl">Total omzet</span>
     <span class="big num">${rp(s.revenue)}</span>
     <span class="sub">dari ${s.orders} pesanan, rata-rata ${rp(s.avg)} per pesanan</span>
     <div class="win">
      <div><small>Hari ini</small><b>${rp(w.today.revenue)}</b><span>${w.today.orders} pesanan</span></div>
      <div><small>7 hari</small><b>${rp(w.week.revenue)}</b><span>${w.week.orders} pesanan</span></div>
      <div><small>Bulan ini</small><b>${rp(w.month.revenue)}</b><span>${w.month.orders} pesanan</span></div>
     </div>
    </div>
    <div class="hero-r">
     <div class="hd"><h2>Omzet 14 hari terakhir</h2><small>${rp(last14)}</small></div>
     <div class="chart">${cols}</div>
     <div class="ticks">${ticks}</div>
    </div>
   </div>
   <div class="kpis">
    <div class="card kpi"><i class="bx bx-shopping-bag"></i><div><b class="num">${s.units_all}</b><small>Total porsi terjual</small></div></div>
    <div class="card kpi ok"><i class="bx bx-receipt"></i><div><b class="num">${s.orders}</b><small>Pesanan masuk</small></div></div>
    <div class="card kpi warn"><i class="bx bx-time-five"></i><div><b class="num">${s.pending}</b><small>Perlu verifikasi</small></div></div>
    <div class="card kpi bad"><i class="bx bx-error-circle"></i><div><b class="num">${s.out_of_stock}</b><small>Menu stok habis</small></div></div>
   </div>
   <div class="two">
    <div class="card"><div class="card-h"><h2>Menu terlaris</h2><small>Total termasuk angka awal</small></div><ul class="rank">${rank}</ul></div>
    <div class="card"><div class="card-h"><h2>Pesanan & pembayaran</h2></div><div class="brk">
     <h3>Status pesanan</h3>${stRows}<h3>Metode pembayaran</h3>${payRows}</div></div>
   </div>`;
}

/* ---------------- stok ---------------- */
function stockBadge(s) {
  if (s === null) return '<span class="badge-s low"><i class="bx bx-help-circle"></i> Belum diatur</span>';
  if (s <= 0) return '<span class="badge-s out"><i class="bx bx-error-circle"></i> Stok habis</span>';
  return '<span class="badge-s' + (s <= 5 ? ' low' : '') + '"><i class="bx bx-package"></i> ' + s + ' tersisa</span>';
}
function renderStock() {
  $('stockList').innerHTML = PRODS.map(p => `
   <div class="card stock-row">
    <img src="assets/img/${esc(p.img)}" alt="">
    <div class="sr-name"><b>${esc(p.name)}</b><small>${rp(p.price)} · ${p.sold} terjual</small><span id="sb${p.id}">${stockBadge(p.stock)}</span></div>
    <div class="sr-ctl">
     <button class="qb" onclick="stepStock(${p.id},-1)" aria-label="Kurangi stok ${esc(p.name)}"><i class="bx bx-minus"></i></button>
     <input type="number" min="0" max="9999" id="si${p.id}" value="${p.stock === null ? 0 : p.stock}" aria-label="Stok ${esc(p.name)}" onkeydown="if(event.key==='Enter')saveStock(${p.id})">
     <button class="qb" onclick="stepStock(${p.id},1)" aria-label="Tambah stok ${esc(p.name)}"><i class="bx bx-plus"></i></button>
     <button class="ab ab-dark ab-sm" onclick="saveStock(${p.id})">Simpan</button>
     <button class="ab ab-line ab-sm" onclick="unstock(${p.id})">Habiskan</button>
    </div>
   </div>`).join('');
}
function stepStock(id, d) { const i = $('si' + id); i.value = Math.max(0, Math.min(9999, (parseInt(i.value, 10) || 0) + d)); }
async function saveStock(id) {
  const v = $('si' + id).value;
  const r = await call('stock_set', { id, stock: v === '' ? null : Number(v) });
  if (!r.ok) { toast(r.msg); return; }
  const p = PRODS.find(x => x.id === id); p.stock = r.stock;
  $('sb' + id).innerHTML = stockBadge(r.stock); $('si' + id).value = r.stock;
  toast(p.name + ': ' + (r.stock === 0 ? 'stok habis' : 'stok ' + r.stock));
  loadSummary();
}
function unstock(id) { $('si' + id).value = 0; saveStock(id); }

/* ---------------- pesanan ---------------- */
function waLink(hp) {
  let n = String(hp).replace(/\D/g, '');
  if (n.startsWith('0')) n = '62' + n.slice(1);
  return 'https://wa.me/' + n;
}
const isWait = s => /^Menunggu/.test(s);
const statusClass = s => s === 'Dibatalkan' ? 'bad' : isWait(s) ? 'warn' : 'ok';
const FILTERS = [['semua', 'Semua', () => true], ['menunggu', 'Perlu verifikasi', o => isWait(o.status)],
  ['proses', 'Diproses / dikirim', o => /^(Pembayaran terverifikasi|Diproses|Dikirim)$/.test(o.status)],
  ['selesai', 'Selesai', o => o.status === 'Selesai'], ['batal', 'Dibatalkan', o => o.status === 'Dibatalkan']];
function renderOrders() {
  $('ordFilter').innerHTML = FILTERS.map(([k, l, f]) =>
    `<button class="pill${FILTER === k ? ' on' : ''}" onclick="FILTER='${k}';renderOrders()">${l}<em>${ORDERS.filter(f).length}</em></button>`).join('');
  const fn = FILTERS.find(f => f[0] === FILTER)[2], list = ORDERS.filter(fn);
  const pend = ORDERS.filter(o => isWait(o.status)).length;
  $('nvBadge').hidden = !pend; $('nvBadge').textContent = pend;
  if (!list.length) { $('ordList').innerHTML = '<div class="card empty"><i class="bx bx-receipt"></i> Belum ada pesanan di kategori ini.</div>'; return; }
  $('ordList').innerHTML = list.map(o => {
    const bukti = o.bukti
      ? `<button class="proof" onclick="openProof('${esc(o.bukti)}')" title="Klik untuk memperbesar"><img src="${esc(o.bukti)}" alt="Bukti pembayaran ${esc(o.kode)}"><span><i class="bx bx-zoom-in"></i> Lihat bukti</span></button>`
      : o.bukti_hilang ? '<div class="noproof bad"><i class="bx bx-error"></i>File bukti tidak ditemukan di server</div>'
      : '<div class="noproof"><i class="bx bx-image-alt"></i>Tidak ada bukti (' + esc(o.bayar) + ')</div>';
    const off = o.status === 'Dibatalkan' ? 'disabled' : '';
    return `
    <article class="card ord">
     <div class="ord-head"><div><b class="ord-code">${esc(o.kode)}</b><small>${esc(o.waktu)}</small></div><span class="st ${statusClass(o.status)}">${esc(o.status)}</span></div>
     <div class="ord-body">
      <div class="ord-info">
       <p><i class="bx bx-user"></i><span><b>${esc(o.nama)}</b>${o.email ? ' <small>(' + esc(o.email) + ')</small>' : ''}</span></p>
       <p><i class="bx bx-phone"></i><a href="${esc(waLink(o.hp))}" target="_blank" rel="noopener">${esc(o.hp)}</a></p>
       <p><i class="bx bx-map"></i>${esc(o.alamat)}</p>
       ${o.catatan ? '<p><i class="bx bx-note"></i>' + esc(o.catatan) + '</p>' : ''}
       <p><i class="bx bx-wallet"></i>${esc(o.bayar)}</p>
       <ul class="items">${o.items.map(i => `<li><span>${i.qty}× ${esc(i.name)}</span><span>${rp(i.sub)}</span></li>`).join('')}</ul>
       <div class="tot"><span>Total</span><b class="num">${rp(o.total)}</b></div>
      </div>
      <div>${bukti}</div>
     </div>
     <div class="ord-act">
      <select id="os${o.id}" aria-label="Status pesanan ${esc(o.kode)}" ${off}>${STATUSES.map(s => `<option${s === o.status ? ' selected' : ''}>${esc(s)}</option>`).join('')}</select>
      <button class="ab ab-dark ab-sm" onclick="setStatus(${o.id})" ${off}>Simpan status</button>
     </div>
    </article>`;
  }).join('');
}
async function setStatus(id) {
  const st = $('os' + id).value;
  if (st === 'Dibatalkan' && !confirm('Batalkan pesanan ini? Stok akan dikembalikan dan pesanan tidak bisa diubah lagi.')) return;
  const r = await call('order_status', { id, status: st });
  if (!r.ok) { toast(r.msg); return; }
  toast('Status diperbarui');
  await loadAll();
}
function openProof(src) { $('lbImg').src = src; $('lbOpen').href = src; $('lightbox').hidden = false; }
function closeProof() { $('lightbox').hidden = true; $('lbImg').removeAttribute('src'); }
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeProof(); });

async function loadSummary() {
  const r = await call('summary');
  if (r.ok) { SUM = r.summary; renderSummary(); }
  else $('sumBody').innerHTML = '<div class="card empty"><i class="bx bx-error-circle"></i> ' + esc(r.msg) + '</div>';
}
async function loadAll(manual) {
  const [o, s] = await Promise.all([call('orders'), call('stock_list'), loadSummary()]);
  if (o.ok) { ORDERS = o.orders; STATUSES = o.statuses; renderOrders(); }
  else $('ordList').innerHTML = '<div class="card empty"><i class="bx bx-error-circle"></i> ' + esc(o.msg) + '</div>';
  if (s.ok) { PRODS = s.products; renderStock(); }
  if (manual) toast('Data dimuat ulang');
}
showTab(({ '#orders': 'orders', '#stock': 'stock' })[location.hash] || 'summary');
loadAll();
</script>
<?php endif; ?>
</body>
</html>
