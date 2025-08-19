<?php
require_once __DIR__ . '/../includes/db.php';
session_start();
if (isset($_SESSION['user'])) { header('Location: index.php'); exit; }
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email'] ?? '');
  $pass  = $_POST['password'] ?? '';
  if ($email && $pass) {
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if ($user && password_verify($pass, $user['password_hash'])) {
      $_SESSION['user'] = ['id'=>$user['id'],'name'=>$user['name'],'email'=>$user['email'],'role'=>$user['role']];
      header('Location: index.php'); exit;
    } else { $error = 'Invalid credentials'; }
  } else { $error = 'Email and password required'; }
}
?>
<!doctype html><html><head><meta charset="utf-8"><title>Login</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head><body class="bg-light">
<div class="container py-5"><div class="row justify-content-center"><div class="col-md-5">
<div class="card shadow-sm"><div class="card-body">
<h4 class="card-title mb-3">Sign in</h4>
<?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
<form method="post">
  <div class="mb-2"><label class="form-label">Email</label><input class="form-control" name="email" type="email" required></div>
  <div class="mb-2"><label class="form-label">Password</label><input class="form-control" name="password" type="password" required></div>
  <button class="btn btn-primary w-100">Login</button>
</form>
</div></div>
<div class="text-center mt-2"><small>Default admin: admin@example.com / Admin@123</small></div>
</div></div></div>
</body></html>
