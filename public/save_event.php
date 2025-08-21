
<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();
$payload = json_decode(file_get_contents('php://input'), true) ?? [];
$title = trim($payload['title'] ?? ''); 
$start = $payload['start'] ?? ''; 
$end = $payload['end'] ?? '';
if (!$title || !$start || !$end){ 
    http_response_code(422); 
    echo json_encode(['error'=>'All fields are required']); 
    exit; 
}
// $startUtc=(new DateTime($start))->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s');
// $endUtc=(new DateTime($end))->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s');

// console.log("Start: " + $start + "; End: " + $end);


$startUtc=gmdate("Y-m-d H:i:s", strtotime($start));
$endUtc=gmdate("Y-m-d H:i:s", strtotime($end));
// console.log("Start UTC:" + $startUtc+ "; End UTC:" + $endUtc);
if ($endUtc <= $startUtc){ 
    http_response_code(422); 
    echo json_encode(['error'=>'End time must be after start time']); 
    exit; 
}
$ov=$pdo->prepare('SELECT COUNT(*) FROM webinars WHERE (? < end_at) AND (? > start_at)');
$ov->execute([$startUtc,$endUtc]);
if ((int)$ov->fetchColumn()>0){ 
    http_response_code(409); 
    echo json_encode(['error'=>'Selected slot overlaps an existing webinar']); 
    exit; 
}
$ins=$pdo->prepare('INSERT INTO webinars (title,start_at,end_at,initiated_by) VALUES (?,?,?,?)');
// $ins->execute([$title,$startUtc,$endUtc,$_SESSION['user']['id']]);
 $ins->execute([$title,$start,$end,$_SESSION['user']['id']]);
echo json_encode(['ok'=>true,'id'=>$pdo->lastInsertId()]);
