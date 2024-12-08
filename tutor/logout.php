<?php
session_start();
require_once '../tutor.php';
$user = new TUTOR();

// If the user is logged in, log out and redirect
if (isset($_SESSION['tutorSession'])) {
    $user->logout();  // This will log the logout activity and destroy the session
    $user->redirect('login');  // Redirect to login page after logout
} else {
    // If no session exists, just redirect to login page
    header("Location: login");
    exit;
}
?>
