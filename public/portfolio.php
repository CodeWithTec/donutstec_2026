<?php
require_once __DIR__ . '/../includes/functions.php';

$db = Database::getConnection();
$items = $db->query("SELECT * FROM portfolio ORDER BY sort_order ASC, id DESC")->fetchAll();
$categories = array_values(array_unique(array_filter(array_column($items, 'category'))));

$page_title = 'Portfolio';
$active_nav = 'portfolio';
include __DIR__ . '/../includes/header.php';
?>

<section class="py-5" style="background: linear-gradient(135deg,#151B54,#1E90FF); color:#fff;">
  <div class="container py-4">
    <span class="badge bg-light text-dtc-night mb-2 px-3">Portfolio</span>
    <h1 class="display-5 fw-bold mb-2">Our Work</h1>
    <p class="lead mb-0 opacity-75">Real projects delivered to real clients.</p>
  </div>
</section>

<section class="py-5">
  <div class="container">
    <!-- Filters -->
    <div class="d-flex flex-wrap gap-2 justify-content-center mb-5">
      <button class="btn btn-dtc-primary active" data-portfolio-filter="all">All</button>
      <?php foreach ($categories as $cat): ?>
        <button class="btn btn-dtc-outline" data-portfolio-filter="<?= e($cat) ?>"><?= e($cat) ?></button>
      <?php endforeach; ?>
    </div>

    <div class="row g-4">
      <?php foreach ($items as $p): ?>
        <div class="col-md-6 col-lg-4" data-portfolio-category="<?= e($p['category']) ?>">
          <div class="card card-dtc h-100">
            <div style="height:200px; background: linear-gradient(135deg,#1E90FF,#0000FF); border-radius:12px 12px 0 0; display:flex; align-items:center; justify-content:center;">
              <i class="bi bi-window" style="font-size:5rem; color:rgba(255,255,255,0.85);"></i>
            </div>
            <div class="card-body">
              <span class="badge badge-dtc-sky mb-2"><?= e($p['category']) ?></span>
              <h5 class="fw-bold text-dtc-night"><?= e($p['title']) ?></h5>
              <p class="text-muted small mb-3"><?= e($p['description']) ?></p>
              <?php if ($p['project_url']): ?>
                <a href="<?= e($p['project_url']) ?>" target="_blank" rel="noopener"
                   class="text-dtc-dodger fw-semibold small">
                  Visit Project <i class="bi bi-box-arrow-up-right"></i>
                </a>
              <?php endif; ?>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="py-5" style="background:#EEF4FF;">
  <div class="container text-center">
    <h2 class="fw-bold text-dtc-night mb-3">Want to Be Our Next Success Story?</h2>
    <a href="<?= BASE_URL ?>/public/quote.php" class="btn btn-dtc-primary btn-lg px-4">Start Your Project</a>
  </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>