<?php
include 'includes/session.php';

if (isset($_POST['archiveTutee'])) {
    $id = $_POST['id'];

    // Start transaction
    $conn->begin_transaction();

    try {
        // Fetch the tutee data to be archived
        $sql = "SELECT * FROM tutee WHERE id = '$id'";
        $query = $conn->query($sql);

        if ($query->num_rows > 0) {
            $row = $query->fetch_assoc();

            // Insert tutee data into the archive_tutee table
            $sql_archive = "INSERT INTO archive_tutee (id, firstname, lastname, age, sex, number, guardianname, fblink, barangay, tutee_bday, school, grade, emailaddress, photo, password, bio, address, last_login) 
                            VALUES ('".$row['id']."', '".$row['firstname']."', '".$row['lastname']."', '".$row['age']."', '".$row['sex']."', '".$row['number']."', '".$row['guardianname']."', '".$row['fblink']."', '".$row['barangay']."', '".$row['tutee_bday']."', '".$row['school']."', '".$row['grade']."', '".$row['emailaddress']."', '".$row['photo']."', '".$row['password']."', '".$row['bio']."', '".$row['address']."', '".$row['last_login']."')";
            $conn->query($sql_archive);

            // Archive and delete related records from notifications table
            $sql_notifications_archive = "INSERT INTO archive_notifications (id, sender_id, receiver_id, title, message, status, date_sent, sent_for)
                                          SELECT id, sender_id, receiver_id, title, message, status, date_sent, sent_for 
                                          FROM notifications WHERE receiver_id = '$id'";
            $conn->query($sql_notifications_archive);
            $sql_notifications_delete = "DELETE FROM notifications WHERE receiver_id = '$id' AND sent_for = 'tutee'";
            $conn->query($sql_notifications_delete);

            // Archive and delete related records from tutee_logs table
            $sql_logs_archive = "INSERT INTO archive_tutee_logs (id, tutee_id, activity, datetime)
                                 SELECT id, tutee_id, activity, datetime FROM tutee_logs WHERE tutee_id = '$id'";
            $conn->query($sql_logs_archive);
            $sql_logs_delete = "DELETE FROM tutee_logs WHERE tutee_id = '$id'";
            $conn->query($sql_logs_delete);

            // Delete the tutee from the tutee table after archiving
            $sql_delete_tutee = "DELETE FROM tutee WHERE id = '$id'";
            $conn->query($sql_delete_tutee);

            // Commit transaction
            $conn->commit();
            $_SESSION['success'] = 'Tutee and related records archived successfully';
        } else {
            throw new Exception('Tutee not found.');
        }
    } catch (Exception $e) {
        // Rollback transaction if an error occurs
        $conn->rollback();
        $_SESSION['error'] = $e->getMessage();
    }
} else {
    $_SESSION['error'] = 'Select item to archive first';
}

header('location: tutee');
exit();
?>
