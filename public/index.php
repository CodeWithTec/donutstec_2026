<?php
require_once __DIR__ . '/../includes/functions.php';

$db = Database::getConnection();
$portfolio  = $db->query("SELECT * FROM portfolio WHERE is_featured = 1 ORDER BY sort_order ASC LIMIT 3")->fetchAll();
$testimonials = $db->query("SELECT * FROM testimonials WHERE is_approved = 1 ORDER BY id DESC LIMIT 3")->fetchAll();
$posts = $db->query("SELECT * FROM blog_posts WHERE status = 'published' ORDER BY published_at DESC LIMIT 3")->fetchAll();

$page_title = 'Software Development Company in Liberia';
$active_nav = 'home';
include __DIR__ . '/../includes/header.php';
?>

<!-- HERO -->
<section class="hero-dtc">
  <div class="container position-relative">
    <div class="row align-items-center">
      <div class="col-lg-7">
        <span class="badge bg-dtc-dodger mb-3 px-3 py-2">🇱🇷 Proudly Built in Liberia</span>
        <h1 class="display-4 mb-3">Building Software<br>That Works For You</h1>
        <p class="lead mb-4">
          DonutsTecLiberia crafts web apps, mobile apps, and custom software
          engineered for African businesses to scale — with clean code and clear communication.
        </p>
        <a href="<?= BASE_URL ?>/public/quote.php" class="btn btn-dtc-accent btn-lg px-4 me-2">
          Get a Free Quote <i class="bi bi-arrow-right ms-1"></i>
        </a>
        <a href="<?= BASE_URL ?>/public/portfolio.php" class="btn btn-outline-light btn-lg px-4">View Our Work</a>
        <div class="mt-4 d-flex gap-4 small opacity-75">
          <div><i class="bi bi-check-circle-fill text-dtc-sky me-1"></i> 30+ projects delivered</div>
          <div><i class="bi bi-check-circle-fill text-dtc-sky me-1"></i> 100% client focus</div>
        </div>
      </div>
      <div class="col-lg-5 d-none d-lg-block text-center">
        <!-- <i class="bi bi-code-square" style="font-size:14rem; opacity:0.22;"></i> -->
        <img src="<?= BASE_URL ?>/assets/img/logo.png" alt="DonutsTecLiberia Logo" class="img-fluid logo-rotate" style="max-height:300px; opacity:0.8;">
      </div>
    </div>
  </div>
</section>

<!-- SERVICES -->
<section class="py-5">
  <div class="container">
    <div class="text-center mb-5">
      <h2 class="fw-bold text-dtc-night">What We Do</h2>
      <p class="text-muted">End-to-end software services under one roof</p>
    </div>
    <div class="row g-4">
      <?php
      $services = [
        ['bi-window-stack',        'Web Development',  'Modern, responsive websites & web apps built with PHP, JS & MySQL.'],
        ['bi-phone',               'Mobile Apps',      'iOS & Android applications tailored to your business workflows.'],
        ['bi-gear-wide-connected', 'Custom Software',  'Bespoke systems: ERPs, CRMs, booking systems and more.'],
        ['bi-palette',             'UI / UX Design',   'User-first interfaces that convert visitors into customers.'],
      ];
      foreach ($services as [$icon, $title, $desc]): ?>
        <div class="col-md-6 col-lg-3">
          <div class="card card-dtc h-100 p-3">
            <div class="card-body">
              <div class="card-icon"><i class="bi <?= $icon ?>"></i></div>
              <h5 class="fw-bold text-dtc-night"><?= $title ?></h5>
              <p class="text-muted small mb-0"><?= $desc ?></p>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
    <div class="text-center mt-4">
      <a href="<?= BASE_URL ?>/public/services.php" class="btn btn-dtc-outline px-4">
        Explore All Services <i class="bi bi-arrow-right ms-1"></i>
      </a>
    </div>
  </div>
</section>

