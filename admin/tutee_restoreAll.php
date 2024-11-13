<?php
include 'includes/session.php';

if(isset($_POST['restoreAllTutee'])){
    // Start transaction
    $conn->begin_transaction();

    try {
        // Fetch all tutees from the archive
        $sql = "SELECT * FROM archive_tutee";
        $query = $conn->query($sql);

        if($query->num_rows > 0){
            while($row = $query->fetch_assoc()){
                $id = $row['id'];

                // Insert each tutee back into the tutee table
                $sql_restore = "INSERT INTO tutee (firstname, lastname, age, sex, number, guardianname, fblink, barangay, tutee_bday, school, grade, emailaddress, photo, password, bio) 
                                VALUES ('".$row['firstname']."', '".$row['lastname']."', '".$row['age']."', '".$row['sex']."', '".$row['number']."', '".$row['guardianname']."', '".$row['fblink']."', '".$row['barangay']."', '".$row['tutee_bday']."', '".$row['school']."', '".$row['grade']."', '".$row['emailaddress']."', '".$row['photo']."', '".$row['password']."', '".$row['bio']."')";
                $conn->query($sql_restore);

                // Delete from the archive_tutee table after restoring
                $sql_delete = "DELETE FROM archive_tutee WHERE id = '$id'";
                $conn->query($sql_delete);
            }

            // Commit transaction
            $conn->commit();
            $_SESSION['success'] = 'All tutees restored successfully';
        } else {
            throw new Exception('No tutees found in archive');
        }
    } catch (mysqli_sql_exception $exception) {
        // Rollback transaction if an error occurs
        $conn->rollback();
        $_SESSION['error'] = $exception->getMessage();
    }
} else {
    $_SESSION['error'] = 'No action performed to restore tutees.';
}

header('location: archive_tutee.php');
?>
