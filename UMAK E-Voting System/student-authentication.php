<?php
session_start();

// Preserve phase from login
if (!isset($_SESSION['current_phase'])) {
    $_SESSION['current_phase'] = null;
}

// Database connection
$host = "localhost";
$user = "u803144294_system";
$pass = "3AINS-G7_db";
$db   = "u803144294_system";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Make sure the user is logged in
if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user details
$stmt = $conn->prepare("SELECT role FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
  header("Location: login.php");
  exit();
}

$role = strtoupper($user['role']);

// Prevent reloading the scanner page: allow only the first GET after login.
// Subsequent GET (reload) will redirect back to login.
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  if (!isset($_SESSION['scan_access'])) {
    $_SESSION['scan_access'] = true; // first-time access allowed
  } else {
    // reload detected — clear flag and force re-login
    unset($_SESSION['scan_access']);
    header("Location: login.php");
    exit();
  }
}

// Handle QR verification (AJAX request)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['qr_text'])) {
  $qr_text = trim($_POST['qr_text']);

  // Expected QR format:
  // STUDENT: QR|25|ENROLLED
  // ADMIN:   QR|1|ADMIN
  $parts = explode("|", $qr_text);
  if (count($parts) !== 3 || strtoupper($parts[0]) !== "QR") {
    echo "INVALID_FORMAT";
    exit();
  }

  $qr_user_id = trim($parts[1]);
  $qr_status = strtoupper(trim($parts[2]));

  // Must match logged-in user's ID
  if ($qr_user_id != $user_id) {
    echo "MISMATCH";
    exit();
  }

  // If logged in as ADMIN
  if ($role === "ADMIN") {
    if ($qr_status === "ADMIN") {
      $_SESSION['role'] = $role;        // ✅ ENSURE ROLE IS IN SESSION
      unset($_SESSION['scan_access']);
      echo "ADMIN_OK";
      exit();
    } else {
      echo "ROLE_MISMATCH";
      exit();
    }
  }

  // If logged in as STUDENT
  if ($role === "STUDENT") {
    if ($qr_status === "ENROLLED") {
      $_SESSION['role'] = $role;        // ✅ ENSURE ROLE IS IN SESSION
      unset($_SESSION['scan_access']);
      echo "OK";
      exit();
    } else {
      echo "NOT_ENROLLED";
      exit();
    }
  }

  // Unknown role
  echo "INVALID_ROLE";
  exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Student Authentication</title>

  <!-- Bootstrap 5.3 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <!-- QR Code Scanner -->
  <script src="https://unpkg.com/html5-qrcode"></script>

  <!-- External CSS -->
  <link rel="stylesheet" href="auth-style12.css">
</head>

<body>
  <div class="background-overlay"></div>

  <!-- HEADER -->
  <nav class="navbar navbar-expand-lg custom-navbar">
    <div class="container-fluid d-flex align-items-center">
      <div class="d-flex align-items-center">
        <img src="sc.png" alt="Logo 1" class="img-fluid me-2 rounded-circle" style="width: 60px;">
        <img src="UMAK.png" alt="Logo 2" class="img-fluid" style="width: 75px;">
      </div>
    </div>
  </nav>

  <div class="auth-section">
    <!-- Left: QR Code Scanner -->
    <div>
      <h4 class="instruction mb-3">Student ID QR Code Scanner</h4>

      <div class="scanner-box mb-3 position-relative">
        <div id="reader"></div>
        <div class="scan-line"></div>
      </div>

      <div id="alert-container" class="mt-2"></div>

      <p class="note">(Scan your Student QR Code ID/COR here)</p>
    </div>

    <!-- Right: Instructions -->
    <div>
      <h4 class="instruction mb-3">Instructions</h4>
      <ul class="list-unstyled">
        <li>• ONLY enrolled students this semester will be eligible to vote for this election.</li>
        <li>• Make sure that you have a stable internet.</li>
        <li>• Make sure you are in a well-lit area while scanning the QR code.</li>
        <li>• Make sure your camera is facing the QR code clearly.</li>
        <li>• Hold your student ID/Certificate of Registration clearly until the system detects it.</li>
        <li>• Once your ID/Certificate of Recognition is recognized, you’ll be redirected automatically.</li>
      </ul>
    </div>
  </div>

  <!-- QR Scanner Script -->
  <script>
    function showAlert(message, type = "danger") {
      const container = document.getElementById("alert-container");
      container.innerHTML = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
          ${message}
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>`;
    }

    function onScanSuccess(decodedText) {
      document.querySelector('.scan-line').style.animation = 'none';

      fetch("student-authentication.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "qr_text=" + encodeURIComponent(decodedText)
      })
      .then(res => res.text())
      .then(response => {
        if (response === "OK") {
          window.location.href = "dashboard.php";
        } else if (response === "ADMIN_OK") {
          window.location.href = "admin_dashboard.php";
        } else if (response === "MISMATCH") {
          showAlert("QR Code does not match the email and password. Kindly make sure you are using your QR CODE from your COR or ID .");
        } else if (response === "ROLE_MISMATCH") {
          showAlert("Access denied! This QR code doesn't match your account role.");
        } else if (response === "NOT_ENROLLED") {
          showAlert("Access denied, You are not currently enrolled this semester.");
        } else if (response === "INVALID_ROLE") {
          showAlert("⚠️ Unknown QR type detected. Please contact the administrator.");
        } else {
          showAlert("Verification failed. Please try again.");
        }
      })
      .catch(() => showAlert("Connection error. Please try again."));
    }

    function onScanError(errorMessage) {
      // silent scan errors
    }

// choose a qrbox that is always smaller than the video
const html5QrCode = new Html5Qrcode("reader");

const boxSize = (window.innerWidth > 768) ? 250          // desktop size
                                         : Math.min(200, Math.floor(window.innerWidth * 0.5));

const config = { fps: 20, qrbox: { width: boxSize, height: boxSize } };

html5QrCode.start({ facingMode: "environment" }, config, onScanSuccess, onScanError)
        .catch(err => showAlert("Camera error: " + err));
  </script>
</body>
</html>