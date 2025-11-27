<?php
session_start();

/* 1.  mandatory check */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'STUDENT') {
    header('Location: login.php');
    exit;
}

/* 2.  fetch year level AND user details from DB (only once per login) */
if (!isset($_SESSION['year_level']) || !isset($_SESSION['full_name']) || !isset($_SESSION['initials'])) {
    $host = "localhost";
    $user = "u803144294_system";
    $pass = "3AINS-G7_db";
    $db   = "u803144294_system";
    $conn = new mysqli($host, $user, $pass, $db);
    if ($conn->connect_error) die("DB error: " . $conn->connect_error);

    $stmt = $conn->prepare("SELECT s.year_level, u.first_name, u.last_name
                           FROM students s
                           JOIN users u ON s.user_id = u.user_id
                           WHERE s.user_id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $stmt->bind_result($yl, $firstName, $lastName);
    $stmt->fetch();

    $_SESSION['year_level'] = (int)$yl;
    $_SESSION['first_name'] = $firstName;
    $_SESSION['last_name']  = $lastName;
    $_SESSION['full_name']  = $firstName . ' ' . $lastName;
    $_SESSION['initials']   = strtoupper(substr($firstName, 0, 1) . substr($lastName, 0, 1));

    $stmt->close();
    $conn->close();
}

/* 3.  NEW: current election phase (VOTING or VIEW_CANDIDATES) */
$currentPhase = $_SESSION['current_phase'] ?? '';
$isViewingOnly = ($currentPhase === 'VIEW_CANDIDATES');

/* 4.  expose for JS */
$yearLevel = $_SESSION['year_level'] ?? 0;
$fullName  = $_SESSION['full_name']  ?? '';
$initials  = $_SESSION['initials']   ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>View Candidates</title>

  <!-- Bootstrap 5.3 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
  <link rel="stylesheet" href="dashboard12.css">

  <!--lock floating ballot while modal open -->
  <style>
  .floating-ballot.disabled {
      pointer-events: none;
      opacity: .5;
  }
  /* NEW: disabled look for Add-to-Ballot buttons */
  .add-ballot-btn.disabled {
      pointer-events: none;
      opacity: .5;
      cursor: not-allowed;
  }
  </style>
</head>

<body>
  <div class="background-overlay"></div>

  <!-- ========= NAVBAR (UPDATED) ========= -->
  <nav class="navbar navbar-expand-lg fixed-top"
       style="background-color:rgba(10,46,92,.85); backdrop-filter:blur(10px); border-bottom:1px solid rgba(255,255,255,.3); padding:.4rem 0;">
    <div class="container-fluid d-flex align-items-center justify-content-between">
      <!-- left logos -->
      <div class="d-flex align-items-center">
        <img src="sc.png" alt="Logo 1" class="img-fluid me-2 rounded-circle" style="width:60px;">
        <img src="UMAK.png" alt="Logo 2" class="img-fluid me-3" style="width:75px;">
        <span class="e-voting-text text-white fw-bold"></span>
      </div>

      <!-- profile dropdown -->
      <div class="dropdown ms-auto d-flex align-items-center">
        <span class="me-2 fw-semibold text-white"><?= htmlspecialchars($_SESSION['full_name'] ?? 'User') ?></span>
        <a class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" href="#"
           id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
          <div class="profile-img monogram-avatar"><?= htmlspecialchars($_SESSION['initials'] ?? 'U') ?></div>
          <i class="bi bi-caret-down-fill ms-1"></i>
        </a>
        <ul class="dropdown-menu dropdown-menu-end shadow-sm mt-2" aria-labelledby="userDropdown">
          <li><a class="dropdown-item logout-item" href="#" onclick="openLogoutModal(); return false;">Log out</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- ========= PAGE TITLE ========= -->
  <div class="title-box">View Candidates</div>

  <!-- ========= EMPTY SKELETONS (JS will fill them) ========= -->
  <div class="candidate-container" data-role="CHAIRPERSON"></div>
  <div class="candidate-container" data-role="VICE CHAIRPERSON"></div>
  <div class="candidate-container" data-role="SECRETARY"></div>
  <div class="candidate-container" data-role="TREASURER"></div>
  <div class="candidate-container" data-role="AUDITOR"></div>
  <div class="candidate-container" data-role="2ND YEAR REPRESENTATIVE"></div>
  <div class="candidate-container" data-role="3RD YEAR REPRESENTATIVE"></div>
  <div class="candidate-container" data-role="4TH YEAR REPRESENTATIVE"></div>

  <!-- ========= FLOATING BALLOT ICON ========= -->
  <div class="floating-ballot" id="ballotIcon">
    <i class="fa-solid fa-file-lines"></i>
  </div>

  <!-- View-Ballot Modal -->
  <div class="modal fade" id="ballotModal" tabindex="-1" aria-labelledby="ballotModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content shadow-lg">
        <div class="modal-header">
          <h4 class="modal-title w-100 text-center fw-bold" id="ballotModalLabel">Your Chosen Candidates</h4>
          <button type="button" class="close-btn" data-bs-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body" id="selectedCandidatesList">
          <p class="text-muted text-center">No candidates selected yet.</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button id="submitVoteBtn" type="button" class="btn btn-success">Submit Vote</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Incomplete ballot warning -->
  <div class="modal fade unvotedModal" id="unvotedModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content p-3" style="border-radius:15px;">
        <div class="modal-header border-0"><h5 class="fw-bold">Incomplete Ballot</h5></div>
        <div class="modal-body">
          <p>You haven't voted for:</p>
          <ul id="missingPositionsList" class="ms-3"></ul>
          <p>Submit anyway?</p>
        </div>
        <div class="modal-footer border-0">
          <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button class="btn btn-danger" id="confirmSubmit">Confirm</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Confirm full ballot -->
  <div class="modal fade confirmVoteModal" id="confirmVoteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
      <div class="modal-content p-3" style="border-radius:15px;">
        <div class="modal-header border-0"><h5 class="fw-bold">Confirm Submission</h5></div>
        <div class="modal-body text-center"><p>Submit your ballot?</p></div>
        <div class="modal-footer border-0 d-flex justify-content-center">
          <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button class="btn btn-success" id="confirmFinalSubmit">Confirm</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Custom alert bubble -->
  <div id="customAlert" class="custom-alert"><p></p></div>

  <!-- Logout modal -->
  <div id="logoutModal" class="logout-modal">
    <div class="logout-modal-content">
      <h3>Confirm Logout</h3>
      <p>Are you sure you want to log out?</p>
      <div class="logout-buttons">
        <button class="cancel-btn" onclick="closeLogoutModal()">Cancel</button>
        <button class="confirm-btn" onclick="confirmLogout()">Logout</button>
      </div>
    </div>
  </div>

  <!-- pass vars to JS -->
  <script>
    const voterYearLevel = <?= (int)$yearLevel ?>;
    const isViewingOnly  = <?= $isViewingOnly ? 'true' : 'false' ?>;
  </script>

<!-- ====================  COURSE / COLLEGE ABBREVIATIONS  ==================== -->
<script>
const courseAbbr = {
    "Bachelor of Science in Computer Science (Application Development Elective Track)": "BSCS-ADT",
    "Bachelor of Science in Information Technology (Information and Network Security Elective Track)": "BSIT-INS",
    "Diploma in Application Development": "Dip-AppDev",
    "Diploma in Computer Network Administration": "Dip-CNA",
    "Bachelor of Science in Accountancy": "BSA",
    "Bachelor of Science in Management Accounting": "BSMA",
    "Bachelor of Science in Entrepreneurial Management": "BSEM",
    "Bachelor of Science in Business Administration Major in Marketing Management": "BSBA-MM",
    "Bachelor of Science in Office Administration": "BSOA",
    "Bachelor of Science in Financial Management": "BSFM",
    "Associate in Building and Property Management": "Assoc-BPM",
    "Associate in Supply Management": "Assoc-SM",
    "Bachelor in Multimedia Arts": "BMA",
    "Associate in Customer Service Communication": "Assoc-CSC",
    "Bachelor of Science in Exercise and Sports Science major in Fitness and Sports Management": "BSESS-FSM",
    "Bachelor of Arts in Political Science major in Paralegal Studies": "AB-PL",
    "Bachelor of Arts in Political Science major in Policy Management": "AB-PM",
    "Bachelor of Arts in Political Science major in Local Government Administration": "AB-LGA",
    "Master of Arts in Nursing": "MAN",
    "Bachelor of Science in Nursing": "BSN",
    "Bachelor of Science in Hospitality Management": "BSHM",
    "Bachelor of Science in Tourism Management": "BSTM",
    "Associate in Hospitality Management": "Assoc-HM"
};
const collegeAbbr = {
    "Institute of Arts and Design": "IAD",
    "College of Computing and Information Sciences": "CCIS",
    "College of Business and Financial Sciences": "CBFS",
    "Institute of Accountancy": "IA",
    "College of Human Kinetics": "CHK",
    "College of Governance and Public Policy": "CGPP",
    "Institute of Nursing": "IN",
    "College of Tourism and Hospitality Management": "CTHM"
};
</script>

<!-- ====================  YEAR-BASED VOTING RULES  ==================== -->
<script>
function getAllowedPositions(voterYear) {
    switch (voterYear) {
        case 1: return new Set(['CHAIRPERSON','VICE CHAIRPERSON','SECRETARY','TREASURER','AUDITOR','2ND YEAR REPRESENTATIVE']);
        case 2: return new Set(['CHAIRPERSON','VICE CHAIRPERSON','SECRETARY','TREASURER','AUDITOR','3RD YEAR REPRESENTATIVE']);
        case 3: return new Set(['CHAIRPERSON','VICE CHAIRPERSON','SECRETARY','TREASURER','AUDITOR','4TH YEAR REPRESENTATIVE']);
        case 4:
        case 5: return new Set(['CHAIRPERSON','VICE CHAIRPERSON','SECRETARY','TREASURER','AUDITOR']);
        default: return new Set();
    }
}
const allowedPositions = getAllowedPositions(voterYearLevel);
</script>

<!-- ====================  FETCH REAL CANDIDATES  ==================== -->
<script>
fetch('candidates.php')
  .then(r => r.json())
  .then(buildCards)
  .catch(err => console.error(err));

function groupByPosition(arr) {
    const map = {};
    arr.forEach(c => {
        const key = c.position_name.toUpperCase();
        if (!map[key]) map[key] = [];
        map[key].push(c);
    });
    return map;
}

/* ----------  BUILD CARDS – SKIP DISALLOWED POSITIONS  ---------- */
function buildCards(data) {
    const byPos = groupByPosition(data);

    /* 1.  shuffle each position */
    Object.keys(byPos).forEach(pos => { byPos[pos] = byPos[pos].sort(() => Math.random() - 0.5); });

    document.querySelectorAll('.candidate-container').forEach(container => {
        const role = container.dataset.role;
        if (!allowedPositions.has(role)) { container.remove(); return; }

        const people = byPos[role] || [];
        if (!people.length) { container.remove(); return; }

        const template = createTemplateCard();
        container.innerHTML = '';
        people.forEach((c, idx) => {
            const card = template.cloneNode(true);
            card.style.display = idx === 0 ? 'flex' : 'none';

            card.querySelector('.candidate-role').textContent = role;

            /* ---- picture ---- */
            const img = card.querySelector('.candidate-photo');
            img.src = c.profile_picture || 'bg.jpg';
            img.onerror = () => { img.src = 'bg.jpg'; };

            /* ---- abbreviate ---- */
            const rawCourse = (c.course_name || '').trim();
            const shortCourse = courseAbbr[rawCourse] || rawCourse;
            const shortCollege = collegeAbbr[c.college_name] || c.college_name;

            card.querySelector('.candidate-details').innerHTML = `
              <div class="row">
                <div class="col">
                  <p><strong>Name:</strong> ${c.full_name}</p>
                  <p><strong>College:</strong> ${shortCollege}</p>
                </div>
                <div class="col right-col">
                  <p><strong>Course:</strong> ${shortCourse}</p>
                  <p><strong>Year:</strong> ${c.year_level}${['st','nd','rd','th'][c.year_level-1]||'th'} year</p>
                </div>
              </div>`;

            const plat = (c.platforms  || '').split(/[;\n]+/).map(t => t.trim()).filter(Boolean);
            const cred = (c.credentials || '').split(/[;\n]+/).map(t => t.trim()).filter(Boolean);

            card.querySelector('.tab-pane.show ul').innerHTML = plat.map(l => `<li>${l}</li>`).join('');
            card.querySelectorAll('.tab-pane')[1].querySelector('ul').innerHTML = cred.map(l => `<li>${l}</li>`).join('');

            const addBtn = card.querySelector('.add-ballot-btn');
            addBtn.dataset.candidateId = c.candidate_id;
            if (isViewingOnly) {
                addBtn.disabled = true;
                addBtn.classList.add('disabled');
                addBtn.title = 'Ballot submission is disabled during candidate viewing.';
            }

            container.appendChild(card);
        });
        attachNav(container);
    });

    /* ----------  DISABLE FLOATING BALLOT ICON  ---------- */
    if (isViewingOnly) {
        const ballotIcon = document.getElementById('ballotIcon');
        ballotIcon.style.opacity = '0.5';
        ballotIcon.style.pointerEvents = 'none';
        ballotIcon.title = 'Ballot viewing is disabled during candidate viewing.';
    }
}

function createTemplateCard() {
  const div = document.createElement('div');
  div.className = 'candidate-card';
  div.innerHTML = `
    <button class="nav-btn prev">&#10094;</button>
    <h3 class="candidate-role"></h3>
    <hr class="role-divider">
    <div class="candidate-info">
      <img class="candidate-photo" src="" alt="Candidate">
      <div class="info-text">
        <div class="candidate-details"></div>
        <div class="info-box">
          <div class="tab-header">
            <button class="tab-btn active">Platforms</button>
            <button class="tab-btn">Credentials</button>
          </div>
          <div class="tab-content">
            <div class="tab-pane show"><ul></ul></div>
            <div class="tab-pane"><ul></ul></div>
          </div>
        </div>
        <button class="add-ballot-btn">Add to Ballot</button>
      </div>
    </div>
    <button class="nav-btn next">&#10095;</button>`;
  return div;
}

function attachNav(container) {
    const cards = container.querySelectorAll('.candidate-card');
    let idx = 0;
    const show = (i, direction) => {
        cards.forEach(card => card.classList.remove('slide-in-next','slide-in-prev','slide-out-next','slide-out-prev'));
        if (cards[idx] && direction) cards[idx].classList.add(direction==='next'?'slide-out-next':'slide-out-prev');
        idx = i;
        setTimeout(() => {
            cards.forEach((c, k) => {
                c.style.display = k === idx ? 'flex' : 'none';
                if (k === idx && direction) c.classList.add(direction==='next'?'slide-in-next':'slide-in-prev');
            });
        }, 250);
    };
    show(0);
    container.querySelectorAll('.next').forEach(b => b.onclick = () => show((idx+1)%cards.length, 'next'));
    container.querySelectorAll('.prev').forEach(b => b.onclick = () => show((idx-1+cards.length)%cards.length, 'prev'));
}
</script>

<!-- ====================  BALLOT LOGIC  ==================== -->
<script>
const ballotIcon = document.getElementById('ballotIcon');
const selectedCandidateIds = [];
const selectedCandidates = window.selectedCandidates || [];

document.addEventListener('click', e => {
    if (!e.target.classList.contains('add-ballot-btn')) return;
    if (isViewingOnly) return; // should never fire, but just in case

    const btn = e.target;
    const newId = parseInt(btn.dataset.candidateId);
    const card = btn.closest('.candidate-card');
    const position = card.querySelector('.candidate-role').textContent.trim().toUpperCase();

    const samePosIds = Array.from(document.querySelectorAll('.candidate-card'))
        .filter(c => c.querySelector('.candidate-role').textContent.trim().toUpperCase() === position)
        .map(c => parseInt(c.querySelector('.add-ballot-btn').dataset.candidateId));

    const alreadyPicked = selectedCandidateIds.find(id => samePosIds.includes(id));
    if (alreadyPicked) {
        showCustomAlertDanger(`You have already chosen a candidate for ${position}. Remove the current one first if you want to change.`);
        return;
    }

    selectedCandidateIds.push(newId);
    flyAnimation(card);
});

function flyAnimation(card) {
    const img = card.querySelector('.candidate-photo');
    const candidateName = card.querySelector('.candidate-details strong')?.textContent || "Unknown Candidate";
    if (!selectedCandidates.includes(candidateName)) selectedCandidates.push(candidateName);

    const clone = img.cloneNode(true);
    const rect = img.getBoundingClientRect();
    const ballotRect = ballotIcon.getBoundingClientRect();

    clone.classList.add('flying-image');
    document.body.appendChild(clone);
    clone.style.left = rect.left + "px";
    clone.style.top  = rect.top + "px";
    clone.style.width  = rect.width + "px";
    clone.style.height = rect.height + "px";
    clone.style.opacity = "1";
    clone.style.position = "fixed";
    clone.style.zIndex = "9999";
    clone.style.borderRadius = "10px";

    setTimeout(() => {
        clone.style.transition = "all 1.5s cubic-bezier(0.25, 1, 0.5, 1)";
        clone.style.left = ballotRect.left + ballotRect.width/4 + "px";
        clone.style.top  = ballotRect.top  + ballotRect.height/4 + "px";
        clone.style.width  = "25px";
        clone.style.height = "25px";
        clone.style.opacity = "0.3";
        clone.style.transform = "rotate(360deg) scale(0.8)";
    }, 20);

    setTimeout(() => {
        ballotIcon.animate([
            { transform: "scale(1)", boxShadow: "0 0 0px rgba(10,46,92,0.5)" },
            { transform: "scale(1.3)", boxShadow: "0 0 15px rgba(10,46,92,0.8)" },
            { transform: "scale(1)", boxShadow: "0 0 0px rgba(10,46,92,0.5)" }
        ], { duration: 400, easing: "ease-out" });
        clone.remove();
    }, 1000);
}

/* ==================== REMOVE BUTTON ANIMATION ==================== */
function setupRemoveButtonAnimation() {
    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove-btn')) {
            const removeBtn = e.target.closest('.remove-btn');
            const candidateBox = removeBtn.closest('.candidate-box');
            const candidateId = removeBtn.dataset.candidateId;
            if (candidateBox && candidateId) animateRemoval(removeBtn, candidateBox, candidateId);
        }
    });
}
function animateRemoval(removeBtn, candidateBox, candidateId) {
    if (removeBtn.classList.contains('removing')) return;
    removeBtn.classList.add('removing');
    createRemovalParticles(removeBtn);

    const id = parseInt(candidateId);
    const idx = selectedCandidateIds.indexOf(id);
    if (idx > -1) selectedCandidateIds.splice(idx, 1);

    const candidateName = candidateBox.querySelector('h4')?.nextElementSibling?.textContent.replace('Name: ','').trim();
    if (candidateName) {
        const nameIdx = selectedCandidates.indexOf(candidateName);
        if (nameIdx > -1) selectedCandidates.splice(nameIdx, 1);
    }

    candidateBox.classList.add('removing');
    setTimeout(() => {
        candidateBox.remove();
        const listBox = document.getElementById('selectedCandidatesList');
        if (listBox.querySelectorAll('.candidate-box').length === 0) {
            listBox.innerHTML = '<p class="text-muted text-center">No candidates selected yet.</p>';
        }
    }, 400);
}
function createRemovalParticles(removeBtn) {
    const rect = removeBtn.getBoundingClientRect();
    const centerX = rect.left + rect.width/2;
    const centerY = rect.top  + rect.height/2;
    const colors = ['#dc3545','#ff6b7a','#ff4757','#ff3838'];
    for (let i=0;i<6;i++){
        const p = document.createElement('div');
        p.className = 'removal-particle';
        p.style.background = colors[Math.floor(Math.random()*colors.length)];
        p.style.left = centerX + 'px';
        p.style.top  = centerY + 'px';
        document.body.appendChild(p);
        const angle = (Math.PI*2*i)/6, dist = 15+Math.random()*25, dur = 400+Math.random()*200;
        setTimeout(()=>{
            p.style.transition = `all ${dur}ms cubic-bezier(0.19,1,0.22,1)`;
            p.style.left = centerX + Math.cos(angle)*dist + 'px';
            p.style.top  = centerY + Math.sin(angle)*dist + 'px';
            p.style.opacity = '0'; p.style.transform = 'scale(0.3)';
        },10);
        setTimeout(()=> p.remove(), dur+100);
    }
}
setupRemoveButtonAnimation();

