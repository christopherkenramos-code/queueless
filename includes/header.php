<?php
$u = current_user();
$page = $page ?? '';
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($pageTitle ?? 'QueueLess — MHO Naguilian') ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= $assetBase ?? '' ?>assets/css/style.css">
</head>
<body>
<nav>
  <div class="logo" onclick="location.href='<?= $homeLink ?? 'index.php' ?>'">
    <div class="logo-mark">
      <svg viewBox="0 0 24 24" fill="none"><path d="M12 22c5-3 8-7.5 8-13V5l-8-3-8 3v4c0 5.5 3 10 8 13Z" stroke="#fff" stroke-width="1.8" stroke-linejoin="round"/><path d="M12 8v6M9 11h6" stroke="#fff" stroke-width="1.8" stroke-linecap="round"/></svg>
    </div>
    <div><b>QueueLess</b><small>MHO Naguilian, La Union</small></div>
  </div>
  <div class="nav-links">
    <a href="<?= $boardLink ?? ($assetBase ?? '') . 'board.php' ?>">Display Board</a>
    <?php if ($u && in_array($u['role'], ['staff', 'admin'])): ?>
      <a href="<?= $staffLink ?? ($assetBase ?? '') . 'staff/dashboard.php' ?>">Dashboard</a>
    <?php elseif ($u): ?>
      <a href="<?= $patientLink ?? ($assetBase ?? '') . 'patient/dashboard.php' ?>">Dashboard</a>
    <?php endif; ?>
    <?php if ($u): ?>
      <a href="<?= $logoutLink ?? ($assetBase ?? '') . 'logout.php' ?>">Sign out</a>
    <?php else: ?>
      <a href="<?= $loginLink ?? ($assetBase ?? '') . 'login.php' ?>">Sign in</a>
      <a class="btn btn-primary btn-sm" href="<?= $loginLink ?? ($assetBase ?? '') . 'login.php' ?>">Book Now</a>
    <?php endif; ?>
  </div>
</nav>
