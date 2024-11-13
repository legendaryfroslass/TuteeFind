<?php
include 'includes/session.php';

if (isset($_POST['restoreAll']) && isset($_POST['selected_ids'])) {
    $selected_ids = json_decode($_POST['selected_ids'], true); // Decode the JSON array

    if (count($selected_ids) > 0) {
        // Begin a transaction to ensure data integrity
        $conn->begin_transaction();

        try {
            // Prepare the insert statement for the professor table
            $stmt_restore = $conn->prepare("INSERT INTO professor (lastname, firstname, middlename, faculty_id, age, emailaddress. prof_username, prof_password) 
                                            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

            // Prepare a check for existing faculty_id in the professor table
            $stmt_check = $conn->prepare("SELECT faculty_id FROM professor WHERE faculty_id = ?");

            // Arrays to store duplicated faculty IDs and successfully restored professor IDs
            $duplicate_faculty_ids = [];
            $restored_professor_ids = [];

            // Loop through each selected archived professor ID
            foreach ($selected_ids as $id) {
                // Fetch professor data based on the selected ID
                $sql_select = "SELECT * FROM archive_professor WHERE id = ?";
                $stmt_select = $conn->prepare($sql_select);
                $stmt_select->bind_param("i", $id);
                $stmt_select->execute();
                $result = $stmt_select->get_result();

                if ($result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    $faculty_id = $row['faculty_id'];

                    // Check if the faculty_id already exists in the professor table
                    $stmt_check->bind_param("s", $faculty_id);
                    $stmt_check->execute();
                    $stmt_check->store_result();

                    if ($stmt_check->num_rows === 0) {
                        // If faculty_id does not exist, proceed to restore
                        $stmt_restore->bind_param(
                            "sssiisss",
                            $row['lastname'],
                            $row['firstname'],
                            $row['middlename'],
                            $row['faculty_id'],
                            $row['age'],
                            $row['emailaddress'],
                            $row['prof_username'],
                            $row['prof_password']
                        );

                        if ($stmt_restore->execute()) {
                            // Track successfully restored professor IDs
                            $restored_professor_ids[] = $row['id'];
                        } else {
                            throw new Exception("Error restoring professor with Faculty ID " . $faculty_id . ": " . $conn->error);
                        }
                    } else {
                        // Add duplicate faculty_id to the warning list
                        $duplicate_faculty_ids[] = $faculty_id;
                    }
                }
                $stmt_select->close();
            }

            // Check if any professors were restored
            if (!empty($restored_professor_ids)) {
                // Delete only the entries that were successfully restored from the archive_professor table
                $ids_to_delete = implode(",", $restored_professor_ids);
                $sql_delete = "DELETE FROM archive_professor WHERE id IN ($ids_to_delete)";

                if ($conn->query($sql_delete)) {
                    // Commit the transaction
                    $conn->commit();

                    // Success message, including warning if there were duplicates
                    $success_message = "Selected professors were restored successfully.";
                    if (!empty($duplicate_faculty_ids)) {
                        $success_message .= " However, the following faculty IDs were already in the professor table: " . implode(", ", $duplicate_faculty_ids);
                    }
                    $_SESSION['success'] = $success_message;
                } else {
                    throw new Exception("Error deleting archived professors: " . $conn->error);
                }
            } else {
                // No professors were restored due to duplicates
                if (!empty($duplicate_faculty_ids)) {
                    $_SESSION['error'] = "No professors were restored because the faculty IDs were already in the professor table: " . implode(", ", $duplicate_faculty_ids);
                } else {
                    $_SESSION['error'] = "No professors selected for restoration.";
                }
            }

            $stmt_check->close();
            $stmt_restore->close();
        } catch (Exception $e) {
            // Rollback the transaction if there was an error
            $conn->rollback();
            $_SESSION['error'] = $e->getMessage();
        }
    } else {
        $_SESSION['error'] = 'No professors selected for restoration.';
    }
} else {
    $_SESSION['error'] = 'Action not specified or no professors selected.';
}

// Redirect to archive_professor.php
header('location: archive_professor.php');
exit();
?>