/* ----------  show ballot modal  ---------- */
ballotIcon.addEventListener('click', () => {
    if (ballotIcon.classList.contains('disabled')) return;
    ballotIcon.classList.add('disabled');

    const listBox = document.getElementById('selectedCandidatesList');
    const chosen = Array.from(document.querySelectorAll('.add-ballot-btn'))
                        .filter(btn => selectedCandidateIds.includes(+btn.dataset.candidateId));
    const toId = str => str.toLowerCase().replace(/\s+/g,'');

    listBox.innerHTML = chosen.length
        ? chosen.map(btn => {
              const card   = btn.closest('.candidate-card');
              const role   = card.querySelector('.candidate-role').textContent.trim();
              const photo  = card.querySelector('.candidate-photo').src;
              const details = card.querySelector('.candidate-details');
              const info = details.innerHTML;
              return `
                <div class="candidate-box" id="${toId(role)}">
                  <div class="candidate-info">
                    <div class="candidate-photo"><img src="${photo}" alt=""></div>
                    <div>
                      <h4 class="mb-4">${role}</h4>
                      ${info}
                    </div>
                  </div>
                  <button class="remove-btn"
                          data-candidate-id="${btn.dataset.candidateId}">
                    <i class="bi bi-trash"></i>
                  </button>
                </div>`;
          }).join('')
        : '<p class="text-muted text-center">No candidates selected yet.</p>';

    const modal = new bootstrap.Modal(document.getElementById('ballotModal'));
    modal.show();
    document.getElementById('ballotModal')
            .addEventListener('hidden.bs.modal', () => ballotIcon.classList.remove('disabled'), {once:true});
});

