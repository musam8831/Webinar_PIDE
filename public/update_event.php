<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();

$payload = json_decode(file_get_contents('php://input'), true) ?? [];
$id    = (int)($payload['id'] ?? 0);
$title = trim($payload['title'] ?? '');
$start = $payload['start'] ?? '';
$end   = $payload['end'] ?? '';

if ($id <= 0 || !$title || !$start || !$end) { http_response_code(422); echo json_encode(['error'=>'All fields are required']); exit; }

# Permission: only initiator or admin
$ownerStmt = $pdo->prepare('SELECT initiated_by FROM webinars WHERE id = ?');
$ownerStmt->execute([$id]);
$owner = $ownerStmt->fetchColumn();
if (!$owner) { http_response_code(404); echo json_encode(['error'=>'Not found']); exit; }
$me = $_SESSION['user'];
if ((int)$owner !== (int)$me['id'] && $me['role'] !== 'admin') { http_response_code(403); echo json_encode(['error'=>'Only initiator or admin can update']); exit; }

$startUtc = (new DateTime($start))->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s');
$endUtc   = (new DateTime($end))->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s');
if ($endUtc <= $startUtc) { http_response_code(422); echo json_encode(['error'=>'End time must be after start time']); exit; }

# Overlap check excluding this event
$ov = $pdo->prepare('SELECT COUNT(*) FROM webinars WHERE id <> ? AND (? < end_at) AND (? > start_at)');
$ov->execute([$id, $startUtc, $endUtc]);
if ((int)$ov->fetchColumn() > 0) { http_response_code(409); echo json_encode(['error'=>'Selected slot overlaps an existing webinar']); exit; }

$upd = $pdo->prepare('UPDATE webinars SET title=?, start_at=?, end_at=? WHERE id=?');
$upd->execute([$title, $startUtc, $endUtc, $id]);

echo json_encode(['ok'=>true]);
