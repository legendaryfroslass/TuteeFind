<?php
include 'includes/session.php';

// Assuming you have already connected to the database

// Assuming you have retrieved the professor's ID from the POST request
$id = $_POST['id'];

// Fetching professor information from the database
$sql = "SELECT * FROM professor WHERE id = '$id'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Output data of each row
    $row = $result->fetch_assoc();
    // Format the data as JSON and return it
    echo json_encode($row);
} else {
    // Return an empty JSON object if no data is found
    echo json_encode((object)[]); // or echo "{}";
}

$conn->close();
?>
