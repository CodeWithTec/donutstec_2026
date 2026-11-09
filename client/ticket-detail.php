<?php
require_once __DIR__ . '/includes/guard.php';
$me = current_user();
$db = Database::getConnection();

$id = (int)($_GET['id'] ?? 0);
$stmt = $db->prepare("SELECT * FROM tickets WHERE id = ? AND client_id = ? LIMIT 1");
$stmt->execute([$id, $me['id']]);
$ticket = $stmt->fetch();

if (!$ticket) {
    set_flash('danger', 'Ticket not found.');
    redirect(BASE_URL . '/client/tickets.php');
}

$repliesStmt = $db->prepare("SELECT r.*, u.full_name, u.role FROM ticket_replies r
                             JOIN users u ON r.user_id = u.id
                             WHERE r.ticket_id = ? ORDER BY r.created_at ASC");
$repliesStmt->execute([$id]);
$replies = $repliesStmt->fetchAll();

$page_title = 'Ticket #' . $ticket['id'];
$active_page = 'tickets';
include __DIR__ . '/includes/client_header.php';
?>

<a href="<?= BASE_URL ?>/client/tickets.php" class="small text-muted text-decoration-none">
  <i class="bi bi-arrow-left"></i> All Tickets
</a>

<div class="card card-dtc mt-3">
  <div class="card-body">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
      <div>
        <h4 class="fw-bold text-dtc-night mb-1"><?= e($ticket['subject']) ?></h4>
        <div class="small text-muted">Opened <?= date('M d, Y g:i A', strtotime($ticket['created_at'])) ?></div>
      </div>
      <div>
        <span class="badge bg-secondary"><?= strtoupper($ticket['priority']) ?></span>
        <span class="badge bg-primary"><?= strtoupper(str_replace('_',' ',$ticket['status'])) ?></span>
      </div>
    </div>

    <div class="p-3 rounded bg-light mb-3">
      <div class="fw-semibold small text-dtc-night mb-1"><?= e($me['full_name']) ?> (You)</div>
      <div style="white-space:pre-wrap;"><?= e($ticket['message']) ?></div>
    </div>

    <h6 class="fw-bold text-dtc-night mt-4 mb-3">Conversation (<?= count($replies) ?>)</h6>

    <?php if (empty($replies)): ?>
      <p class="text-muted small">No replies yet. Our team will respond soon.</p>
    <?php else: foreach ($replies as $r): ?>
      <div class="p-3 rounded mb-2" style="background:<?= $r['role']==='admin' ? '#EEF4FF' : '#F8F9FC' ?>;">
        <div class="fw-semibold small text-dtc-night mb-1">
          <?= e($r['full_name']) ?>
          <?php if ($r['role']==='admin'): ?><span class="badge bg-dtc-night ms-2">Support</span><?php endif; ?>
          <span class="text-muted fw-normal ms-2"><?= date('M d, g:i A', strtotime($r['created_at'])) ?></span>
        </div>
        <div style="white-space:pre-wrap;"><?= e($r['body']) ?></div>
      </div>
    <?php endforeach; endif; ?>

    <?php if ($ticket['status'] !== 'closed'): ?>
      <form action="<?= BASE_URL ?>/client/ajax/reply_ticket.php" method="POST" class="mt-4">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="ticket_id" value="<?= $ticket['id'] ?>">
        <label class="form-label small fw-semibold">Reply</label>
        <textarea name="body" class="form-control mb-2" rows="3" required></textarea>
        <button class="btn btn-dtc-primary"><i class="bi bi-send me-1"></i> Send Reply</button>
      </form>
    <?php else: ?>
      <div class="alert alert-secondary mt-3 mb-0 small">This ticket has been closed. Open a new ticket if you need further help.</div>
    <?php endif; ?>
  </div>
</div>

<?php include __DIR__ . '/includes/client_footer.php'; ?>