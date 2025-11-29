<?php
// filepath: c:\xampp\htdocs\evoting_system\save_note.php
ob_start();
session_start();

$host = "localhost";
$user = "u803144294_system";  
$pass = "3AINS-G7_db"; 
$db   = "u803144294_system";
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    ob_end_clean();
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'DB connection failed']);
    exit;
}

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "ADMIN") {
    ob_end_clean();
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/audit_helper.php';

$note = $_POST['note'] ?? '';
$admin_user_id = $_SESSION["user_id"];

// Check if a note already exists for this admin (use correct column name: note_id)
$result = $conn->query("SELECT note_id FROM adminnotes WHERE admin_user_id = $admin_user_id");

ob_end_clean();
header('Content-Type: application/json');

if ($result && $result->num_rows > 0) {
    // UPDATE existing note
    $stmt = $conn->prepare("UPDATE adminnotes SET note_text = ?, updated_at = NOW() WHERE admin_user_id = ?");
    if (!$stmt) {
        echo json_encode(['success' => false, 'error' => $conn->error]);
        $conn->close();
        exit;
    }
    $stmt->bind_param("si", $note, $admin_user_id);
} else {
    // INSERT new note
    $stmt = $conn->prepare("INSERT INTO adminnotes (admin_user_id, note_text) VALUES (?, ?)");
    if (!$stmt) {
        echo json_encode(['success' => false, 'error' => $conn->error]);
        $conn->close();
        exit;
    }
    $stmt->bind_param("is", $admin_user_id, $note);
}

if ($stmt->execute()) {
    log_action($conn, $admin_user_id, "Saved admin note");
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => $stmt->error]);
}

$stmt->close();
$conn->close();
?>
