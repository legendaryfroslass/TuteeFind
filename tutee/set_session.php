<?php
session_start();

// Get the JSON input
$data = json_decode(file_get_contents("php://input"), true);

// Store birthday and age in session
$_SESSION['birthday'] = $data['birthday'];
$_SESSION['age'] = $data['age'];

// Return success response
echo json_encode(['status' => 'success']);
