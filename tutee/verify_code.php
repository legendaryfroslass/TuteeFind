<?php
session_start();
include('spinner.php');

// Check if the reset code is set in the session
if (!isset($_SESSION['email']) || !isset($_SESSION['reset_code'])) {
    $_SESSION['error'] = "Reset code is not set. Please request a new code.";
    header('Location: forgotpassword.php?error'); // Redirect to a page to request a new code
    exit();
} elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if form is submitted
    $input_code = $_POST['code'] ?? ''; // Use null coalescing operator to avoid undefined index

    // Compare the input code with the session reset code
    if ($input_code == $_SESSION['reset_code']) {
        $_SESSION['verified'] = true; // Set verified flag in session
        header('Location: change_password.php'); // Make sure to include the .php extension
        exit();
    } else {
        $_SESSION['error'] = "Invalid code. Please try again.";
        header('Location: verify_code.php'); // Redirect to the same page to show the error
        exit();
    }
}

// Optionally, you can display the error message here
if (!empty($_SESSION['error'])) {
    $modalMessage = $_SESSION['error']; // Store the error message for the modal
    unset($_SESSION['error']); // Clear the error after displaying it
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
    <link rel="stylesheet" href="spinner.css">
    <title>Verify Code</title>
    <style>
        .bg-container {
            background-image: url("../assets/verify_code.jpg");
        }
        .btn-no-outline {
        border: none;        /* Remove border */
        outline: none;        /* Remove the default outline on focus */
        background: none;     /* Ensure no background is applied */
        box-shadow: none;     /* Remove any potential shadow */
        padding: 0;           /* Optional: To ensure no extra padding */
    }

    .btn-no-outline:focus {
        outline: none;        /* Prevent outline when focused */
        box-shadow: none;     /* Prevent box-shadow when focused */
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
            <div class="d-grid gap-2 d-md-block mb-5">
                <button type="button" onclick="window.location.href='forgotpassword.php'" class="btn-no-outline">
                    <i class="bi bi-arrow-left-circle fs-2"></i>
                </button>
            </div>
            <div class="row justify-content-center">
                <div class="header-text mb-4">
                    <h2>Verify Code</h2>
                    <p>Please enter the verification code sent to your email.</p>
                </div>
                <form method="POST" action="">
                    <div class="input-group mb-5">
                        <input type="text" name="code" class="form-control form-control-lg bg-light fs-6" placeholder="Verification code" required>
                    </div>
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    <div class="input-group mb-3">
                        <button type="submit" class="btn btn-lg btn-primary w-100 fs-6">Verify</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>