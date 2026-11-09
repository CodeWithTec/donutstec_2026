<?php
/**
 * Client portal layout header.
 * Expects $page_title.
 */
require_once __DIR__ . '/../../includes/functions.php';
$me = current_user();
$page_title = $page_title ?? 'Client Dashboard';
$active_page = $active_page ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($page_title) ?> — DonutsTecLiberia Client</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
  <style>
    body { background:#F8F9FC; }
    .client-wrapper { display:flex; min-height:100vh; }
    .client-sidebar {
      width: 260px;
      background: var(--dtc-night);
      color: #fff;
      flex-shrink: 0;
      position: fixed;
      top: 0; bottom: 0; left: 0;
      overflow-y: auto;
      z-index: 1030;
      transition: transform .25s;
    }
    .client-sidebar .brand {
      padding: 1.25rem 1.5rem;
      border-bottom: 1px solid rgba(255,255,255,0.08);
      font-weight: 800;
      font-size: 1.25rem;
      color: #fff;
      text-decoration: none;
      display: block;
    }
    .client-sidebar .brand span { color: var(--dtc-dodger); }
    .client-sidebar .nav-link {
      color: rgba(255,255,255,0.75);
      padding: 0.75rem 1.5rem;
      display: flex;
      align-items: center;
      gap: 0.75rem;
      border-left: 3px solid transparent;
      transition: all .2s;
    }
    .client-sidebar .nav-link:hover {
      background: rgba(30,144,255,0.12);
      color: #fff;
    }
    .client-sidebar .nav-link.active {
      background: rgba(30,144,255,0.18);
      color: var(--dtc-sky);
      border-left-color: var(--dtc-dodger);
      font-weight: 600;
    }
    .client-sidebar .nav-link i { font-size: 1.15rem; width: 22px; text-align: center; }
    .client-sidebar .sidebar-footer {
      padding: 1rem 1.5rem;
      border-top: 1px solid rgba(255,255,255,0.08);
      font-size: 0.85rem;
      color: rgba(255,255,255,0.6);
    }
    .client-main {
      margin-left: 260px;
      flex: 1;
      min-width: 0;
      display: flex;
      flex-direction: column;
    }
    .client-topbar {
      background: #fff;
      border-bottom: 1px solid #E9ECF5;
      padding: 0.85rem 1.5rem;
      display: flex;
      align-items: center;
      justify-content: space-between;
      position: sticky;
      top: 0;
      z-index: 1020;
      box-shadow: 0 1px 8px rgba(21,27,84,0.05);
    }
    .client-content { padding: 2rem 1.5rem; flex: 1; }
    .avatar-badge {
      width: 38px; height: 38px;
      border-radius: 50%;
      background: var(--dtc-dodger);
      color: #fff;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-weight: 700;
    }
    @media (max-width: 991px) {
      .client-sidebar { transform: translateX(-100%); }
      .client-sidebar.open { transform: translateX(0); }
      .client-main { margin-left: 0; }
    }
    .stat-card {
      background: #fff;
      border-radius: 12px;
      border: 1px solid rgba(130,202,255,0.4);
      padding: 1.25rem;
      box-shadow: 0 2px 12px rgba(21,27,84,0.05);
    }
    .stat-card .stat-icon {
      width: 46px; height: 46px;
      border-radius: 10px;
      display: flex; align-items: center; justify-content: center;
      color: #fff; font-size: 1.25rem;
    }
    .stat-card .stat-value { font-size: 1.75rem; font-weight: 700; color: var(--dtc-night); }
    .stat-card .stat-label { color: var(--dtc-muted); font-size: 0.85rem; }
  </style>
</head>
<body>
<div class="client-wrapper">

  <?php include __DIR__ . '/sidebar.php'; ?>

  <div class="client-main">
    <div class="client-topbar">
      <div class="d-flex align-items-center">
        <button class="btn btn-link text-dark d-lg-none me-2 p-0" onclick="document.querySelector('.client-sidebar').classList.toggle('open')">
          <i class="bi bi-list fs-4"></i>
        </button>
        <h5 class="mb-0 fw-bold text-dtc-night"><?= e($page_title) ?></h5>
      </div>
      <div class="d-flex align-items-center gap-3">
        <a href="<?= BASE_URL ?>/public/index.php" class="small text-muted d-none d-md-inline">
          <i class="bi bi-box-arrow-up-right me-1"></i> View Site
        </a>
        <!-- Notifications bell -->
<div class="dropdown">
  <a href="#" id="notifBell" class="btn btn-link text-dark position-relative p-0 me-1"
     data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
    <i class="bi bi-bell fs-5"></i>
    <span id="notifBadge" class="badge bg-danger position-absolute top-0 start-100 translate-middle"
          style="display:none; font-size:.6rem;">0</span>
  </a>
  <div class="dropdown-menu dropdown-menu-end shadow p-0" style="width:340px; max-height:420px; overflow-y:auto;">
    <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
      <strong class="small">Notifications</strong>
      <a href="<?= BASE_URL ?>/<?= $me['role'] === 'admin' ? 'admin' : 'client' ?>/notifications.php" class="small text-dtc-dodger">View all</a>
    </div>
    <div id="notifDropdown"></div>
  </div>
</div>
        <div class="dropdown">
          <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">
            <span class="avatar-badge me-2"><?= strtoupper(substr($me['full_name'],0,1)) ?></span>
            <span class="d-none d-md-inline text-dark small fw-semibold"><?= e($me['full_name']) ?></span>
          </a>
          <ul class="dropdown-menu dropdown-menu-end shadow">
            <li><a class="dropdown-item" href="<?= BASE_URL ?>/client/profile.php"><i class="bi bi-person me-2"></i>My Profile</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item text-danger" href="<?= BASE_URL ?>/public/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
          </ul>
        </div>
      </div>
    </div>
    <div class="client-content">
      <?php display_flashes(); ?>

      <script>window.DTC_BASE_URL = '<?= BASE_URL ?>';</script>
<script src="<?= BASE_URL ?>/assets/js/notif.js"></script>