<?php
require_once '../vendor/autoload.php'; // Include the vendor autoload
include '../includes/conn.php'; // Include the database connection file

// Check if tutor_id is provided
if (isset($_GET['tutor_id'])) {
    $tutorId = intval($_GET['tutor_id']); // Get tutor ID

    // Fetch tutor's full name
    $sql = "SELECT CONCAT(firstname, ' ', lastname) AS full_name FROM tutor WHERE id = ?";
    $stmt = $conn->prepare($sql); // Prepare the SQL statement
    $stmt->bind_param("i", $tutorId); // Bind parameters
    $stmt->execute(); // Execute the statement
    $result = $stmt->get_result(); // Fetch the result
    $tutor = $result->fetch_assoc(); // Fetch tutor data

    // Check if tutor exists
    if (!$tutor) {
        echo "Tutor not found."; // Display error if tutor is not found
        exit;
    }

    $tutorName = $tutor['full_name']; // Get tutor full name

    // Fetch tutor logs
    $sql = "SELECT activity, datetime FROM tutor_logs WHERE tutor_id = ? ORDER BY datetime ASC";
    $stmt = $conn->prepare($sql); // Prepare the SQL statement
    $stmt->bind_param("i", $tutorId); // Bind parameters
    $stmt->execute(); // Execute the statement
    $result = $stmt->get_result(); // Fetch the result

    // Generate PDF
    $pdf = new TCPDF();
    $pdf->AddPage(); // Add a new page

    // Add university name, department, address, and logos at the top
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 15, 'Pamantasan ng Lungsod ng Valenzuela', 0, 1, 'C'); // Center align the text

    $pdf->SetFont('helvetica', '', 10);
    $pdf->MultiCell(0, 5, 'Department of the National Service Training Program' . "\n" .
    'Student Center Building, Tongco Street, Barangay Maysan' . "\n" .
    'Valenzuela City, Metro Manila', 0, 'C', 0, 1, '', '', true);

    // Logo 1: plvlogo.png - placed on the left
    $pdf->Image('LoginBackground/plvlogo.png', 35, 20, 20, '', '', '', '', true, 300, '', false, false, 0, false, false, false);

    // Logo 2: ltslogo.png - placed on the right
    $pdf->Image('LoginBackground/ltslogo.png', 155, 20, 20, '', '', '', '', true, 300, '', false, false, 0, false, false, false);

    // Title and tutor name section with bold styling
    $pdf->SetFont('helvetica', 'B', 12); // Set bold font
    $pdf->Cell(0, 10, 'Activity Logs for Tutor', 0, 1, 'C'); // Add bold title with line spacing

    $pdf->SetFont('helvetica', '', 12); // Set regular font
    $pdf->Cell(0, 10, "Tutor: $tutorName", 0, 1, 'C'); // Add tutor name with bold

    // HTML content for the table
    $html = '
    <table border="1" cellpadding="6" cellspacing="0" style="border-collapse: collapse;">
        <thead style="background-color: #f0f0f0;">
            <tr>
                <th style="padding: 8px; font-weight: bold;">Activity</th>
                <th style="padding: 8px; font-weight: bold;">Date & Time</th>
            </tr>
        </thead>
        <tbody>';
    
    // Loop through the logs and populate the table rows
    while ($row = $result->fetch_assoc()) {
        $dateTime = new DateTime($row['datetime']);
        $formattedTime = $dateTime->format('m/d/Y h:i:s A'); // Format as MM/DD/YYYY hh:mm:ss AM/PM

        $html .= '
        <tr>
            <td style="padding: 8px;">' . htmlspecialchars($row['activity']) . '</td>
            <td style="padding: 8px;">' . $formattedTime . '</td>
        </tr>';
    }

    $html .= '</tbody></table>'; // Close table

    $pdf->writeHTML($html, true, false, true, false, ''); // Write the HTML content

    // Output the PDF
    $pdf->Output("tutor_logs_$tutorId.pdf", 'I'); // Send to browser
} else {
    echo "No tutor ID provided."; // Display error if tutor ID is missing
    exit;
}
?>
