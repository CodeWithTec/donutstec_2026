<?php
$active_page = $active_page ?? '';
$db = Database::getConnection();
$me = current_user();
$unreadMsgs    = (int)$db->query("SELECT COUNT(*) FROM messages WHERE receiver_id = {$me['id']} AND is_read = 0")->fetchColumn();
$openTickets   = (int)$db->query("SELECT COUNT(*) FROM tickets WHERE status IN ('open','in_progress')")->fetchColumn();
$newQuotes     = (int)$db->query("SELECT COUNT(*) FROM quotes WHERE status = 'new'")->fetchColumn();
$unreadContact = (int)$db->query("SELECT COUNT(*) FROM contact_messages WHERE is_read = 0")->fetchColumn();
?>
<aside class="admin-sidebar">
  <a href="<?= BASE_URL ?>/admin/dashboard.php" class="brand">Donuts<span>Tec</span> Admin</a>
  <nav class="nav flex-column py-2">
    <div class="nav-section">Overview</div>
    <a class="nav-link <?= $active_page==='dashboard'?'active':'' ?>" href="<?= BASE_URL ?>/admin/dashboard.php">
      <i class="bi bi-speedometer2"></i> Dashboard
    </a>

    <div class="nav-section">Clients & Projects</div>
    <a class="nav-link <?= $active_page==='users'?'active':'' ?>" href="<?= BASE_URL ?>/admin/users.php">
      <i class="bi bi-people"></i> Clients
    </a>
    <a class="nav-link <?= $active_page==='projects'?'active':'' ?>" href="<?= BASE_URL ?>/admin/projects.php">
      <i class="bi bi-kanban"></i> Projects
    </a>

    <div class="nav-section">Inbox</div>
    <a class="nav-link <?= $active_page==='messages'?'active':'' ?>" href="<?= BASE_URL ?>/admin/messages.php">
      <i class="bi bi-chat-dots"></i> Messages
      <?php if ($unreadMsgs>0): ?><span class="badge-pill"><?= $unreadMsgs ?></span><?php endif; ?>
    </a>
    <a class="nav-link <?= $active_page==='tickets'?'active':'' ?>" href="<?= BASE_URL ?>/admin/tickets.php">
      <i class="bi bi-life-preserver"></i> Tickets
      <?php if ($openTickets>0): ?><span class="badge-pill"><?= $openTickets ?></span><?php endif; ?>
    </a>
    <a class="nav-link <?= $active_page==='quotes'?'active':'' ?>" href="<?= BASE_URL ?>/admin/quotes.php">
      <i class="bi bi-file-earmark-text"></i> Quotes
      <?php if ($newQuotes>0): ?><span class="badge-pill"><?= $newQuotes ?></span><?php endif; ?>
    </a>
    <a class="nav-link <?= $active_page==='contact'?'active':'' ?>" href="<?= BASE_URL ?>/admin/contact-messages.php">
      <i class="bi bi-envelope"></i> Contact
      <?php if ($unreadContact>0): ?><span class="badge-pill"><?= $unreadContact ?></span><?php endif; ?>
    </a>

    <div class="nav-section">Content</div>
    <a class="nav-link <?= $active_page==='portfolio'?'active':'' ?>" href="<?= BASE_URL ?>/admin/portfolio.php">
      <i class="bi bi-images"></i> Portfolio
    </a>
    <a class="nav-link <?= $active_page==='blog'?'active':'' ?>" href="<?= BASE_URL ?>/admin/blog.php">
      <i class="bi bi-journal-richtext"></i> Blog
    </a>
    <a class="nav-link <?= $active_page==='testimonials'?'active':'' ?>" href="<?= BASE_URL ?>/admin/testimonials.php">
      <i class="bi bi-star"></i> Testimonials
    </a>
    <a class="nav-link <?= $active_page==='feedback'?'active':'' ?>" href="<?= BASE_URL ?>/admin/feedback.php">
  <i class="bi bi-chat-square-quote"></i> Project Feedback
</a>

    <div class="nav-section">Finance</div>
    <a class="nav-link <?= $active_page==='invoices'?'active':'' ?>" href="<?= BASE_URL ?>/admin/invoices.php">
      <i class="bi bi-receipt"></i> Invoices
    </a>

    <div class="nav-section">System</div>
    <a class="nav-link <?= $active_page==='newsletter'?'active':'' ?>" href="<?= BASE_URL ?>/admin/newsletter.php">
      <i class="bi bi-envelope-paper"></i> Newsletter
    </a>
    <a class="nav-link <?= $active_page==='2fa'?'active':'' ?>" href="<?= BASE_URL ?>/admin/2fa.php">
  <i class="bi bi-shield-lock"></i> 2FA Security
</a>
    <a class="nav-link <?= $active_page==='settings'?'active':'' ?>" href="<?= BASE_URL ?>/admin/settings.php">
      <i class="bi bi-gear"></i> Settings
    </a>
    <a class="nav-link <?= $active_page==='backup'?'active':'' ?>" href="<?= BASE_URL ?>/admin/backup.php">
  <i class="bi bi-hdd-stack"></i> Backups
</a>
  </nav>
</aside>