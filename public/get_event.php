<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();
$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT w.*, u.name AS initiator_name, u.email AS initiator_email FROM webinars w JOIN users u ON u.id = w.initiated_by WHERE w.id = ?');
$stmt->execute([$id]);
$row = $stmt->fetch();
if (!$row) { echo json_encode(['error'=>'Not found']); exit; }
$me = $_SESSION['user'];
$canDelete = ($me['role'] === 'admin') || ((int)$me['id'] === (int)$row['initiated_by']);
header('Content-Type: application/json');
echo json_encode([
  'id'=>$row['id'],
  'title'=>$row['title'],
  'start'=>gmdate('Y-m-d H:i', strtotime($row['start_at'])),
  'end'=>gmdate('Y-m-d H:i', strtotime($row['end_at'])),
  'initiator_name'=>$row['initiator_name'],
  'initiator_email'=>$row['initiator_email'],
  'can_delete'=>$canDelete
]);
