<?php
include 'includes/session.php';

if (isset($_POST['archiveAll']) && isset($_POST['selected_ids'])) {
    $selected_ids = json_decode($_POST['selected_ids'], true); // Decode the JSON array
   
    if (count($selected_ids) > 0) {
        // Begin a transaction to ensure data integrity
        $conn->begin_transaction();

        try {
            // Prepare the insert statement for the archive
            $stmt_archive = $conn->prepare("INSERT INTO archive_professor (lastname, firstname, middlename, faculty_id, age, emailaddress, prof_username, prof_password) 
                                            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

            // Prepare a check for existing faculty_id in the archive_professor table
            $stmt_check = $conn->prepare("SELECT faculty_id FROM archive_professor WHERE faculty_id = ?");

            // Arrays to store duplicated faculty IDs and successfully archived professor IDs
            $duplicate_faculty_ids = [];
            $archived_professor_ids = [];

            // Loop through each selected professor ID
            foreach ($selected_ids as $id) {
                // Fetch professor data based on the selected ID
                $sql_select = "SELECT * FROM professor WHERE id = ?";
                $stmt_select = $conn->prepare($sql_select);
                $stmt_select->bind_param("i", $id);
                $stmt_select->execute();
                $result = $stmt_select->get_result();

                if ($result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    $faculty_id = $row['faculty_id'];

                    // Check if the faculty_id already exists in the archive_professor table
                    $stmt_check->bind_param("s", $faculty_id);
                    $stmt_check->execute();
                    $stmt_check->store_result();

                    if ($stmt_check->num_rows === 0) {
                        // If faculty_id does not exist, proceed to archive
                        $stmt_archive->bind_param(
                            "ssssisss",
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
                            // Keep track of successfully archived professor IDs
                            $archived_professor_ids[] = $id;
                        } else {
                            throw new Exception("Error archiving professor with ID " . $id . ": " . $conn->error);
                        }
                    } else {
                        // Add duplicate faculty_id to the warning list
                        $duplicate_faculty_ids[] = $faculty_id;
                    }
                }
                $stmt_select->close();
            }

            // Check if any professors were archived
            if (!empty($archived_professor_ids)) {
                // Delete only professors who were successfully archived
                $ids_to_delete = implode(",", $archived_professor_ids);
                $sql_delete = "DELETE FROM professor WHERE id IN ($ids_to_delete)";

                if ($conn->query($sql_delete)) {
                    // Commit the transaction
                    $conn->commit();

                    // Success message, including warning if there were duplicates
                    $success_message = "Selected professors were archived successfully.";
                    if (!empty($duplicate_faculty_ids)) {
                        $success_message .= " However, the following faculty IDs were already archived: " . implode(", ", $duplicate_faculty_ids);
                    }
                    $_SESSION['success'] = $success_message;
                } else {
                    throw new Exception("Error in deleting professors: " . $conn->error);
                }
            } else {
                // No professors were archived due to duplicates
                if (!empty($duplicate_faculty_ids)) {
                    $_SESSION['error'] = "No professors were archived because the faculty IDs were already in the archive: " . implode(", ", $duplicate_faculty_ids);
                } else {
                    $_SESSION['error'] = "No professors selected for archiving.";
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
        $_SESSION['error'] = 'No professors selected for archiving.';
    }
} else {
    $_SESSION['error'] = 'Action not specified or no professors selected.';
}

// Redirect to professor.php
header('location: professor.php');
exit();
?>
