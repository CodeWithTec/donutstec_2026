<?php
require_once __DIR__ . '/includes/guard.php';
$me = current_user();
$db = Database::getConnection();

/* Handle profile update */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form'] ?? '') === 'profile') {
    verify_csrf();
    $full_name = sanitize($_POST['full_name'] ?? '');
    $phone     = sanitize($_POST['phone'] ?? '');
    $company   = sanitize($_POST['company_name'] ?? '');

    if (strlen($full_name) < 2) {
        set_flash('danger', 'Name is too short.');
        redirect(BASE_URL . '/client/profile.php');
    }

    $db->prepare("UPDATE users SET full_name = ?, phone = ?, company_name = ? WHERE id = ?")
       ->execute([$full_name, $phone, $company, $me['id']]);
    set_flash('success', 'Profile updated successfully.');
    redirect(BASE_URL . '/client/profile.php');
}

/* Handle password change */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form'] ?? '') === 'password') {
    verify_csrf();
    $current = $_POST['current_password'] ?? '';
    $new     = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (!password_verify($current, $me['password'])) {
        set_flash('danger', 'Current password is incorrect.');
        redirect(BASE_URL . '/client/profile.php');
    }
    if (strlen($new) < 8) {
        set_flash('danger', 'New password must be at least 8 characters.');
        redirect(BASE_URL . '/client/profile.php');
    }
    if ($new !== $confirm) {
        set_flash('danger', 'Passwords do not match.');
        redirect(BASE_URL . '/client/profile.php');
    }

    $hash = password_hash($new, PASSWORD_BCRYPT);
    $db->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([$hash, $me['id']]);
    set_flash('success', 'Password changed successfully.');
    redirect(BASE_URL . '/client/profile.php');
}

$page_title = 'My Profile';
$active_page = 'profile';
include __DIR__ . '/includes/client_header.php';
?>

<div class="row g-4">
  <div class="col-lg-6">
    <div class="card card-dtc">
      <div class="card-body">
        <h5 class="fw-bold text-dtc-night mb-3">Profile Information</h5>
        <form method="POST">
          <input type="hidden" name="form" value="profile">
          <?= csrf_field() ?>
          <div class="mb-3">
            <label class="form-label small fw-semibold">Full Name</label>
            <input type="text" name="full_name" class="form-control" value="<?= e($me['full_name']) ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label small fw-semibold">Email</label>
            <input type="email" class="form-control" value="<?= e($me['email']) ?>" disabled>
            <div class="form-text small">Email cannot be changed. Contact support if needed.</div>
          </div>
          <div class="mb-3">
            <label class="form-label small fw-semibold">Phone</label>
            <input type="text" name="phone" class="form-control" value="<?= e($me['phone']) ?>">
          </div>
          <div class="mb-3">
            <label class="form-label small fw-semibold">Company</label>
            <input type="text" name="company_name" class="form-control" value="<?= e($me['company_name']) ?>">
          </div>
          <button class="btn btn-dtc-primary">Save Changes</button>
        </form>
      </div>
    </div>
  </div>

  <div class="col-lg-6">
    <div class="card card-dtc">
      <div class="card-body">
        <h5 class="fw-bold text-dtc-night mb-3">Change Password</h5>
        <form method="POST">
          <input type="hidden" name="form" value="password">
          <?= csrf_field() ?>
          <div class="mb-3">
            <label class="form-label small fw-semibold">Current Password</label>
            <input type="password" name="current_password" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label small fw-semibold">New Password <small class="text-muted">(min 8 chars)</small></label>
            <input type="password" name="new_password" class="form-control" required minlength="8">
          </div>
          <div class="mb-3">
            <label class="form-label small fw-semibold">Confirm New Password</label>
            <input type="password" name="confirm_password" class="form-control" required minlength="8">
          </div>
          <button class="btn btn-dtc-primary">Update Password</button>
        </form>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/includes/client_footer.php'; ?>