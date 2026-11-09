<?php
require_once __DIR__ . '/includes/guard.php';
$db = Database::getConnection();

$filter = sanitize($_GET['status'] ?? '');
$sql = "SELECT p.*, u.full_name AS client_name
        FROM projects p JOIN users u ON p.client_id=u.id";
$params = [];
if (in_array($filter, ['pending','in_progress','on_hold','completed','cancelled'], true)) {
    $sql .= " WHERE p.status = ?"; $params[] = $filter;
}
$sql .= " ORDER BY p.updated_at DESC";
$stmt = $db->prepare($sql); $stmt->execute($params);
$projects = $stmt->fetchAll();

$page_title  = 'Projects';
$active_page = 'projects';
include __DIR__ . '/includes/admin_header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
  <div class="d-flex gap-1 flex-wrap">
    <a href="?" class="btn btn-sm <?= !$filter?'btn-dtc-primary':'btn-dtc-outline' ?>">All</a>
    <?php foreach (['pending'=>'Pending','in_progress'=>'In Progress','on_hold'=>'On Hold','completed'=>'Completed','cancelled'=>'Cancelled'] as $k=>$v): ?>
      <a href="?status=<?= $k ?>" class="btn btn-sm <?= $filter===$k?'btn-dtc-primary':'btn-dtc-outline' ?>"><?= $v ?></a>
    <?php endforeach; ?>
  </div>
  <a href="<?= BASE_URL ?>/admin/project-edit.php" class="btn btn-dtc-primary btn-sm"><i class="bi bi-plus-circle me-1"></i> New Project</a>
</div>

<div class="card card-dtc">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="small text-muted" style="background:#F8F9FC;">
          <tr><th>Project</th><th>Client</th><th>Status</th><th>Progress</th><th>Deadline</th><th></th></tr>
        </thead>
        <tbody>
          <?php if (empty($projects)): ?>
            <tr><td colspan="6" class="text-center py-4 text-muted">No projects found.</td></tr>
          <?php else: foreach ($projects as $p):
            $sb = ['pending'=>'bg-secondary','in_progress'=>'bg-primary','on_hold'=>'bg-warning text-dark','completed'=>'bg-success','cancelled'=>'bg-danger'][$p['status']] ?? 'bg-secondary';
          ?>
            <tr>
              <td>
                <div class="fw-semibold"><?= e($p['title']) ?></div>
                <div class="small text-muted"><?= e($p['service_type']) ?></div>
              </td>
              <td class="small"><?= e($p['client_name']) ?></td>
              <td><span class="badge <?= $sb ?>"><?= strtoupper(str_replace('_',' ',$p['status'])) ?></span></td>
              <td style="min-width:140px;">
                <div class="progress" style="height:6px;">
                  <div class="progress-bar progress-bar-dtc" style="width:<?= (int)$p['progress'] ?>%;"></div>
                </div>
                <div class="small text-muted mt-1"><?= (int)$p['progress'] ?>%</div>
              </td>
              <td class="small text-muted"><?= $p['deadline'] ? date('M d, Y', strtotime($p['deadline'])) : '—' ?></td>
              <td><a href="<?= BASE_URL ?>/admin/project-edit.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-dtc-outline">Edit</a></td>
            </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>