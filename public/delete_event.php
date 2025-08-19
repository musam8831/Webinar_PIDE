<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();

$payload = json_decode(file_get_contents('php://input'), true) ?? [];
$id = (int)($payload['id'] ?? 0);
if ($id <= 0) { http_response_code(422); echo json_encode(['error'=>'Invalid ID']); exit; }

$ownerStmt = $pdo->prepare('SELECT initiated_by FROM webinars WHERE id = ?');
$ownerStmt->execute([$id]); $owner = $ownerStmt->fetchColumn();
if (!$owner) { http_response_code(404); echo json_encode(['error'=>'Not found']); exit; }

$me = $_SESSION['user'];
if ((int)$owner !== (int)$me['id'] && $me['role'] !== 'admin') { http_response_code(403); echo json_encode(['error'=>'Only initiator or admin can delete']); exit; }

$pdo->prepare('DELETE FROM webinars WHERE id = ?')->execute([$id]);
echo json_encode(['ok'=>true]);
