    <?php
    ob_start();                 // capture any accidental output
    session_start();
    header('Content-Type: application/json');
    
    require_once 'dbc.php';        // provides $pdo (PDO connection)
    require_once 'phpmailer/Exception.php';
    require_once 'phpmailer/PHPMailer.php';
    require_once 'phpmailer/SMTP.php';
    
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;
    
    /* ----------  CONFIG  ---------- */
    $debug = true;   // true = expose real SQL error to browser
    
    /* ----------  AUTHORISATION  ---------- */
    if (
        !isset($_SESSION['user_id']) ||
        $_SESSION['role'] !== 'STUDENT'
    ) {
        http_response_code(401);
        exit(json_encode(['ok' => false, 'msg' => 'Not authorised']));
    }
    
    $userId = (int) $_SESSION['user_id'];
    
    
    
    require_once __DIR__ . '/schedule_guard.php';
    denyIfViewingOnly($pdo); // ✅ now uses PDO
    
    
    /* ----------  READ JSON BODY  ---------- */
    $payload = json_decode(file_get_contents('php://input'), true);
    $chosen  = array_map('intval', $payload['candidates'] ?? []);
    
    if (!$chosen) {
        exit(json_encode(['ok' => false, 'msg' => 'No candidates selected']));
    }
    
    /* ----------  DB WORK  ---------- */
    try {
        $pdo->beginTransaction();
    
        /* 1.  ballot header (ignore duplicate) */
        $pdo->prepare("INSERT IGNORE INTO ballots (user_id) VALUES (?)")
            ->execute([$userId]);
        $ballotId = (int) $pdo->lastInsertId();
    
        if (!$ballotId) {                       // student already voted
            http_response_code(200);
            exit(json_encode([
                'ok'  => false,
                'msg' => 'You have already submitted your ballot.'
            ]));
        }
    
        /* 2.  individual votes */
        $stmt = $pdo->prepare(
            "INSERT INTO votes (ballot_id, candidate_id) VALUES (?, ?)"
        );
        foreach ($chosen as $cid) {
            $stmt->execute([$ballotId, $cid]);
        }
    
        $pdo->commit();
    
        /* 3.  MAIL RECEIPT  ------------------------------------ */
        $studentEmail = $_SESSION['email'] ?? '';
        $studentName  = $_SESSION['first_name'] . ' ' . $_SESSION['last_name'];
    
        /* build HTML table ONCE – do NOT echo it */
        $html = "<h3>UMak E-Voting – Ballot Receipt</h3>
                 <p>Your ballot was recorded on " . date('Y-m-d H:i:s') . ".</p>
                 <table border='1' cellpadding='6'>
                   <tr><th>Position</th><th>Candidate</th></tr>";
    
        $placeHolders = implode(',', array_fill(0, count($chosen), '?'));
        $sql = "SELECT p.position_name, c.full_name
                FROM candidates cd
                JOIN positions p ON p.position_id = cd.position_id
                JOIN users c ON c.user_id = cd.user_id
                WHERE cd.candidate_id IN ($placeHolders)
                ORDER BY p.position_order";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($chosen);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $html .= "<tr><td>{$row['position_name']}</td><td>{$row['full_name']}</td></tr>";
        }
        $html .= "</table>";
    
        /* send */
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'universityevotesystem@gmail.com';
            $mail->Password   = 'vexz pgsu jqgq xqiu';   // 16-char app password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
    
            $mail->setFrom('universityevotesystem@gmail.com', 'UMak E-Voting');
            $mail->addAddress($studentEmail, $studentName);
            $mail->isHTML(true);
            $mail->Subject = 'Ballot receipt – UMak E-Voting';
            $mail->Body    = $html;
            $mail->AltBody = strip_tags($html);
    
            $mail->send();
            error_log("Receipt sent to $studentEmail");
        } catch (Exception $e) {
            /* mail failed, but vote succeeded – just log it */
            error_log("Mailer error: " . $e->getMessage());
        }
    
        /* 4.  final JSON response  --------------------------------- */
        ob_end_clean();          // discard ANY accidental output
        echo json_encode(['ok' => true, 'msg' => 'Your ballot has been successfully submitted! 🗳️']);
    
    } catch (Throwable $e) {
        $pdo->rollBack();
        $realMsg = $e->getMessage();
        $sendMsg = $debug ? $realMsg : 'Server error';
        http_response_code(500);
        ob_end_clean();
        echo json_encode(['ok' => false, 'msg' => $sendMsg]);
        error_log("submit_vote.php user $userId : $realMsg");
    }
    ?>