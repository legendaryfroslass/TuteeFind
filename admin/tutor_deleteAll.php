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

            // Prepare statements for each table to delete only selected IDs
            $queries = [
                "DELETE FROM archive_requests WHERE tutor_id IN ($placeholders)",
                "DELETE FROM archive_tutee_progress WHERE tutor_id IN ($placeholders)",
                "DELETE FROM archive_tutor_ratings WHERE tutor_id IN ($placeholders)",
                "DELETE FROM archive_tutor_sessions WHERE tutor_id IN ($placeholders)",
                "DELETE FROM archive_tutee_summary WHERE tutor_id IN ($placeholders)",
                "DELETE FROM archive_events WHERE tutor_id IN ($placeholders)",
                "DELETE FROM archive_messages WHERE tutor_id IN ($placeholders)",
                "DELETE FROM archive_notifications WHERE receiver_id IN ($placeholders) AND sent_for = 'tutor'",
                "DELETE FROM archive_tutor_logs WHERE tutor_id IN ($placeholders)",
                "DELETE FROM archive_tutor WHERE id IN ($placeholders)"
            ];

            foreach ($queries as $query) {
                $stmt = $conn->prepare($query);
                $stmt->bind_param(str_repeat('i', count($selected_ids)), ...$selected_ids);
                $stmt->execute();
            }

            // Commit transaction
            $conn->commit();
            $_SESSION['success'] = 'Selected archived records deleted successfully.';
        } catch (mysqli_sql_exception $exception) {
            // Rollback transaction if an error occurs
            $conn->rollback();
            $_SESSION['error'] = $exception->getMessage();
        }
    } else {
        $_SESSION['error'] = 'No IDs selected for deletion.';
    }
}

header('location: archive_tutor');
exit();
?>
