<?php
require_once '../vendor/autoload.php'; // Adjust path if needed
require('includes/conn.php'); // Ensure this path is correct

// Capture the search input if it exists
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Modify your SQL query to include a WHERE clause for searching
$sql = "SELECT id, firstname, lastname, barangay, number, age, tutee_bday, school, grade FROM archive_tutee 
        WHERE firstname LIKE '%$search%' 
        OR lastname LIKE '%$search%' 
        OR barangay LIKE '%$search%' 
        OR number LIKE '%$search%' 
        OR age LIKE '%$search%' 
        OR school LIKE '%$search%' 
        OR grade LIKE '%$search%'"; // Include additional fields in the search

$query = $conn->query($sql);

// Create new PDF document with portrait orientation
$pdf = new TCPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false); // 'P' for portrait

// Set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Your Name');
$pdf->SetTitle('Tutee List');
$pdf->SetSubject('List of Tutees');

// Set margins to accommodate the table
$pdf->SetMargins(10, 10, 10); // Left, Top, Right margins
$pdf->SetHeaderMargin(10);
$pdf->SetFooterMargin(10);

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
$pdf->Cell(0, 10, 'Tutee List', 0, 1, 'C');

// Fetch the current date
$dateDownloaded = date('F j, Y'); // e.g., "September 7, 2024"

// Add the date downloaded
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
        width: 8%; /* Adjust this width to match other columns */
    }
    .data {
        width: 12%; /* Adjust this width to fit within the page */
    }
    .age, .grade {
        width: 10%; /* Set the same width for Age and Grade columns */
    }
    .contact {
        width: 13%; /* Increase width for Contact No. column */
    }
</style>
<table border="1" cellpadding="4">
    <thead>
        <tr>
            <th class="serial">#</th> <!-- Serial Number Column -->
            <th class="data">Firstname</th>
            <th class="data">Lastname</th>
            <th class="data">Barangay</th>
            <th class="contact">Contact No.</th> <!-- Adjusted class for width -->
            <th class="age">Age</th> <!-- Adjusted class for width -->
            <th class="data">Birthday</th>
            <th class="data">School</th>
            <th class="grade">Grade</th> <!-- Adjusted class for width -->
        </tr>
    </thead>
    <tbody>';

// Fetch data from the database based on the search term
$query = $conn->query($sql);

if ($query === false) {
    die("Error executing query: " . $conn->error);
}

// Initialize the serial number counter
$serialNumber = 1;

while ($row = $query->fetch_assoc()) {
    // Check if the birthday is valid
    $birthday = $row['tutee_bday'];
    $formattedBirthday = '';
    if ($birthday && $birthday != '0000-00-00') {
        $formattedBirthday = date('F j, Y', strtotime($birthday));
    }
    
    $html .= '<tr>
                <td class="serial">' . $serialNumber++ . '</td> <!-- Serial Number -->
                <td class="data">' . $row['firstname'] . '</td>
                <td class="data">' . $row['lastname'] . '</td>
                <td class="data">' . $row['barangay'] . '</td>
                <td class="contact">' . $row['number'] . '</td>
                <td class="age">' . $row['age'] . '</td>
                <td class="data">' . $formattedBirthday . '</td>
                <td class="data">' . $row['school'] . '</td>
                <td class="grade">' . $row['grade'] . '</td>
              </tr>';
}

$html .= '</tbody></table>';

// Output the HTML content
$pdf->writeHTML($html, true, false, true, false, '');

// Close and output PDF document
$pdf->Output('tutee_list.pdf', 'I');
?>
