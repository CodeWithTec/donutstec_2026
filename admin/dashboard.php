<?php
require_once __DIR__ . '/includes/guard.php';
$db = Database::getConnection();

/* KPIs */
$totalClients = (int)$db->query("SELECT COUNT(*) FROM users WHERE role='client'")->fetchColumn();
$activeProj   = (int)$db->query("SELECT COUNT(*) FROM projects WHERE status IN ('pending','in_progress','on_hold')")->fetchColumn();
$openTickets  = (int)$db->query("SELECT COUNT(*) FROM tickets WHERE status IN ('open','in_progress')")->fetchColumn();
$newQuotes    = (int)$db->query("SELECT COUNT(*) FROM quotes WHERE status='new'")->fetchColumn();
$revenuePaid  = (float)$db->query("SELECT COALESCE(SUM(amount),0) FROM invoices WHERE status='paid'")->fetchColumn();
$revenueDue   = (float)$db->query("SELECT COALESCE(SUM(amount),0) FROM invoices WHERE status IN ('unpaid','overdue')")->fetchColumn();

/* Chart data: revenue by month (last 6 months) */
$revRows = $db->query("
  SELECT DATE_FORMAT(paid_at, '%Y-%m') AS m, SUM(amount) AS total
  FROM invoices WHERE status='paid' AND paid_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
  GROUP BY m ORDER BY m
")->fetchAll();
$revLabels = []; $revValues = [];
for ($i = 5; $i >= 0; $i--) {
    $key = date('Y-m', strtotime("-$i months"));
    $revLabels[] = date('M', strtotime("-$i months"));
    $found = 0;
    foreach ($revRows as $r) if ($r['m'] === $key) $found = (float)$r['total'];
    $revValues[] = $found;
}

/* Projects by status */
$projByStatus = $db->query("SELECT status, COUNT(*) c FROM projects GROUP BY status")->fetchAll();

/* Recent activity */
$activity = $db->query("SELECT a.*, u.full_name FROM activity_logs a
                        LEFT JOIN users u ON a.user_id = u.id
                        ORDER BY a.created_at DESC LIMIT 10")->fetchAll();

$page_title  = 'Dashboard';
$active_page = 'dashboard';
include __DIR__ . '/includes/admin_header.php';
?>

<div class="row g-3 mb-4">
  <div class="col-6 col-lg-3">
    <div class="stat-card d-flex align-items-center gap-3">
      <div class="stat-icon bg-dtc-dodger"><i class="bi bi-people"></i></div>
      <div><div class="stat-value"><?= $totalClients ?></div><div class="stat-label">Clients</div></div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="stat-card d-flex align-items-center gap-3">
      <div class="stat-icon" style="background:var(--dtc-blue);"><i class="bi bi-kanban"></i></div>
      <div><div class="stat-value"><?= $activeProj ?></div><div class="stat-label">Active Projects</div></div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="stat-card d-flex align-items-center gap-3">
      <div class="stat-icon" style="background:#ffc107;"><i class="bi bi-life-preserver"></i></div>
      <div><div class="stat-value"><?= $openTickets ?></div><div class="stat-label">Open Tickets</div></div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="stat-card d-flex align-items-center gap-3">
      <div class="stat-icon" style="background:#28a745;"><i class="bi bi-cash-stack"></i></div>
      <div><div class="stat-value">$<?= number_format($revenuePaid, 0) ?></div><div class="stat-label">Revenue Paid</div></div>
    </div>
  </div>
</div>

<div class="row g-4 mb-4">
  <!-- Revenue chart -->
  <div class="col-lg-8">
    <div class="card card-dtc h-100">
      <div class="card-body">
        <h5 class="fw-bold text-dtc-night mb-3">Revenue (last 6 months)</h5>
        <canvas id="revenueChart" height="110"></canvas>
      </div>
    </div>
  </div>
  <!-- Projects by status -->
  <div class="col-lg-4">
    <div class="card card-dtc h-100">
      <div class="card-body">
        <h5 class="fw-bold text-dtc-night mb-3">Projects by Status</h5>
        <canvas id="statusChart" height="180"></canvas>
        <div class="small text-muted mt-3">
          <div><strong>Outstanding Revenue:</strong> $<?= number_format($revenueDue, 2) ?></div>
          <div><strong>New Quotes:</strong> <?= $newQuotes ?></div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row g-4">
  <div class="col-lg-7">
    <div class="card card-dtc">
      <div class="card-body">
        <h5 class="fw-bold text-dtc-night mb-3">Recent Activity</h5>
        <?php if (empty($activity)): ?>
          <p class="text-muted small">No activity yet.</p>
        <?php else: foreach ($activity as $a): ?>
          <div class="d-flex border-bottom py-2">
            <div class="text-dtc-dodger me-2"><i class="bi bi-dot fs-4"></i></div>
            <div class="flex-grow-1">
              <div class="small"><strong><?= e($a['full_name'] ?? 'System') ?></strong> <?= e($a['action']) ?></div>
              <div class="small text-muted"><?= date('M d, g:i A', strtotime($a['created_at'])) ?></div>
            </div>
          </div>
        <?php endforeach; endif; ?>
      </div>
    </div>
  </div>
  <div class="col-lg-5">
    <div class="card card-dtc h-100">
      <div class="card-body">
        <h5 class="fw-bold text-dtc-night mb-3">Quick Actions</h5>
        <div class="d-grid gap-2">
          <a href="<?= BASE_URL ?>/admin/project-edit.php" class="btn btn-dtc-primary"><i class="bi bi-plus-circle me-1"></i> New Project</a>
          <a href="<?= BASE_URL ?>/admin/blog-edit.php" class="btn btn-dtc-outline"><i class="bi bi-journal-plus me-1"></i> New Blog Post</a>
          <a href="<?= BASE_URL ?>/admin/quotes.php" class="btn btn-dtc-outline"><i class="bi bi-inbox me-1"></i> Quotes (<?= $newQuotes ?> new)</a>
          <a href="<?= BASE_URL ?>/admin/tickets.php" class="btn btn-dtc-outline"><i class="bi bi-life-preserver me-1"></i> Tickets (<?= $openTickets ?> open)</a>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
  const revCtx = document.getElementById('revenueChart').getContext('2d');
  new Chart(revCtx, {
    type: 'line',
    data: {
      labels: <?= json_encode($revLabels) ?>,
      datasets: [{
        label: 'Revenue ($)',
        data: <?= json_encode($revValues) ?>,
        borderColor: '#1E90FF',
        backgroundColor: 'rgba(30,144,255,0.15)',
        fill: true, tension: 0.35, borderWidth: 3, pointRadius: 5, pointBackgroundColor: '#0000FF'
      }]
    },
    options: {
      plugins: { legend: { display: false } },
      scales: { y: { beginAtZero: true, ticks: { callback: v => '$' + v } } }
    }
  });

  const statusCtx = document.getElementById('statusChart').getContext('2d');
  new Chart(statusCtx, {
    type: 'doughnut',
    data: {
      labels: <?= json_encode(array_map(fn($r)=>ucfirst(str_replace('_',' ',$r['status'])), $projByStatus)) ?>,
      datasets: [{
        data: <?= json_encode(array_map(fn($r)=>(int)$r['c'], $projByStatus)) ?>,
        backgroundColor: ['#1E90FF','#0000FF','#82CAFF','#28a745','#dc3545','#6c757d']
      }]
    },
    options: { plugins: { legend: { position: 'bottom' } }, cutout: '65%' }
  });
</script>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>