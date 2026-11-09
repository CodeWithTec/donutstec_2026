<?php
require_once __DIR__.'/includes/guard.php';
$db = Database::getConnection();

if (isset($_GET['read'])) {
    $db->prepare("UPDATE contact_messages SET is_read=1 WHERE id=?")->execute([(int)$_GET['read']]);
    redirect(BASE_URL.'/admin/contact-messages.php');
}
if (isset($_GET['del'])) {
    $db->prepare("DELETE FROM contact_messages WHERE id=?")->execute([(int)$_GET['del']]);
    set_flash('success','Message deleted.');
    redirect(BASE_URL.'/admin/contact-messages.php');
}

$msgs = $db->query("SELECT * FROM contact_messages ORDER BY is_read ASC, created_at DESC")->fetchAll();
$page_title='Contact Messages'; $active_page='contact';
include __DIR__.'/includes/admin_header.php';
?>

<div class="card card-dtc">
  <div class="card-body p-0">
    <?php if (empty($msgs)): ?><p class="p-4 text-muted mb-0">No messages yet.</p>
    <?php else: foreach ($msgs as $m): ?>
      <div class="p-3 border-bottom <?= !$m['is_read']?'bg-light':'' ?>">
        <div class="d-flex justify-content-between">
          <div>
            <div class="fw-semibold"><?= e($m['name']) ?> <?php if(!$m['is_read']): ?><span class="badge bg-dtc-dodger ms-1">New</span><?php endif; ?></div>
            <div class="small text-muted"><?= e($m['email']) ?> <?= $m['phone'] ? '· '.e($m['phone']) : '' ?> · <?= date('M d, Y g:i A', strtotime($m['created_at'])) ?></div>
          </div>
          <div>
            <?php if (!$m['is_read']): ?><a href="?read=<?= $m['id'] ?>" class="btn btn-sm btn-dtc-outline">Mark Read</a><?php endif; ?>
            <a href="?del=<?= $m['id'] ?>" class="btn btn-sm btn-outline-danger" data-confirm="Delete?"><i class="bi bi-trash"></i></a>
          </div>
        </div>
        <div class="fw-semibold small mt-2"><?= e($m['subject']) ?></div>
        <div class="small mt-1" style="white-space:pre-wrap;"><?= e($m['message']) ?></div>
      </div>
    <?php endforeach; endif; ?>
  </div>
</div>

<?php include __DIR__.'/includes/admin_footer.php'; ?>