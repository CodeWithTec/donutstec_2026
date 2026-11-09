<?php
require_once __DIR__ . '/../includes/functions.php';

$db = Database::getConnection();
$posts = $db->query("SELECT * FROM blog_posts WHERE status = 'published' ORDER BY published_at DESC")->fetchAll();

$page_title = 'Blog & Insights';
$active_nav = 'blog';
include __DIR__ . '/../includes/header.php';
?>

<section class="py-5" style="background: linear-gradient(135deg,#151B54,#1E90FF); color:#fff;">
  <div class="container py-4">
    <span class="badge bg-light text-dtc-night mb-2 px-3">Blog</span>
    <h1 class="display-5 fw-bold mb-2">Insights & Updates</h1>
    <p class="lead mb-0 opacity-75">Thoughts on software, business, and building for Africa.</p>
  </div>
</section>

<section class="py-5">
  <div class="container">
    <?php if (empty($posts)): ?>
      <div class="text-center py-5">
        <i class="bi bi-journal-x fs-1 text-muted"></i>
        <p class="mt-3 text-muted">No posts yet. Check back soon!</p>
      </div>
    <?php else: ?>
      <div class="row g-4">
        <?php foreach ($posts as $post): ?>
          <div class="col-md-6 col-lg-4">
            <div class="card card-dtc h-100">
              <div style="height:180px; background: linear-gradient(135deg,#82CAFF,#1E90FF); border-radius:12px 12px 0 0;"></div>
              <div class="card-body">
                <div class="small text-muted mb-2">
                  <i class="bi bi-calendar me-1"></i>
                  <?= date('M d, Y', strtotime($post['published_at'])) ?>
                  <span class="ms-2"><i class="bi bi-eye me-1"></i><?= (int)$post['views'] ?></span>
                </div>
                <h5 class="fw-bold text-dtc-night"><?= e($post['title']) ?></h5>
                <p class="small text-muted"><?= e($post['excerpt']) ?></p>
                <a href="<?= BASE_URL ?>/public/blog-post.php?slug=<?= urlencode($post['slug']) ?>"
                   class="text-dtc-dodger fw-semibold small">
                  Read Article <i class="bi bi-arrow-right"></i>
                </a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>