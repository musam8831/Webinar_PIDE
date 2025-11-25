
<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();
$id=(int)($_GET['id']??0);
$stmt=$pdo->prepare('SELECT w.*, c.title AS category_title, u.name AS initiator_name, u.email AS initiator_email FROM webinars w LEFT JOIN categories c ON c.id=w.category_id JOIN users u ON u.id=w.initiated_by WHERE w.id=?');
$stmt->execute([$id]); $row=$stmt->fetch();
if(!$row){ echo json_encode(['error'=>'Not found']); exit; }
$me=$_SESSION['user']; 
$canDelete = ($me['role']==='admin')||((int)$me['id']===(int)$row['initiated_by']);
$canEdit = ($me['role']==='admin'); // Only admin can edit approved webinars
header('Content-Type: application/json');
echo json_encode([
  'id'=>$row['id'],
  'title'=>$row['title'],
  'category_id'=>$row['category_id'],
  'start'=>gmdate('Y-m-d H:i', strtotime($row['start_at'])),
  'end'=>gmdate('Y-m-d H:i', strtotime($row['end_at'])),
  'start_local'=>(new DateTime($row['start_at'].' UTC'))->setTimezone(new DateTimeZone(date_default_timezone_get()))->format('Y-m-d\TH:i'),
  'end_local'=>(new DateTime($row['end_at'].' UTC'))->setTimezone(new DateTimeZone(date_default_timezone_get()))->format('Y-m-d\TH:i'),
  'initiator_name'=>$row['initiator_name'],
  'initiator_email'=>$row['initiator_email'],
  'can_delete'=>$canDelete,
  'can_edit'=>$canEdit
]);

