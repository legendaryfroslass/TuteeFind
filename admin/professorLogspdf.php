<?php
require_once '../vendor/autoload.php'; // Include the vendor autoload
include '../includes/conn.php'; // Include the database connection file

// Check if professor_id is provided
if (isset($_GET['professor_id'])) {
    $professorId = intval($_GET['professor_id']); // Get professor ID

    // Fetch professor's full name
    $sql = "SELECT CONCAT(firstname, ' ', lastname) AS full_name FROM professor WHERE id = ?";
    $stmt = $conn->prepare($sql); // Prepare the SQL statement
    $stmt->bind_param("i", $professorId); // Bind parameters
    $stmt->execute(); // Execute the statement
    $result = $stmt->get_result(); // Fetch the result
    $professor = $result->fetch_assoc(); // Fetch professor data

    // Check if professor exists
    if (!$professor) {
        echo "Professor not found."; // Display error if professor is not found
        exit;
    }

    $professorName = $professor['full_name']; // Get professor full name

    // Fetch professor logs
    $sql = "SELECT activity, datetime FROM professor_logs WHERE professor_id = ?";
    $stmt = $conn->prepare($sql); // Prepare the SQL statement
    $stmt->bind_param("i", $professorId); // Bind parameters
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

    // Title and professor name section with bold styling
    $pdf->SetFont('helvetica', 'B', 12); // Set bold font
    $pdf->Cell(0, 10, 'Activity Logs for Professor', 0, 1, 'C'); // Add bold title with line spacing

    $pdf->SetFont('helvetica', '', 12); // Set regular font
    $pdf->Cell(0, 10, "Professor: $professorName", 0, 1, 'C'); // Add professor name with bold

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
        $html .= '
        <tr>
            <td style="padding: 8px;">' . htmlspecialchars($row['activity']) . '</td>
            <td style="padding: 8px;">' . htmlspecialchars($row['datetime']) . '</td>
        </tr>';
    }

    $html .= '</tbody></table>'; // Close table

    $pdf->writeHTML($html, true, false, true, false, ''); // Write the HTML content

    // Output the PDF
    $pdf->Output("professor_logs_$professorId.pdf", 'I'); // Send to browser
} else {
    echo "No professor ID provided."; // Display error if professor ID is missing
    exit;
}
?>
