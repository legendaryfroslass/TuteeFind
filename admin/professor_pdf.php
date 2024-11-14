<?php
require_once '../vendor/autoload.php'; // Adjust path if needed
require('includes/conn.php'); // Ensure this path is correct

// Capture the search input if it exists
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Modify your SQL query to include a WHERE clause
$sql = "SELECT id, lastname, firstname, middlename, faculty_id FROM professor 
        WHERE lastname LIKE '%$search%' 
        OR firstname LIKE '%$search%' 
        OR middlename LIKE '%$search%' 
        OR faculty_id LIKE '%$search%'"; // Include additional fields in the search

$query = $conn->query($sql);

// Create new PDF document
$pdf = new TCPDF();

// Set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Your Name');
$pdf->SetTitle('Professor Data Report');
$pdf->SetSubject('List of LTS Professors');

// Add a page
$pdf->AddPage();

// Set font for title
$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 10, 'List of LTS Professors', 0, 1, 'C');

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
            <th>Middle Name</th>
            <th>Age</th>
            <th>Faculty ID</th>
        </tr>
    </thead>
    <tbody>';

// Fetch data from the database based on the search term
$sql = "SELECT lastname, firstname, middlename, age, faculty_id FROM professor 
        WHERE lastname LIKE '%$search%' 
        OR firstname LIKE '%$search%' 
        OR middlename LIKE '%$search%' 
        OR faculty_id LIKE '%$search%'"; // Use the same search condition here
$query = $conn->query($sql);

if ($query === false) {
    die("Error executing query: " . $conn->error);
}

while ($row = $query->fetch_assoc()) {
    $html .= '<tr>
                <td>' . $row['lastname'] . '</td>
                <td>' . $row['firstname'] . '</td>
                <td>' . $row['middlename'] . '</td>
                <td>' . $row['age'] . '</td>
                <td>' . $row['faculty_id'] . '</td>
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
$pdf->Output('professor_data_report.pdf', 'I');

?>
