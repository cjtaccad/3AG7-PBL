<?php
// view_candidates.php  (testing version - no login required)

error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once __DIR__ . '/audit_helper.php';

$host = "localhost";
$user = "u803144294_system";
$pass = "3AINS-G7_db";
$db   = "u803144294_system";
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) die("DB Connect Error: " . $conn->connect_error);

function json_resp($code, $message, $extra = []) {
    header('Content-Type: application/json');
    echo json_encode(array_merge(['code' => $code, 'message' => $message], $extra));
    exit;
}

/* -------------------- AJAX HANDLERS -------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    /* Delete single candidate */
    if ($action === 'delete_candidate') {
        $candidate_id = intval($_POST['candidate_id'] ?? 0);
        $q = $conn->prepare("SELECT user_id, profile_picture FROM candidates WHERE candidate_id = ?");
        $q->bind_param('i', $candidate_id);
        $q->execute();
        $res = $q->get_result();
        $row = $res ? $res->fetch_assoc() : null;
        $candidate_user_id = $row['user_id'] ?? 0;
        $pic = $row['profile_picture'] ?? '';
        $q->close();

        $del = $conn->prepare("DELETE FROM candidates WHERE candidate_id = ?");
        $del->bind_param('i', $candidate_id);
        if ($del->execute()) {
            if (!empty($pic) && file_exists($pic)) @unlink($pic);
            log_action($conn, $_SESSION['user_id'] ?? 0, "Deleted candidate {$candidate_user_id}");
            json_resp(1, 'Candidate deleted successfully.');
        } else {
            json_resp(0, 'Error deleting candidate: ' . $conn->error);
        }
        exit;
    }

    /* Update candidate */
    if ($action === 'update_candidate') {
        $candidate_id = intval($_POST['candidate_id'] ?? 0);
        $position_id  = intval($_POST['position_id'] ?? 0);
        $platforms    = trim($_POST['platforms'] ?? '');
        $credentials  = trim($_POST['credentials'] ?? '');

        if ($candidate_id <= 0 || $position_id <= 0) json_resp(0, 'Invalid data.');

        /* new uploaded file or base64 drag-and-drop */
        $new_pic_path = null;
        if (!empty($_POST['cropped_image'])) {
            $saved = save_base64_image($_POST['cropped_image']);
            if ($saved) $new_pic_path = $saved;
        } elseif (!empty($_FILES['update_candidate_file']) && $_FILES['update_candidate_file']['error'] === UPLOAD_ERR_OK) {
            $tmp  = $_FILES['update_candidate_file']['tmp_name'];
            $orig = basename($_FILES['update_candidate_file']['name']);
            $ext  = pathinfo($orig, PATHINFO_EXTENSION);
            $upDir = 'uploads/candidates';
            if (!is_dir($upDir)) mkdir($upDir, 0755, true);
            $filename = uniqid('cand_up_') . '.' . $ext;
            $dest = $upDir . '/' . $filename;
            if (move_uploaded_file($tmp, $dest)) $new_pic_path = $dest;
        }

        /* candidate user_id for audit */
        $q2 = $conn->prepare("SELECT user_id FROM candidates WHERE candidate_id = ?");
        $q2->bind_param('i', $candidate_id);
        $q2->execute();
        $r2 = $q2->get_result();
        $rr = $r2 ? $r2->fetch_assoc() : null;
        $candidate_user_id = $rr['user_id'] ?? 0;
        $q2->close();

        if ($new_pic_path) {
            $stmt = $conn->prepare("UPDATE candidates SET position_id=?, platforms=?, credentials=?, profile_picture=? WHERE candidate_id=?");
            $stmt->bind_param('isssi', $position_id, $platforms, $credentials, $new_pic_path, $candidate_id);
        } else {
            $stmt = $conn->prepare("UPDATE candidates SET position_id=?, platforms=?, credentials=? WHERE candidate_id=?");
            $stmt->bind_param('issi', $position_id, $platforms, $credentials, $candidate_id);
        }

        if ($stmt->execute()) {
            log_action($conn, $_SESSION['user_id'] ?? 0, "Updated candidate {$candidate_user_id}");
            json_resp(1, 'Candidate updated successfully.');
        } else {
            json_resp(0, 'Error updating candidate.');
        }
        exit;
    }

    /* Clear all candidates */
    if ($action === 'clear_all_candidates') {
        $resPics = $conn->query("SELECT profile_picture FROM candidates");
        if ($resPics) {
            while ($r = $resPics->fetch_assoc()) {
                if (!empty($r['profile_picture']) && file_exists($r['profile_picture'])) @unlink($r['profile_picture']);
            }
        }
        $conn->query("SET FOREIGN_KEY_CHECKS=0");
        $truncate = $conn->query("TRUNCATE TABLE candidates");
        $conn->query("SET FOREIGN_KEY_CHECKS=1");
        if ($truncate) {
            log_action($conn, $_SESSION['user_id'] ?? 0, "Cleared all candidates");
            json_resp(1, 'All candidates deleted successfully.');
        } else {
            json_resp(0, 'Error clearing candidates: ' . $conn->error);
        }
        exit;
    }
    json_resp(0, 'Invalid action.');
}

