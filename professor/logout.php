<?php
session_start();
include 'includes/conn.php'; // Include database connection

// Set the time zone to Asia/Manila
date_default_timezone_set('Asia/Manila');

// Log the activity
if (isset($_SESSION['professor_id'])) {
    $activity = "Log-out";
    // Format the current date and time in the desired format
    $formatted_datetime = date('F j, Y h:i:s A'); // Example: October 6, 2024 11:14:33 PM

    $logSql = "INSERT INTO professor_logs (id, professor_id, activity, datetime) VALUES (?, ?, ?, ?)";
    $logStmt = $conn->prepare($logSql);
    $logStmt->bind_param("iiss", $id,$_SESSION['professor_id'], $activity, $formatted_datetime);
    $logStmt->execute();
}

session_destroy();
header('location: landingpage');
exit();
?>