/* ----------  submit vote  ---------- */
document.getElementById('submitVoteBtn').addEventListener('click', () => {
    const allPos  = ['CHAIRPERSON','VICE CHAIRPERSON','SECRETARY','TREASURER','AUDITOR',
                     '2ND YEAR REPRESENTATIVE','3RD YEAR REPRESENTATIVE','4TH YEAR REPRESENTATIVE'];
    const idToPos = {};
    document.querySelectorAll('.candidate-card').forEach(card => {
        const cid = card.querySelector('.add-ballot-btn')?.dataset.candidateId;
        const pos = card.querySelector('.candidate-role')?.textContent.trim();
        if (cid && pos) idToPos[cid] = pos;
    });
    const votedPos = new Set(selectedCandidateIds.map(id => idToPos[id]).filter(Boolean));
    const missing  = Array.from(allowedPositions).filter(p => !votedPos.has(p));

    if (missing.length) {
        const list = document.getElementById('missingPositionsList');
        list.innerHTML = missing.map(p => `<li>${p}</li>`).join('');
        new bootstrap.Modal(document.getElementById('unvotedModal')).show();
    } else {
        new bootstrap.Modal(document.getElementById('confirmVoteModal')).show();
    }
});

/* ----------  confirm handlers  ---------- */
document.getElementById('confirmSubmit').addEventListener('click', () => {
    bootstrap.Modal.getInstance(document.getElementById('unvotedModal'))?.hide();
    sendBallot();
});
document.getElementById('confirmFinalSubmit').addEventListener('click', () => {
    bootstrap.Modal.getInstance(document.getElementById('confirmVoteModal'))?.hide();
    sendBallot();
});

