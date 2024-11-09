<?php
include 'includes/session.php';

if (isset($_POST['archiveAll']) && isset($_POST['selected_ids'])) {
    $selected_ids = json_decode($_POST['selected_ids'], true); // Decode the JSON array

    if (count($selected_ids) > 0) {
        // Begin a transaction to ensure data integrity
        $conn->begin_transaction();

        try {
            // Prepare the insert statement for the archive
            $stmt_archive = $conn->prepare("INSERT INTO archive_tutor (lastname, firstname, age, sex, number, barangay, student_id, course, year_section, professor, fblink, emailaddress, password) 
                                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

            // Prepare a check for existing student_id in the archive_tutor table
            $stmt_check = $conn->prepare("SELECT student_id FROM archive_tutor WHERE student_id = ?");

            // Arrays to store duplicated student IDs and successfully archived tutor IDs
            $duplicate_student_ids = [];
            $archived_tutor_ids = [];

            // Loop through each selected tutor ID
            foreach ($selected_ids as $id) {
                // Fetch tutor data based on the selected ID
                $sql_select = "SELECT * FROM tutor WHERE id = ?";
                $stmt_select = $conn->prepare($sql_select);
                $stmt_select->bind_param("i", $id);
                $stmt_select->execute();
                $result = $stmt_select->get_result();

                if ($result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    $student_id = $row['student_id'];

                    // Check if the student_id already exists in the archive_tutor table
                    $stmt_check->bind_param("s", $student_id);
                    $stmt_check->execute();
                    $stmt_check->store_result();

                    if ($stmt_check->num_rows === 0) {
                        // If student_id does not exist, proceed to archive
                        $stmt_archive->bind_param(
                            "ssissssssssss",
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

                        if ($stmt_archive->execute()) {
                            // Keep track of successfully archived tutor IDs
                            $archived_tutor_ids[] = $id;
                        } else {
                            throw new Exception("Error archiving tutor with ID " . $id . ": " . $conn->error);
                        }
                    } else {
                        // Add duplicate student_id to the warning list
                        $duplicate_student_ids[] = $student_id;
                    }
                }
                $stmt_select->close();
            }

            // Check if any tutors were archived
            if (!empty($archived_tutor_ids)) {
                // Delete only tutors who were successfully archived
                $ids_to_delete = implode(",", $archived_tutor_ids);
                $sql_delete = "DELETE FROM tutor WHERE id IN ($ids_to_delete)";

                if ($conn->query($sql_delete)) {
                    // Commit the transaction
                    $conn->commit();

                    // Success message, including warning if there were duplicates
                    $success_message = "Selected tutors were archived successfully.";
                    if (!empty($duplicate_student_ids)) {
                        $success_message .= " However, the following student IDs were already archived: " . implode(", ", $duplicate_student_ids);
                    }
                    $_SESSION['success'] = $success_message;
                } else {
                    throw new Exception("Error in deleting tutors: " . $conn->error);
                }
            } else {
                // No tutors were archived due to duplicates
                if (!empty($duplicate_student_ids)) {
                    $_SESSION['error'] = "No tutors were archived because the student IDs were already in the archive: " . implode(", ", $duplicate_student_ids);
                } else {
                    $_SESSION['error'] = "No tutors selected for archiving.";
                }
            }

            $stmt_check->close();
            $stmt_archive->close();
        } catch (Exception $e) {
            // Rollback the transaction if there was an error
            $conn->rollback();
            $_SESSION['error'] = $e->getMessage();
        }
    } else {
        $_SESSION['error'] = 'No tutors selected for archiving.';
    }
} else {
    $_SESSION['error'] = 'Action not specified or no tutors selected.';
}

// Redirect to tutor.php
header('location: tutor.php');
exit();
?>
