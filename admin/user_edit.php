<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin();
$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT id,name,email,role FROM users WHERE id=?'); $stmt->execute([$id]); $u=$stmt->fetch();
if(!$u){ header('Location: users.php'); exit; }
if($_SERVER['REQUEST_METHOD']==='POST'){
  $name=trim($_POST['name']); $email=trim($_POST['email']); $role=$_POST['role'];
  if(!empty($_POST['password'])){
    $hash=password_hash($_POST['password'],PASSWORD_DEFAULT);
    $pdo->prepare('UPDATE users SET name=?,email=?,role=?,password_hash=? WHERE id=?')->execute([$name,$email,$role,$hash,$id]);
  } else {
    $pdo->prepare('UPDATE users SET name=?,email=?,role=? WHERE id=?')->execute([$name,$email,$role,$id]);
  }
  header('Location: users.php'); exit;
}
?>
<!doctype html><html><head><meta charset="utf-8"><title>Edit User</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head><body class="p-3"><div class="container">
<h4>Edit User</h4>
<form method="post">
  <div class="mb-2"><label>Name</label><input name="name" class="form-control" value="<?=htmlspecialchars($u['name'])?>" required></div>
  <div class="mb-2"><label>Email</label><input name="email" type="email" class="form-control" value="<?=htmlspecialchars($u['email'])?>" required></div>
  <div class="mb-2"><label>New Password (leave blank to keep)</label><input name="password" type="password" class="form-control"></div>
  <div class="mb-2"><label>Role</label><select name="role" class="form-select"><option value="user" <?= $u['role']=='user'?'selected':'' ?>>user</option><option value="admin" <?= $u['role']=='admin'?'selected':'' ?>>admin</option></select></div>
  <button class="btn btn-primary">Save</button> <a href="users.php" class="btn btn-secondary">Cancel</a>
</form>
</div></body></html>
