<?php
// add_candidate.php
// Full Add Candidate page: search student, image upload/edit, platforms+credentials, and insert candidate.
// Place this file where your admin iframe can load it.

error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// DB config - adjust if different
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'evoting_system';

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    die("DB Connect Error: " . $conn->connect_error);
}

// NULL PAG WALANG PROVIDED IMAGE
function save_base64_image($dataurl, $targetDir = 'uploads/candidates') {
    if (!$dataurl) return null;
    if (!preg_match('/^data:image\/(\w+);base64,/', $dataurl, $type)) return null;
    $data = substr($dataurl, strpos($dataurl, ',') + 1);
    $data = base64_decode($data);
    if ($data === false) return null;
    $ext = strtolower($type[1]);
    if (!in_array($ext, ['jpg','jpeg','png','gif','webp'])) $ext = 'png';
    if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);
    $filename = uniqid('cand_') . '.' . $ext;
    $filePath = rtrim($targetDir, '/') . '/' . $filename;
    if (file_put_contents($filePath, $data) === false) return null;
    return $filePath;
}

// SEARCH QUERY ITO
$student = null;
$search_error = '';
if (isset($_POST['search'])) {
    $uid = intval($_POST['user_id']);
    if ($uid <= 0) {
        $search_error = "Please enter a valid student ID.";
    } else {
        $sql = "SELECT u.user_id, COALESCE(u.full_name, CONCAT_WS(' ', u.first_name, u.middle_name, u.last_name)) AS full_name, s.year_level, s.is_enrolled, c.course_name, co.college_name
                FROM Users u
                JOIN Students s ON u.user_id = s.user_id
                JOIN Courses c ON s.course_id = c.course_id
                JOIN Colleges co ON c.college_id = co.college_id
                WHERE u.user_id = ? AND u.role = 'STUDENT' LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $uid);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res && $res->num_rows > 0) {
            $student = $res->fetch_assoc();

            // If student is in 4th year, show flash and prevent adding as candidate
            $year = intval($student['year_level']);
            if ($year >= 4) {
                $_SESSION['flash'] = ['message' => 'This student is not eligible to be a candidate.', 'type' => 'danger'];
                $stmt->close();
                header('Location: add_candidate.php');
                exit;
            }

        } else {
            $search_error = "Student not found or not a STUDENT role.";
        }
        $stmt->close();
    }
}

// Query for Student Info
if (!$student && isset($_GET['user_id'])) {
    $uid = intval($_GET['user_id']);
    if ($uid > 0) {
        $sql = "SELECT u.user_id, COALESCE(u.full_name, CONCAT_WS(' ', u.first_name, u.middle_name, u.last_name)) AS full_name, s.year_level, s.is_enrolled, c.course_name, co.college_name
                FROM Users u
                JOIN Students s ON u.user_id = s.user_id
                JOIN Courses c ON s.course_id = c.course_id
                JOIN Colleges co ON c.college_id = co.college_id
                WHERE u.user_id = ? AND u.role = 'STUDENT' LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $uid);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res && $res->num_rows > 0) {
            $student = $res->fetch_assoc();
        }
        
        if ($student) {
    $year = intval($student['year_level']);
    if ($year >= 4) {
        $_SESSION['flash'] = ['message' => 'This student is not eligible to run for this election.', 'type' => 'danger'];
        header('Location: add_candidate.php');
        exit;
    }
}


        $stmt->close();
    }
}

