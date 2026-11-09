<?php
require_once __DIR__ . '/includes/guard.php';
$me = current_user();
$db = Database::getConnection();

/* Stats */
$activeProjects = (int)$db->query("SELECT COUNT(*) FROM projects WHERE client_id={$me['id']} AND status IN ('pending','in_progress','on_hold')")->fetchColumn();
$completed      = (int)$db->query("SELECT COUNT(*) FROM projects WHERE client_id={$me['id']} AND status='completed'")->fetchColumn();
$openTickets    = (int)$db->query("SELECT COUNT(*) FROM tickets WHERE client_id={$me['id']} AND status IN ('open','in_progress')")->fetchColumn();
$unreadMsgs     = (int)$db->query("SELECT COUNT(*) FROM messages WHERE receiver_id={$me['id']} AND is_read=0")->fetchColumn();

/* Recent projects */
$projects = $db->query("SELECT * FROM projects WHERE client_id={$me['id']} ORDER BY updated_at DESC LIMIT 4")->fetchAll();

/* Recent notifications */
$notifs = $db->query("SELECT * FROM notifications WHERE user_id={$me['id']} ORDER BY created_at DESC LIMIT 5")->fetchAll();

$page_title = 'Dashboard';
$active_page = 'dashboard';
include __DIR__ . '/includes/client_header.php';
?>

<!-- Welcome -->
<div class="mb-4">
  <h3 class="fw-bold text-dtc-night">Welcome back, <?= e(explode(' ', $me['full_name'])[0]) ?> 👋</h3>
  <p class="text-muted mb-0">Here's what's happening with your projects today.</p>
</div>

<!-- KPI CARDS -->
<div class="row g-3 mb-4">
  <div class="col-6 col-lg-3">
    <div class="stat-card d-flex align-items-center gap-3">
      <div class="stat-icon bg-dtc-dodger"><i class="bi bi-kanban"></i></div>
      <div>
        <div class="stat-value"><?= $activeProjects ?></div>
        <div class="stat-label">Active Projects</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="stat-card d-flex align-items-center gap-3">
      <div class="stat-icon" style="background:#28a745;"><i class="bi bi-check2-circle"></i></div>
      <div>
        <div class="stat-value"><?= $completed ?></div>
        <div class="stat-label">Completed</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="stat-card d-flex align-items-center gap-3">
      <div class="stat-icon" style="background:#ffc107;"><i class="bi bi-life-preserver"></i></div>
      <div>
        <div class="stat-value"><?= $openTickets ?></div>
        <div class="stat-label">Open Tickets</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="stat-card d-flex align-items-center gap-3">
      <div class="stat-icon" style="background:var(--dtc-blue);"><i class="bi bi-chat-dots"></i></div>
      <div>
        <div class="stat-value"><?= $unreadMsgs ?></div>
        <div class="stat-label">Unread Messages</div>
      </div>
    </div>
  </div>
</div>

<div class="row g-4">
  <!-- Recent Projects -->
  <div class="col-lg-8">
    <div class="card card-dtc h-100">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h5 class="fw-bold text-dtc-night mb-0">Recent Projects</h5>
          <a href="<?= BASE_URL ?>/client/projects.php" class="small text-dtc-dodger">View All</a>
        </div>
        <?php if (empty($projects)): ?>
          <div class="text-center py-4">
            <i class="bi bi-folder-x fs-1 text-muted"></i>
            <p class="mt-2 text-muted">No projects yet. Contact us to start one!</p>
            <a href="<?= BASE_URL ?>/public/quote.php" class="btn btn-dtc-primary btn-sm">Request a Quote</a>
          </div>
        <?php else: foreach ($projects as $p):
          $badge = [
            'pending'=>'bg-secondary','in_progress'=>'bg-primary',
            'on_hold'=>'bg-warning text-dark','completed'=>'bg-success','cancelled'=>'bg-danger'
          ][$p['status']] ?? 'bg-secondary';
        ?>
          <div class="border-bottom pb-3 mb-3">
            <div class="d-flex justify-content-between align-items-start">
              <div>
                <a href="<?= BASE_URL ?>/client/project-detail.php?id=<?= $p['id'] ?>" class="fw-bold text-dtc-night text-decoration-none">
                  <?= e($p['title']) ?>
                </a>
                <div class="small text-muted"><?= e($p['service_type']) ?></div>
              </div>
              <span class="badge <?= $badge ?>"><?= str_replace('_',' ', strtoupper($p['status'])) ?></span>
            </div>
            <div class="progress mt-2" style="height:8px;">
              <div class="progress-bar progress-bar-dtc" style="width:<?= (int)$p['progress'] ?>%;"></div>
            </div>
            <div class="small text-muted mt-1"><?= (int)$p['progress'] ?>% complete</div>
          </div>
        <?php endforeach; endif; ?>
      </div>
    </div>
  </div>

  <!-- Notifications -->
  <div class="col-lg-4">
    <div class="card card-dtc h-100">
      <div class="card-body">
        <h5 class="fw-bold text-dtc-night mb-3">Notifications</h5>
        <?php if (empty($notifs)): ?>
          <p class="text-muted small">No notifications yet.</p>
        <?php else: foreach ($notifs as $n): ?>
          <div class="border-bottom pb-2 mb-2">
            <div class="small fw-semibold"><?= e($n['title']) ?></div>
            <div class="small text-muted"><?= e($n['message']) ?></div>
            <div class="small text-muted mt-1"><?= date('M d, g:i A', strtotime($n['created_at'])) ?></div>
          </div>
        <?php endforeach; endif; ?>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/includes/client_footer.php'; ?>