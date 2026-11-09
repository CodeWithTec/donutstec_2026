<?php
require_once __DIR__.'/includes/guard.php';
$db = Database::getConnection();

if ($_SERVER['REQUEST_METHOD']==='POST') {
    verify_csrf();
    $allowed = [
    'site_name','site_tagline','site_email','site_phone','site_address',
    'facebook_url','twitter_url','linkedin_url','instagram_url',
    'hero_heading','hero_subtext','about_story',
    'seo_title','seo_description','seo_keywords','seo_og_image','analytics_id',
];
    foreach ($allowed as $key) {
        if (isset($_POST[$key])) {
            $stmt = $db->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
            $stmt->execute([$key, sanitize($_POST[$key])]);
        }
    }
    log_activity('Updated settings', current_user()['id']);
    set_flash('success','Settings saved.');
    redirect(BASE_URL.'/admin/settings.php');
}

$rows = $db->query("SELECT * FROM settings")->fetchAll();
$s = [];
foreach ($rows as $r) $s[$r['setting_key']] = $r['setting_value'];

$page_title='Site Settings'; $active_page='settings';
include __DIR__.'/includes/admin_header.php';
?>

<form method="POST">
  <?= csrf_field() ?>
  <div class="row g-3">
    <div class="col-lg-6">
      <div class="card card-dtc">
        <div class="card-body">
          <h5 class="fw-bold text-dtc-night mb-3">General</h5>
          <div class="mb-2"><label class="form-label small fw-semibold">Site Name</label><input name="site_name" class="form-control form-control-sm" value="<?= e($s['site_name']??'') ?>"></div>
          <div class="mb-2"><label class="form-label small fw-semibold">Tagline</label><input name="site_tagline" class="form-control form-control-sm" value="<?= e($s['site_tagline']??'') ?>"></div>
          <div class="mb-2"><label class="form-label small fw-semibold">Email</label><input name="site_email" class="form-control form-control-sm" value="<?= e($s['site_email']??'') ?>"></div>
          <div class="mb-2"><label class="form-label small fw-semibold">Phone</label><input name="site_phone" class="form-control form-control-sm" value="<?= e($s['site_phone']??'') ?>"></div>
          <div class="mb-2"><label class="form-label small fw-semibold">Address</label><input name="site_address" class="form-control form-control-sm" value="<?= e($s['site_address']??'') ?>"></div>
        </div>
      </div>
    </div>
    <div class="col-lg-6">
      <div class="card card-dtc">
        <div class="card-body">
          <h5 class="fw-bold text-dtc-night mb-3">Social Links</h5>
          <?php foreach (['facebook_url'=>'Facebook','twitter_url'=>'Twitter/X','linkedin_url'=>'LinkedIn','instagram_url'=>'Instagram'] as $k=>$v): ?>
            <div class="mb-2"><label class="form-label small fw-semibold"><?= $v ?></label><input name="<?= $k ?>" class="form-control form-control-sm" value="<?= e($s[$k]??'') ?>"></div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
    <div class="col-12">
      <div class="card card-dtc">
        <div class="card-body">
          <h5 class="fw-bold text-dtc-night mb-3">Homepage & About</h5>
          <div class="mb-2"><label class="form-label small fw-semibold">Hero Heading</label><input name="hero_heading" class="form-control form-control-sm" value="<?= e($s['hero_heading']??'') ?>"></div>
          <div class="mb-2"><label class="form-label small fw-semibold">Hero Subtext</label><textarea name="hero_subtext" class="form-control form-control-sm" rows="2"><?= e($s['hero_subtext']??'') ?></textarea></div>
          <div class="mb-2"><label class="form-label small fw-semibold">About Story</label><textarea name="about_story" class="form-control form-control-sm" rows="3"><?= e($s['about_story']??'') ?></textarea></div>
        </div>
      </div>
    </div>

            <div class="col-12">
  <div class="card card-dtc">
    <div class="card-body">
      <h5 class="fw-bold text-dtc-night mb-3">SEO</h5>
      <div class="mb-2">
        <label class="form-label small fw-semibold">Default Page Title</label>
        <input name="seo_title" class="form-control form-control-sm" value="<?= e($s['seo_title'] ?? '') ?>">
      </div>
      <div class="mb-2">
        <label class="form-label small fw-semibold">Meta Description (160 chars)</label>
        <textarea name="seo_description" class="form-control form-control-sm" rows="2" maxlength="160"><?= e($s['seo_description'] ?? '') ?></textarea>
      </div>
      <div class="mb-2">
        <label class="form-label small fw-semibold">Meta Keywords (comma-separated)</label>
        <input name="seo_keywords" class="form-control form-control-sm" value="<?= e($s['seo_keywords'] ?? '') ?>">
      </div>
      <div class="mb-2">
        <label class="form-label small fw-semibold">OG Image URL (1200×630 recommended)</label>
        <input name="seo_og_image" class="form-control form-control-sm" value="<?= e($s['seo_og_image'] ?? '') ?>">
      </div>
      <div class="mb-2">
        <label class="form-label small fw-semibold">Google Analytics ID</label>
        <input name="analytics_id" class="form-control form-control-sm" placeholder="G-XXXXXXXXXX" value="<?= e($s['analytics_id'] ?? '') ?>">
      </div>
    </div>
  </div>
</div>

  </div>
  <button class="btn btn-dtc-primary mt-3"><i class="bi bi-check2 me-1"></i> Save All Settings</button>
</form>

<?php include __DIR__.'/includes/admin_footer.php'; ?>