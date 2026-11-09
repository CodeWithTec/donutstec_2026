<?php
require_once __DIR__ . '/../includes/functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Forgot Password — DonutsTecLiberia</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>
<div class="auth-wrapper">
  <div class="auth-card">
    <div class="brand-logo">Donuts<span>Tec</span></div>
    <div class="brand-sub">Reset your password</div>
    <?php display_flashes(); ?>
    <p class="small text-muted mb-3">Enter the email you registered with, and we'll send you a secure link to reset your password.</p>
    <form action="<?= BASE_URL ?>/includes/password_reset.php?action=request" method="POST">
      <?= csrf_field() ?>
      <div class="mb-3">
        <label class="form-label">Email address</label>
        <input type="email" name="email" class="form-control" required autofocus>
      </div>
      <button type="submit" class="btn btn-dtc-primary w-100 py-2">
        <i class="bi bi-send me-1"></i> Send Reset Link
      </button>
    </form>
    <p class="text-center mt-3 mb-0 small">
      <a href="<?= BASE_URL ?>/public/login.php" class="text-muted">
        <i class="bi bi-arrow-left"></i> Back to login
      </a>
    </p>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>