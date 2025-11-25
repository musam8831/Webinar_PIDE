
<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();
$me = $_SESSION['user'];
$events=[];
$stmt=$pdo->prepare('SELECT w.id, w.title, w.start_at, w.end_at, w.initiated_by, w.category_id, c.title AS category_title, u.name AS initiator_name, u.email AS initiator_email FROM webinars w JOIN users u ON u.id=w.initiated_by LEFT JOIN categories c ON c.id=w.category_id WHERE w.is_approved=1 ORDER BY w.start_at ASC');
$stmt->execute();
while($row=$stmt->fetch()){
  $canDelete = ($me['role']==='admin') || ((int)$me['id']===(int)$row['initiated_by']);
  $canEdit = ($me['role']==='admin'); // Only admin can edit approved webinars
  $events[]=[
    'id'=>$row['id'],
    'title'=>$row['title'],
    'start'=>$row['start_at'],
    'end'=>$row['end_at'],
    'extendedProps'=>[
      'initiator_name'=>$row['initiator_name'],
      'initiator_email'=>$row['initiator_email'],
      'category_title'=>$row['category_title'],
      'can_delete'=>$canDelete,
      'can_edit'=>$canEdit
    ]
  ];
}
header('Content-Type: application/json'); echo json_encode($events);