async function sendBallot() {
    const idToPos = {};
    document.querySelectorAll('.candidate-card').forEach(card => {
        const cid = card.querySelector('.add-ballot-btn')?.dataset.candidateId;
        const pos = card.querySelector('.candidate-role')?.textContent.trim();
        if (cid && pos) idToPos[cid] = pos;
    });
    const filteredIds = selectedCandidateIds.filter(id => {
        const pos = idToPos[id];
        return pos && allowedPositions.has(pos);
    });
    const votedPos = new Set(filteredIds.map(id => idToPos[id]).filter(Boolean));
    const missing  = Array.from(allowedPositions).filter(p => !votedPos.has(p));

    document.querySelectorAll('.modal').forEach(el => bootstrap.Modal.getInstance(el)?.hide());

    try {
        const res = await fetch('submit_vote.php', {
            method : 'POST',
            headers: {'Content-Type':'application/json'},
            body   : JSON.stringify({candidates: filteredIds})
        });
        if (!res.ok) throw new Error('Server returned ' + res.status);
        const data = await res.json();
        const msg = data.ok
            ? (missing.length
                ? 'Your ballot has been submitted with missing positions!'
                : 'Your ballot has been successfully submitted! 🗳️')
            : data.msg;
        showCustomAlert(msg);
        if (data.ok) selectedCandidateIds.length = 0;
    } catch (err) {
        console.error(err);
        showCustomAlertDanger('Error submitting vote. Please try again.');
    }
}

