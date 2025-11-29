<?php
// schedule.php
// Election Schedule module (set/view/delete schedules)
// Place in your evoting_system project root and load via admin iframe during development.

error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
date_default_timezone_set('Asia/Manila');

require_once __DIR__ . '/audit_helper.php';

// ADD THESE LINES:
$host = "localhost";
$user = "u803144294_system";  
$pass = "3AINS-G7_db"; 
$db   = "u803144294_system";

// Now create the connection
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("DB Connect Error: " . $conn->connect_error);
}

// Helper: flash message
function flash($msg, $type = 'success') {
    $_SESSION['schedule_flash'] = ['message' => $msg, 'type' => $type];
}

// Handle create schedule (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_schedule') {
    $allowed = ['VOTING', 'VIEW_CANDIDATES', 'CANDIDATE_CHECKING'];
$phase   = in_array($_POST['phase'], $allowed) ? $_POST['phase'] : 'VIEW_CANDIDATES';
    $start_date = trim($_POST['start_date'] ?? '');
    $end_date = trim($_POST['end_date'] ?? '');

    // Basic validation
    if (!$start_date || !$end_date) {
        flash('Please provide both start and end dates.', 'danger');
        header('Location: schedule.php');
        exit;
    }

    // Parse dates and attach default times 08:00:00 and 17:00:00
    $start_dt = DateTime::createFromFormat('Y-m-d H:i', $start_date . ' 08:00');
    $end_dt = DateTime::createFromFormat('Y-m-d H:i', $end_date . ' 17:00');
    if (!$start_dt || !$end_dt) {
        flash('Invalid date format.', 'danger');
        header('Location: schedule.php');
        exit;
    }

    // Ensure start <= end
    if ($end_dt < $start_dt) {
        flash('End date cannot be earlier than start date.', 'danger');
        header('Location: schedule.php');
        exit;
    }

    // Insert schedule
    $stmt = $conn->prepare("INSERT INTO schedule (phase, start_datetime, end_datetime) VALUES (?, ?, ?)");
    $s1 = $start_dt->format('Y-m-d H:i:s');
    $s2 = $end_dt->format('Y-m-d H:i:s');
    $stmt->bind_param('sss', $phase, $s1, $s2);
    if ($stmt->execute()) {
        flash('Schedule added successfully.', 'success');
        // audit: only include the date, not the time
        $date1 = $start_dt->format('F d, Y');
        $date2 = $end_dt->format('F d, Y');
        log_action($conn, $_SESSION['user_id'] ?? 0, "Added schedule: {$phase} from {$date1} to {$date2}");
    } else {
        flash('Error adding schedule: ' . $stmt->error, 'danger');
    }
    $stmt->close();
    header('Location: schedule.php');
    exit;
}

// Handle AJAX delete (POST) -> returns JSON
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_schedule') {
    header('Content-Type: application/json; charset=utf-8');
    $id = intval($_POST['id'] ?? 0);
    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid schedule ID.']);
        exit;
    }

    // Fetch schedule (to allow potential checks)
    $q = $conn->prepare("SELECT phase, start_datetime, end_datetime FROM schedule WHERE schedule_id = ?");
    $q->bind_param('i', $id);
    $q->execute();
    $res = $q->get_result();
    if (!$res || $res->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Schedule not found.']);
        $q->close();
        exit;
    }
    $row = $res->fetch_assoc();
    $q->close();

    // Delete
    $d = $conn->prepare("DELETE FROM schedule WHERE schedule_id = ?");
    $d->bind_param('i', $id);
    if ($d->execute()) {
        echo json_encode(['success' => true, 'message' => 'Schedule deleted successfully.']);
        // Audit: show deleted voting schedule if phase is VOTING
        if ($row['phase'] === 'VOTING') {
            log_action($conn, $_SESSION['user_id'] ?? 0, "Deleted voting schedule");
        } else {
            log_action($conn, $_SESSION['user_id'] ?? 0, "Deleted candidate viewing schedule");
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Error deleting schedule: ' . $conn->error]);
    }
    $d->close();
    exit;
}

