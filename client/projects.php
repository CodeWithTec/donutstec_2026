<?php
require_once __DIR__ . '/includes/guard.php';
$me = current_user();
$db = Database::getConnection();

$statusFilter = sanitize($_GET['status'] ?? '');
$where = "WHERE client_id = ?";
$params = [$me['id']];
if (in_array($statusFilter, ['pending','in_progress','on_hold','completed','cancelled'], true)) {
    $where .= " AND status = ?";
    $params[] = $statusFilter;
}

$stmt = $db->prepare("SELECT * FROM projects $where ORDER BY updated_at DESC");
$stmt->execute($params);
$projects = $stmt->fetchAll();

$page_title = 'My Projects';
$active_page = 'projects';
include __DIR__ . '/includes/client_header.php';
?>

<div class="d-flex flex-wrap gap-2 mb-4">
  <a href="?" class="btn btn-sm <?= !$statusFilter?'btn-dtc-primary':'btn-dtc-outline' ?>">All</a>
  <?php foreach (['pending'=>'Pending','in_progress'=>'In Progress','on_hold'=>'On Hold','completed'=>'Completed'] as $k=>$v): ?>
    <a href="?status=<?= $k ?>" class="btn btn-sm <?= $statusFilter===$k?'btn-dtc-primary':'btn-dtc-outline' ?>"><?= $v ?></a>
  <?php endforeach; ?>
</div>

<?php if (empty($projects)): ?>
  <div class="card card-dtc text-center p-5">
    <i class="bi bi-folder-x fs-1 text-muted"></i>
    <p class="mt-3 text-muted">No projects found for this filter.</p>
    <a href="<?= BASE_URL ?>/public/quote.php" class="btn btn-dtc-primary mx-auto" style="width:fit-content;">Start a Project</a>
  </div>
<?php else: ?>
  <div class="row g-4">
    <?php foreach ($projects as $p):
      $badge = ['pending'=>'bg-secondary','in_progress'=>'bg-primary','on_hold'=>'bg-warning text-dark','completed'=>'bg-success','cancelled'=>'bg-danger'][$p['status']] ?? 'bg-secondary';
    ?>
      <div class="col-md-6 col-xl-4">
        <div class="card card-dtc h-100">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-2">
              <span class="badge badge-dtc-sky"><?= e($p['service_type']) ?></span>
              <span class="badge <?= $badge ?>"><?= str_replace('_',' ', strtoupper($p['status'])) ?></span>
            </div>
            <h5 class="fw-bold text-dtc-night"><?= e($p['title']) ?></h5>
            <p class="small text-muted"><?= e($p['description']) ?></p>
            <div class="progress mb-1" style="height:8px;">
              <div class="progress-bar progress-bar-dtc" style="width:<?= (int)$p['progress'] ?>%;"></div>
            </div>
            <div class="small text-muted mb-3"><?= (int)$p['progress'] ?>% complete</div>
            <?php if ($p['deadline']): ?>
              <div class="small text-muted mb-3">
                <i class="bi bi-calendar-event me-1"></i>
                Deadline: <?= date('M d, Y', strtotime($p['deadline'])) ?>
              </div>
            <?php endif; ?>
            <a href="<?= BASE_URL ?>/client/project-detail.php?id=<?= $p['id'] ?>" class="btn btn-dtc-primary btn-sm w-100">
              View Project <i class="bi bi-arrow-right ms-1"></i>
            </a>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php include __DIR__ . '/includes/client_footer.php'; ?>