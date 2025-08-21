
<?php $user = $_SESSION['user'] ?? null; ?>
<nav class="navbar navbar-expand-lg bg-white border-bottom mb-3">
  <div class="container-fluid">
    <a class="navbar-brand" href="index.php">Webinar Booking</a>
    <div class="d-flex ms-auto align-items-center">
      <?php if ($user): ?>
        <span class="me-3">Hello, <?=htmlspecialchars($user['name'])?> (<?=htmlspecialchars($user['role'])?>)</span>
        <?php if ($user['role'] === 'admin'): ?>
          <a href="../admin/users.php" class="btn btn-outline-primary btn-sm me-2">User Management</a>
          <a href="yearly_dashboard.php" class="btn btn-outline-success btn-sm me-2">Yearly Dashboard</a>
        <?php endif; ?>
        <a href="logout.php" class="btn btn-outline-secondary btn-sm">Logout</a>
      <?php else: ?>
        <a href="login.php" class="btn btn-primary btn-sm">Login</a>
      <?php endif; ?>
    </div>
  </div>
</nav>
