
<?php
session_start();
$config = require __DIR__ . '/../includes/config.php';
$params = [
  'response_type' => 'code',
  'client_id'     => $config['google']['client_id'],
  'redirect_uri'  => $config['google']['redirect_uri'],
  'scope'         => 'openid email profile',
  'access_type'   => 'online',
  'prompt'        => 'select_account',
  'include_granted_scopes' => 'true'
];
$auth_url = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
header('Location: ' . $auth_url); exit;
