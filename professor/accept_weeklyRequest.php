<?php
include 'includes/session.php';
require '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (isset($_POST['accept'])) {
    // Check if required POST data is set
    if (isset($_POST['weekly_id'], $_POST['remarks'], $_POST['tutor_id'])) {
        // Retrieve form data
        $weekly_id = intval($_POST['weekly_id']); // Sanitize weekly progress ID
        $remarks = $_POST['remarks'];
        $tutor_id = intval($_POST['tutor_id']); // Sanitize tutor ID
        $status = 'accepted'; // Status is always 'accepted'

        // Get the tutor's email address
        $sql = "SELECT emailaddress FROM tutor WHERE id = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("i", $tutor_id); // Bind tutor ID
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $tutorEmail = $row['emailaddress'];
            } else {
                $_SESSION['error'] = 'Tutor not found.';
                header('Location: weekly_request');
                exit();
            }
            $stmt->close();
        } else {
            $_SESSION['error'] = 'Error: ' . $conn->error;
            header('Location: weekly_request');
            exit();
        }
        $sql = "INSERT INTO notifications (sender_id, receiver_id, title, message, status, sent_for) 
        VALUES (NULL, ?, 'Your professor accepted your weekly progress. Keep up the good work!', CONCAT('Reason: ', ?), 'unread', 'tutor')";
        
        // Insert a notification for the tutor about the acceptance
        $notificationStmt = $conn->prepare($sql);
        $notificationStmt->bind_param("is", $tutor_id, $remarks); // "i" for integer, "s" for string
        $notificationStmt->execute();
        // Prepare and send the email using PHPMailer
        $mail = new PHPMailer(true);
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'findtutee@gmail.com';
            $mail->Password = 'tzbb qafz fhar ryzf';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('findtutee@gmail.com', 'TUTEEFIND');
            $mail->addAddress($tutorEmail); // Recipient's email address

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Weekly Progress Accepted';
            $mail->Body = "
                <h3>Your professor accepted your weekly progress. Keep up the good work!</h3>
            ";

            $mail->send();
        } catch (Exception $e) {
            $_SESSION['error'] = 'Email could not be sent. Error: ' . $mail->ErrorInfo;
            header('Location: weekly_request');
            exit();
        }

        // Prepare the SQL statement to update the weekly progress
        $sql = "UPDATE tutee_progress SET remarks = ?, status = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            // Bind the parameters (remarks, status, weekly_id)
            $stmt->bind_param("ssi", $remarks, $status, $weekly_id);

            // Execute the statement
            if ($stmt->execute()) {
                $_SESSION['success'] = 'Weekly progress accepted successfully.';
            } else {
                $_SESSION['error'] = 'Error: ' . $stmt->error;
            }

            $stmt->close(); // Close the statement
        } else {
            $_SESSION['error'] = 'Error: ' . $conn->error;
        }
    } else {
        $_SESSION['error'] = 'Missing form data.';
    }
} else {
    $_SESSION['error'] = 'Fill up the form first.';
}

$conn->close();
header('Location: weekly_request');
?>
