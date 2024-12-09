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

            // Insert back into the tutor table
            $sql_restore = "INSERT INTO tutor (INSERT INTO tutor (id, lastname, firstname, age, sex, number, barangay, student_id, course, year_section, professor, fblink, emailaddress, password, bio, last_login 
                            VALUES ('".$row['id']."', '".$row['lastname']."', '".$row['firstname']."', '".$row['age']."', '".$row['sex']."', '".$row['number']."', '".$row['barangay']."', '".$row['student_id']."', '".$row['course']."', '".$row['year_section']."', '".$row['professor']."', '".$row['fblink']."', '".$row['emailaddress']."', '".$row['password']."', '".$row['bio']."', '".$row['last_login']."')";

            if ($conn->query($sql_restore)) {
                // Restore related data from each table manually

                // Restore requests table
                $sql_restore_requests = "INSERT INTO requests SELECT * FROM archive_requests WHERE tutor_id = '$id'";
                if (!$conn->query($sql_restore_requests)) {
                    throw new Exception('Error restoring requests: ' . $conn->error);
                }

                // Restore tutee_progress table
                $sql_restore_tutee_progress = "INSERT INTO tutee_progress SELECT * FROM archive_tutee_progress WHERE tutor_id = '$id'";
                if (!$conn->query($sql_restore_tutee_progress)) {
                    throw new Exception('Error restoring tutee_progress: ' . $conn->error);
                }

                // Restore tutor_ratings table
                $sql_restore_tutor_ratings = "INSERT INTO tutor_ratings SELECT * FROM archive_tutor_ratings WHERE tutor_id = '$id'";
                if (!$conn->query($sql_restore_tutor_ratings)) {
                    throw new Exception('Error restoring tutor_ratings: ' . $conn->error);
                }

                // Restore tutor_sessions table
                $sql_restore_tutor_sessions = "INSERT INTO tutor_sessions SELECT * FROM archive_tutor_sessions WHERE tutor_id = '$id'";
                if (!$conn->query($sql_restore_tutor_sessions)) {
                    throw new Exception('Error restoring tutor_sessions: ' . $conn->error);
                }

                // Restore tutee_summary table
                $sql_restore_tutee_summary = "INSERT INTO tutee_summary SELECT * FROM archive_tutee_summary WHERE tutor_id = '$id'";
                if (!$conn->query($sql_restore_tutee_summary)) {
                    throw new Exception('Error restoring tutee_summary: ' . $conn->error);
                }

                // Restore events table
                $sql_restore_events = "INSERT INTO events SELECT * FROM archive_events WHERE tutor_id = '$id'";
                if (!$conn->query($sql_restore_events)) {
                    throw new Exception('Error restoring events: ' . $conn->error);
                }

                // Restore messages table
                $sql_restore_messages = "INSERT INTO messages SELECT * FROM archive_messages WHERE tutor_id = '$id'";
                if (!$conn->query($sql_restore_messages)) {
                    throw new Exception('Error restoring messages: ' . $conn->error);
                }

                // Restore notifications table
                $sql_restore_notifications = "INSERT INTO notifications SELECT * FROM archive_notifications WHERE receiver_id = '$id' AND sent_for = 'tutor'";
                if (!$conn->query($sql_restore_notifications)) {
                    throw new Exception('Error restoring notifications: ' . $conn->error);
                }

                // Restore tutor_logs table
                $sql_restore_tutor_logs = "INSERT INTO tutor_logs SELECT * FROM archive_tutor_logs WHERE tutor_id = '$id'";
                if (!$conn->query($sql_restore_tutor_logs)) {
                    throw new Exception('Error restoring tutor_logs: ' . $conn->error);
                }

                // Delete the restored data from archive tables
                $sql_delete_requests = "DELETE FROM archive_requests WHERE tutor_id = '$id'";
                if (!$conn->query($sql_delete_requests)) {
                    throw new Exception("Error deleting requests from archive: " . $conn->error);
                }

                $sql_delete_tutee_progress = "DELETE FROM archive_tutee_progress WHERE tutor_id = '$id'";
                if (!$conn->query($sql_delete_tutee_progress)) {
                    throw new Exception("Error deleting tutee_progress from archive: " . $conn->error);
                }

                $sql_delete_tutor_ratings = "DELETE FROM archive_tutor_ratings WHERE tutor_id = '$id'";
                if (!$conn->query($sql_delete_tutor_ratings)) {
                    throw new Exception("Error deleting tutor_ratings from archive: " . $conn->error);
                }

                $sql_delete_tutor_sessions = "DELETE FROM archive_tutor_sessions WHERE tutor_id = '$id'";
                if (!$conn->query($sql_delete_tutor_sessions)) {
                    throw new Exception("Error deleting tutor_sessions from archive: " . $conn->error);
                }

                $sql_delete_tutee_summary = "DELETE FROM archive_tutee_summary WHERE tutor_id = '$id'";
                if (!$conn->query($sql_delete_tutee_summary)) {
                    throw new Exception("Error deleting tutee_summary from archive: " . $conn->error);
                }

                $sql_delete_events = "DELETE FROM archive_events WHERE tutor_id = '$id'";
                if (!$conn->query($sql_delete_events)) {
                    throw new Exception("Error deleting events from archive: " . $conn->error);
                }

                $sql_delete_messages = "DELETE FROM archive_messages WHERE tutor_id = '$id'";
                if (!$conn->query($sql_delete_messages)) {
                    throw new Exception("Error deleting messages from archive: " . $conn->error);
                }

                $sql_delete_notifications = "DELETE FROM archive_notifications WHERE receiver_id = '$id' AND sent_for = 'tutor'";
                if (!$conn->query($sql_delete_notifications)) {
                    throw new Exception("Error deleting notifications from archive: " . $conn->error);
                }

                $sql_delete_tutor_logs = "DELETE FROM archive_tutor_logs WHERE tutor_id = '$id'";
                if (!$conn->query($sql_delete_tutor_logs)) {
                    throw new Exception("Error deleting tutor_logs from archive: " . $conn->error);
                }

                // Delete the tutor from the archive_tutor table
                $sql_delete_tutor = "DELETE FROM archive_tutor WHERE id = '$id'";
                if (!$conn->query($sql_delete_tutor)) {
                    throw new Exception("Error deleting tutor from archive_tutor: " . $conn->error);
                }

                // Commit the transaction
                $conn->commit();
                $_SESSION['success'] = 'Tutor and related data restored successfully';
            } else {
                throw new Exception('Error restoring tutor: ' . $conn->error);
            }
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
