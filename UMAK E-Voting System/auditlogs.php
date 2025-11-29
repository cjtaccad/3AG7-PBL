<?php

// auditlogs.php
// Simple Audit Logs viewer with filters, modals, and 'Generate Ballots' button.
// Put this file in your admin iframe directory.

error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

$host = "localhost";
$user = "u803144294_system";  
$pass = "3AINS-G7_db"; 
$db   = "u803144294_system";
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) die("DB Connect Error: " . $conn->connect_error);

date_default_timezone_set('Asia/Manila');

// 24 hour format sa timestamp
// Helper to format timestamp: November 25, 2025 14:32:10
function fmt_dt($dt) {
    if (!$dt) return '';
    // UTC → Manila (+8 h)
    $date = new DateTime($dt, new DateTimeZone('UTC'));
    $date->modify('+8 hours');
    return $date->format('F j, Y H:i:s');
}


/*
function fmt_dt($dt) {
    if (!$dt) return '';
    $date = new DateTime($dt, new DateTimeZone('UTC'));
    $date->modify('+8 hours');          // UTC → Manila
    return $date->format('F j, Y g:i:s A'); // 12-hour with AM/PM
}
*/

// Handle AJAX detail request for a particular log (admin or student)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_view'])) {
    $type = $_POST['ajax_view'];
    if ($type === 'log_detail') {
        $id = intval($_POST['log_id'] ?? 0);
        if ($id <= 0) { echo json_encode(['ok'=>false,'msg'=>'Invalid id']); exit; }
        $stmt = $conn->prepare("SELECT l.log_id, l.user_id, l.action, l.log_timestamp, u.role, u.email, u.full_name
                                FROM auditlogs l
                                LEFT JOIN users u ON l.user_id = u.user_id
                                WHERE l.log_id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        $stmt->close();
        
        // Check if this log has an attached PDF file
        $has_pdf = false;
        $pdf_filename = null;
        if (strpos($row['action'], 'Exported PDF results - File:') !== false || 
            strpos($row['action'], 'Exported ballot list PDF - File:') !== false) {
            // Extract filename from action string
            preg_match('/File:\s*(.+?)(\s*\(|$)/', $row['action'], $matches);
            if (!empty($matches[1])) {
                $pdf_filename = trim($matches[1]);
                $has_pdf = true;
            }
        }
        
        $row['has_pdf'] = $has_pdf;
        $row['pdf_filename'] = $pdf_filename;
        
        echo json_encode(['ok'=>true,'data'=>$row]);
        exit;
    }

    if ($type === 'student_ballot') {
        // return a student's ballot choices for preview (no personal data)
        $ballot_id = intval($_POST['ballot_id'] ?? 0);
        if ($ballot_id <= 0) { echo json_encode(['ok'=>false,'msg'=>'Invalid ballot id']); exit; }

        $sql = "SELECT v.ballot_id, v.vote_id, v.candidate_id, p.position_name, u.full_name AS candidate_name, u.user_id AS candidate_user_id
                FROM votes v
                JOIN candidates ca ON v.candidate_id = ca.candidate_id
                JOIN positions p ON ca.position_id = p.position_id
                JOIN users u ON ca.user_id = u.user_id
                WHERE v.ballot_id = ?
                ORDER BY p.position_id ASC";
        $stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(['ok' => false, 'msg' => 'SQL error: ' . $conn->error]);
    exit;
}

        $stmt->bind_param('i', $ballot_id);
        $stmt->execute();
        $res = $stmt->get_result();
        $rows = [];
        while ($r = $res->fetch_assoc()) $rows[] = $r;
        $stmt->close();
        echo json_encode(['ok'=>true,'data'=>$rows]);
        exit;
    }
    // else
    echo json_encode(['ok'=>false,'msg'=>'Unknown request']);
    exit;
}

// Handle PDF download request
if (isset($_GET['download_pdf'])) {
    $filename = basename($_GET['download_pdf']); // sanitize
    $filepath = __DIR__ . '/audit_exports/' . $filename;
    
    if (file_exists($filepath)) {
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        readfile($filepath);
        exit;
    } else {
        die('PDF file not found.');
    }
}

// ----------------- Page: GET filters -----------------
$filter_user_type = $_GET['user_type'] ?? 'ALL'; // ALL / ADMIN / STUDENT
$filter_date = $_GET['date'] ?? ''; // yyyy-mm-dd

// Build SQL for logs
$conds = [];
$params = [];
$types = '';

if ($filter_user_type === 'ADMIN') {
    $conds[] = "u.role = 'ADMIN'";
} elseif ($filter_user_type === 'STUDENT') {
    $conds[] = "u.role = 'STUDENT'";
}
if ($filter_date) {
    // search logs within the selected date (00:00:00 - 23:59:59)
    $dt_start = $filter_date . ' 00:00:00';
    $dt_end   = $filter_date . ' 23:59:59';
    $conds[] = "l.log_timestamp BETWEEN ? AND ?";
    $params[] = $dt_start;
    $params[] = $dt_end;
    $types .= 'ss';
}

