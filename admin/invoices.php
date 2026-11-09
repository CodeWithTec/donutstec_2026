<?php
require_once __DIR__.'/includes/guard.php';
$db = Database::getConnection();

if ($_SERVER['REQUEST_METHOD']==='POST') {
    verify_csrf();
    $id = (int)($_POST['id'] ?? 0);
    $st = in_array($_POST['status'] ?? '', ['unpaid','paid','overdue','cancelled'], true) ? $_POST['status'] : 'unpaid';
    if ($id) {
        $paidAt = $st==='paid' ? date('Y-m-d H:i:s') : null;
        $db->prepare("UPDATE invoices SET status=?, paid_at=? WHERE id=?")->execute([$st,$paidAt,$id]);
        set_flash('success','Invoice updated.');
    }
    redirect(BASE_URL.'/admin/invoices.php');
}

$rows = $db->query("SELECT i.*, p.title AS project_title, u.full_name AS client_name
                    FROM invoices i JOIN projects p ON i.project_id=p.id JOIN users u ON p.client_id=u.id
                    ORDER BY i.created_at DESC")->fetchAll();
$totalPaid = 0; $totalDue = 0;
foreach ($rows as $r) { if($r['status']==='paid') $totalPaid += $r['amount']; elseif(in_array($r['status'],['unpaid','overdue'])) $totalDue += $r['amount']; }

$page_title='Invoices'; $active_page='invoices';
include __DIR__.'/includes/admin_header.php';
?>

<div class="row g-3 mb-3">
  <div class="col-md-4"><div class="stat-card"><div class="stat-value text-success">$<?= number_format($totalPaid,2) ?></div><div class="stat-label">Total Paid</div></div></div>
  <div class="col-md-4"><div class="stat-card"><div class="stat-value text-warning">$<?= number_format($totalDue,2) ?></div><div class="stat-label">Outstanding</div></div></div>
  <div class="col-md-4"><div class="stat-card"><div class="stat-value"><?= count($rows) ?></div><div class="stat-label">Total Invoices</div></div></div>
</div>

<div class="card card-dtc">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="small text-muted" style="background:#F8F9FC;"><tr><th>Invoice #</th><th>Client</th><th>Project</th><th>Amount</th><th>Status</th><th>Due</th><th>Actions</th></tr></thead>
        <tbody>
        <?php if (empty($rows)): ?><tr><td colspan="7" class="text-center py-4 text-muted">No invoices yet.</td></tr>
        <?php else: foreach ($rows as $r): $sb=['unpaid'=>'bg-warning text-dark','paid'=>'bg-success','overdue'=>'bg-danger','cancelled'=>'bg-secondary'][$r['status']] ?? 'bg-secondary'; ?>
          <tr>
            <td class="small fw-semibold"><?= e($r['invoice_no']) ?></td>
            <td class="small"><?= e($r['client_name']) ?></td>
            <td class="small"><?= e($r['project_title']) ?></td>
            <td class="small fw-bold">$<?= number_format((float)$r['amount'],2) ?></td>
            <td><span class="badge <?= $sb ?>"><?= strtoupper($r['status']) ?></span></td>
            <td class="small text-muted"><?= $r['due_date'] ? date('M d, Y', strtotime($r['due_date'])) : '—' ?></td>
            <td>
              <form method="POST" class="d-flex gap-1">
                <?= csrf_field() ?><input type="hidden" name="id" value="<?= $r['id'] ?>">
                <select name="status" class="form-select form-select-sm">
                  <?php foreach (['unpaid','paid','overdue','cancelled'] as $s): ?><option value="<?= $s ?>" <?= $r['status']===$s?'selected':'' ?>><?= ucfirst($s) ?></option><?php endforeach; ?>
                </select>
                <button class="btn btn-sm btn-dtc-primary">Save</button>
                <a href="<?= BASE_URL ?>/admin/invoice-pdf.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-outline-secondary" title="Download PDF">
                <i class="bi bi-file-pdf"></i>
                </a>
              </form>
            </td>
          </tr>
        <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include __DIR__.'/includes/admin_footer.php'; ?>