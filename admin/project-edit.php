<?php
require_once __DIR__ . '/includes/guard.php';
$db = Database::getConnection();

$id = (int)($_GET['id'] ?? 0);
$project = null;
if ($id) {
    $stmt = $db->prepare("SELECT * FROM projects WHERE id=? LIMIT 1");
    $stmt->execute([$id]); $project = $stmt->fetch();
    if (!$project) { set_flash('danger','Project not found.'); redirect(BASE_URL.'/admin/projects.php'); }
}

/* Save project */
if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['form']??'')==='project') {
    verify_csrf();
    $data = [
        'client_id'    => (int)($_POST['client_id'] ?? 0),
        'title'        => sanitize($_POST['title'] ?? ''),
        'description'  => sanitize($_POST['description'] ?? ''),
        'service_type' => sanitize($_POST['service_type'] ?? ''),
        'status'       => $_POST['status'] ?? 'pending',
        'progress'     => max(0, min(100, (int)($_POST['progress'] ?? 0))),
        'budget'       => (float)($_POST['budget'] ?? 0),
        'start_date'   => $_POST['start_date'] ?: null,
        'deadline'     => $_POST['deadline'] ?: null,
        'admin_note'   => sanitize($_POST['admin_note'] ?? ''),
    ];
    if (!$data['client_id'] || !$data['title']) {
        set_flash('danger','Client and title are required.');
    } elseif ($id) {
        $db->prepare("UPDATE projects SET client_id=?,title=?,description=?,service_type=?,status=?,progress=?,budget=?,start_date=?,deadline=?,admin_note=? WHERE id=?")
           ->execute([...array_values($data), $id]);
        set_flash('success','Project updated.');
        redirect(BASE_URL.'/admin/project-edit.php?id='.$id);
    } else {
        $db->prepare("INSERT INTO projects (client_id,title,description,service_type,status,progress,budget,start_date,deadline,admin_note) VALUES (?,?,?,?,?,?,?,?,?,?)")
           ->execute(array_values($data));
        $newId = (int)$db->lastInsertId();
        log_activity("Created project #$newId", current_user()['id']);
        set_flash('success','Project created.');
        redirect(BASE_URL.'/admin/project-edit.php?id='.$newId);
    }
}

/* Add milestone */
if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['form']??'')==='milestone_add' && $id) {
    verify_csrf();
    $db->prepare("INSERT INTO milestones (project_id,title,description,status,due_date,sort_order) VALUES (?,?,?,?,?,?)")
       ->execute([$id, sanitize($_POST['m_title']), sanitize($_POST['m_desc']), $_POST['m_status'] ?? 'pending', $_POST['m_due'] ?: null, (int)($_POST['m_order'] ?? 0)]);
    set_flash('success','Milestone added.');
    redirect(BASE_URL.'/admin/project-edit.php?id='.$id);
}

/* Delete milestone */
if (($_GET['del_m'] ?? 0) && $id) {
    $db->prepare("DELETE FROM milestones WHERE id=? AND project_id=?")->execute([(int)$_GET['del_m'], $id]);
    set_flash('success','Milestone deleted.');
    redirect(BASE_URL.'/admin/project-edit.php?id='.$id);
}

/* Upload file */
if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['form']??'')==='file_upload' && $id && !empty($_FILES['file']['name'])) {
    verify_csrf();
    if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);
    $allowed = ['pdf','png','jpg','jpeg','gif','doc','docx','xls','xlsx','zip','txt'];
    $ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed)) {
        set_flash('danger','File type not allowed.');
    } else {
        $stored = uniqid('pf_').'.'.$ext;
        if (move_uploaded_file($_FILES['file']['tmp_name'], UPLOAD_DIR.$stored)) {
            $db->prepare("INSERT INTO project_files (project_id,uploaded_by,original_name,stored_name,file_size,mime_type,file_type) VALUES (?,?,?,?,?,?,?)")
               ->execute([$id, current_user()['id'], $_FILES['file']['name'], $stored, $_FILES['file']['size'], $_FILES['file']['type'], 'deliverable']);
            set_flash('success','File uploaded.');
        }
    }
    redirect(BASE_URL.'/admin/project-edit.php?id='.$id);
}

/* Delete file */
if (($_GET['del_f'] ?? 0) && $id) {
    $stmt = $db->prepare("SELECT stored_name FROM project_files WHERE id=? AND project_id=?");
    $stmt->execute([(int)$_GET['del_f'], $id]);
    if ($row = $stmt->fetch()) {
        @unlink(UPLOAD_DIR.$row['stored_name']);
        $db->prepare("DELETE FROM project_files WHERE id=?")->execute([(int)$_GET['del_f']]);
        set_flash('success','File deleted.');
    }
    redirect(BASE_URL.'/admin/project-edit.php?id='.$id);
}

