<?php
include 'includes/session.php';

// Start a transaction to ensure data integrity
$conn->begin_transaction();

try {
    // Delete related logs from the archive_professor_logs table
    $sql_logs = "DELETE FROM archive_professor_logs";
    if (!$conn->query($sql_logs)) {
        throw new Exception("Error deleting logs from archive_professor_logs: " . $conn->error);
    }

    // Delete all professors from the archive_professor table
    $sql = "DELETE FROM archive_professor";
    if ($conn->query($sql)) {
        $_SESSION['success'] = "List of professors reset successfully";
    } else {
        throw new Exception("Error deleting professors from archive_professor: " . $conn->error);
    }

    // Commit the transaction
    $conn->commit();
} catch (Exception $e) {
    // Rollback the transaction if an error occurs
    $conn->rollback();
    $_SESSION['error'] = $e->getMessage();
}

header('location: archive_professor.php');
?>
