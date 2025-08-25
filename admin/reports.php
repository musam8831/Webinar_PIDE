<?php
// admin/reports.php
declare(strict_types=1);

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin(); // Admin-only

// Helper: safe int from GET
function get_int(string $key): ?int {
    return filter_input(INPUT_GET, $key, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1970, 'max_range' => 3000]]) ?: null;
}

// ---- Step 0: load distinct years from webinars (based on start_at) ----
$yearsStmt = $pdo->query("SELECT DISTINCT YEAR(start_at) AS y FROM webinars ORDER BY y DESC");
$years = $yearsStmt->fetchAll(PDO::FETCH_COLUMN);
if (!$years) {
    // If no data exists, still offer current year so UI works
    $years = [ (int)date('Y') ];
}

$selectedYear = get_int('year');

// ---- If no year selected, show selection UI ----
if (!$selectedYear) {
    ?>
    <!doctype html>
    <html lang="en">
    <head>
      <meta charset="utf-8">
      <title>PIDE Webinar Portal — Reports (Select Year)</title>
      <meta name="viewport" content="width=device-width, initial-scale=1">

      <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
      <link href="../assets/css/PIDETheme.css" rel="stylesheet">
      <link href="../assets/css/style.css" rel="stylesheet">
      <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

      <style>
        .reports-shell { max-width: 800px; margin: 28px auto; }
        .card-sel { background:#fff; border-radius:12px; box-shadow:0 6px 18px rgba(0,0,0,.12); overflow:hidden; }
        .card-sel .card-header { background:#fff; border:0; padding:16px 20px; }
        .report-title { margin:0; font-weight:700; color: var(--pide-green); display:flex; align-items:center; gap:.5rem; }
      </style>
    </head>
    <body>
      <?php include __DIR__ . '/../public/navbar.php'; ?>

      <main class="reports-shell">
        <div class="card card-sel">
          <div class="card-header">
            <h5 class="report-title"><i class="bi bi-funnel"></i> Select Year for Reports</h5>
          </div>
          <div class="card-body">
            <form method="get" action="reports.php" class="row g-3">
              <div class="col-12">
                <label class="form-label">Year</label>
                <select name="year" class="form-select" required>
                  <option value="">— Choose year —</option>
                  <?php foreach ($years as $y): ?>
                    <option value="<?= (int)$y ?>"><?= (int)$y ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-12">
                <button class="btn btn-success"><i class="bi bi-bar-chart me-1"></i> Generate Report</button>
                <a href="../public/index.php" class="btn btn-outline-secondary">Cancel</a>
              </div>
            </form>
          </div>
        </div>

        <div class="footer-text mt-3">© <?= date('Y') ?> Pakistan Institute of Development Economics (PIDE)</div>
      </main>
    </body>
    </html>
    <?php
    exit;
}

// ---- Year selected: compute time range in UTC ----
$year = $selectedYear;
$startUtc = sprintf('%04d-01-01 00:00:00', $year);
$endUtc   = sprintf('%04d-12-31 23:59:59', $year);

// ---- Per-user counts (include users with zero via LEFT JOIN) ----
$perUserStmt = $pdo->prepare("
  SELECT u.id, u.name, COUNT(w.id) AS webinar_count
  FROM users u
  LEFT JOIN webinars w
    ON w.initiated_by = u.id
   AND w.start_at >= :startUtc
   AND w.start_at <= :endUtc
  GROUP BY u.id, u.name
  ORDER BY webinar_count DESC, u.name ASC
");
$perUserStmt->execute([':startUtc'=>$startUtc, ':endUtc'=>$endUtc]);
$perUser = $perUserStmt->fetchAll(PDO::FETCH_ASSOC);

// ---- Monthly trend for the selected year ----
$trendStmt = $pdo->prepare("
  SELECT DATE_FORMAT(w.start_at, '%Y-%m') AS ym, COUNT(*) AS total
  FROM webinars w
  WHERE w.start_at >= :startUtc AND w.start_at <= :endUtc
  GROUP BY ym
  ORDER BY ym
");
$trendStmt->execute([':startUtc'=>$startUtc, ':endUtc'=>$endUtc]);
$trend = $trendStmt->fetchAll(PDO::FETCH_ASSOC);

// ---- Prepare arrays for JS ----
$userLabels = array_map(fn($r) => $r['name'], $perUser);
$userCounts = array_map(fn($r) => (int)$r['webinar_count'], $perUser);

$trendLabels = array_map(fn($r) => $r['ym'], $trend);
$trendCounts = array_map(fn($r) => (int)$r['total'], $trend);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>PIDE Webinar Portal — Reports (<?= htmlspecialchars((string)$year) ?>)</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Charts & Export -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

  <!-- Theme -->
  <link href="../assets/css/PIDETheme.css" rel="stylesheet">
  <link href="../assets/css/style.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <style>
    .reports-shell { max-width: 1200px; margin: 28px auto; }
    .report-card {
      background:#fff; border-radius:12px; box-shadow:0 6px 18px rgba(0,0,0,.12);
      overflow:hidden; margin-bottom:20px;
    }
    .report-card .card-header { background:#fff; border:0; padding:16px 20px; }
    .report-title { margin:0; font-weight:700; color: var(--pide-green); display:flex; align-items:center; gap:.5rem; }
    .chart-wrap { height: 380px; }
    .btn-export { --bs-btn-color:#fff; --bs-btn-bg: var(--pide-green); --bs-btn-border-color: var(--pide-green); }
    .btn-export:hover { background: var(--pide-green-dark); border-color: var(--pide-green-dark); }
    .exporting .no-print { display:none !important; }
  </style>
</head>
<body>
<?php include __DIR__ . '/../public/navbar.php'; ?>

<main class="reports-shell" id="reportRoot">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="calendar-title m-0">
      <i class="bi bi-graph-up-arrow"></i>
      Webinar Performance Reports — <?= htmlspecialchars((string)$year) ?>
    </h4>
    <div class="no-print">
      <a href="reports.php" class="btn btn-outline-secondary me-2"><i class="bi bi-arrow-left"></i> Change Year</a>
      <button class="btn btn-export" id="btnExport"><i class="bi bi-filetype-pdf me-1"></i> Export PDF</button>
    </div>
  </div>

  <!-- Bar chart -->
  <div class="card report-card">
    <div class="card-header">
      <h5 class="report-title"><i class="bi bi-bar-chart-line"></i> Webinars per User</h5>
    </div>
    <div class="card-body">
      <div class="chart-wrap"><canvas id="barChart"></canvas></div>
    </div>
  </div>

  <!-- Pie chart -->
  <div class="card report-card">
    <div class="card-header">
      <h5 class="report-title"><i class="bi bi-pie-chart"></i> Contribution by User</h5>
    </div>
    <div class="card-body">
      <div class="chart-wrap"><canvas id="pieChart"></canvas></div>
    </div>
  </div>

  <!-- Monthly trend line -->
  <div class="card report-card">
    <div class="card-header">
      <h5 class="report-title"><i class="bi bi-graph-up"></i> Webinars Trend (Monthly)</h5>
    </div>
    <div class="card-body">
      <div class="chart-wrap"><canvas id="lineChart"></canvas></div>
    </div>
  </div>

  <!-- Data table -->
  <div class="card report-card">
    <div class="card-header">
      <h5 class="report-title"><i class="bi bi-table"></i> Detailed Data</h5>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
          <thead style="background: var(--pide-green); color:#fff;">
            <tr>
              <th>User</th>
              <th>Total Webinars Hosted (<?= htmlspecialchars((string)$year) ?>)</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($perUser as $r): ?>
            <tr>
              <td><?= htmlspecialchars($r['name']) ?></td>
              <td><span class="badge bg-success"><?= (int)$r['webinar_count'] ?></span></td>
            </tr>
          <?php endforeach; ?>
          <?php if (empty($perUser)): ?>
            <tr><td colspan="2" class="text-center text-muted py-4">No data available.</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="footer-text">© <?= date('Y') ?> Pakistan Institute of Development Economics (PIDE)</div>
</main>

<script>
(function(){
  // Data from PHP
  const userLabels = <?= json_encode($userLabels, JSON_UNESCAPED_UNICODE) ?>;
  const userCounts = <?= json_encode($userCounts, JSON_NUMERIC_CHECK) ?>;
  const trendLabels = <?= json_encode($trendLabels, JSON_UNESCAPED_UNICODE) ?>;
  const trendCounts = <?= json_encode($trendCounts, JSON_NUMERIC_CHECK) ?>;

  // Finite, distinct palette (cycles if > length)
  const palette = [
    "#118C3C","#DD9300","#2A9D8F","#277DA1","#E76F51",
    "#6A4C93","#F4A261","#4D908E","#577590","#BC4749",
    "#5C8001","#845EC2","#FF8066","#4B4453","#00C9A7"
  ];
  const colorAt = i => palette[i % palette.length];

  // BAR
  const barCtx = document.getElementById('barChart').getContext('2d');
  const barChart = new Chart(barCtx, {
    type: 'bar',
    data: {
      labels: userLabels,
      datasets: [{
        label: 'Webinars Hosted',
        data: userCounts,
        backgroundColor: userLabels.map((_,i)=>colorAt(i))
      }]
    },
    options: {
      maintainAspectRatio:false,
      plugins:{ legend:{ display:false } },
      scales:{ y:{ beginAtZero:true, ticks:{ precision:0 } } }
    }
  });

  // PIE
  const pieCtx = document.getElementById('pieChart').getContext('2d');
  const pieChart = new Chart(pieCtx, {
    type: 'pie',
    data: {
      labels: userLabels,
      datasets: [{
        data: userCounts,
        backgroundColor: userLabels.map((_,i)=>colorAt(i)),
        borderWidth: 0
      }]
    },
    options: { maintainAspectRatio:false }
  });

  // LINE (monthly trend)
  const lineCtx = document.getElementById('lineChart').getContext('2d');
  const lineChart = new Chart(lineCtx, {
    type: 'line',
    data: {
      labels: trendLabels,
      datasets: [{
        label: 'Webinars / Month',
        data: trendCounts,
        borderColor: "#118C3C",
        backgroundColor: "rgba(17,140,60,0.18)",
        fill: true,
        tension: 0.3,
        pointRadius: 3
      }]
    },
    options: { maintainAspectRatio:false }
  });

  // PDF Export
  document.getElementById('btnExport').addEventListener('click', async () => {
    const { jsPDF } = window.jspdf;
    const root = document.getElementById('reportRoot');
    root.classList.add('exporting');

    // Ensure charts rendered at current size
    [barChart, pieChart, lineChart].forEach(ch => ch.resize());

    const canvas = await html2canvas(root, { scale: 2, useCORS: true, backgroundColor: '#ffffff' });
    const imgData = canvas.toDataURL('image/png');

    const pdf = new jsPDF('p','pt','a4');
    const pageWidth  = pdf.internal.pageSize.getWidth();
    const pageHeight = pdf.internal.pageSize.getHeight();
    const imgWidth   = pageWidth;
    const imgHeight  = canvas.height * (imgWidth / canvas.width);

    let heightLeft = imgHeight;
    let position   = 0;

    pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight);
    heightLeft -= pageHeight;

    while (heightLeft > 0) {
      pdf.addPage();
      position = heightLeft * -1;
      pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight);
      heightLeft -= pageHeight;
    }

    pdf.save('PIDE_Webinar_Reports_<?= (int)$year ?>.pdf');
    root.classList.remove('exporting');
  });
})();
</script>
</body>
</html>