/* -------------------- FORM HANDLER: Update Candidate (non-AJAX fallback) -------------------- */
if (isset($_POST['update_candidate'])) {
    $cid            = intval($_POST['candidate_id']);
    $position_id    = intval($_POST['position_id']);
    $platforms      = trim($_POST['platforms']);
    $credentials    = trim($_POST['credentials']);
    $existing_photo = trim($_POST['existing_photo']);
    $new_photo      = null;

    /* base64 from drag-and-drop */
    if (!empty($_POST['cropped_image'])) {
        $saved = save_base64_image($_POST['cropped_image']);
        if ($saved) $new_photo = $saved;
    } elseif (!empty($_FILES['new_photo']['tmp_name'])) {
        $tmp = $_FILES['new_photo']['tmp_name'];
        $ext = pathinfo($_FILES['new_photo']['name'], PATHINFO_EXTENSION);
        $upDir = 'uploads/candidates';
        if (!is_dir($upDir)) mkdir($upDir, 0755, true);
        $filename = uniqid('cand_') . '.' . $ext;
        $dest = "$upDir/$filename";
        if (move_uploaded_file($tmp, $dest)) {
            $new_photo = $dest;
            if ($existing_photo && file_exists($existing_photo)) unlink($existing_photo);
        }
    }
    $final_photo = $new_photo ?: $existing_photo;

    $stmt = $conn->prepare("UPDATE candidates SET position_id=?, platforms=?, credentials=?, profile_picture=? WHERE candidate_id=?");
    $stmt->bind_param('isssi', $position_id, $platforms, $credentials, $final_photo, $cid);
    if ($stmt->execute()) {
        log_action($conn, $_SESSION['user_id'] ?? 0, "Updated candidate id {$cid}");
        $_SESSION['flash'] = ['message' => 'Candidate updated successfully!', 'type' => 'success'];
    } else {
        $_SESSION['flash'] = ['message' => 'Error updating candidate: ' . $stmt->error, 'type' => 'danger'];
    }
    $stmt->close();
    header("Location: view_candidates.php");
    exit;
}

/* -------------------- FETCH POSITIONS + CANDIDATES -------------------- */
$positions = [];
$resPos = $conn->query("SELECT position_id, position_name FROM positions ORDER BY position_id ASC");
if ($resPos) while ($r = $resPos->fetch_assoc()) $positions[] = $r;

$filter_position = intval($_GET['position_id'] ?? 0);
$search_user_id  = intval($_GET['search_user_id'] ?? 0);

$candidates_by_position = [];
$sql = "SELECT ca.candidate_id, ca.user_id, ca.position_id, ca.platforms, ca.credentials, ca.profile_picture,
               u.full_name, s.year_level, c.course_name, co.college_name, p.position_name
        FROM candidates ca
        JOIN users u ON ca.user_id = u.user_id
        JOIN students s ON u.user_id = s.user_id
        JOIN courses c ON s.course_id = c.course_id
        JOIN colleges co ON c.college_id = co.college_id
        JOIN positions p ON ca.position_id = p.position_id";

$conds = []; $params = []; $types = '';
if ($filter_position > 0) { $conds[] = "ca.position_id=?"; $types .= 'i'; $params[] = $filter_position; }
if ($search_user_id > 0)  { $conds[] = "ca.user_id=?";    $types .= 'i'; $params[] = $search_user_id; }
if ($conds) $sql .= " WHERE " . implode(" AND ", $conds);
$sql .= " ORDER BY p.position_id ASC, u.full_name ASC";

