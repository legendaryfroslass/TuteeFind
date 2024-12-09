<?php
include 'includes/session.php';

if (isset($_POST['deleteAllTutee'])) {
    // Start transaction
    $conn->begin_transaction();

    try {
        // Define the archive tables to delete from
        $archive_tables = [
            "archive_messages",
            "archive_notifications",
            "archive_requests",
            "archive_tutee_progress",
            "archive_tutee_summary",
            "archive_tutor_ratings",
            "archive_tutor_sessions",
            "archive_tutee_logs",
            "archive_tutee"
        ];

        // Loop through each table and delete all records
        foreach ($archive_tables as $table) {
            $sql_delete = "DELETE FROM $table";
            if (!$conn->query($sql_delete)) {
                throw new Exception("Error deleting from $table: " . $conn->error);
            }
        }

        // Commit transaction
        $conn->commit();
        $_SESSION['success'] = 'All archived tutees and related records deleted successfully';
    } catch (Exception $e) {
        // Rollback transaction if an error occurs
        $conn->rollback();
        $_SESSION['error'] = $e->getMessage();
    }
} else {
    $_SESSION['error'] = 'Select an option to delete all first';
}

header('location: archive_tutee.php');
exit();
?>
