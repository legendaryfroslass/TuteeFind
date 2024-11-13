<?php
include 'includes/session.php';

if(isset($_POST['restore'])){
    $id = $_POST['id'];

    // Fetch the professor's data from the archive
    $sql = "SELECT * FROM archive_professor WHERE id = '$id'";
    $query = $conn->query($sql);
    $row = $query->fetch_assoc();

    // Check if the faculty_id already exists in the professor table
    $check_sql = "SELECT * FROM professor WHERE faculty_id = '".$row['faculty_id']."'";
    $check_query = $conn->query($check_sql);

    if($check_query->num_rows == 0) {
        // Insert back into the professor table
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
    } else {
        // Mention the duplicate faculty_id in the error message
        $_SESSION['error'] = 'The faculty ID "'.$row['faculty_id'].'" already exists in the professor table.';
    }
}
else{ 
    $_SESSION['error'] = 'Select item to restore first';
}

header('location: archive_professor.php');
?>
