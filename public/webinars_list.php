<?php
// public/webinars_list.php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();

$user = $_SESSION['user'];
$is_admin = $user['role'] === 'admin';

$config = require __DIR__ . '/../includes/config.php';
$base = rtrim($config['base_url'], '/');

// Get filter values
// status: approved (default), rejected, pending, all
$filter_status = $_GET['status'] ?? 'approved';
$filter_user = (int)($_GET['user'] ?? ($is_admin ? 0 : $user['id']));
$filter_date = $_GET['date'] ?? 'all';
$filter_category = (int)($_GET['category'] ?? 0);
$filter_search = trim($_GET['search'] ?? '');
$filter_from = $_GET['from'] ?? '';
$filter_to = $_GET['to'] ?? '';

// Build WHERE clause
$where_parts = [];
$params = [];

// Status filter (default: show only approved)
if ($filter_status === 'approved') {
  $where_parts[] = 'w.is_approved=1';
} elseif ($filter_status === 'rejected') {
  // Rejected webinars are those with a rejection_reason set
  $where_parts[] = 'w.rejection_reason IS NOT NULL AND w.rejection_reason <> ""';
} elseif ($filter_status === 'pending') {
  // Pending = not approved and not rejected
  $where_parts[] = '(w.is_approved=0 AND (w.rejection_reason IS NULL OR w.rejection_reason = ""))';
} // 'all' => no extra clause

// User filter (admin only)
if ($is_admin && $filter_user > 0) {
    $where_parts[] = 'w.initiated_by=?';
    $params[] = $filter_user;
} elseif (!$is_admin) {
    $where_parts[] = 'w.initiated_by=?';
    $params[] = $user['id'];
}

// Date filter
$now = new DateTime();
$today_start = $now->format('Y-m-d 00:00:00');
$today_end = $now->format('Y-m-d 23:59:59');

if ($filter_date === 'today') {
    $where_parts[] = '(w.start_at >= ? AND w.start_at <= ?)';
    $params[] = $today_start;
    $params[] = $today_end;
} elseif ($filter_date === 'week') {
    $week_start = clone $now;
    $week_start->modify('monday this week');
    $week_start = $week_start->format('Y-m-d 00:00:00');
    $week_end = clone $now;
    $week_end->modify('sunday this week');
    $week_end = $week_end->format('Y-m-d 23:59:59');
    $where_parts[] = '(w.start_at >= ? AND w.start_at <= ?)';
    $params[] = $week_start;
    $params[] = $week_end;
} elseif ($filter_date === 'month') {
    $month_start = $now->format('Y-m-01 00:00:00');
    $month_end = $now->format('Y-m-t 23:59:59');
    $where_parts[] = '(w.start_at >= ? AND w.start_at <= ?)';
    $params[] = $month_start;
    $params[] = $month_end;
} elseif ($filter_date === 'year') {
    $year_start = $now->format('Y-01-01 00:00:00');
    $year_end = $now->format('Y-12-31 23:59:59');
    $where_parts[] = '(w.start_at >= ? AND w.start_at <= ?)';
    $params[] = $year_start;
    $params[] = $year_end;
} elseif ($filter_date === 'custom' && $filter_from && $filter_to) {
    $where_parts[] = '(w.start_at >= ? AND w.start_at <= ?)';
    $params[] = $filter_from . ' 00:00:00';
    $params[] = $filter_to . ' 23:59:59';
}

// Category filter
if ($filter_category > 0) {
    $where_parts[] = 'w.category_id=?';
    $params[] = $filter_category;
}

// Search filter
if ($filter_search) {
    $where_parts[] = '(w.title LIKE ? OR u.name LIKE ?)';
    $params[] = '%' . $filter_search . '%';
    $params[] = '%' . $filter_search . '%';
}

$where_clause = implode(' AND ', $where_parts);

// Fetch webinars
$query = "SELECT w.id, w.title, w.start_at, w.end_at, w.category_id, c.title AS category_title,
                 w.initiated_by, u.name AS initiator_name, u.email AS initiator_email, w.created_at
          FROM webinars w
          JOIN users u ON u.id=w.initiated_by
          LEFT JOIN categories c ON c.id=w.category_id
          WHERE $where_clause
          ORDER BY w.start_at DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$webinars = $stmt->fetchAll();

// Get all users for filter (admin only)
$all_users = [];
if ($is_admin) {
    $stmt = $pdo->query('SELECT id, name FROM users ORDER BY name ASC');
    $all_users = $stmt->fetchAll();
}

