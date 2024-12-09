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
        $sql2 = "INSERT INTO archive_tutee_progress (id, tutee_id, tutor_id, week_number, uploaded_files, description, date, rendered_hours, location, subject, status, remarks)
                 SELECT id, tutee_id, tutor_id, week_number, uploaded_files, description, date, rendered_hours, location, subject, status, remarks FROM tutee_progress WHERE tutor_id = '$id'";
        $conn->query($sql2);
        $sql_delete2 = "DELETE FROM tutee_progress WHERE tutor_id = '$id'";
        $conn->query($sql_delete2);

        // Archive and delete from tutor_ratings table where tutor_id matches
        $sql3 = "INSERT INTO archive_tutor_ratings (id, tutee_id, tutor_id, rating, comment, pdf_content)
                 SELECT id, tutee_id, tutor_id, rating, comment, pdf_content FROM tutor_ratings WHERE tutor_id = '$id'";
        $conn->query($sql3);
        $sql_delete3 = "DELETE FROM tutor_ratings WHERE tutor_id = '$id'";
        $conn->query($sql_delete3);

        // Archive and delete from tutor_sessions table where tutor_id matches
        $sql4 = "INSERT INTO archive_tutor_sessions (id, tutor_id, tutee_id, status)
                 SELECT id, tutor_id, tutee_id, status FROM tutor_sessions WHERE tutor_id = '$id'";
        $conn->query($sql4);
        $sql_delete4 = "DELETE FROM tutor_sessions WHERE tutor_id = '$id'";
        $conn->query($sql_delete4);

        // Archive and delete from tutee_summary table where tutor_id matches
        $sql6 = "INSERT INTO archive_tutee_summary (tutee_id, tutor_id, completed_weeks, registered_weeks)
                 SELECT tutee_id, tutor_id, completed_weeks, registered_weeks FROM tutee_summary WHERE tutor_id = '$id'";
        $conn->query($sql6);
        $sql_delete6 = "DELETE FROM tutee_summary WHERE tutor_id = '$id'";
        $conn->query($sql_delete6);

        // Archive and delete from archive_events table where tutor_id matches
        $sql7 = "INSERT INTO archive_events (id, tutor_id, event_name, rendered_hours, description, attached_file, created_at, status, remarks)
                 SELECT id, tutor_id, event_name, rendered_hours, description, attached_file, created_at, status, remarks FROM events WHERE tutor_id = '$id'";
        $conn->query($sql7);
        $sql_delete7 = "DELETE FROM events WHERE tutor_id = '$id'";
        $conn->query($sql_delete7);

        // Archive and delete from archive_messages table where tutor_id matches
        $sql8 = "INSERT INTO archive_messages (id, tutor_id, tutee_id, sender_type, message, created_at, is_read)
                 SELECT id, tutor_id, tutee_id, sender_type, message, created_at, is_read FROM messages WHERE tutor_id = '$id'";
        $conn->query($sql8);
        $sql_delete8 = "DELETE FROM messages WHERE tutor_id = '$id'";
        $conn->query($sql_delete8);

        // Archive and delete from archive_notifications table where tutor_id matches
        $sql9 = "INSERT INTO archive_notifications (id, sender_id, receiver_id, title, message, status, date_sent, sent_for)
                 SELECT id, sender_id, receiver_id, title, message, status, date_sent, sent_for FROM notifications WHERE receiver_id = '$id' AND sent_for = 'tutor'";
        $conn->query($sql9);
        $sql_delete9 = "DELETE FROM notifications WHERE sender_id = '$id' OR receiver_id = '$id'";
        $conn->query($sql_delete9);

        // Archive and delete from archive_tutor_logs table where tutor_id matches
        $sql10 = "INSERT INTO archive_tutor_logs (id, tutor_id, activity, datetime)
                  SELECT id, tutor_id, activity, datetime FROM tutor_logs WHERE tutor_id = '$id'";
        $conn->query($sql10);
        $sql_delete10 = "DELETE FROM tutor_logs WHERE tutor_id = '$id'";
        $conn->query($sql_delete10);

        // Archive and delete from tutor table
        $sql5 = "INSERT INTO archive_tutor (id, lastname, firstname, age, sex, number, barangay, student_id, course, year_section, professor, fblink, emailaddress, password, bio, last_login)
                 SELECT id, lastname, firstname, age, sex, number, barangay, student_id, course, year_section, professor, fblink, emailaddress, password, bio, last_login
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

header('location:tutor');
?>
