
<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();
$payload = json_decode(file_get_contents('php://input'), true) ?? [];
$id=(int)($payload['id']??0);
$title=trim($payload['title']??'');
$category_id=(int)($payload['category_id']??0);
$start=$payload['start']??'';
$end=$payload['end']??'';

if ($id<=0 || !$title || !$start || !$end || $category_id<=0){
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

// Get the webinar and check permissions - only admin can edit approved webinars
$owner=$pdo->prepare('SELECT initiated_by, is_approved FROM webinars WHERE id=?');
$owner->execute([$id]);
$webinar=$owner->fetch();

if(!$webinar){
    http_response_code(404);
    echo json_encode(['error'=>'Not found']);
    exit;
}

$me=$_SESSION['user'];
if ($me['role']!=='admin'){
    http_response_code(403);
    echo json_encode(['error'=>'Only admin can edit webinars']);
    exit;
}

$startUtc=(new DateTime($start))->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s');
$endUtc=(new DateTime($end))->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s');

if ($endUtc <= $startUtc){
    http_response_code(422);
    echo json_encode(['error'=>'End time must be after start time']);
    exit;
}

$ov=$pdo->prepare('SELECT COUNT(*) FROM webinars WHERE id<>? AND is_approved=1 AND (? < end_at) AND (? > start_at)');
$ov->execute([$id,$startUtc,$endUtc]);

if ((int)$ov->fetchColumn()>0){
    http_response_code(409);
    echo json_encode(['error'=>'Selected slot overlaps an existing approved webinar']);
    exit;
}

$upd=$pdo->prepare('UPDATE webinars SET title=?, category_id=?, start_at=?, end_at=? WHERE id=?');
$upd->execute([$title, $category_id, $startUtc, $endUtc, $id]);
echo json_encode(['ok'=>true]);

