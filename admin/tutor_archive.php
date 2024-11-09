<?php
include 'includes/session.php';

if (isset($_POST['archiveTutor'])) {
    $id = $_POST['id'];

    // Start transaction
    $conn->begin_transaction();

    try {
        // Archive and delete from requests table where tutor_id matches
        $sql1 = "INSERT INTO archive_requests (request_id, tutor_id, tutee_id, status)
                 SELECT request_id, tutor_id, tutee_id, status FROM requests WHERE tutor_id = '$id'";
        $conn->query($sql1);
        $sql_delete1 = "DELETE FROM requests WHERE tutor_id = '$id'";
        $conn->query($sql_delete1);

        // Archive and delete from tutee_progress table where tutor_id matches
        $sql2 = "INSERT INTO archive_tutee_progress (id, tutee_id, tutor_id, week_number, uploaded_files, description, date)
                 SELECT id, tutee_id, tutor_id, week_number, uploaded_files, description, date FROM tutee_progress WHERE tutor_id = '$id'";
        $conn->query($sql2);
        $sql_delete2 = "DELETE FROM tutee_progress WHERE tutor_id = '$id'";
        $conn->query($sql_delete2);

        // Archive and delete from tutor_ratings table where tutor_id matches
        $sql3 = "INSERT INTO archive_tutor_ratings (id, tutee_id, tutor_id, rating, comment)
                 SELECT id, tutee_id, tutor_id, rating, comment FROM tutor_ratings WHERE tutor_id = '$id'";
        $conn->query($sql3);
        $sql_delete3 = "DELETE FROM tutor_ratings WHERE tutor_id = '$id'";
        $conn->query($sql_delete3);

        // Archive and delete from tutor_sessions table where tutor_id matches
        $sql4 = "INSERT INTO archive_tutor_sessions (id, tutor_id, tutee_id)
                 SELECT id, tutor_id, tutee_id FROM tutor_sessions WHERE tutor_id = '$id'";
        $conn->query($sql4);
        $sql_delete4 = "DELETE FROM tutor_sessions WHERE tutor_id = '$id'";
        $conn->query($sql_delete4);

        // Archive and delete from tutor table
        $sql5 = "INSERT INTO archive_tutor (id, lastname, firstname, age, sex, number, barangay, student_id, course, year_section, professor, fblink, emailaddress, password)
                 SELECT id, lastname, firstname, age, sex, number, barangay, student_id, course, year_section, professor, fblink, emailaddress, password 
                 FROM tutor WHERE id = '$id'";
        $conn->query($sql5);
        $sql_delete5 = "DELETE FROM tutor WHERE id = '$id'";
        $conn->query($sql_delete5);

        // Commit transaction
        $conn->commit();

        $_SESSION['success'] = 'Tutor and related records archived successfully';
    } catch (mysqli_sql_exception $exception) {
        // Rollback transaction if an error occurs
        $conn->rollback();
        $_SESSION['error'] = $exception->getMessage();
    }
} else {
    $_SESSION['error'] = 'Select item to archive first';
}

header('location:tutor.php');
?>
