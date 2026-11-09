</main>

<footer class="footer-dtc">
  <div class="container">
    <div class="row g-4">
      <div class="col-lg-4">
        <h5>Donuts<span class="text-dtc-sky">Tec</span> Liberia</h5>
        <p class="small mt-2">
          Building software that works for you — from Monrovia to the world.
          Web apps, mobile apps, and custom software engineered for African businesses.
        </p>
        <div class="mt-3">
          <a href="#" class="me-3 fs-5"><i class="bi bi-facebook"></i></a>
          <a href="#" class="me-3 fs-5"><i class="bi bi-twitter-x"></i></a>
          <a href="#" class="me-3 fs-5"><i class="bi bi-linkedin"></i></a>
          <a href="#" class="fs-5"><i class="bi bi-instagram"></i></a>
        </div>
      </div>
      <div class="col-lg-2 col-md-4">
        <h5>Company</h5>
        <ul class="list-unstyled small">
          <li class="mb-2"><a href="<?= BASE_URL ?>/public/about.php">About Us</a></li>
          <li class="mb-2"><a href="<?= BASE_URL ?>/public/services.php">Services</a></li>
          <li class="mb-2"><a href="<?= BASE_URL ?>/public/portfolio.php">Portfolio</a></li>
          <li class="mb-2"><a href="<?= BASE_URL ?>/public/blog.php">Blog</a></li>
        </ul>
      </div>
      <div class="col-lg-2 col-md-4">
  <h5>Get Started</h5>
  <ul class="list-unstyled small">
    <li class="mb-2"><a href="<?= BASE_URL ?>/public/quote.php">Request a Quote</a></li>
    <?php if ($current_user ?? current_user()): ?>
      <li class="mb-2"><a href="<?= BASE_URL ?>/<?= ($current_user ?? current_user())['role'] ?>/dashboard.php">My Dashboard</a></li>
      <li class="mb-2"><a href="<?= BASE_URL ?>/public/logout.php">Log Out</a></li>
    <?php else: ?>
      <li class="mb-2"><a href="<?= BASE_URL ?>/public/register.php">Create Account</a></li>
      <li class="mb-2"><a href="<?= BASE_URL ?>/public/login.php">Client Login</a></li>
    <?php endif; ?>
    <li class="mb-2"><a href="<?= BASE_URL ?>/public/contact.php">Contact</a></li>
  </ul>
</div>
      <div class="col-lg-4 col-md-4">
        <h5>Newsletter</h5>
        <p class="small">Get occasional updates on new projects, insights, and tech tips.</p>
        <!-- <form id="newsletterForm" class="d-flex gap-2"> -->
        <form id="newsletterForm" data-action="<?= BASE_URL ?>/public/subscribe.php" class="d-flex gap-2">
          <input type="email" name="email" class="form-control form-control-sm" placeholder="your@email.com" required>
          <button class="btn btn-dtc-accent btn-sm px-3" type="submit">Join</button>
        </form>
        <div id="newsletterMsg" class="small mt-2"></div>
        <hr class="border-secondary">
        <p class="small mb-1"><i class="bi bi-envelope me-1"></i> info@donutstec.com</p>
        <p class="small mb-1"><i class="bi bi-geo-alt me-1"></i> Monrovia, Liberia</p>
      </div>
    </div>
    <hr class="border-secondary mt-4">
    <div class="d-flex flex-column flex-md-row justify-content-between small">
      <span>&copy; <?= date('Y') ?> DonutsTecLiberia. All rights reserved.</span>
      <span>Made with <i class="bi bi-heart-fill text-danger"></i> in Liberia</span>
    </div>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
</body>
<?php
$__db = Database::getConnection();
$__gid = $__db->query("SELECT setting_value FROM settings WHERE setting_key='analytics_id'")->fetchColumn();
if ($__gid):
?>
<script async src="https://www.googletagmanager.com/gtag/js?id=<?= e($__gid) ?>"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', '<?= e($__gid) ?>');
</script>
<?php endif; ?>
</html>