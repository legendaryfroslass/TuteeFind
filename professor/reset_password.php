<?php
session_start();
include 'includes/header.php';

// Check if OTP is stored in session
if(!isset($_SESSION['otp'])) {
    // If OTP is not set, redirect back to forgot password page
    header("Location: forgot_password");
    exit();
}

// Process form submission for OTP verification
if(isset($_POST['submit'])){
    // Retrieve the entered OTP
    $entered_otp = $_POST['otp'];
    
    // Retrieve the OTP from the session
    $stored_otp = $_SESSION['otp'];
    
    // Compare entered OTP with stored OTP
    if($entered_otp == $stored_otp) {
        header("Location: password_reset");
        exit();
    }
     else {
        // OTP mismatch, display error message
        $_SESSION['error'] = "Invalid OTP. Please try again.";
    }
}
?>

<body class="hold-transition login-page" style="background-image: url('LoginBackground/plv2.jpg'); background-size: cover; background-position: center;">
    
    <div class="login-overlay"></div> <!-- Overlay to darken the background -->

    <div class="login-box">
        <div class="login-logo">
            <b style="color: white; text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.7); font-size: 48px;">Professor</b> <!-- Text shadow added for better readability -->
        </div>

  
        <div class="login-box-body">
            <p class="login-box-msg">Enter OTP</p>
            
            <!-- Display error message if OTP verification failed -->
            <?php if(isset($_SESSION['error'])): ?>
                <div class="callout callout-danger text-center">
                    <p><?php echo $_SESSION['error']; ?></p> 
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>
            
            <!-- OTP Verification Form -->
            <form action="" method="POST">
                <div class="form-group has-feedback">
                    <input type="text" class="form-control" name="otp" placeholder="OTP" required>
                    <span class="glyphicon glyphicon-lock form-control-feedback"></span>
                </div>
                <div class="row">
                    <div class="col-xs-6 col-xs-offset-3">
                        <button type="submit" class="btn btn-primary btn-block btn-flat" name="submit"><i class="fa fa-check"></i> Verify OTP</button>
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

