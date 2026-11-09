<?php
require_once __DIR__ . '/../includes/functions.php';

$token = $_GET['token'] ?? '';
if (!$token) {
    set_flash('danger', 'Missing reset token.');
    redirect(BASE_URL . '/public/forgot-password.php');
}

/* Verify token is valid before showing form */
$db = Database::getConnection();
$stmt = $db->prepare("SELECT id FROM password_resets
                      WHERE token_hash = ? AND used_at IS NULL AND expires_at > NOW() LIMIT 1");
$stmt->execute([hash('sha256', $token)]);
$valid = (bool)$stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Reset Password — DonutsTecLiberia</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>
<div class="auth-wrapper">
  <div class="auth-card">
    <div class="brand-logo">Donuts<span>Tec</span></div>
    <div class="brand-sub">Choose a new password</div>
    <?php display_flashes(); ?>
    <?php if (!$valid): ?>
      <div class="alert alert-danger small">This reset link is invalid or has expired.</div>
      <a href="<?= BASE_URL ?>/public/forgot-password.php" class="btn btn-dtc-primary w-100">Request new link</a>
    <?php else: ?>
      <form action="<?= BASE_URL ?>/includes/password_reset.php?action=reset" method="POST">
        <?= csrf_field() ?>
        <input type="hidden" name="token" value="<?= e($token) ?>">
        <div class="mb-3">
          <label class="form-label">New Password <small class="text-muted">(min 8 chars)</small></label>
          <input type="password" name="password" class="form-control" required minlength="8" autofocus>
        </div>
        <div class="mb-3">
          <label class="form-label">Confirm Password</label>
          <input type="password" name="confirm_password" class="form-control" required minlength="8">
        </div>
        <button type="submit" class="btn btn-dtc-primary w-100 py-2">
          <i class="bi bi-shield-check me-1"></i> Update Password
        </button>
      </form>
    <?php endif; ?>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>