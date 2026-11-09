<?php
require_once __DIR__ . '/../includes/functions.php';

if (is_logged_in()) {
    redirect(current_user_role() === 'admin'
        ? BASE_URL . '/admin/dashboard.php'
        : BASE_URL . '/client/dashboard.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login — DonutsTecLiberia</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>
<div class="auth-wrapper">
  <div class="auth-card">
    <div class="brand-logo">Donuts<span>Tec</span></div>
    <div class="brand-sub">Welcome back — please log in</div>

    <?php display_flashes(); ?>

    <form action="<?= BASE_URL ?>/includes/auth.php?action=login" method="POST" novalidate>
      <?= csrf_field() ?>
      <div class="mb-3">
        <label class="form-label">Email address</label>
        <input type="email" name="email" class="form-control" required autofocus>
      </div>
      <div class="mb-3">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control" required>
      </div>
      <div class="d-flex justify-content-between mb-3">
        <div class="form-check">
          <input class="form-check-input" type="checkbox" id="remember">
          <label class="form-check-label small" for="remember">Remember me</label>
        </div>
        <a href="<?= BASE_URL ?>/public/forgot-password.php" class="small text-dtc-dodger">Forgot password?</a>
      </div>
      <button type="submit" class="btn btn-dtc-primary w-100 py-2">
        <i class="bi bi-box-arrow-in-right me-1"></i> Sign In
      </button>
    </form>

    <p class="text-center mt-4 mb-0 small">
      Don't have an account?
      <a href="<?= BASE_URL ?>/public/register.php" class="text-dtc-dodger fw-semibold">Create one</a>
    </p>
    <p class="text-center mt-2 mb-0 small">
      <a href="<?= BASE_URL ?>/public/index.php" class="text-muted">
        <i class="bi bi-arrow-left"></i> Back to site
      </a>
    </p>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>