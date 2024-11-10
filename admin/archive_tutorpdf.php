<?php
require_once '../vendor/autoload.php';
require('includes/conn.php'); // Ensure this path is correct

// Create new PDF document with landscape orientation
$pdf = new TCPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false); // 'L' for landscape

// Set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Your Name');
$pdf->SetTitle('Archived Tutor\'s List');
$pdf->SetSubject('Archived Tutor\'s List');

// Set margins to accommodate the table
$pdf->SetMargins(10, 10, 10); // Left, Top, Right margins
$pdf->SetHeaderMargin(10);
$pdf->SetFooterMargin(10);

// Add a page
$pdf->AddPage();

// Set font for title
$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 10, 'Archived Tutor\'s List', 0, 1, 'C');

// Fetch the current date
$dateDownloaded = date('F j, Y'); // e.g., "September 7, 2024"

// Add the date downloaded on
$pdf->SetFont('helvetica', '', 12);
$pdf->Cell(0, 10, 'Downloaded On: ' . $dateDownloaded, 0, 1, 'R'); // Aligns the text to the right

// Set font for table
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
    .serial {
        width: 10%; /* Adjust this width as needed */
    }
    .data {
        width: 14%; /* Adjust this width to fit within the page */
    }
</style>
<table border="1" cellpadding="4">
    <thead>
        <tr>
            <th class="serial">#</th> <!-- Serial Number Column -->
            <th class="data">Lastname</th>
            <th class="data">Firstname</th>
            <th class="data">Student ID</th>
            <th class="data">Course</th>
            <th class="data">Year & Section</th>
            <th class="data">Archive Time</th>
        </tr>
    </thead>
    <tbody>';

// Fetch data from the database
$sql = "SELECT id, lastname, firstname, student_id, course, year_section, archive_at FROM archive_tutor";
$query = $conn->query($sql);

if ($query === false) {
    die("Error executing query: " . $conn->error);
}

// Initialize the serial number counter
$serialNumber = 1;

while ($row = $query->fetch_assoc()) {
    // Format the archive_at field to 'Month Day, Year Hour:Minute:Second AM/PM'
    $archiveTime = date('F j, Y g:i:s A', strtotime($row['archive_at']));
    
    $html .= '<tr>
                <td class="serial">' . $serialNumber++ . '</td> <!-- Serial Number -->
                <td class="data">' . $row['lastname'] . '</td>
                <td class="data">' . $row['firstname'] . '</td>
                <td class="data">' . $row['student_id'] . '</td>
                <td class="data">' . $row['course'] . '</td>
                <td class="data">' . $row['year_section'] . '</td>
                <td class="data">' . $archiveTime . '</td>
              </tr>';
}

$html .= '</tbody></table>';

// Output the HTML content
$pdf->writeHTML($html, true, false, true, false, '');

// Close and output PDF document
$pdf->Output('archive_tutors_report.pdf', 'I');
?>
