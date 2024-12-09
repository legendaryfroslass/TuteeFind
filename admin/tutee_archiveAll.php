<?php
include 'includes/session.php';

if (isset($_POST['archiveAllTutee'])) {
    // Start transaction
    $conn->begin_transaction();

    try {
        // Fetch all tutees to be archived
        $sql = "SELECT * FROM tutee";
        $query = $conn->query($sql);

        if ($query->num_rows > 0) {
            while ($row = $query->fetch_assoc()) {
                $id = $row['id'];

                // Insert each tutee's data into the archive_tutee table
                $sql_archive = "INSERT INTO archive_tutee (id, firstname, lastname, age, sex, number, guardianname, fblink, barangay, tutee_bday, school, grade, emailaddress, photo, password, bio, address) 
                                VALUES ('".$row['id']."','".$row['firstname']."', '".$row['lastname']."', '".$row['age']."', '".$row['sex']."', '".$row['number']."', '".$row['guardianname']."', '".$row['fblink']."', '".$row['barangay']."', '".$row['tutee_bday']."', '".$row['school']."', '".$row['grade']."', '".$row['emailaddress']."', '".$row['photo']."', '".$row['password']."', '".$row['bio']."', '".$row['address']."')";
                $conn->query($sql_archive);

                // Delete related records for each tutee
                $related_tables = [
                    "requests" => "tutee_id",
                    "tutee_progress" => "tutee_id",
                    "tutee_summary" => "tutee_id",
                    "tutor_ratings" => "tutee_id",
                    "tutor_sessions" => "tutee_id",
                    "messages" => "tutee_id",
                    "notifications" => "receiver_id", // Corrected to use `receiver_id`
                    "tutee_logs" => "tutee_id"
                ];

                foreach ($related_tables as $table => $column) {
                    $sql_delete_related = "DELETE FROM $table WHERE $column = '$id'";
                    $conn->query($sql_delete_related);
                }

                // Delete from the tutee table after archiving
                $sql_delete_tutee = "DELETE FROM tutee WHERE id = '$id'";
                $conn->query($sql_delete_tutee);
            }

            // Commit transaction
            $conn->commit();
            $_SESSION['success'] = 'All tutees and related records archived successfully';
        } else {
            throw new Exception('No tutees found to archive.');
        }
    } catch (Exception $e) {
        // Rollback transaction if an error occurs
        $conn->rollback();
        $_SESSION['error'] = $e->getMessage();
    }
} else {
    $_SESSION['error'] = 'No action performed to archive tutees.';
}

header('location: tutee.php');
exit();
?>
