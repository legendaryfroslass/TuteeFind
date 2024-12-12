<?php
include 'includes/session.php';

if (isset($_POST['restoreTutee'])) {
    $id = $_POST['id'];

    // Fetch the tutee's data from the archive
    $sql = "SELECT * FROM archive_tutee WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id); // Use integer for the ID
    $stmt->execute();
    $query = $stmt->get_result();

    if ($query->num_rows > 0) {
        $row = $query->fetch_assoc();

        // Prepare the insert statement for restoring tutee
        $sql_restore_tutee = "INSERT INTO tutee (id, firstname, lastname, age, sex, number, guardianname, fblink, barangay, tutee_bday, school, grade, emailaddress, photo, password, bio, address, last_login) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt_restore_tutee = $conn->prepare($sql_restore_tutee);
        $stmt_restore_tutee->bind_param(
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

        if ($stmt_restore_tutee->execute()) {
            // Restore notifications for the tutee
            $sql_restore_notifications = "INSERT INTO notifications (id, sender_id, receiver_id, title, message, status, date_sent, sent_for)
                                          SELECT id, sender_id, receiver_id, title, message, status, date_sent, sent_for FROM archive_notifications WHERE receiver_id = ? AND sent_for = 'tutee'";
            $stmt_restore_notifications = $conn->prepare($sql_restore_notifications);
            $stmt_restore_notifications->bind_param("i", $id);

            if (!$stmt_restore_notifications->execute()) {
                $_SESSION['error'] = 'Error restoring notifications: ' . $conn->error;
                $conn->rollback();
                exit();
            }

            // Restore tutee logs
            $sql_restore_logs = "INSERT INTO tutee_logs (id, tutee_id, activity, datetime)
                                 SELECT id, tutee_id, activity, datetime FROM archive_tutee_logs WHERE tutee_id = ?";
            $stmt_restore_logs = $conn->prepare($sql_restore_logs);
            $stmt_restore_logs->bind_param("i", $id);

            if (!$stmt_restore_logs->execute()) {
                $_SESSION['error'] = 'Error restoring tutee logs: ' . $conn->error;
                $conn->rollback();
                exit();
            }

            // Delete from the archive_tutee table after restoring
            $sql_delete_tutee = "DELETE FROM archive_tutee WHERE id = ?";
            $stmt_delete_tutee = $conn->prepare($sql_delete_tutee);
            $stmt_delete_tutee->bind_param("i", $id);

            if (!$stmt_delete_tutee->execute()) {
                $_SESSION['error'] = 'Error deleting tutee from archive: ' . $conn->error;
                $conn->rollback();
                exit();
            }

            // Delete from the archive_notifications table after restoring
            $sql_delete_notifications = "DELETE FROM archive_notifications WHERE receiver_id = ? AND sent_for = 'tutee'";
            $stmt_delete_notifications = $conn->prepare($sql_delete_notifications);
            $stmt_delete_notifications->bind_param("i", $id);

            if (!$stmt_delete_notifications->execute()) {
                $_SESSION['error'] = 'Error deleting notifications from archive: ' . $conn->error;
                $conn->rollback();
                exit();
            }

            // Delete from the archive_tutee_logs table after restoring
            $sql_delete_logs = "DELETE FROM archive_tutee_logs WHERE tutee_id = ?";
            $stmt_delete_logs = $conn->prepare($sql_delete_logs);
            $stmt_delete_logs->bind_param("i", $id);

            if (!$stmt_delete_logs->execute()) {
                $_SESSION['error'] = 'Error deleting tutee logs from archive: ' . $conn->error;
                $conn->rollback();
                exit();
            }

            $_SESSION['success'] = 'Tutee and associated data restored successfully';
        } else {
            $_SESSION['error'] = 'Error restoring tutee: ' . $conn->error;
        }
    } else {
        $_SESSION['error'] = 'Tutee not found in archive';
    }
} else {
    $_SESSION['error'] = 'Select item to restore first';
}

header('location: archive_tutee.php');
exit();
?>