if ($params) {
    $stmt = $conn->prepare($sql);
    $refs = [&$types];
    foreach ($params as $k => $v) $refs[] = &$params[$k];
    call_user_func_array([$stmt, 'bind_param'], $refs);
    $stmt->execute();
    $res = $stmt->get_result();
} else {
    $res = $conn->query($sql);
}
if ($res) while ($row = $res->fetch_assoc()) {
    $pid = intval($row['position_id']);
    if (!isset($candidates_by_position[$pid])) $candidates_by_position[$pid] = ['position_name' => $row['position_name'], 'candidates' => []];
    $candidates_by_position[$pid]['candidates'][] = $row;
}

/* helper reused by AJAX update */
function save_base64_image($dataurl, $targetDir = 'uploads/candidates') {
    if (!$dataurl) return null;
    if (!preg_match('/^data:image\/(\w+);base64,/', $dataurl, $type)) return null;
    $data = substr($dataurl, strpos($dataurl, ',') + 1);
    $data = base64_decode($data);
    if ($data === false) return null;
    $ext = strtolower($type[1]);
    if (!in_array($ext, ['jpg','jpeg','png','gif','webp'])) $ext = 'png';
    if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);
    $filename = uniqid('cand_up_') . '.' . $ext;
    $filePath = rtrim($targetDir, '/') . '/' . $filename;
    if (file_put_contents($filePath, $data) === false) return null;
    return $filePath;
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>View Candidates</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <script src="https://unpkg.com/html5-qrcode@2.3.8/minified/html5-qrcode.min.js"></script>
  <style>
    body{ background:url("bg.jpg") no-repeat center center fixed; background-size:cover; }
    .btn-yellow{ background:#024399ff; color:#032c63; border:none; }
    .candidate-row{ padding:8px 10px; border-bottom:1px solid #eee; display:flex; justify-content:space-between; align-items:center; }
    .candidate-info{ font-size:.95rem; }
    .thumb{ width:60px; height:75px; object-fit:cover; border-radius:6px; border:1px solid #ccc; }
    .position-title{ margin-top:1rem; margin-bottom:-0.25rem; font-weight:700; color:#032c63; background:#f8f9fb; padding:6px 8px; border-radius:6px; text-transform:uppercase; }
    .toast-container{ position:fixed; top:1rem; right:1rem; z-index:9999; }
    .action-btn-small{ padding:.4rem .7rem; font-size:.92rem; line-height:1; border-radius:.45rem; }
    #upd_dropbox.dragover{ border:2px dashed #024399ff; background-color:#f0f8ff; }
  </style>
</head>
<body>
<div class="container-fluid p-3">
  <div class="card mb-3"><div class="card-body">
    <h3 class="fw-bold mb-4">VIEW CANDIDATES</h3>
    <form id="filterForm" class="row g-2 align-items-center">
      <div class="col-auto"><select id="positionFilter" name="position_id" class="form-select">
        <option value="0">Show all</option>
        <?php foreach ($positions as $p): ?>
          <option value="<?= $p['position_id'] ?>" <?= $filter_position==$p['position_id']?'selected':'' ?>><?= htmlspecialchars($p['position_name']) ?></option>
        <?php endforeach; ?>
      </select></div>
      <div class="col-auto"><input type="text" id="searchUser" name="search_user_id" value="<?= $search_user_id>0?$search_user_id:'' ?>" class="form-control" placeholder="Search by ID"></div>
      <div class="col-auto"><button class="btn btn-primary">Filter</button>
      <button type="button" id="btnReset" class="btn btn-secondary">Reset</button></div>
      <div class="col-auto ms-auto"><button id="btnClearAll" type="button" class="btn btn-danger">Delete all</button></div>
    </form>
  </div></div>

  <?php if (empty($candidates_by_position)): ?>
    <div class="alert alert-secondary">No candidates found.</div>
  <?php else:
        $loop_positions = ($filter_position > 0)
            ? array_filter($positions, fn($p) => $p['position_id'] == $filter_position)
            : $positions;
        foreach ($loop_positions as $p):
          $pid = $p['position_id']; ?>
      <div class="position-title"><?= htmlspecialchars($p['position_name']) ?></div>
      <div class="card mb-4"><div class="card-body p-0">
      <?php if (isset($candidates_by_position[$pid])):
              foreach ($candidates_by_position[$pid]['candidates'] as $c): ?>
          <div class="candidate-row" data-id="<?= $c['candidate_id'] ?>">
            <div class="d-flex align-items-center gap-2">
              <?php if ($c['profile_picture']): ?><img src="<?= htmlspecialchars($c['profile_picture']) ?>" class="thumb"><?php else: ?><div class="thumb bg-light text-center">No<br>Img</div><?php endif; ?>
              <div class="candidate-info">
                <div><strong>Student ID:</strong> <?= $c['user_id'] ?></div>
                <div><strong>Name:</strong> <?= htmlspecialchars($c['full_name']) ?></div>
                <div><strong>Year:</strong> <?= $c['year_level'] ?></div>
                <div><strong>College:</strong> <?= htmlspecialchars($c['college_name']) ?></div>
              </div>
            </div>
            <div class="d-flex gap-1">
              <button type="button" class="btn btn-primary action-btn-small btn-update" data-cand='<?= json_encode($c) ?>' title="Update candidate">Update</button>
              <button type="button" class="btn btn-danger action-btn-small btn-delete" data-id="<?= $c['candidate_id'] ?>" title="Delete candidate">Delete</button>
            </div>
          </div>
      <?php endforeach; else: ?><div class="p-3 text-muted">No candidates for this position.</div><?php endif; ?>
      </div></div>
  <?php endforeach; endif; ?>
</div>

<!-- Toast -->
<div class="toast-container" id="toastContainer"></div>

<!-- Clear Modal -->
<div class="modal fade" id="clearAllModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
  <div class="modal-header"><h5>Clear All Candidates</h5></div>
  <div class="modal-body">Are you sure you want to delete all candidates?</div>
  <div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
    <button type="button" id="confirmClear" class="btn btn-danger">Confirm</button>
  </div>
</div></div></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
/* ---------- generic toast ---------- */
function showToast(msg, ok=true){
  const el=document.createElement('div');
  el.className='toast align-items-center text-white border-0 show';
  el.innerHTML=`<div class="d-flex ${ok?'bg-success':'bg-danger'} rounded"><div class="toast-body">${msg}</div></div>`;
  document.getElementById('toastContainer').appendChild(el);
  setTimeout(()=>el.remove(),3000);
}

/* ---------- filter ---------- */
document.getElementById('filterForm').addEventListener('submit',e=>{
  e.preventDefault();
  const pos=document.getElementById('positionFilter').value;
  const uid=document.getElementById('searchUser').value.trim();
  const params=new URLSearchParams();
  if(pos!=='0') params.set('position_id',pos);
  if(uid!=='') params.set('search_user_id',uid);
  location.search=params.toString()||location.pathname;
});
document.getElementById('btnReset').addEventListener('click',()=>location.href=location.pathname);

/* ---------- delete ---------- */
document.querySelectorAll('.btn-delete').forEach(btn=>{
  btn.addEventListener('click',()=>{
    const id=btn.dataset.id;
    fetch('',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:new URLSearchParams({action:'delete_candidate',candidate_id:id})})
      .then(r=>r.json()).then(j=>{ showToast(j.message,j.code===1); if(j.code===1)setTimeout(()=>location.reload(),800); });
  });
});

/* ---------- clear all ---------- */
document.getElementById('btnClearAll').addEventListener('click',()=>new bootstrap.Modal(document.getElementById('clearAllModal')).show());
document.getElementById('confirmClear').addEventListener('click',()=>{
  fetch('',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:new URLSearchParams({action:'clear_all_candidates'})})
    .then(r=>r.json()).then(j=>{ showToast(j.message,j.code===1); setTimeout(()=>location.reload(),800); });
});
</script>

<!-- Update Candidate Modal -->
<div class="modal fade" id="updateCandidateModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <form id="updateCandidateForm" method="post" enctype="multipart/form-data">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title"><strong>Update Candidate</strong></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="candidate_id" id="upd_candidate_id">
          <input type="hidden" name="existing_photo" id="existing_photo">
          <input type="hidden" name="cropped_image" id="upd_cropped_image">

          <div class="mb-3">
            <label class="form-label fw-semibold">Student Information</label>
            <div class="border p-2 rounded bg-light">
              <p class="mb-1"><strong>ID:</strong> <span id="upd_user_id"></span></p>
              <p class="mb-1"><strong>Name:</strong> <span id="upd_full_name"></span></p>
              <p class="mb-1"><strong>Year:</strong> <span id="upd_year"></span></p>
              <p class="mb-1"><strong>Course:</strong> <span id="upd_course"></span></p>
              <p class="mb-1"><strong>College:</strong> <span id="upd_college"></span></p>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">Position</label>
            <select name="position_id" id="upd_position_id" class="form-select" required>
              <option value="">-- Select Position --</option>
              <?php foreach ($positions as $p): ?>
                <option value="<?= $p['position_id'] ?>"><?= htmlspecialchars($p['position_name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label"><strong>Platforms</strong></label>
            <textarea name="platforms" id="upd_platforms" class="form-control" rows="3" required></textarea>
          </div>

          <div class="mb-3">
            <label class="form-label"><strong>Credentials</strong></label>
            <textarea name="credentials" id="upd_credentials" class="form-control" rows="3"></textarea>
          </div>

          <!-- image drag-drop -->
          <div class="mb-3">
            <label class="form-label fw-bold">Candidate Image</label>
            <div id="upd_dropbox" class="border rounded position-relative p-3 text-center" style="height:230px; width:185px; cursor:pointer; margin:auto;">
              <div id="upd_placeholder" class="text-muted">Drag & Drop or Click to Upload</div>
              <img id="upd_preview" src="" style="display:none; max-height:200px; width:auto; object-fit:cover;">
              <button type="button" id="upd_remove" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1" style="display:none;">&times;</button>
            </div>
            <input type="file" id="upd_file" name="update_candidate_file" accept="image/*" class="form-control mt-2">
            <small class="text-muted">The Profile Picture must be 185px by 230px in size.</small>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" name="update_candidate" class="btn btn-warning">Save Changes</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', ()=>{
  const modal   = new bootstrap.Modal(document.getElementById('updateCandidateModal'));
  const preview = document.getElementById('upd_preview');
  const placeholder = document.getElementById('upd_placeholder');
  const removeBtn   = document.getElementById('upd_remove');
  const fileInput   = document.getElementById('upd_file');
  const croppedInput= document.getElementById('upd_cropped_image');
  const dropbox     = document.getElementById('upd_dropbox');

  /* populate modal */
  document.querySelectorAll('.btn-update').forEach(btn=>{
    btn.addEventListener('click', ()=>{
      const data = JSON.parse(btn.dataset.cand);
      document.getElementById('upd_candidate_id').value = data.candidate_id;
      document.getElementById('upd_user_id').textContent = data.user_id;
      document.getElementById('upd_full_name').textContent = data.full_name;
      document.getElementById('upd_year').textContent = data.year_level;
      document.getElementById('upd_course').textContent = data.course_name;
      document.getElementById('upd_college').textContent = data.college_name;
      document.getElementById('upd_position_id').value = data.position_id;
      document.getElementById('upd_platforms').value = data.platforms;
      document.getElementById('upd_credentials').value = data.credentials;

      if (data.profile_picture && data.profile_picture.trim() !== '') {
        preview.src = data.profile_picture;
        preview.style.display = 'block';
        placeholder.style.display = 'none';
        removeBtn.style.display = 'block';
        document.getElementById('existing_photo').value = data.profile_picture;
      } else {
        preview.style.display = 'none';
        placeholder.style.display = 'block';
        removeBtn.style.display = 'none';
        document.getElementById('existing_photo').value = '';
      }
      croppedInput.value = '';
      modal.show();
    });
  });

  /* remove */
  removeBtn.addEventListener('click', ()=>{
    preview.src = ''; preview.style.display = 'none';
    placeholder.style.display = 'block'; removeBtn.style.display = 'none';
    document.getElementById('existing_photo').value = '';
    fileInput.value = ''; croppedInput.value = '';
  });

  /* preview + base64 */
  function showPreview(file){
    if(!file||!file.type.startsWith('image/')) return;
    const reader=new FileReader();
    reader.onload=e=>{
      const base64=e.target.result;
      preview.src=base64; preview.style.display='block';
      preview.style.width='100%'; preview.style.height='100%';
      preview.style.objectFit='cover'; placeholder.style.display='none';
      removeBtn.style.display='block'; croppedInput.value=base64;
    };
    reader.readAsDataURL(file);
  }

  /* click */
  dropbox.addEventListener('click',()=>fileInput.click());

  /* drag-drop */
  dropbox.addEventListener('dragover',e=>{e.preventDefault();dropbox.classList.add('dragover');});
  dropbox.addEventListener('dragleave',()=>dropbox.classList.remove('dragover'));
  dropbox.addEventListener('drop',e=>{e.preventDefault();dropbox.classList.remove('dragover');const f=e.dataTransfer.files[0]; if(f)showPreview(f);});

  /* file input */
  fileInput.addEventListener('change',()=>{const f=fileInput.files[0]; if(f)showPreview(f);});
});
</script>

</body>
</html>