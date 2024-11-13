<?php
include 'includes/session.php';

if (isset($_POST['archive'])) {
    $id = $_POST['id'];

    // Fetch the professor's data
    $sql = "SELECT * FROM professor WHERE id = '$id'";
    $query = $conn->query($sql);
    $row = $query->fetch_assoc();

    if ($row) {
        // Check if the faculty_id already exists in the archive_professor table
        $faculty_id = $row['faculty_id'];
        $stmt_check = $conn->prepare("SELECT faculty_id FROM archive_professor WHERE faculty_id = ?");
        $stmt_check->bind_param("s", $faculty_id);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows === 0) {
            // If faculty_id does not exist, proceed to archive
            $stmt_archive = $conn->prepare("INSERT INTO archive_professor (lastname, firstname, middlename, faculty_id, age, prof_username, prof_password) 
                                            VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt_archive->bind_param(
                "ssssiisss",
                $row['lastname'],
                $row['firstname'],
                $row['middlename'],
                $row['faculty_id'],
                $row['age'],
                $row['prof_username'],
                $row['prof_password']
            );

            if ($stmt_archive->execute()) {
                // Delete from the activity_logs table first
                $sql_delete_logs = "DELETE FROM activity_logs WHERE professor_id = '$id'";
                if ($conn->query($sql_delete_logs)) {
                    // Now delete from the professor table
                    $sql_delete = "DELETE FROM professor WHERE id = '$id'";
                    if ($conn->query($sql_delete)) {
                        $_SESSION['success'] = 'Professor with the Faculty ID "' . $faculty_id . '" archived successfully';
                    } else {
                        $_SESSION['error'] = $conn->error;
                    }
                } else {
                    $_SESSION['error'] = $conn->error;
                }
            } else {
                $_SESSION['error'] = $conn->error;
            }

            $stmt_archive->close();
        } else {
            // Mention the duplicate faculty_id in the error message
            $_SESSION['error'] = 'Faculty ID "' . $faculty_id . '" already exists in the archive.';
        }

        $stmt_check->close();
    } else {
        $_SESSION['error'] = 'Professor not found.';
    }
} else {
    $_SESSION['error'] = 'Select item to archive first';
}

header('location: professor.php');
exit();
?>
