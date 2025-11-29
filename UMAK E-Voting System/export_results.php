<?php
session_start();
require_once __DIR__ . '/audit_helper.php';

// export_results.php - Dompdf version with Total Students Who Voted
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/dompdf/autoload.inc.php';
use Dompdf\Dompdf;
use Dompdf\Options;

// DB connection
$host = "localhost";
$user = "u803144294_system";  
$pass = "3AINS-G7_db"; 
$db   = "u803144294_system";
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("DB Connection failed: " . $conn->connect_error);
}

// Fetch candidates + votes grouped by candidate (by position)
$sql = "
SELECT 
    ca.candidate_id, ca.user_id, ca.position_id,
    u.full_name, s.year_level, co.college_name, p.position_name,
    COUNT(v.vote_id) AS total_votes
FROM candidates ca
JOIN users u ON ca.user_id = u.user_id
JOIN students s ON u.user_id = s.user_id
JOIN courses c ON s.course_id = c.course_id
JOIN colleges co ON c.college_id = co.college_id
JOIN positions p ON ca.position_id = p.position_id
LEFT JOIN votes v ON ca.candidate_id = v.candidate_id
GROUP BY ca.candidate_id
ORDER BY p.position_id ASC, total_votes DESC
";

$res = $conn->query($sql);
$data = [];
$total_votes_all = 0;

if ($res) {
    while ($r = $res->fetch_assoc()) {
        $data[$r['position_name']][] = $r;
        $total_votes_all += intval($r['total_votes']);
    }
}

// Total number of students who voted
$total_voted = 0;
$tvRes = $conn->query("SELECT COUNT(*) AS total_voted FROM ballots");
if ($tvRes && $row = $tvRes->fetch_assoc()) {
    $total_voted = intval($row['total_voted']);
}

// Logo paths
$umak_logo_file = __DIR__ . '/UMAK.png';
$sc_logo_file = __DIR__ . '/sc.png';

// Encode logos as base64
$umak_logo_b64 = file_exists($umak_logo_file)
    ? 'data:image/' . pathinfo($umak_logo_file, PATHINFO_EXTENSION) . ';base64,' . base64_encode(file_get_contents($umak_logo_file))
    : '';
$sc_logo_b64 = file_exists($sc_logo_file)
    ? 'data:image/' . pathinfo($sc_logo_file, PATHINFO_EXTENSION) . ';base64,' . base64_encode(file_get_contents($sc_logo_file))
    : '';

// Build HTML
$html = '<!doctype html><html><head><meta charset="utf-8"><style>
body { font-family: DejaVu Sans, Arial, Helvetica, sans-serif; color:#222; font-size:12px; }
.header-table { width:100%; margin-bottom:8px; }
.header-table td { vertical-align: middle; }
.logo { width:70px; height:auto; }
.title-main { text-align:center; }
.title-main h2 { margin:0; color: #000000ff; font-size:18px; font-weight:800; }
.title-main p { margin:2px 0 6px; font-size:12px; color:#333; }
.info-row { margin-bottom:10px; }
.stat-box { display:inline-block; width:48%; padding:10px; border-radius:6px; background:#f8f9ff; border:1px solid #e6eefc; text-align:center; vertical-align:top; }
.stat-box h4 { margin:0; color:#032c63; }
.pos-header { margin-top:18px; background:#032c63; color:#fff; padding:8px 10px; border-radius:4px; font-size:14px; font-weight:bold; text-transform:uppercase; letter-spacing:0.5px; }
table { width:100%; border-collapse:collapse; margin-top:6px; }
th, td { border:1px solid #ccc; padding:6px; font-size:11px; text-align:left; }
th { background:#f2f6ff; color:#032c63; font-weight:600; }
.footer { text-align:center; font-size:10px; color:#666; margin-top:14px; }
</style></head><body>';

// Header with logos
$html .= '<table class="header-table"><tr>';
$html .= '<td style="width:18%; text-align:left;">' . ($umak_logo_b64 ? "<img src=\"$umak_logo_b64\" class=\"logo\">" : '') . '</td>';
$html .= '<td style="width:64%; text-align:center;" class="title-main">
            <h2>UNIVERSITY OF MAKATI</h2>
            <p>Commission on Student Elections — Official Student Council Election Results</p>
          </td>';
$html .= '<td style="width:18%; text-align:right;">' . ($sc_logo_b64 ? "<img src=\"$sc_logo_b64\" class=\"logo\">" : '') . '</td>';
$html .= '</tr></table>';

// Stats
$html .= '<div class="info-row">';
$html .= '<div class="stat-box"><h4>Total Students Who Voted</h4><div style="font-size:18px; font-weight:700;">' . number_format($total_voted) . '</div></div>';
$html .= '<div style="width:4%; display:inline-block;"></div>';
$html .= '</div>';

// Results
if (empty($data)) {
    $html .= '<p style="color:#b30000;"><strong>No candidates found or no votes recorded.</strong></p>';
} else {
    foreach ($data as $position => $rows) {
        // Position Header (bold)
        $html .= '<div class="pos-header">' . strtoupper(htmlspecialchars($position)) . '</div>';
        $html .= '<table><thead><tr>
                    <th style="width:12%;">Student ID</th>
                    <th style="width:42%;">Candidate Name</th>
                    <th style="width:10%;">Year</th>
                    <th style="width:26%;">College</th>
                    <th style="width:10%;">Votes</th>
                  </tr></thead><tbody>';
        foreach ($rows as $r) {
            $html .= '<tr>';
            $html .= '<td>' . htmlspecialchars($r['user_id']) . '</td>';
            $html .= '<td>' . htmlspecialchars($r['full_name']) . '</td>';
            $html .= '<td style="text-align:center;">' . htmlspecialchars($r['year_level']) . '</td>';
            $html .= '<td>' . htmlspecialchars($r['college_name']) . '</td>';
            $html .= '<td style="text-align:center; font-weight:700;">' . intval($r['total_votes']) . '</td>';
            $html .= '</tr>';
        }
        $html .= '</tbody></table>';
    }
}

// Footer
$html .= '<div class="footer">Generated by UMAK E-Vote System — ' . date('F d, Y h:i A') . '</div>';
$html .= '</body></html>';

// Dompdf config
$options = new Options();
$options->set('isRemoteEnabled', true);
$options->set('defaultFont', 'DejaVu Sans');
$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Create audit_exports folder if it doesn't exist
$export_dir = __DIR__ . '/audit_exports';
if (!is_dir($export_dir)) {
    mkdir($export_dir, 0755, true);
}

// Generate unique filename with timestamp
$timestamp = date('Y-m-d_H-i-s');
$admin_id = $_SESSION['user_id'] ?? 'unknown';
$filename = "results_{$admin_id}_{$timestamp}.pdf";
$filepath = $export_dir . '/' . $filename;

// Save PDF to file
$pdf_content = $dompdf->output();
file_put_contents($filepath, $pdf_content);

// Log the export with file reference using audit_helper
log_action($conn, $admin_id, "Exported PDF results - File: {$filename}");

// Stream to browser (show in new tab, not download)
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="UMAK_Election_Results.pdf"');
echo $pdf_content;

exit;
?>
