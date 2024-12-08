<?php
session_start();
require_once '../tutee.php';  // Include the TUTEE class
$user = new TUTEE();  // Create a new TUTEE object

// Check if the user is logged in (session exists)
if (isset($_SESSION["userSession"])) {
    // Log the logout activity before destroying the session
    $user->logout(); 

    // Now destroy the session
    session_unset();  // Unset all session variables
    session_destroy();  // Destroy the session

    // Redirect to the login page
    header("Location: login");
    exit();
} else {
    // If not logged in, redirect to login page directly
    header("Location: login");
    exit();
}
