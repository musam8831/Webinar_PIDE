
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
    if ($user && !empty($user['password_hash']) && password_verify($pass, $user['password_hash'])) {
      $_SESSION['user'] = ['id'=>$user['id'],'name'=>$user['name'],'email'=>$user['email'],'role'=>$user['role']];
      header('Location: index.php'); exit;
    } else { $error = 'Invalid credentials'; }
  } else { $error = 'Email and password required'; }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>PIDE Webinar Booking – Login</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background: #f7f9fa;
    }
    .login-container {
      max-width: 400px;
      margin: 80px auto;
      background: #ffffff;
      padding: 30px;
      border-radius: 8px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    .login-logo {
      width: 180px;
      margin-bottom: 20px;
    }
    .btn-primary {
      background-color: #00703c;
      border-color: #006b34;
    }
    .btn-primary:hover {
      background-color: #005a2a;
    }
    .form-control:focus {
      border-color: #00703c;
      box-shadow: none;
    }
  </style>
</head>
<body>
  <div class="login-container text-center">
    <img src="../assets/img/pide-logo.png" alt="PIDE Logo" class="login-logo">
    <h3 class="mb-4">PIDE Webinar Booking</h3>
    <?php if ($error): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="post">
      <div class="mb-3 text-start"><label>Email</label><input type="email" name="email" class="form-control" required></div>
      <div class="mb-3 text-start"><label>Password</label><input type="password" name="password" class="form-control" required></div>
      <button type="submit" class="btn btn-primary w-100">Login</button>
    </form>
    <div class="mt-3">
      <!-- <a href="google_login.php" title="Log in with Google" class="p-12-24 b-radius-8 gray-border social" data-qa="google-login-button">
        <img src="https://auth.hostinger.com/assets/images/oauth/google.svg" alt="Google">
      </a> -->
      <a class="btn google-btn" href="google_login.php">
        <img src="https://www.gstatic.com/firebasejs/ui/2.0.0/images/auth/google.svg" alt="" style="width: 50px; height: 50px;">
        <span> Sign in with Google</span>
      </a>
    </div>
  </div>
</body>
</html>

