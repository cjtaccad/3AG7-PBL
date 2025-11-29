<?php
// view_results.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once __DIR__ . '/audit_helper.php'; // <-- add this so log_action() is available

$host = "localhost";
$user = "u803144294_system";  
$pass = "3AINS-G7_db"; 
$db   = "u803144294_system";
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) die("DB Connection failed: " . $conn->connect_error);

// Get positions
$positions = [];
$res = $conn->query("SELECT position_id, position_name FROM positions ORDER BY position_id ASC");
if ($res) while ($row = $res->fetch_assoc()) $positions[] = $row;

// Filters
$filter_position = intval($_GET['position_id'] ?? 0);
$search_user_id = intval($_GET['search_user_id'] ?? 0);

// Election status (based on Schedule)
$now = date('Y-m-d H:i:s');
$status_query = $conn->query("SELECT * FROM schedule WHERE start_datetime <= '$now' AND end_datetime >= '$now' LIMIT 1");
$election_status = ($status_query && $status_query->num_rows > 0) ? "OPEN" : "CLOSED";

// Total voted students
$total_voted_res = $conn->query("SELECT COUNT(*) AS total_voted FROM ballots");
$total_voted = ($total_voted_res && $row = $total_voted_res->fetch_assoc()) ? $row['total_voted'] : 0;

// Fetch candidates with votes
$sql = "
    SELECT 
        ca.candidate_id, ca.user_id, ca.position_id, ca.platforms, ca.credentials, ca.profile_picture,
        u.full_name, s.year_level, c.course_name, co.college_name, p.position_name,
        COUNT(v.vote_id) AS total_votes
    FROM candidates ca
    JOIN users u ON ca.user_id = u.user_id
    JOIN students s ON u.user_id = s.user_id
    JOIN courses c ON s.course_id = c.course_id
    JOIN colleges co ON c.college_id = co.college_id
    JOIN positions p ON ca.position_id = p.position_id
    LEFT JOIN votes v ON ca.candidate_id = v.candidate_id
";

$conds = [];
$params = []; $types = '';
if ($filter_position > 0) {
    $conds[] = "ca.position_id = ?";
    $params[] = $filter_position;
    $types   .= 'i';
}
if ($search_user_id > 0) {
    $conds[] = "ca.user_id = ?";
    $params[] = $search_user_id;
    $types   .= 'i';
}
if ($conds) $sql .= " WHERE " . implode(" AND ", $conds);
$sql .= " GROUP BY ca.candidate_id ORDER BY p.position_id ASC, total_votes DESC";

$candidates_by_position = [];
if ($types) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $res = $stmt->get_result();
} else {
    $res = $conn->query($sql);
}
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $pid = intval($row['position_id']);
        if (!isset($candidates_by_position[$pid])) {
            $candidates_by_position[$pid] = [
                'position_name' => $row['position_name'],
                'candidates' => []
            ];
        }
        $candidates_by_position[$pid]['candidates'][] = $row;
    }
}

