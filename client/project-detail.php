<?php
require_once __DIR__ . '/includes/guard.php';
$me = current_user();
$db = Database::getConnection();

$id = (int)($_GET['id'] ?? 0);
$stmt = $db->prepare("SELECT * FROM projects WHERE id = ? AND client_id = ? LIMIT 1");
$stmt->execute([$id, $me['id']]);
$project = $stmt->fetch();

if (!$project) {
    set_flash('danger', 'Project not found.');
    redirect(BASE_URL . '/client/projects.php');
}

/* Milestones */
$ms = $db->prepare("SELECT * FROM milestones WHERE project_id = ? ORDER BY sort_order ASC, id ASC");
$ms->execute([$id]);
$milestones = $ms->fetchAll();

/* Files */
$fl = $db->prepare("SELECT f.*, u.full_name AS uploader FROM project_files f
                    JOIN users u ON f.uploaded_by = u.id
                    WHERE f.project_id = ? ORDER BY f.created_at DESC");
$fl->execute([$id]);
$files = $fl->fetchAll();

/* Invoices */
$iv = $db->prepare("SELECT * FROM invoices WHERE project_id = ? ORDER BY created_at DESC");
$iv->execute([$id]);
$invoices = $iv->fetchAll();

$page_title = 'Project: ' . $project['title'];
$active_page = 'projects';
include __DIR__ . '/includes/client_header.php';
?>

<div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-4">
  <div>
    <a href="<?= BASE_URL ?>/client/projects.php" class="small text-muted text-decoration-none">
      <i class="bi bi-arrow-left"></i> All Projects
    </a>
    <h3 class="fw-bold text-dtc-night mt-1 mb-1"><?= e($project['title']) ?></h3>
    <div class="text-muted small"><?= e($project['service_type']) ?> &middot; Started <?= date('M d, Y', strtotime($project['start_date'])) ?></div>
  </div>
  <div class="text-end">
    <div class="badge bg-primary fs-6"><?= (int)$project['progress'] ?>% complete</div>
    <div class="progress mt-2" style="width:200px; height:8px;">
      <div class="progress-bar progress-bar-dtc" style="width:<?= (int)$project['progress'] ?>%;"></div>
    </div>
  </div>
</div>

<div class="row g-4">
  <!-- Left: overview, milestones -->
  <div class="col-lg-8">
    <div class="card card-dtc mb-4">
      <div class="card-body">
        <h5 class="fw-bold text-dtc-night mb-3">Overview</h5>
        <p class="text-muted"><?= e($project['description']) ?></p>
        <div class="row small text-muted">
          <div class="col-md-4 mb-2"><strong>Budget:</strong> $<?= number_format((float)$project['budget'],2) ?></div>
          <div class="col-md-4 mb-2"><strong>Deadline:</strong> <?= $project['deadline'] ? date('M d, Y', strtotime($project['deadline'])) : '—' ?></div>
          <div class="col-md-4 mb-2"><strong>Status:</strong> <?= ucfirst(str_replace('_',' ',$project['status'])) ?></div>
        </div>
      </div>
    </div>

    <div class="card card-dtc mb-4">
      <div class="card-body">
        <h5 class="fw-bold text-dtc-night mb-3">Milestones</h5>
        <?php if (empty($milestones)): ?>
          <p class="text-muted small">No milestones defined yet.</p>
        <?php else: ?>
          <div class="timeline">
            <?php foreach ($milestones as $m):
              $icon = ['completed'=>'bi-check-circle-fill text-success',
                       'in_progress'=>'bi-hourglass-split text-warning',
                       'pending'=>'bi-circle text-muted'][$m['status']] ?? 'bi-circle text-muted';
            ?>
              <div class="d-flex mb-3">
                <div class="me-3 fs-4"><i class="bi <?= $icon ?>"></i></div>
                <div class="flex-grow-1">
                  <div class="fw-semibold text-dtc-night"><?= e($m['title']) ?></div>
                  <div class="small text-muted"><?= e($m['description']) ?></div>
                  <div class="small text-muted mt-1">
                    <?php if ($m['due_date']): ?><i class="bi bi-calendar me-1"></i>Due <?= date('M d, Y', strtotime($m['due_date'])) ?><?php endif; ?>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <div class="card card-dtc">
      <div class="card-body">
        <h5 class="fw-bold text-dtc-night mb-3">Files & Deliverables</h5>
        <?php if (empty($files)): ?>
          <p class="text-muted small">No files yet.</p>
        <?php else: ?>
          <div class="list-group list-group-flush">
            <?php foreach ($files as $f): ?>
              <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                <div>
                  <i class="bi bi-file-earmark me-2 text-dtc-dodger"></i>
                  <span class="fw-semibold"><?= e($f['original_name']) ?></span>
                  <div class="small text-muted">Uploaded by <?= e($f['uploader']) ?> &middot; <?= date('M d, Y', strtotime($f['created_at'])) ?></div>
                </div>
                <a href="<?= UPLOAD_URL . e($f['stored_name']) ?>" class="btn btn-sm btn-dtc-outline" download>
                  <i class="bi bi-download"></i>
                </a>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <hr class="my-3">
<h6 class="fw-bold text-dtc-night small mb-2">Upload a File (Feedback / Deliverables)</h6>
<form action="<?= BASE_URL ?>/client/ajax/upload_file.php" method="POST" enctype="multipart/form-data" class="d-flex gap-2">
  <?= csrf_field() ?>
  <input type="hidden" name="project_id" value="<?= $project['id'] ?>">
  <input type="file" name="file" class="form-control form-control-sm" required>
  <button class="btn btn-sm btn-dtc-primary"><i class="bi bi-upload me-1"></i> Upload</button>
</form>
<div class="small text-muted mt-1">Max 10 MB. Allowed: pdf, doc(x), xls(x), png, jpg, zip, csv</div>
      </div>
    </div>
  </div>

  <!-- Right: invoices + quick actions -->
  <div class="col-lg-4">
    <div class="card card-dtc mb-4">
      <div class="card-body">
        <h5 class="fw-bold text-dtc-night mb-3">Invoices</h5>
        <?php if (empty($invoices)): ?>
          <p class="text-muted small">No invoices yet.</p>
        <?php else: foreach ($invoices as $inv):
          $ib = ['unpaid'=>'bg-warning text-dark','paid'=>'bg-success','overdue'=>'bg-danger','cancelled'=>'bg-secondary'][$inv['status']] ?? 'bg-secondary';
        ?>
          <div class="d-flex justify-content-between align-items-start mb-3">
            <div>
              <div class="fw-semibold small"><?= e($inv['invoice_no']) ?></div>
              <div class="small text-muted">Due <?= $inv['due_date'] ? date('M d, Y', strtotime($inv['due_date'])) : '—' ?></div>
            </div>
            <div class="text-end">
              <div class="fw-bold">$<?= number_format((float)$inv['amount'],2) ?></div>
              <span class="badge <?= $ib ?>"><?= strtoupper($inv['status']) ?></span>
            </div>
            <a href="<?= BASE_URL ?>/client/invoice-pdf.php?id=<?= $inv['id'] ?>" class="btn btn-sm btn-outline-secondary">
  <i class="bi bi-file-pdf"></i> PDF
</a>
          </div>
        <?php endforeach; endif; ?>
      </div>
    </div>

    <div class="card card-dtc">
      <div class="card-body">
        <h5 class="fw-bold text-dtc-night mb-3">Quick Actions</h5>
        <a href="<?= BASE_URL ?>/client/messages.php?project=<?= $project['id'] ?>" class="btn btn-dtc-primary w-100 mb-2">
          <i class="bi bi-chat-dots me-1"></i> Message About This Project
        </a>
        <a href="<?= BASE_URL ?>/client/tickets.php?project=<?= $project['id'] ?>" class="btn btn-dtc-outline w-100">
          <i class="bi bi-life-preserver me-1"></i> Report an Issue
        </a>
      </div>
      <?php if ($project['status'] === 'completed'): ?>
  <a href="<?= BASE_URL ?>/client/feedback.php?project=<?= $project['id'] ?>" class="btn btn-dtc-primary w-100 mt-2">
    <i class="bi bi-star me-1"></i> Leave Feedback
  </a>
<?php endif; ?>
    </div>
  </div>
</div>

<?php include __DIR__ . '/includes/client_footer.php'; ?>