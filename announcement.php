<?php
/* ----------------------------------------------------------
   Election Announcement – multi-admin safe
   Dynamic created_by taken from $_SESSION['user_id']
   ---------------------------------------------------------- */
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

/* ---------- 1.  AUTH CHECK ---------- */
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'ADMIN') {
    $_SESSION['announcement_flash'] = ['message' => 'Administrators only.', 'type' => 'danger'];
    header('Location: announcement.php');
    exit;
}
$createdBy = (int)$_SESSION['user_id'];   // ← dynamic, never hard-coded again

/* ---------- 2.  DB ---------- */
$host = "localhost";
$user = "u803144294_system";  
$pass = "3AINS-G7_db"; 
$db   = "u803144294_system";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die('DB Connect Error: ' . $conn->connect_error);
}

/* ---------- 3.  PHP MAILER ---------- */
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/Exception.php';
require 'phpmailer/PHPMailer.php';
require 'phpmailer/SMTP.php';

/* ---------- 4.  CONSTANTS ---------- */
$SENDER_EMAIL  =  'universityevotesystem@gmail.com';
$SENDER_NAME   = 'UMak E-Voting';
$SMTP_HOST     = 'smtp.gmail.com';
$SMTP_PORT     = 587;
$SMTP_USERNAME =  'universityevotesystem@gmail.com';
$SMTP_PASSWORD =  'vexz pgsu jqgq xqiu';
$MAX_FILE_SIZE_BYTES = 25 * 1024 * 1024;         // 25 MB
$MAX_FILES           = 10;
$UPLOAD_DIR          = __DIR__ . '/uploads/announcements';
$ELECTION_PORTAL_URL = 'https://umakevoting.online/login.php';

/* ---------- 5.  HELPERS ---------- */
function flash($msg, $type = 'success') {
    $_SESSION['announcement_flash'] = ['message' => $msg, 'type' => $type];
}
function get_flash() {
    if (!empty($_SESSION['announcement_flash'])) {
        $f = $_SESSION['announcement_flash'];
        unset($_SESSION['announcement_flash']);
        return $f;
    }
    return null;
}
$phpmailer_missing = !class_exists('PHPMailer\PHPMailer\PHPMailer');

