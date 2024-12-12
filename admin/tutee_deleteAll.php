<?php
include 'includes/session.php';

if (isset($_POST['deleteAll']) && isset($_POST['selected_ids'])) {
    $selected_ids = json_decode($_POST['selected_ids'], true); // Decode the JSON array

    if (count($selected_ids) > 0) {
        // Start transaction
        $conn->begin_transaction();

        try {
            // Delete from archive_notifications using receiver_id and sent_for
            $sql_notifications_delete = "DELETE FROM archive_notifications 
                                          WHERE receiver_id IN (" . implode(',', array_fill(0, count($selected_ids), '?')) . ") 
                                          AND sent_for = 'tutee'";
            $stmt_notifications = $conn->prepare($sql_notifications_delete);
            $stmt_notifications->bind_param(str_repeat('i', count($selected_ids)), ...$selected_ids);
            $stmt_notifications->execute();

            // Delete from archive_tutee_logs using tutee_id
            $sql_logs_delete = "DELETE FROM archive_tutee_logs 
                                WHERE tutee_id IN (" . implode(',', array_fill(0, count($selected_ids), '?')) . ")";
            $stmt_logs = $conn->prepare($sql_logs_delete);
            $stmt_logs->bind_param(str_repeat('i', count($selected_ids)), ...$selected_ids);
            $stmt_logs->execute();

            // Delete from archive_tutee using id
            $sql_tutee_delete = "DELETE FROM archive_tutee 
                                 WHERE id IN (" . implode(',', array_fill(0, count($selected_ids), '?')) . ")";
            $stmt_tutee = $conn->prepare($sql_tutee_delete);
            $stmt_tutee->bind_param(str_repeat('i', count($selected_ids)), ...$selected_ids);
            $stmt_tutee->execute();

            // Commit transaction
            $conn->commit();
            $_SESSION['success'] = 'Selected archived tutees and related records deleted successfully';
        } catch (Exception $e) {
            // Rollback transaction if an error occurs
            $conn->rollback();
            $_SESSION['error'] = $e->getMessage();
        }
    } else {
        $_SESSION['error'] = 'No IDs selected for deletion.';
    }
} else {
    $_SESSION['error'] = 'Select an option to delete all first.';
}

header('location: archive_tutee');
exit();
?>
