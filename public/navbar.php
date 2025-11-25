<?php 
$user = $_SESSION['user'] ?? null; 
?>
<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm mb-3">
  <div class="container-fluid">
    <!-- Brand / Logo -->
    <a class="navbar-brand d-flex align-items-center fw-bold text-success" href="../public/index.php">
      <img src="../assets/img/_PIDE LOGO White PNG.png" 
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
          <a href="../admin/users.php" class="btn btn-outline-success btn-sm me-2">
            User Management
          </a>
          <a href="../public/yearly_dashboard.php" class="btn btn-outline-success btn-sm me-2">
            Yearly Dashboard
          </a>
          <!-- NEW: Reports button -->
          <a href="../admin/reports.php" class="btn btn-outline-success btn-sm me-2">Reports</a>
        <?php endif; ?>

        <a href="logout.php" class="btn btn-outline-danger btn-sm">Logout</a>
      <?php else: ?>
        <a href="login.php" class="btn btn-success btn-sm">Login</a>
      <?php endif; ?>
    </div>
  </div>
</nav>
