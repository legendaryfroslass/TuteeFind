<?php
include 'includes/session.php';

if (isset($_POST['delete'])) {
    $id = $_POST['id'];

    // Start a transaction to ensure data integrity
    $conn->begin_transaction();

    try {
        // Delete related logs from the archive_professor_logs table
        $sql_logs = "DELETE FROM archive_professor_logs WHERE professor_id = '$id'";
        if (!$conn->query($sql_logs)) {
            throw new Exception("Error deleting logs for professor with ID $id: " . $conn->error);
        }

        // Delete professor from the archive_professor table
        $sql_professor = "DELETE FROM archive_professor WHERE id = '$id'";
        if ($conn->query($sql_professor)) {
            $_SESSION['success'] = 'Professor and related logs deleted successfully';
        } else {
            throw new Exception("Error deleting professor with ID $id: " . $conn->error);
        }

        // Commit the transaction
        $conn->commit();
    } catch (Exception $e) {
        // Rollback the transaction if an error occurs
        $conn->rollback();
        $_SESSION['error'] = $e->getMessage();
    }
} else {
    $_SESSION['error'] = 'Select item to delete first';
}

header('location: archive_professor.php');
?>
