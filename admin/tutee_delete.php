<?php
include 'includes/session.php';

if(isset($_POST['delete'])){
    $id = $_POST['id'];
    // Start transaction
    $conn->begin_transaction();

    try {
        // Delete from related tables
        $sql1 = "DELETE FROM archive_requests WHERE tutee_id = '$id'";
        $conn->query($sql1);
        $sql2 = "DELETE FROM archive_tutee_progress WHERE tutee_id = '$id'";
        $conn->query($sql2);
        $sql3 = "DELETE FROM archive_tutee_summary WHERE tutee_id = '$id'";
        $conn->query($sql3);
        $sql4 = "DELETE FROM archive_tutor_ratings WHERE tutee_id = '$id'";
        $conn->query($sql4);
        $sql5 = "DELETE FROM archive_tutor_sessions WHERE tutee_id = '$id'";
        $conn->query($sql5);
        $sql6 = "DELETE FROM archive_tutee WHERE id = '$id'";
        $conn->query($sql6);

        // Commit transaction
        $conn->commit();
        $_SESSION['success'] = 'Tutee and related records deleted successfully';
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