<!-- FEATURED PORTFOLIO -->
<section class="py-5" style="background:#EEF4FF;">
  <div class="container">
    <div class="text-center mb-5">
      <h2 class="fw-bold text-dtc-night">Featured Work</h2>
      <p class="text-muted">A few projects we're proud of</p>
    </div>
    <div class="row g-4">
      <?php foreach ($portfolio as $p): ?>
        <div class="col-md-4">
          <div class="card card-dtc h-100">
            <div style="height:180px; background: linear-gradient(135deg,#1E90FF,#0000FF); border-radius:12px 12px 0 0; display:flex; align-items:center; justify-content:center;">
              <i class="bi bi-window" style="font-size:4rem; color:rgba(255,255,255,0.8);"></i>
            </div>
            <div class="card-body">
              <span class="badge badge-dtc-sky mb-2"><?= e($p['category']) ?></span>
              <h5 class="fw-bold text-dtc-night"><?= e($p['title']) ?></h5>
              <p class="text-muted small"><?= e($p['description']) ?></p>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
    <div class="text-center mt-4">
      <a href="<?= BASE_URL ?>/public/portfolio.php" class="btn btn-dtc-primary px-4">
        View All Projects <i class="bi bi-arrow-right ms-1"></i>
      </a>
    </div>
  </div>
</section>

<!-- TESTIMONIALS -->
<section class="py-5">
  <div class="container">
    <div class="text-center mb-5">
      <h2 class="fw-bold text-dtc-night">What Clients Say</h2>
      <p class="text-muted">Real feedback from real partners</p>
    </div>
    <div class="row g-4">
      <?php foreach ($testimonials as $t): ?>
        <div class="col-md-4">
          <div class="card card-dtc h-100 p-4">
            <div class="text-warning mb-2">
              <?php for ($i=0; $i<(int)$t['rating']; $i++) echo '<i class="bi bi-star-fill"></i>'; ?>
            </div>
            <p class="fst-italic">"<?= e($t['content']) ?>"</p>
            <div class="d-flex align-items-center mt-auto">
              <div class="rounded-circle bg-dtc-dodger text-white d-flex align-items-center justify-content-center me-3"
                   style="width:44px;height:44px;font-weight:700;">
                <?= strtoupper(substr($t['client_name'],0,1)) ?>
              </div>
              <div>
                <div class="fw-bold"><?= e($t['client_name']) ?></div>
                <div class="small text-muted"><?= e($t['client_role']) ?></div>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- BLOG PREVIEW -->
<?php if ($posts): ?>
<section class="py-5" style="background:#EEF4FF;">
  <div class="container">
    <div class="text-center mb-5">
      <h2 class="fw-bold text-dtc-night">Latest Insights</h2>
      <p class="text-muted">Thoughts from our engineering team</p>
    </div>
    <div class="row g-4">
      <?php foreach ($posts as $post): ?>
        <div class="col-md-4">
          <div class="card card-dtc h-100">
            <div style="height:160px; background: linear-gradient(135deg,#82CAFF,#1E90FF); border-radius:12px 12px 0 0;"></div>
            <div class="card-body">
              <div class="small text-muted mb-2">
                <i class="bi bi-calendar me-1"></i>
                <?= date('M d, Y', strtotime($post['published_at'])) ?>
              </div>
              <h5 class="fw-bold text-dtc-night"><?= e($post['title']) ?></h5>
              <p class="small text-muted"><?= e($post['excerpt']) ?></p>
              <a href="<?= BASE_URL ?>/public/blog-post.php?slug=<?= urlencode($post['slug']) ?>"
                 class="text-dtc-dodger fw-semibold small">
                Read more <i class="bi bi-arrow-right"></i>
              </a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- CTA -->
<section class="py-5" style="background: linear-gradient(135deg,#151B54,#1E90FF);">
  <div class="container text-center text-white">
    <h2 class="fw-bold mb-3">Ready to Start Your Project?</h2>
    <p class="mb-4 opacity-75">Get a free quote in minutes. No commitment required.</p>
    <a href="<?= BASE_URL ?>/public/quote.php" class="btn btn-light btn-lg px-4 text-dtc-night fw-semibold">
      Request a Quote <i class="bi bi-arrow-right ms-1"></i>
    </a>
  </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>