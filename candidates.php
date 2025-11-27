<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
// api/candidates.php
header('Content-Type: application/json');

require_once 'dbc.php';          // 1-line PDO connection (see below)

$stmt = $pdo->prepare(
    "SELECT c.candidate_id,
            u.full_name,
            p.position_name,
            co.college_name,
            cs.course_name,
	    s.year_level, 
            c.platforms,
            c.credentials,
            c.profile_picture
     FROM   candidates c
     JOIN   students s  ON s.user_id   = c.user_id
     JOIN   users   u  ON u.user_id   = s.user_id
     JOIN   positions p ON p.position_id = c.position_id
     JOIN   courses cs ON cs.course_id  = s.course_id
     JOIN   colleges co ON co.college_id = cs.college_id
     ORDER  BY p.position_order, u.full_name"
);
$stmt->execute();

echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));