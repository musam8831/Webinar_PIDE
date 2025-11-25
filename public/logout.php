<?php
session_start();
// Load base_url from config to ensure correct redirect path
$config = require __DIR__ . '/../includes/config.php';
$base = rtrim($config['base_url'], '/');
session_destroy();
header('Location: ' . $base . '/public/login.php');
exit;
?>