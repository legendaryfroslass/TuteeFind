<?php
include '../includes/conn.php'; // Include database connection

if(isset($_POST['id'])){
    $id = $_POST['id'];
    
    $sql = "SELECT activity, datetime FROM activity_logs WHERE professor_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    $logs = '';
    while($row = $result->fetch_assoc()){
        $logs .= '<tr><td>'.$row['activity'].'</td><td>'.$row['datetime'].'</td></tr>';
    }

    echo $logs; // Output the logs as HTML table rows
}
?>
