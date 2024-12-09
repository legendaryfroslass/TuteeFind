<?php
include 'includes/session.php';

if (isset($_POST['delete'])) {
    $id = $_POST['id'];
    // Start transaction
    $conn->begin_transaction();

    try {
        // Define the archive tables and their corresponding field for tutee_id
        $archive_tables = [
            "archive_messages" => "tutee_id",
            "archive_notifications" => "receiver_id",
            "archive_requests" => "tutee_id",
            "archive_tutee_progress" => "tutee_id",
            "archive_tutee_summary" => "tutee_id",
            "archive_tutor_ratings" => "tutee_id",
            "archive_tutor_sessions" => "tutee_id",
            "archive_tutee_logs" => "tutee_id",
            "archive_tutee" => "id"
        ];

        // Loop through each table and delete records where tutee_id matches
        foreach ($archive_tables as $table => $field) {
            $sql_delete = "DELETE FROM $table WHERE $field = '$id'";
            if (!$conn->query($sql_delete)) {
                throw new Exception("Error deleting from $table: " . $conn->error);
            }
        }

        // Commit transaction
        $conn->commit();
        $_SESSION['success'] = 'Tutee and related records deleted successfully';
    } catch (Exception $e) {
        // Rollback transaction if an error occurs
        $conn->rollback();
        $_SESSION['error'] = $e->getMessage();
    }
} else {
    $_SESSION['error'] = 'Select item to delete first';
}

header('location: archive_tutee.php');
exit();
?>
