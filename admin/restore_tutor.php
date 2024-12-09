<?php
include 'includes/session.php';

if (isset($_POST['restore'])) {
    $id = $_POST['id'];

    // Start a transaction
    $conn->begin_transaction();

    try {
        // Fetch the tutor's data from the archive
        $sql = "SELECT * FROM archive_tutor WHERE id = '$id'";
        $query = $conn->query($sql);

        if ($query->num_rows > 0) {
            $row = $query->fetch_assoc();

            // Prepare the insert statement for the tutor table
            $stmt_restore = $conn->prepare("INSERT INTO tutor (id, lastname, firstname, age, sex, number, barangay, student_id, course, year_section, professor, fblink, emailaddress, password, bio, last_login) 
                                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

            // Bind the parameters for the insert
            $stmt_restore->bind_param(
                "ississssssssssss", // Types of the parameters
                $row['id'],        // id
                $row['lastname'],  // lastname
                $row['firstname'], // firstname
                $row['age'],       // age
                $row['sex'],       // sex
                $row['number'],    // number
                $row['barangay'],  // barangay
                $row['student_id'],// student_id
                $row['course'],    // course
                $row['year_section'], // year_section
                $row['professor'], // professor
                $row['fblink'],    // fblink
                $row['emailaddress'], // emailaddress
                $row['password'],  // password
                $row['bio'],       // bio
                $row['last_login'] // last_login
            );

            // Execute the insert statement
            if (!$stmt_restore->execute()) {
                throw new Exception("Error restoring tutor: ".$conn->error);
            }

            $tutor_id = $row['id']; // Store the tutor's ID for restoring related data

            // Restore related data for this tutor
            // Restore requests
            $sql_restoreRequests = "INSERT INTO requests (request_id, tutor_id, tutee_id, status)
                                    SELECT request_id, tutor_id, tutee_id, status FROM archive_requests WHERE tutor_id = '$tutor_id'";
            if (!$conn->query($sql_restoreRequests)) {
                throw new Exception('Error restoring requests: ' . $conn->error);
            }
            $sql_deleteRequests = "DELETE FROM archive_requests WHERE tutor_id = '$tutor_id'";
            if (!$conn->query($sql_deleteRequests)) {
                throw new Exception("Error deleting requests from archive: " . $conn->error);
            }

            // Restore tutee progress
            $sql_restoreProgress = "INSERT INTO tutee_progress (id, tutee_id, tutor_id, week_number, uploaded_files, description, date)
                                    SELECT id, tutee_id, tutor_id, week_number, uploaded_files, description, date FROM archive_tutee_progress WHERE tutor_id = '$tutor_id'";
            if (!$conn->query($sql_restoreProgress)) {
                throw new Exception('Error restoring tutee_progress: ' . $conn->error);
            }
            $sql_deleteProgress = "DELETE FROM archive_tutee_progress WHERE tutor_id = '$tutor_id'";
            if (!$conn->query($sql_deleteProgress)) {
                throw new Exception("Error deleting tutee_progress from archive: " . $conn->error);
            }

            // Restore tutor ratings
            $sql_restoreRatings = "INSERT INTO tutor_ratings (id, tutee_id, tutor_id, rating, comment, pdf_content)
                                   SELECT id, tutee_id, tutor_id, rating, comment, pdf_content FROM archive_tutor_ratings WHERE tutor_id = '$tutor_id'";
            if (!$conn->query($sql_restoreRatings)) {
                throw new Exception('Error restoring tutor_ratings: ' . $conn->error);
            }
            $sql_deleteRatings = "DELETE FROM archive_tutor_ratings WHERE tutor_id = '$tutor_id'";
            if (!$conn->query($sql_deleteRatings)) {
                throw new Exception("Error deleting tutor_ratings from archive: " . $conn->error);
            }

            // Restore tutor sessions
            $sql_restoreSessions = "INSERT INTO tutor_sessions (id, tutor_id, tutee_id, status)
                                    SELECT id, tutor_id, tutee_id, status FROM archive_tutor_sessions WHERE tutor_id = '$tutor_id'";
            if (!$conn->query($sql_restoreSessions)) {
                throw new Exception('Error restoring tutor_sessions: ' . $conn->error);
            }
            $sql_deleteSessions = "DELETE FROM archive_tutor_sessions WHERE tutor_id = '$tutor_id'";
            if (!$conn->query($sql_deleteSessions)) {
                throw new Exception("Error deleting tutor_sessions from archive: " . $conn->error);
            }

            // Restore tutee summary
            $sql_restoreSummary = "INSERT INTO tutee_summary (tutee_id, tutor_id, completed_weeks, registered_weeks)
                                    SELECT tutee_id, tutor_id, completed_weeks, registered_weeks FROM archive_tutee_summary WHERE tutor_id = '$tutor_id'";
            if (!$conn->query($sql_restoreSummary)) {
                throw new Exception('Error restoring tutee_summary: ' . $conn->error);
            }
            $sql_deleteSummary = "DELETE FROM archive_tutee_summary WHERE tutor_id = '$tutor_id'";
            if (!$conn->query($sql_deleteSummary)) {
                throw new Exception("Error deleting tutee_summary from archive: " . $conn->error);
            }

            // Restore events
            $sql_restoreEvents = "INSERT INTO events (id, tutor_id, event_name, rendered_hours, description, attached_file, created_at, status, remarks)
                                  SELECT id, tutor_id, event_name, rendered_hours, description, attached_file, created_at, status, remarks FROM archive_events WHERE tutor_id = '$tutor_id'";
            if (!$conn->query($sql_restoreEvents)) {
                throw new Exception('Error restoring events: ' . $conn->error);
            }
            $sql_deleteEvents = "DELETE FROM archive_events WHERE tutor_id = '$tutor_id'";
            if (!$conn->query($sql_deleteEvents)) {
                throw new Exception("Error deleting events from archive: " . $conn->error);
            }

            // Restore messages
            $sql_restoreMessages = "INSERT INTO messages (id, tutor_id, tutee_id, sender_type, message, created_at, is_read)
                                    SELECT id, tutor_id, tutee_id, sender_type, message, created_at, is_read FROM archive_messages WHERE tutor_id = '$tutor_id'";
            if (!$conn->query($sql_restoreMessages)) {
                throw new Exception('Error restoring messages: ' . $conn->error);
            }
            $sql_deleteMessages = "DELETE FROM archive_messages WHERE tutor_id = '$tutor_id'";
            if (!$conn->query($sql_deleteMessages)) {
                throw new Exception("Error deleting messages from archive: " . $conn->error);
            }

            // Restore notifications
            $sql_restoreNotifications = "INSERT INTO notifications (id, sender_id, receiver_id, title, message, status, date_sent, sent_for)
                                         SELECT id, sender_id, receiver_id, title, message, status, date_sent, sent_for FROM archive_notifications WHERE receiver_id = '$tutor_id' AND sent_for = 'tutor'";
            if (!$conn->query($sql_restoreNotifications)) {
                throw new Exception('Error restoring notifications: ' . $conn->error);
            }
            $sql_deleteNotifications = "DELETE FROM archive_notifications WHERE receiver_id = '$tutor_id' AND sent_for = 'tutor'";
            if (!$conn->query($sql_deleteNotifications)) {
                throw new Exception("Error deleting notifications from archive: " . $conn->error);
            }

            // Restore tutor logs
            $sql_restoreLogs = "INSERT INTO tutor_logs (id, tutor_id, activity, datetime)
                               SELECT id, tutor_id, activity, datetime FROM archive_tutor_logs WHERE tutor_id = '$tutor_id'";
            if (!$conn->query($sql_restoreLogs)) {
                throw new Exception('Error restoring tutor logs: ' . $conn->error);
            }
            $sql_deleteLogs = "DELETE FROM archive_tutor_logs WHERE tutor_id = '$tutor_id'";
            if (!$conn->query($sql_deleteLogs)) {
                throw new Exception("Error deleting tutor logs from archive: " . $conn->error);
            }

            // Delete the tutor from archive_tutor table after restoring
            $sql_deleteTutor = "DELETE FROM archive_tutor WHERE id = '$tutor_id'";
            if (!$conn->query($sql_deleteTutor)) {
                throw new Exception("Error deleting tutor from archive_tutor: " . $conn->error);
            }

            // Commit the transaction
            $conn->commit();
            $_SESSION['success'] = 'Tutor and related data restored successfully';
        } else {
            throw new Exception('Tutor not found in archive');
        }
    } catch (Exception $e) {
        // Rollback the transaction in case of any error
        $conn->rollback();
        $_SESSION['error'] = $e->getMessage();
    }
} else {
    $_SESSION['error'] = 'Select item to restore first';
}

header('location: archive_tutor.php');
exit();
?>
