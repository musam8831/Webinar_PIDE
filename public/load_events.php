<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();
$events = [];
$stmt = $pdo->query('SELECT w.id, w.title, w.start_at, w.end_at FROM webinars w ORDER BY w.start_at ASC');
while ($row = $stmt->fetch()) {
  $events[] = [
    'id' => $row['id'],
    'title' => $row['title'],
    'start' => gmdate('c', strtotime($row['start_at'])),
    'end' => gmdate('c', strtotime($row['end_at'])),
  ];
}
header('Content-Type: application/json');
echo json_encode($events);
