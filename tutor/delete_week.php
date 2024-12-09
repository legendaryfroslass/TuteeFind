<?php
require_once '../tutor.php';
$user_login = new TUTOR();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tutee_id = $_POST['tutee_id'];
    $week_id = $_POST['week_id'];

    try {
        // Begin transaction
        $user_login->runQuery("BEGIN");

        // Check if the week has uploaded files before deletion
        $checkStmt = $user_login->runQuery("
            SELECT uploaded_files 
            FROM tutee_progress 
            WHERE tutee_id = :tutee_id AND id = :week_id
        ");
        $checkStmt->bindParam(':tutee_id', $tutee_id);
        $checkStmt->bindParam(':week_id', $week_id);
        $checkStmt->execute();
        $weekData = $checkStmt->fetch(PDO::FETCH_ASSOC);

        $hasUploadedFiles = !empty($weekData['uploaded_files']);

        // Delete the week record
        $stmt = $user_login->runQuery("
            DELETE FROM tutee_progress 
            WHERE tutee_id = :tutee_id AND id = :week_id
        ");
        $stmt->bindParam(':tutee_id', $tutee_id);
        $stmt->bindParam(':week_id', $week_id);
        $stmt->execute();

        // Check if any rows were affected by the delete operation
        if ($stmt->rowCount() > 0) {
            // Update the registered_weeks count
            $updateRegisteredWeeksStmt = $user_login->runQuery("
                UPDATE tutee_summary 
                SET registered_weeks = registered_weeks - 1 
                WHERE tutee_id = :tutee_id
            ");
            $updateRegisteredWeeksStmt->bindParam(':tutee_id', $tutee_id);
            $updateRegisteredWeeksStmt->execute();

            // If the week had uploaded files, delete the associated file
            if ($hasUploadedFiles) {
                $file_path = $weekData['uploaded_files'];
                if (unlink($file_path)) {
                    // Update the completed_weeks count
                    $updateCompletedWeeksStmt = $user_login->runQuery("
                        UPDATE tutee_summary 
                        SET completed_weeks = completed_weeks - 1 
                        WHERE tutee_id = :tutee_id
                    ");
                    $updateCompletedWeeksStmt->bindParam(':tutee_id', $tutee_id);
                    $updateCompletedWeeksStmt->execute();
                } else {
                    // Rollback transaction if file deletion fails
                    $user_login->runQuery("ROLLBACK");
                    echo json_encode(['success' => false, 'message' => 'Failed to delete file']);
                    exit;
                }
            }
            // Check if both completed_weeks and registered_weeks are now zero
            $checkCountsStmt = $user_login->runQuery("
                SELECT completed_weeks, registered_weeks 
                FROM tutee_summary 
                WHERE tutee_id = :tutee_id
            ");
            $checkCountsStmt->bindParam(':tutee_id', $tutee_id);
            $checkCountsStmt->execute();
            $counts = $checkCountsStmt->fetch(PDO::FETCH_ASSOC);

            if ($counts['completed_weeks'] == 0 && $counts['registered_weeks'] == 0) {
                // Remove the tutee record from tutees table
                $deleteTuteeStmt = $user_login->runQuery("
                    DELETE FROM tutee_summary
                    WHERE tutee_id = :tutee_id
                ");
                $deleteTuteeStmt->bindParam(':tutee_id', $tutee_id);
                $deleteTuteeStmt->execute();
            }

            // Commit transaction
            $user_login->runQuery("COMMIT");

            echo json_encode(['success' => true]);
        } else {
            // Rollback transaction if no rows were deleted
            $user_login->runQuery("ROLLBACK");
            echo json_encode(['success' => false, 'message' => 'No such record found']);
        }
    } catch (PDOException $ex) {
        // Rollback transaction in case of error
        $user_login->runQuery("ROLLBACK");
        echo json_encode(['success' => false, 'message' => $ex->getMessage()]);
    }
}
?>
