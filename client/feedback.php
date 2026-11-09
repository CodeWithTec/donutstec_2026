<?php
require_once __DIR__ . '/includes/guard.php';
$me = current_user();
$db = Database::getConnection();

$projectId = (int)($_GET['project'] ?? 0);
if (!$projectId) { redirect(BASE_URL . '/client/projects.php'); }

/* Verify project belongs to client and is completed */
$s = $db->prepare("SELECT * FROM projects WHERE id=? AND client_id=? AND status='completed' LIMIT 1");
$s->execute([$projectId, $me['id']]);
$project = $s->fetch();
if (!$project) { set_flash('danger', 'Feedback is only available for completed projects.'); redirect(BASE_URL . '/client/projects.php'); }

/* Existing feedback? */
$s = $db->prepare("SELECT * FROM project_feedback WHERE project_id=? LIMIT 1");
$s->execute([$projectId]);
$existing = $s->fetch();

/* Submit */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $rating = max(1, min(5, (int)($_POST['rating'] ?? 5)));
    $comments = sanitize($_POST['comments'] ?? '');
    $isPublic = isset($_POST['is_public']) ? 1 : 0;

    if ($existing) {
        $db->prepare("UPDATE project_feedback SET rating=?, comments=?, is_public=? WHERE id=?")
           ->execute([$rating, $comments, $isPublic, $existing['id']]);
    } else {
        $db->prepare("INSERT INTO project_feedback (project_id, client_id, rating, comments, is_public) VALUES (?,?,?,?,?)")
           ->execute([$projectId, $me['id'], $rating, $comments, $isPublic]);
    }
    set_flash('success', 'Thank you for your feedback!');
    redirect(BASE_URL . '/client/feedback.php?project=' . $projectId);
}

$page_title = 'Project Feedback';
$active_page = 'projects';
include __DIR__ . '/includes/client_header.php';
?>

<div class="card card-dtc">
  <div class="card-body">
    <h4 class="fw-bold text-dtc-night mb-1"><?= e($project['title']) ?></h4>
    <p class="text-muted small mb-4">We'd love to know how we did.</p>

    <?php if ($existing): ?>
      <div class="alert alert-info small mb-3">
        <i class="bi bi-info-circle me-1"></i> You've already submitted feedback — you can update it below.
      </div>
    <?php endif; ?>

    <form method="POST">
      <?= csrf_field() ?>
      <div class="mb-3">
        <label class="form-label small fw-semibold">Your Rating</label>
        <div class="d-flex gap-2 fs-3 text-warning" id="starRow">
          <?php for ($i=1; $i<=5; $i++): ?>
            <i class="bi bi-star<?= ($existing['rating'] ?? 0) >= $i ? '-fill' : '' ?>"
               data-star="<?= $i ?>" style="cursor:pointer;"></i>
          <?php endfor; ?>
        </div>
        <input type="hidden" name="rating" id="ratingInput" value="<?= $existing['rating'] ?? 5 ?>">
      </div>
      <div class="mb-3">
        <label class="form-label small fw-semibold">Comments (optional)</label>
        <textarea name="comments" class="form-control" rows="4"><?= e($existing['comments'] ?? '') ?></textarea>
      </div>
      <div class="form-check mb-3">
        <input class="form-check-input" type="checkbox" name="is_public" id="pub" <?= !empty($existing['is_public']) ? 'checked' : '' ?>>
        <label class="form-check-label small" for="pub">Allow DonutsTecLiberia to publish this testimonial on our site.</label>
      </div>
      <button class="btn btn-dtc-primary"><i class="bi bi-send me-1"></i> Submit Feedback</button>
    </form>
  </div>
</div>

<script>
  document.querySelectorAll('[data-star]').forEach(el => {
    el.addEventListener('click', () => {
      const n = +el.dataset.star;
      document.getElementById('ratingInput').value = n;
      document.querySelectorAll('[data-star]').forEach(s => {
        s.classList.toggle('bi-star-fill', +s.dataset.star <= n);
        s.classList.toggle('bi-star', +s.dataset.star > n);
      });
    });
  });
</script>

<?php include __DIR__ . '/includes/client_footer.php'; ?>