/* ---------- 6.  FORM HANDLER ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_announcement'])) {
    $to_scope     = $_POST['to_scope'] ?? 'ALL';
    $subject      = trim($_POST['subject'] ?? '');
    $message_html = $_POST['message_html'] ?? '';

/* ---- make text part identical to HTML links ---- */
$message_text = preg_replace(
    '#<a\s+[^>]*href=["\'](.*?)["\'][^>]*>(.*?)</a>#i',
    '$2 ($1)',
    $message_html
);
$message_text = strip_tags($message_text);

    if ($subject === '') {
        flash('Subject is required.', 'danger');
        header('Location: announcement.php');
        exit;
    }
    if ($message_text === '') {
        flash('Message body is required.', 'danger');
        header('Location: announcement.php');
        exit;
    }
    if ($phpmailer_missing) {
        flash('PHPMailer classes not found.', 'danger');
        header('Location: announcement.php');
        exit;
    }

    /* ---- Recipients ---- */
    $recipients = [];
    if ($to_scope === 'CANDIDATES') {
        $q = $conn->query("
            SELECT DISTINCT u.email
            FROM candidates ca
            JOIN users u ON ca.user_id = u.user_id
            WHERE u.email IS NOT NULL AND u.email <> ''
        ");
    } else {
        $q = $conn->query("
            SELECT DISTINCT u.email
            FROM users u
            JOIN students s ON u.user_id = s.user_id
            WHERE u.role = 'STUDENT' AND u.email IS NOT NULL AND u.email <> ''
        ");
    }
    if ($q) {
        while ($r = $q->fetch_assoc()) {
            $recipients[] = $r['email'];
        }
    }

    if (empty($recipients)) {
        flash('No recipients found for the selected target group.', 'danger');
        header('Location: announcement.php');
        exit;
    }

    /* ---- Attachments ---- */
    $saved_files = [];
    if (!is_dir($UPLOAD_DIR)) {
        @mkdir($UPLOAD_DIR, 0755, true);
    }

    if (!empty($_FILES['attachments']['name'][0])) {
        $count = count($_FILES['attachments']['name']);
        if ($count > $MAX_FILES) {
            flash("Too many files. Maximum $MAX_FILES.", 'danger');
            header('Location: announcement.php');
            exit;
        }
        for ($i = 0; $i < $count; $i++) {
            if ($_FILES['attachments']['error'][$i] !== UPLOAD_ERR_OK) {
                continue;
            }
            $size = $_FILES['attachments']['size'][$i];
            if ($size > $MAX_FILE_SIZE_BYTES) {
                flash('One or more files exceed 25 MB.', 'danger');
                header('Location: announcement.php');
                exit;
            }
            $orig = basename($_FILES['attachments']['name'][$i]);
            $ext  = pathinfo($orig, PATHINFO_EXTENSION);
            $safe = uniqid('ann_') . ($ext ? ".$ext" : '');
            $dest = $UPLOAD_DIR . '/' . $safe;
            if (move_uploaded_file($_FILES['attachments']['tmp_name'][$i], $dest)) {
                $saved_files[] = ['orig' => $orig, 'path' => $dest];
            }
        }
    }

    /* ---- Send mail ---- */
    $mail = new PHPMailer(true);  
    try {
        $mail->isSMTP();

        $mail->AllowEmpty = true;          // suppress “no MX” warnings
$mail->Validator  = static function () { return true; }; // accept any href
        $mail->AllowEmpty = true;          // suppress “no MX” warnings
$mail->Validator  = static function () { return true; }; // accept any href

        $mail->Host       = $SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = $SMTP_USERNAME;
        $mail->Password   = $SMTP_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = $SMTP_PORT;

        $mail->setFrom($SENDER_EMAIL, $SENDER_NAME);
        foreach ($recipients as $r) {
            $mail->addBCC($r);
        }
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $message_html;
        $mail->AltBody = $message_text;

        foreach ($saved_files as $f) {
            $mail->addAttachment($f['path'], $f['orig']);
        }
        try {
    $mail->send();
} catch (Exception $e) {
    /* ---- add this line for permanent trace ---- */
    error_log(__FILE__ . ' :: PHPMailer error: ' . $e->errorMessage());
    flash('Mail error: ' . $e->getMessage(), 'danger');
    header('Location: announcement.php');
    exit;
}

        /* ---- DB record ---- */
        $conn->begin_transaction();
        $ins = $conn->prepare("INSERT INTO announcements (title, message, created_by) VALUES (?, ?, ?)");
        $ins->bind_param('ssi', $subject, $message_html, $createdBy); // ← dynamic admin
        $ins->execute();
        $annId = $ins->insert_id;
        $ins->close();

        if ($saved_files) {
            $stmt = $conn->prepare("INSERT INTO announcementfiles (announcement_id, file_name, file_path) VALUES (?, ?, ?)");
            foreach ($saved_files as $f) {
                $stmt->bind_param('iss', $annId, $f['orig'], $f['path']);
                $stmt->execute();
            }
            $stmt->close();
        }
        $conn->commit();

        flash('Announcement sent to ' . count($recipients) . ' recipient(s).', 'success');
        header('Location: announcement.php');
        exit;
    } catch (Exception $e) {
        flash('Mail error: ' . $e->getMessage(), 'danger');
        header('Location: announcement.php');
        exit;
    }
}

$flash = get_flash();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Election Announcement</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <style>
    :root{--yellow:#f5d000; }
    body{background:#f8f9fb;color:var(--blue);}
    .card{border-radius:12px;}
    .attach-box{min-height:250px;border:2px dashed rgba(3,44,99,0.08);background:#fff;padding:12px;border-radius:8px;}
    .file-row{display:flex;justify-content:space-between;align-items:center;gap:8px;padding:6px 8px;border-radius:6px;background:#f4f7fb;margin-bottom:6px;}
    .btn-yellow{background:var(--yellow);color:var(--blue);border:none;}
    .label-inline{position:relative;}
    .label-inline label{position:absolute;left:12px;top:-10px;background:white;padding:0 6px;font-weight:600;color:var(--blue);}
    #message_html{width:100%;min-height:320px;font-family:monospace;}
  </style>
</head>
<body>
<div class="container-fluid p-3">
  

  <?php if ($flash): ?>
    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:20000">
      <div class="toast align-items-center text-white border-0 <?= $flash['type']==='success' ? 'bg-success' : 'bg-danger' ?> show">
        <div class="d-flex">
          <div class="toast-body"><?= htmlspecialchars($flash['message']) ?></div>
          <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <div class="row gx-3">
    
    <div class="col-lg-12">
      
      <div class="card mb-3 p-3">
        <h3 class="fw-bold mb-3">ELECTION ANNOUNCEMENT</h3>
        <form id="announceForm" method="post" enctype="multipart/form-data">
          <div class="row g-3 align-items-center mb-4">
            <div class="col-md-5">
              <div class="label-inline">
                <label>From</label>
                <input class="form-control" type="text" readonly value="<?= htmlspecialchars($SENDER_EMAIL) ?>">
              </div>
            </div>
            <div class="col-md-4">
              <div class="label-inline">
                <label>To</label>
                <select name="to_scope" class="form-select">
                  <option value="ALL">All University Students</option>
                  <option value="CANDIDATES">Candidates Only</option>
                </select>
              </div>
            </div>
            <div class="col-md-3 d-flex gap-2 justify-content-end">
              <button type="button" id="btnClearAll" class="btn btn-secondary">Clear All</button>
              <button type="button" id="btnInsertURL" class="btn btn-primary">Insert URL</button>
            </div>
          </div>

          <div class="row g-3 align-items-start">
            <div class="col-md-8">
              <div class="mb-2 label-inline">
                <label class="form-label">Subject</label>
                <input type="text" name="subject" id="subject" class="form-control" placeholder="Announcement subject...">
              </div>

              <div class="mb-2">
                <label class="form-label"><Strong>Compose Message</Strong></label>
                <div class="btn-group btn-group-sm mb-1" role="group">
                  <button type="button" class="btn btn-outline-secondary" onclick="format('bold')" title="Bold"><i class="bi bi-type-bold"></i></button>
                  <button type="button" class="btn btn-outline-secondary" onclick="format('italic')" title="Italic"><i class="bi bi-type-italic"></i></button>
                  <button type="button" class="btn btn-outline-secondary" onclick="format('underline')" title="Underline"><i class="bi bi-type-underline"></i></button>
                  <button type="button" class="btn btn-outline-secondary" onclick="insertLink()" title="Insert / edit link"><i class="bi bi-link-45deg"></i></button>
                  <button type="button" class="btn btn-outline-secondary" onclick="format('undo')" title="Undo"><i class="bi bi-arrow-counterclockwise"></i></button>
                  <button type="button" class="btn btn-outline-secondary" onclick="format('redo')" title="Redo"><i class="bi bi-arrow-clockwise"></i></button>
                </div>
                <div id="messageDiv" contenteditable="true" class="form-control" style="min-height:320px; max-height:600px; overflow:auto;"></div>
                <textarea id="message_html" name="message_html" style="display:none;"></textarea>
              </div>

              <div class="text-center mt-2">
                <button type="button" id="openConfirm" class="btn btn-yellow">SEND ANNOUNCEMENT</button>
              </div>
            </div>

            <div class="col-md-4">
              <div class="attach-box label-inline">
                <div class="d-flex justify-content-between align-items-center mb-2">
                  <label>Attachments</label>
                  <div>
                    
                    <div class="small text-muted">Max 25 MB per file</div></div>
                  <div><small class="text-muted">Max <?= $MAX_FILES ?> files</small></div>
                </div>
                <div id="dropArea" class="mb-2" style="min-height:120px; display:flex; flex-direction:column; gap:6px;">
                  <div id="dropHint" class="text-center text-muted" style="padding:12px; border-radius:6px;">Drag & drop files here or click 'Choose files'</div>
                  <div id="fileList"></div>
                </div>
                <div class="mt-2">
                  <input type="file" id="attachments" name="attachments[]" multiple class="form-control">
                </div>
              </div>
            </div>
          </div>

          <input type="hidden" name="send_announcement" value="1">
        </form>
      </div>
    </div>

    
  </div>
</div>

<!-- Confirm Modal -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Confirm Send</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p>Are you sure you want to send this announcement?</p>
        <p><strong>To:</strong> <span id="c_to"></span></p>
        <p><strong>Subject:</strong> <span id="c_subject"></span></p>
        <p><strong>Attachments:</strong> <span id="c_attach_count">0</span></p>
        <div class="small text-muted"><em>The announcement will be sent via the configured university Gmail account.</em></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
        <button id="confirmSendBtn" type="button" class="btn btn-primary">Confirm Send</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
/* ===== file-upload & UI helpers ===== */
const dropArea   = document.getElementById('dropArea');
const fileInput  = document.getElementById('attachments');
const fileList   = document.getElementById('fileList');
const maxFiles   = <?= $MAX_FILES ?>;
const maxBytes   = <?= $MAX_FILE_SIZE_BYTES ?>;
let filesState   = [];

function uid(){return 'f'+Math.random().toString(36).slice(2,9);}
function escapeHtml(s){return s.replaceAll('&','&amp;').replaceAll('<','&lt;').replaceAll('>','&gt;');}
function humanFileSize(bytes){
  const i=Math.floor(Math.log(bytes)/Math.log(1024));
  return (bytes/Math.pow(1024,i)).toFixed(1)*1+' '+['B','KB','MB','GB'][i];
}
function renderFiles(){
  fileList.innerHTML='';
  filesState.forEach(fObj=>{
    const row=document.createElement('div');row.className='file-row';
    row.innerHTML=`<div><strong>${escapeHtml(fObj.file.name)}</strong><div class="small text-muted">${humanFileSize(fObj.file.size)}</div></div>
                   <div><button data-id="${fObj.id}" class="btn btn-sm btn-outline-danger remove-file">Remove</button></div>`;
    fileList.appendChild(row);
  });
  const dt=new DataTransfer(); filesState.forEach(o=>dt.items.add(o.file));
  fileInput.files=dt.files;
}
dropArea.addEventListener('dragover',e=>{e.preventDefault();dropArea.classList.add('dragover');});
dropArea.addEventListener('dragleave',e=>{e.preventDefault();dropArea.classList.remove('dragover');});
dropArea.addEventListener('drop',e=>{
  e.preventDefault(); dropArea.classList.remove('dragover'); handleFiles(e.dataTransfer.files);
});
fileInput.addEventListener('change',e=>handleFiles(e.target.files));
function handleFiles(list){
  if(!list||list.length===0)return;
  const toAdd=Array.from(list);
  if(filesState.length+toAdd.length>maxFiles){alert('Too many files. Maximum '+maxFiles);return;}
  for(const f of toAdd){if(f.size>maxBytes){alert('File '+f.name+' exceeds 25MB');continue;}filesState.push({file:f,id:uid()});}
  renderFiles();
}
document.addEventListener('click',e=>{
  if(e.target.matches('.remove-file')){
    const id=e.target.dataset.id; filesState=filesState.filter(x=>x.id!==id); renderFiles();
  }
});
document.getElementById('btnClearAll').addEventListener('click',()=>{
  if(!confirm('Clear Subject, Message and Attachments?'))return;
  document.getElementById('subject').value='';
  document.getElementById('message_html').value='';
  filesState=[]; renderFiles();
});
document.getElementById('btnInsertURL').addEventListener('click',()=>{
  const url=<?= json_encode($ELECTION_PORTAL_URL) ?>;
  const ta=document.getElementById('message_html');
  const link=`Visit the election portal: ${url}\n`;
  ta.setRangeText(link,ta.selectionStart,ta.selectionEnd,'end');
  ta.focus();
});
document.getElementById('openConfirm').addEventListener('click',()=>{
  const to=document.querySelector('select[name="to_scope"]').value;
  const s=document.getElementById('subject').value.trim();
  const attachCount=filesState.length;
  document.getElementById('c_to').textContent=(to==='CANDIDATES')?'Candidates Only':'All University Students';
  document.getElementById('c_subject').textContent=s||'(no subject)';
  document.getElementById('c_attach_count').textContent=attachCount;
  new bootstrap.Modal(document.getElementById('confirmModal')).show();
});
document.getElementById('confirmSendBtn').addEventListener('click',()=>{
  document.getElementById('announceForm').submit();
});
renderFiles();

/* ===== simple rich-edit ===== */
const msger = document.getElementById('messageDiv');
const mirror = document.getElementById('message_html');

function format(cmd, val = null) {
  document.execCommand(cmd, false, val);
  msger.focus();
  syncMirror();
}
function insertLink() {
    let url = prompt('Enter URL (include https://)');
    if (!url) return;

    /* --- basic sanity checks --- */
    url = url.trim();
    if (!/^https?:\/\//i.test(url)) url = 'https://' + url;

    /* --- escape & < > so we never break the HTML --- */
    url = url.replace(/&/g, '&amp;')
             .replace(/</g, '&lt;')
             .replace(/>/g, '&gt;');

    const text = window.getSelection().toString() || url;
    const html = `<a href="${url}" target="_blank" rel="noopener">${text}</a>`;
    format('insertHTML', html);
}
function syncMirror() {
    /* 1.  get raw HTML from editor */
    let dirty = msger.innerHTML;

    /* 2.  allow only the tags/attributes we need */
    const allowed = {
        a: ['href','title','target','rel'],
        b: [], i: [], u: [], br: [], p: [], div: []
    };

    const tmp = document.createElement('div');
    tmp.innerHTML = dirty;

    tmp.querySelectorAll('*').forEach(el => {
        const tag = el.tagName.toLowerCase();
        if (!allowed[tag]) {           // tag not whitelisted → remove it
            el.replaceWith(el.textContent);
            return;
        }
        /* remove any attribute that is not in the whitelist */
        [...el.attributes].forEach(attr => {
            if (!allowed[tag].includes(attr.name.toLowerCase()))
                el.removeAttribute(attr.name);
        });
        /* force external links to open safely */
        if (tag === 'a') {
            el.setAttribute('target','_blank');
            el.setAttribute('rel','noopener');
        }
    });

    /* 3.  hand the clean HTML to the hidden textarea */
    mirror.value = tmp.innerHTML;
}
msger.addEventListener('input', syncMirror);
syncMirror();
</script>
</body>
</html>