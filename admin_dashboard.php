<?php
session_start();
$current_page = basename($_SERVER['PHP_SELF']);


/*  mandatory check */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'ADMIN') {
    header('Location: login.php');
    exit;
}

?>




<!doctype html>
<html lang="en">


<head>

  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Student Council E-Voting System</title>



<!-- Mga external Libraries-->     
  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- ICONS NI MASTER -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <!-- LINK NG SA CSS FILE -->
  <link rel="stylesheet" href="front.css">
</head>



<body>
<div class="background-overlay"></div>

<div class="app-wrapper d-flex"> <!-- allow full-viewport flex layout -->
  <!-- SIDEBAR -->
  <aside class="sidebar d-flex flex-column gap-3">
     
    <div class="d-flex align-items-center gap-2">
      <div>
        <div class="brand">Student Council E-Voting System</div>
        <div class="big" style="opacity:0.85">Admin Dashboard</div>
      </div>
    </div>



<hr class="sidebar-divider my-0"></hr>

      <!-- PHP LINKS -->
    <nav class="nav flex-column">
      <a class="nav-link" href="#" data-src="Adm_dashboard.php"><i class="fa-solid fa-tachometer-alt me-2"></i> Dashboard</a>
      <a class="nav-link" href="#" data-src="add_candidate.php"><i class="fa-solid fa-user-plus me-2"></i>Add Candidate</a>
      <a class="nav-link" href="#" data-src="view_candidates.php"><i class="fa-solid fa-users me-2"></i>View Candidates</a>
      <a class="nav-link" href="#" data-src="view_results.php"><i class="fa-solid fa-chart-column me-2"></i>View Results</a>

      <div class="mt-3">
        <small style="opacity:0.85">Other Admin Tools</small>
        <a class="nav-link" href="#" data-src="announcement.php"><i class="fa-solid fa-bullhorn me-2"></i>Announcements</a>
        <a class="nav-link" href="#" data-src="schedule.php"><i class="fa-solid fa-calendar-days me-2"></i>Schedule</a>
        <a class="nav-link" href="#" data-src="auditlogs.php"><i class="fa-solid fa-file-lines me-2"></i>Audit Logs</a>
      </div>
    </nav>


       
    <div class="dropup w-100 mt-auto">
      <hr class="sidebar-divider my-0"></hr>
      <button class="btn profile-section w-100 d-flex align-items-center gap-3 dropdown-toggle" 
              id="adminProfile" data-bs-toggle="dropdown" aria-expanded="false">
        <div class="profile-avatar">G7</div>
        <div class="profile-text text-start">
          <div class="name">Paul Admin</div>
          <div class="role">Admin</div>
        </div>
      </button>
      <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="adminProfile">
        <li>
          <a class="dropdown-item d-flex align-items-center gap-2 text-danger"
   href="#"
   onclick="openStep1()">
    <i class="fa-solid fa-arrows-rotate me-2"></i>Reset System
</a>

          <button type="button" class="dropdown-item text-danger" data-bs-toggle="modal" data-bs-target="#logoutModal">
            <i class="fa-solid fa-right-from-bracket me-2"></i> Log out
          </button>
        </li>
      </ul>
    </div>
  </aside>


  


  <!-- NANDITO MAIN PAGE -->
  <div class="flex-grow-1 d-flex flex-column">  <!-- make main area a column so iframe can flex -->

    <!-- TOPBAR -->
    <header class="topbar d-flex align-items-center justify-content-between px-4 py-3 flex-wrap gap-3">
  
  <div class="d-flex align-items-center gap-3">
    <button id="sidebarToggle" class="btn btn-primary d-lg-none">
      <i class="fa fa-bars"></i>
    </button>
  </div>
</header>

<script>
  const date = new Date();
  document.getElementById('currentDate').textContent = date.toLocaleDateString('en-US', { 
    weekday: 'short', 
    year: 'numeric', 
    month: 'short', 
    day: 'numeric' 
  });
