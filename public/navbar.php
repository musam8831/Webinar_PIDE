<?php
$user = $_SESSION['user'] ?? null;
$config = require __DIR__ . '/../includes/config.php';
$base = rtrim($config['base_url'], '/');
?>
<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm mb-3">
  <div class="container-fluid">
    <!-- Brand / Logo -->
    <a class="navbar-brand d-flex align-items-center fw-bold text-success" href="<?= $base ?>/index.php">
      <img src="<?= $base ?>/assets/img/_PIDE LOGO White PNG.png" 
           alt="PIDE Logo" 
           width="200" 
           height="110" 
           class="me-2">
      
    </a>

    <!-- Right Section -->
    <div class="d-flex ms-auto align-items-center">
      <?php if ($user): ?>
       <span class="navbar-brand me-3 fw-semibold text-success">
          Hello, <?=htmlspecialchars($user['name'])?> 
          <small class="fw-normal">(<?=htmlspecialchars($user['role'])?>)</small>
        </span>

        <?php if ($user['role'] === 'admin'): ?>
          <a href="<?= $base ?>/admin/users.php" class="btn btn-outline-success btn-sm me-2">
            User Management
          </a>
          <a href="<?= $base ?>/admin/categories.php" class="btn btn-outline-success btn-sm me-2">
            Categories
          </a>
          <a href="<?= $base ?>/admin/pending_webinars.php" class="btn btn-outline-warning btn-sm me-2">
            <i class="bi bi-exclamation-circle"></i> Pending Approval
          </a>
          <a href="<?= $base ?>/public/yearly_dashboard.php" class="btn btn-outline-success btn-sm me-2">
            Yearly Dashboard
          </a>
          <a href="<?= $base ?>/admin/reports.php" class="btn btn-outline-success btn-sm me-2">Reports</a>
        <?php endif; ?>
        
        <a href="<?= $base ?>/public/webinars_list.php" class="btn btn-outline-info btn-sm me-2">
          <i class="bi bi-list-check"></i> My Webinars
        </a>

        <a href="<?= $base ?>/public/logout.php" class="btn btn-outline-danger btn-sm">Logout</a>
      <?php else: ?>
        <a href="<?= $base ?>/public/login.php" class="btn btn-success btn-sm">Login</a>
      <?php endif; ?>
    </div>
  </div>
</nav>
