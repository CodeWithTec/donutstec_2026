<?php
require_once __DIR__.'/includes/guard.php';
$db = Database::getConnection();

if ($_SERVER['REQUEST_METHOD']==='POST') {
    verify_csrf();
    $id = (int)($_POST['id'] ?? 0);
    $st = in_array($_POST['status'] ?? '', ['new','contacted','converted','rejected'], true) ? $_POST['status'] : 'new';
    if ($id) { $db->prepare("UPDATE quotes SET status=? WHERE id=?")->execute([$st, $id]); set_flash('success','Quote updated.'); }
    redirect(BASE_URL.'/admin/quotes.php');
}

$quotes = $db->query("SELECT * FROM quotes ORDER BY created_at DESC")->fetchAll();
$page_title='Quote Requests'; $active_page='quotes';
include __DIR__.'/includes/admin_header.php';
?>

<div class="card card-dtc">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="small text-muted" style="background:#F8F9FC;"><tr><th>Name</th><th>Contact</th><th>Service</th><th>Budget</th><th>Details</th><th>Status</th><th></th></tr></thead>
        <tbody>
        <?php if (empty($quotes)): ?><tr><td colspan="7" class="text-center py-4 text-muted">No quotes yet.</td></tr>
        <?php else: foreach ($quotes as $q):
          $sb=['new'=>'bg-primary','contacted'=>'bg-info text-dark','converted'=>'bg-success','rejected'=>'bg-secondary'][$q['status']] ?? 'bg-secondary';
        ?>
          <tr>
            <td><div class="fw-semibold small"><?= e($q['name']) ?></div><div class="small text-muted"><?= date('M d, Y', strtotime($q['created_at'])) ?></div></td>
            <td class="small"><?= e($q['email']) ?><br><span class="text-muted"><?= e($q['phone']) ?></span></td>
            <td class="small"><?= e($q['service_type']) ?></td>
            <td class="small"><?= e($q['budget_range'] ?: '—') ?></td>
            <td class="small text-muted" style="max-width:260px;"><div style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= e($q['details']) ?></div></td>
            <td><span class="badge <?= $sb ?>"><?= strtoupper($q['status']) ?></span></td>
            <td>
              <details><summary class="btn btn-sm btn-dtc-outline" style="cursor:pointer;">View</summary>
                <div class="p-2 small mt-2" style="max-width:320px;background:#F8F9FC;border-radius:6px;white-space:pre-wrap;"><?= e($q['details']) ?></div>
                <form method="POST" class="d-flex gap-1 mt-2">
                  <?= csrf_field() ?><input type="hidden" name="id" value="<?= $q['id'] ?>">
                  <select name="status" class="form-select form-select-sm">
                    <?php foreach (['new','contacted','converted','rejected'] as $s): ?><option value="<?= $s ?>" <?= $q['status']===$s?'selected':'' ?>><?= ucfirst($s) ?></option><?php endforeach; ?>
                  </select>
                  <button class="btn btn-sm btn-dtc-primary">Save</button>
                </form>
              </details>
            </td>
          </tr>
        <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include __DIR__.'/includes/admin_footer.php'; ?>