</script>

    <!-- content area -->
    <main class="main-content flex-grow-1 p-0" style="min-height: 0;"> <!-- allow flex shrinking -->
      
      <!-- iframe wrapper for PHP pages -->
      <div class="card shadow-sm h-100" style="border: 0; margin: 0;"> <!-- no border/margin -->
        <div class="card-body p-0 d-flex flex-column" style="min-height: 0;"> <!-- allow flex shrinking -->
          <!-- Loading overlay -->
          <div id="loader" class="w-100 text-center py-4">
            <div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div>
          </div>
  
          <iframe id="mainFrame" class="content-frame" src="Adm_dashboard.php" frameborder="0" style="flex: 1 1 auto; min-height: 0; width: 100%;"></iframe>
        </div>
      </div>
  
    </main>
   </div>
</div>

<!-- LOGOUT CONFIRMATION MODAL -->
<div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="logoutModalLabel">
          <i class="fa-solid fa-triangle-exclamation me-2"></i> Confirm Logout
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body text-center">
        <p class="mb-0 fs-6">Are you sure you want to log out of your account?</p>
      </div>
      <div class="modal-footer d-flex justify-content-center">
        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancel</button>
        <form action="logout.php" method="post" class="d-inline">
          <button type="submit" class="btn btn-danger px-4">Log out</button>
        </form>
      </div>
    </div>
  </div>
</div>


<!-- RESET SYSTEM — STEP 1 -->
<div class="modal fade" id="resetStep1" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content shadow-lg">

      <div class="modal-header bg-primary text-white">
    <h5 class="modal-title fw-bold">Student Council E-Voting System Reset</h5>
</div>

      <div class="modal-body">
        <p class="fw-bold text-dark">You are about to reset the E-Voting system. Resetting will delete:</p>

        <ul class="text-dark">
          <li>Candidates encoded in the system</li>
          <li>Results of the previous election</li>
          <li>All submitted student ballots</li>
          <li>Set Schedules</li>
        </ul>

        <p class="fw-bold text-danger">Are you sure you want to continue?</p>
      </div>

      <div class="modal-footer">
    <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
    <button class="btn btn-info text-white" onclick="openStep2()">Proceed</button>
</div>

    </div>
  </div>
</div>

<!-- RESET SYSTEM — STEP 2 -->
<div class="modal fade" id="resetStep2" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content shadow-lg">

      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title fw-bold">Admin System Checklist</h5>
      </div>

      <div class="modal-body">
        <p class="fw-bold text-dark">Before resetting, confirm the following:</p>
        <ul class="text-dark">
          <li>Exported the results of the previous election</li>
          <li>Exported all student ballots</li>
        </ul>

        <p class="fw-bold text-danger">Have you accomplished all the above?</p>
      </div>

      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-warning" onclick="openStep3()">Yes, Continue</button>
      </div>

    </div>
  </div>
</div>

<!-- RESET SYSTEM — STEP 3 (ADMIN AUTH) -->
<div class="modal fade" id="resetStep3" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content shadow-lg">

      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title fw-bold">Admin Verification</h5>
      </div>

      <div class="modal-body">

        <form id="resetAuthForm">
          <label class="fw-bold text-dark">Admin Email</label>
          <input type="email" class="form-control mb-3" id="adminEmail" required>

          <label class="fw-bold text-dark">Password</label>
           <div class="input-group mb-3">
    <input type="password" class="form-control" id="adminPassword" required>
    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword()">
        <i id="toggleIcon" class="fa-solid fa-eye"></i>
    </button>
</div>
        </form>

        <div id="resetError" class="text-danger fw-bold d-none"></div>

      </div>

      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-danger" onclick="submitReset()">Confirm Reset</button>
      </div>

    </div>
  </div>
</div>

