<?php
require_once __DIR__ . '/includes/guard.php';
$me = current_user();
$db = Database::getConnection();

/* Threads: group messages with each project as a thread */
$projects = $db->prepare("SELECT id, title FROM projects WHERE client_id = ? ORDER BY updated_at DESC");
$projects->execute([$me['id']]);
$projects = $projects->fetchAll();

$projectId = (int)($_GET['project'] ?? 0);
$activeProject = null;
foreach ($projects as $p) { if ($p['id'] === $projectId) $activeProject = $p; }
if (!$activeProject && !empty($projects)) $activeProject = $projects[0];

/* Find admin id */
$adminId = (int)$db->query("SELECT id FROM users WHERE role='admin' LIMIT 1")->fetchColumn();

/* Load messages for the active thread */
$messages = [];
if ($activeProject) {
    $stmt = $db->prepare("SELECT m.*, u.full_name AS sender_name, u.role AS sender_role
                          FROM messages m JOIN users u ON m.sender_id = u.id
                          WHERE m.project_id = ? AND (m.sender_id = ? OR m.receiver_id = ?)
                          ORDER BY m.created_at ASC");
    $stmt->execute([$activeProject['id'], $me['id'], $me['id']]);
    $messages = $stmt->fetchAll();

    /* Mark messages as read */
    $db->prepare("UPDATE messages SET is_read = 1 WHERE receiver_id = ? AND project_id = ?")
       ->execute([$me['id'], $activeProject['id']]);
}

$page_title = 'Messages';
$active_page = 'messages';
include __DIR__ . '/includes/client_header.php';
?>

<div class="row g-4">
  <!-- Thread list -->
  <div class="col-lg-4">
    <div class="card card-dtc">
      <div class="card-body p-0">
        <div class="p-3 border-bottom">
          <h5 class="fw-bold text-dtc-night mb-0">Project Threads</h5>
        </div>
        <?php if (empty($projects)): ?>
          <p class="text-muted small p-3 mb-0">No projects yet. You can message us from the Support page once a project starts.</p>
        <?php else: foreach ($projects as $p): ?>
          <a href="?project=<?= $p['id'] ?>" class="d-block p-3 border-bottom text-decoration-none <?= $activeProject && $activeProject['id']===$p['id'] ? 'bg-light' : '' ?>">
            <div class="fw-semibold text-dtc-night small"><?= e($p['title']) ?></div>
            <div class="small text-muted">Project #<?= $p['id'] ?></div>
          </a>
        <?php endforeach; endif; ?>
      </div>
    </div>
  </div>

  <!-- Chat area -->
  <div class="col-lg-8">
    <div class="card card-dtc">
      <div class="card-body d-flex flex-column" style="height:600px;">
        <?php if (!$activeProject): ?>
          <div class="m-auto text-center text-muted">
            <i class="bi bi-chat-square-dots fs-1"></i>
            <p class="mt-2">Select a project to start chatting.</p>
          </div>
        <?php else: ?>
          <div class="border-bottom pb-2 mb-3">
            <h6 class="fw-bold text-dtc-night mb-0"><?= e($activeProject['title']) ?></h6>
            <small class="text-muted">Project #<?= $activeProject['id'] ?></small>
          </div>

          <div id="chatBox" class="flex-grow-1 overflow-auto mb-3" style="max-height:420px;">
  <?php if (empty($messages)): ?>
    <p class="text-muted small text-center mt-4">No messages yet. Start the conversation!</p>
  <?php else: foreach ($messages as $m): ?>
    <div class="d-flex mb-3 <?= $m['sender_id']==$me['id'] ? 'flex-row-reverse' : '' ?>" data-msg-id="<?= (int)$m['id'] ?>">
      <div class="me-2 ms-2 avatar-badge" style="background:<?= $m['sender_role']==='admin' ? 'var(--dtc-night)' : 'var(--dtc-dodger)' ?>;">
        <?= strtoupper(substr($m['sender_name'],0,1)) ?>
      </div>
      <div class="p-2 px-3 rounded-3" style="max-width:75%; background:<?= $m['sender_id']==$me['id'] ? 'var(--dtc-dodger)' : '#EEF4FF' ?>; color:<?= $m['sender_id']==$me['id'] ? '#fff' : '#212529' ?>;">
        <div class="small fw-semibold"><?= e($m['sender_name']) ?></div>
        <div style="white-space:pre-wrap;"><?= e($m['body']) ?></div>
        <div class="small opacity-75 mt-1"><?= date('M d, g:i A', strtotime($m['created_at'])) ?></div>
      </div>
    </div>
  <?php endforeach; endif; ?>
</div>

<form id="chatForm" action="<?= BASE_URL ?>/client/ajax/send_message.php" method="POST" class="d-flex gap-2 align-items-end">
  <input type="hidden" name="project_id" value="<?= $activeProject['id'] ?>">
  <input type="hidden" name="receiver_id" value="<?= $adminId ?>">
  <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
  <textarea name="body" class="form-control" rows="1" placeholder="Type your message... (Enter to send, Shift+Enter for newline)" required style="resize:none; min-height:38px; max-height:120px;"></textarea>
  <button type="submit" class="btn btn-dtc-primary px-3" aria-label="Send"><i class="bi bi-send"></i></button>
</form>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<script src="<?= BASE_URL ?>/assets/js/chat-poll.js"></script>
<?php if ($activeProject): 
  $lastMsgId = 0;
  if (!empty($messages)) { $last = end($messages); $lastMsgId = (int)$last['id']; }
?>
<script>
  // Start poller
  dtcPollChat({
    endpoint: '<?= BASE_URL ?>/client/ajax/fetch_messages.php',
    projectId: <?= (int)$activeProject['id'] ?>,
    boxId: 'chatBox',
    lastId: <?= $lastMsgId ?>,
    interval: 2500
  });
</script>
<?php endif; ?>

<?php include __DIR__ . '/includes/client_footer.php'; ?>