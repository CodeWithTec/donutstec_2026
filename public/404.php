<?php
http_response_code(404);
require_once __DIR__ . '/../includes/functions.php';
$page_title = 'Page Not Found';
$noindex = true;
include __DIR__ . '/../includes/header.php';
?>
<section class="py-5">
  <div class="container text-center py-5">
    <div class="display-1 fw-bold text-dtc-dodger">404</div>
    <h2 class="fw-bold text-dtc-night mb-3">Page Not Found</h2>
    <p class="text-muted mb-4">The page you're looking for doesn't exist or has been moved.</p>
    <a href="<?= BASE_URL ?>/public/index.php" class="btn btn-dtc-primary btn-lg px-4">
      <i class="bi bi-house me-1"></i> Back Home
    </a>
  </div>
</section>
<?php include __DIR__ . '/../includes/footer.php'; ?>