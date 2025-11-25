
<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();
$payload = json_decode(file_get_contents('php://input'), true) ?? [];
$title = trim($payload['title'] ?? ''); 
$category_id = (int)($payload['category_id'] ?? 0);
$start = $payload['start'] ?? ''; 
$end = $payload['end'] ?? '';

if (!$title || !$start || !$end || $category_id <= 0){ 
    http_response_code(422); 
    echo json_encode(['error'=>'All fields are required']); 
    exit; 
}

// Verify category exists and is active
$catStmt = $pdo->prepare('SELECT id FROM categories WHERE id=? AND is_active=1');
$catStmt->execute([$category_id]);
if (!$catStmt->fetch()) {
    http_response_code(422); 
    echo json_encode(['error'=>'Invalid category selected']); 
    exit;
}

// Check for overlaps with approved webinars only for new submissions
$startUtc=gmdate("Y-m-d H:i:s", strtotime($start));
$endUtc=gmdate("Y-m-d H:i:s", strtotime($end));

if ($endUtc <= $startUtc){ 
    http_response_code(422); 
    echo json_encode(['error'=>'End time must be after start time']); 
    exit; 
}

$ov=$pdo->prepare('SELECT COUNT(*) FROM webinars WHERE is_approved=1 AND (? < end_at) AND (? > start_at)');
$ov->execute([$startUtc,$endUtc]);
if ((int)$ov->fetchColumn()>0){ 
    http_response_code(409); 
    echo json_encode(['error'=>'Selected slot overlaps an existing approved webinar']); 
    exit; 
}

// Insert with is_approved=0 (unapproved by default)
$ins=$pdo->prepare('INSERT INTO webinars (title, category_id, start_at, end_at, initiated_by, is_approved) VALUES (?,?,?,?,?,0)');
$ins->execute([$title, $category_id, $start, $end, $_SESSION['user']['id']]);
echo json_encode(['ok'=>true,'id'=>$pdo->lastInsertId()]);

