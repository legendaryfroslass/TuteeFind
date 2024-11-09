<?php
include 'includes/session.php';

if(isset($_POST['deleteAllTutee'])){
    // Start transaction
    $conn->begin_transaction();

    try {
        // Delete all related records from the archive tables
        $sql1 = "DELETE FROM archive_requests";
        $conn->query($sql1);
        $sql2 = "DELETE FROM archive_tutee_progress";
        $conn->query($sql2);
        $sql3 = "DELETE FROM archive_tutee_summary";
        $conn->query($sql3);
        $sql4 = "DELETE FROM archive_tutor_ratings";
        $conn->query($sql4);
        $sql5 = "DELETE FROM archive_tutor_sessions";
        $conn->query($sql5);
        $sql6 = "DELETE FROM archive_tutee";
        $conn->query($sql6);

        // Commit transaction
        $conn->commit();
        $_SESSION['success'] = 'All tutees and related records deleted successfully';
    } catch (mysqli_sql_exception $exception) {
        // Rollback transaction if an error occurs
        $conn->rollback();
        $_SESSION['error'] = $exception->getMessage();
    }
} else {
    $_SESSION['error'] = 'Select option to delete all first';
}

header('location: archive_tutee.php');
?>
