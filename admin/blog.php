<?php
require_once __DIR__.'/includes/guard.php';
$db = Database::getConnection();

if (isset($_GET['del'])) {
    $s=$db->prepare("SELECT image FROM blog_posts WHERE id=?");$s->execute([(int)$_GET['del']]);
    if($r=$s->fetch()){if($r['image'])@unlink(UPLOAD_DIR.$r['image']);}
    $db->prepare("DELETE FROM blog_posts WHERE id=?")->execute([(int)$_GET['del']]);
    set_flash('success','Post deleted.'); redirect(BASE_URL.'/admin/blog.php');
}

$posts = $db->query("SELECT p.*, u.full_name AS author FROM blog_posts p JOIN users u ON p.author_id=u.id ORDER BY p.created_at DESC")->fetchAll();
$page_title='Blog Posts'; $active_page='blog';
include __DIR__.'/includes/admin_header.php';
?>

<div class="d-flex justify-content-between mb-3">
  <h5 class="fw-bold text-dtc-night mb-0">All Posts</h5>
  <a href="<?= BASE_URL ?>/admin/blog-edit.php" class="btn btn-sm btn-dtc-primary"><i class="bi bi-plus-circle me-1"></i> New Post</a>
</div>

<div class="card card-dtc">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="small text-muted" style="background:#F8F9FC;"><tr><th>Title</th><th>Author</th><th>Status</th><th>Views</th><th>Published</th><th></th></tr></thead>
        <tbody>
        <?php if (empty($posts)): ?><tr><td colspan="6" class="text-center py-4 text-muted">No posts.</td></tr>
        <?php else: foreach ($posts as $p): $sb=$p['status']==='published'?'bg-success':'bg-secondary'; ?>
          <tr>
            <td class="small fw-semibold"><?= e($p['title']) ?></td>
            <td class="small"><?= e($p['author']) ?></td>
            <td><span class="badge <?= $sb ?>"><?= strtoupper($p['status']) ?></span></td>
            <td class="small"><?= (int)$p['views'] ?></td>
            <td class="small text-muted"><?= $p['published_at'] ? date('M d, Y', strtotime($p['published_at'])) : '—' ?></td>
            <td>
              <a href="<?= BASE_URL ?>/admin/blog-edit.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-dtc-outline">Edit</a>
              <a href="?del=<?= $p['id'] ?>" class="btn btn-sm btn-outline-danger" data-confirm="Delete this post?"><i class="bi bi-trash"></i></a>
            </td>
          </tr>
        <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include __DIR__.'/includes/admin_footer.php'; ?>