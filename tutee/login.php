<?php

session_start();
require_once '../tutee.php';
$user_login = new TUTEE();

// Check if there's a message in the session
$message = '';
$messageType = '';
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $messageType = $_SESSION['messageType'];
    unset($_SESSION['message']);
    unset($_SESSION['messageType']);
}

if(isset($_POST['btn-login'])) {
    $emailaddress = trim($_POST['txtemail']);
    $password = trim($_POST['txtupass']);

    if ($user_login->login($emailaddress, $password)) {
        $_SESSION['role'] = 'tutee';
        $user_login->redirect("tutee");
    } else {
        $_SESSION['login_failed'] = true;
        header("Location: login?error");
        exit();
    }
}

// Check if birthday and age are set in the URL
if (isset($_GET['birthday']) && isset($_GET['age'])) {
    $_SESSION['birthday'] = $_GET['birthday'];
    $_SESSION['age'] = $_GET['age'];
}

// Check if registration was successful
if (isset($_GET['registered']) && $_GET['registered'] == 'success') {
    echo '<script>
            document.addEventListener("DOMContentLoaded", function() {
                var successModal = new bootstrap.Modal(document.getElementById("successModal"));
                successModal.show();
            });
            </script>';
}

// Check if login failed via URL parameter
if (isset($_GET['error']) || isset($_GET['notAvail'])) {
    echo '<script>
            document.addEventListener("DOMContentLoaded", function() {
                var invalidLoginModal = new bootstrap.Modal(document.getElementById("invalidLogin"));
                invalidLoginModal.show();
            });
            </script>';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="tutee.css">
    <link rel="stylesheet" href="tuteelogin.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <title>Login</title>
</head>
<body>
    <script>
            document.addEventListener('DOMContentLoaded', function() {
                <?php if ($message != ''): ?>
                    var myModal = new bootstrap.Modal(document.getElementById('resultModal'));
                    document.getElementById('modalMessage').innerText = '<?php echo $message; ?>';
                    myModal.show();
                <?php endif; ?>
            });
        </script>

        <!-- Invalid login credentials -->
        <div class="modal fade" id="invalidLogin" tabindex="-1" aria-labelledby="invalidLogin" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content m-3">
                    <div class="modal-body" style="text-align: center;">
                        <div class="icon-wrapper m-3">
                            <div class="m-3">
                                <i class="bi bi-exclamation-triangle" id="warningAlertIcon"></i>
                            </div>
                        </div>
                        <h5>Oops! Something Went Wrong</h5>
                        <p class="m-3">Incorrect Email or Password</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Dismiss</button>
                    </div>
                </div>
            </div>
        </div>

    <!-- Success Modal -->
    <div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content m-3">
                <div class="modal-body" style="text-align: center;">
                <div class="modal-header d-flex justify-content-center align-items-center border-0 m-2">
                    <!-- Boxicons Checkmark with Circular Swipe Animation -->
                    <div class="checkmark-container">
                    <svg class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                        <circle class="checkmark__circle" cx="26" cy="26" r="25" fill="none"/>
                        <path class="checkmark__check" fill="none" d="M14 27l7.5 7.5L38 18"/>
                    </svg>
                    </div>
                </div>
                    <h5>Registration Successful</h5>
                    <p class="m-3">Your account has been successfully created! Please log in to continue.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Dismiss</button>
                </div>
            </div>
        </div>
    </div>

        <!-- Modal -->
        <div class="modal fade" id="resultModal" tabindex="-1" aria-labelledby="resultModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="resultModalLabel">Registration Result</h5>
                    </div>
                    <div class="modal-body">
                        <p id="modalMessage"></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div class="modal fade" id="passwordChangedModal" tabindex="-1" aria-labelledby="passwordChangedModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content m-3">
                    <div class="modal-body" style="text-align: center;">
                        <div class="modal-header d-flex justify-content-center align-items-center border-0 mt-2">
                            <!-- Centered header content -->
                            <img src="../assets/check.png" alt="Success" class="success-icon" style="width: 65px; height: 65px;">
                        </div>
                        <h5>Success</h5>
                        <p class="m-3">Your password has been updated successfully! You may now log in with your new password.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Dismiss</button>
                    </div>
                </div>
            </div>
        </div>

    <div class="bg-container">
    <div class="col-5 d-flex align-items-center min-vh-100 left">
    <!-------------------- ------ Right Box ---------------------------->
        <div class="left-box m-5 p-4">
            <div class="pb-5">
                <img src="../assets/TuteeFindLogo.png" class="logo"><img src="../assets/TuteeFindLogoName.png" class="word-logo">
            </div>
            <div class="row justify-content-center ">
                    <div class="header-text mb-4">
                        <h2>Hello, <span style="color: #EBB55A;">Tutee</span></h2>
                        <p>We are happy to have you back!</p>
                    </div>
                    <form method="POST" action="">
                        <div class="contact-number form-floating mb-3">
                            <input type="email" class="form-control form-control-lg fs-6" placeholder="Email address" name="txtemail" required>
                            <label for="email">Email</label>
                        </div> 
                        <div>
                            <div class="contact-number form-floating mb-3">
                                <!-- Password input field -->
                                <input type="password" class="form-control form-control-lg fs-6 rounded-end" placeholder="Password" name="txtupass" id="passwordInput" required>
                                
                                <!-- Label for the input field -->
                                <label for="passwordInput">Password</label>
                                
                                <!-- Eye toggle icon positioned absolutely within the container -->
                                <span class="toggle-password position-absolute" id="togglePassword" style="cursor: pointer; right: 10px; top: 50%; transform: translateY(-50%);">
                                    <i class="bi bi-eye" id="toggleIcon"></i>
                                </span>
                                
                                <!-- Optional feedback message -->
                                <div class="invalid-feedback" id="password-feedback"></div>
                            </div>
                        </div>
                        <div class="input-group mb-5 d-flex justify-content-between">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="rememberMeCheck">
                                <label for="rememberMeCheck" class="form-check-label text-secondary"><small>Remember Me</small></label>
                            </div>
                        </div>
                        <div class="input-group mb-3">
                            <button type="submit" class="btn btn-lg btn-primary w-100 fs-6" name="btn-login">Login</button>
                        </div>
                    </form>
                    <div class="row align-items-center justify-content-center" style="text-align: center;">
                        <small>Don't have account?<a type="button" class="btn btn-link btn-sm linkless" data-bs-toggle="modal" data-bs-target="#ageModal" >Sign up</a></small>
                        <small><a href="forgotpassword" class="linkless">Forgot Password?</a></small>
                        
                    </div>
                </div>
            </div>
        </div>

        <!-- Ask birthday modal -->
        <div class="modal fade" id="ageModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="ageModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="ageModalLabel">Enter Your Child's Birthday</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form action="register.php" id="askBday" method="POST">
                            <label for="userBirthday" class="m-3">Please enter your child's birthday before proceeding:</label>
                            <input type="date" id="userBirthday" name="birthday" class="form-control ageTextbox" placeholder="Enter your birthday" required>
                            <div class="invalid-feedback text-center" id="bdayInvalid">Sorry! You must be between <b>6</b> and <b>11</b> years old to register.</div>
                            <div class="d-flex m-3">
                                <button type="button" class="btn btn-primary ms-auto" id="submitAge">Proceed</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
        <script src="login.js"></script>
    </body>
</html>