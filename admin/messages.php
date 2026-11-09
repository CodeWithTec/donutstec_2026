<?php
require_once __DIR__ . '/includes/guard.php';
$me = current_user();
$db = Database::getConnection();

/* Threads: one per project with messages */
$threadsStmt = $db->query("
  SELECT p.id AS project_id, p.title, u.full_name AS client_name, u.id AS client_id,
    (SELECT COUNT(*) FROM messages m WHERE m.project_id=p.id AND m.receiver_id={$me['id']} AND m.is_read=0) AS unread,
    (SELECT MAX(created_at) FROM messages m WHERE m.project_id=p.id) AS last_at
  FROM projects p JOIN users u ON p.client_id=u.id
  WHERE EXISTS (SELECT 1 FROM messages m WHERE m.project_id=p.id)
  ORDER BY last_at DESC
");
$threads = $threadsStmt->fetchAll();

$activeId = (int)($_GET['project'] ?? 0);
if (!$activeId && $threads) $activeId = (int)$threads[0]['project_id'];

/* Load active thread messages */
$messages = []; $activeProject = null;
if ($activeId) {
    foreach ($threads as $t) if ($t['project_id']==$activeId) $activeProject = $t;
    $s = $db->prepare("SELECT m.*, u.full_name AS sender_name, u.role FROM messages m JOIN users u ON m.sender_id=u.id WHERE m.project_id=? ORDER BY m.created_at ASC");
    $s->execute([$activeId]); $messages = $s->fetchAll();
    $db->prepare("UPDATE messages SET is_read=1 WHERE project_id=? AND receiver_id=?")->execute([$activeId, $me['id']]);
}

$page_title = 'Messages';
$active_page = 'messages';
include __DIR__ . '/includes/admin_header.php';
?>

<div class="row g-3">
  <div class="col-lg-4">
    <div class="card card-dtc h-100">
      <div class="card-body p-0">
        <div class="p-3 border-bottom"><h5 class="fw-bold text-dtc-night mb-0">Client Threads</h5></div>
        <?php if (empty($threads)): ?><p class="text-muted small p-3 mb-0">No conversations yet.</p>
        <?php else: foreach ($threads as $t): ?>
          <a href="?project=<?= $t['project_id'] ?>" class="d-block p-3 border-bottom text-decoration-none <?= $activeId==$t['project_id']?'bg-light':'' ?>">
            <div class="d-flex justify-content-between">
              <div class="fw-semibold text-dtc-night small"><?= e($t['title']) ?></div>
              <?php if ($t['unread']>0): ?><span class="badge bg-dtc-dodger"><?= $t['unread'] ?></span><?php endif; ?>
            </div>
            <div class="small text-muted"><?= e($t['client_name']) ?></div>
            <div class="small text-muted"><?= $t['last_at'] ? date('M d, g:i A', strtotime($t['last_at'])) : '' ?></div>
          </a>
        <?php endforeach; endif; ?>
      </div>
    </div>
  </div>

  <div class="col-lg-8">
    <div class="card card-dtc h-100">
      <div class="card-body d-flex flex-column" style="height:600px;">
        <?php if (!$activeProject): ?>
          <div class="m-auto text-center text-muted"><i class="bi bi-chat-square-dots fs-1"></i><p class="mt-2">Select a thread.</p></div>
        <?php else: ?>
          <div class="border-bottom pb-2 mb-3">
            <h6 class="fw-bold text-dtc-night mb-0"><?= e($activeProject['title']) ?></h6>
            <small class="text-muted">Client: <?= e($activeProject['client_name']) ?></small>
          </div>
          <div id="adminChatBox" class="flex-grow-1 overflow-auto mb-3" style="max-height:420px;">
            <?php foreach ($messages as $m): $isMe = $m['sender_id']==$me['id']; ?>
              <div class="d-flex mb-3 <?= $isMe?'flex-row-reverse':'' ?>">
                <div class="avatar-badge me-2 ms-2" style="background:<?= $m['role']==='admin'?'var(--dtc-night)':'var(--dtc-dodger)' ?>;"><?= strtoupper(substr($m['sender_name'],0,1)) ?></div>
                <div class="p-2 px-3 rounded-3" style="max-width:75%;background:<?= $isMe?'var(--dtc-dodger)':'#EEF4FF' ?>;color:<?= $isMe?'#fff':'#212529' ?>;">
                  <div class="small fw-semibold"><?= e($m['sender_name']) ?></div>
                  <div style="white-space:pre-wrap;"><?= e($m['body']) ?></div>
                  <div class="small opacity-75 mt-1"><?= date('M d, g:i A', strtotime($m['created_at'])) ?></div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
          <form id="adminChatForm" action="<?= BASE_URL ?>/admin/ajax/send_message.php" method="POST" class="d-flex gap-2">
            <input type="hidden" name="project_id" value="<?= $activeProject['project_id'] ?>">
            <input type="hidden" name="receiver_id" value="<?= $activeProject['client_id'] ?>">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <textarea name="body" class="form-control" rows="1" required placeholder="Reply..." style="resize:none;"></textarea>
            <button class="btn btn-dtc-primary px-3"><i class="bi bi-send"></i></button>
          </form>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<script src="<?= BASE_URL ?>/assets/js/chat-poll.js"></script>
<?php if ($activeProject && !empty($messages)): ?>
<script>
  dtcPollChat({
    endpoint: '<?= BASE_URL ?>/admin/ajax/fetch_messages.php',
    projectId: <?= (int)$activeProject['project_id'] ?>,
    boxId: 'adminChatBox',
    lastId: <?= (int)end($messages)['id'] ?>
  });
</script>
<?php endif; ?>


<?php include __DIR__ . '/includes/admin_footer.php'; ?>