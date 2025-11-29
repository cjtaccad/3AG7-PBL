<?php

if (!function_exists('log_action')) {
    function log_action(mysqli $conn, int $user_id, string $action): bool {
        // normalize
        $user_id = intval($user_id);
        $stmt = $conn->prepare("INSERT INTO auditlogs (user_id, action) VALUES (?, ?)");
        if (!$stmt) return false;
        $stmt->bind_param('is', $user_id, $action);
        $ok = $stmt->execute();
        $stmt->close();
        return (bool)$ok;
    }
}