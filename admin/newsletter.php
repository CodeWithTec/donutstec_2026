<?php
require_once __DIR__.'/includes/guard.php';
$db = Database::getConnection();

if (isset($_GET['export'])) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=newsletter_'.date('Ymd').'.csv');
    $out = fopen('php://output','w');
    fputcsv($out, ['Email','Subscribed At','Active']);
    foreach ($db->query("SELECT * FROM newsletter ORDER BY subscribed_at DESC") as $r) {
        fputcsv($out, [$r['email'], $r['subscribed_at'], $r['is_active']?'Yes':'No']);
    }
    fclose($out); exit;
}

if (isset($_GET['del'])) { $db->prepare("DELETE FROM newsletter WHERE id=?")->execute([(int)$_GET['del']]); set_flash('success','Removed.'); redirect(BASE_URL.'/admin/newsletter.php'); }

$rows = $db->query("SELECT * FROM newsletter ORDER BY subscribed_at DESC")->fetchAll();
$page_title='Newsletter'; $active_page='newsletter';
include __DIR__.'/includes/admin_header.php';
?>

<div class="d-flex justify-content-between mb-3">
  <h5 class="fw-bold text-dtc-night mb-0"><?= count($rows) ?> subscribers</h5>
  <a href="?export=1" class="btn btn-sm btn-dtc-primary"><i class="bi bi-download me-1"></i> Export CSV</a>
</div>

<div class="card card-dtc">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead class="small text-muted" style="background:#F8F9FC;"><tr><th>Email</th><th>Subscribed At</th><th>Active</th><th></th></tr></thead>
        <tbody>
        <?php if (empty($rows)): ?><tr><td colspan="4" class="text-center py-4 text-muted">No subscribers yet.</td></tr>
        <?php else: foreach ($rows as $r): ?>
          <tr>
            <td class="small"><?= e($r['email']) ?></td>
            <td class="small text-muted"><?= date('M d, Y', strtotime($r['subscribed_at'])) ?></td>
            <td><?= $r['is_active']?'<span class="badge bg-success">Yes</span>':'<span class="badge bg-secondary">No</span>' ?></td>
            <td><a href="?del=<?= $r['id'] ?>" class="btn btn-sm btn-outline-danger" data-confirm="Remove this subscriber?"><i class="bi bi-trash"></i></a></td>
          </tr>
        <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include __DIR__.'/includes/admin_footer.php'; ?>