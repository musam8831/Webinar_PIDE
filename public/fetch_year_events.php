<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin();
$year = (int)($_GET['year'] ?? date('Y'));
$start = "$year-01-01 00:00:00";
$end = ($year+1) . "-01-01 00:00:00";
$stmt = $pdo->prepare('SELECT w.id, w.title, w.start_at, w.end_at, u.name AS initiator FROM webinars w JOIN users u ON u.id = w.initiated_by WHERE w.start_at >= ? AND w.start_at < ? ORDER BY w.start_at');
$stmt->execute([$start, $end]);
$out = [];
while ($r = $stmt->fetch()) {
  $out[] = [
    'id' => (int)$r['id'],
    'title' => $r['title'],
    'date' => gmdate('Y-m-d', strtotime($r['start_at'])),
    'start' => gmdate('H:i', strtotime($r['start_at'])),
    'end' => gmdate('H:i', strtotime($r['end_at'])),
    'initiator' => $r['initiator']
  ];
}
header('Content-Type: application/json');
echo json_encode($out);