<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999">
    <div id="resetToast" class="toast align-items-center text-white bg-success border-0" role="alert">
        <div class="d-flex">
            <div class="toast-body fw-bold">System Reset Successful</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" 
                    data-bs-dismiss="toast"></button>
        </div>
    </div>

    <div id="errorToast" class="toast align-items-center text-white bg-danger border-0" role="alert">
        <div class="d-flex">
            <div id="errorToastMsg" class="toast-body fw-bold">Error</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" 
                    data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>





<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // Sidebar para sa mga naka phone
  const sidebar = document.querySelector('.sidebar');
  const btn = document.getElementById('sidebarToggle');

  // Guard: only attach listeners if elements exist
  if (btn && sidebar) {
    // Stop propagation so document click handler won't immediately close when clicking icon
    btn.addEventListener('click', (e) => {
      e.stopPropagation();
      sidebar.classList.toggle('show');
    });
  }

  // Load links into iframe
  const links = document.querySelectorAll('[data-src]');
  const iframe = document.getElementById('mainFrame');
  const loader = document.getElementById('loader');

  links.forEach(a => a.addEventListener('click', (e) => {
    e.preventDefault();

    // Remove highlight from all links
    links.forEach(link => link.classList.remove('active'));

    // Add highlight to clicked one
    a.classList.add('active');

    // Load the page into the iframe
    const src = a.getAttribute('data-src');
    if (!src) return;
    loader.style.display = 'block';
    iframe.style.display = 'none';
    iframe.src = src;

    iframe.onload = () => {
      loader.style.display = 'none';
      iframe.style.display = 'block';
    };

    // Close sidebar on mobile
    if (window.innerWidth < 992) sidebar.classList.remove('show');
  }));

  // Close sidebar when clicking outside (only on mobile)
  document.addEventListener('click', function(event) {
    // Only run if sidebar is shown and screen is small
    if (window.innerWidth < 992 && sidebar.classList.contains('show')) {
      // If the click is NOT inside the sidebar or the toggle button (or its descendants)
      if (!sidebar.contains(event.target) && !(btn && btn.contains(event.target))) {
        sidebar.classList.remove('show');
      }
    }
  });

function openStep1() {
    new bootstrap.Modal(document.getElementById('resetStep1')).show();
}
function openStep2() {
    bootstrap.Modal.getInstance(document.getElementById('resetStep1')).hide();
    new bootstrap.Modal(document.getElementById('resetStep2')).show();
}
function openStep3() {
    bootstrap.Modal.getInstance(document.getElementById('resetStep2')).hide();
    new bootstrap.Modal(document.getElementById('resetStep3')).show();
}


function submitReset() {
    let email = document.getElementById("adminEmail").value;
    let password = document.getElementById("adminPassword").value;

    fetch("reset_system.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ email, password })
    })
    .then(res => res.json())
    .then(data => {

        if (data.status === "error") {
            document.getElementById("errorToastMsg").innerHTML = data.message;
            new bootstrap.Toast(document.getElementById("errorToast")).show();
            return;
        }

        if (data.status === "success") {
            bootstrap.Modal.getInstance(document.getElementById('resetStep3')).hide();

            setTimeout(() => {
                new bootstrap.Toast(document.getElementById("resetToast")).show();
            }, 300);

            setTimeout(() => location.reload(), 2000);
        }
    })
    .catch(() => {
        document.getElementById("errorToastMsg").innerHTML = "Server error. Try again.";
        new bootstrap.Toast(document.getElementById("errorToast")).show();
    });
}


function togglePassword() {
    const pass = document.getElementById("adminPassword");
    const icon = document.getElementById("toggleIcon");

    if (pass.type === "password") {
        pass.type = "text";
        icon.classList.remove("fa-eye");
        icon.classList.add("fa-eye-slash");
    } else {
        pass.type = "password";
        icon.classList.remove("fa-eye-slash");
        icon.classList.add("fa-eye");
    }
}

// Allow ENTER key to submit reset
document.getElementById("adminPassword").addEventListener("keyup", function(e) {
    if (e.key === "Enter") submitReset();
});


</script>

<!-- PROFILE DROPDOWN (Top right corner) -->






</body>
</html>

