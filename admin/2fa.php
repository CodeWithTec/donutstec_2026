<?php
require_once __DIR__ . '/includes/guard.php';
require_once __DIR__ . '/../includes/totp.php';
require_once __DIR__ . '/../includes/qr.php';
$me = current_user();
$db = Database::getConnection();

/* Fresh row */
$s = $db->prepare("SELECT totp_secret, totp_enabled FROM users WHERE id = ?");
$s->execute([$me['id']]);
$row = $s->fetch();

/* If not yet enabled, generate a temp secret (but don't save — wait for verification) */
$tempSecret = $_SESSION['totp_pending'] ?? totp_secret();
$_SESSION['totp_pending'] = $tempSecret;

$qrUri = totp_uri($tempSecret, $me['email'], 'DonutsTecLiberia');

/* Handle verification POST */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form'] ?? '') === 'enable') {
    verify_csrf();
    $code = $_POST['code'] ?? '';
    if (!totp_verify($tempSecret, $code)) {
        set_flash('danger', 'Invalid code. Please try again.');
    } else {
        $db->prepare("UPDATE users SET totp_secret = ?, totp_enabled = 1 WHERE id = ?")
           ->execute([$tempSecret, $me['id']]);
        unset($_SESSION['totp_pending']);
        log_activity('2FA enabled', $me['id']);
        set_flash('success', '2FA enabled successfully.');
        redirect(BASE_URL . '/admin/2fa.php');
    }
}

/* Handle disable */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form'] ?? '') === 'disable') {
    verify_csrf();
    $code = $_POST['code'] ?? '';
    if (!totp_verify($row['totp_secret'] ?? '', $code)) {
        set_flash('danger', 'Invalid code — could not disable.');
    } else {
        $db->prepare("UPDATE users SET totp_secret = NULL, totp_enabled = 0 WHERE id = ?")->execute([$me['id']]);
        log_activity('2FA disabled', $me['id']);
        set_flash('success', '2FA disabled.');
    }
    redirect(BASE_URL . '/admin/2fa.php');
}

$page_title = 'Two-Factor Authentication';
$active_page = '';
include __DIR__ . '/includes/admin_header.php';
?>

<div class="row">
  <div class="col-lg-8">
    <div class="card card-dtc">
      <div class="card-body">
        <h5 class="fw-bold text-dtc-night mb-3"><i class="bi bi-shield-lock me-2"></i> Two-Factor Authentication</h5>

        <?php if ($row['totp_enabled']): ?>
          <div class="alert alert-success small"><i class="bi bi-check-circle me-1"></i> 2FA is <strong>enabled</strong> on your account.</div>
          <p class="small text-muted">To disable 2FA, enter a current code below.</p>
          <form method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="form" value="disable">
            <div class="d-flex gap-2">
              <input type="text" name="code" maxlength="6" class="form-control" placeholder="6-digit code" style="max-width:200px;" required>
              <button class="btn btn-outline-danger">Disable 2FA</button>
            </div>
          </form>
        <?php else: ?>
          <p class="small text-muted mb-3">Scan the QR code below with <strong>Google Authenticator</strong>, <strong>Authy</strong>, or <strong>Microsoft Authenticator</strong>. Then enter the 6-digit code shown in your app to confirm.</p>
          <div class="row g-3 align-items-center mb-3">
            <div class="col-auto"><?= qr_img($qrUri, 200) ?></div>
            <div class="col">
              <div class="small text-muted mb-1">Or enter this secret manually:</div>
              <code style="font-size:15px;background:#EEF4FF;padding:6px 12px;border-radius:6px;display:inline-block;letter-spacing:2px;"><?= e($tempSecret) ?></code>
            </div>
          </div>
          <form method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="form" value="enable">
            <label class="form-label small fw-semibold">Enter 6-digit code from your app</label>
            <div class="d-flex gap-2">
              <input type="text" name="code" maxlength="6" class="form-control" placeholder="123456" style="max-width:200px;letter-spacing:4px;font-size:20px;text-align:center;" required autofocus>
              <button class="btn btn-dtc-primary">Enable 2FA</button>
            </div>
          </form>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>