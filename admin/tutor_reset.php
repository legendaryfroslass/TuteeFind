<?php
	include 'includes/session.php';
	// Start transaction
    $conn->begin_transaction();

    try {
        // Delete from requests table where tutor_id matches
        $sql1 = "DELETE FROM requests";
        $conn->query($sql1);

        // Delete from tutee_progress table where tutor_id matches
        $sql2 = "DELETE FROM tutee_progress";
        $conn->query($sql2);

        // Delete from tutee_progress table where tutor_id matches
        $sql3 = "DELETE FROM tutor_ratings";
        $conn->query($sql3);

        // Delete from tutee_progress table where tutor_id matches
        $sql4 = "DELETE FROM tutor_sessions";
        $conn->query($sql4);
		// Delete from tutee_progress table where tutor_id matches
        $sql4 = "DELETE FROM tutee_summary";
        $conn->query($sql4);

        // Delete from tutor table
        $sql5 = "DELETE FROM archive_tutor";
        $conn->query($sql5);

        // Commit transaction
        $conn->commit();

        $_SESSION['success'] = 'Tutor and related records deleted successfully';
    } catch (mysqli_sql_exception $exception) {
        // Rollback transaction if an error occurs
        $conn->rollback();
        $_SESSION['error'] = $exception->getMessage();
    }

	header('location: archive_tutor');

?>