<?php
session_start();
require_once '../tutor.php';
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
    
    $tutee = new TUTOR();
    $stmt = $tutee->runQuery("SELECT * FROM tutor WHERE emailaddress=:email_id");
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style2.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="icon" href="../assets/TuteeFindLogo.png" type="image/png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <title>Forgot Password</title>
</head>
<body>
<div class="bg-container">
    <div class="container  d-flex justify-content-center align-items-center min-vh-100">
        <div class="row border rounded-5 p-3 bg-white shadow box-area">
            <div class="col-md-6 rounded-4 d-flex justify-content-center align-items-center flex-column left-box" >
                    <img src="../assets/register2.png" alt="left-img" class="img-fluid custom-height" style="border-radius: 20px 0 0 20px;" >
            </div> 
            <div class="col-md-6 right-box">
                <div class="row justify-content-center">
                    <div class="header-text mb-2 mt-4">
                        <h2>Forgot Password?</h2>
                        <p>No worries. We’ll send instructions to you. Enter the email address associated to your account.</p>
                    </div>
                    <form method="POST" action="">
                        <div class="input-group mb-5">
                            <input type="email" name="email" class="form-control form-control-lg bg-light fs-6" placeholder="Email address" required>
                        </div>
                        <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
                        <div class="input-group mb-3">
                            <button type="submit" class="btn btn-lg btn-primary w-100 fs-6">Next</button>
                        </div>
                    </form>
                    <div class="row align-items-center">
                        <small style="text-align: center;">Already have an account?<a href="login" type="button" class="btn btn-link btn-sm" data-toggle="modal" data-target="#exampleModal">Log in</a></small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
