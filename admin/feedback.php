<?php
require_once __DIR__ . '/includes/guard.php';
$db = Database::getConnection();

if (isset($_GET['toggle'])) {
    $db->prepare("UPDATE project_feedback SET is_public = 1 - is_public WHERE id = ?")->execute([(int)$_GET['toggle']]);
    set_flash('success', 'Visibility toggled.');
    redirect(BASE_URL . '/admin/feedback.php');
}

$rows = $db->query("
  SELECT f.*, p.title AS project_title, u.full_name AS client_name, u.company_name
  FROM project_feedback f
  JOIN projects p ON f.project_id = p.id
  JOIN users u ON f.client_id = u.id
  ORDER BY f.created_at DESC
")->fetchAll();

$page_title = 'Project Feedback';
$active_page = '';
include __DIR__ . '/includes/admin_header.php';
?>

<div class="card card-dtc">
  <div class="card-body p-0">
    <?php if (empty($rows)): ?>
      <p class="p-4 text-muted mb-0">No feedback yet.</p>
    <?php else: foreach ($rows as $r): ?>
      <div class="p-3 border-bottom">
        <div class="d-flex justify-content-between">
          <div>
            <div class="fw-semibold"><?= e($r['client_name']) ?><?= $r['company_name'] ? ' — '.e($r['company_name']) : '' ?></div>
            <div class="small text-muted">Project: <?= e($r['project_title']) ?> · <?= date('M d, Y', strtotime($r['created_at'])) ?></div>
          </div>
          <div class="text-end">
            <div class="text-warning fs-6"><?= str_repeat('★', (int)$r['rating']) ?></div>
            <a href="?toggle=<?= $r['id'] ?>" class="btn btn-sm <?= $r['is_public'] ? 'btn-success' : 'btn-outline-secondary' ?> mt-1">
              <?= $r['is_public'] ? 'Public' : 'Private' ?>
            </a>
          </div>
        </div>
        <?php if ($r['comments']): ?>
          <div class="small mt-2" style="white-space:pre-wrap;"><?= e($r['comments']) ?></div>
        <?php endif; ?>
      </div>
    <?php endforeach; endif; ?>
  </div>
</div>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>