/* Create invoice */
if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['form']??'')==='invoice_add' && $id) {
    verify_csrf();
    $db->prepare("INSERT INTO invoices (project_id,invoice_no,amount,status,due_date,notes,created_by) VALUES (?,?,?,?,?,?,?)")
       ->execute([$id, sanitize($_POST['i_no']), (float)$_POST['i_amount'], $_POST['i_status'] ?? 'unpaid', $_POST['i_due'] ?: null, sanitize($_POST['i_notes']), current_user()['id']]);
    set_flash('success','Invoice created.');
    redirect(BASE_URL.'/admin/project-edit.php?id='.$id);
}

/* Load project data */
$clients   = $db->query("SELECT id, full_name, email FROM users WHERE role='client' ORDER BY full_name")->fetchAll();
$milestones = $files = $invoices = [];
if ($id) {
    $s = $db->prepare("SELECT * FROM milestones WHERE project_id=? ORDER BY sort_order,id"); $s->execute([$id]); $milestones = $s->fetchAll();
    $s = $db->prepare("SELECT f.*, u.full_name AS uploader FROM project_files f JOIN users u ON f.uploaded_by=u.id WHERE f.project_id=? ORDER BY f.created_at DESC"); $s->execute([$id]); $files = $s->fetchAll();
    $s = $db->prepare("SELECT * FROM invoices WHERE project_id=? ORDER BY created_at DESC"); $s->execute([$id]); $invoices = $s->fetchAll();
}

$page_title  = $id ? 'Edit Project' : 'New Project';
$active_page = 'projects';
include __DIR__ . '/includes/admin_header.php';
?>

<a href="<?= BASE_URL ?>/admin/projects.php" class="small text-muted text-decoration-none"><i class="bi bi-arrow-left"></i> All Projects</a>

<form method="POST" class="mt-3">
  <?= csrf_field() ?>
  <input type="hidden" name="form" value="project">
  <div class="card card-dtc mb-3">
    <div class="card-body">
      <h5 class="fw-bold text-dtc-night mb-3">Project Details</h5>
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label small fw-semibold">Client *</label>
          <select name="client_id" class="form-select" required>
            <option value="">— Select —</option>
            <?php foreach ($clients as $c): ?>
              <option value="<?= $c['id'] ?>" <?= ($project['client_id']??0)==$c['id']?'selected':'' ?>><?= e($c['full_name']) ?> (<?= e($c['email']) ?>)</option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label small fw-semibold">Title *</label>
          <input type="text" name="title" class="form-control" required value="<?= e($project['title']??'') ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label small fw-semibold">Service Type</label>
          <input type="text" name="service_type" class="form-control" value="<?= e($project['service_type']??'') ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label small fw-semibold">Status</label>
          <select name="status" class="form-select">
            <?php foreach (['pending','in_progress','on_hold','completed','cancelled'] as $s): ?>
              <option value="<?= $s ?>" <?= ($project['status']??'pending')===$s?'selected':'' ?>><?= ucfirst(str_replace('_',' ',$s)) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label small fw-semibold">Progress (%)</label>
          <input type="number" name="progress" min="0" max="100" class="form-control" value="<?= (int)($project['progress']??0) ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label small fw-semibold">Budget ($)</label>
          <input type="number" step="0.01" name="budget" class="form-control" value="<?= e($project['budget']??'') ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label small fw-semibold">Start Date</label>
          <input type="date" name="start_date" class="form-control" value="<?= e($project['start_date']??'') ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label small fw-semibold">Deadline</label>
          <input type="date" name="deadline" class="form-control" value="<?= e($project['deadline']??'') ?>">
        </div>
        <div class="col-12">
          <label class="form-label small fw-semibold">Description</label>
          <textarea name="description" class="form-control" rows="3"><?= e($project['description']??'') ?></textarea>
        </div>
        <div class="col-12">
          <label class="form-label small fw-semibold">Admin Note (internal)</label>
          <textarea name="admin_note" class="form-control" rows="2"><?= e($project['admin_note']??'') ?></textarea>
        </div>
      </div>
      <button class="btn btn-dtc-primary mt-3"><?= $id ? 'Save Changes' : 'Create Project' ?></button>
    </div>
  </div>
</form>