//ADD CANDIDATE QUERY ITO
$add_message = '';
if (isset($_POST['add_candidate'])) {
    $user_id = intval($_POST['user_id'] ?? 0);
    $position_id = intval($_POST['position_id'] ?? 0);
    $platforms = trim($_POST['platforms'] ?? '');
    $credentials = trim($_POST['credentials'] ?? '');
    $final_image_path = null;

    if ($user_id <= 0 || $position_id <= 0) {
        $_SESSION['flash'] = ['message' => 'Invalid student or position.', 'type' => 'danger'];
        header('Location: add_candidate.php');
        exit;
    }

    // CHECK KUNG CANDIDATE NA BA SI USER
    $check = $conn->prepare("SELECT candidate_id FROM Candidates WHERE user_id = ?");
    $check->bind_param('i', $user_id);
    $check->execute();
    $check_result = $check->get_result();
    if ($check_result && $check_result->num_rows > 0) {
        $check->close();
        $_SESSION['flash'] = ['message' => 'This student is already a candidate!', 'type' => 'danger'];
        // Redirect to GET to show flash (avoids re-posting)
        header('Location: add_candidate.php?user_id=' . $user_id);
        exit;
    }
    $check->close();

    // Handle optional image (uploaded file will be processed server-side)
    if (!empty($_POST['cropped_image'])) {
        $saved = save_base64_image($_POST['cropped_image']);
        if ($saved) $final_image_path = $saved;
    } elseif (!empty($_FILES['candidate_file']) && $_FILES['candidate_file']['error'] === UPLOAD_ERR_OK) {
        $tmp = $_FILES['candidate_file']['tmp_name'];
        $orig = basename($_FILES['candidate_file']['name']);
        $ext = pathinfo($orig, PATHINFO_EXTENSION);
        $upDir = 'uploads/candidates';
        if (!is_dir($upDir)) mkdir($upDir, 0755, true);
        $filename = uniqid('cand_') . '.' . $ext;
        $dest = $upDir . '/' . $filename;
        if (move_uploaded_file($tmp, $dest)) $final_image_path = $dest;
    }

    // Insert candidate
    $insert = $conn->prepare("INSERT INTO Candidates (user_id, position_id, platforms, credentials, profile_picture)
                              VALUES (?, ?, ?, ?, ?)");
    $insert->bind_param('iisss', $user_id, $position_id, $platforms, $credentials, $final_image_path);
    if ($insert->execute()) {
        $_SESSION['flash'] = ['message' => 'Candidate added successfully!', 'type' => 'success'];
        $insert->close();
        header('Location: add_candidate.php?user_id=' . $user_id);
        exit;
    } else {
        $_SESSION['flash'] = ['message' => 'Error saving candidate: ' . $insert->error, 'type' => 'danger'];
        $insert->close();
        header('Location: add_candidate.php?user_id=' . $user_id);
        exit;
    }
}


// POSITIONS SA DROPDOWN
$positions = [];
$respos = $conn->query("SELECT position_id, position_name FROM Positions ORDER BY position_id ASC");
if ($respos) {
    while ($r = $respos->fetch_assoc()) $positions[] = $r;
}
?>




<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Add Candidate</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="front.css">


</head>
<body>
<div class="container-fluid p-3">
  <div class="card mb-2">
    <div class="card-body">
      <h3 class="fw-bold mb-4">ADD CANDIDATE</h3>

      <!-- SEARCH FORM -->
      <form method="post" class="row g-2 align-items-center">
        <div class="col-auto" style="flex:1">
          <input type="text" name="user_id" class="form-control" placeholder="Student ID" inputmode="numeric" pattern="\d*" required value="<?= isset($_POST['user_id']) ? htmlspecialchars($_POST['user_id']) : '' ?>">
        </div>
        <div class="col-auto">
          <button class="btn btn-primary" type="submit" name="search">Search</button>
        </div>
        <?php if ($search_error): ?>
          <div class="col-12 mt-2 text-danger"><?= htmlspecialchars($search_error) ?></div>
        <?php endif; ?>
      </form>
    </div>
  </div>

  <?php if (isset($student) && $student): ?>
  <div class="row g-1">
    <div class="col-lg-5">
      <div class="card mt-1">
        <div class="card-body">
          <label class="form-label">Student Information</label>
          <p><strong>Name:</strong> <?= htmlspecialchars($student['full_name']) ?></p>
          <p><strong>Course:</strong> <?= htmlspecialchars($student['course_name']) ?></p>
          <p><strong>College:</strong> <?= htmlspecialchars($student['college_name']) ?></p>
          <p><strong>Year:</strong> <?= htmlspecialchars($student['year_level']) ?></p>
        </div>
      </div>

      <div class="card mt-1">
        <div class="card-body">
          <label class="form-label">Candidate Photo</label>
          <div id="dropbox" class="image-dropbox" tabindex="0">
            <div id="placeholder" class="text-center" style="color:rgba(3,44,99,.7);">
              Drag & Drop image here<br>or click to choose<br><small style="color:#666"></small>
            </div>
            <img id="previewImage" src="" style="display:none; position:absolute; left:0; top:0; width:100%; height:100%; object-fit:cover;">
            <div class="image-controls">
              <button id="removeImage" type="button" class="remove-btn" title="Remove image">&times;</button>
            </div>
            <!-- editing controls removed -->
          </div>
      
          <div class="mt-2 d-flex gap-2">
            <input id="fileInput" name="candidate_file" type="file" accept="image/*" class="form-control form-control-sm">
            <button id="btnLoadFile" type="button" class="btn btn-secondary btn-sm">Load</button>
          </div>
          <small class="text-muted">The Profile Picture must be 185px by 230px in size.</small>
        </div>
      </div>
      <!-- ensure the dropbox is centered in the column -->
      <div class="image-wrap mt-2">
        <!-- dropbox already exists above; move or duplicate if needed -->
      </div>
       
     </div>

    <div class="col-lg-7">
      <div class="card">
        <div class="card-body">
          <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="user_id" value="<?= htmlspecialchars($student['user_id']) ?>">
            <div class="mb-3">
              <label class="form-label">Position</label>
              <select name="position_id" class="form-select" required>
  <option value="">-- Select Position --</option>
  <?php
    $year = intval($student['year_level']);
    foreach ($positions as $p):
        $name = $p['position_name'];
        $hide = false;

        // Year-based restriction logic
        if ($year === 1 && in_array($name, ['3rd Year Representative', '4th Year Representative'])) $hide = true;
        if ($year === 2 && in_array($name, ['2nd Year Representative', '4th Year Representative'])) $hide = true;
        if ($year === 3 && in_array($name, ['2nd Year Representative', '3rd Year Representative'])) $hide = true;

        if ($hide) continue; // skip disallowed positions
  ?>
      <option value="<?= intval($p['position_id']) ?>"><?= htmlspecialchars($name) ?></option>
  <?php endforeach; ?>
</select>
            </div>

            <div class="mb-3">
              <label class="form-label">Platforms</label>
              <textarea name="platforms" class="form-control large" placeholder="Enter candidate platforms..." required></textarea>
            </div>

            <div class="mb-3">
              <label class="form-label">Credentials</label>
              <textarea name="credentials" class="form-control large" placeholder="Enter credentials, awards, experience..."></textarea>
            </div>

            <!-- Hidden field for base64 cropped image -->
            <input type="hidden" name="cropped_image" id="cropped_image">

            <div class="d-flex gap-2">
              <button type="submit" name="add_candidate" class="btn btn-primary">Add Candidate</button>
              <button type="reset" id="btnReset" class="btn btn-secondary">Reset</button>
            </div>
          </form>


 <?php if ($add_message): ?>
<div id="toastContainer" class="position-fixed top-0 end-0 p-3" style="z-index:10800;">
  <div id="liveToast" class="toast align-items-center text-white border-0 <?= (stripos($add_message,'successfully') !== false) ? 'bg-success' : 'bg-danger' ?>"
       role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="3000">
    <div class="d-flex">
      <div class="toast-body fw-semibold px-3 py-2">
        <?= htmlspecialchars($add_message) ?>
      </div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
  </div>
</div>

<script>
 window.addEventListener('load', function() {
  // Build toast dynamically after Bootstrap has loaded
  const msg = <?= json_encode($add_message) ?>;
  const isSuccess = msg.toLowerCase().includes('successfully');
  const container = document.createElement('div');
  container.className = 'position-fixed top-0 end-0 p-3';
  container.style.zIndex = '20000';
  container.innerHTML = `
    <div class="toast align-items-center text-white border-0 show fade ${isSuccess ? 'bg-success' : 'bg-danger'}" role="alert">
      <div class="d-flex">
        <div class="toast-body fw-semibold px-3 py-2">${msg}</div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
      </div>
    </div>`;
  document.body.appendChild(container);

  // Use Bootstrap toast API if available
  const toastEl = container.querySelector('.toast');
  if (window.bootstrap && bootstrap.Toast) {
    const toast = new bootstrap.Toast(toastEl, { delay: 3500 });
    toast.show();
    toastEl.addEventListener('hidden.bs.toast', ()=> container.remove());
  } else {
    // fallback (in case bootstrap not loaded yet)
    toastEl.classList.add('show');
    setTimeout(()=> container.remove(), 4000);
  }
});
</script>
<?php endif; ?>


        </div>
      </div>
    </div>
  </div>
  <?php endif; ?>
</div>






<!-- JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
(function(){
  const dropbox = document.getElementById('dropbox');
  const fileInput = document.getElementById('fileInput');
  const btnLoadFile = document.getElementById('btnLoadFile');
  const previewImg = document.getElementById('previewImage');
  const placeholder = document.getElementById('placeholder');
  const removeBtn = document.getElementById('removeImage');

  if (!dropbox || !fileInput) return;

  function showPlaceholder(show){
    if (placeholder) placeholder.style.display = show ? 'block' : 'none';
  }

  function resetImage(){
    if (previewImg) { previewImg.style.display = 'none'; previewImg.src = ''; }
    fileInput.value = '';
    showPlaceholder(true);
  }

  function loadFile(file){
    if (!file) return;
    const reader = new FileReader();
    reader.onload = function(e){
  if (previewImg) {
    const base64 = e.target.result;
    previewImg.src = base64;
    previewImg.style.display = 'block';
    previewImg.style.width = '100%';
    previewImg.style.height = '100%';
    showPlaceholder(false);

    // ✅ Add this line to send Base64 to PHP
    document.getElementById('cropped_image').value = base64;
  }
};

    reader.readAsDataURL(file);
  }

  // click opens file picker
  dropbox.addEventListener('click', ()=> fileInput.click());

  // drag & drop
  dropbox.addEventListener('dragover', function(e){
    e.preventDefault();
    this.classList.add('dragover');
  });
  dropbox.addEventListener('dragleave', function(e){
    e.preventDefault();
    this.classList.remove('dragover');
  });
  dropbox.addEventListener('drop', function(e){
    e.preventDefault();
    this.classList.remove('dragover');
    const f = e.dataTransfer.files && e.dataTransfer.files[0];
    if (f) loadFile(f);
  });

  // file input change
  btnLoadFile && btnLoadFile.addEventListener('click', ()=> fileInput.click());
  fileInput.addEventListener('change', function(e){
    const f = this.files && this.files[0];
    if (f) loadFile(f);
  });

  // remove button
  removeBtn && removeBtn.addEventListener('click', function(){
    if (confirm('Remove attached image?')) resetImage();
  });
})();
</script>

<?php if (!empty($_SESSION['flash'])): 
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
?>
<div id="flashToastContainer" class="position-fixed top-0 end-0 p-3" style="z-index:20000">
  <div class="toast align-items-center text-white border-0 <?= ($flash['type'] === 'success') ? 'bg-success' : 'bg-danger' ?>" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex">
      <div class="toast-body"><?= htmlspecialchars($flash['message']) ?></div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
  </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function(){
  var el = document.querySelector('#flashToastContainer .toast');
  if (!el) return;
  if (window.bootstrap && bootstrap.Toast) {
    var t = new bootstrap.Toast(el, { delay: 3500 });
    t.show();
    el.addEventListener('hidden.bs.toast', function(){ var c = document.getElementById('flashToastContainer'); if(c) c.remove(); });
  } else {
    el.classList.add('show');
    setTimeout(function(){ var c = document.getElementById('flashToastContainer'); if(c) c.remove(); }, 3500);
  }
});
</script>
<?php endif; ?>
</script>
</body>
</html>
