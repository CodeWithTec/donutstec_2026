<?php
require_once __DIR__ . '/includes/guard.php';
$db = Database::getConnection();

$q = sanitize($_GET['q'] ?? '');
$like = '%' . $q . '%';

$results = ['users' => [], 'projects' => [], 'invoices' => [], 'tickets' => []];

if (strlen($q) >= 2) {
    $s = $db->prepare("SELECT id, full_name, email, company_name FROM users WHERE role='client' AND (full_name LIKE ? OR email LIKE ? OR company_name LIKE ?) LIMIT 10");
    $s->execute([$like, $like, $like]);
    $results['users'] = $s->fetchAll();

    $s = $db->prepare("SELECT p.id, p.title, p.status, u.full_name AS client_name FROM projects p JOIN users u ON p.client_id=u.id WHERE p.title LIKE ? OR p.description LIKE ? LIMIT 10");
    $s->execute([$like, $like]);
    $results['projects'] = $s->fetchAll();

    $s = $db->prepare("SELECT i.id, i.invoice_no, i.amount, i.status, p.title AS project FROM invoices i JOIN projects p ON i.project_id=p.id WHERE i.invoice_no LIKE ? LIMIT 10");
    $s->execute([$like]);
    $results['invoices'] = $s->fetchAll();

    $s = $db->prepare("SELECT t.id, t.subject, t.status, u.full_name AS client_name FROM tickets t JOIN users u ON t.client_id=u.id WHERE t.subject LIKE ? OR t.message LIKE ? LIMIT 10");
    $s->execute([$like, $like]);
    $results['tickets'] = $s->fetchAll();
}

$page_title = 'Search';
$active_page = '';
include __DIR__ . '/includes/admin_header.php';
?>

<form method="GET" class="mb-4">
  <div class="input-group">
    <input type="text" name="q" class="form-control form-control-lg" value="<?= e($q) ?>" placeholder="Search users, projects, invoices, tickets..." autofocus>
    <button class="btn btn-dtc-primary px-4"><i class="bi bi-search"></i></button>
  </div>
</form>

<?php if (strlen($q) < 2): ?>
  <p class="text-muted">Type at least 2 characters to search.</p>
<?php else:
  $total = array_sum(array_map('count', $results));
  if (!$total): ?>
    <p class="text-muted">No results for "<?= e($q) ?>".</p>
  <?php else: ?>
    <p class="text-muted"><?= $total ?> result(s) for "<strong><?= e($q) ?></strong>"</p>

    <?php if ($results['users']): ?>
      <div class="card card-dtc mb-3"><div class="card-body">
        <h6 class="fw-bold text-dtc-night mb-3"><i class="bi bi-people me-1"></i> Clients (<?= count($results['users']) ?>)</h6>
        <?php foreach ($results['users'] as $u): ?>
          <div class="border-bottom py-2 small">
            <strong><?= e($u['full_name']) ?></strong> — <?= e($u['email']) ?>
            <?php if ($u['company_name']): ?><span class="text-muted"> · <?= e($u['company_name']) ?></span><?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div></div>
    <?php endif; ?>

    <?php if ($results['projects']): ?>
      <div class="card card-dtc mb-3"><div class="card-body">
        <h6 class="fw-bold text-dtc-night mb-3"><i class="bi bi-kanban me-1"></i> Projects (<?= count($results['projects']) ?>)</h6>
        <?php foreach ($results['projects'] as $p): ?>
          <div class="border-bottom py-2 small d-flex justify-content-between">
            <span><strong><?= e($p['title']) ?></strong> — <?= e($p['client_name']) ?> <span class="badge bg-secondary ms-1"><?= $p['status'] ?></span></span>
            <a href="<?= BASE_URL ?>/admin/project-edit.php?id=<?= $p['id'] ?>" class="text-dtc-dodger">Edit</a>
          </div>
        <?php endforeach; ?>
      </div></div>
    <?php endif; ?>

    <?php if ($results['invoices']): ?>
      <div class="card card-dtc mb-3"><div class="card-body">
        <h6 class="fw-bold text-dtc-night mb-3"><i class="bi bi-receipt me-1"></i> Invoices (<?= count($results['invoices']) ?>)</h6>
        <?php foreach ($results['invoices'] as $i): ?>
          <div class="border-bottom py-2 small">
            <strong><?= e($i['invoice_no']) ?></strong> — $<?= number_format((float)$i['amount'],2) ?>
            (<?= e($i['project']) ?>) <span class="badge bg-secondary ms-1"><?= $i['status'] ?></span>
          </div>
        <?php endforeach; ?>
      </div></div>
    <?php endif; ?>

    <?php if ($results['tickets']): ?>
      <div class="card card-dtc mb-3"><div class="card-body">
        <h6 class="fw-bold text-dtc-night mb-3"><i class="bi bi-life-preserver me-1"></i> Tickets (<?= count($results['tickets']) ?>)</h6>
        <?php foreach ($results['tickets'] as $t): ?>
          <div class="border-bottom py-2 small d-flex justify-content-between">
            <span><strong>#<?= $t['id'] ?> — <?= e($t['subject']) ?></strong> (<?= e($t['client_name']) ?>)</span>
            <a href="<?= BASE_URL ?>/admin/ticket-detail.php?id=<?= $t['id'] ?>" class="text-dtc-dodger">Open</a>
          </div>
          
        <?php endforeach; ?>
      </div></div>
    <?php endif; ?>
  <?php endif;
endif; ?>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>