// Example: if your export is triggered by a GET param, add the audit call after generation
if (isset($_GET['export']) && $_GET['export'] === 'pdf') {
    // ...existing code that builds the PDF (dompdf or other) ...

    // send/output the PDF here (existing code)
    // e.g. $dompdf->stream("results.pdf", ["Attachment" => 1]);

    // Record audit AFTER successful generation/output
    $admin_uid = $_SESSION['user_id'] ?? 0;
    $pos = intval($filter_position ?? 0);
    $search = intval($search_user_id ?? 0);
    log_action($conn, $admin_uid, "Exported results PDF (position_id={$pos}, search_user_id={$search})");

    exit(); // ensure script ends after streaming the PDF
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>View Results</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
  .vote-bar { background-color: #007bff; height: 20px; border-radius: 5px; }
  .vote-count { font-weight: bold; color: #032c63; white-space: nowrap; }
  .candidate-card { border-bottom: 1px solid #eee; padding: 8px 0; }
  .show-more-btn { cursor: pointer; color: #007bff; font-weight: 500; }
  .status-open { color: green; font-weight: bold; }
  .status-closed { color: red; font-weight: bold; }
</style>
</head>
<body class="bg-light">

<div class="container-fluid p-3">
  

  <div class="card p-3 mb-3">
    <h3 class="fw-bold mb-4">VIEW RESULTS</h3>
    <form class="row g-2 align-items-center" method="get">
      <div class="col-md-4">
        <select name="position_id" class="form-select">
          <option value="0">Show All</option>
          <?php foreach ($positions as $p): ?>
            <option value="<?= $p['position_id'] ?>" <?= $filter_position == $p['position_id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($p['position_name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3">
        <input type="text" name="search_user_id" value="<?= $search_user_id ?: '' ?>" placeholder="Enter Student ID" class="form-control">
      </div>
      <div class="col-md-3">
        <button class="btn btn-primary">Filter</button>
        <a href="view_results.php" class="btn btn-secondary">Reset</a>
      </div>
      <div class="col-md-2 text-end">
        <a href="export_results.php" target="_blank" class="btn btn-warning w-100">Export PDF</a>
      </div>
    </form>
  </div>

  <!-- Status Overview -->
  <div class="row mb-4">
    <div class="col-md-6">
      <div class="card text-center shadow-sm">
        <div class="card-body">
          <h6>Total Students Voted</h6>
          <h4 class="fw-bold"><?= $total_voted ?></h4>
        </div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card text-center shadow-sm">
        <div class="card-body">
          <h6>Election Status</h6>
          <h4 class="<?= $election_status == 'OPEN' ? 'status-open' : 'status-closed' ?>">
            <?= $election_status ?>
          </h4>
        </div>
      </div>
    </div>
  </div>

  <!-- Candidates per Position -->
  <?php if (empty($candidates_by_position)): ?>
    <div class="alert alert-secondary text-center">No candidates found.</div>
  <?php else: ?>
    <?php foreach ($candidates_by_position as $pid => $data): ?>
      <div class="card mb-4 shadow-sm">
        <div class="card-header bg-primary text-white" style="background-color: #335582 !important;">
          <h5 class="mb-0"><?= htmlspecialchars($data['position_name']) ?></h5>
        </div>
        <div class="card-body">
          <?php 
          $total_votes_pos = array_sum(array_column($data['candidates'], 'total_votes'));
          $count = 0;
          foreach ($data['candidates'] as $c): 
            $count++;
            $show_class = ($count > 2) ? 'extra-candidate d-none' : '';
            $percent = ($total_votes_pos > 0) ? round(($c['total_votes'] / $total_votes_pos) * 100, 1) : 0;
          ?>
            <div class="candidate-card <?= $show_class ?>">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <span class="fw-semibold"><?= htmlspecialchars($c['full_name']) ?></span>
                  <span class="text-muted small"> (<?= $c['user_id'] ?>)</span><br>
                  <span class="text-muted small"><?= htmlspecialchars($c['year_level']) ?> Year - <?= htmlspecialchars($c['college_name']) ?></span>
                </div>
                <div class="vote-count text-end">
                  <?= $c['total_votes'] ?> vote<?= $c['total_votes'] == 1 ? '' : 's' ?>
                </div>
              </div>
              <div class="progress mt-2" style="height:20px;">
                <div class="progress-bar" style="width:<?= $percent ?>%; background-color: #E6B800;"><?= $percent ?>%</div>
              </div>
            </div>
          <?php endforeach; ?>
          <?php if (count($data['candidates']) > 2): ?>
            <div class="text-center mt-2">
              <span class="show-more-btn">Show More</span>
            </div>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

<script>
document.querySelectorAll('.show-more-btn').forEach(btn=>{
  btn.addEventListener('click',()=>{
    const cardBody = btn.closest('.card-body');
    const hidden = cardBody.querySelectorAll('.extra-candidate');
    const isHidden = hidden[0]?.classList.contains('d-none');
    hidden.forEach(el => el.classList.toggle('d-none'));
    btn.textContent = isHidden ? 'Show Less' : 'Show More';
  });
});
</script>
</body>
</html>
