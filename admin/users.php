<?php
require_once __DIR__ . '/includes/guard.php';
$db = Database::getConnection();

/* Handle user toggles */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $id     = (int)($_POST['id'] ?? 0);
    $action = $_POST['action'] ?? '';

    if ($id && $id !== current_user()['id']) {
        if ($action === 'ban')         $db->prepare("UPDATE users SET status='banned' WHERE id=?")->execute([$id]);
        if ($action === 'activate')    $db->prepare("UPDATE users SET status='active' WHERE id=?")->execute([$id]);
        if ($action === 'delete')      $db->prepare("DELETE FROM users WHERE id=? AND role='client'")->execute([$id]);
        log_activity("Admin $action user #$id", current_user()['id']);
        set_flash('success', 'User updated.');
    } else {
        set_flash('danger', 'Invalid action.');
    }
    redirect(BASE_URL . '/admin/users.php');
}

$search = sanitize($_GET['q'] ?? '');
$sql = "SELECT u.*, (SELECT COUNT(*) FROM projects p WHERE p.client_id=u.id) AS project_count
        FROM users u WHERE u.role='client'";
$params = [];
if ($search) { $sql .= " AND (u.full_name LIKE ? OR u.email LIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%"; }
$sql .= " ORDER BY u.created_at DESC";
$stmt = $db->prepare($sql); $stmt->execute($params);
$users = $stmt->fetchAll();

$page_title  = 'Clients';
$active_page = 'users';
include __DIR__ . '/includes/admin_header.php';
?>

<div class="d-flex justify-content-between mb-3 gap-2 flex-wrap">
  <form class="d-flex" method="GET">
    <input type="text" name="q" class="form-control form-control-sm me-2" placeholder="Search name or email" value="<?= e($search) ?>" style="min-width:260px;">
    <button class="btn btn-sm btn-dtc-primary">Search</button>
  </form>
  <a href="?q=" class="btn btn-sm btn-dtc-outline">Reset</a>
</div>

<div class="card card-dtc">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="small text-muted" style="background:#F8F9FC;">
          <tr><th>Name</th><th>Email</th><th>Company</th><th>Projects</th><th>Status</th><th>Joined</th><th>Actions</th></tr>
        </thead>
        <tbody>
          <?php if (empty($users)): ?>
            <tr><td colspan="7" class="text-center py-4 text-muted">No clients found.</td></tr>
          <?php else: foreach ($users as $u):
            $sb = $u['status']==='active' ? 'bg-success' : ($u['status']==='banned' ? 'bg-danger' : 'bg-warning text-dark');
          ?>
            <tr>
              <td>
                <div class="fw-semibold"><?= e($u['full_name']) ?></div>
                <div class="small text-muted"><?= e($u['phone']) ?></div>
              </td>
              <td class="small"><?= e($u['email']) ?></td>
              <td class="small"><?= e($u['company_name'] ?: '—') ?></td>
              <td><span class="badge bg-dtc-dodger"><?= $u['project_count'] ?></span></td>
              <td><span class="badge <?= $sb ?>"><?= strtoupper($u['status']) ?></span></td>
              <td class="small text-muted"><?= date('M d, Y', strtotime($u['created_at'])) ?></td>
              <td>
                <form method="POST" class="d-inline">
                  <?= csrf_field() ?>
                  <input type="hidden" name="id" value="<?= $u['id'] ?>">
                  <?php if ($u['status']==='active'): ?>
                    <button name="action" value="ban" class="btn btn-sm btn-outline-warning" data-confirm="Ban this user?"><i class="bi bi-slash-circle"></i></button>
                  <?php else: ?>
                    <button name="action" value="activate" class="btn btn-sm btn-outline-success"><i class="bi bi-check2"></i></button>
                  <?php endif; ?>
                  <button name="action" value="delete" class="btn btn-sm btn-outline-danger" data-confirm="Delete this user permanently?"><i class="bi bi-trash"></i></button>
                </form>
              </td>
            </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>