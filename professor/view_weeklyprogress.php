<?php
include 'includes/session.php'; // Ensure session and DB connection

header('Content-Type: application/json');

// Get ID from the POST request
$id = isset($_POST['id']) ? $_POST['id'] : null;

if (!$id) {
    echo json_encode(['error' => 'Invalid or missing ID']);
    exit;
}

// Fetch record from the database
$sql = "SELECT * FROM tutee_progress WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();

    // Construct response
    $response = array(
        'week_number' => $row['week_number'],
        'rendered_hours' => $row['rendered_hours'],
        'uploaded_files' => !empty($row['uploaded_files']) ? '../uploads/' . $row['uploaded_files'] : null, // Add base path
        'description' => $row['description'],
        'date' => $row['date'],
        'location' => $row['location'],
        'subject' => $row['subject'],
        'status' => $row['status'],
        'remarks' => $row['remarks']
    );

    echo json_encode($response);
} else {
    echo json_encode(['error' => 'No record found for the given ID']);
}

$conn->close();
?>
