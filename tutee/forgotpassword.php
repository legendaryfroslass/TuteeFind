<?php
session_start();
require_once '../tutee.php';
require '../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require '../vendor/phpmailer/phpmailer/src/Exception.php';
require '../vendor/phpmailer/phpmailer/src/SMTP.php';
require '../vendor/phpmailer/phpmailer/src/PHPMailer.php';

function send_verification_code($email, $code) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'findtutee@gmail.com';
        $mail->Password = 'tzbb qafz fhar ryzf';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587 ;
        
        $mail->setFrom('findtutee@gmail.com', 'TUTEEFIND');
        $mail->addAddress($email); // Set the recipient as the email entered in the form

        $mail->isHTML(true);
        $mail->Subject = 'Password Reset Code';
        $mail->Body    = "Your password reset code is <b>$code</b>";
        
        // Enable verbose debug output
        $mail->SMTPDebug = 2;
        $mail->Debugoutput = 'html';

        $mail->send();
        return true;
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        return false;
    }
}


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    
    $tutee = new TUTEE();
    $stmt = $tutee->runQuery("SELECT * FROM tutee WHERE emailaddress=:email_id");
    $stmt->execute(array(":email_id" => $email));
    if ($stmt->rowCount() == 1) {
        $code = rand(100000, 999999);
        $_SESSION['reset_code'] = $code;
        $_SESSION['email'] = $email;
        if (send_verification_code($email, $code)) {
            header('Location: verify_code');
            exit();
        } else {
            $error = "Failed to send email. Please try again.";
        }
    } else {
        $error = "Email not found in our system.";
    }
}
include('spinner.php');

// Check if login failed via URL parameter
// if (isset($_GET['notAvail'])) {
//     echo '<script>
//             window.onload = function() {
//                 var invalidLoginModal = new bootstrap.Modal(document.getElementById("invalidLogin"));
//                 invalidLoginModal.show();
//             }
//             </script>';
// }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="tuteelogin.css">
    <link rel="stylesheet" href="spinner.css">
    <title>Forgot Password</title>
    <style>
        .bg-container {
            background-image: url("../assets/forgorpass2.jpg");
        }
        /* .spinner-container {
            display: flex;
            justify-content: center;
            align-items: center;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.8);
            z-index: 9999;
            display: none;
        }
        .spinner {
            font-size: 48px;
        } */
        .d-none {
            display: none;
        }
        /* Add styles for the spinner blades */
    </style>
</head>
<body>

    <!-- Invalid login credentials -->
    <!-- <div class="modal fade" id="invalidLogin" tabindex="-1" aria-labelledby="invalidLogin" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content m-3">
                <div class="modal-body" style="text-align: center;">
                    <div class="icon-wrapper m-3">
                        <div class="m-3">
                            <i class="bi bi-exclamation-triangle" id="warningAlertIcon"></i>
                        </div>
                    </div>
                    <h5>Oops! Something Went Wrong</h5>
                    <p class="m-3">Please input a valid email first.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Dismiss</button>
                </div>
            </div>
        </div>
    </div> -->

<div class="bg-container">
    <div class="col-5 d-flex align-items-center min-vh-100 left">
        <div class="left-box m-5 p-4">
            <div class="pb-5">
                <img src="../assets/TuteeFindLogo.png" class="logo">
                <img src="../assets/TuteeFindLogoName.png" class="word-logo">
            </div>
            <div class="row justify-content-center">
                <div class="header-text mb-4">
                    <h2>Forgot Password?</h2>
                    <p>No worries. We’ll send instructions to you. Enter the email address associated with your account.</p>
                </div>
                
                <!-- Spinner -->
                <!-- <div class="spinner-container">
                    <div class="spinner center">
                        <div class="spinner-blade"></div>
                        <div class="spinner-blade"></div>
                        <div class="spinner-blade"></div>
                        <div class="spinner-blade"></div>
                        <div class="spinner-blade"></div>
                        <div class="spinner-blade"></div>
                        <div class="spinner-blade"></div>
                        <div class="spinner-blade"></div>
                        <div class="spinner-blade"></div>
                        <div class="spinner-blade"></div>
                        <div class="spinner-blade"></div>
                        <div class="spinner-blade"></div>
                        <div class="spinner-blade"></div>
                    </div>
                </div> -->

                <form method="POST" action="" id="forgotPasswordForm">
                    <div class="input-group mb-5">
                        <input type="email" name="email" class="form-control form-control-lg bg-light fs-6" placeholder="Email address" required>
                    </div>
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    <div class="input-group mb-3">
                        <button type="submit" id="submitButton" class="btn btn-lg btn-primary w-100 fs-6">Next</button>
                    </div>
                </form>
                <div class="row align-items-center justify-content-center" style="text-align: center;">
                    <small><a href="login" type="button" class="btn btn-link btn-sm linkless">Back to Login</a></small>
                </div>
            </div>
        </div>
    </div>

    <script>
        // document.getElementById('forgotPasswordForm').addEventListener('submit', function(e) {
        //     // Show the spinner
        //     document.querySelector('.spinner-container').style.display = 'flex'; // Show the spinner

        //     // Disable the submit button to prevent multiple clicks
        //     document.getElementById('submitButton').disabled = true;
        // });
    </script>

    <script src="spinner.js"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>