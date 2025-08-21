
<?php
require_once __DIR__ . '/../includes/db.php';
$config = require __DIR__ . '/../includes/config.php';
session_start();
if (!isset($_GET['code'])) { header('Location: login.php'); exit; }
$code = $_GET['code'];

// Exchange code for token
$ch = curl_init('https://oauth2.googleapis.com/token');
curl_setopt_array($ch, [
  CURLOPT_POST => true,
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
  CURLOPT_POSTFIELDS => http_build_query([
    'code' => $code,
    'client_id' => $config['google']['client_id'],
    'client_secret' => $config['google']['client_secret'],
    'redirect_uri' => $config['google']['redirect_uri'],
    'grant_type' => 'authorization_code'
  ])
]);
$tokenRes = curl_exec($ch);
if ($tokenRes === false) { die('Curl error: '.curl_error($ch)); }
curl_close($ch);
$token = json_decode($tokenRes, true);
if (!isset($token['access_token'])) { die('Failed to get access token.'); }

// Fetch user info
$ch2 = curl_init('https://www.googleapis.com/oauth2/v2/userinfo');
curl_setopt_array($ch2, [
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $token['access_token']]
]);
$infoRes = curl_exec($ch2);
if ($infoRes === false) { die('Curl error: '.curl_error($ch2)); }
curl_close($ch2);
$info = json_decode($infoRes, true);
$email = $info['email'] ?? null;
$name  = $info['name'] ?? ($info['given_name'] ?? 'Google User');

if (!$email) { die('Email not provided by Google.'); }

// Restrict to pide.org.pk domain
if (substr($email, -12) !== '@pide.org.pk') {
    echo "Access denied. Only pide.org.pk email addresses are allowed.";
    exit;
}

// Ensure user exists
$stmt = $pdo->prepare('SELECT * FROM users WHERE email=? LIMIT 1');
$stmt->execute([$email]);
$user = $stmt->fetch();
if (!$user) {
  $ins = $pdo->prepare('INSERT INTO users (name,email,role) VALUES (?,?,?)');
  $ins->execute([$name,$email,'user']);
  $uid = $pdo->lastInsertId();
  $user = ['id'=>$uid,'name'=>$name,'email'=>$email,'role'=>'user'];
} else {
  $user = ['id'=>$user['id'],'name'=>$user['name'],'email'=>$user['email'],'role'=>$user['role']];
}

$_SESSION['user'] = $user;
header('Location: index.php'); exit;
