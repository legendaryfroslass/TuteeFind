<?php
include 'includes/session.php';

if (isset($_POST['archive'])) {
    $id = $_POST['id'];

    // Start transaction
    $conn->begin_transaction();

    try {
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
                $stmt_archive = $conn->prepare("INSERT INTO archive_professor (id, lastname, firstname, middlename, faculty_id, age, emailaddress, prof_username, prof_password) 
                                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt_archive->bind_param(
                    "issssisss",
                    $row['id'],
                    $row['lastname'],
                    $row['firstname'],
                    $row['middlename'],
                    $row['faculty_id'],
                    $row['age'],
                    $row['emailaddress'],
                    $row['prof_username'],
                    $row['prof_password']
                );

                if ($stmt_archive->execute()) {
                    // Archive professor logs
                    $sql_fetch_logs = "SELECT * FROM professor_logs WHERE professor_id = '$id'";
                    $query_logs = $conn->query($sql_fetch_logs);

                    while ($log_row = $query_logs->fetch_assoc()) {
                        $stmt_archive_logs = $conn->prepare("INSERT INTO archive_professor_logs (id, professor_id, activity, datetime) 
                                                             VALUES (?, ?, ?, ?)");
                        $stmt_archive_logs->bind_param(
                            "iiss",
                            $log_row['id'],
                            $log_row['professor_id'],
                            $log_row['activity'],
                            $log_row['datetime']
                        );
                        $stmt_archive_logs->execute();
                    }

                    // Delete from professor_logs
                    $sql_delete_logs = "DELETE FROM professor_logs WHERE professor_id = '$id'";
                    $conn->query($sql_delete_logs);

                    // Delete from the professor table
                    $sql_delete = "DELETE FROM professor WHERE id = '$id'";
                    $conn->query($sql_delete);

                    // Commit transaction
                    $conn->commit();
                    $_SESSION['success'] = 'Professor with the Faculty ID "' . $faculty_id . '" and related logs archived successfully';
                } else {
                    throw new Exception($conn->error);
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
    } catch (Exception $e) {
        // Rollback transaction if an error occurs
        $conn->rollback();
        $_SESSION['error'] = $e->getMessage();
    }
} else {
    $_SESSION['error'] = 'Select item to archive first';
}

header('location: professor.php');
exit();
?>
