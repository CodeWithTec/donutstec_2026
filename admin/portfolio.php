<?php
require_once __DIR__.'/includes/guard.php';
$db = Database::getConnection();

/* Create / update */
if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['form']??'')==='save') {
    verify_csrf();
    $id    = (int)($_POST['id'] ?? 0);
    $title = sanitize($_POST['title'] ?? '');
    $cat   = sanitize($_POST['category'] ?? '');
    $desc  = sanitize($_POST['description'] ?? '');
    $url   = sanitize($_POST['project_url'] ?? '');
    $feat  = isset($_POST['is_featured']) ? 1 : 0;
    $order = (int)($_POST['sort_order'] ?? 0);
    $imageName = $_POST['existing_image'] ?? null;

    if (!empty($_FILES['image']['name'])) {
        if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR,0755,true);
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (in_array($ext,['jpg','jpeg','png','gif','webp'])) {
            $stored = uniqid('pf_').'.'.$ext;
            if (move_uploaded_file($_FILES['image']['tmp_name'], UPLOAD_DIR.$stored)) $imageName = $stored;
        }
    }

    if ($id) {
        $db->prepare("UPDATE portfolio SET title=?,category=?,description=?,project_url=?,is_featured=?,sort_order=?,image=? WHERE id=?")
           ->execute([$title,$cat,$desc,$url,$feat,$order,$imageName,$id]);
        set_flash('success','Portfolio updated.');
    } else {
        $db->prepare("INSERT INTO portfolio (title,category,description,project_url,is_featured,sort_order,image) VALUES (?,?,?,?,?,?,?)")
           ->execute([$title,$cat,$desc,$url,$feat,$order,$imageName]);
        set_flash('success','Portfolio item added.');
    }
    redirect(BASE_URL.'/admin/portfolio.php');
}

if (isset($_GET['del'])) {
    $s = $db->prepare("SELECT image FROM portfolio WHERE id=?"); $s->execute([(int)$_GET['del']]);
    if ($r = $s->fetch()) { if ($r['image']) @unlink(UPLOAD_DIR.$r['image']); }
    $db->prepare("DELETE FROM portfolio WHERE id=?")->execute([(int)$_GET['del']]);
    set_flash('success','Deleted.'); redirect(BASE_URL.'/admin/portfolio.php');
}

$edit = null;
if (isset($_GET['edit'])) { $s=$db->prepare("SELECT * FROM portfolio WHERE id=?"); $s->execute([(int)$_GET['edit']]); $edit=$s->fetch(); }

$items = $db->query("SELECT * FROM portfolio ORDER BY sort_order, id DESC")->fetchAll();
$page_title='Portfolio'; $active_page='portfolio';
include __DIR__.'/includes/admin_header.php';
?>

<div class="row g-3">
  <div class="col-lg-4">
    <div class="card card-dtc">
      <div class="card-body">
        <h5 class="fw-bold text-dtc-night mb-3"><?= $edit?'Edit Item':'Add Item' ?></h5>
        <form method="POST" enctype="multipart/form-data">
          <?= csrf_field() ?><input type="hidden" name="form" value="save">
          <input type="hidden" name="id" value="<?= $edit['id']??0 ?>">
          <input type="hidden" name="existing_image" value="<?= e($edit['image']??'') ?>">
          <div class="mb-2"><label class="form-label small fw-semibold">Title</label><input name="title" class="form-control form-control-sm" required value="<?= e($edit['title']??'') ?>"></div>
          <div class="mb-2"><label class="form-label small fw-semibold">Category</label><input name="category" class="form-control form-control-sm" value="<?= e($edit['category']??'') ?>"></div>
          <div class="mb-2"><label class="form-label small fw-semibold">Description</label><textarea name="description" class="form-control form-control-sm" rows="2"><?= e($edit['description']??'') ?></textarea></div>
          <div class="mb-2"><label class="form-label small fw-semibold">Project URL</label><input name="project_url" class="form-control form-control-sm" value="<?= e($edit['project_url']??'') ?>"></div>
          <div class="mb-2"><label class="form-label small fw-semibold">Image</label><input type="file" name="image" class="form-control form-control-sm"></div>
          <div class="row g-2 mb-2">
            <div class="col-6"><input name="sort_order" type="number" class="form-control form-control-sm" placeholder="Order" value="<?= e($edit['sort_order']??0) ?>"></div>
            <div class="col-6 d-flex align-items-center"><div class="form-check"><input class="form-check-input" type="checkbox" name="is_featured" id="feat" <?= !empty($edit['is_featured'])?'checked':'' ?>><label class="form-check-label small" for="feat">Featured</label></div></div>
          </div>
          <button class="btn btn-dtc-primary btn-sm w-100"><?= $edit?'Save':'Add' ?></button>
          <?php if ($edit): ?><a href="<?= BASE_URL ?>/admin/portfolio.php" class="btn btn-sm btn-light w-100 mt-1">Cancel</a><?php endif; ?>
        </form>
      </div>
    </div>
  </div>
  <div class="col-lg-8">
    <div class="card card-dtc">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="small text-muted" style="background:#F8F9FC;"><tr><th>Title</th><th>Category</th><th>Featured</th><th>Order</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($items as $it): ?>
              <tr>
                <td class="small fw-semibold"><?= e($it['title']) ?></td>
                <td class="small"><?= e($it['category']) ?></td>
                <td><?= $it['is_featured']?'<span class="badge bg-success">Yes</span>':'<span class="badge bg-secondary">No</span>' ?></td>
                <td class="small"><?= $it['sort_order'] ?></td>
                <td>
                  <a href="?edit=<?= $it['id'] ?>" class="btn btn-sm btn-dtc-outline">Edit</a>
                  <a href="?del=<?= $it['id'] ?>" class="btn btn-sm btn-outline-danger" data-confirm="Delete?"><i class="bi bi-trash"></i></a>
                </td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__.'/includes/admin_footer.php'; ?>