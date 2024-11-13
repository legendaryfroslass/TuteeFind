<?php
include 'includes/session.php';

if(isset($_POST['restore'])){
    $id = $_POST['id'];

    // Fetch the professor's data from archive_professor table
    $sql = "SELECT * FROM archive_professor WHERE id = '$id'";
    $query = $conn->query($sql);
    $row = $query->fetch_assoc();

    if($row){
        // Insert into professor table
        $sql_restore = "INSERT INTO professor (lastname, firstname, middlename, faculty_id, age, prof_username, prof_password) 
                        VALUES ('".$row['lastname']."', '".$row['firstname']."', '".$row['middlename']."', '".$row['faculty_id']."', '".$row['age']."', '".$row['prof_username']."', '".$row['prof_password']."')";

        if($conn->query($sql_restore)){
            // Delete from the archive_professor table after restoring
            $sql_delete = "DELETE FROM archive_professor WHERE id = '$id'";
            if($conn->query($sql_delete)){
                $_SESSION['success'] = 'Professor restored successfully';
            }
            else{
                $_SESSION['error'] = $conn->error;
            }
        }
        else{
            $_SESSION['error'] = $conn->error;
        }
    }
    else{ 
        $_SESSION['error'] = 'Professor not found in archive';
    }
}
else{ 
    $_SESSION['error'] = 'Select item to restore first';
}

header('location: archive_professor.php');
?>
