<?php
include 'includes/session.php';

if(isset($_POST['archiveTutee'])){
    $id = $_POST['id'];

    // Start transaction
    $conn->begin_transaction();

    try {
        // Fetch the tutee data to be archived
        $sql = "SELECT * FROM tutee WHERE id = '$id'";
        $query = $conn->query($sql);

        if($query->num_rows > 0){
            $row = $query->fetch_assoc();

            // Insert tutee data into the archive_tutee table
            $sql_archive = "INSERT INTO archive_tutee (firstname, lastname, age, sex, number, guardianname, fblink, barangay, tutee_birthday, school, grade, emailaddress, photo, password) 
                            VALUES ('".$row['firstname']."', '".$row['lastname']."', '".$row['age']."', '".$row['sex']."', '".$row['number']."', '".$row['guardianname']."', '".$row['fblink']."', '".$row['barangay']."', '".$row['tutee_birthday']."', '".$row['school']."', '".$row['grade']."', '".$row['emailaddress']."', '".$row['photo']."', '".$row['password']."')";
            $conn->query($sql_archive);

            // Delete related records
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

            // Commit transaction
            $conn->commit();
            $_SESSION['success'] = 'Tutee and related records archived successfully';
        } else {
            throw new Exception('Tutee not found.');
        }
    } catch (mysqli_sql_exception $exception) {
        // Rollback transaction if an error occurs
        $conn->rollback();
        $_SESSION['error'] = $exception->getMessage();
    }
} else {
    $_SESSION['error'] = 'Select item to archive first';
}

header('location: tutee.php');
?>
