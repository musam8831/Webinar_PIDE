<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin();
$error='';
if ($_SERVER['REQUEST_METHOD']==='POST'){
  $name=trim($_POST['name']); $email=trim($_POST['email']); $role=$_POST['role']??'user'; $pass=$_POST['password']??'';
  if($name && $email && $pass){
    $hash=password_hash($pass,PASSWORD_DEFAULT);
    $stmt=$pdo->prepare('INSERT INTO users (name,email,password_hash,role) VALUES (?,?,?,?)');
    $stmt->execute([$name,$email,$hash,$role]);
    header('Location: users.php'); exit;
  } else { $error='All fields required'; }
}
?>
<!doctype html><html><head><meta charset="utf-8"><title>Add User</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head><body class="p-3 bg-light"><div class="container">
<h4>Add User</h4>
<?php if($error) echo '<div class="alert alert-danger">'.$error.'</div>'; ?>
<form method="post" class="card card-body shadow-sm bg-white">
  <div class="mb-2"><label class="form-label">Name</label><input name="name" class="form-control" required></div>
  <div class="mb-2"><label class="form-label">Email</label><input name="email" type="email" class="form-control" required></div>
  <div class="mb-2"><label class="form-label">Password</label><input name="password" type="password" class="form-control" required></div>
  <div class="mb-2"><label class="form-label">Role</label><select name="role" class="form-select"><option value="user">user</option><option value="admin">admin</option></select></div>
  <div><button class="btn btn-success">Create</button> <a href="users.php" class="btn btn-secondary">Cancel</a></div>
</form>
</div></body></html>
