/* =====================================================
   APP.JS — fungsi bersama: keranjang, toast, API, logout
   (PRODUCTS, WA, LOGGED diisi dari includes/footer.php)
   ===================================================== */
const $ = id => document.getElementById(id);
const rp = n => 'Rp ' + Number(n).toLocaleString('id-ID');
const byId = id => PRODUCTS.find(p => p.id == id);

/* ---- keranjang disimpan di localStorage, jadi ikut ke semua halaman ---- */
let cart = {};
try { cart = JSON.parse(localStorage.getItem('pl_cart') || '{}'); } catch (e) {}

function saveCart() {
  try { localStorage.setItem('pl_cart', JSON.stringify(cart)); } catch (e) {}
  const c = $('cartCount');
  if (c) c.textContent = Object.values(cart).reduce((a, b) => a + b, 0);
}
function cartTotal() {
  return Object.entries(cart).reduce((s, [id, q]) => s + (byId(id) ? byId(id).price * q : 0), 0);
}
/* stock: null = tidak dibatasi, 0 = UNSTOCK (habis) */
const maxQty = id => { const s = byId(id) && byId(id).stock; return s === null || s === undefined ? 100 : s; };
/* buang item habis & sesuaikan jumlah dengan stock terbaru */
Object.keys(cart).forEach(id => {
  if (!byId(id) || maxQty(id) < 1) delete cart[id]; else if (cart[id] > maxQty(id)) cart[id] = maxQty(id);
});
function addToCart(id) {
  if (maxQty(id) < 1) { toast('Maaf, ' + byId(id).name + ' sedang habis'); return; }
  if ((cart[id] || 0) >= maxQty(id)) { toast('Stok ' + byId(id).name + ' hanya ' + maxQty(id)); return; }
  cart[id] = (cart[id] || 0) + 1;
  saveCart();
  toast('✓ ' + byId(id).name + ' ditambahkan');
  if (typeof renderCart === 'function') renderCart();
}
function changeQty(id, d) {
  if (d > 0 && (cart[id] || 0) >= maxQty(id)) { toast('Stok ' + byId(id).name + ' hanya ' + maxQty(id)); return; }
  cart[id] = (cart[id] || 0) + d;
  if (cart[id] <= 0) delete cart[id];
  saveCart();
  if (typeof renderCart === 'function') renderCart();
}

/* ---- toast ---- */
function toast(t) {
  const e = $('toast'); if (!e) return;
  e.textContent = t; e.classList.add('show');
  setTimeout(() => e.classList.remove('show'), 1800);
}

/* ---- panggilan ke api.php ---- */
async function api(action, data) {
  const r = await fetch('api.php?action=' + action, {
    method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(data || {})
  });
  return r.json();
}
/* kirim FormData (untuk upload file), jangan set Content-Type agar browser mengisi boundary */
async function apiForm(action, formData) {
  const r = await fetch('api.php?action=' + action, { method: 'POST', body: formData });
  const text = await r.text();
  try {
    return JSON.parse(text);
  } catch (e) {
    if (!r.ok) throw new Error(`Server gagal memproses pesanan (HTTP ${r.status}). Periksa konfigurasi PHP/API.`);
    throw new Error('Respons server tidak valid. Periksa log PHP/Apache.');
  }
}
async function logout() { await api('logout'); location.href = 'index.php'; }

saveCart();
