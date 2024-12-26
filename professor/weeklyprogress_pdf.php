<?php
require_once '../vendor/autoload.php'; // Corrected path to the vendor folder
include '../includes/conn.php'; // Adjusted path to the database connection file

if (isset($_GET['tutor_id']) && isset($_GET['tutee_id'])) {
    $tutorId = $_GET['tutor_id'];
    $tuteeId = $_GET['tutee_id'];

    // Fetch tutor and tutee names
    $tutorQuery = "SELECT firstname, lastname FROM tutor WHERE id = ?";
    $tuteeQuery = "SELECT firstname, lastname FROM tutee WHERE id = ?";
    
    $tutorStmt = $conn->prepare($tutorQuery);
    $tutorStmt->bind_param("i", $tutorId);
    $tutorStmt->execute();
    $tutorResult = $tutorStmt->get_result();
    $tutor = $tutorResult->fetch_assoc();
    
    $tuteeStmt = $conn->prepare($tuteeQuery);
    $tuteeStmt->bind_param("i", $tuteeId);
    $tuteeStmt->execute();
    $tuteeResult = $tuteeStmt->get_result();
    $tutee = $tuteeResult->fetch_assoc();

    // Fetch progress data from the database
    $progressQuery = "SELECT week_number, description, date, uploaded_files FROM tutee_progress WHERE tutor_id = ? AND tutee_id = ?";
    $progressStmt = $conn->prepare($progressQuery);
    $progressStmt->bind_param("ii", $tutorId, $tuteeId);
    $progressStmt->execute();
    $progressResult = $progressStmt->get_result();

    if ($progressResult->num_rows > 0) {
        // Create PDF instance
        $pdf = new TCPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // PDF settings
        // $pdf->SetCreator(PDF_CREATOR);
        // $pdf->SetAuthor('Your Organization');
        // $pdf->SetTitle('Weekly Progress Report');
        // $pdf->SetHeaderData('', 0, 'Weekly Progress Report', "Tutor: {$tutor['firstname']} {$tutor['lastname']} - Tutee: {$tutee['firstname']} {$tutee['lastname']}", [0, 0, 0], [255, 255, 255]);
        // $pdf->SetFooterData([0, 0, 0], [255, 255, 255]);
        // $pdf->setHeaderFont([PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN]);
        // $pdf->setFooterFont([PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA]);
        // $pdf->SetMargins(15, 27, 15);
        // $pdf->SetAutoPageBreak(TRUE, 25);
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
        // Start content
        $html = '<h2 style="text-align: center;">Weekly Progress Report</h2>';
        $html .= '<p style="text-align: center; font-weight: bold;">Tutor: ' . $tutor['firstname'] . ' ' . $tutor['lastname'] . ' - Tutee: ' . $tutee['firstname'] . ' ' . $tutee['lastname'] . '</p>';
        
      
        while ($row = $progressResult->fetch_assoc()) {
            $weekNumber = htmlspecialchars($row['week_number']);
            $formattedDate = date('F j, Y h:i:s A', strtotime($row['date']));
            $description = nl2br(htmlspecialchars($row['description']));
            $uploadedFiles = explode(',', $row['uploaded_files']);

            // Section for each week's progress
            $html .= '<div style="margin-bottom: 20px;">';
            $html .= '<h4>Week ' . $weekNumber . ' - ' . $formattedDate . '</h4>';
            $html .= '<p><strong>Description:</strong><br>' . $description . '</p>';

            // Display images and embedded PDFs/Word files
            foreach ($uploadedFiles as $file) {
                if (!empty($file)) {
                    $filePath = '../uploads/' . htmlspecialchars($file);
                    $fileExtension = pathinfo($filePath, PATHINFO_EXTENSION);

                    if (file_exists($filePath)) {
                        if (in_array(strtolower($fileExtension), ['png', 'jpg', 'jpeg', 'gif'])) {
                            $base64Image = base64_encode(file_get_contents($filePath));
                            $html .= '<img src="data:image/' . $fileExtension . ';base64,' . $base64Image . '" alt="' . htmlspecialchars($file) . '" style="max-width: 100%; height: auto; margin-bottom: 10px;">';
                        } elseif (in_array(strtolower($fileExtension), ['pdf'])) {
                            $pdfData = file_get_contents($filePath);
                            $base64Pdf = base64_encode($pdfData);
                            $html .= '<embed src="data:application/pdf;base64,' . $base64Pdf . '" width="100%" height="400px" type="application/pdf">';
                        } elseif (in_array(strtolower($fileExtension), ['doc', 'docx'])) {
                            $docContent = file_get_contents($filePath);
                            $html .= '<div style="border: 1px solid #ddd; padding: 10px; background-color: #f9f9f9; margin-bottom: 10px;">';
                            $html .= '<pre>' . htmlspecialchars($docContent) . '</pre>';
                            $html .= '</div>';
                        } else {
                            $html .= '<p class="text-warning">File not supported: ' . htmlspecialchars($file) . '</p>';
                        }
                    } else {
                        $html .= '<p class="text-danger">File not found: ' . htmlspecialchars($file) . '</p>';
                    }
                }
            }

            $html .= '</div>'; // Close week section
        }

        $html .= '</div>'; // Close content div

        $pdf->writeHTML($html, true, false, true, false, '');

        $pdf->Output('weekly_progress_report.pdf', 'I');
    } else {
        echo '<p class="text-danger">No progress available for the specified tutor and tutee.</p>';
    }

    $progressStmt->close();
    $tutorStmt->close();
    $tuteeStmt->close();
    $conn->close();
} else {
    echo '<p class="text-danger">Error: Tutor ID and tutee ID are required.</p>';
}
?>
