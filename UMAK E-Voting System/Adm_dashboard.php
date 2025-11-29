<?php
date_default_timezone_set('Asia/Manila');
session_start();

// ✅ Database connection
$host = "localhost";
$user = "u803144294_system";  
$pass = "3AINS-G7_db"; 
$db   = "u803144294_system";
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) die("Database connection failed: " . $conn->connect_error);

// ✅ Security
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "ADMIN") {
    header("Location: login.php");
    exit;
}

$admin_user_id = $_SESSION["user_id"];

// ✅ Fetch existing admin note (ONLY ONCE - remove duplicates)
$noteResult = $conn->query("SELECT note_text FROM adminnotes WHERE admin_user_id = $admin_user_id");
$existingNote = '';
if ($noteResult && $noteResult->num_rows > 0) {
    $existingNote = $noteResult->fetch_assoc()['note_text'] ?? '';
}

// ==========================================
// 🔹 Fetch dashboard data
// ==========================================
$total_students = $conn->query("SELECT COUNT(*) AS total FROM students")->fetch_assoc()['total'] ?? 0;
$enrolled_students = $conn->query("SELECT COUNT(*) AS total FROM students WHERE is_enrolled = 1")->fetch_assoc()['total'] ?? 0;
$total_candidates = $conn->query("SELECT COUNT(*) AS total FROM candidates")->fetch_assoc()['total'] ?? 0;
$stmt = $conn->prepare("SELECT COUNT(*) AS total FROM ballots");
$stmt->execute();
$total_voted = $stmt->get_result()->fetch_assoc()['total'] ?? 0;

