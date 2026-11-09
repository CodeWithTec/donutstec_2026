<?php
require_once __DIR__ . '/../includes/functions.php';
$page_title = 'About Us';
$active_nav = 'about';
include __DIR__ . '/../includes/header.php';
?>

<!-- PAGE HEADER -->
<section class="py-5" style="background: linear-gradient(135deg,#151B54,#1E90FF); color:#fff;">
  <div class="container py-4">
    <span class="badge bg-light text-dtc-night mb-2 px-3">About Us</span>
    <h1 class="display-5 fw-bold mb-2">We Build Software That Matters</h1>
    <p class="lead mb-0 opacity-75">Empowering Liberian and African businesses through world-class technology.</p>
  </div>
</section>

<!-- STORY -->
<section class="py-5">
  <div class="container">
    <div class="row align-items-center g-5">
      <div class="col-lg-6">
        <h2 class="fw-bold text-dtc-night mb-3">Our Story</h2>
        <p class="text-muted">
          DonutsTecLiberia was born from a simple idea: African businesses deserve
          software built for African realities — not overpriced imports designed
          for someone else's market.
        </p>
        <p class="text-muted">
          From our base in Monrovia, we've partnered with startups, schools, and
          enterprises across Liberia and West Africa to ship web apps, mobile apps,
          and custom systems that solve real problems.
        </p>
        <div class="row mt-4">
          <div class="col-6 mb-3">
            <div class="card card-dtc p-3 text-center">
              <div class="fs-2 fw-bold text-dtc-dodger">30+</div>
              <div class="small text-muted">Projects Delivered</div>
            </div>
          </div>
          <div class="col-6 mb-3">
            <div class="card card-dtc p-3 text-center">
              <div class="fs-2 fw-bold text-dtc-dodger">100%</div>
              <div class="small text-muted">Client Focused</div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-lg-6">
        <div class="p-5 rounded-4 text-center" style="background: linear-gradient(135deg,#151B54,#1E90FF);">
          <i class="bi bi-people-fill text-white" style="font-size:8rem; opacity:0.75;"></i>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- MISSION / VISION / VALUES -->
<section class="py-5" style="background:#EEF4FF;">
  <div class="container">
    <div class="row g-4">
      <div class="col-md-4">
        <div class="card card-dtc h-100 p-4">
          <div class="card-icon"><i class="bi bi-bullseye"></i></div>
          <h4 class="fw-bold text-dtc-night">Our Mission</h4>
          <p class="text-muted mb-0">To democratize high-quality software for African businesses — one product at a time.</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card card-dtc h-100 p-4">
          <div class="card-icon"><i class="bi bi-eye"></i></div>
          <h4 class="fw-bold text-dtc-night">Our Vision</h4>
          <p class="text-muted mb-0">A future where every African business has access to world-class digital tools.</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card card-dtc h-100 p-4">
          <div class="card-icon"><i class="bi bi-shield-check"></i></div>
          <h4 class="fw-bold text-dtc-night">Our Values</h4>
          <p class="text-muted mb-0">Integrity, craftsmanship, transparency, and client obsession in every project.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- CTA -->
<section class="py-5">
  <div class="container text-center">
    <h2 class="fw-bold text-dtc-night mb-3">Let's Build Something Great Together</h2>
    <p class="text-muted mb-4">Start with a free consultation — no strings attached.</p>
    <a href="<?= BASE_URL ?>/public/quote.php" class="btn btn-dtc-primary btn-lg px-4">Request a Quote</a>
  </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>