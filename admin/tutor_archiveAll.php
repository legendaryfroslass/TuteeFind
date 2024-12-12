<?php
include 'includes/session.php';

if (isset($_POST['archiveAll']) && isset($_POST['selected_ids'])) {
    $selected_ids = json_decode($_POST['selected_ids'], true); // Decode the JSON array

    if (count($selected_ids) > 0) {
        // Begin a transaction to ensure data integrity
        $conn->begin_transaction();

        try {
            // Prepare the insert statement for the archive
            $stmt_archive = $conn->prepare("INSERT INTO archive_tutor (id, lastname, firstname, age, sex, number, barangay, student_id, course, year_section, professor, fblink, emailaddress, password, bio, last_login) 
                                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

            // Prepare check for existing student_id in the archive_tutor table
            $stmt_check = $conn->prepare("SELECT student_id FROM archive_tutor WHERE student_id = ?");

            // Arrays to store duplicated student IDs and successfully archived tutor IDs
            $duplicate_student_ids = [];
            $archived_tutor_ids = [];

            // Loop through each selected tutor ID
            foreach ($selected_ids as $id) {
                // Fetch tutor data based on the selected ID
                $sql_select = "SELECT * FROM tutor WHERE id = ?";
                $stmt_select = $conn->prepare($sql_select);
                $stmt_select->bind_param("i", $id);
                $stmt_select->execute();
                $result = $stmt_select->get_result();

                if ($result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    $student_id = $row['student_id'];

                    // Check if the student_id already exists in the archive_tutor table
                    $stmt_check->bind_param("s", $student_id);
                    $stmt_check->execute();
                    $stmt_check->store_result();

                    if ($stmt_check->num_rows === 0) {
                        // If student_id does not exist, proceed to archive
                        $stmt_archive->bind_param(
                            "ississssssssssss",
                            $row['id'],
                            $row['lastname'],
                            $row['firstname'],
                            $row['age'],
                            $row['sex'],
                            $row['number'],
                            $row['barangay'],
                            $row['student_id'],
                            $row['course'],
                            $row['year_section'],
                            $row['professor'],
                            $row['fblink'],
                            $row['emailaddress'],
                            $row['password'],
                            $row['bio'],
                            $row['last_login']
                        );

                        if ($stmt_archive->execute()) {
                            // Archive related tables here
                            // Archive requests
                            $sql_request = "INSERT INTO archive_requests (request_id, tutor_id, tutee_id, status)
                                            SELECT request_id, tutor_id, tutee_id, status FROM requests WHERE tutor_id = ?";
                            $stmt_request = $conn->prepare($sql_request);
                            $stmt_request->bind_param("i", $id);
                            $stmt_request->execute();
                            $sql_delete_request = "DELETE FROM requests WHERE tutor_id = ?";
                            $stmt_delete_request = $conn->prepare($sql_delete_request);
                            $stmt_delete_request->bind_param("i", $id);
                            $stmt_delete_request->execute();

                            // Archive tutee_progress
                            $sql_progress = "INSERT INTO archive_tutee_progress (id, tutee_id, tutor_id, week_number, uploaded_files, description, date, rendered_hours, location, subject, status, remarks)
                                            SELECT id, tutee_id, tutor_id, week_number, uploaded_files, description, date, rendered_hours, location, subject, status, remarks FROM tutee_progress WHERE tutor_id = ?";
                            $stmt_progress = $conn->prepare($sql_progress);
                            $stmt_progress->bind_param("i", $id);
                            $stmt_progress->execute();
                            $sql_delete_progress = "DELETE FROM tutee_progress WHERE tutor_id = ?";
                            $stmt_delete_progress = $conn->prepare($sql_delete_progress);
                            $stmt_delete_progress->bind_param("i", $id);
                            $stmt_delete_progress->execute();

                            // Archive tutor_ratings
                            $sql_ratings = "INSERT INTO archive_tutor_ratings (id, tutee_id, tutor_id, rating, comment, pdf_content)
                                            SELECT id, tutee_id, tutor_id, rating, comment, pdf_content FROM tutor_ratings WHERE tutor_id = ?";
                            $stmt_ratings = $conn->prepare($sql_ratings);
                            $stmt_ratings->bind_param("i", $id);
                            $stmt_ratings->execute();
                            $sql_delete_ratings = "DELETE FROM tutor_ratings WHERE tutor_id = ?";
                            $stmt_delete_ratings = $conn->prepare($sql_delete_ratings);
                            $stmt_delete_ratings->bind_param("i", $id);
                            $stmt_delete_ratings->execute();

                            // Archive tutor_sessions
                            $sql_sessions = "INSERT INTO archive_tutor_sessions (id, tutor_id, tutee_id, status)
                                            SELECT id, tutor_id, tutee_id, status FROM tutor_sessions WHERE tutor_id = ?";
                            $stmt_sessions = $conn->prepare($sql_sessions);
                            $stmt_sessions->bind_param("i", $id);
                            $stmt_sessions->execute();
                            $sql_delete_sessions = "DELETE FROM tutor_sessions WHERE tutor_id = ?";
                            $stmt_delete_sessions = $conn->prepare($sql_delete_sessions);
                            $stmt_delete_sessions->bind_param("i", $id);
                            $stmt_delete_sessions->execute();

                            // Archive tutee_summary
                            $sql_summary = "INSERT INTO archive_tutee_summary (tutee_id, tutor_id, completed_weeks, registered_weeks)
                                            SELECT tutee_id, tutor_id, completed_weeks, registered_weeks FROM tutee_summary WHERE tutor_id = ?";
                            $stmt_summary = $conn->prepare($sql_summary);
                            $stmt_summary->bind_param("i", $id);
                            $stmt_summary->execute();
                            $sql_delete_summary = "DELETE FROM tutee_summary WHERE tutor_id = ?";
                            $stmt_delete_summary = $conn->prepare($sql_delete_summary);
                            $stmt_delete_summary->bind_param("i", $id);
                            $stmt_delete_summary->execute();

                            // Archive events
                            $sql_events = "INSERT INTO archive_events (id, tutor_id, event_name, rendered_hours, description, attached_file, created_at, status, remarks)
                                           SELECT id, tutor_id, event_name, rendered_hours, description, attached_file, created_at, status, remarks FROM events WHERE tutor_id = ?";
                            $stmt_events = $conn->prepare($sql_events);
                            $stmt_events->bind_param("i", $id);
                            $stmt_events->execute();
                            $sql_delete_events = "DELETE FROM events WHERE tutor_id = ?";
                            $stmt_delete_events = $conn->prepare($sql_delete_events);
                            $stmt_delete_events->bind_param("i", $id);
                            $stmt_delete_events->execute();

                            // Archive tutor_logs
                            $sql_logs = "INSERT INTO archive_tutor_logs (id, tutor_id, activity, datetime)
                                         SELECT id, tutor_id, activity, datetime FROM tutor_logs WHERE tutor_id = ?";
                            $stmt_logs = $conn->prepare($sql_logs);
                            $stmt_logs->bind_param("i", $id);
                            $stmt_logs->execute();
                            $sql_delete_logs = "DELETE FROM tutor_logs WHERE tutor_id = ?";
                            $stmt_delete_logs = $conn->prepare($sql_delete_logs);
                            $stmt_delete_logs->bind_param("i", $id);
                            $stmt_delete_logs->execute();

                            // Archive notifications
                            $sql_notifications = "INSERT INTO archive_notifications (id, sender_id, receiver_id, title, message, status, date_sent, sent_for)
                                                 SELECT id, sender_id, receiver_id, title, message, status, date_sent, sent_for FROM notifications WHERE receiver_id = ? AND sent_for = 'tutor'";
                            $stmt_notifications = $conn->prepare($sql_notifications);
                            $stmt_notifications->bind_param("i", $id);
                            $stmt_notifications->execute();
                            $sql_delete_notifications = "DELETE FROM notifications WHERE sender_id = ? OR receiver_id = ?";
                            $stmt_delete_notifications = $conn->prepare($sql_delete_notifications);
                            $stmt_delete_notifications->bind_param("ii", $id, $id);
                            $stmt_delete_notifications->execute();

                            // Delete the tutor from the tutor table after archiving
                            $sql_delete_tutor = "DELETE FROM tutor WHERE id = ?";
                            $stmt_delete_tutor = $conn->prepare($sql_delete_tutor);
                            $stmt_delete_tutor->bind_param("i", $id);
                            $stmt_delete_tutor->execute();

                            // Keep track of successfully archived tutor IDs
                            $archived_tutor_ids[] = $id;
                        } else {
                            throw new Exception("Error archiving tutor with ID " . $id . ": " . $conn->error);
                        }
                    } else {
                        // Add duplicate student_id to the warning list
                        $duplicate_student_ids[] = $student_id;
                    }
                }
                $stmt_select->close();
            }

            // Commit the transaction
            $conn->commit();

        } catch (Exception $e) {
            // Rollback the transaction in case of any errors
            $conn->rollback();
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }

        // Close prepared statements
        $stmt_archive->close();
        $stmt_check->close();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No tutors selected']);
    }
}

header('location:tutor');
?>
