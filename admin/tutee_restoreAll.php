<?php
include 'includes/session.php';

if (isset($_POST['restoreAllTutee'])) {
    // Start transaction
    $conn->begin_transaction();

    try {
        // Fetch all tutees from archive_tutee
        $sql = "SELECT * FROM archive_tutee";
        $query = $conn->query($sql);

        if ($query->num_rows > 0) {
            // Prepare the insert statement for tutee table
            $stmt_restore = $conn->prepare("INSERT INTO tutee (id, firstname, lastname, age, sex, number, guardianname, fblink, barangay, tutee_bday, school, grade, emailaddress, photo, password, bio, address) 
                                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

            // Archive to active table mapping
            $tables = [
                'archive_messages' => 'messages',
                'archive_notifications' => 'notifications',
                'archive_requests' => 'requests',
                'archive_tutee_progress' => 'tutee_progress',
                'archive_tutee_summary' => 'tutee_summary',
                'archive_tutor_ratings' => 'tutor_ratings',
                'archive_tutor_sessions' => 'tutor_sessions',
                'archive_tutee_logs' => 'tutee_logs'
            ];

            // Loop through each archived tutee
            while ($row = $query->fetch_assoc()) {
                // Restore tutee to active table
                $stmt_restore->bind_param(
                    "ississsssssssssss",
                    $row['id'],
                    $row['firstname'],
                    $row['lastname'],
                    $row['age'],
                    $row['sex'],
                    $row['number'],
                    $row['guardianname'],
                    $row['fblink'],
                    $row['barangay'],
                    $row['tutee_bday'],
                    $row['school'],
                    $row['grade'],
                    $row['emailaddress'],
                    $row['photo'],
                    $row['password'],
                    $row['bio'],
                    $row['address']
                );

                if (!$stmt_restore->execute()) {
                    throw new Exception("Error restoring tutee with ID " . $row['id'] . ": " . $conn->error);
                }

                // Restore related data for the current tutee
                foreach ($tables as $archive_table => $main_table) {
                    $sql_restore_related = "INSERT INTO $main_table SELECT * FROM $archive_table WHERE tutee_id = ?";
                    $stmt_restore_related = $conn->prepare($sql_restore_related);
                    $stmt_restore_related->bind_param("i", $row['id']);
                    $stmt_restore_related->execute();

                    $sql_delete_related = "DELETE FROM $archive_table WHERE tutee_id = ?";
                    $stmt_delete_related = $conn->prepare($sql_delete_related);
                    $stmt_delete_related->bind_param("i", $row['id']);
                    $stmt_delete_related->execute();
                }

                // Delete the restored tutee from archive_tutee
                $sql_delete_tutee = "DELETE FROM archive_tutee WHERE id = ?";
                $stmt_delete_tutee = $conn->prepare($sql_delete_tutee);
                $stmt_delete_tutee->bind_param("i", $row['id']);
                $stmt_delete_tutee->execute();
            }

            // Commit transaction
            $conn->commit();
            $_SESSION['success'] = 'All tutees and their related data restored successfully.';
        } else {
            throw new Exception('No tutees found in the archive.');
        }
    } catch (Exception $e) {
        // Rollback transaction if an error occurs
        $conn->rollback();
        $_SESSION['error'] = $e->getMessage();
    }
} else {
    $_SESSION['error'] = 'No action specified to restore tutees.';
}

header('location: archive_tutee.php');
exit();
?>
