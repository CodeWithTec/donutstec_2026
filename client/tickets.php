<?php
require_once __DIR__ . '/includes/guard.php';
$me = current_user();
$db = Database::getConnection();

$projectFilter = (int)($_GET['project'] ?? 0);

$stmt = $db->prepare("SELECT * FROM tickets WHERE client_id = ? ORDER BY created_at DESC");
$stmt->execute([$me['id']]);
$tickets = $stmt->fetchAll();

$page_title = 'Support Tickets';
$active_page = 'tickets';
include __DIR__ . '/includes/client_header.php';
?>

<div class="row g-4">
  <!-- New ticket form -->
  <div class="col-lg-4">
    <div class="card card-dtc">
      <div class="card-body">
        <h5 class="fw-bold text-dtc-night mb-3">New Ticket</h5>
        <form action="<?= BASE_URL ?>/client/ajax/create_ticket.php" method="POST">
          <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
          <?php if ($projectFilter): ?>
            <input type="hidden" name="project_id" value="<?= $projectFilter ?>">
          <?php endif; ?>
          <div class="mb-2">
            <label class="form-label small fw-semibold">Subject</label>
            <input type="text" name="subject" class="form-control form-control-sm" required>
          </div>
          <div class="mb-2">
            <label class="form-label small fw-semibold">Priority</label>
            <select name="priority" class="form-select form-select-sm">
              <option value="low">Low</option>
              <option value="medium" selected>Medium</option>
              <option value="high">High</option>
              <option value="urgent">Urgent</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label small fw-semibold">Message</label>
            <textarea name="message" class="form-control form-control-sm" rows="4" required></textarea>
          </div>
          <button class="btn btn-dtc-primary w-100"><i class="bi bi-plus-circle me-1"></i> Submit Ticket</button>
        </form>
      </div>
    </div>
  </div>

  <!-- Tickets list -->
  <div class="col-lg-8">
    <div class="card card-dtc">
      <div class="card-body">
        <h5 class="fw-bold text-dtc-night mb-3">Your Tickets</h5>
        <?php if (empty($tickets)): ?>
          <p class="text-muted small mb-0">You haven't submitted any tickets yet.</p>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-hover align-middle">
              <thead class="small text-muted">
                <tr><th>#</th><th>Subject</th><th>Priority</th><th>Status</th><th>Created</th><th></th></tr>
              </thead>
              <tbody>
                <?php foreach ($tickets as $t):
                  $pb = ['low'=>'bg-secondary','medium'=>'bg-info text-dark','high'=>'bg-warning text-dark','urgent'=>'bg-danger'][$t['priority']] ?? 'bg-secondary';
                  $sb = ['open'=>'bg-primary','in_progress'=>'bg-info text-dark','resolved'=>'bg-success','closed'=>'bg-secondary'][$t['status']] ?? 'bg-secondary';
                ?>
                  <tr>
                    <td>#<?= $t['id'] ?></td>
                    <td class="small"><?= e($t['subject']) ?></td>
                    <td><span class="badge <?= $pb ?>"><?= strtoupper($t['priority']) ?></span></td>
                    <td><span class="badge <?= $sb ?>"><?= strtoupper(str_replace('_',' ',$t['status'])) ?></span></td>
                    <td class="small text-muted"><?= date('M d', strtotime($t['created_at'])) ?></td>
                    <td><a href="<?= BASE_URL ?>/client/ticket-detail.php?id=<?= $t['id'] ?>" class="btn btn-sm btn-dtc-outline">Open</a></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/includes/client_footer.php'; ?>