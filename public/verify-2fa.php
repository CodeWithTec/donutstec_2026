<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/totp.php';

if (empty($_SESSION['2fa_pending_user'])) {
    redirect(BASE_URL . '/public/login.php');
}

$db = Database::getConnection();
$s = $db->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
$s->execute([$_SESSION['2fa_pending_user']]);
$user = $s->fetch();
if (!$user) { unset($_SESSION['2fa_pending_user']); redirect(BASE_URL . '/public/login.php'); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $code = $_POST['code'] ?? '';
    if (totp_verify($user['totp_secret'], $code)) {
        session_regenerate_id(true);
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_name'] = $user['full_name'];
        unset($_SESSION['2fa_pending_user']);
        $db->prepare("UPDATE users SET last_login=NOW() WHERE id=?")->execute([$user['id']]);
        log_activity('Logged in (2FA)', $user['id']);
        redirect($user['role'] === 'admin'
            ? BASE_URL . '/admin/dashboard.php'
            : BASE_URL . '/client/dashboard.php');
    }
    set_flash('danger', 'Invalid 2FA code.');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>2FA Verification — DonutsTecLiberia</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>
<div class="auth-wrapper">
  <div class="auth-card">
    <div class="brand-logo">Donuts<span>Tec</span></div>
    <div class="brand-sub">Enter your 6-digit code</div>
    <?php display_flashes(); ?>
    <form method="POST">
      <?= csrf_field() ?>
      <div class="mb-3">
        <label class="form-label">Authentication Code</label>
        <input type="text" name="code" maxlength="6" class="form-control form-control-lg text-center"
               style="letter-spacing:6px;font-size:24px;" placeholder="000000" autofocus required>
      </div>
      <button class="btn btn-dtc-primary w-100 py-2">Verify</button>
    </form>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>