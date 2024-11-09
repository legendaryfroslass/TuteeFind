<?php 
session_start();
include 'includes/conn.php';

// Set the time zone to Asia/Manila
date_default_timezone_set('Asia/Manila');

if (isset($_POST['login'])) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Check for empty fields
    if (empty($username) || empty($password)) {
        $_SESSION['error'] = 'Input admin credentials first';
        header('location: index.php');
        exit();
    }

    // Prepare the SQL statement to find the professor by username
    $sql = "SELECT * FROM professor WHERE prof_username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if a matching account exists
    if ($result->num_rows < 1) {
        $_SESSION['error'] = 'Cannot find account with the username';
    } else {
        $row = $result->fetch_assoc();
        $hashed_password = $row['prof_password'];

        // Verify the password
        if (password_verify($password, $hashed_password)) {
            $_SESSION['professor_id'] = $row['id'];

            // Log the activity
            $activity = "Log-in";
            $formatted_datetime = date('F j, Y h:i:s A'); // Example: October 6, 2024 11:14:33 PM
            $logSql = "INSERT INTO activity_logs (professor_id, activity, datetime) 
                       VALUES (?, ?, ?)";
            $logStmt = $conn->prepare($logSql);
            $logStmt->bind_param("iss", $_SESSION['professor_id'], $activity, $formatted_datetime);
            $logStmt->execute();

            // Update the last_login field
            $updateLoginSql = "UPDATE professor SET last_login = NOW() WHERE id = ?";
            $updateLoginStmt = $conn->prepare($updateLoginSql);
            $updateLoginStmt->bind_param("i", $_SESSION['professor_id']);
            $updateLoginStmt->execute();

            // Set the professor's status to active upon successful login
            // (This is optional; you're already updating last_login, but if you have a specific status column, you can update that too)
            // $statusUpdateSql = "UPDATE professor SET status = 'Active' WHERE id = ?";
            // $statusUpdateStmt = $conn->prepare($statusUpdateSql);
            // $statusUpdateStmt->bind_param("i", $_SESSION['professor_id']);
            // $statusUpdateStmt->execute();

            header('location: home.php');
            exit();
        } else {
            $_SESSION['error'] = 'Incorrect password';
        }
    }
    header('location: index.php');
    exit();
} else {
    $_SESSION['error'] = 'Input admin credentials first';
    header('location: index.php');
    exit();
}
?>
