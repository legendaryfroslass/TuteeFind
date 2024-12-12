<?php 
include 'includes/session.php';

if(isset($_POST['id'])){
    $id = $_POST['id'];
    
    // Prepare the SQL statement to fetch professor details using a prepared statement
    $sql = "SELECT * FROM tutee WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id); // "i" indicates the parameter type is integer
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if a professor with the provided ID exists
    if($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // Return professor details as JSON response
        echo json_encode($row);
    } else {
        // If no professor found with the provided ID, return an error message
        echo json_encode(['error' => 'Tutee not found']);
    }
}
?>
