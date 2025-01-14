<?php
// Start output buffering
ob_start();

// Start the session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include 'includes/conn.php';  // Ensure the database connection is properly included

// Check if professor is logged in
if (!isset($_SESSION['professor_id']) || trim($_SESSION['professor_id']) == '') {
    // Redirect to login page if professor is not logged in
    header('Location: landingpage.php');
    exit(); // Make sure script stops after header redirection
}

// Fetch professor details from database
$sql = "SELECT * FROM professor WHERE id = '".$_SESSION['professor_id']."'";
$query = $conn->query($sql);
$user = $query->fetch_assoc();

// Ensure no output before header is called
?>
