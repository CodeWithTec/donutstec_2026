<?php
require_once __DIR__ . '/../includes/functions.php';
$page_title = 'Our Services';
$active_nav = 'services';
include __DIR__ . '/../includes/header.php';
?>

<section class="py-5" style="background: linear-gradient(135deg,#151B54,#1E90FF); color:#fff;">
  <div class="container py-4">
    <span class="badge bg-light text-dtc-night mb-2 px-3">Services</span>
    <h1 class="display-5 fw-bold mb-2">What We Offer</h1>
    <p class="lead mb-0 opacity-75">Full-stack software development services for every stage of your business.</p>
  </div>
</section>

<section class="py-5">
  <div class="container">
    <div class="row g-4">
      <?php
      $services = [
        ['bi-window-stack','Web Development','Modern, responsive websites and web applications built with PHP, JavaScript, MySQL, and Bootstrap.'],
        ['bi-phone','Mobile App Development','iOS and Android applications that connect with your backend, mobile money, and APIs.'],
        ['bi-gear-wide-connected','Custom Software','Bespoke ERPs, CRMs, booking systems, and workflow automation tailored to your business.'],
        ['bi-palette','UI/UX Design','Research-driven interface and experience design that converts and delights.'],
        ['bi-cloud-arrow-up','Deployment & Hosting','Reliable hosting setup, CI/CD pipelines, and continuous monitoring.'],
        ['bi-headset','Maintenance & Support','Ongoing support, bug fixes, and feature development after launch.'],
      ];
      foreach ($services as [$icon, $title, $desc]): ?>
        <div class="col-md-6 col-lg-4">
          <div class="card card-dtc h-100 p-4">
            <div class="card-icon"><i class="bi <?= $icon ?>"></i></div>
            <h4 class="fw-bold text-dtc-night"><?= $title ?></h4>
            <p class="text-muted mb-0"><?= $desc ?></p>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- PROCESS -->
<section class="py-5" style="background:#EEF4FF;">
  <div class="container">
    <div class="text-center mb-5">
      <h2 class="fw-bold text-dtc-night">How We Work</h2>
      <p class="text-muted">A transparent process from start to finish</p>
    </div>
    <div class="row g-4">
      <?php
      $steps = [
        ['1','Discovery','We listen to understand your goals, users, and constraints.'],
        ['2','Design','Wireframes and mockups to align on look, feel, and flow.'],
        ['3','Build','Agile development with regular demos and your feedback.'],
        ['4','Launch','Testing, deployment, and handover — plus ongoing support.'],
      ];
      foreach ($steps as [$n, $title, $desc]): ?>
        <div class="col-md-6 col-lg-3">
          <div class="card card-dtc h-100 p-4 text-center">
            <div class="rounded-circle bg-dtc-dodger text-white d-inline-flex align-items-center justify-content-center mb-3"
                 style="width:48px;height:48px;font-weight:700;font-size:1.25rem;">
              <?= $n ?>
            </div>
            <h5 class="fw-bold text-dtc-night"><?= $title ?></h5>
            <p class="text-muted small mb-0"><?= $desc ?></p>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="py-5">
  <div class="container text-center">
    <h2 class="fw-bold text-dtc-night mb-3">Need Something Custom?</h2>
    <p class="text-muted mb-4">Tell us your idea — we'll turn it into a working product.</p>
    <a href="<?= BASE_URL ?>/public/quote.php" class="btn btn-dtc-primary btn-lg px-4">Get a Free Quote</a>
  </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>