// Get all categories
$stmt = $pdo->query('SELECT id, title FROM categories WHERE is_active=1 ORDER BY title ASC');
$all_categories = $stmt->fetchAll();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>My Webinars - PIDE Webinar Portal</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link href="../assets/css/PIDETheme.css" rel="stylesheet">
  <link href="../assets/css/styles.css" rel="stylesheet">
  <style>
    .list-shell { max-width: 1200px; margin: 20px auto; }
    .filter-section { background:#f8f9fa; padding:15px; border-radius:8px; margin-bottom:20px; }
    .webinar-row { background:#fff; border-radius:8px; box-shadow:0 2px 8px rgba(0,0,0,.1); margin-bottom:12px; padding:15px; }
    .webinar-row:hover { box-shadow:0 4px 12px rgba(0,0,0,.15); }
    .row-title { font-size:16px; font-weight:600; color:var(--pide-green); margin-bottom:8px; }
    .row-meta { font-size:13px; color:#666; margin:3px 0; }
    .row-actions { display:flex; gap:8px; margin-top:10px; }
  </style>
</head>
<body>
<?php include 'navbar.php'; ?>

<div class="list-shell">
  <h4 class="mb-4"><i class="bi bi-list-check"></i> Webinars List</h4>

  <!-- Filters -->
  <div class="filter-section">
    <form method="GET" class="row g-3">
      <!-- User Filter (Admin Only) -->
      <?php if ($is_admin): ?>
        <div class="col-md-3">
          <label class="form-label">Filter by User</label>
          <select class="form-select" name="user" onchange="this.form.submit();">
            <option value="0">All Users</option>
            <?php foreach ($all_users as $u): ?>
              <option value="<?= $u['id'] ?>" <?= $filter_user == $u['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($u['name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      <?php endif; ?>

      <!-- Category Filter -->
      <div class="col-md-<?= $is_admin ? '3' : '4' ?>">
        <label class="form-label">Category</label>
        <select class="form-select" name="category" onchange="this.form.submit();">
          <option value="0">All Categories</option>
          <?php foreach ($all_categories as $cat): ?>
            <option value="<?= $cat['id'] ?>" <?= $filter_category == $cat['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($cat['title']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Status Filter -->
      <div class="col-md-2">
        <label class="form-label">Status</label>
        <select class="form-select" name="status" onchange="this.form.submit();">
          <option value="all" <?= $filter_status === 'all' ? 'selected' : '' ?>>All</option>
          <option value="approved" <?= $filter_status === 'approved' ? 'selected' : '' ?>>Approved</option>
          <option value="pending" <?= $filter_status === 'pending' ? 'selected' : '' ?>>Pending</option>
          <option value="rejected" <?= $filter_status === 'rejected' ? 'selected' : '' ?>>Rejected</option>
        </select>
      </div>

      <!-- Date Filter -->
      <div class="col-md-<?= $is_admin ? '2' : '4' ?>">
        <label class="form-label">Date Range</label>
        <select class="form-select" name="date" id="dateFilter" onchange="this.form.submit();">
          <option value="all" <?= $filter_date === 'all' ? 'selected' : '' ?>>All Dates</option>
          <option value="today" <?= $filter_date === 'today' ? 'selected' : '' ?>>Today</option>
          <option value="week" <?= $filter_date === 'week' ? 'selected' : '' ?>>Current Week</option>
          <option value="month" <?= $filter_date === 'month' ? 'selected' : '' ?>>Current Month</option>
          <option value="year" <?= $filter_date === 'year' ? 'selected' : '' ?>>Current Year</option>
          <option value="custom" <?= $filter_date === 'custom' ? 'selected' : '' ?>>Custom</option>
        </select>
      </div>

      <!-- Search -->
      <div class="col-md-<?= $is_admin ? '2' : '4' ?>">
        <label class="form-label">Search</label>
        <input type="text" class="form-control" name="search" placeholder="Title or name..." value="<?= htmlspecialchars($filter_search) ?>">
      </div>

      <div class="col-md-2 d-flex align-items-end gap-2">
        <button type="submit" class="btn btn-primary">
          <i class="bi bi-search"></i> Search
        </button>
        <a href="<?= $base ?>/public/webinars_list.php" class="btn btn-secondary">Reset</a>
      </div>
    </form>

    <!-- Custom Date Range (shown when custom is selected) -->
    <?php if ($filter_date === 'custom'): ?>
      <form method="GET" class="row g-3 mt-2">
        <input type="hidden" name="date" value="custom">
        <input type="hidden" name="status" value="<?= htmlspecialchars($filter_status) ?>">
        <input type="hidden" name="user" value="<?= $filter_user ?>">
        <input type="hidden" name="category" value="<?= $filter_category ?>">
        <input type="hidden" name="search" value="<?= htmlspecialchars($filter_search) ?>">
        
        <div class="col-md-3">
          <label class="form-label">From Date</label>
          <input type="date" class="form-control" name="from" value="<?= htmlspecialchars($filter_from) ?>" required>
        </div>
        <div class="col-md-3">
          <label class="form-label">To Date</label>
          <input type="date" class="form-control" name="to" value="<?= htmlspecialchars($filter_to) ?>" required>
        </div>
        <div class="col-md-6 d-flex align-items-end gap-2">
          <button type="submit" class="btn btn-primary">Apply</button>
        </div>
      </form>
    <?php endif; ?>
  </div>

  <!-- Results -->
  <div class="mb-3">
    <p class="text-muted">Found <strong><?= count($webinars) ?></strong> webinar(s)</p>
  </div>

  <?php if (empty($webinars)): ?>
    <div class="alert alert-info" role="alert">
      <i class="bi bi-info-circle"></i> No webinars found matching your filters.
    </div>
  <?php else: ?>
    <?php foreach ($webinars as $webinar): ?>
      <div class="webinar-row">
        <div class="row-title"><?= htmlspecialchars($webinar['title']) ?></div>
        <div class="row-meta">
          <i class="bi bi-calendar-event"></i> <?= date('M d, Y H:i', strtotime($webinar['start_at'])) ?> - <?= date('H:i', strtotime($webinar['end_at'])) ?>
        </div>
        <div class="row-meta">
          <i class="bi bi-person"></i> <?= htmlspecialchars($webinar['initiator_name']) ?> (<?= htmlspecialchars($webinar['initiator_email']) ?>)
        </div>
        <?php if ($webinar['category_title']): ?>
          <div class="row-meta">
            <i class="bi bi-tag"></i> <span class="badge bg-info"><?= htmlspecialchars($webinar['category_title']) ?></span>
          </div>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>

  <div class="footer-text mt-5">
    © <?= date('Y') ?> Pakistan Institute of Development Economics (PIDE)
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
