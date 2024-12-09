<?php
include 'includes/session.php';
require '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// PHPMailer classes are already autoloaded, no need to require them separately.
// require '../vendor/phpmailer/phpmailer/src/Exception.php';
// require '../vendor/phpmailer/phpmailer/src/SMTP.php';
// require '../vendor/phpmailer/phpmailer/src/PHPMailer.php';

if (isset($_POST['reject'])) {
    // Check if required POST data is set
    if (isset($_POST['event_id'], $_POST['remarks'], $_POST['tutor_id'])) {
        // Retrieve form data
        $event_id = intval($_POST['event_id']); // Sanitize event ID
        $remarks = $_POST['remarks'];
        $tutor_id = intval($_POST['tutor_id']); // Sanitize tutor ID
        $status = 'rejected'; // Status is always 'accepted'

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
                header('Location: event_request');
                exit();
            }
            $stmt->close();
        } else {
            $_SESSION['error'] = 'Error: ' . $conn->error;
            header('Location: event_request');
            exit();
        }
        $sql = "INSERT INTO notifications (sender_id, receiver_id, title, message, status, sent_for) 
        VALUES (NULL, ?, 'Your professor has rejected your event request submission.', CONCAT('Reason: ', ?), 'unread', 'tutor')";
        
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
            $mail->Subject = 'Request Rejected';
            $mail->Body = "
                <h3>Your Professor rejected your event request. Check your remarks why the event is rejected.</h3>
                <p>Remarks: $remarks.</p>
            ";

            $mail->send();
        } catch (Exception $e) {
            $_SESSION['error'] = 'Email could not be sent. Error: ' . $mail->ErrorInfo;
            header('Location: event_request');
            exit();
        }

        // Prepare the SQL statement to update the event
        $sql = "UPDATE events SET remarks = ?, status = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            // Bind the parameters (remarks, status, event_id)
            $stmt->bind_param("ssi", $remarks, $status, $event_id);

            // Execute the statement
            if ($stmt->execute()) {
                $_SESSION['success'] = 'Request rejected successfully';
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
header('Location: event_request');
?>
