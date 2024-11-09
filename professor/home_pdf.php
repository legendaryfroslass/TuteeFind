<?php
session_start();
require_once '../../vendor/autoload.php'; // Ensure the path is correct
include 'includes/conn.php'; // Include the database connection file

if(!isset($_SESSION['professor_id'])){
  header('location: login');
  exit();
}

$professor_id = $_SESSION['professor_id'];

// Create new PDF document with landscape orientation
$pdf = new TCPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false); // 'L' for landscape

// Set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Your Name'); // Change this to the appropriate author
$pdf->SetTitle('Professor Dashboard Report');
$pdf->SetSubject('Professor Dashboard Report');

// Set margins to accommodate the table
$pdf->SetMargins(10, 10, 10); // Left, Top, Right margins
$pdf->SetHeaderMargin(10);
$pdf->SetFooterMargin(10);

// Add a page
$pdf->AddPage();

// Set font for title
$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 10, 'Professor Dashboard Report', 0, 1, 'C');

// Fetch statistics for the professor
$stats = [
    'matches' => $conn->prepare("SELECT COUNT(*) as count 
                                  FROM requests 
                                  INNER JOIN tutor ON requests.tutor_id = tutor.id
                                  INNER JOIN professor ON tutor.professor = professor.faculty_id
                                  WHERE requests.status = 'accepted' AND professor.id = ?")
];

    $stats['matches']->bind_param("i", $professor_id);
    $stats['matches']->execute();
    $result = $stats['matches']->get_result();
    $stats['matches'] = $result->fetch_assoc()['count'];

    $stats['tutors'] = $conn->prepare("SELECT COUNT(*) as count 
                                        FROM tutor 
                                        WHERE professor = (SELECT faculty_id FROM professor WHERE id = ?)");
    $stats['tutors']->bind_param("i", $professor_id);
    $stats['tutors']->execute();
    $result = $stats['tutors']->get_result();
    $stats['tutors'] = $result->fetch_assoc()['count'];

    $stats['tutees'] = $conn->prepare("SELECT COUNT(DISTINCT tutee.id) as count 
                                         FROM tutee 
                                         INNER JOIN requests ON tutee.id = requests.tutee_id
                                         INNER JOIN tutor ON requests.tutor_id = tutor.id
                                         INNER JOIN professor ON tutor.professor = professor.faculty_id
                                         WHERE professor.id = ? AND requests.status = 'accepted'");
    $stats['tutees']->bind_param("i", $professor_id);
    $stats['tutees']->execute();
    $result = $stats['tutees']->get_result();
    $stats['tutees'] = $result->fetch_assoc()['count'];

// Add the statistics to the PDF
$pdf->SetFont('helvetica', '', 12);
$pdf->Cell(0, 10, 'Number of Matches: ' . $stats['matches'], 0, 1);
$pdf->Cell(0, 10, 'Number of Tutors: ' . $stats['tutors'], 0, 1);
$pdf->Cell(0, 10, 'Number of Tutees: ' . $stats['tutees'], 0, 1);

// Fetch the current date
$dateDownloaded = date('F j, Y'); // e.g., "September 7, 2024"

// Add the date downloaded
$pdf->Cell(0, 10, 'Downloaded On: ' . $dateDownloaded, 0, 1, 'R'); // Aligns the text to the right

// Set font for tables
$pdf->SetFont('helvetica', '', 10);

// Fetch and display barangay tallies for tutors
$sqlTutorBarangays = "SELECT DISTINCT t.barangay, COUNT(*) AS total_tutors 
                      FROM tutor t
                      INNER JOIN professor p ON t.professor = p.faculty_id
                      WHERE p.id = ?
                      GROUP BY t.barangay 
                      ORDER BY total_tutors DESC";

$stmt = $conn->prepare($sqlTutorBarangays);
$stmt->bind_param("i", $professor_id);
$stmt->execute();
$queryTutorBarangays = $stmt->get_result();

// Create table for Tutor Barangay tally
$html = '<h4><strong>Tutor Barangay Tally</strong></h4>';
$html .= '<table border="1" cellpadding="5">';
$html .= '<thead><tr><th>Barangay</th><th>Total Tutors</th></tr></thead>';
$html .= '<tbody>';
while($row = $queryTutorBarangays->fetch_assoc()) {
    $html .= '<tr>';
    $html .= '<td>'.$row['barangay'].'</td>';
    $html .= '<td>'.$row['total_tutors'].'</td>';
    $html .= '</tr>';
}
$html .= '</tbody>';
$html .= '</table>';

// Add to PDF
$pdf->writeHTML($html, true, false, true, false, '');

// Fetch and display barangay tallies for tutees
$sqlTuteeBarangays = "SELECT DISTINCT tutee.barangay, COUNT(*) AS total_tutees 
                      FROM tutee 
                      INNER JOIN requests ON tutee.id = requests.tutee_id
                      INNER JOIN tutor ON requests.tutor_id = tutor.id
                      INNER JOIN professor p ON tutor.professor = p.faculty_id
                      WHERE p.id = ? AND requests.status = 'accepted'
                      GROUP BY tutee.barangay 
                      ORDER BY total_tutees DESC";

$stmt = $conn->prepare($sqlTuteeBarangays);
$stmt->bind_param("i", $professor_id);
$stmt->execute();
$queryTuteeBarangays = $stmt->get_result();

// Create table for Tutee Barangay tally
$html = '<h4><strong>Tutee Barangay Tally</strong></h4>';
$html .= '<table border="1" cellpadding="5">';
$html .= '<thead><tr><th>Barangay</th><th>Total Tutees</th></tr></thead>';
$html .= '<tbody>';
while($row = $queryTuteeBarangays->fetch_assoc()) {
    $html .= '<tr>';
    $html .= '<td>'.$row['barangay'].'</td>';
    $html .= '<td>'.$row['total_tutees'].'</td>';
    $html .= '</tr>';
}
$html .= '</tbody>';
$html .= '</table>';

// Add to PDF
$pdf->writeHTML($html, true, false, true, false, '');

// Close and output PDF document
$pdf->Output('Professor_Dashboard_Report.pdf', 'I'); // 'I' for inline view

// Close the database connection
$conn->close();
?>
