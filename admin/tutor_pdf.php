<?php
require_once '../vendor/autoload.php'; // Adjust path if needed
require('includes/conn.php'); // Ensure this path is correct

// Capture the search input if it exists 
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Modify your SQL query to include a WHERE clause
$sql = "SELECT lastname, firstname, student_id, course, year_section FROM tutor 
        WHERE LOWER(lastname) LIKE LOWER('%$search%') 
        OR LOWER(firstname) LIKE LOWER('%$search%') 
        OR LOWER(student_id) LIKE LOWER('%$search%') 
        OR LOWER(course) LIKE LOWER('%$search%') 
        OR LOWER(year_section) LIKE LOWER('%$search%') 
        OR LOWER(CONCAT(course, ' ', year_section)) LIKE LOWER('%$search%')";

$query = $conn->query($sql);

// Debug output to verify the query
if (!$query) {
    die("Error executing query: " . $conn->error);
}


// Create new PDF document
$pdf = new TCPDF();

// Set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Your Name');
$pdf->SetTitle('Tutor Data Report');
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
$pdf->Cell(0, 10, 'List of Tutors', 0, 1, 'C');

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
            <th>Last Name</th>
            <th>First Name</th>
            <th>Tutor ID</th>
            <th>Course</th>
            <th>Year & Section</th>
        </tr>
    </thead>
    <tbody>';


// Fetch data from the database based on the search term
$sql = "SELECT lastname, firstname, student_id, course, year_section FROM tutor 
        WHERE lastname LIKE '%$search%' 
        OR firstname LIKE '%$search%' 
        OR student_id LIKE '%$search%' 
        OR course LIKE '%$search%' 
        OR year_section LIKE '%$search%' 
        OR CONCAT(course, ' ', year_section) LIKE '%$search%'";  // Use the same search condition here
$query = $conn->query($sql);

if ($query === false) {
    die("Error executing query: " . $conn->error);
}

while ($row = $query->fetch_assoc()) {
    $html .= '<tr>
                <td>' . $row['lastname'] . '</td>
                <td>' . $row['firstname'] . '</td>
                <td>' . $row['student_id'] . '</td>
                <td>' . $row['course'] . '</td>
                <td>' . $row['year_section'] . '</td>
              </tr>';
}

$html .= '</tbody></table>';

// Output the HTML content
$pdf->writeHTML($html, true, false, true, false, '');

// Print the date of download
$pdf->Ln(10); // Add a line break
$pdf->SetFont('helvetica', 'I', 10);
$pdf->Cell(0, 10, 'Date downloaded: ' . date('F d, Y'), 0, 1, 'R');

// Close and output PDF document
$pdf->Output('tutor_data_report.pdf', 'I');

?>
