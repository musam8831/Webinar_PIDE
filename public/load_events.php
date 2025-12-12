
<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();
$me = $_SESSION['user'];
$events=[];

// Show approved webinars for everyone
// Show unapproved webinars only to their creator or admins
$stmt=$pdo->prepare('SELECT w.id, w.title, w.start_at, w.end_at, w.initiated_by, w.is_approved, w.rejection_reason, w.category_id, c.title AS category_title, u.name AS initiator_name, u.email AS initiator_email FROM webinars w JOIN users u ON u.id=w.initiated_by LEFT JOIN categories c ON c.id=w.category_id WHERE w.is_approved=1 OR (w.initiated_by=? OR ?=\'admin\') ORDER BY w.start_at ASC');
$stmt->execute([$me['id'], $me['role']]);
while($row=$stmt->fetch()){
  $canDelete = ($me['role']==='admin') || ((int)$me['id']===(int)$row['initiated_by'] && !$row['is_approved']);
  $canEdit = ($me['role']==='admin') || ((int)$me['id']===(int)$row['initiated_by'] && !$row['is_approved']);
  $isApproved = (int)$row['is_approved'] === 1;
  $isPending = (int)$row['is_approved'] === 0 && (empty($row['rejection_reason']) || $row['rejection_reason'] === '');
  $isRejected = !empty($row['rejection_reason']) && $row['rejection_reason'] !== '';
  
  $events[]=[
    'id'=>$row['id'],
    'title'=>$row['title'],
    'start'=>$row['start_at'],
    'end'=>$row['end_at'],
    'backgroundColor' => $isApproved ? '#118C3C' : ($isRejected ? '#dc3545' : '#ff8c00'),
    'borderColor' => $isApproved ? '#0D6E30' : ($isRejected ? '#991b1b' : '#e67e00'),
    'extendedProps'=>[
      'initiator_name'=>$row['initiator_name'],
      'initiator_email'=>$row['initiator_email'],
      'category_title'=>$row['category_title'],
      'can_delete'=>$canDelete,
      'can_edit'=>$canEdit,
      'is_approved'=>$isApproved,
      'is_pending'=>$isPending,
      'is_rejected'=>$isRejected,
      'rejection_reason'=>$row['rejection_reason'] ?? ''
    ]
  ];
}
header('Content-Type: application/json'); echo json_encode($events);

