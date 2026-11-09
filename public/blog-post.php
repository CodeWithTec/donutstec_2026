<?php
require_once __DIR__ . '/../includes/functions.php';

$slug = sanitize($_GET['slug'] ?? '');
if (!$slug) redirect(BASE_URL . '/public/blog.php');

$db = Database::getConnection();
$stmt = $db->prepare("SELECT p.*, u.full_name AS author_name FROM blog_posts p
                     JOIN users u ON p.author_id = u.id
                     WHERE p.slug = ? AND p.status = 'published' LIMIT 1");
$stmt->execute([$slug]);
$post = $stmt->fetch();

if (!$post) {
    http_response_code(404);
    $page_title = 'Post Not Found';
    include __DIR__ . '/../includes/header.php';
    echo '<div class="container py-5 text-center"><h1>404 — Post not found</h1><a href="'.BASE_URL.'/public/blog.php" class="btn btn-dtc-primary mt-3">Back to Blog</a></div>';
    include __DIR__ . '/../includes/footer.php';
    exit;
}

// Increment views
$db->prepare("UPDATE blog_posts SET views = views + 1 WHERE id = ?")->execute([$post['id']]);

$page_title = $post['title'];
$page_description = $post['excerpt'];
$active_nav = 'blog';
include __DIR__ . '/../includes/header.php';
?>

<section class="py-5" style="background: linear-gradient(135deg,#151B54,#1E90FF); color:#fff;">
  <div class="container py-4">
    <a href="<?= BASE_URL ?>/public/blog.php" class="text-dtc-sky small">
      <i class="bi bi-arrow-left"></i> Back to Blog
    </a>
    <h1 class="display-6 fw-bold mt-3 mb-2"><?= e($post['title']) ?></h1>
    <div class="small opacity-75">
      By <?= e($post['author_name']) ?> &middot;
      <?= date('F d, Y', strtotime($post['published_at'])) ?> &middot;
      <i class="bi bi-eye"></i> <?= (int)$post['views'] ?> views
    </div>
  </div>
</section>

<section class="py-5">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-8">
        <article class="bg-white p-4 p-md-5 rounded-3 shadow-sm" style="line-height:1.8;">
          <?= $post['content'] /* trusted admin HTML */ ?>
        </article>
        <div class="text-center mt-4">
          <a href="<?= BASE_URL ?>/public/blog.php" class="btn btn-dtc-outline">
            <i class="bi bi-arrow-left me-1"></i> All Articles
          </a>
        </div>
      </div>
    </div>
  </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>