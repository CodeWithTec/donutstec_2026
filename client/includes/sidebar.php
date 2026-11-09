<?php $active_page = $active_page ?? ''; ?>
<aside class="client-sidebar">
  <a href="<?= BASE_URL ?>/client/dashboard.php" class="brand">Donuts<span>Tec</span></a>
  <nav class="nav flex-column py-2">
    <a class="nav-link <?= $active_page==='dashboard'?'active':'' ?>" href="<?= BASE_URL ?>/client/dashboard.php">
      <i class="bi bi-speedometer2"></i> Dashboard
    </a>
    <a class="nav-link <?= $active_page==='projects'?'active':'' ?>" href="<?= BASE_URL ?>/client/projects.php">
      <i class="bi bi-kanban"></i> My Projects
    </a>
    <a class="nav-link <?= $active_page==='messages'?'active':'' ?>" href="<?= BASE_URL ?>/client/messages.php">
      <i class="bi bi-chat-dots"></i> Messages
    </a>
    <a class="nav-link <?= $active_page==='tickets'?'active':'' ?>" href="<?= BASE_URL ?>/client/tickets.php">
      <i class="bi bi-life-preserver"></i> Support
    </a>
    <a class="nav-link <?= $active_page==='profile'?'active':'' ?>" href="<?= BASE_URL ?>/client/profile.php">
      <i class="bi bi-person-circle"></i> My Profile
    </a>
  </nav>
  <div class="sidebar-footer">
    <div class="small mb-1"><i class="bi bi-envelope me-1"></i> info@donutstec.com</div>
    <div class="small"><i class="bi bi-headset me-1"></i> Need help? Open a ticket</div>
  </div>
</aside>