$schedules = $conn->query("SELECT phase, start_datetime, end_datetime FROM schedule ORDER BY start_datetime ASC");
$audit_logs = $conn->query("
    SELECT u.full_name, a.action, a.log_timestamp
    FROM auditLogs a
    JOIN users u ON a.user_id = u.user_id
    ORDER BY a.log_timestamp DESC
    LIMIT 5
");
$candidates = $conn->query("
    SELECT u.full_name, p.position_name, co.course_name
    FROM candidates c
    JOIN students s ON c.user_id = s.user_id
    JOIN users u ON s.user_id = u.user_id
    JOIN courses co ON s.course_id = co.course_id
    JOIN positions p ON c.position_id = p.position_id
    ORDER BY p.position_id ASC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard Content</title>

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <!-- Custom -->
  <link rel="stylesheet" href="front.css">

  <style>
    body { background-color: #f8f9fa; padding: 20px; }
    .stat-card { transition: transform .2s; }
    .stat-card:hover { transform: translateY(-3px); }
    h5.text-primary { color: #000 !important; font-weight: 500 !important; }
  </style>
</head>

<body>

  <!-- 🔹 STATISTICS -->
  <div class="row g-3 mb-4">
    <?php
      $stats = [
        [
          'value' => $conn->query("SELECT COUNT(*) AS total FROM positions")->fetch_assoc()['total'] ?? 0,
          'label' => 'Total Positions',
          'icon'  => 'fa-briefcase',
        ],
        [
          'value' => $enrolled_students,
          'label' => 'Enrolled Students',
          'icon'  => 'fa-user-graduate',
        ],
        [
          'value' => $total_candidates,
          'label' => 'Candidates',
          'icon'  => 'fa-users',
        ],
        [
          'value' => $total_voted,
          'label' => 'Students Voted',
          'icon'  => 'fa-check-to-slot',
        ],
      ];
    ?>

    <?php foreach ($stats as $stat): ?>
      <div class="col-6 col-md-6 col-lg-3">
        <div class="card stat-card shadow-sm border-0 h-100">
          <div class="card-body d-flex justify-content-between align-items-center">
            <div>
              <div class="fs-2 fw-bold text-primary mb-1"><?= $stat['value'] ?></div>
              <div class="text-muted small"><?= $stat['label'] ?></div>
            </div>
            <div class="opacity-25">
              <i class="fa-solid <?= $stat['icon'] ?> fa-2x"></i>
            </div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- 🔹 NOTEPAD + SMALL CHART -->
  <div class="row g-4 mb-4">
    <!-- Admin Notepad -->
    <div class="col-lg-6">
      <div class="card shadow-sm border-0 h-100">
        <div class="card-body d-flex flex-column" style="height: 100%;">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="text-primary mb-0">Admin Notepad</h5>
            <small class="text-muted" id="saveStatus">Ready</small>
          </div>
          
          <div id="adminNote" class="form-control flex-grow-1" contenteditable="true" placeholder="Type your notes here..." style="min-height: 180px; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; overflow-y: auto; white-space: pre-wrap;"><?php echo $existingNote; ?></div>
          
          <small class="text-muted mt-2 d-block">
            <i class="fa-solid fa-keyboard me-1"></i>Press <kbd>Ctrl+B</kbd> to make text <strong>bold</strong>
          </small>
        </div>
      </div>
    </div>

    <!-- Voting Progress (smaller) -->
    <div class="col-lg-6">
      <div class="card shadow-sm border-0 h-100">
        <div class="card-body">
          <h5 class="text-primary mb-3 text-center">Voting Progress</h5>
          <div style="height:220px;">
            <canvas id="voteChart"></canvas>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- 🔹 SCHEDULE TRACKER -->
  <div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
      <h5 class="text-primary mb-3">Election Schedule</h5>
      <div class="table-responsive">
        <table class="table table-striped align-middle">
          <thead class="table-light">
            <tr>
              <th>Phase</th>
              <th>Start</th>
              <th>End</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($row = $schedules->fetch_assoc()): ?>
              <?php
                $now = date('Y-m-d H:i:s');
                $status = ($now < $row['start_datetime']) ? 'Upcoming' :
                          (($now > $row['end_datetime']) ? 'Ended' : 'Ongoing');
                $badge = ($status == 'Ongoing') ? 'success' : (($status == 'Upcoming') ? 'warning' : 'secondary');
              ?>
              <tr>
                <td><?php echo htmlspecialchars($row['phase']); ?></td>
                <td><?php echo date("M d, Y h:i A", strtotime($row['start_datetime'])); ?></td>
                <td><?php echo date("M d, Y h:i A", strtotime($row['end_datetime'])); ?></td>
                <td><span class="badge bg-<?php echo $badge; ?>"><?php echo $status; ?></span></td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- 🔹 CANDIDATES OVERVIEW -->
  <div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
      <h5 class="text-primary mb-3">Candidates Overview</h5>
      <div class="table-responsive">
        <table class="table table-hover align-middle">
          <thead class="table-light">
            <tr>
              <th>Name</th>
              <th>Position</th>
              <th>Course</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($candidates->num_rows > 0): ?>
              <?php while ($cand = $candidates->fetch_assoc()): ?>
                <tr>
                  <td><?php echo htmlspecialchars($cand['full_name']); ?></td>
                  <td><?php echo htmlspecialchars($cand['position_name']); ?></td>
                  <td><?php echo htmlspecialchars($cand['course_name']); ?></td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr><td colspan="3" class="text-center text-muted">No candidates found.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- 🔹 RECENT ACTIVITIES -->
  <div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
      <h5 class="text-primary mb-3">Recent Activities (Last 5)</h5>
      <ul class="list-group list-group-flush">
        <?php if ($audit_logs->num_rows > 0): ?>
          <?php while ($log = $audit_logs->fetch_assoc()): ?>
            <li class="list-group-item d-flex justify-content-between align-items-center">
              <div>
                <strong><?php echo htmlspecialchars($log['full_name']); ?></strong>
                <span class="text-muted small">— <?php echo htmlspecialchars($log['action']); ?></span>
              </div>
              <small class="text-secondary"><?php echo date("M d, h:i A", strtotime($log['log_timestamp'])); ?></small>
            </li>
          <?php endwhile; ?>
        <?php else: ?>
          <li class="list-group-item text-muted">No recent activity.</li>
        <?php endif; ?>
      </ul>
    </div>
  </div>

  <script>
  // Auto-save notepad with debounce
  const noteDiv = document.getElementById('adminNote');
  const saveStatus = document.getElementById('saveStatus');
  const boldIndicator = document.getElementById('boldIndicator');
  let saveTimeout;

  // Ctrl+B for bold (only selected text)
  noteDiv.addEventListener('keydown', function(e) {
    if ((e.ctrlKey || e.metaKey) && e.key === 'b') {
      e.preventDefault();
      document.execCommand('bold', false, null);
      // Show bold indicator
      boldIndicator.classList.remove('d-none');
      setTimeout(() => {
        boldIndicator.classList.add('d-none');
      }, 1500);
    }
  });

  // Auto-save on input (debounced)
  noteDiv.addEventListener('input', function() {
    clearTimeout(saveTimeout);
    saveStatus.textContent = 'Saving...';
    saveStatus.style.color = '#ffc107';

    saveTimeout = setTimeout(() => {
      const note = noteDiv.innerHTML; // Use innerHTML to preserve formatting
      const formData = new FormData();
      formData.append('note', note);

      fetch('save_note.php', {
        method: 'POST',
        body: formData
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          saveStatus.textContent = 'Saved';
          saveStatus.style.color = '#28a745';
          setTimeout(() => {
            saveStatus.textContent = 'Ready';
            saveStatus.style.color = '#6c757d';
          }, 2000);
        } else {
          throw new Error(data.error || 'Save failed');
        }
      })
      .catch(err => {
        saveStatus.textContent = 'Error';
        saveStatus.style.color = '#dc3545';
        console.error('Save error:', err);
      });
    }, 1000);
  });
</script>

  <!-- 🧠 Chart Script -->
  <script>
    const ctx = document.getElementById('voteChart');
    new Chart(ctx, {
      type: 'doughnut',
      data: {
        labels: ['Voted', 'Not Yet Voted'],
        datasets: [{
          data: [<?php echo $total_voted; ?>, <?php echo max($enrolled_students - $total_voted, 0); ?>],
          backgroundColor: ['#2273ddff', '#ffc60bff'],
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { position: 'bottom' } }
      }
    });
  </script>

</body>
</html>
<?php
// Only respond to the actual AJAX save-note call
if ($_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['note']) &&
    isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {

    session_start();
    require_once __DIR__ . '/audit_helper.php';

    $host = "localhost";
    $user = "u803144294_system";
    $pass = "O=EE=zbFZl&4";
    $db   = "u803144294_system";
    $conn = new mysqli($host, $user, $pass, $db);
    if ($conn->connect_error) {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'DB connection failed']);
        exit;
    }

    $admin_user_id = $_SESSION['user_id'] ?? 0;
    $note = $_POST['note'] ?? '';

    $stmt = $conn->prepare("REPLACE INTO adminnotes (admin_user_id, note_text) VALUES (?, ?)");
    $stmt->bind_param("is", $admin_user_id, $note);

    if ($stmt->execute()) {
        log_action($conn, $admin_user_id, "Saved admin note");
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => $stmt->error]);
    }
    $stmt->close();
    $conn->close();
    exit;
}
?>