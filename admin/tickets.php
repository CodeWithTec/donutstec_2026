<?php
require_once __DIR__ . '/includes/guard.php';
$db = Database::getConnection();

$status = sanitize($_GET['status'] ?? '');
$sql = "SELECT t.*, u.full_name AS client_name FROM tickets t JOIN users u ON t.client_id=u.id";
$params = [];
if (in_array($status, ['open','in_progress','resolved','closed'], true)) { $sql .= " WHERE t.status=?"; $params[]=$status; }
$sql .= " ORDER BY t.updated_at DESC";
$s = $db->prepare($sql); $s->execute($params); $tickets = $s->fetchAll();

$page_title='Tickets'; $active_page='tickets';
include __DIR__.'/includes/admin_header.php';
?>

<div class="d-flex gap-1 mb-3 flex-wrap">
  <a href="?" class="btn btn-sm <?= !$status?'btn-dtc-primary':'btn-dtc-outline' ?>">All</a>
  <?php foreach (['open'=>'Open','in_progress'=>'In Progress','resolved'=>'Resolved','closed'=>'Closed'] as $k=>$v): ?>
    <a href="?status=<?= $k ?>" class="btn btn-sm <?= $status===$k?'btn-dtc-primary':'btn-dtc-outline' ?>"><?= $v ?></a>
  <?php endforeach; ?>
</div>

<div class="card card-dtc">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="small text-muted" style="background:#F8F9FC;"><tr><th>#</th><th>Subject</th><th>Client</th><th>Priority</th><th>Status</th><th>Updated</th><th></th></tr></thead>
        <tbody>
        <?php if (empty($tickets)): ?><tr><td colspan="7" class="text-center py-4 text-muted">No tickets.</td></tr>
        <?php else: foreach ($tickets as $t):
          $pb=['low'=>'bg-secondary','medium'=>'bg-info text-dark','high'=>'bg-warning text-dark','urgent'=>'bg-danger'][$t['priority']] ?? 'bg-secondary';
          $sb=['open'=>'bg-primary','in_progress'=>'bg-info text-dark','resolved'=>'bg-success','closed'=>'bg-secondary'][$t['status']] ?? 'bg-secondary';
        ?>
          <tr>
            <td>#<?= $t['id'] ?></td>
            <td class="small fw-semibold"><?= e($t['subject']) ?></td>
            <td class="small"><?= e($t['client_name']) ?></td>
            <td><span class="badge <?= $pb ?>"><?= strtoupper($t['priority']) ?></span></td>
            <td><span class="badge <?= $sb ?>"><?= strtoupper(str_replace('_',' ',$t['status'])) ?></span></td>
            <td class="small text-muted"><?= date('M d', strtotime($t['updated_at'])) ?></td>
            <td><a href="<?= BASE_URL ?>/admin/ticket-detail.php?id=<?= $t['id'] ?>" class="btn btn-sm btn-dtc-outline">Open</a></td>
          </tr>
        <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include __DIR__.'/includes/admin_footer.php'; ?>