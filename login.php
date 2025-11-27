<?php
date_default_timezone_set('Asia/Manila');
$now = date('Y-m-d H:i:s');
echo "<!-- PHP now: $now -->";

session_start();
session_regenerate_id(true);
$_SESSION = array();

/* ---------- 1.  DB CONNECT ---------- */
$host = "localhost";
$username = "u803144294_system";
$password = "3AINS-G7_db";
$database = "u803144294_system";

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

/* ---------- 2.  LOGIN HANDLER ---------- */
/* ---------- 2.  LOGIN HANDLER ---------- */
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email    = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    /* 2a.  Normal credential check */
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (hash("sha256", $password) === $user["password_hash"]) {
            /* 2b.  Schedule check — ONLY for students */
            $currentPhase = null;
            if ($user["role"] === 'STUDENT') {
                $now = date('Y-m-d H:i:s');
                $schedCheck = $conn->prepare("SELECT phase
                               FROM schedule
                               WHERE start_datetime <= ? AND end_datetime >= ?
                                 AND phase IN ('VOTING','VIEW_CANDIDATES','CANDIDATE_CHECKING')
                               LIMIT 1");
                $schedCheck->bind_param("ss", $now, $now);
                $schedCheck->execute();
                $schedRes = $schedCheck->get_result();

                if ($schedRes->num_rows === 0) {
                    echo "<script>
                            alert('Login is currently disabled. No active voting or candidate-viewing schedule.');
                          </script>";
                    $schedCheck->close();
                    goto renderPage;
                }
                $scheduleRow  = $schedRes->fetch_assoc();
                $currentPhase = $scheduleRow['phase'];
                $schedCheck->close();
                
                /* ---- NEW: candidate-only gate ---- */
/* ---- candidate-only gate ---- */
if ($currentPhase === 'CANDIDATE_CHECKING') {
    // 🔍 SANITY CHECK
    error_log("DEBUG: phase=CANDIDATE_CHECKING, userID={$user['user_id']}");

    $chk = $conn->prepare("SELECT 1
                           FROM candidates
                           WHERE user_id = ?
                           LIMIT 1");
    $chk->bind_param('i', $user['user_id']);
    $chk->execute();

    $isCandidate = $chk->get_result()->num_rows > 0;
    // 🔍 SANITY CHECK
    error_log("DEBUG: isCandidate=" . ($isCandidate ? 'yes' : 'no'));

    if (!$isCandidate) {
        echo "<script>
                alert('You are not a candidate. Login is disabled during candidate-checking period.');
              </script>";
        $chk->close();
        // 🔍 SANITY CHECK
        error_log("DEBUG: blocking login – not a candidate");
        goto renderPage;
    }
    $chk->close();
}
                
                
            }

            /* 2c.  Everything good – create session */
            $_SESSION["user_id"]       = $user["user_id"];
            $_SESSION["email"]         = $user["email"];
            $_SESSION["role"]          = $user["role"];
            $_SESSION["current_phase"] = $currentPhase;   // null for admins

            require_once __DIR__ . '/audit_helper.php';
            log_action($conn, $user["user_id"], "Logged in");

            /* 2d.  Redirect by role */
            if (in_array($user["role"], ["ADMIN", "STUDENT"])) {
                header("Location: student-authentication.php");
                exit;
            } else {
                echo "<script>alert('Unknown user role!');</script>";
            }
        } else {
            echo "<script>alert('Incorrect input please use your UMaK Gmail and password.');</script>";
        }
    } else {
        echo "<script>alert('Incorrect input please use your UMaK Gmail and password.');</script>";
    }

    $stmt->close();
}

renderPage:    // fall-through label when login blocked or failed
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Voting System Login</title>

  <!-- Bootstrap 5.3 CDN -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <!-- External CSS file -->
  <link rel="stylesheet" href="login.css">
</head>

<body class="bg-light">
  <div class="background-overlay"></div>

  <!-- HEADER START -->
  <nav class="navbar navbar-expand-lg custom-navbar">
    <div class="container-fluid d-flex align-items-center">
      <div class="d-flex align-items-center">
        <img src="sc.png" alt="Logo 1" class="img-fluid me-2 rounded-circle" style="width: 60px;">
        <img src="UMAK.png" alt="Logo 2" class="img-fluid" style="width: 75px;">
      </div>
      <h2 class="header-title">STUDENT COUNCIL VOTING SYSTEM</h2>
    </div>
  </nav>

  <!-- LOGIN FORM -->
  <div class="login-container">
    <form id="loginForm" method="POST" action="">
      <div class="form-group mb-3">
        <input type="email" name="email" class="form-control" id="email" placeholder="Email" required>
      </div>

      <div class="position-relative mb-3">
        <input 
          type="password" 
          name="password" 
          class="form-control" 
          id="password" 
          placeholder="Password"
          required
        >
        <i class="toggle-password bi bi-eye-slash" id="togglePassword"></i>
      </div>

      <p class="text-center mb-4" style="font-size: 19px; font-weight: 400; color: #050605;">
        (Use UMak account only)
      </p>

      <div class="text-center">
        <button type="submit" class="btn btn-primary"
          style="border-radius: 30px; padding: 12px 30px; font-size: 18px;">
          Login
        </button>
      </div>
    </form>
  </div>

  <!-- Toast Notification -->
  <div class="position-fixed" style="top: 105px; left: 50%; transform: translateX(-50%); z-index: 9999;">
    <div id="notEnrolledToast" class="toast align-items-center text-bg-danger border-0" role="alert">
      <div class="d-flex">
        <div class="toast-body">
          You are not currently enrolled.
        </div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
      </div>
    </div>
  </div>

  <!-- Script -->
  <script>
    // Show toast if redirected with ?error=not_enrolled
    const params = new URLSearchParams(window.location.search);
    const error = params.get("error");
    if (error === "not_enrolled") {
      const toastEl = document.getElementById('notEnrolledToast');
      const toast = new bootstrap.Toast(toastEl);
      toast.show();
    }

    // Toggle password visibility
    const togglePassword = document.querySelector("#togglePassword");
    const password = document.querySelector("#password");

    togglePassword.addEventListener("click", function () {
      const type = password.getAttribute("type") === "password" ? "text" : "password";
      password.setAttribute("type", type);
      this.classList.toggle("bi-eye");
      this.classList.toggle("bi-eye-slash");
    });
  </script>

</body>
</html>