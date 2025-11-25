<?php
// admin/pending_webinars.php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin();

$user = $_SESSION['user'];
$message = '';
$error = '';

// Handle approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $webinar_id = (int)($_POST['webinar_id'] ?? 0);
    
    if ($action === 'approve' && $webinar_id > 0) {
        try {
            $stmt = $pdo->prepare('UPDATE webinars SET is_approved=1, approved_by=?, approved_on=NOW() WHERE id=?');
            $stmt->execute([$user['id'], $webinar_id]);
            $message = 'Webinar approved successfully!';
        } catch (Exception $e) {
            $error = 'Error approving webinar: ' . $e->getMessage();
        }
    } elseif ($action === 'reject' && $webinar_id > 0) {
        $reason = trim($_POST['reason'] ?? '');
        if (!$reason) {
            $error = 'Rejection reason is required.';
        } else {
            try {
                $stmt = $pdo->prepare('UPDATE webinars SET rejection_reason=? WHERE id=?');
                $stmt->execute([$reason, $webinar_id]);
                // Delete rejected webinar
                $pdo->prepare('DELETE FROM webinars WHERE id=?')->execute([$webinar_id]);
                $message = 'Webinar rejected and deleted.';
            } catch (Exception $e) {
                $error = 'Error rejecting webinar: ' . $e->getMessage();
            }
        }
    }
}

// Fetch pending webinars
$stmt = $pdo->query('SELECT w.id, w.title, w.start_at, w.end_at, w.category_id, c.title AS category_title, w.initiated_by, u.name AS initiator_name, u.email AS initiator_email, w.created_at FROM webinars w JOIN users u ON u.id=w.initiated_by LEFT JOIN categories c ON c.id=w.category_id WHERE w.is_approved=0 ORDER BY w.created_at DESC');
$pending_webinars = $stmt->fetchAll();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Pending Webinars - PIDE Webinar Portal</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link href="../assets/css/PIDETheme.css" rel="stylesheet">
  <link href="../assets/css/styles.css" rel="stylesheet">
  <style>
    .pending-shell { max-width: 1200px; margin: 20px auto; }
    .webinar-card { background:#fff; border-radius:8px; box-shadow:0 2px 8px rgba(0,0,0,.1); margin-bottom:15px; padding:15px; }
    .webinar-header { display:flex; justify-content:space-between; align-items:start; margin-bottom:10px; }
    .webinar-title { font-size:18px; font-weight:600; color:var(--pide-green); }
    .webinar-meta { font-size:13px; color:#666; margin:5px 0; }
    .action-buttons { display:flex; gap:10px; margin-top:15px; }
  </style>
</head>
<body>
<?php include __DIR__ . '/../public/navbar.php'; ?>

<div class="pending-shell">
  <?php if ($message): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <?= htmlspecialchars($message) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>
  
  <?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <?= htmlspecialchars($error) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <div class="d-flex justify-content-between align-items-center mb-4">
    <h4><i class="bi bi-exclamation-circle"></i> Pending Webinar Approvals</h4>
    <span class="badge bg-warning"><?= count($pending_webinars) ?> Pending</span>
  </div>

  <?php if (empty($pending_webinars)): ?>
    <div class="alert alert-info" role="alert">
      <i class="bi bi-info-circle"></i> No pending webinars to approve.
    </div>
  <?php else: ?>
    <?php foreach ($pending_webinars as $webinar): ?>
      <div class="webinar-card">
        <div class="webinar-header">
          <div class="flex-grow-1">
            <div class="webinar-title"><?= htmlspecialchars($webinar['title']) ?></div>
            <div class="webinar-meta">
              <i class="bi bi-person"></i> <strong><?= htmlspecialchars($webinar['initiator_name']) ?></strong> (<?= htmlspecialchars($webinar['initiator_email']) ?>)
            </div>
            <div class="webinar-meta">
              <i class="bi bi-calendar-event"></i> <?= date('M d, Y H:i', strtotime($webinar['start_at'])) ?> - <?= date('H:i', strtotime($webinar['end_at'])) ?>
            </div>
            <?php if ($webinar['category_title']): ?>
              <div class="webinar-meta">
                <i class="bi bi-tag"></i> <?= htmlspecialchars($webinar['category_title']) ?>
              </div>
            <?php endif; ?>
            <div class="webinar-meta text-muted" style="font-size:12px;">
              Submitted: <?= date('M d, Y H:i', strtotime($webinar['created_at'])) ?>
            </div>
          </div>
        </div>

        <div class="action-buttons">
          <!-- Approve Button -->
          <form method="POST" style="display:inline;">
            <input type="hidden" name="action" value="approve">
            <input type="hidden" name="webinar_id" value="<?= $webinar['id'] ?>">
            <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Approve this webinar?');">
              <i class="bi bi-check-circle"></i> Approve
            </button>
          </form>

          <!-- Reject Button -->
          <button class="btn btn-danger btn-sm" onclick="showRejectModal(<?= $webinar['id'] ?>, '<?= htmlspecialchars($webinar['title'], ENT_QUOTES) ?>');">
            <i class="bi bi-x-circle"></i> Reject
          </button>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>

  <div class="footer-text mt-5">
    © <?= date('Y') ?> Pakistan Institute of Development Economics (PIDE)
  </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Reject Webinar</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST">
        <input type="hidden" name="action" value="reject">
        <input type="hidden" name="webinar_id" id="rejectWebinarId">
        <div class="modal-body">
          <p class="mb-3">Webinar: <strong id="rejectWebinarTitle"></strong></p>
          <label class="form-label">Rejection Reason <span class="text-danger">*</span></label>
          <textarea class="form-control" name="reason" rows="4" placeholder="Please provide the reason for rejection" required></textarea>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger">Reject Webinar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function showRejectModal(id, title) {
  document.getElementById('rejectWebinarId').value = id;
  document.getElementById('rejectWebinarTitle').textContent = title;
  const modal = new bootstrap.Modal(document.getElementById('rejectModal'));
  modal.show();
}
</script>
</body>
</html>
