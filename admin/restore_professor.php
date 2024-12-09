<?php
include 'includes/session.php';

if (isset($_POST['restore'])) {
    $id = $_POST['id'];

    // Fetch the professor's data from the archive
    $sql = "SELECT * FROM archive_professor WHERE id = '$id'";
    $query = $conn->query($sql);
    $row = $query->fetch_assoc();

    // Check if the faculty_id already exists in the professor table
    $check_sql = "SELECT * FROM professor WHERE faculty_id = '".$row['faculty_id']."'";
    $check_query = $conn->query($check_sql);

    if ($check_query->num_rows == 0) {
        // Begin a transaction to ensure data integrity
        $conn->begin_transaction();

        try {
            // Insert the professor back into the professor table
            $sql_restore = "INSERT INTO professor (id, lastname, firstname, middlename, faculty_id, age, emailaddress, prof_username, prof_password) 
                            VALUES ('".$row['id']."','".$row['lastname']."', '".$row['firstname']."', '".$row['middlename']."', '".$row['faculty_id']."', '".$row['age']."', '".$row['emailaddress']."', '".$row['prof_username']."', '".$row['prof_password']."')";

            if ($conn->query($sql_restore)) {
                // Restore the logs from archive_professor_logs
                $sql_logs = "SELECT * FROM archive_professor_logs WHERE professor_id = '$id'";
                $query_logs = $conn->query($sql_logs);

                if ($query_logs->num_rows > 0) {
                    // Insert the logs back into the professor_logs table
                    while ($log = $query_logs->fetch_assoc()) {
                        $sql_restore_log = "INSERT INTO professor_logs (id, professor_id, activity, datetime) 
                                            VALUES ('" . $log['id'] . "','" . $log['professor_id'] . "', '" . $log['activity'] . "', '" . $log['datetime'] . "')";
                        $conn->query($sql_restore_log);
                    }

                    // Delete the logs from the archive_professor_logs table after restoring
                    $sql_delete_logs = "DELETE FROM archive_professor_logs WHERE professor_id = '$id'";
                    $conn->query($sql_delete_logs);
                }

                // Delete from the archive_professor table after restoring
                $sql_delete = "DELETE FROM archive_professor WHERE id = '$id'";
                if ($conn->query($sql_delete)) {
                    // Commit the transaction
                    $conn->commit();
                    $_SESSION['success'] = 'Professor and their logs restored successfully';
                } else {
                    throw new Exception("Error deleting professor from archive: " . $conn->error);
                }
            } else {
                throw new Exception("Error restoring professor: " . $conn->error);
            }
        } catch (Exception $e) {
            // Rollback the transaction if there was an error
            $conn->rollback();
            $_SESSION['error'] = $e->getMessage();
        }
    } else {
        // Mention the duplicate faculty_id in the error message
        $_SESSION['error'] = 'The faculty ID "'.$row['faculty_id'].'" already exists in the professor table.';
    }
} else {
    $_SESSION['error'] = 'Select item to restore first';
}

header('location: archive_professor.php');
exit();
?>
