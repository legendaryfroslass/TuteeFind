<?php
include 'includes/session.php';

if (isset($_POST['restore'])) {
    $id = $_POST['id'];

    // Fetch the professor's data from archive_professor table
    $sql = "SELECT * FROM archive_professor WHERE id = '$id'";
    $query = $conn->query($sql);
    $row = $query->fetch_assoc();

    if ($row) {
        // Begin a transaction to ensure data integrity
        $conn->begin_transaction();

        try {
            // Insert the professor data into the professor table
            $sql_restore = "INSERT INTO professor (id, lastname, firstname, middlename, faculty_id, age, emailaddress, prof_username, prof_password) 
                            VALUES ('" . $row['id'] . "','" . $row['lastname'] . "', '" . $row['firstname'] . "', '" . $row['middlename'] . "', '" . $row['faculty_id'] . "', '" . $row['age'] . "', '" . $row['emailaddress'] . "', '" . $row['prof_username'] . "', '" . $row['prof_password'] . "')";

            if ($conn->query($sql_restore)) {
                // Fetch the professor's logs from archive_professor_logs
                $sql_logs = "SELECT * FROM archive_professor_logs WHERE professor_id = '$id'";
                $query_logs = $conn->query($sql_logs);

                if ($query_logs->num_rows > 0) {
                    // Insert the logs back into the professor_logs table
                    while ($log = $query_logs->fetch_assoc()) {
                        $sql_restore_log = "INSERT INTO professor_logs (id, professor_id, activity, datetime) 
                                            VALUES ('" . $log['id'] . "','" . $log['professor_id'] . "', '" . $log['activity'] . "', '" . $log['datetime'] . "')";
                        $conn->query($sql_restore_log);
                    }

                    // Delete the logs from the archive_professor_logs table
                    $sql_delete_logs = "DELETE FROM archive_professor_logs WHERE professor_id = '$id'";
                    $conn->query($sql_delete_logs);
                }

                // Delete from the archive_professor table after restoring
                $sql_delete = "DELETE FROM archive_professor WHERE id = '$id'";
                if ($conn->query($sql_delete)) {
                    // Commit the transaction if all operations are successful
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
        $_SESSION['error'] = 'Professor not found in archive';
    }
} else {
    $_SESSION['error'] = 'Select item to restore first';
}

header('location: archive_professor.php');
exit();
?>
