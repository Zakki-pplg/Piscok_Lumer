/* =====================================================
   APP.JS — fungsi bersama: toast, API, logout
   (PRODUCTS, WA, LOGGED diisi dari includes/footer.php)
   ===================================================== */
const $ = id => document.getElementById(id);
const rp = n => 'Rp ' + Number(n).toLocaleString('id-ID');
const byId = id => PRODUCTS.find(p => p.id == id);

/* ---- toast: ikon centang + teks (teks di-escape agar aman) ---- */
function toast(t, icon = 'bx-check-circle') {
  const e = $('toast'); if (!e) return;
  e.innerHTML = `<i class="bx ${icon}"></i><span></span>`;
  e.querySelector('span').textContent = t;
  e.classList.add('show');
  clearTimeout(toast._t);
  toast._t = setTimeout(() => e.classList.remove('show'), 2200);
}

/* ---- panggilan ke api.php ---- */
async function api(action, data) {
  const r = await fetch('api.php?action=' + action, {
    method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(data || {})
  });
  return r.json();
}
async function logout() { await api('logout'); location.href = 'index.php'; }

/* keranjang lama (versi sebelumnya) tidak dipakai lagi: bersihkan sisa data di browser */
try { localStorage.removeItem('pl_cart'); } catch (e) {}
