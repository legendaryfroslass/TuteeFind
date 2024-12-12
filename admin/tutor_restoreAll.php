<?php
include 'includes/session.php';

if (isset($_POST['restoreAll']) && isset($_POST['selected_ids'])) {
    $selected_ids = json_decode($_POST['selected_ids'], true); // Decode the JSON array

    if (count($selected_ids) > 0) {
        // Begin a transaction to ensure data integrity
        $conn->begin_transaction();

        try {
            // Prepare the select statement to fetch archived tutors
            $sql_select = "SELECT * FROM archive_tutor WHERE id = ?";
            $stmt_select = $conn->prepare($sql_select);

            // Prepare the insert statement for the tutor table
            $stmt_restore = $conn->prepare("INSERT INTO tutor (id, lastname, firstname, age, sex, number, barangay, student_id, course, year_section, professor, fblink, emailaddress, password, bio, last_login) 
                                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

            foreach ($selected_ids as $tutor_id) {
                // Bind the ID to the select query
                $stmt_select->bind_param("i", $tutor_id);
                $stmt_select->execute();
                $result = $stmt_select->get_result();

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        // Bind parameters for restoring the tutor
                        $stmt_restore->bind_param(
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

                        if (!$stmt_restore->execute()) {
                            throw new Exception("Error restoring tutor with ID {$tutor_id}: " . $conn->error);
                        }

                        // Restore related data for the tutor (use proper prepared statements)
                        $related_tables = [
                            'requests' => "INSERT INTO requests (request_id, tutor_id, tutee_id, status) SELECT request_id, tutor_id, tutee_id, status FROM archive_requests WHERE tutor_id = ?",
                            'tutee_progress' => "INSERT INTO tutee_progress (id, tutee_id, tutor_id, week_number, uploaded_files, description, date, rendered_hours, location, subject, status, remarks) 
                                                 SELECT id, tutee_id, tutor_id, week_number, uploaded_files, description, date, rendered_hours, location, subject, status, remarks FROM archive_tutee_progress WHERE tutor_id = ?",
                            'tutor_ratings' => "INSERT INTO tutor_ratings (id, tutee_id, tutor_id, rating, comment, pdf_content) 
                                                SELECT id, tutee_id, tutor_id, rating, comment, pdf_content FROM archive_tutor_ratings WHERE tutor_id = ?",
                            'tutor_sessions' => "INSERT INTO tutor_sessions (id, tutor_id, tutee_id, status) 
                                                 SELECT id, tutor_id, tutee_id, status FROM archive_tutor_sessions WHERE tutor_id = ?",
                            'tutee_summary' => "INSERT INTO tutee_summary (tutee_id, tutor_id, completed_weeks, registered_weeks) 
                                                SELECT tutee_id, tutor_id, completed_weeks, registered_weeks FROM archive_tutee_summary WHERE tutor_id = ?",
                            'events' => "INSERT INTO events (id, tutor_id, event_name, rendered_hours, description, attached_file, created_at, status, remarks) 
                                         SELECT id, tutor_id, event_name, rendered_hours, description, attached_file, created_at, status, remarks FROM archive_events WHERE tutor_id = ?",
                            'notifications' => "INSERT INTO notifications (id, sender_id, receiver_id, title, message, status, date_sent, sent_for) 
                                                SELECT id, sender_id, receiver_id, title, message, status, date_sent, sent_for FROM archive_notifications WHERE receiver_id = ? AND sent_for = 'tutor'",
                            'tutor_logs' => "INSERT INTO tutor_logs (id, tutor_id, activity, datetime) 
                                             SELECT id, tutor_id, activity, datetime FROM archive_tutor_logs WHERE tutor_id = ?"
                        ];

                        foreach ($related_tables as $table => $restore_query) {
                            $stmt_related = $conn->prepare($restore_query);
                            $stmt_related->bind_param("i", $tutor_id);
                            if (!$stmt_related->execute()) {
                                throw new Exception("Error restoring related data for tutor ID {$tutor_id} in table {$table}: " . $conn->error);
                            }
                        }

                        // Delete the tutor and related archived data
                        $archive_delete_queries = [
                            "DELETE FROM archive_tutor WHERE id = ?",
                            "DELETE FROM archive_requests WHERE tutor_id = ?",
                            "DELETE FROM archive_tutee_progress WHERE tutor_id = ?",
                            "DELETE FROM archive_tutor_ratings WHERE tutor_id = ?",
                            "DELETE FROM archive_tutor_sessions WHERE tutor_id = ?",
                            "DELETE FROM archive_tutee_summary WHERE tutor_id = ?",
                            "DELETE FROM archive_events WHERE tutor_id = ?",
                            "DELETE FROM archive_notifications WHERE receiver_id = ? AND sent_for = 'tutor'",
                            "DELETE FROM archive_tutor_logs WHERE tutor_id = ?"
                        ];

                        foreach ($archive_delete_queries as $delete_query) {
                            $stmt_delete = $conn->prepare($delete_query);
                            $stmt_delete->bind_param("i", $tutor_id);
                            if (!$stmt_delete->execute()) {
                                throw new Exception("Error deleting archived data for tutor ID {$tutor_id}: " . $conn->error);
                            }
                        }
                    }
                }
            }

            // Commit the transaction
            $conn->commit();
            $_SESSION['success'] = "All selected archived tutors restored successfully.";
        } catch (Exception $e) {
            // Rollback the transaction if there was an error
            $conn->rollback();
            $_SESSION['error'] = $e->getMessage();
        }
    } else {
        $_SESSION['error'] = 'No tutors selected for restoration.';
    }
} else {
    $_SESSION['error'] = 'Action not specified.';
}

header('location: archive_tutor');
exit();
?>
