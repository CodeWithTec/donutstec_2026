<?php
require_once __DIR__.'/includes/guard.php';
$db = Database::getConnection();

if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['form']??'')==='save') {
    verify_csrf();
    $db->prepare("INSERT INTO testimonials (client_name,client_role,content,rating,is_approved) VALUES (?,?,?,?,1)")
       ->execute([sanitize($_POST['client_name']), sanitize($_POST['client_role']), sanitize($_POST['content']), (int)($_POST['rating'] ?? 5)]);
    set_flash('success','Testimonial added.');
    redirect(BASE_URL.'/admin/testimonials.php');
}
if (isset($_GET['approve'])) { $db->prepare("UPDATE testimonials SET is_approved=1 WHERE id=?")->execute([(int)$_GET['approve']]); redirect(BASE_URL.'/admin/testimonials.php'); }
if (isset($_GET['reject']))  { $db->prepare("UPDATE testimonials SET is_approved=0 WHERE id=?")->execute([(int)$_GET['reject']]);  redirect(BASE_URL.'/admin/testimonials.php'); }
if (isset($_GET['del']))     { $db->prepare("DELETE FROM testimonials WHERE id=?")->execute([(int)$_GET['del']]); set_flash('success','Deleted.'); redirect(BASE_URL.'/admin/testimonials.php'); }

$items = $db->query("SELECT * FROM testimonials ORDER BY is_approved, created_at DESC")->fetchAll();
$page_title='Testimonials'; $active_page='testimonials';
include __DIR__.'/includes/admin_header.php';
?>

<div class="row g-3">
  <div class="col-lg-4">
    <div class="card card-dtc">
      <div class="card-body">
        <h5 class="fw-bold text-dtc-night mb-3">Add Testimonial</h5>
        <form method="POST">
          <?= csrf_field() ?><input type="hidden" name="form" value="save">
          <div class="mb-2"><label class="form-label small fw-semibold">Client Name</label><input name="client_name" class="form-control form-control-sm" required></div>
          <div class="mb-2"><label class="form-label small fw-semibold">Role / Company</label><input name="client_role" class="form-control form-control-sm"></div>
          <div class="mb-2"><label class="form-label small fw-semibold">Content</label><textarea name="content" class="form-control form-control-sm" rows="3" required></textarea></div>
          <div class="mb-2"><label class="form-label small fw-semibold">Rating</label>
            <select name="rating" class="form-select form-select-sm"><?php for($i=5;$i>=1;$i--): ?><option value="<?= $i ?>"><?= $i ?> star<?= $i>1?'s':'' ?></option><?php endfor; ?></select>
          </div>
          <button class="btn btn-dtc-primary btn-sm w-100">Add</button>
        </form>
      </div>
    </div>
  </div>
  <div class="col-lg-8">
    <div class="card card-dtc">
      <div class="card-body p-0">
        <?php foreach ($items as $t): ?>
          <div class="p-3 border-bottom">
            <div class="d-flex justify-content-between">
              <div>
                <div class="fw-semibold"><?= e($t['client_name']) ?> <?php if($t['is_approved']):?><span class="badge bg-success ms-1">Approved</span><?php else:?><span class="badge bg-warning text-dark ms-1">Pending</span><?php endif;?></div>
                <div class="small text-muted"><?= e($t['client_role']) ?> · <?= str_repeat('★',(int)$t['rating']) ?></div>
              </div>
              <div>
                <?php if (!$t['is_approved']): ?><a href="?approve=<?= $t['id'] ?>" class="btn btn-sm btn-outline-success">Approve</a><?php else: ?><a href="?reject=<?= $t['id'] ?>" class="btn btn-sm btn-outline-warning">Unpublish</a><?php endif; ?>
                <a href="?del=<?= $t['id'] ?>" class="btn btn-sm btn-outline-danger" data-confirm="Delete?"><i class="bi bi-trash"></i></a>
              </div>
            </div>
            <div class="small mt-2">"<?= e($t['content']) ?>"</div>
          </div>
        <?php endforeach; ?>
        <?php if (empty($items)): ?><p class="p-4 text-muted mb-0">No testimonials yet.</p><?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__.'/includes/admin_footer.php'; ?>