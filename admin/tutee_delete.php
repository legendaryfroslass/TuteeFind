<?php
include 'includes/session.php';

if (isset($_POST['deleteArchive'])) {
    $id = $_POST['id'];

    // Start transaction
    $conn->begin_transaction();

    try {
        // Delete from archive_notifications table where receiver_id matches and sent_for is 'tutee'
        $sql1 = "DELETE FROM archive_notifications WHERE receiver_id = '$id' AND sent_for = 'tutee'";
        $conn->query($sql1);

        // Delete from archive_tutee_logs table where tutee_id matches
        $sql2 = "DELETE FROM archive_tutee_logs WHERE tutee_id = '$id'";
        $conn->query($sql2);

        // Delete from archive_tutee table where id matches
        $sql3 = "DELETE FROM archive_tutee WHERE id = '$id'";
        $conn->query($sql3);

        // Commit transaction
        $conn->commit();

        $_SESSION['success'] = 'Archived tutee and related records deleted successfully';
    } catch (mysqli_sql_exception $exception) {
        // Rollback transaction if an error occurs
        $conn->rollback();
        $_SESSION['error'] = $exception->getMessage();
    }
} else {
    $_SESSION['error'] = 'Select item to delete first';
}

header('location: archive_tutee.php');
?>