$sql = "SELECT l.log_id, l.user_id, l.action, l.log_timestamp, u.role, u.email, u.full_name
        FROM auditlogs l
        LEFT JOIN users u ON l.user_id = u.user_id";

if ($conds) $sql .= " WHERE " . implode(' AND ', $conds);
$sql .= " ORDER BY l.log_timestamp DESC LIMIT 500"; // limit to 500 for performance

$stmt = null;
$res = null;
if ($params) {
    $stmt = $conn->prepare($sql);
    // dynamic binding
    $refs = [];
    $refs[] = &$types;
    foreach ($params as $k => $v) $refs[] = &$params[$k];
    call_user_func_array([$stmt, 'bind_param'], $refs);
    $stmt->execute();
    $res = $stmt->get_result();
} else {
    $res = $conn->query($sql);
}
$logs = [];
if ($res) while ($row = $res->fetch_assoc()) $logs[] = $row;
if ($stmt) $stmt->close();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Audit Logs</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    label {
      font-weight: 700 !important;
      color: #032c63;
    }
    :root{ --muted:#6c757d; }
    body { background:#f8f9fb; color:var(--blue); }
    .card { border-radius:10px; }
    .log-row { cursor:pointer; }
    .small-muted { color: #6c757d; font-size: .9rem; }
    .btn-yellow { background: #f5d000; color: #032c63; border: none; }
  </style>
</head>
<body>
<div class="container-fluid p-3">
  

  <div class="card mb-3 p-3">
    <h3 class="fw-bold mb-3">AUDIT LOGS</h3>
    <div class="row g-2 align-items-end">
      <div class="col-md-3">
        <label>User</label>
        <select id="filterType" class="form-select">
          <option value="ALL" <?= $filter_user_type==='ALL'?'selected':'' ?>>ALL</option>
          <option value="ADMIN" <?= $filter_user_type==='ADMIN'?'selected':'' ?>>Admin</option>
          <option value="STUDENT" <?= $filter_user_type==='STUDENT'?'selected':'' ?>>Students</option>
        </select>
      </div>
      <div class="col-md-4">
        <label>Date</label>
        <div class="input-group">
          <input id="filterDate" type="date" class="form-control" value="<?= htmlspecialchars($filter_date) ?>">
          <button id="btnClear" class="btn btn-secondary">Clear filter</button>
        </div>
        
      </div>
      <div class="col-md-5 text-end">
        <a id="btnGenerate" class="btn btn-primary"
           href="generate_ballots_pdf.php?date=<?= urlencode($filter_date) ?>"
           target="_blank">
          <i class="fa-solid fa-file-pdf me-2"></i>Generate All Ballots
        </a>
      </div>
    </div>
  </div>

  <div class="card p-0">
    <div class="table-responsive">
      <table class="table mb-0">
        <thead class="table-light">
          <tr>
            <th style="width:220px">Timestamp</th>
            <th style="width:130px">User</th>
            <th>Activity</th>
            <th style="width:130px">Action</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($logs)): ?>
            <tr><td colspan="4" class="text-center text-muted py-4">No audit logs found for the selected filters.</td></tr>
          <?php else: foreach ($logs as $l): ?>
            <tr class="log-row" data-log-id="<?= $l['log_id'] ?>" data-role="<?= $l['role'] ?>">
              <td><?= htmlspecialchars(fmt_dt($l['log_timestamp'])) ?></td>
              <td>
                <div><strong><?= htmlspecialchars($l['role'] ?: 'SYSTEM') ?></strong></div>
                <div class="small-muted"><?= htmlspecialchars($l['email'] ?: '—') ?></div>
              </td>
              <td><?= htmlspecialchars($l['action']) ?></td>
              <td class="text-center">
                <?php if (($l['role'] ?? '') === 'STUDENT'): ?>
                  <?php
                    // find ballot_id for this student's latest ballot (if any) for quick preview
                    $bid = 0;
                    $bq = $conn->prepare("SELECT ballot_id FROM ballots WHERE user_id = ? LIMIT 1");
                    $bq->bind_param('i', $l['user_id']);
                    $bq->execute();
                    $br = $bq->get_result();
                    if ($br && $r=$br->fetch_assoc()) $bid = $r['ballot_id'];
                    $bq->close();
                  ?>
                  <?php if ($bid): ?>
                    <button class="btn btn-sm btn-info btn-view-log" data-ballot-id="<?= $bid ?>">View Ballot</button>
                  <?php else: ?>
                    <span class="small-muted">No ballot</span>
                  <?php endif; ?>
                <?php else: ?>
                  <button class="btn btn-sm btn-info btn-view-log" data-log-id="<?= $l['log_id'] ?>">View Log</button>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>

<!-- Modals -->
<div class="modal fade" id="logModal" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content">
  <div class="modal-header"><h5 class="modal-title">Activity Details</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
  <div class="modal-body" id="logModalBody"><div class="text-muted">Loading...</div></div>
  <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Close</button></div>
</div></div></div>

<div class="modal fade" id="ballotModal" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content">
  <div class="modal-header"><h5 class="modal-title">Student Ballot (Preview)</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
  <div class="modal-body" id="ballotModalBody"><div class="text-muted">Loading...</div></div>
  <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Close</button></div>
</div></div></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// auto-filter behavior
document.getElementById('filterType').addEventListener('change', applyFilters);
document.getElementById('filterDate').addEventListener('change', applyFilters);
document.getElementById('btnClear').addEventListener('click', ()=> {
  location.href = location.pathname;
});
function applyFilters(){
  const t = document.getElementById('filterType').value;
  const d = document.getElementById('filterDate').value;
  const params = new URLSearchParams();
  if (t && t !== 'ALL') params.set('user_type', t);
  if (d) params.set('date', d);
  location.search = params.toString();
}

// View admin log details
document.querySelectorAll('.btn-view-log').forEach(b=>{
  b.addEventListener('click', ()=>{
    const id = b.getAttribute('data-ballot-id') || b.getAttribute('data-log-id');
    fetch(location.pathname, {
      method:'POST',
      headers:{'Content-Type':'application/x-www-form-urlencoded'},
      body: new URLSearchParams({
        ajax_view: b.hasAttribute('data-ballot-id') ? 'student_ballot' : 'log_detail',
        ballot_id: b.getAttribute('data-ballot-id'),
        log_id: b.getAttribute('data-log-id')
      })
    }).then(r=>r.text()).then(t=>{
  console.log('RAW RESPONSE:', t);   // <-- inspect in console
  const j = JSON.parse(t);           // will still throw if not JSON
      if (!j.ok) {
        document.getElementById('logModalBody').innerText = j.msg;
        new bootstrap.Modal(document.getElementById('logModal')).show();
      } else {
        const d = j.data;
        if (b.hasAttribute('data-ballot-id')) {
          // ✅ Show ballot modal
          if (!d.length) {
            document.getElementById('ballotModalBody').innerHTML = '<div class="text-muted">Student has not voted any positions.</div>';
          } else {
            let html = '<ol>';
            d.forEach(r => {
              html += `<li><strong>${escapeHtml(r.position_name)}</strong>: ${escapeHtml(r.candidate_name)} (ID: ${escapeHtml(r.candidate_user_id)})</li>`;
            });
            html += '</ol>';
            document.getElementById('ballotModalBody').innerHTML = html;
          }
          new bootstrap.Modal(document.getElementById('ballotModal')).show();
        } else {
          // ✅ Show log modal
          let html = `<p><strong>Action:</strong> ${escapeHtml(d.action)}</p>`;
          html += `<p><strong>User ID:</strong> ${d.user_id}</p>`;
          html += `<p><strong>Role:</strong> ${escapeHtml(d.role)}</p>`;
          html += `<p><strong>Email:</strong> ${escapeHtml(d.email)}</p>`;
          const manilaTime = new Date(new Date(d.log_timestamp).getTime() + 8 * 3600 * 1000);
html += `<p><strong>Time:</strong> ${manilaTime.toLocaleString('en-PH', { timeZone: 'Asia/Manila' })}</p>`;
          if (d.has_pdf && d.pdf_filename) {
            html += `<hr><p><strong>Attached PDF:</strong></p>`;
            html += `<a href="?download_pdf=${encodeURIComponent(d.pdf_filename)}" class="btn btn-sm btn-danger"><i class="fa-solid fa-file-pdf me-1"></i> Download PDF</a>`;
          }
          document.getElementById('logModalBody').innerHTML = html;
          new bootstrap.Modal(document.getElementById('logModal')).show();
        }
      }
    });
  });
});

// Preview student ballot
document.querySelectorAll('.btn-preview-ballot').forEach(b=>{
  b.addEventListener('click', ()=>{
    const bid = b.getAttribute('data-ballot-id');
    fetch(location.pathname, {
      method:'POST',
      headers:{'Content-Type':'application/x-www-form-urlencoded'},
      body: new URLSearchParams({ajax_view:'student_ballot','ballot_id':bid})
    }).then(r=>r.json()).then(j=>{
      if (!j.ok) { document.getElementById('ballotModalBody').innerText = j.msg; }
      else {
        const rows = j.data;
        if (!rows.length) document.getElementById('ballotModalBody').innerHTML = '<div class="text-muted">Student has not voted any positions.</div>';
        else {
          let html = '<ol>';
          rows.forEach(r => {
            html += `<li><strong>${escapeHtml(r.position_name)}</strong>: ${escapeHtml(r.candidate_name)} (ID: ${escapeHtml(r.candidate_user_id)})</li>`;
          });
          html += '</ol>';
          document.getElementById('ballotModalBody').innerHTML = html;
        }
      }
      new bootstrap.Modal(document.getElementById('ballotModal')).show();
    });
  });
});

function escapeHtml(s){ if(!s) return ''; return String(s).replaceAll('&','&amp;').replaceAll('<','&lt;').replaceAll('>','&gt;'); }
</script>
</body>
</html>
