<?php
require_once '../vendor/autoload.php';
require('includes/conn.php'); // Ensure this path is correct

// Create new PDF document with landscape orientation
$pdf = new TCPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false); // 'L' for landscape

// Set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Your Name');
$pdf->SetTitle('TuteeFind Dashboard Report');
$pdf->SetSubject('TuteeFind Dashboard Report');

// Set margins to accommodate the table
$pdf->SetMargins(10, 10, 10); // Left, Top, Right margins
$pdf->SetHeaderMargin(10);
$pdf->SetFooterMargin(10);

// Add a page
$pdf->AddPage();

// Set font for title
$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 10, 'Administrative Dashboard Report', 0, 1, 'C');

// Fetch data for number of matches, professors, tutors, and tutees
$stats = [
    'matches' => $conn->query("SELECT COUNT(*) as count FROM requests WHERE status = 'accepted'")->fetch_assoc()['count'],
    'professors' => $conn->query("SELECT COUNT(*) as count FROM professor")->fetch_assoc()['count'],
    'tutors' => $conn->query("SELECT COUNT(*) as count FROM tutor")->fetch_assoc()['count'],
    'tutees' => $conn->query("SELECT COUNT(*) as count FROM tutee")->fetch_assoc()['count']
];

// Add the statistics to the PDF
$pdf->SetFont('helvetica', '', 12);
$pdf->Cell(0, 10, 'Number of Matches: ' . $stats['matches'], 0, 1);
$pdf->Cell(0, 10, 'Number of Professors: ' . $stats['professors'], 0, 1);
$pdf->Cell(0, 10, 'Number of Tutors: ' . $stats['tutors'], 0, 1);
$pdf->Cell(0, 10, 'Number of Tutees: ' . $stats['tutees'], 0, 1);

// Fetch the current date
$dateDownloaded = date('F j, Y'); // e.g., "September 7, 2024"

// Add the date downloaded
$pdf->Cell(0, 10, 'Downloaded On: ' . $dateDownloaded, 0, 1, 'R'); // Aligns the text to the right

// Set font for tables
$pdf->SetFont('helvetica', '', 10);

// Table header with updated styling
$html = '
<style>
    table {
        border-collapse: collapse;
        width: 100%;
    }
    th {
        background-color: #ADD8E6; /* Light blue background */
        color: #000080; /* Dark blue text */
        font-weight: bold;
        text-align: center;
        padding: 8px;
    }
    td {
        border: 1px solid #ddd; /* Light grey border */
        text-align: center; /* Center-align text */
        padding: 8px;
    }
    tr:nth-child(even) {
        background-color: #f2f2f2; /* Alternating row color */
    }
    tr:hover {
        background-color: #ddd; /* Highlight row on hover */
    }
</style>
';

// Counter for barangays
$counter = 1;

// Tutor Barangay Tally
$html .= '
<h4><strong>Tutor Barangay Tally</strong></h4>
<table border="1" cellpadding="4">
    <thead>
        <tr>
            <th>Barangay</th>
            <th>Total</th>
        </tr>
    </thead>
    <tbody>';

// Fetch data for tutor barangays
$sqlTutorBarangays = "SELECT barangay, COUNT(*) AS total_tutors FROM tutor GROUP BY barangay ORDER BY total_tutors DESC";
$queryTutorBarangays = $conn->query($sqlTutorBarangays);
$totalTutorBarangays = 0;

while ($row = $queryTutorBarangays->fetch_assoc()) {
    $html .= "<tr><td>".$counter.'. '.$row['barangay']."</td><td>".$row['total_tutors']."</td></tr>";
    $counter++;
    $totalTutorBarangays++;
}

$html .= '<tr><td colspan="2" style="text-align: right;"><strong>Total Barangays: '.$totalTutorBarangays.'</strong></td></tr>';
$html .= '</tbody></table>';

// Reset counter for Tutee Barangay Tally
$counter = 1;

// Tutee Barangay Tally
$html .= '
<h4><strong>Tutee Barangay Tally</strong></h4>
<table border="1" cellpadding="4">
    <thead>
        <tr>
            <th>Barangay</th>
            <th>Total</th>
        </tr>
    </thead>
    <tbody>';

// Fetch data for tutee barangays
$sqlTuteeBarangays = "SELECT barangay, COUNT(*) AS total_tutees FROM tutee GROUP BY barangay ORDER BY total_tutees DESC";
$queryTuteeBarangays = $conn->query($sqlTuteeBarangays);
$totalTuteeBarangays = 0;

while ($row = $queryTuteeBarangays->fetch_assoc()) {
    $html .= "<tr><td>".$counter.'. '.$row['barangay']."</td><td>".$row['total_tutees']."</td></tr>";
    $counter++;
    $totalTuteeBarangays++;
}

$html .= '<tr><td colspan="2" style="text-align: right;"><strong>Total Barangays: '.$totalTuteeBarangays.'</strong></td></tr>';
$html .= '</tbody></table>';

// Output the HTML content
$pdf->writeHTML($html, true, false, true, false, '');

// Close and output PDF document
$pdf->Output('dashboard_report.pdf', 'I');
// Set the time zone to Asia/Manila
date_default_timezone_set('Asia/Manila');

 // Log the activity
 $activity = "Download PDF Report";
 $formatted_datetime = date('F j, Y h:i:s A'); // Example: October 6, 2024 11:14:33 PM
 $logSql = "INSERT INTO professor_logs (professor_id, activity, datetime) 
            VALUES (?, ?, ?)";
 $logStmt = $conn->prepare($logSql);
 $logStmt->bind_param("iss", $_SESSION['professor_id'], $activity, $formatted_datetime);
 $logStmt->execute();
?>
?>
