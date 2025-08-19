<?php
require __DIR__ . '/includes/db.php';
$sql = file_get_contents(__DIR__ . '/schema.sql');
$pdo->exec($sql);
$check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$check->execute(['admin@example.com']);
if (!$check->fetch()) {
  $hash = password_hash('Admin@123', PASSWORD_DEFAULT);
  $ins = $pdo->prepare("INSERT INTO users (name,email,password_hash,role) VALUES (?,?,?,?)");
  $ins->execute(['Administrator','admin@example.com',$hash,'admin']);
  echo "Admin created: admin@example.com / Admin@123\n";
} else { echo "Admin already exists.\n"; }
echo "Setup complete.\n";
