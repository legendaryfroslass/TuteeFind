<?php
session_start();
require_once '../tutee.php';

// Ensure both email and reset code are present in the session and the user is verified
if (!isset($_SESSION['email']) || !isset($_SESSION['reset_code']) || !isset($_SESSION['verified']) || $_SESSION['verified'] !== true) {
    header('Location: forgotpassword');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    if ($new_password == $confirm_password) {
        $tutee = new TUTEE();
        $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $tutee->runQuery("UPDATE tutee SET password=:password WHERE emailaddress=:email");
        $stmt->execute(array(":password" => $new_password_hash, ":email" => $_SESSION['email']));
        // Clear session data after successful password reset
        unset($_SESSION['reset_code']);
        unset($_SESSION['email']);
        unset($_SESSION['verified']); // Clear the verified flag
        // $success = "Password changed successfully.";

        header('Location: login?passwordChanged=true');
        exit();
    } else {
        $error = "Passwords do not match. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="tuteelogin.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <title>Change Password</title>
    <style>
        .invalid-feedback {
            display: none;
            color: red;
            font-size: 0.875em;
        }
        .is-invalid ~ .invalid-feedback {
            display: block;
        }
        
    </style>
</head>
<body>
    <div class="bg-container">
    <div class="col-5 d-flex align-items-center min-vh-100 left">
        <div class="left-box m-5 p-4">
            <div class="pb-5">
                <img src="../assets/TuteeFindLogo.png" class="logo">
                <img src="../assets/TuteeFindLogoName.png" class="word-logo">
            </div>
            <div class="row justify-content-center">
                <div class="header-text mb-4">
                    <h2>Change Password</h2>
                    <p>Enter your new password.</p>
                </div>
                <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
                <?php if (isset($success)) echo "<div class='alert alert-success'>$success</div>"; ?>
                <form method="POST" action="" id="changePassForm">
                    <div class="input-group mb-3">
                        <div class="form-floating mb-2 position-relative">
                            <input type="password" name="password" class="password form-control form-control-lg fs-6" id="password" placeholder="New Password" required>
                            <label for="password">Password</label>
                            <span class="toggle-password position-absolute" id="togglePassword" style="cursor: pointer;">
                                <i class="bi bi-eye" id="toggleIcon"></i>
                            </span>
                            <div class="invalid-feedback" id="passwordFeedback">Password must be at least 8 characters long and contain at least one number.</div>
                        </div>
                    </div>
                    <div>
                        <div class="form-floating mb-3 position-relative mb-5">
                            <input type="password" name="confirm_password" class="confirm-password form-control form-control-lg fs-6" id="confirm-password" placeholder="Confirm New Password" required> 
                            <label for="confirm-password">Confirm Password</label>
                            <span class="toggle-password position-absolute" id="toggleConfirmPassword" style="cursor: pointer;">
                                <i class="bi bi-eye" id="toggleConfirmIcon"></i>
                            </span>
                            <div class="invalid-feedback" id="confirmPasswordFeedback">Passwords do not match.</div>
                        </div>
                    </div>
                    <div class="input-group">
                        <button type="submit" id="changePassBtn" class="btn btn-lg btn-primary w-100 fs-6">Change Password</button>
                    </div>
                    <div class="row align-items-center justify-content-center m-3" style="text-align: center;">
                        <small><a href="login" type="button" class="btn btn-link btn-sm linkless">Back to Login</a></small>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const passwordInput = document.getElementById('password');
            const confirmPasswordInput = document.getElementById('confirm-password');
            const passwordFeedback = document.getElementById('password-feedback');
            // const confirmPasswordFeedback = confirmPasswordInput.nextElementSibling;

            function validatePassword() {
                const passwordValue = passwordInput.value;
                const passwordValid = passwordValue.length >= 8 && /\d/.test(passwordValue);
                
                if (passwordValid) {
                    passwordInput.classList.remove('is-invalid');
                    passwordFeedback.style.display = 'none';
                } else {
                    passwordInput.classList.add('is-invalid');
                    passwordFeedback.style.display = 'block';
                }
                return passwordValid;
            }

            function validateConfirmPassword() {
                if (confirmPasswordInput.value === passwordInput.value) {
                    confirmPasswordInput.classList.remove('is-invalid');
                    confirmPasswordFeedback.style.display = 'none';
                } else {
                    confirmPasswordInput.classList.add('is-invalid');
                    confirmPasswordFeedback.style.display = 'block';
                }
            }

            passwordInput.addEventListener('input', function () {
                validatePassword();
                validateConfirmPassword();
            });

            confirmPasswordInput.addEventListener('input', function () {
                validateConfirmPassword();
            });
        });

        //Eye Toggle for password
        const togglePassword = document.querySelector('#togglePassword');
        const passwordInput = document.querySelector('#password');
        const toggleIcon = document.querySelector('#toggleIcon');

        togglePassword.addEventListener('click', function () {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            toggleIcon.classList.toggle('bi-eye');
            toggleIcon.classList.toggle('bi-eye-slash');
        });

        //Eye Toggle for password confirmation
        const toggleConfirmPassword = document.querySelector('#toggleConfirmPassword');
        const confirmPasswordInput = document.querySelector('#confirm-password');
        const toggleConfirmIcon = document.querySelector('#toggleConfirmIcon');

        toggleConfirmPassword.addEventListener('click', function () {
            const type = confirmPasswordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            confirmPasswordInput.setAttribute('type', type);
            toggleConfirmIcon.classList.toggle('bi-eye');
            toggleConfirmIcon.classList.toggle('bi-eye-slash');
        });
    </script>
    <script src="login.js"></script>
    <script src="register.js"></script>
    <script src="spinner.js"></script>
</body>
</html>
