<?php
// Start the session at the top of the file
session_start();

// Check if the session variable exists
if (!isset($_SESSION['professor_id'])) {
    // Redirect to login page or handle the error
    die('You must be logged in to view this page.');
}

require_once '../vendor/autoload.php'; // Adjust path if needed
require('includes/conn.php'); // Ensure this path is correct

// Capture the search input if it exists
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Prepare SQL statement with search conditions
$sql = "
SELECT t.id, t.lastname, t.firstname, t.student_id, t.course, t.year_section
FROM tutor t
INNER JOIN professor p ON t.professor = p.faculty_id
WHERE p.id = ?
AND (
    CONCAT(LOWER(t.course), ' ', LOWER(t.year_section)) LIKE LOWER(?) OR
    CONCAT(LOWER(t.firstname), ' ', LOWER(t.lastname)) LIKE LOWER(?) OR
    LOWER(t.student_id) LIKE LOWER(?)
)";

// Get the professor's ID from session
$professor_id = $_SESSION['professor_id'];

// Prepare statement
$stmt = $conn->prepare($sql);
$search_param = "%$search%";
$stmt->bind_param("isss", $professor_id, $search_param, $search_param, $search_param);
$stmt->execute();
$result = $stmt->get_result();

// Create new PDF document
$pdf = new TCPDF();

// Set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Your Name');
$pdf->SetTitle('Tutor List');
$pdf->SetSubject('List of Tutors');

// Add a page
$pdf->AddPage();
// Add university name, department, address, and logos at the top
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 15, 'Pamantasan ng Lungsod ng Valenzuela', 0, 1, 'C'); // Center align the text

$pdf->SetFont('helvetica', '', 10);
$pdf->MultiCell(0, 5, 'Department of the National Service Training Program' . "\n" .
'Student Center Building, Tongco Street, Barangay Maysan' . "\n" .
'Valenzuela City, Metro Manila', 0, 'C', 0, 1, '', '', true);

// Logo 1: ltslogo.png - placed on the left
$pdf->Image('LoginBackground/plvlogo.png', 35, 20, 20, '', '', '', '', true, 300, '', false, false, 0, false, false, false);

// Logo 2: plvlogo.png - placed on the right
$pdf->Image('LoginBackground/ltslogo.png', 155, 20, 20, '', '', '', '', true, 300, '', false, false, 0, false, false, false);
// Set font for title
$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 10, 'Tutor List', 0, 1, 'C');

// Set font for table
$pdf->SetFont('helvetica', '', 10);

// Table header with styling
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
</style>
<table border="1" cellpadding="4">
    <thead>
        <tr>
            <th>#</th>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Tutor ID</th>
            <th>Course</th>
            <th>Year & Section</th>
        </tr>
    </thead>
    <tbody>';

// Initialize a counter for numbering
$counter = 1;

// Populate the table with results
while ($row = $result->fetch_assoc()) {
    $html .= '<tr>
                <td>' . $counter . '</td>
                <td>' . htmlspecialchars($row['firstname']) . '</td>
                <td>' . htmlspecialchars($row['lastname']) . '</td>
                <td>' . htmlspecialchars($row['student_id']) . '</td>
                <td>' . htmlspecialchars($row['course']) . '</td>
                <td>' . htmlspecialchars($row['year_section']) . '</td>
              </tr>';
    $counter++; // Increment the counter
}

// Close the table body
$html .= '</tbody></table>';

// Output the HTML content
$pdf->writeHTML($html, true, false, true, false, '');

// Print the date of download
$pdf->Ln(10); // Add a line break
$pdf->SetFont('helvetica', 'I', 10);
$pdf->Cell(0, 10, 'Date downloaded: ' . date('F d, Y'), 0, 1, 'R');

// Log the download activity
date_default_timezone_set('Asia/Manila');
$activity = "Download PDF Report: Tutor List";
$formatted_datetime = date('F j, Y h:i:s A');
$logSql = "INSERT INTO professor_logs (professor_id, activity, datetime) 
           VALUES (?, ?, ?)";
$logStmt = $conn->prepare($logSql);
$logStmt->bind_param("iss", $professor_id, $activity, $formatted_datetime);
$logStmt->execute();

// Close and output PDF document
$pdf->Output('tutor_list.pdf', 'I');
?>
