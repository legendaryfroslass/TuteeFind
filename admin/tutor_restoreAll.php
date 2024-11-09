<?php
include 'includes/session.php';

if (isset($_POST['restoreAllTutor'])) {
    // Begin a transaction to ensure data integrity
    $conn->begin_transaction();

    try {
        // Fetch all tutors from archive_tutor table
        $sql_select = "SELECT * FROM archive_tutor";
        $query = $conn->query($sql_select);

        if ($query->num_rows > 0) {
            // Prepare the insert statement for the tutor table
            $stmt_restore = $conn->prepare("INSERT INTO tutor (lastname, firstname, age, sex, number, barangay, student_id, course, year_section, professor, fblink, emailaddress, password) 
                                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

            // Loop through each archived tutor and insert into tutor table
            while ($row = $query->fetch_assoc()) {
                $stmt_restore->bind_param(
                    "sssisssssssss",
                    $row['lastname'],
                    $row['firstname'],
                    $row['age'],
                    $row['sex'],
                    $row['number'],
                    $row['barangay'],
                    $row['student_id'],
                    $row['course'],
                    $row['year_section'],
                    $row['professor'],
                    $row['fblink'],
                    $row['emailaddress'],
                    $row['password']
                );

                if (!$stmt_restore->execute()) {
                    throw new Exception("Error restoring tutor with Student ID ".$row['student_id'].": ".$conn->error);
                }
            }

            // Delete all entries from the archive_tutor table
            $sql_delete = "DELETE FROM archive_tutor";
            if ($conn->query($sql_delete)) {
                // Commit the transaction
                $conn->commit();
                $_SESSION['success'] = "All archived tutors restored successfully";
            } else {
                throw new Exception("Error deleting archived tutors: ".$conn->error);
            }
        } else {
            $_SESSION['error'] = "No archived tutors found to restore";
        }
    } catch (Exception $e) {
        // Rollback the transaction if there was an error
        $conn->rollback();
        $_SESSION['error'] = $e->getMessage();
    }
} else { 
    $_SESSION['error'] = 'Action not specified';
}

header('location:archive_tutor.php');
exit();
?>
