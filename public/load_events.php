<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();

$me = $_SESSION['user'];
$events = [];
$stmt = $pdo->query('
  SELECT w.id, w.title, w.start_at, w.end_at, w.initiated_by, u.name AS initiator_name, u.email AS initiator_email
  FROM webinars w
  JOIN users u ON u.id = w.initiated_by
  ORDER BY w.start_at ASC
');
while ($row = $stmt->fetch()) {
  $canDelete = ($me['role'] === 'admin') || ((int)$me['id'] === (int)$row['initiated_by']);
  $canEdit   = $canDelete; // same logic; feel free to customize

  $events[] = [
    'id' => $row['id'],
    'title' => $row['title'],
    'start' => gmdate('c', strtotime($row['start_at'])), // ISO 8601 UTC
    'end'   => gmdate('c', strtotime($row['end_at'])),
    'extendedProps' => [
      'initiator_name'  => $row['initiator_name'],
      'initiator_email' => $row['initiator_email'],
      'can_delete' => $canDelete,
      'can_edit'   => $canEdit
    ],
  ];
}
header('Content-Type: application/json');
echo json_encode($events);
