<?php
require_once '../vendor/autoload.php'; // Adjust the path as necessary
require('includes/conn.php'); // Ensure this path is correct

// Create new PDF document with portrait orientation
$pdf = new TCPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false); // 'P' for portrait

// Set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Your Name');
$pdf->SetTitle('Archived Tutee\'s List');
$pdf->SetSubject('Archived Tutee\'s List');

// Set margins to accommodate the table
$pdf->SetMargins(10, 10, 10); // Left, Top, Right margins
$pdf->SetHeaderMargin(10);
$pdf->SetFooterMargin(10);

// Add a page
$pdf->AddPage();

// Set font for title
$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 10, 'Archived Tutee\'s List', 0, 1, 'C');

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
        width: 8%; /* Adjusted width for serial number */
    }
    .age {
        width: 10%; /* Adjusted width for age */
    }
    .grade {
        width: 10%; /* Adjusted width for grade */
    }
    .contact {
        width: 13%; /* Adjusted width for contact number (1% longer) */
    }
    .data {
        width: 12%; /* Adjusted width for other columns */
    }
</style>
<table border="1" cellpadding="4">
    <thead>
        <tr>
            <th class="serial">#</th> <!-- Serial Number Column -->
            <th class="data">Firstname</th>
            <th class="data">Lastname</th>
            <th class="data">Barangay</th>
            <th class="contact">Contact No.</th> <!-- Adjusted width for contact number -->
            <th class="age">Age</th>
            <th class="data">Birthday</th>
            <th class="data">School</th>
            <th class="grade">Grade</th>
        </tr>
    </thead>
    <tbody>';

// Fetch data from the database
$sql = "SELECT id, firstname, lastname, barangay, number, age, tutee_birthday, school, grade FROM archive_tutee";
$query = $conn->query($sql);

if ($query === false) {
    die("Error executing query: " . $conn->error);
}

// Initialize the serial number counter
$serialNumber = 1;

while ($row = $query->fetch_assoc()) {
    // Format the tutee_birthday field to 'Month Day, Year'
    $birthday = date('F j, Y', strtotime($row['tutee_birthday']));
    
    $html .= '<tr>
                <td class="serial">' . $serialNumber++ . '</td> <!-- Serial Number -->
                <td class="data">' . $row['firstname'] . '</td>
                <td class="data">' . $row['lastname'] . '</td>
                <td class="data">' . $row['barangay'] . '</td>
                <td class="contact">' . $row['number'] . '</td> <!-- Adjusted width for contact number -->
                <td class="age">' . $row['age'] . '</td>
                <td class="data">' . $birthday . '</td>
                <td class="data">' . $row['school'] . '</td>
                <td class="grade">' . $row['grade'] . '</td>
              </tr>';
}

$html .= '</tbody></table>';

// Output the HTML content
$pdf->writeHTML($html, true, false, true, false, '');

// Close and output PDF document
$pdf->Output('archive_tutees_report.pdf', 'I');
?>
