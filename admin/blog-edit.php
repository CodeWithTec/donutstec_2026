<?php
require_once __DIR__.'/includes/guard.php';
$me = current_user();
$db = Database::getConnection();

$id = (int)($_GET['id'] ?? 0);
$post = null;
if ($id) { $s=$db->prepare("SELECT * FROM blog_posts WHERE id=? LIMIT 1");$s->execute([$id]);$post=$s->fetch();
  if (!$post) { set_flash('danger','Post not found.'); redirect(BASE_URL.'/admin/blog.php'); } }

if ($_SERVER['REQUEST_METHOD']==='POST') {
    verify_csrf();
    $title = sanitize($_POST['title'] ?? '');
    $slug  = sanitize($_POST['slug'] ?? '');
    $excerpt = sanitize($_POST['excerpt'] ?? '');
    $content = $_POST['content'] ?? ''; // trusted admin HTML (TinyMCE)
    $status  = in_array($_POST['status'] ?? '', ['draft','published'], true) ? $_POST['status'] : 'draft';
    if (!$title || !$content) { set_flash('danger','Title and content are required.'); redirect(BASE_URL.'/admin/blog-edit.php?id='.$id); }
    if (!$slug) $slug = strtolower(trim(preg_replace('/[^a-z0-9]+/i','-',$title), '-'));

    $image = $post['image'] ?? null;
    if (!empty($_FILES['image']['name'])) {
        if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR,0755,true);
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (in_array($ext,['jpg','jpeg','png','gif','webp'])) {
            $stored = uniqid('bl_').'.'.$ext;
            if (move_uploaded_file($_FILES['image']['tmp_name'], UPLOAD_DIR.$stored)) $image = $stored;
        }
    }

    $publishedAt = ($status === 'published') ? ($post['published_at'] ?: date('Y-m-d H:i:s')) : null;

    if ($id) {
        $db->prepare("UPDATE blog_posts SET title=?, slug=?, excerpt=?, content=?, status=?, image=?, published_at=? WHERE id=?")
           ->execute([$title,$slug,$excerpt,$content,$status,$image,$publishedAt,$id]);
        set_flash('success','Post updated.');
    } else {
        $db->prepare("INSERT INTO blog_posts (author_id,title,slug,excerpt,content,status,image,published_at) VALUES (?,?,?,?,?,?,?,?)")
           ->execute([$me['id'],$title,$slug,$excerpt,$content,$status,$image,$publishedAt]);
        $id = (int)$db->lastInsertId();
        set_flash('success','Post created.');
    }
    redirect(BASE_URL.'/admin/blog-edit.php?id='.$id);
}

$page_title = $id ? 'Edit Post' : 'New Post';
$active_page = 'blog';
include __DIR__.'/includes/admin_header.php';
?>

<a href="<?= BASE_URL ?>/admin/blog.php" class="small text-muted text-decoration-none"><i class="bi bi-arrow-left"></i> All Posts</a>

<form method="POST" enctype="multipart/form-data" class="mt-3">
  <?= csrf_field() ?>
  <div class="row g-3">
    <div class="col-lg-8">
      <div class="card card-dtc mb-3">
        <div class="card-body">
          <input name="title" class="form-control form-control-lg mb-2 fw-bold" placeholder="Post title" required value="<?= e($post['title']??'') ?>">
          <input name="slug" class="form-control form-control-sm mb-2" placeholder="slug (auto if empty)" value="<?= e($post['slug']??'') ?>">
          <textarea name="excerpt" class="form-control form-control-sm mb-2" rows="2" placeholder="Short excerpt"><?= e($post['excerpt']??'') ?></textarea>
          <label class="form-label small fw-semibold">Content</label>
          <textarea name="content" id="contentEditor" class="form-control" rows="14"><?= e($post['content']??'') ?></textarea>
        </div>
      </div>
    </div>
    <div class="col-lg-4">
      <div class="card card-dtc mb-3">
        <div class="card-body">
          <h6 class="fw-bold text-dtc-night">Publish</h6>
          <select name="status" class="form-select form-select-sm mb-2">
            <option value="draft" <?= ($post['status']??'draft')==='draft'?'selected':'' ?>>Draft</option>
            <option value="published" <?= ($post['status']??'')==='published'?'selected':'' ?>>Published</option>
          </select>
          <button class="btn btn-dtc-primary w-100 btn-sm">Save Post</button>
        </div>
      </div>
      <div class="card card-dtc">
        <div class="card-body">
          <h6 class="fw-bold text-dtc-night">Featured Image</h6>
          <?php if (!empty($post['image'])): ?>
            <img src="<?= UPLOAD_URL.e($post['image']) ?>" class="img-fluid mb-2 rounded">
          <?php endif; ?>
          <input type="file" name="image" class="form-control form-control-sm">
        </div>
      </div>
    </div>
  </div>
</form>

<!-- TinyMCE via CDN -->
<script src="https://cdn.jsdelivr.net/npm/tinymce@6.8.3/tinymce.min.js"></script>
<script>
  tinymce.init({
    selector: '#contentEditor',
    height: 500,
    menubar: false,
    plugins: 'lists link image code table',
    toolbar: 'undo redo | bold italic underline | bullist numlist | link image | code',
    content_style: 'body{font-family:Segoe UI,sans-serif;font-size:14px;}'
  });
</script>

<?php include __DIR__.'/includes/admin_footer.php'; ?>