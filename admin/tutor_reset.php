<?php
include 'includes/session.php';

// Start transaction
$conn->begin_transaction();

try {
    // Delete all records from archive_requests table
    $sql1 = "DELETE FROM archive_requests";
    $conn->query($sql1);

    // Delete all records from archive_tutee_progress table
    $sql2 = "DELETE FROM archive_tutee_progress";
    $conn->query($sql2);

    // Delete all records from archive_tutor_ratings table
    $sql3 = "DELETE FROM archive_tutor_ratings";
    $conn->query($sql3);

    // Delete all records from archive_tutor_sessions table
    $sql4 = "DELETE FROM archive_tutor_sessions";
    $conn->query($sql4);

    // Delete all records from archive_tutee_summary table
    $sql5 = "DELETE FROM archive_tutee_summary";
    $conn->query($sql5);

    // Delete all records from archive_events table
    $sql6 = "DELETE FROM archive_events";
    $conn->query($sql6);

    // Delete all records from archive_messages table
    $sql7 = "DELETE FROM archive_messages";
    $conn->query($sql7);

    // Delete all records from archive_notifications table
    $sql8 = "DELETE FROM archive_notifications";
    $conn->query($sql8);

    // Delete all records from archive_tutor_logs table
    $sql9 = "DELETE FROM archive_tutor_logs";
    $conn->query($sql9);

    // Delete all records from archive_tutor table
    $sql10 = "DELETE FROM archive_tutor";
    $conn->query($sql10);

    // Commit transaction
    $conn->commit();

    $_SESSION['success'] = 'All archived records deleted successfully';
} catch (mysqli_sql_exception $exception) {
    // Rollback transaction if an error occurs
    $conn->rollback();
    $_SESSION['error'] = $exception->getMessage();
}

header('location: archive_tutor.php');
exit();
?>
