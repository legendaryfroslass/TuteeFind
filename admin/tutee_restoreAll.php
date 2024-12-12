<?php
include 'includes/session.php';

if (isset($_POST['restoreAllTutee'])) {
    // Begin a transaction to ensure data integrity
    $conn->begin_transaction();

    try {
        // Fetch all tutees from archive_tutee table
        $sql_select = "SELECT * FROM archive_tutee";
        $query = $conn->query($sql_select);

        if ($query->num_rows > 0) {
            // Prepare the insert statement for the tutee table
            $stmt_restore = $conn->prepare("INSERT INTO tutee (id, firstname, lastname, age, sex, number, guardianname, fblink, barangay, tutee_bday, school, grade, emailaddress, photo, password, bio, address, last_login) 
                                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

            // Loop through each archived tutee and insert into tutee table
            while ($row = $query->fetch_assoc()) {
                // Insert tutee data
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
                    throw new Exception("Error restoring tutee with ID " . $row['id'] . ": " . $conn->error);
                }

                $tutee_id = $row['id']; // Store the tutee's ID for restoring related data

                // Restore related data for this tutee

                // Restore notifications
                $sql_restoreNotifications = "INSERT INTO notifications (id, sender_id, receiver_id, title, message, status, date_sent, sent_for)
                                             SELECT id, sender_id, receiver_id, title, message, status, date_sent, sent_for FROM archive_notifications WHERE receiver_id = '$tutee_id' AND sent_for = 'tutee'";
                $conn->query($sql_restoreNotifications);
                $sql_deleteNotifications = "DELETE FROM archive_notifications WHERE receiver_id = '$tutee_id' AND sent_for = 'tutee'";
                $conn->query($sql_deleteNotifications);

                // Restore tutee logs
                $sql_restoreLogs = "INSERT INTO tutee_logs (id, tutee_id, activity, datetime)
                                   SELECT id, tutee_id, activity, datetime FROM archive_tutee_logs WHERE tutee_id = '$tutee_id'";
                $conn->query($sql_restoreLogs);
                $sql_deleteLogs = "DELETE FROM archive_tutee_logs WHERE tutee_id = '$tutee_id'";
                $conn->query($sql_deleteLogs);

                // Delete the tutee from archive_tutee table after restoring
                $sql_deleteTutee = "DELETE FROM archive_tutee WHERE id = '$tutee_id'";
                $conn->query($sql_deleteTutee);
            }

            // Commit the transaction
            $conn->commit();
            $_SESSION['success'] = "All archived tutees restored successfully";
        } else {
            $_SESSION['error'] = "No archived tutees found to restore";
        }
    } catch (Exception $e) {
        // Rollback the transaction if there was an error
        $conn->rollback();
        $_SESSION['error'] = $e->getMessage();
    }
} else { 
    $_SESSION['error'] = 'Action not specified';
}

header('location: archive_tutee');
exit();
?>
