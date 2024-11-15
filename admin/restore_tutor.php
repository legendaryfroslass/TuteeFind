<?php
include 'includes/session.php';

if(isset($_POST['restore'])){
    $id = $_POST['id'];

    // Fetch the tutor's data from the archive
    $sql = "SELECT * FROM archive_tutor WHERE id = '$id'";
    $query = $conn->query($sql);

    if($query->num_rows > 0){
        $row = $query->fetch_assoc();

        // Insert back into the tutor table
        $sql_restore = "INSERT INTO tutor (lastname, firstname, student_id, course, year_section, professor, fblink, emailaddress, password, bio, age, sex, number, barangay) 
        VALUES ('".$row['lastname']."', '".$row['firstname']."', '".$row['student_id']."', '".$row['course']."', '".$row['year_section']."', '".$row['professor']."', '".$row['fblink']."', '".$row['emailaddress']."', '".$row['password']."', '".$row['bio']."', '".$row['age']."', '".$row['sex']."', '".$row['number']."', '".$row['barangay']."')";

        if($conn->query($sql_restore)){
            // Delete from the archive_tutor table after restoring
            $sql_delete = "DELETE FROM archive_tutor WHERE id = '$id'";
            if($conn->query($sql_delete)){
                $_SESSION['success'] = 'Tutor restored successfully';
            }
            else{
                $_SESSION['error'] = $conn->error;
            }
        }
        else{
            $_SESSION['error'] = $conn->error;
        }
    } else {
        $_SESSION['error'] = 'Tutor not found in archive';
    }
}
else{ 
    $_SESSION['error'] = 'Select item to restore first';
}

header('location: archive_tutor.php');
?>