// Fetch schedules for display
$sche = $conn->query("SELECT schedule_id, phase, start_datetime, end_datetime FROM schedule ORDER BY start_datetime DESC");
$schedules = [];
if ($sche) while ($r = $sche->fetch_assoc()) $schedules[] = $r;

// Helper to get status
function get_status($start, $end) {
    $now = new DateTime();
    $s = new DateTime($start);
    $e = new DateTime($end);
    if ($now >= $s && $now <= $e) return 'ON GOING';
    return 'CLOSED';
}

// Grab flash (if any)
$flash = $_SESSION['schedule_flash'] ?? null;
if ($flash) unset($_SESSION['schedule_flash']);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Schedule — UMAK E-Vote</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- flatpickr -->
  <link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">
  <style>
    .label-module { font-size:1.25rem; font-weight:700; color:#032c63; }
    .card-small { max-width:480px; }
    .note-italic { font-style:italic; color:#555; }
    .status-ongoing { color: #0f5132; background:#d1e7dd; padding:6px 8px; border-radius:6px; font-weight:600; display:inline-block; }
    .status-closed { color:#842029; background:#f8d7da; padding:6px 8px; border-radius:6px; font-weight:600; display:inline-block; }

    /* Calendar popup only (do NOT affect input textbox) */
    .flatpickr-calendar {
      font-size: 11px;           /* smaller text in calendar */
      /* transform: scale(0.92); */ /* Try commenting this out */
      transform-origin: 0 0;
      max-width: 300px;
    }
    .flatpickr-day {
      padding: 4px;
      width: 28px;
      height: 28px;
      line-height: 28px;
    }
    .flatpickr-months .flatpickr-month {
      padding: 6px 8px;
    }

    /* Responsive tweaks for small screens */
    @media (max-width: 480px) {
      .flatpickr-calendar { transform: scale(0.88); max-width: 340px; }
      .flatpickr-day { width: 24px; height: 24px; line-height: 24px; }
    }

    label {
      font-weight: 700 !important;
      color: #032c63;
    }
  </style>
</head>
<body class="bg-light">
<div class="container-fluid p-3">

 <div class="card mb-3 p-3">
  <h3 class="fw-bold mb-2">SCHEDULE ELECTION</h3>
  <div class="row g-3">
    <!-- Left: Set schedule -->
    <div class="col-lg-4">
      <div class="card card-small shadow-sm">
        <div class="card-body">
          <h5 class="card-title mb-3">Set Schedule</h5>

          <form id="setScheduleForm" method="post" action="schedule.php">
            <input type="hidden" name="action" value="add_schedule">

            <div class="mb-3">
              <label class="form-label">Schedule Type</label>
              <select name="phase" class="form-select" required>
                <option value="VIEW_CANDIDATES">Candidate Viewing (For All Students)</option>
                <option value="VOTING">Voting (For All Students)</option>
                <option value="CANDIDATE_CHECKING">Candidate Checking (For Candidates Only)</option>
              </select>
            </div>

            <div class="mb-3">
              <label class="form-label">Opening Date</label>
              <input id="startDate" name="start_date" type="text" class="form-control" placeholder="Select opening date" autocomplete="off" required>
            </div>

            <div class="mb-3">
              <label class="form-label">Closing Date</label>
              <input id="endDate" name="end_date" type="text" class="form-control" placeholder="Select closing date" autocomplete="off" disabled required>
              <div id="endHint" class="form-text text-muted">Set opening date first.</div>
            </div>

            <div class="mb-3">
              <div class="note-italic">The system will automatically open on <strong>8:00AM</strong> and will close at <strong>5:00PM</strong></strong></div>
            </div>

            <div class="d-flex gap-2">
              <button id="btnSet" type="button" class="btn btn-primary">Set</button>
              <button type="reset" id="btnReset" class="btn btn-secondary">Reset</button>
            </div>
          </form>
        </div>
      </div>
      
    </div>

    <!-- Right: schedules table -->
    <div class="col-lg-8">
      <div class="card shadow-sm">
        <div class="card-body">
          <h5 class="card-title mb-3">Existing Schedules</h5>

          <?php if ($flash): ?>
            <div class="mb-3">
              <div class="alert alert-<?= $flash['type'] === 'danger' ? 'danger' : 'success' ?>"><?= htmlspecialchars($flash['message']) ?></div>
            </div>
          <?php endif; ?>

          <div class="table-responsive">
            <table class="table table-sm align-middle">
              <thead>
                <tr>
                  
                  <th><label>Type</label></th>
                  <th><label>Start</label></th>
                  <th><label>End</label></th>
                  <th><label>Status</label></th>
                  <th><label>Action</label></th>
                  
                </tr>
              </thead>
              <tbody id="schedulesTbody">
                <?php if (empty($schedules)): ?>
                  <tr><td colspan="5" class="text-center text-muted">No schedules set.</td></tr>
                <?php else: foreach ($schedules as $s): 
                    $status = get_status($s['start_datetime'], $s['end_datetime']);
                    $badge = $status === 'ON GOING' ? '<span class="status-ongoing">ON GOING</span>' : '<span class="status-closed">CLOSED</span>';
                    $phaseLabels = [
    'VOTING'               => 'Voting',
    'VIEW_CANDIDATES'      => 'Candidate Viewing',
    'CANDIDATE_CHECKING'   => 'Candidate Checking'
];
$typeLabel = $phaseLabels[$s['phase']] ?? 'Unknown';
                    // Format dates as: November 13, 2025
                    $start_fmt = date("F d, Y", strtotime($s['start_datetime']));
                    $end_fmt   = date("F d, Y", strtotime($s['end_datetime']));
                ?>
                <tr data-id="<?= intval($s['schedule_id']) ?>">
                  <td><?= htmlspecialchars($typeLabel) ?></td>
                  <td><?= htmlspecialchars($start_fmt) ?></td>
                  <td><?= htmlspecialchars($end_fmt) ?></td>
                  <td><?= $badge ?></td>
                  <td>
                    <button class="btn btn-sm btn-danger btn-delete-schedule" data-id="<?= intval($s['schedule_id']) ?>">Delete</button>
                  </td>
                </tr>
                <?php endforeach; endif; ?>
              </tbody>
            </table>
          </div>

        </div>
      </div>
    </div>
  </div>
 </div>
</div>

<!-- Confirmation modal for set -->
<div class="modal fade" id="confirmSetModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Confirm Schedule</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div id="confirmDetails"></div>
        <div class="mt-3">
          <small class="text-muted">Default time range will be applied: <strong>08:00 AM — 05:00 PM</strong></small>
        </div>
      </div>
      <div class="modal-footer">
        <button id="cancelSet" type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button id="confirmSet" type="button" class="btn btn-success">Confirm</button>
      </div>
    </div>
  </div>
</div>

<!-- Delete confirmation modal -->
<div class="modal fade" id="deleteScheduleModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Delete Schedule</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p>Are you sure you want to delete this Schedule?</p>
        <p class="note-italic">Note: Deleting a schedule during an open election status will automatically close the election for all the users.</p>
      </div>
      <div class="modal-footer">
        <button id="cancelDelete" type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button id="confirmDelete" type="button" class="btn btn-success">Confirm</button>
      </div>
    </div>
  </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
document.addEventListener('DOMContentLoaded', function(){
  // flatpickr instances (date only)
  const today = new Date();
  const fpStart = flatpickr("#startDate", {
    dateFormat: "Y-m-d",
    minDate: today,
    allowInput: false, // <--- add this
    clickOpens: true,  // <--- ensure this is true (default)
    onChange: function(selectedDates, dateStr){
      if (dateStr) {
        // enable end date picker and set its minDate to chosen start
        fpEnd.set('minDate', dateStr);
        document.getElementById('endDate').disabled = false;
        document.getElementById('endHint').textContent = 'Select closing date (cannot be before opening date).';
      } else {
        document.getElementById('endDate').disabled = true;
        document.getElementById('endHint').textContent = 'Set opening date first.';
      }
    }
  });

  const fpEnd = flatpickr("#endDate", {
    dateFormat: "Y-m-d",
    allowInput: false, // <--- add this
    clickOpens: true,  // <--- ensure this is true (default)
    onOpen: function(selectedDates, dateStr, instance){
      // If start not set, prevent and show hint
      const startVal = document.getElementById('startDate').value;
      if (!startVal) {
        instance.close();
        const endHint = document.getElementById('endHint');
        endHint.textContent = 'Kindly set an opening date first.';
        setTimeout(()=> endHint.textContent = 'Set opening date first.', 1800);
      }
    }
  });

  // Confirm set modal wiring
  const confirmModal = new bootstrap.Modal(document.getElementById('confirmSetModal'));
  document.getElementById('btnSet').addEventListener('click', function(){
    const typeEl = document.querySelector('select[name="phase"]');
    const start = document.getElementById('startDate').value;
    const end = document.getElementById('endDate').value;
    if (!start || !end) {
      alert('Please select both opening and closing dates.');
      return;
    }
    // prepare details (attach default times)
    const tzStart = start + ' 08:00:00';
    const tzEnd = end + ' 17:00:00';
    const typeLabel = typeEl.value === 'VOTING' ? 'Voting' : 'Candidate Viewing';
    document.getElementById('confirmDetails').innerHTML =
      '<p><strong>Type:</strong> ' + typeLabel + '</p>' +
      '<p><strong>Start:</strong> ' + tzStart + '</p>' +
      '<p><strong>End:</strong> ' + tzEnd + '</p>';
    confirmModal.show();
  });

  document.getElementById('confirmSet').addEventListener('click', function(){
    document.getElementById('setScheduleForm').submit();
  });

  // Delete modal wiring
  let scheduleToDeleteId = 0;
  const deleteModal = new bootstrap.Modal(document.getElementById('deleteScheduleModal'));

  document.querySelectorAll('.btn-delete-schedule').forEach(btn=>{
    btn.addEventListener('click', function(){
      scheduleToDeleteId = this.getAttribute('data-id');
      deleteModal.show();
    });
  });

  document.getElementById('confirmDelete').addEventListener('click', function(){
    if (!scheduleToDeleteId) return;
    // send AJAX to delete
    fetch(window.location.href, {
      method: 'POST',
      headers: {'Content-Type':'application/x-www-form-urlencoded'},
      body: new URLSearchParams({ action: 'delete_schedule', id: scheduleToDeleteId })
    })
    .then(r=>r.json())
    .then(j=>{
      deleteModal.hide();
      // show a simple toast
      const toast = document.createElement('div');
      toast.className = 'position-fixed top-0 end-0 p-3';
      toast.style.zIndex = '20000';
      toast.innerHTML = '<div class="toast align-items-center text-white border-0 ' + (j.success ? 'bg-success' : 'bg-danger') + ' show"><div class="d-flex"><div class="toast-body">' + j.message + '</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div></div>';
      document.body.appendChild(toast);
      setTimeout(()=> location.reload(), 900);
    })
    .catch(err=>{
      alert('Error deleting schedule.');
      deleteModal.hide();
    });
  });

  // Reset form behavior: disable endDate
  document.getElementById('btnReset').addEventListener('click', function(){
    document.getElementById('endDate').disabled = true;
    document.getElementById('endHint').textContent = 'Set opening date first.';
    fpStart.clear();
    fpEnd.clear();
  });

});
</script>
</body>
</html>
