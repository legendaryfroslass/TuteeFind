<?php
session_start();

$error = ''; // Initialize the error variable

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $input_code = $_POST['code'];
    if ($input_code == $_SESSION['reset_code']) {
        $_SESSION['verified'] = true; // Set verified flag in session
        header('Location: change_password');
        exit();
    } else {
        $error = "Invalid code. Please try again.";
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
    <title>Verify Code</title>
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
                    <div class="header-text mb-0 mt-4">
                        <h2>Verify Code</h2>
                        <p>Please enter the verification code sent to your email.</p>
                    </div>
                    <form method="POST" action="">
                        <div class="input-group mb-5">
                            <input type="text" name="code" class="form-control form-control-lg bg-light fs-6" placeholder="Verification code" required>
                        </div>
                        <div class="input-group mb-3">
                            <button type="submit" class="btn btn-lg btn-primary w-100 fs-6">Verify</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
