<?php
include 'includes/session.php';

if(isset($_POST['archiveAllTutee'])){
    // Start transaction
    $conn->begin_transaction();

    try {
        // Fetch all tutees to be archived
        $sql = "SELECT * FROM tutee";
        $query = $conn->query($sql);

        if($query->num_rows > 0){
            while($row = $query->fetch_assoc()){
                $id = $row['id'];

                // Insert each tutee's data into the archive_tutee table
                $sql_archive = "INSERT INTO archive_tutee (firstname, lastname, age, sex, number, guardianname, fblink, barangay, tutee_bday, school, grade, emailaddress, photo, password, bio, address) 
                                VALUES ('".$row['firstname']."', '".$row['lastname']."', '".$row['age']."', '".$row['sex']."', '".$row['number']."', '".$row['guardianname']."', '".$row['fblink']."', '".$row['barangay']."', '".$row['tutee_bday']."', '".$row['school']."', '".$row['grade']."', '".$row['emailaddress']."', '".$row['photo']."', '".$row['password']."', '".$row['bio']."', '".$row['address']."')";
                $conn->query($sql_archive);

                // Delete related records for each tutee
                $sql1 = "DELETE FROM requests WHERE tutee_id = '$id'";
                $conn->query($sql1);
                $sql2 = "DELETE FROM tutee_progress WHERE tutee_id = '$id'";
                $conn->query($sql2);
                $sql3 = "DELETE FROM tutee_summary WHERE tutee_id = '$id'";
                $conn->query($sql3);
                $sql4 = "DELETE FROM tutor_ratings WHERE tutee_id = '$id'";
                $conn->query($sql4);
                $sql5 = "DELETE FROM tutor_sessions WHERE tutee_id = '$id'";
                $conn->query($sql5);

                // Delete from the tutee table after archiving
                $sql_delete = "DELETE FROM tutee WHERE id = '$id'";
                $conn->query($sql_delete);
            }

            // Commit transaction
            $conn->commit();
            $_SESSION['success'] = 'All tutees and related records archived successfully';
        } else {
            throw new Exception('No tutees found to archive.');
        }
    } catch (mysqli_sql_exception $exception) {
        // Rollback transaction if an error occurs
        $conn->rollback();
        $_SESSION['error'] = $exception->getMessage();
    }
} else {
    $_SESSION['error'] = 'No action performed to archive tutees.';
}

header('location: tutee.php');
?>
