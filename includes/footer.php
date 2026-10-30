<footer>
 <img src="assets/img/logo.png" alt="Logo">
 © <?= date('Y') ?> <?= STORE_NAME ?> — Dibuat dengan 🍫 dan cinta.
</footer>

<a class="wa-float" target="_blank" rel="noopener" href="https://wa.me/<?= WA_NUMBER ?>?text=Halo%20kak%2C%20saya%20mau%20order%20piscok%20lumer" title="Chat WhatsApp">💬</a>
<div class="toast" id="toast"></div>

<script>
const PRODUCTS = <?= json_encode($products, JSON_UNESCAPED_UNICODE) ?>;
const WA = '<?= WA_NUMBER ?>';
const LOGGED = <?= $user ? 'true' : 'false' ?>;
</script>
<script src="assets/app.js"></script>
