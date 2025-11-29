<?php
// to  make sure no one can find a loophole and  manage to submit their ballot if currently not in voting phase
require_once __DIR__ . '/dbc.php';   // gives $conn
date_default_timezone_set('Asia/Manila');

function currentPhase($pdo): ?string {
    $now = date('Y-m-d H:i:s');
    $st = $pdo->prepare("SELECT phase
                         FROM schedule
                         WHERE start_datetime <= ? AND end_datetime >= ?
                           AND phase IN ('VOTING','VIEW_CANDIDATES', 'CANDIDATE_CHECKING')
                         LIMIT 1");
    $st->execute([$now, $now]);
    $row = $st->fetch(PDO::FETCH_ASSOC);
    return $row ? $row['phase'] : null;
}

function denyIfViewingOnly($pdo): void {
    if (currentPhase($pdo) === 'VIEW_CANDIDATES') {
        http_response_code(403);
        exit(json_encode(['ok' => false, 'msg' => 'Ballot submission is disabled during candidate viewing.']));
    }
}