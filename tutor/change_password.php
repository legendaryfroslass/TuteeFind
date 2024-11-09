<?php
session_start();
require_once '../tutor.php';

// Ensure both email and reset code are present in the session and the user is verified
if (!isset($_SESSION['email']) || !isset($_SESSION['reset_code']) || !isset($_SESSION['verified']) || $_SESSION['verified'] !== true) {
    header('Location: forgotpassword');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    if ($new_password == $confirm_password) {
        $tutee = new TUTOR();
        $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $tutee->runQuery("UPDATE tutor SET password=:password WHERE emailaddress=:email");
        $stmt->execute(array(":password" => $new_password_hash, ":email" => $_SESSION['email']));
        // Clear session data after successful password reset
        unset($_SESSION['reset_code']);
        unset($_SESSION['email']);
        unset($_SESSION['verified']); // Clear the verified flag
        $success = "Password changed successfully.";
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
    <link rel="stylesheet" href="style2.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="icon" href="../assets/TuteeFindLogo.png" type="image/png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <title>Change Password</title>
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
                        <h2>Change Password</h2>
                        <p>Enter your new password.</p>
                    </div>
                    <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
                    <?php if (isset($success)) echo "<div class='alert alert-success'>$success</div>"; ?>
                    <!-- <form method="POST" action="">
                                <div class="input-group mb-3">
                                    <input type="password" class="form-control form-control-lg bg-light fs-6" style="width:44.5vh;" placeholder="New Password" name="password" id="password">
                                    <div class="invalid-feedback" id="password-feedback">Password must be at least 8 characters long and contain at least one number.</div>
                                </div>
                                <div class="input-group mb-3">
                                    <input type="password" class="form-control form-control-lg bg-light fs-6" style="width:44.5vh;" placeholder="Confirm Password" name="confirm_password" id="confirm-password">
                                    <div class="invalid-feedback">Passwords do not match.</div>
                                </div>
                        <div class="input-group mb-3">
                            <button type="submit" class="btn btn-lg btn-primary w-100 fs-6">Change Password</button>
                        </div>
                    </form> -->
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
                    <div class="row align-items-center">
                        <small style="text-align: center;">Remembered your password?<a href="login" type="button" class="btn btn-link btn-sm" data-toggle="modal" data-target="#exampleModal">Log in</a></small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
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
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const passwordInput = document.getElementById('password');
            const confirmPasswordInput = document.getElementById('confirm-password');
            const passwordFeedback = document.getElementById('password-feedback');

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
</body>
</html>
