<?php
include 'includes/session.php'; // Include session and database connection

// Get the event ID from the POST request
$id = $_POST['id']; 

// Fetch the event based on the event ID
$sql = "SELECT * FROM events WHERE id = '$id'";
$result = $conn->query($sql);

// Check if the event was found
if ($result->num_rows > 0) {
    // Fetch the event data
    $row = $result->fetch_assoc();
    
    // Prepare the response data
    $response = array(
        'event_name' => $row['event_name'],
        'rendered_hours' => $row['rendered_hours'],
        'description' => $row['description'],
        'attached_file' => $row['attached_file'],  // This is the filename of the image
        'created_at' => $row['created_at'],
        'status' => $row['status'],
        'remarks' => $row['remarks']
    );

    // Return the response as a JSON object
    echo json_encode($response);
} else {
    // If no event is found, return an empty JSON object
    echo json_encode((object)[]); 
}

// Close the database connection
$conn->close();
?>