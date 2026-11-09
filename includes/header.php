<?php
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/url.php';
require_once __DIR__ . '/seo.php';

$current_user = current_user();
$active_nav = $active_nav ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?= seo_meta() ?>
  <link rel="icon" type="image/png" href="<?= BASE_URL ?>/assets/img/favicon.png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
  <?php if (current_path() === '/public/index.php' || current_path() === '/'): ?>
    <?= seo_jsonld() ?>
  <?php endif; ?>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dtc sticky-top">
  <div class="container">
    <a class="navbar-brand" href="<?= BASE_URL ?>/public/index.php">
      <img src="<?= BASE_URL ?>/assets/img/logo.png" alt="Bootstrap" width="30" height="30">
      Donuts<span>Tec</span> Liberia</a>
    <button class="navbar-toggler text-white border-0" data-bs-toggle="collapse" data-bs-target="#navMain">
      <i class="bi bi-list fs-2"></i>
    </button>
    <div class="collapse navbar-collapse" id="navMain">
      <ul class="navbar-nav ms-auto align-items-lg-center">
        <li class="nav-item"><a class="nav-link <?= $active_nav==='home'?'active':'' ?>" href="<?= BASE_URL ?>/public/index.php">Home</a></li>
        <li class="nav-item"><a class="nav-link <?= $active_nav==='about'?'active':'' ?>" href="<?= BASE_URL ?>/public/about.php">About</a></li>
        <li class="nav-item"><a class="nav-link <?= $active_nav==='services'?'active':'' ?>" href="<?= BASE_URL ?>/public/services.php">Services</a></li>
        <li class="nav-item"><a class="nav-link <?= $active_nav==='portfolio'?'active':'' ?>" href="<?= BASE_URL ?>/public/portfolio.php">Portfolio</a></li>
        <li class="nav-item"><a class="nav-link <?= $active_nav==='blog'?'active':'' ?>" href="<?= BASE_URL ?>/public/blog.php">Blog</a></li>
        <li class="nav-item"><a class="nav-link <?= $active_nav==='contact'?'active':'' ?>" href="<?= BASE_URL ?>/public/contact.php">Contact</a></li>
        <?php if ($current_user): ?>
          <li class="nav-item ms-lg-3">
            <a class="btn btn-dtc-accent px-3"
               href="<?= BASE_URL ?>/<?= $current_user['role'] ?>/dashboard.php">
              <i class="bi bi-speedometer2 me-1"></i> Dashboard
            </a>
          </li>
        <?php else: ?>
          <li class="nav-item ms-lg-3">
            <a class="btn btn-dtc-outline text-white px-3" href="<?= BASE_URL ?>/public/login.php">Login</a>
          </li>
          <li class="nav-item ms-lg-2">
            <a class="btn btn-dtc-accent px-3" href="<?= BASE_URL ?>/public/quote.php">Get a Quote</a>
          </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<main></main>