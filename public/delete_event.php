
<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();
$payload=json_decode(file_get_contents('php://input'), true) ?? [];
$id=(int)($payload['id']??0);
if($id<=0){ http_response_code(422); echo json_encode(['error'=>'Invalid ID']); exit; }

$owner=$pdo->prepare('SELECT initiated_by, is_approved FROM webinars WHERE id=?');
$owner->execute([$id]);
$webinar=$owner->fetch();

if(!$webinar){
    http_response_code(404);
    echo json_encode(['error'=>'Not found']);
    exit;
}

$me=$_SESSION['user'];
// Users can only delete their own unapproved webinars. Admin can delete anything.
if ($me['role']!=='admin'){
    if ((int)$webinar['initiated_by'] !== (int)$me['id'] || $webinar['is_approved']){
        http_response_code(403);
        echo json_encode(['error'=>'You can only delete your own unapproved webinars']);
        exit;
    }
}

$pdo->prepare('DELETE FROM webinars WHERE id=?')->execute([$id]);
echo json_encode(['ok'=>true]);

