<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include 'includes/conn.php';

if (!isset($_SESSION['professor_id']) || trim($_SESSION['professor_id']) == '') {
    header('location: index.php');
    exit(); // Ensure script stops execution after redirection
}

$sql = "SELECT * FROM professor WHERE id = '".$_SESSION['professor_id']."'";
$query = $conn->query($sql);
$user = $query->fetch_assoc();
?>
