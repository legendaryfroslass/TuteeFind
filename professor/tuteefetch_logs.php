<?php
include 'includes/conn.php'; // Include database connection

if (isset($_POST['id'])) {
    $id = $_POST['id'];
    $logs = ''; // Initialize logs variable to store the fetched logs

    // Query to fetch the tutor's activity logs
    $sql = "SELECT activity, datetime FROM tutee_logs WHERE tutee_id = ? ORDER BY datetime ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Format the datetime field to a more readable format
            $dateTime = new DateTime($row['datetime']);
            $formattedTime = $dateTime->format('m/d/Y h:i:s A'); // Format as MM/DD/YYYY hh:mm:ss AM/PM

            // Append each log entry to the logs string
            $logs .= '<tr><td>' . $row['activity'] . '</td><td>' . $formattedTime . '</td></tr>';
        }
    } else {
        // Display a message if no logs found
        $logs .= '<tr><td colspan="2">No logs found</td></tr>';
    }
    echo $logs; // Return the logs as response
}
?>
