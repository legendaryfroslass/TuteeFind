<?php
session_start();
require_once '../tutor.php';
$user_login = new TUTOR();
$error_message = '';

if (isset($_POST['btn-login'])) {
    $student_id = trim($_POST['txtemail']);
    $password = trim($_POST['txtupass']);

    // Set the time zone to Asia/Manila
date_default_timezone_set('Asia/Manila');

// Log the activity (e.g., tutor logs in)
$activity = "Log-in";
$formatted_datetime = date('F j, Y h:i:s A'); // Example: October 6, 2024 11:14:33 PM

// Insert the log into the activity_logs table for the tutor
$logSql = "INSERT INTO tutor_logs (student_id, activity, datetime) 
           VALUES (?, ?, ?)";
$logStmt = $conn->prepare($logSql);
$logStmt->bind_param("iss", $_SESSION['student_id'], $activity, $formatted_datetime);
$logStmt->execute();

    $login_result = $user_login->login($student_id, $password);

    if ($login_result === true) {
        $_SESSION['role'] = 'tutor';
        $user_login->redirect("suggestedtutee");
    } elseif ($login_result === 'email_not_found') {
        $error_message = "Student ID doesn't exist.";
    } elseif ($login_result === 'password_incorrect') {
        $error_message = "Password is incorrect.";
    } else {
        $error_message = "An unknown error occurred. Please try again.";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="icon" href="../assets/TuteeFindLogo.png" type="image/png">
    <link rel="stylesheet" href="style2.css">
    <title>Login</title>
</head>
<body>
    <div class="bg-container">
        <div class="container d-flex justify-content-center align-items-center min-vh-100">
            <div class="row border rounded-5 p-3 bg-white shadow box-area">
                <div class="col-md-6 rounded-4 d-flex justify-content-center align-items-center flex-column left-box">
                    <img src="../assets/register2.png" alt="left-img" class="img-fluid custom-height" style="border-radius: 20px 0 0 20px;">
                </div> 

                <div class="col-md-6 right-box">
                    <div class="row justify-content-center">
                        <div class="header-text mb-4">
                            <h2>Hello Tutor</h2>
                            <p>We are happy to have you back.</p>
                        </div>
                        <form method="POST" action="">
                            <!-- Display error message if there is one -->
                            <?php if ($error_message): ?>
                                <div class="alert alert-danger">
                                    <?php echo $error_message; ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="input-group mb-3">
                                <input type="text" class="form-control form-control-lg bg-light fs-6" placeholder="Student Number" name="txtemail" autocomplete="username" required>
                            </div>
                            <div class="input-group mb-1">
                                <input type="password" class="form-control form-control-lg fs-6 rounded-end" placeholder="Password" name="txtupass" id="passwordInput" required>
                                <span class="toggle-password position-absolute" id="togglePassword" style="cursor: pointer; right: 10px; top: 50%; transform: translateY(-50%); z-index: 10;">
                                    <i class="bi bi-eye" id="toggleIcon"></i>
                                </span>
                            </div>

                            <div class="input-group mb-5 d-flex justify-content-between">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="formCheck">
                                    <label for="formCheck" class="form-check-label text-secondary"><small>Remember Me</small></label>
                                </div>
                                <div class="forgot">
                                    <small><a href="forgotpassword" style="text-decoration: none;">Forgot Password?</a></small>
                                </div>
                            </div>
                            <div class="input-group mb-3">
                                <button type="submit" class="btn btn-lg btn-primary w-100 fs-6" name="btn-login">Login</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js" crossorigin="anonymous"></script>
    <script >
        // Toggle Password
        const togglePassword = document.querySelector('#togglePassword');
        const passwordInput = document.querySelector('#passwordInput');
        const toggleIcon = document.querySelector('#toggleIcon');

        togglePassword.addEventListener('click', function () {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            toggleIcon.classList.toggle('bi-eye');
            toggleIcon.classList.toggle('bi-eye-slash');
});
    </script>
</body>
</html>
