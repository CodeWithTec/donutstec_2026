<?php
require_once __DIR__ . '/../includes/functions.php';

if (is_logged_in()) {
    redirect(current_user_role() === 'admin'
        ? BASE_URL . '/admin/dashboard.php'
        : BASE_URL . '/client/dashboard.php');
}
$old = $_SESSION['old'] ?? [];
unset($_SESSION['old']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Create Account — DonutsTecLiberia</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>
<div class="auth-wrapper">
  <div class="auth-card" style="max-width:520px;">
    <div class="brand-logo">Donuts<span>Tec</span></div>
    <div class="brand-sub">Create your client account</div>

    <?php display_flashes(); ?>

    <form action="<?= BASE_URL ?>/includes/auth.php?action=register" method="POST" novalidate>
      <?= csrf_field() ?>
      <div class="mb-3">
        <label class="form-label">Full name</label>
        <input type="text" name="full_name" class="form-control" required
               value="<?= e($old['full_name'] ?? '') ?>">
      </div>
      <div class="mb-3">
        <label class="form-label">Email address</label>
        <input type="email" name="email" class="form-control" required
               value="<?= e($old['email'] ?? '') ?>">
      </div>
      <div class="row">
        <div class="col-md-6 mb-3">
          <label class="form-label">Phone</label>
          <input type="text" name="phone" class="form-control"
                 value="<?= e($old['phone'] ?? '') ?>">
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label">Company (optional)</label>
          <input type="text" name="company_name" class="form-control"
                 value="<?= e($old['company_name'] ?? '') ?>">
        </div>
      </div>
      <div class="mb-3">
        <label class="form-label">Password <small class="text-muted">(min 8 chars)</small></label>
        <input type="password" name="password" class="form-control" required minlength="8">
      </div>
      <div class="mb-3">
        <label class="form-label">Confirm password</label>
        <input type="password" name="confirm_password" class="form-control" required minlength="8">
      </div>
      <button type="submit" class="btn btn-dtc-primary w-100 py-2">
        <i class="bi bi-person-plus me-1"></i> Create Account
      </button>
    </form>

    <p class="text-center mt-4 mb-0 small">
      Already have an account?
      <a href="<?= BASE_URL ?>/public/login.php" class="text-dtc-dodger fw-semibold">Log in</a>
    </p>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>