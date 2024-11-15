<?php
include 'includes/conn.php'; // Include database connection

if (isset($_POST['id'])) {
    $id = $_POST['id'];
    echo "ID received: " . $id; // Debugging line
} else {
    echo "ID not received"; // Debugging line

    
    $sql = "SELECT activity, datetime FROM tutor_logs WHERE student_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $logs .= '<tr><td>' . $row['activity'] . '</td><td>' . $row['datetime'] . '</td></tr>';
        }
    } else {
        $logs .= '<tr><td colspan="2">No logs found</td></tr>';
    }
    
}
?>


