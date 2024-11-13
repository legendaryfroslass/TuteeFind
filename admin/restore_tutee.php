<?php
include 'includes/session.php';

if(isset($_POST['restoreTutee'])){
    $id = $_POST['id'];

    // Fetch the tutee's data from the archive
    $sql = "SELECT * FROM archive_tutee WHERE id = '$id'";
    $query = $conn->query($sql);

    if($query->num_rows > 0){
        $row = $query->fetch_assoc();

        // Insert back into the tutee table
        $sql_restore = "INSERT INTO tutee (firstname, lastname, age, sex, number, guardianname, fblink, barangay, tutee_bday, school, grade, emailaddress, photo, password, bio) 
                        VALUES ('".$row['firstname']."', '".$row['lastname']."', '".$row['age']."', '".$row['sex']."', '".$row['number']."', '".$row['guardianname']."', '".$row['fblink']."', '".$row['barangay']."', '".$row['tutee_bday']."', '".$row['school']."', '".$row['grade']."', '".$row['emailaddress']."', '".$row['photo']."', '".$row['password']."', '".$row['bio']."')";

        if($conn->query($sql_restore)){
            // Delete from the archive_tutee table after restoring
            $sql_delete = "DELETE FROM archive_tutee WHERE id = '$id'";
            if($conn->query($sql_delete)){
                $_SESSION['success'] = 'Tutee restored successfully';
            }
            else{
                $_SESSION['error'] = $conn->error;
            }
        }
        else{
            $_SESSION['error'] = $conn->error;
        }
    } else {
        $_SESSION['error'] = 'Tutee not found in archive';
    }
}
else{ 
    $_SESSION['error'] = 'Select item to restore first';
}

header('location: archive_tutee.php');
?>
