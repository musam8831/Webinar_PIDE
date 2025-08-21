
<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin();
$users = $pdo->query('SELECT id,name,email,role,created_at FROM users ORDER BY id DESC')->fetchAll();
?>
<!doctype html><html><head><meta charset="utf-8"><title>User Management</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body class="p-3 bg-light">
<div class="container">
<div class="d-flex justify-content-between mb-3">
  <h4>Users</h4>
  <div>
    <a href="../public/index.php" class="btn btn-secondary btn-sm">Back</a>
    <a href="user_add.php" class="btn btn-success btn-sm">Add User</a>
  </div>
</div>
<table class="table table-striped bg-white shadow-sm">
  <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Created</th><th>Actions</th></tr></thead>
  <tbody>
  <?php foreach($users as $u): ?>
    <tr>
      <td><?= $u['id'] ?></td>
      <td><?= htmlspecialchars($u['name']) ?></td>
      <td><?= htmlspecialchars($u['email']) ?></td>
      <td><?= $u['role'] ?></td>
      <td><?= $u['created_at'] ?></td>
      <td>
        <a class="btn btn-sm btn-primary" href="user_edit.php?id=<?= $u['id'] ?>">Edit</a>
        <a class="btn btn-sm btn-danger" href="user_delete.php?id=<?= $u['id'] ?>" onclick="return confirm('Delete user?')">Delete</a>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div>
</body></html>
