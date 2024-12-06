<?php
include 'includes/session.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']); // Sanitize input
    $sql = "SELECT pdf_content FROM tutor_ratings WHERE tutor_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($pdf_content);
        $stmt->fetch();

        // Set headers to view the PDF in browser
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="feedback.pdf"');
        echo $pdf_content; // Stream the PDF content
    } else {
        echo "No PDF found for this ID.";
    }
    $stmt->close();
} else {
    echo "Invalid request.";
}
?>
