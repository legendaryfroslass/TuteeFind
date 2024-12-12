<?php
include 'includes/session.php';

if (isset($_POST['restoreAll']) && isset($_POST['selected_ids'])) {
    $selected_ids = json_decode($_POST['selected_ids'], true); // Decode the JSON array

    if (count($selected_ids) > 0) {
        // Begin a transaction to ensure data integrity
        $conn->begin_transaction();

        try {
            // Prepare the select statement to fetch archived tutees
            $sql_select = "SELECT * FROM archive_tutee WHERE id = ?";
            $stmt_select = $conn->prepare($sql_select);

            // Prepare the insert statement for the tutee table
            $stmt_restore = $conn->prepare("INSERT INTO tutee (id, firstname, lastname, age, sex, number, guardianname, fblink, barangay, tutee_bday, school, grade, emailaddress, photo, password, bio, address, last_login) 
                                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

            // Related tables
            $related_tables = [
                'notifications' => "INSERT INTO notifications (id, sender_id, receiver_id, title, message, status, date_sent, sent_for) 
                                    SELECT id, sender_id, receiver_id, title, message, status, date_sent, sent_for FROM archive_notifications WHERE receiver_id = ? AND sent_for = 'tutee'",
                'tutee_logs' => "INSERT INTO tutee_logs (id, tutee_id, activity, datetime) 
                                 SELECT id, tutee_id, activity, datetime FROM archive_tutee_logs WHERE tutee_id = ?"
            ];

            foreach ($selected_ids as $tutee_id) {
                // Bind the ID to the select query
                $stmt_select->bind_param("i", $tutee_id);
                $stmt_select->execute();
                $result = $stmt_select->get_result();

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        // Bind parameters for restoring the tutee
                        $stmt_restore->bind_param(
                            "ississssssssssssss",
                            $row['id'],
                            $row['firstname'],
                            $row['lastname'],
                            $row['age'],
                            $row['sex'],
                            $row['number'],
                            $row['guardianname'],
                            $row['fblink'],
                            $row['barangay'],
                            $row['tutee_bday'],
                            $row['school'],
                            $row['grade'],
                            $row['emailaddress'],
                            $row['photo'],
                            $row['password'],
                            $row['bio'],
                            $row['address'],
                            $row['last_login']
                        );

                        if (!$stmt_restore->execute()) {
                            throw new Exception("Error restoring tutee with ID {$tutee_id}: " . $conn->error);
                        }

                        // Restore related data for the tutee
                        foreach ($related_tables as $table => $restore_query) {
                            $stmt_related = $conn->prepare($restore_query);
                            $stmt_related->bind_param("i", $tutee_id);
                            if (!$stmt_related->execute()) {
                                throw new Exception("Error restoring related data for tutee ID {$tutee_id} in table {$table}: " . $conn->error);
                            }
                        }

                        // Delete the tutee from the archive_tutee table after restoring
                        $sql_deleteTutee = "DELETE FROM archive_tutee WHERE id = ?";
                        $stmt_deleteTutee = $conn->prepare($sql_deleteTutee);
                        $stmt_deleteTutee->bind_param('i', $tutee_id);
                        $stmt_deleteTutee->execute();

                        // Delete the tutee from the archive_tutee table after restoring
                        $sql_deleteTuteelogs = "DELETE FROM archive_tutee_logs WHERE tutee_id = ?";
                        $stmt_deleteTuteelogs = $conn->prepare($sql_deleteTuteelogs);
                        $stmt_deleteTuteelogs->bind_param('i', $tutee_id);
                        $stmt_deleteTuteelogs->execute();

                        // Delete the tutee from the archive_tutee table after restoring
                        $sql_deleteTuteenotifications = "DELETE FROM archive_notifications WHERE receiver_id = ? AND sent_for = 'tutee'";
                        $stmt_deleteTuteenotifications = $conn->prepare($sql_deleteTuteenotifications);
                        $stmt_deleteTuteenotifications->bind_param('i', $tutee_id);
                        $stmt_deleteTuteenotifications->execute();
                    }
                }
            }

            // Commit the transaction
            $conn->commit();
            $_SESSION['success'] = "All selected archived tutees restored successfully.";
        } catch (Exception $e) {
            // Rollback the transaction if there was an error
            $conn->rollback();
            $_SESSION['error'] = $e->getMessage();
        }
    } else {
        $_SESSION['error'] = 'No tutees selected for restoration.';
    }
} else {
    $_SESSION['error'] = 'Action not specified.';
}

header('location: archive_tutee');
exit();
?>