/* ----------  custom alerts  ---------- */
function showCustomAlert(msg) {
    const box = document.getElementById('customAlert');
    box.querySelector('p').textContent = msg;
    box.classList.remove('show', 'danger');
    void box.offsetWidth;
    box.classList.add('show');
    setTimeout(() => box.classList.remove('show'), 3000);
}
function showCustomAlertDanger(msg) {
    const box = document.getElementById('customAlert');
    box.querySelector('p').textContent = msg;
    box.classList.remove('show');
    void box.offsetWidth;
    box.classList.add('show', 'danger');
    setTimeout(() => box.classList.remove('show', 'danger'), 3500);
}

/* ----------  logout  ---------- */
function openLogoutModal()  { document.getElementById('logoutModal').style.display = 'flex'; }
function closeLogoutModal() { document.getElementById('logoutModal').style.display = 'none'; }
function confirmLogout()    { window.location.href = 'login.php'; }

/* ----------  card tab switch  ---------- */
document.addEventListener('click', e => {
    if (!e.target.classList.contains('tab-btn')) return;
    const box  = e.target.closest('.info-box');
    box.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    box.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('show'));
    e.target.classList.add('active');
    const idx = [...e.target.parentNode.children].indexOf(e.target);
    box.querySelectorAll('.tab-pane')[idx].classList.add('show');
    setTimeout(() => checkTabScrollbars(), 50);
});

// ============ AUTO-SCROLLBAR CODE ============
function checkTabScrollbars() {
    document.querySelectorAll('.tab-content').forEach(tabContent => {
        const isOverflowing = tabContent.scrollHeight > tabContent.clientHeight + 5;
        if (isOverflowing) tabContent.classList.remove('no-scroll');
        else tabContent.classList.add('no-scroll');
    });
}
function initScrollbarCheck() {
    setTimeout(checkTabScrollbars, 100);
    setTimeout(checkTabScrollbars, 500);
    setTimeout(checkTabScrollbars, 1000);
}
document.addEventListener('DOMContentLoaded', function() {
    initScrollbarCheck();
    const originalBuildCards = buildCards;
    buildCards = function(data) {
        originalBuildCards(data);
        setTimeout(checkTabScrollbars, 300);
    };
});
window.addEventListener('resize', checkTabScrollbars);
</script>
</body>
</html>