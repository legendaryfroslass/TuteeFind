<?php
require_once '../vendor/autoload.php'; // Include the vendor autoload
include '../includes/conn.php'; // Include the database connection file

if (isset($_GET['tutee_id'])) {
    $tuteeId = intval($_GET['tutee_id']); // Get tutee ID

    // Fetch tutee's full name
    $sql = "SELECT CONCAT(firstname, ' ', lastname) AS full_name FROM tutee WHERE id = ?";
    $stmt = $conn->prepare($sql); // Prepare the SQL statement
    $stmt->bind_param("i", $tuteeId); // Bind parameters
    $stmt->execute(); // Execute the statement
    $result = $stmt->get_result(); // Fetch the result
    $tutee = $result->fetch_assoc(); // Fetch tutee data

    // Check if tutee exists
    if (!$tutee) {
        echo "Tutee not found."; // Display error if tutee is not found
        exit;
    }

    $tuteeName = $tutee['full_name']; // Get tutee full name

    // Fetch tutee logs
    $sql = "SELECT activity, datetime FROM tutee_logs WHERE tutee_id = ? ORDER BY datetime ASC";
    $stmt = $conn->prepare($sql); // Prepare the SQL statement
    $stmt->bind_param("i", $tuteeId); // Bind parameters
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

    // Title and tutee name section with bold styling
    $pdf->SetFont('helvetica', 'B', 12); // Set bold font
    $pdf->Cell(0, 10, 'Activity Logs for Tutee', 0, 1, 'C'); // Add bold title with line spacing

    $pdf->SetFont('helvetica', '', 12); // Set regular font
    $pdf->Cell(0, 10, "Tutee: $tuteeName", 0, 1, 'C'); // Add tutee name with bold

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
    $pdf->Output("tutee_logs_$tuteeId.pdf", 'I'); // Send to browser
} else {
    echo "No tutee ID provided."; // Display error if tutee ID is missing
    exit;
}
?>
