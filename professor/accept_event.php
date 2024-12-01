<?php
include 'includes/session.php';

if (isset($_POST['accept'])) {
    // Check if required POST data is set
    if (isset($_POST['event_id'], $_POST['remarks'])) {
        // Retrieve form data
        $event_id = $_POST['event_id'];
        $remarks = $_POST['remarks'];
        $status = 'accepted'; // Status is always 'accepted'

        // Prepare the SQL statement to update the event
        $sql = "UPDATE events SET remarks = ?, status = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            // Bind the parameters (remarks, status, event_id)
            $stmt->bind_param("ssi", $remarks, $status, $event_id);

            // Execute the statement
            if ($stmt->execute()) {
                $_SESSION['success'] = 'Request accepted successfully';
            } else {
                $_SESSION['error'] = 'Error: ' . $stmt->error;
            }

            $stmt->close(); // Close the statement
        } else {
            $_SESSION['error'] = 'Error: ' . $conn->error;
        }
    } else {
        $_SESSION['error'] = 'Missing form data.';
    }
} else {
    $_SESSION['error'] = 'Fill up the form first.';
}

$conn->close();
header('Location: event_request.php');
?>
