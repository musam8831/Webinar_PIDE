<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin();
$id = (int)($_GET['id'] ?? 0);
if ($id>0) { $pdo->prepare('DELETE FROM users WHERE id=?')->execute([$id]); }
header('Location: users.php'); exit;
