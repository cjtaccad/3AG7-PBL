<?php
session_start();
header('Content-Type: application/json');

// Database connection
$conn = new mysqli("localhost", "u803144294_system", "3AINS-G7_db", "u803144294_system");

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
    exit;
}

// Get JSON input
$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['email']) || !isset($data['password'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing credentials']);
    exit;
}

$email = $conn->real_escape_string($data['email']);
$password = $data['password'];

// Verify admin credentials
$query = "SELECT u.user_id, u.password_hash, u.role 
          FROM users u 
          WHERE u.email = '$email' AND u.role = 'ADMIN'";

$result = $conn->query($query);

if (!$result || $result->num_rows === 0) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Invalid email or you are not an admin']);
    exit;
}

$user = $result->fetch_assoc();

// Verify password using SHA2 (same as in database)
$password_hash = hash('sha256', $password);

if ($password_hash !== $user['password_hash']) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Invalid password']);
    exit;
}

// Admin verified - proceed with system reset
try {
    $conn->begin_transaction();

    // Delete in correct order (respecting foreign keys)
    $tables_to_clear = [
        'votes',
        'ballots',
        'candidates',
        'schedule',
    
    ];

    foreach ($tables_to_clear as $table) {
        $conn->query("DELETE FROM $table");
        if ($conn->error) {
            throw new Exception("Error deleting from $table: " . $conn->error);
        }
    }

    // Log the reset action
    $admin_id = $user['user_id'];
    $log_query = "INSERT INTO auditlogs (user_id, action, log_timestamp) 
                  VALUES ($admin_id, 'SYSTEM_RESET', NOW())";
    $conn->query($log_query);

    $conn->commit();

    echo json_encode(['status' => 'success', 'message' => 'System reset successful']);

} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Reset failed: ' . $e->getMessage()]);
}

$conn->close();
?>