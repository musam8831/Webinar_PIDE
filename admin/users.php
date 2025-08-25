<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin();

$users = $pdo->query('SELECT id,name,email,role,created_at FROM users ORDER BY id DESC')->fetchAll();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>User Management - PIDE Webinar Booking</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/PIDETheme.css" rel="stylesheet">

</head>
<body>
  <?php include __DIR__ . '/../public/navbar.php'; ?>

  <div class="container my-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h4 class="fw-bold text-success">
        <i class="bi bi-people-fill me-2"></i> User Management
      </h4>
      <div>
        <a href="../public/index.php" class="btn btn-outline-secondary btn-sm me-2">Back</a>
        <a href="user_add.php" class="btn btn-success btn-sm">+ Add User</a>
      </div>
    </div>

    <div class="card shadow-sm border-0 rounded-3">
      <div class="card-body p-0">
        <table class="table table-hover mb-0 align-middle">
          <thead>
            <tr>
              <th>ID</th>
              <th>Name</th>
              <th>Email</th>
              <th>Role</th>
              <th>Created</th>
              <th class="text-center">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach($users as $u): ?>
              <tr>
                <td><?= $u['id'] ?></td>
                <td><?= htmlspecialchars($u['name']) ?></td>
                <td><?= htmlspecialchars($u['email']) ?></td>
                <td><span class="badge bg-success"><?= $u['role'] ?></span></td>
                <td><?= date("M d, Y", strtotime($u['created_at'])) ?></td>
                <td class="text-center">
                  <a class="btn btn-sm btn-outline-primary me-1" href="user_edit.php?id=<?= $u['id'] ?>">Edit</a>
                  <a class="btn btn-sm btn-outline-danger" href="user_delete.php?id=<?= $u['id'] ?>" onclick="return confirm('Delete user?')">Delete</a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.js"></script>
</body>
</html>
