<?php
include 'includes/session.php';

if (isset($_POST['deleteArchive'])) {
    $id = $_POST['id'];

    // Start transaction
    $conn->begin_transaction();

    try {
        // Delete from archive_requests table where tutor_id matches
        $sql1 = "DELETE FROM archive_requests WHERE tutor_id = '$id'";
        $conn->query($sql1);

        // Delete from archive_tutee_progress table where tutor_id matches
        $sql2 = "DELETE FROM archive_tutee_progress WHERE tutor_id = '$id'";
        $conn->query($sql2);

        // Delete from archive_tutor_ratings table where tutor_id matches
        $sql3 = "DELETE FROM archive_tutor_ratings WHERE tutor_id = '$id'";
        $conn->query($sql3);

        // Delete from archive_tutor_sessions table where tutor_id matches
        $sql4 = "DELETE FROM archive_tutor_sessions WHERE tutor_id = '$id'";
        $conn->query($sql4);

        // Delete from archive_tutor table where id matches
        $sql5 = "DELETE FROM archive_tutor WHERE id = '$id'";
        $conn->query($sql5);

        // Delete from archive_events table where tutor_id matches
        $sql6 = "DELETE FROM archive_events WHERE tutor_id = '$id'";
        $conn->query($sql6);

        // Delete from archive_messages table where tutor_id matches
        $sql7 = "DELETE FROM archive_messages WHERE tutor_id = '$id'";
        $conn->query($sql7);

        // Delete from archive_notifications table where tutor_id matches
        $sql8 = "DELETE FROM archive_notifications WHERE receiver_id = '$id' AND sent_for = 'tutor'";
        $conn->query($sql8);

        // Delete from archive_tutor_logs table where tutor_id matches
        $sql9 = "DELETE FROM archive_tutor_logs WHERE tutor_id = '$id'";
        $conn->query($sql9);

        // Commit transaction
        $conn->commit();

        $_SESSION['success'] = 'Archived tutor and related records deleted successfully';
    } catch (mysqli_sql_exception $exception) {
        // Rollback transaction if an error occurs
        $conn->rollback();
        $_SESSION['error'] = $exception->getMessage();
    }
} else {
    $_SESSION['error'] = 'Select item to delete first';
}

header('location: archive_tutor.php');
?>
