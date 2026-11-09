<?php
require_once __DIR__ . '/includes/guard.php';
$me = current_user();
$db = Database::getConnection();

$db->prepare("UPDATE notifications SET is_read=1 WHERE user_id=?")->execute([$me['id']]);

$stmt = $db->prepare("SELECT * FROM notifications WHERE user_id=? ORDER BY created_at DESC LIMIT 200");
$stmt->execute([$me['id']]);
$notifs = $stmt->fetchAll();

$page_title = 'Notifications';
$active_page = '';
include __DIR__ . '/includes/admin_header.php';
?>

<div class="card card-dtc">
  <div class="card-body">
    <?php if (empty($notifs)): ?>
      <div class="text-center text-muted py-4"><i class="bi bi-bell-slash fs-1"></i><p class="mt-2">No notifications yet.</p></div>
    <?php else: foreach ($notifs as $n): ?>
      <div class="d-flex border-bottom py-3">
        <div class="me-3 fs-4 text-dtc-dodger"><i class="bi bi-bell"></i></div>
        <div class="flex-grow-1">
          <div class="fw-semibold text-dtc-night small"><?= e($n['title']) ?></div>
          <div class="small"><?= e($n['message']) ?></div>
          <div class="small text-muted mt-1"><?= date('M d, Y g:i A', strtotime($n['created_at'])) ?></div>
        </div>
        <?php if (!empty($n['link_url'])): ?>
          <a href="<?= BASE_URL . e($n['link_url']) ?>" class="btn btn-sm btn-dtc-outline align-self-center">View</a>
        <?php endif; ?>
      </div>
    <?php endforeach; endif; ?>
  </div>
</div>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>