<?php
include 'includes/session.php';

if (isset($_POST['archiveAllTutee']) && isset($_POST['selected_ids'])) {
    $selected_ids = json_decode($_POST['selected_ids'], true); // Decode JSON array of IDs

    if (count($selected_ids) > 0) {
        // Start transaction
        $conn->begin_transaction();

        try {
            foreach ($selected_ids as $id) {
                // Archive tutee data into archive_tutee table
                $sql_archive = "INSERT INTO archive_tutee (id, firstname, lastname, age, sex, number, guardianname, fblink, barangay, tutee_bday, school, grade, emailaddress, photo, password, bio, address, last_login) 
                                SELECT id, firstname, lastname, age, sex, number, guardianname, fblink, barangay, tutee_bday, school, grade, emailaddress, photo, password, bio, address, last_login 
                                FROM tutee WHERE id = ?";
                $stmt_archive = $conn->prepare($sql_archive);
                $stmt_archive->bind_param('i', $id);
                $stmt_archive->execute();

                // Archive notifications related to the tutee
                $sql_notifications_archive = "INSERT INTO archive_notifications (id, sender_id, receiver_id, title, message, status, date_sent, sent_for)
                                              SELECT id, sender_id, receiver_id, title, message, status, date_sent, sent_for 
                                              FROM notifications WHERE receiver_id = ? AND sent_for = 'tutee'";
                $stmt_notifications = $conn->prepare($sql_notifications_archive);
                $stmt_notifications->bind_param('i', $id);
                $stmt_notifications->execute();

                // Delete notifications after archiving
                $sql_notifications_delete = "DELETE FROM notifications WHERE receiver_id = ? AND sent_for = 'tutee'";
                $stmt_delete_notifications = $conn->prepare($sql_notifications_delete);
                $stmt_delete_notifications->bind_param('i', $id);
                $stmt_delete_notifications->execute();

                // Archive logs related to the tutee
                $sql_logs_archive = "INSERT INTO archive_tutee_logs (id, tutee_id, activity, datetime)
                                     SELECT id, tutee_id, activity, datetime FROM tutee_logs WHERE tutee_id = ?";
                $stmt_logs = $conn->prepare($sql_logs_archive);
                $stmt_logs->bind_param('i', $id);
                $stmt_logs->execute();

                // Delete logs after archiving
                $sql_logs_delete = "DELETE FROM tutee_logs WHERE tutee_id = ?";
                $stmt_delete_logs = $conn->prepare($sql_logs_delete);
                $stmt_delete_logs->bind_param('i', $id);
                $stmt_delete_logs->execute();

                // Delete tutee from the tutee table
                $sql_delete_tutee = "DELETE FROM tutee WHERE id = ?";
                $stmt_delete_tutee = $conn->prepare($sql_delete_tutee);
                $stmt_delete_tutee->bind_param('i', $id);
                $stmt_delete_tutee->execute();
            }

            // Commit transaction
            $conn->commit();
            $_SESSION['success'] = 'Selected tutees and related records archived successfully';
        } catch (Exception $e) {
            // Rollback transaction if an error occurs
            $conn->rollback();
            $_SESSION['error'] = $e->getMessage();
        }
    } else {
        $_SESSION['error'] = 'No IDs selected for archiving.';
    }
} else {
    $_SESSION['error'] = 'No action performed to archive tutees.';
}

header('location: tutee');
exit();
?>