<?php if ($id): ?>
<!-- Milestones -->
<div class="card card-dtc mb-3">
  <div class="card-body">
    <h5 class="fw-bold text-dtc-night mb-3">Milestones</h5>
    <?php if (empty($milestones)): ?><p class="small text-muted">No milestones yet.</p>
    <?php else: foreach ($milestones as $m): ?>
      <div class="d-flex justify-content-between border-bottom py-2">
        <div>
          <div class="fw-semibold small"><?= e($m['title']) ?> <span class="badge bg-secondary ms-1"><?= $m['status'] ?></span></div>
          <div class="small text-muted"><?= e($m['description']) ?><?= $m['due_date'] ? ' · Due '.date('M d, Y',strtotime($m['due_date'])) : '' ?></div>
        </div>
        <a href="?id=<?= $id ?>&del_m=<?= $m['id'] ?>" class="btn btn-sm btn-outline-danger" data-confirm="Delete this milestone?"><i class="bi bi-trash"></i></a>
      </div>
    <?php endforeach; endif; ?>
    <details class="mt-3">
      <summary class="fw-semibold small text-dtc-dodger" style="cursor:pointer;">+ Add Milestone</summary>
      <form method="POST" class="row g-2 mt-2">
        <?= csrf_field() ?>
        <input type="hidden" name="form" value="milestone_add">
        <div class="col-md-4"><input name="m_title" class="form-control form-control-sm" placeholder="Title" required></div>
        <div class="col-md-3"><select name="m_status" class="form-select form-select-sm"><option value="pending">Pending</option><option value="in_progress">In Progress</option><option value="completed">Completed</option></select></div>
        <div class="col-md-3"><input name="m_due" type="date" class="form-control form-control-sm"></div>
        <div class="col-md-2"><input name="m_order" type="number" class="form-control form-control-sm" placeholder="Order" value="0"></div>
        <div class="col-12"><input name="m_desc" class="form-control form-control-sm" placeholder="Description"></div>
        <div class="col-12"><button class="btn btn-sm btn-dtc-primary">Add</button></div>
      </form>
    </details>
  </div>
</div>

<!-- Files -->
<div class="card card-dtc mb-3">
  <div class="card-body">
    <h5 class="fw-bold text-dtc-night mb-3">Files & Deliverables</h5>
    <?php if (empty($files)): ?><p class="small text-muted">No files yet.</p>
    <?php else: foreach ($files as $f): ?>
      <div class="d-flex justify-content-between border-bottom py-2">
        <div class="small"><i class="bi bi-file-earmark me-1"></i> <a href="<?= UPLOAD_URL.e($f['stored_name']) ?>" target="_blank"><?= e($f['original_name']) ?></a> <span class="text-muted">— <?= e($f['uploader']) ?></span></div>
        <a href="?id=<?= $id ?>&del_f=<?= $f['id'] ?>" class="btn btn-sm btn-outline-danger" data-confirm="Delete file?"><i class="bi bi-trash"></i></a>
      </div>
    <?php endforeach; endif; ?>
    <form method="POST" enctype="multipart/form-data" class="d-flex gap-2 mt-3">
      <?= csrf_field() ?>
      <input type="hidden" name="form" value="file_upload">
      <input type="file" name="file" class="form-control form-control-sm" required>
      <button class="btn btn-sm btn-dtc-primary"><i class="bi bi-upload me-1"></i> Upload</button>
    </form>
  </div>
</div>

<!-- Invoices -->
<div class="card card-dtc">
  <div class="card-body">
    <h5 class="fw-bold text-dtc-night mb-3">Invoices</h5>
    <?php if (empty($invoices)): ?><p class="small text-muted">No invoices yet.</p>
    <?php else: foreach ($invoices as $inv):
      $ib = ['unpaid'=>'bg-warning text-dark','paid'=>'bg-success','overdue'=>'bg-danger','cancelled'=>'bg-secondary'][$inv['status']] ?? 'bg-secondary';
    ?>
      <div class="d-flex justify-content-between border-bottom py-2">
        <div class="small"><strong><?= e($inv['invoice_no']) ?></strong> — $<?= number_format((float)$inv['amount'],2) ?> <span class="badge <?= $ib ?>"><?= strtoupper($inv['status']) ?></span></div>
        <div class="small text-muted"><?= $inv['due_date'] ? 'Due '.date('M d, Y',strtotime($inv['due_date'])) : '' ?></div>
      </div>
    <?php endforeach; endif; ?>
    <details class="mt-3">
      <summary class="fw-semibold small text-dtc-dodger" style="cursor:pointer;">+ Add Invoice</summary>
      <form method="POST" class="row g-2 mt-2">
        <?= csrf_field() ?>
        <input type="hidden" name="form" value="invoice_add">
        <div class="col-md-3"><input name="i_no" class="form-control form-control-sm" placeholder="Invoice #" required value="INV-<?= date('Ymd').'-'.strtoupper(bin2hex(random_bytes(2))) ?>"></div>
        <div class="col-md-2"><input name="i_amount" type="number" step="0.01" class="form-control form-control-sm" placeholder="Amount" required></div>
        <div class="col-md-2"><select name="i_status" class="form-select form-select-sm"><option value="unpaid">Unpaid</option><option value="paid">Paid</option><option value="overdue">Overdue</option></select></div>
        <div class="col-md-2"><input name="i_due" type="date" class="form-control form-control-sm"></div>
        <div class="col-md-3"><input name="i_notes" class="form-control form-control-sm" placeholder="Notes"></div>
        <div class="col-12"><button class="btn btn-sm btn-dtc-primary">Create Invoice</button></div>
      </form>
    </details>
  </div>
</div>
<?php endif; ?>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>