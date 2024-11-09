<?php
session_start();
include 'includes/header.php';

// Include necessary files and configurations
require '../vendor/phpmailer/phpmailer/src/Exception.php';
require '../vendor/phpmailer/phpmailer/src/SMTP.php';
require '../vendor/phpmailer/phpmailer/src/PHPMailer.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Include database connection file
include 'includes/conn.php';

// Check if OTP is stored in session
if(!isset($_SESSION['otp'])) {
    // If OTP is not set, redirect back to forgot password page
    header("Location: forgot_password");
    exit();
}

// Process form submission for password change
if (isset($_POST['submit'])) {
    // Retrieve the new password and confirm password
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Perform password strength validation
    if (strlen($password) < 8 || !preg_match("/\d/", $password)) {
        // Password does not meet requirements
        $_SESSION['error'] = "Password must be at least 8 characters long and contain at least one number.";
    } elseif ($password !== $confirm_password) {
        // Password and confirm password do not match
        $_SESSION['error'] = "Passwords do not match.";
    } else {
        // Password meets requirements, proceed to update password
        // Retrieve admin's email from session
        $emailaddress = $_SESSION['emailaddress']; // Use session-stored email address

        // Hash the password before storing it in the database
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Prepare the update query
        $query = "UPDATE admin SET password = ? WHERE emailaddress = ?";
        $stmt = $conn->prepare($query); // Prepare the statement
        if ($stmt) {
            $stmt->bind_param("ss", $hashed_password, $emailaddress); // Bind parameters
            if ($stmt->execute()) {
                // Password updated successfully
                // Optionally send a success email or redirect to login
                header("Location: index");
                exit();
            } else {
                // Error executing the statement
                $_SESSION['error'] = "An error occurred while updating the password. Please try again.";
            }
        } else {
            // Error preparing the statement
            $_SESSION['error'] = "Failed to prepare the password update statement.";
        }
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
            <p class="login-box-msg">Change Password</p>
            
            <!-- Display error message if password validation failed -->
            <?php if(isset($_SESSION['error'])): ?>
                <div class="callout callout-danger text-center">
                    <p><?php echo $_SESSION['error']; ?></p> 
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>
            
            <!-- Password Change Form -->
            <form action="" method="POST" id="changePasswordForm">
                <div class="form-group has-feedback">
                    <input type="password" class="form-control" name="password" id="password" placeholder="New Password" required>
                    <span class="glyphicon glyphicon-lock form-control-feedback"></span>
                </div>
                <div class="form-group has-feedback">
                    <input type="password" class="form-control" name="confirm_password" id="confirm_password" placeholder="Confirm Password" required>
                    <span class="glyphicon glyphicon-lock form-control-feedback"></span>
                </div>
                <div id="password-strength"></div>
                <div class="row">
                    <div class="col-xs-6 col-xs-offset-3">
                        <button type="submit" class="btn btn-primary btn-block btn-flat" name="submit"><i class="fa fa-check"></i> Change Password</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <?php include 'includes/scripts.php' ?>
    <script>
        // Password strength detection
        $('#password').on('keyup', function () {
            var password = $(this).val();
            var strength = 0;

            if (password.match(/[a-z]+/)) {
                strength += 1;
            }
            if (password.match(/[A-Z]+/)) {
                strength += 1;
            }
            if (password.match(/[0-9]+/)) {
                strength += 1;
            }
            if (password.length > 7) {
                strength += 1;
            }

            if (strength === 0) {
                $('#password-strength').html('<span class="text-danger">Weak</span>');
            } else if (strength === 1) {
                $('#password-strength').html('<span class="text-warning">Medium</span>');
            } else if (strength === 2) {
                $('#password-strength').html('<span class="text-primary">Strong</span>');
            } else {
                $('#password-strength').html('<span class="text-success">Very Strong</span>');
            }
        });

        // Confirm password check
        $('#confirm_password').on('keyup', function () {
            var password = $('#password').val();
            var confirm_password = $(this).val();

            if (password !== confirm_password) {
                $('#confirm_password').removeClass('is-valid').addClass('is-invalid');
            } else {
                $('#confirm_password').removeClass('is-invalid').addClass('is-valid');
            }
        });
    </script>
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
