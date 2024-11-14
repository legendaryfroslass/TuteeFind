<?php
include 'includes/session.php';

if (isset($_POST['restoreTutor'])) {
    $id = $_POST['id'];

    // Fetch the tutor's data from archive_tutor table
    $sql = "SELECT * FROM archive_tutor WHERE id = '$id'";
    $query = $conn->query($sql);
    $row = $query->fetch_assoc();

    if ($row) {
        // Insert into tutor table
        $sql_restoreTutor = "INSERT INTO tutor (lastname, firstname, age, sex, number, barangay, student_id, course, year_section, professor, fblink, emailaddress, password, bio) 
                        VALUES ('".$row['lastname']."', '".$row['firstname']."', '".$row['age']."', '".$row['sex']."', '".$row['number']."', '".$row['barangay']."', '".$row['student_id']."', '".$row['course']."', '".$row['year_section']."', '".$row['professor']."', '".$row['fblink']."', '".$row['emailaddress']."', '".$row['password']."', '".$row['bio']."')";

        if ($conn->query($sql_restoreTutor)) {
            // Delete from the archive_tutor table after restoring
            $sql_delete = "DELETE FROM archive_tutor WHERE id = '$id'";
            if ($conn->query($sql_delete)) {
                $_SESSION['success'] = 'Tutor restored successfully';
            } else {
                $_SESSION['error'] = $conn->error;
            }
        } else {
            $_SESSION['error'] = $conn->error;
        }
    } else {
        $_SESSION['error'] = 'Tutor not found in archive';
    }
} else {
    $_SESSION['error'] = 'Select item to restore first';
}

header('location: archive_tutor.php');
exit();
?>
