<?php
include 'includes/session.php';

if (isset($_POST['restoreTutor'])) {
    $id = $_POST['id'];

    // Fetch the tutor's data from archive_tutor table
    $sql = "SELECT * FROM archive_tutor WHERE id = '$id'";
    $query = $conn->query($sql);
    $row = $query->fetch_assoc();

    if ($row) {
        // Insert into tutor table
        $sql_restoreTutor = "INSERT INTO tutor (id, lastname, firstname, age, sex, number, barangay, student_id, course, year_section, professor, fblink, emailaddress, password, bio) 
                        VALUES ('".$row['id']."','".$row['lastname']."', '".$row['firstname']."', '".$row['age']."', '".$row['sex']."', '".$row['number']."', '".$row['barangay']."', '".$row['student_id']."', '".$row['course']."', '".$row['year_section']."', '".$row['professor']."', '".$row['fblink']."', '".$row['emailaddress']."', '".$row['password']."', '".$row['bio']."')";

        if ($conn->query($sql_restoreTutor)) {
            // Restore associated data from the archive tables

            // Restore requests
            $sql_restoreRequests = "INSERT INTO requests (request_id, tutor_id, tutee_id, status)
                                    SELECT request_id, tutor_id, tutee_id, status FROM archive_requests WHERE tutor_id = '$id'";
            if ($conn->query($sql_restoreRequests)) {
                $sql_deleteRequests = "DELETE FROM archive_requests WHERE tutor_id = '$id'";
                $conn->query($sql_deleteRequests);
            }

            // Restore tutee progress
            $sql_restoreProgress = "INSERT INTO tutee_progress (id, tutee_id, tutor_id, week_number, uploaded_files, description, date)
                                    SELECT id, tutee_id, tutor_id, week_number, uploaded_files, description, date FROM archive_tutee_progress WHERE tutor_id = '$id'";
            if ($conn->query($sql_restoreProgress)) {
                $sql_deleteProgress = "DELETE FROM archive_tutee_progress WHERE tutor_id = '$id'";
                $conn->query($sql_deleteProgress);
            }

            // Restore tutor ratings
            $sql_restoreRatings = "INSERT INTO tutor_ratings (id, tutee_id, tutor_id, rating, comment, pdf_content)
                                    SELECT id, tutee_id, tutor_id, rating, comment, pdf_content FROM archive_tutor_ratings WHERE tutor_id = '$id'";
            if ($conn->query($sql_restoreRatings)) {
                $sql_deleteRatings = "DELETE FROM archive_tutor_ratings WHERE tutor_id = '$id'";
                $conn->query($sql_deleteRatings);
            }

            // Restore tutor sessions
            $sql_restoreSessions = "INSERT INTO tutor_sessions (id, tutor_id, tutee_id, status)
                                    SELECT id, tutor_id, tutee_id, status FROM archive_tutor_sessions WHERE tutor_id = '$id'";
            if ($conn->query($sql_restoreSessions)) {
                $sql_deleteSessions = "DELETE FROM archive_tutor_sessions WHERE tutor_id = '$id'";
                $conn->query($sql_deleteSessions);
            }

            // Restore tutee summary
            $sql_restoreSummary = "INSERT INTO tutee_summary (tutee_id, tutor_id, completed_weeks, registered_weeks)
                                    SELECT tutee_id, tutor_id, completed_weeks, registered_weeks FROM archive_tutee_summary WHERE tutor_id = '$id'";
            if ($conn->query($sql_restoreSummary)) {
                $sql_deleteSummary = "DELETE FROM archive_tutee_summary WHERE tutor_id = '$id'";
                $conn->query($sql_deleteSummary);
            }

            // Restore events
            $sql_restoreEvents = "INSERT INTO events (id, tutor_id, event_name, rendered_hours, description, attached_file, created_at, status, remarks)
                                  SELECT id, tutor_id, event_name, rendered_hours, description, attached_file, created_at, status, remarks FROM archive_events WHERE tutor_id = '$id'";
            if ($conn->query($sql_restoreEvents)) {
                $sql_deleteEvents = "DELETE FROM archive_events WHERE tutor_id = '$id'";
                $conn->query($sql_deleteEvents);
            }

            // Restore messages
            $sql_restoreMessages = "INSERT INTO messages (id, tutor_id, tutee_id, sender_type, message, created_at, is_read)
                                    SELECT id, tutor_id, tutee_id, sender_type, message, created_at, is_read FROM archive_messages WHERE tutor_id = '$id'";
            if ($conn->query($sql_restoreMessages)) {
                $sql_deleteMessages = "DELETE FROM archive_messages WHERE tutor_id = '$id'";
                $conn->query($sql_deleteMessages);
            }

            // Restore notifications
            $sql_restoreNotifications = "INSERT INTO notifications (id, sender_id, receiver_id, title, message, status, date_sent, sent_for)
                                         SELECT id, sender_id, receiver_id, title, message, status, date_sent, sent_for FROM archive_notifications WHERE receiver_id = '$id' AND sent_for = 'tutor'";
            if ($conn->query($sql_restoreNotifications)) {
                $sql_deleteNotifications = "DELETE FROM archive_notifications WHERE receiver_id = '$id' AND sent_for = 'tutor'";
                $conn->query($sql_deleteNotifications);
            }

            // Restore tutor logs (if applicable)
            $sql_restoreLogs = "INSERT INTO tutor_logs (id, tutor_id, activity, datetime)
                               SELECT id, tutor_id, activity, datetime FROM archive_tutor_logs WHERE tutor_id = '$id'";
            if ($conn->query($sql_restoreLogs)) {
                $sql_deleteLogs = "DELETE FROM archive_tutor_logs WHERE tutor_id = '$id'";
                $conn->query($sql_deleteLogs);
            }

            // Delete the tutor from the archive_tutor table after restoring
            $sql_delete = "DELETE FROM archive_tutor WHERE id = '$id'";
            if ($conn->query($sql_delete)) {
                $_SESSION['success'] = 'Tutor restored successfully';
            } else {
                $_SESSION['error'] = $conn->error;
            }
        } else {
            $_SESSION['error'] = $conn->error;
        }
    } else {
        $_SESSION['error'] = 'Tutor not found in archive';
    }
} else {
    $_SESSION['error'] = 'Select item to restore first';
}

header('location: archive_tutor.php');
exit();
?>
