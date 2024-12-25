<?php
require_once '../vendor/autoload.php'; // Adjust path if needed
require('includes/conn.php'); // Ensure this path is correct

// Capture the search input if it exists
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Prepare SQL statement with search conditions
$sql = "
SELECT 
    professor.firstname AS proffirst, 
    professor.lastname AS proflast, 
    tutor.firstname AS tutfirst, 
    tutor.lastname AS tutlast, 
    tutee.firstname AS tuteefirst, 
    tutee.lastname AS tuteelast, 
    tutee.barangay AS tuteebarangay,
    tutor.course, 
    tutor.year_section
FROM requests
LEFT JOIN tutor ON tutor.id = requests.tutor_id
LEFT JOIN tutee ON tutee.id = requests.tutee_id
LEFT JOIN professor ON professor.faculty_id = tutor.professor
WHERE requests.status = 'accepted'
AND (
    CONCAT(LOWER(tutor.course), ' ', LOWER(tutor.year_section)) LIKE LOWER('%$search%') OR
    CONCAT(LOWER(tutor.firstname), ' ', LOWER(tutor.lastname)) LIKE LOWER('%$search%') OR
    CONCAT(LOWER(professor.firstname), ' ', LOWER(professor.lastname)) LIKE LOWER('%$search%') OR
    CONCAT(LOWER(tutee.firstname), ' ', LOWER(tutee.lastname)) LIKE LOWER('%$search%') OR 
    LOWER(professor.lastname) LIKE LOWER('%$search%') OR
    LOWER(professor.firstname) LIKE LOWER('%$search%') OR
    LOWER(tutor.lastname) LIKE LOWER('%$search%') OR
    LOWER(tutor.firstname) LIKE LOWER('%$search%') OR
    LOWER(tutee.lastname) LIKE LOWER('%$search%') OR
    LOWER(tutee.firstname) LIKE LOWER('%$search%') OR
    LOWER(tutee.barangay) LIKE LOWER('%$search%')
)";

// Execute the query
$query = $conn->query($sql);

// Check for errors in query execution
if (!$query) {
    die("Error executing query: " . $conn->error);
}

// Create new PDF document
$pdf = new TCPDF();

// Set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Your Name');
$pdf->SetTitle('Tutors and Tutees Report');
$pdf->SetSubject('Tutors and Tutees Report');

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
$pdf->Cell(0, 10, 'Tutors and Tutees Report', 0, 1, 'C');

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
            <th>Professor</th>
            <th>Tutor</th>
            <th>Tutee</th>
            <th>Barangay</th>
            <th>Course: Year & Section</th>
        </tr>
    </thead>
    <tbody>';

// Initialize a counter for numbering
$counter = 1;

// Populate the table with results
while ($row = $query->fetch_assoc()) {
    $html .= '<tr>
                <td>' . $counter . '</td>
                <td>' . htmlspecialchars($row['proffirst'] . ' ' . $row['proflast']) . '</td>
                <td>' . htmlspecialchars($row['tutfirst'] . ' ' . $row['tutlast']) . '</td>
                <td>' . htmlspecialchars($row['tuteefirst'] . ' ' . $row['tuteelast']) . '</td>
                <td>' . htmlspecialchars($row['tuteebarangay']) . '</td>
                <td>' . htmlspecialchars($row['course'] . " " . $row['year_section']) . '</td>
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

// Close and output PDF document
$pdf->Output('matches_report.pdf', 'I');
?>
