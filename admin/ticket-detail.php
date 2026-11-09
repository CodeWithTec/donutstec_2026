<?php
require_once __DIR__.'/includes/guard.php';
$me = current_user();
$db = Database::getConnection();

$id = (int)($_GET['id'] ?? 0);
$s = $db->prepare("SELECT t.*, u.full_name AS client_name, u.email AS client_email FROM tickets t JOIN users u ON t.client_id=u.id WHERE t.id=? LIMIT 1");
$s->execute([$id]); $ticket = $s->fetch();
if (!$ticket) { set_flash('danger','Ticket not found.'); redirect(BASE_URL.'/admin/tickets.php'); }

/* Reply */
if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['form']??'')==='reply') {
    verify_csrf();
    $body = trim($_POST['body'] ?? '');
    if ($body) {
        $db->prepare("INSERT INTO ticket_replies (ticket_id,user_id,body) VALUES (?,?,?)")->execute([$id, $me['id'], $body]);
        $db->prepare("UPDATE tickets SET status='in_progress', updated_at=NOW() WHERE id=?")->execute([$id]);
        /* Notify client */
notify($ticket['client_id'], 'New reply on your ticket',
    "DonutsTecLiberia replied to ticket #{$id}.",
    'ticket',
    '/client/ticket-detail.php?id=' . $id);

/* Email */
require_once __DIR__ . '/../../includes/mailer.php';
$bodyHtml = mail_template(
    'Reply on your ticket #' . $id,
    '<p>' . nl2br(htmlspecialchars($body)) . '</p>',
    'View ticket',
    BASE_URL . '/client/ticket-detail.php?id=' . $id
);
send_mail($ticket['client_email'], 'Reply on your support ticket', $bodyHtml, $ticket['client_name']);
        set_flash('success','Reply sent.');
    }
    redirect(BASE_URL.'/admin/ticket-detail.php?id='.$id);
}

/* Change status */
if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['form']??'')==='status') {
    verify_csrf();
    $newStatus = in_array($_POST['status'] ?? '', ['open','in_progress','resolved','closed'], true) ? $_POST['status'] : 'open';
    $newPriority = in_array($_POST['priority'] ?? '', ['low','medium','high','urgent'], true) ? $_POST['priority'] : $ticket['priority'];
    $db->prepare("UPDATE tickets SET status=?, priority=?, updated_at=NOW() WHERE id=?")->execute([$newStatus, $newPriority, $id]);
    set_flash('success','Ticket updated.');
    redirect(BASE_URL.'/admin/ticket-detail.php?id='.$id);
}

$s = $db->prepare("SELECT r.*, u.full_name, u.role FROM ticket_replies r JOIN users u ON r.user_id=u.id WHERE r.ticket_id=? ORDER BY r.created_at ASC");
$s->execute([$id]); $replies = $s->fetchAll();

$page_title = 'Ticket #'.$ticket['id']; $active_page='tickets';
include __DIR__.'/includes/admin_header.php';
?>

<a href="<?= BASE_URL ?>/admin/tickets.php" class="small text-muted text-decoration-none"><i class="bi bi-arrow-left"></i> All Tickets</a>

<div class="row g-3 mt-2">
  <div class="col-lg-8">
    <div class="card card-dtc">
      <div class="card-body">
        <h4 class="fw-bold text-dtc-night"><?= e($ticket['subject']) ?></h4>
        <div class="small text-muted mb-3">From <?= e($ticket['client_name']) ?> &middot; <?= e($ticket['client_email']) ?> &middot; <?= date('M d, Y g:i A', strtotime($ticket['created_at'])) ?></div>
        <div class="p-3 rounded bg-light mb-3" style="white-space:pre-wrap;"><?= e($ticket['message']) ?></div>

        <?php foreach ($replies as $r): ?>
          <div class="p-3 rounded mb-2" style="background:<?= $r['role']==='admin'?'#EEF4FF':'#F8F9FC' ?>;">
            <div class="fw-semibold small"><?= e($r['full_name']) ?><?php if ($r['role']==='admin'): ?> <span class="badge bg-dtc-night ms-1">Support</span><?php endif; ?> <span class="text-muted fw-normal ms-2"><?= date('M d, g:i A', strtotime($r['created_at'])) ?></span></div>
            <div style="white-space:pre-wrap;"><?= e($r['body']) ?></div>
          </div>
        <?php endforeach; ?>

        <form method="POST" class="mt-3">
          <?= csrf_field() ?>
          <input type="hidden" name="form" value="reply">
          <textarea name="body" class="form-control mb-2" rows="3" placeholder="Your reply..." required></textarea>
          <button class="btn btn-dtc-primary"><i class="bi bi-send me-1"></i> Send Reply</button>
        </form>
      </div>
    </div>
  </div>
  <div class="col-lg-4">
    <div class="card card-dtc">
      <div class="card-body">
        <h5 class="fw-bold text-dtc-night mb-3">Ticket Controls</h5>
        <form method="POST">
          <?= csrf_field() ?>
          <input type="hidden" name="form" value="status">
          <label class="form-label small fw-semibold">Status</label>
          <select name="status" class="form-select form-select-sm mb-2">
            <?php foreach (['open','in_progress','resolved','closed'] as $s): ?><option value="<?= $s ?>" <?= $ticket['status']===$s?'selected':'' ?>><?= ucfirst(str_replace('_',' ',$s)) ?></option><?php endforeach; ?>
          </select>
          <label class="form-label small fw-semibold">Priority</label>
          <select name="priority" class="form-select form-select-sm mb-3">
            <?php foreach (['low','medium','high','urgent'] as $p): ?><option value="<?= $p ?>" <?= $ticket['priority']===$p?'selected':'' ?>><?= ucfirst($p) ?></option><?php endforeach; ?>
          </select>
          <button class="btn btn-dtc-primary w-100">Update Ticket</button>
        </form>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__.'/includes/admin_footer.php'; ?>