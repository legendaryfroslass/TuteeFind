<?php
include 'includes/session.php';

if (isset($_POST['deleteAll']) && isset($_POST['selected_ids'])) {
    $selected_ids = json_decode($_POST['selected_ids'], true); // Decode the JSON array

    if (count($selected_ids) > 0) {
        // Begin a transaction to ensure data integrity
        $conn->begin_transaction();

        try {
            // Prepare a list of placeholders for the selected IDs
            $placeholders = implode(',', array_fill(0, count($selected_ids), '?'));

            // Delete related logs from the archive_professor_logs table for selected IDs
            $sql_logs = "DELETE FROM archive_professor_logs WHERE professor_id IN ($placeholders)";
            $stmt_logs = $conn->prepare($sql_logs);
            $stmt_logs->bind_param(str_repeat('i', count($selected_ids)), ...$selected_ids);
            $stmt_logs->execute();

            // Delete selected professors from the archive_professor table
            $sql_professor = "DELETE FROM archive_professor WHERE id IN ($placeholders)";
            $stmt_professor = $conn->prepare($sql_professor);
            $stmt_professor->bind_param(str_repeat('i', count($selected_ids)), ...$selected_ids);
            $stmt_professor->execute();

            // Commit the transaction
            $conn->commit();
            $_SESSION['success'] = "Selected professors deleted successfully.";
        } catch (Exception $e) {
            // Rollback the transaction if an error occurs
            $conn->rollback();
            $_SESSION['error'] = $e->getMessage();
        }
    } else {
        $_SESSION['error'] = 'No IDs selected for deletion.';
    }
} else {
    $_SESSION['error'] = 'No data received for deletion.';
}

header('location: archive_professor');
exit();
?>
