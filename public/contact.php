<?php
require_once __DIR__ . '/../includes/functions.php';
$page_title = 'Contact Us';
$active_nav = 'contact';
include __DIR__ . '/../includes/header.php';
?>

<section class="py-5" style="background: linear-gradient(135deg,#151B54,#1E90FF); color:#fff;">
  <div class="container py-4">
    <span class="badge bg-light text-dtc-night mb-2 px-3">Contact</span>
    <h1 class="display-5 fw-bold mb-2">Get In Touch</h1>
    <p class="lead mb-0 opacity-75">We usually reply within 24 hours.</p>
  </div>
</section>

<section class="py-5">
  <div class="container">
    <div class="row g-5">
      <div class="col-lg-5">
        <h3 class="fw-bold text-dtc-night mb-3">Reach Us</h3>
        <p class="text-muted">Whether you have a project in mind, a question, or just want to say hi — we'd love to hear from you.</p>
        <div class="d-flex mb-3"><i class="bi bi-geo-alt fs-4 text-dtc-dodger me-3"></i><div><strong>Office</strong><br><span class="text-muted">Monrovia, Liberia</span></div></div>
        <div class="d-flex mb-3"><i class="bi bi-envelope fs-4 text-dtc-dodger me-3"></i><div><strong>Email</strong><br><a href="mailto:info@donutstec.com" class="text-muted">info@donutstec.com</a></div></div>
        <div class="d-flex mb-3"><i class="bi bi-telephone fs-4 text-dtc-dodger me-3"></i><div><strong>Phone</strong><br><span class="text-muted value="<?= e($s['site_phone']??'') ?>">+231 XXX XXX XXXX</span></div></div>
        <div class="d-flex"><i class="bi bi-clock fs-4 text-dtc-dodger me-3"></i><div><strong>Hours</strong><br><span class="text-muted">Mon–Fri, 9am – 5pm GMT</span></div></div>
      </div>
      <div class="col-lg-7">
        <div class="card card-dtc p-4 p-md-5">
          <h4 class="fw-bold text-dtc-night mb-3">Send Us a Message</h4>
          <?php display_flashes(); ?>
          <form action="<?= BASE_URL ?>/public/send_contact.php" method="POST" novalidate>
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
                <label class="form-label small fw-semibold">Subject *</label>
                <input type="text" name="subject" class="form-control" required>
              </div>
              <div class="col-12">
                <label class="form-label small fw-semibold">Message *</label>
                <textarea name="message" class="form-control" rows="5" required></textarea>
              </div>
              <div class="col-12">
                <button class="btn btn-dtc-primary px-4"><i class="bi bi-send me-1"></i> Send Message</button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>