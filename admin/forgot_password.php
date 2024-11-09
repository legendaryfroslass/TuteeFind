<?php
// Include necessary files and configurations
session_start();
include 'includes/header.php';
require '../vendor/phpmailer/phpmailer/src/Exception.php';
require '../vendor/phpmailer/phpmailer/src/SMTP.php';
require '../vendor/phpmailer/phpmailer/src/PHPMailer.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Include database connection file
include 'includes/conn.php';

// Process form submission for forgot password
if(isset($_POST['submit'])){
    // Retrieve the email entered by the user
    $email = $_POST['email'];
    
    // Perform necessary validation on the email
    if(empty($email)){
        $_SESSION['error'] = "Email is required";
    } else {
        // Check if the entered email belongs to an admin
        $query = "SELECT * FROM admin WHERE emailaddress = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if($result->num_rows > 0) {
            $otp = rand(100000, 999999);
            $_SESSION['otp'] = $otp;
            $_SESSION['emailaddress'] = $email;
            sendOTPEmail($email, $otp);
            header("Location: reset_password");
            exit();
        } else {
            $_SESSION['error'] = "Email not found or does not belong to an admin";
        }
    }
}

// Function to send OTP email
function sendOTPEmail($email, $otp) {
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com'; // SMTP server
        $mail->SMTPAuth   = true;
        $mail->Username   = 'findtutee@gmail.com'; // SMTP username
        $mail->Password   = 'tzbb qafz fhar ryzf';   // SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Enable TLS encryption
        $mail->Port       = 587; // TCP port to connect to

        // Recipients
        $mail->setFrom('findtutee@gmail.com', 'TUTEEFIND');
        $mail->addAddress($email); // Add a recipient

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Password Reset OTP';
        $mail->Body    = 'Your OTP for password reset is: ' . $otp;

        $mail->send();
    } catch (Exception $e) {
        // Handle mailer exception
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}
?>

<body class="hold-transition login-page" style="background-image: url('LoginBackground/plv1.jpg'); background-size: cover; background-position: center;">
    
    <div class="login-overlay"></div> <!-- Overlay to darken the background -->

    <div class="login-box">
        <div class="login-logo">
            <b style="color: white; text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.7); font-size: 48px;">Admin</b> <!-- Text shadow added for better readability -->
        </div>

        <div class="login-box-body">
            <p class="login-box-msg">Forgot Password</p>

            <!-- Display success message if email sent successfully -->
            <?php if(isset($_SESSION['success'])): ?>
                <div class="callout callout-success text-center">
                    <p><?php echo $_SESSION['success']; ?></p>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <!-- Display error message if email validation failed -->
            <?php if(isset($_SESSION['error'])): ?>
                <div class="callout callout-danger text-center">
                    <p><?php echo $_SESSION['error']; ?></p>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <!-- Forgot Password Form -->
            <form action="" method="POST">
                <div class="form-group has-feedback">
                    <input type="email" class="form-control" name="email" placeholder="Email" required>
                    <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
                </div>
                <div class="row">
                    <div class="col-xs-6 col-xs-offset-3">
                        <button type="submit" class="btn btn-primary btn-block btn-flat" name="submit"><i class="fa fa-envelope"></i> Send OTP</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <?php include 'includes/scripts.php' ?>
</body>
</html>

<style>
/* Overlay effect */
.login-overlay {
    position: fixed;
    top: 0;
    left: 0;
    height: 100%;
    width: 100%;
    background: rgba(0, 0, 0, 0.5); /* Semi-transparent black overlay */
    z-index: -1; /* Behind the login box */
}
</style>
