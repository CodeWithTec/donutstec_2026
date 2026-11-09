<?php
require_once __DIR__ . '/../includes/functions.php';
$page_title = 'Request a Quote';
$active_nav = '';
include __DIR__ . '/../includes/header.php';
?>

<section class="py-5" style="background: linear-gradient(135deg,#151B54,#1E90FF); color:#fff;">
  <div class="container py-4">
    <span class="badge bg-light text-dtc-night mb-2 px-3">Free Quote</span>
    <h1 class="display-5 fw-bold mb-2">Tell Us About Your Project</h1>
    <p class="lead mb-0 opacity-75">Fill out the form below and we'll get back to you with a proposal within 24–48 hours.</p>
  </div>
</section>

<section class="py-5">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-9">
        <div class="card card-dtc p-4 p-md-5">
          <?php display_flashes(); ?>
          <form action="<?= BASE_URL ?>/public/send_quote.php" method="POST" novalidate>
            <?= csrf_field() ?>
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label small fw-semibold">Your Name *</label>
                <input type="text" name="name" class="form-control" required>
              </div>
              <div class="col-md-6">
                <label class="form-label small fw-semibold">Email *</label>
                <input type="email" name="email" class="form-control" required>
              </div>
              <div class="col-md-6">
                <label class="form-label small fw-semibold">Phone</label>
                <input type="text" name="phone" class="form-control">
              </div>
              <div class="col-md-6">
                <label class="form-label small fw-semibold">Service Type *</label>
                <select name="service_type" class="form-select" required>
                  <option value="">Select…</option>
                  <option>Web Development</option>
                  <option>Mobile App</option>
                  <option>Custom Software</option>
                  <option>UI / UX Design</option>
                  <option>Maintenance & Support</option>
                  <option>Other</option>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label small fw-semibold">Budget Range</label>
                <select name="budget_range" class="form-select">
                  <option value="">Select…</option>
                  <option>Under $500</option>
                  <option>$500 – $2,000</option>
                  <option>$2,000 – $5,000</option>
                  <option>$5,000 – $20,000</option>
                  <option>$20,000+</option>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label small fw-semibold">Expected Timeline</label>
                <select name="timeline" class="form-select">
                  <option value="">Select…</option>
                  <option>ASAP</option>
                  <option>Within 1 month</option>
                  <option>1–3 months</option>
                  <option>3–6 months</option>
                  <option>Flexible</option>
                </select>
              </div>
              <div class="col-12">
                <label class="form-label small fw-semibold">Project Details *</label>
                <textarea name="details" class="form-control" rows="6" required
                  placeholder="Describe your project goals, features, target users, and any references you like…"></textarea>
              </div>
              <div class="col-12">
                <button class="btn btn-dtc-primary btn-lg px-4">
                  <i class="bi bi-send me-1"></i> Request Quote
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>