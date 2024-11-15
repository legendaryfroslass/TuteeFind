<?php
require_once '../tutee.php';
session_start();
$reg_user = new TUTEE();
$message = '';
$messageType = '';
require '../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;
require '../vendor/phpmailer/phpmailer/src/Exception.php';
require '../vendor/phpmailer/phpmailer/src/SMTP.php';
require '../vendor/phpmailer/phpmailer/src/PHPMailer.php';
// Handle OTP generation and email sending
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['sendEmail'])) {
    $emailaddress = $_POST['emailaddress'];
    // Check for duplicate email
    if ($reg_user->isDuplicate($emailaddress)) {
        echo "duplicate";
        exit;  // Stop further execution to avoid additional output
    }
    // Generate OTP and attempt to send email
    $otp = random_int(100000, 999999);
    $_SESSION['otp'] = $otp;
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'findtutee@gmail.com';
        $mail->Password = 'tzbb qafz fhar ryzf';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->setFrom('findtutee@gmail.com', 'TUTEEFIND');
        $mail->addAddress($emailaddress);
        $mail->Subject = 'Your Verification Code';
        $mail->Body = "Your Verification code is: $otp";
        if ($mail->send()) {
            echo "success";
            exit;  // Exit to ensure no additional output
        }
    } catch (Exception $e) {
        echo "Error sending OTP: " . $mail->ErrorInfo;
        exit;  // Exit to ensure no additional output
    }
}
// Verify OTP and proceed to password form
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['verifyOTP'])) {
    $userOtp = $_POST['otp'];
    if ($userOtp == $_SESSION['otp']) {
        error_log("OTP validation succeeded.");
        echo "success";  // Respond with success only for valid OTP
        exit;
    } else {
        error_log("OTP validation failed.");
        echo "Invalid OTP. Please try again.";  // Respond with error for invalid OTP
        exit;
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve form data
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $age = $_POST['calculatedAge'];
    $sex = $_POST['sex'];
    $guardianname = $_POST['guardianname'];
    $fblink = $_POST['fblink'];
    $barangay = $_POST['barangay'];
    $number = $_POST['number'];
    $emailaddress = $_POST['emailaddress'];
    $password = $_POST['password'];
    $tutee_bday = $_POST['birthday'];
    $school = $_POST['school'];
    $grade = $_POST['grade'];
    $bio = $_POST['bio'];
    $address = $_POST['address'];
    
    // // Error messages array
    // $errors = [];
    
    // // Validation checks
    // if (empty($firstname)) $errors[] = "First name is required.";
    // if (empty($lastname)) $errors[] = "Last name is required.";
    // if (empty($age) || $age < 6 || $age > 11) $errors[] = "Age must be between 6 and 11.";
    // if (empty($sex)) $errors[] = "Sex is required.";
    // if (empty($guardianname)) $errors[] = "Guardian's name is required.";
    // if (!empty($fblink) && !filter_var($fblink, FILTER_VALIDATE_URL)) $errors[] = "Facebook link must be a valid URL.";
    // if (empty($barangay)) $errors[] = "Barangay is required.";
    // if (empty($number) || !preg_match('/^[0-9]{11}$/', $number)) $errors[] = "Contact number must be 11 digits.";
    // if (empty($emailaddress) || !filter_var($emailaddress, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email address is required.";
    // if (empty($password) || strlen($password) < 8 || !preg_match('/\d/', $password)) $errors[] = "Password must be at least 8 characters long and contain at least one number.";
    // if (empty($tutee_bday)) $errors[] = "Birthday is required.";
    // if (empty($school)) $errors[] = "School is required.";
    // if (empty($grade)) $errors[] = "Grade is required.";
    // if (empty($address)) $errors[] = "Address is required.";

    // // If there are errors, display them
    // if (!empty($errors)) {
    //     echo "<ul class='error-messages'>";
    //     foreach ($errors as $error) {
    //         echo "<li>" . htmlspecialchars($error) . "</li>";
    //     }
    //     echo "</ul>";
    // } else {
        $registrationResult = $reg_user->register($firstname, $lastname, $age, $sex, $guardianname, $fblink, $barangay, $number, $emailaddress, $password, $tutee_bday, $school, $grade, $bio, $address);

        if ($registrationResult === true) {
            // Successful registration
            header("Location: ../tutee/login?registered=success");
            exit();
        } else {
            header("Location: ../registration/register?registration=error");
            // Failed registration with a specific error
            echo "Error in registration: " . htmlspecialchars($registrationResult);
        }
    // }
    // Store message in session for displaying on the same page
    $_SESSION['message'] = $message;
    $_SESSION['messageType'] = $messageType;
    exit();
}

// Check for a message in the session to display it in the modal
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $messageType = $_SESSION['messageType'];

    // Clear message from session
    unset($_SESSION['message']);
    unset($_SESSION['messageType']);
    // Output a script to show the modal with the message
    echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                $('#modalMessage').text('$message');
                $('#resultModal').modal('show');
            });
            </script>";
}
// Get birthday and age from session (if previously set)
$tutee_bday = isset($_SESSION['birthday']) ? $_SESSION['birthday'] : '';
$age = isset($_SESSION['age']) ? $_SESSION['age'] : '';
include('../tutee/spinner.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <link rel="stylesheet" href="style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../tutee/spinner.css">
    <title>Register</title>
    <style>
        body {
            overflow: hidden;
        }
    </style>
</head>
<body>

<!-- Terms and Conditions Modal -->
<div class="modal fade" data-bs-backdrop="static" data-bs-keyboard="false" id="termCondModal" tabindex="-1" aria-labelledby="terms&condition" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="terms&condition"><strong>Terms & Conditions for TuteeFind</strong></h5>
            </div>
            <div class="modal-body">
            <p>By using the TuteeFind platform and providing your personal information, you agree to the following terms and conditions regarding data privacy and consent:</p><br>


            <b>Data Collection and Usage</b>
            <p>TuteeFind is committed to protecting your privacy. By registering on our platform and sharing your personal information, you are giving TuteeFind permission to collect, use, and process your data for the purpose of matching tutees with suitable tutors. We assure you that the information you provide will be used exclusively for educational purposes and to enhance your experience on our platform.</p><br>


            <b>Types of Information Collected</b>
            <p>We may collect various types of personal information from tutees, including but not limited to:</p>


            First and Last Name: To identify and address you correctly.<br>
            Age: To ensure appropriate tutor matches and compliance with any age-related regulations. <br>
            Birthday: To verify age and assist in accurate tutor matching.<br>
            Sex: To help personalize the tutoring experience and match preferences.<br>
            Contact Number: For direct communication and coordination with tutors.<br>
            School: To match tutees with tutors based on educational institution.<br>
            School Grade: To ensure tutor matches are appropriate for the tutor's academic level.<br>
            Guardian Name: To ensure the safety and consent for minors using our platform.<br><br>
            Facebook Link: To verify identity and facilitate additional means of communication.<br>
            Barangay: For location-based matching and coordination of service.<br>
            Email Address: For communication and account-related purposes.<br>
            Address: To facilitate location-based matching and optimize in-person sessions, if applicable.<br>
           
            <b>Use of Information</b>
            <p>The information collected is used for the following purposes:</p>


            Matching Services: To pair tutees with tutors based on their educational needs and preferences.<br>
            Communication: To contact you regarding your use of the platform, including service updates, notifications, and customer support.<br>
            Improvement of Services: To enhance and personalize your experience by understanding user behavior and preferences.<br>
            Compliance: To comply with legal obligations and protect the rights and safety of TuteeFind and its users.<br><br>
           
            <b>Data Protection</b>
            <p>TuteeFind employs industry-standard security measures to protect your personal information from unauthorized access, disclosure, or misuse. We implement administrative, technical, and physical safeguards to ensure the confidentiality, integrity, and availability of your data.</p>


            <b>Data Retention</b>
            <p>Your personal data will be retained by TuteeFind for a period of one (1) year from the date of your registration. After this one-year period, your data will be permanently deleted from our systems unless retention is required for legal or compliance reasons.</p><br>


            <b>Data Sharing</b>
            <p>Your personal information will not be shared with third parties except in the following circumstances:</p>


            Service Providers: We may share information with trusted third-party service providers who assist us in operating our platform and providing our services, subject to strict confidentiality agreements.<br>
            Legal Requirements: If required by law, we may disclose your information to comply with legal obligations, respond to government requests, or protect the rights and safety of TuteeFind and its users.<br><br>
           
            <b>User Rights</b>
            <p>You have the right to:</p>


            Access: Request access to the personal information we hold about you.<br>
            Rectification: Request correction of any inaccurate or incomplete information.<br>
            Deletion: Request deletion of your personal information, subject to certain exceptions.<br>
            Objection: Object to the processing of your personal information in certain situations.<br>
            Data Portability: Request transfer of your personal information to another service provider.<br><br>
           
            <b>Consent Withdrawal</b>
            <p>You have the right to withdraw your consent for the collection and use of your personal information at any time. To do so, please contact us at findtutee@gmail.com. Withdrawal of consent may affect your ability to use certain features of the platform.</p>


            <b>Changes to Terms and Conditions</b>
            <p>TuteeFind reserves the right to update these terms and conditions at any time. We will notify you of any significant changes through our platform or via email. Your continued use of the platform after such changes signifies your acceptance of the updated terms.</p>


            <p>By using TuteeFind, you acknowledge that you have read, understood, and agreed to these terms and conditions. If you have any questions or concerns about our data privacy practices, please contact us at findtutee@gmail.com.</p>


            Thank you for trusting TuteeFind with your educational needs. <br><br>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="" id="enableButton" required>
                    <label class="form-check-label" for="enableButton">
                        I have read, understood, and agree to the TuteeFind terms & conditions
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button id="acceptBtn" class="btn btn-primary" data-bs-dismiss="modal" disabled>Accept</button>
            </div>
        </div>
    </div>
</div>


    <!-- Modal -->
    <div class="modal fade" id="resultModal" tabindex="-1" aria-labelledby="resultModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content m-3">
                <div class="modal-body" style="text-align: center;">
                    <div class="icon-wrapper m-3">
                        <div class="m-3">
                            <i class="bi bi-exclamation-triangle" id="warningAlertIcon"></i>
                        </div>
                    </div>
                    <h5>Oops! Something Went Wrong</h5>
                    <p id="modalMessage" class="m-3"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Dismiss</button>
                </div>
            </div>
        </div>
    </div>


    <!-- Modal -->
    <div class="modal fade" id="loginSuccessModal" tabindex="-1" aria-labelledby="loginSuccessModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content m-3">
                <div class="modal-body" style="text-align: center;">
                    <div class="icon-wrapper m-3">
                        <div class="m-3">
                            <i class="bi bi-exclamation-triangle"></i>
                        </div>
                    </div>
                    <h5>Welcome</h5>
                    <p id="modalMessageSucc" class="m-3"></p>
                </div>
            </div>
        </div>
    </div>


<form id="registrationForm" class="form-floating" method="post">
    <div class="form1 transition bg-container">
        <div class="container d-flex justify-content-center align-items-center min-vh-100 transition">
            <div class="row border rounded-5 p-3 bg-white shadow box-area">
                <div class="col-md-6 rounded-4 d-flex justify-content-center align-items-center flex-column left-box">
                    <img src="../assets/tuteefind.jpg" class="img-fluid custom-height" style="border-radius: 20px 0 0 20px;">
                </div>
                <!-- Forms -->
                
                <div class="col-md-6 right-box">
                    <div class="row justify-content-center">
                    
                        <div class="header-text text-center mb-4">
                        
                            <h2>Create Account</h2>
                            <p>Fill all the information needed</p>
                            <div class="progress mb-4">
                                <div class="progress-bar" role="progressbar" style="width: 10%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            
                        </div>
                            <div class="form-floating mb-3">
                                <input type="text" name="firstname" class="first-name form-control form-control-lg bg-light fs-6" id="tuteeFirstName" placeholder="First Name" required>
                                <label for="tuteeFirstName">Tutee First Name</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="text" name="lastname" class="last-name form-control form-control-lg bg-light fs-6" id="tuteeLastName" placeholder="Last Name" required>
                                <label for="tuteeLastName">Tutee Last Name</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="text" name="calculatedAge" class="form-control form-control-lg bg-light fs-6" id="tuteeCalculatedAge" placeholder="Calculated Age" value="<?php echo htmlspecialchars($age); ?>" readonly required>
                                <label for="calculatedAge">Tutee's Age</label>
                                <div class="invalid-feedback" id="birthday-feedback">Sorry! You must be between <b>6</b> and <b>11</b> years old to register.</div>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="date" name="birthday" class="form-control form-control-lg bg-light fs-6" id="tuteeBirthday" placeholder="Tutee Birthday" value="<?php echo htmlspecialchars($tutee_bday); ?>" required>
                                <label for="tuteeBirthday">Tutee Birthday</label>
                            </div>
                            <div class="input-group mb-3">
                                <select name="sex" class="sex form-select form-control-lg bg-light fs-6" aria-label="Default select example" required>
                                    <option value="" disabled selected>Sex</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="number" name="number" maxlength="11" class="last-name form-control form-control-lg bg-light fs-6 numOnly" id="contactNo" placeholder="Contact Number" required>
                                <label for="contactNo">Contact Number</label>
                            </div>
                        <div class="d-flex align-items-center justify-content-center input-group mb-2">
                            <a class="btn btn-lg btn-primary w-100 fs-6" href="#" role="button" type="button" id="nextButton-1">Next</a>
                        </div>
                        <div class="d-flex align-items-center justify-content-center mb-3">
                            <a href="../tutee/login" style="text-decoration: none;">I already have an account</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- FORM 2 -->
    <div class="form2 d-none transition bg-container">
        <div class="container d-flex justify-content-center align-items-center min-vh-100">
            <div class="row border rounded-5 p-3 bg-white shadow box-area">
                <div class="col-md-6 rounded-4 d-flex justify-content-center align-items-center flex-column left-box">
                    <img src="../assets/tuteefind.jpg" class="img-fluid custom-height" style="border-radius: 20px 0 0 20px;">
                </div>
                <!-- Forms -->
                <div class="col-md-6 right-box">
                    <div class="row justify-content-center">
                        <div class="header-text text-center mb-4">
                            <h2>Create Account</h2>
                            <p>Fill all the information needed</p>
                            <div class="progress">
                                <div class="progress-bar" role="progressbar" style="width: 45%" aria-valuenow="66.67" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                        <div>
                            <div class="contact-number form-floating mb-3">
                                <input type="text" name="school" class="form-control form-control-lg bg-light fs-6" id="tuteeSchool" placeholder="Tutee School" required>
                                <label for="tuteeSchool">Tutee School</label>
                            </div>
                        </div>
                        <div>
                            <div class="input-group mb-3">
                                <select name="grade" class="barangay form-select form-control-lg bg-light fs-6" aria-label="Default select example" required>
                                    <option value="" disabled selected>Tutee Grade</option>
                                    <!-- <option value="Preschool">Preschool</option> -->
                                    <option value="Grade 1">Grade 1</option>
                                    <option value="Grade 2">Grade 2</option>
                                    <option value="Grade 3">Grade 3</option>
                                    <option value="Grade 4">Grade 4</option>
                                    <option value="Grade 5">Grade 5</option>
                                    <option value="Grade 6">Grade 6</option>
                                    <option value="Grade 7">Grade 7</option>
                                    <option value="Grade 8">Grade 8</option>
                                    <option value="Out of School Youth">Out of School Youth</option>
                                </select>
                            </div>
                        </div>
                        <div>
                            <div class="contact-number form-floating mb-3">
                                <input type="text" name="guardianname" class="form-control form-control-lg bg-light fs-6" id="guardianName" placeholder="Name of Guardian" required>
                                <label for="guardianName">Name of Guardian</label>
                            </div>
                        </div>
                        <div>
                            <div class="contact-number form-floating mb-3">
                                <input type="text" name="fblink" class="form-control form-control-lg bg-light fs-6" id="facebookLink" placeholder="Facebook Link">
                                <label for="facebookLink">FaceBook Link</label>
                            </div>
                        </div>
                        <div class="input-group mb-3">
                            <select name="barangay" id="barangay" class="barangay form-select form-control-lg bg-light fs-6" aria-label="Default select example" required>
                            <option value="" disabled selected>Barangay</option>
                            <option value="Arkong Bato">Arkong Bato</option>
                            <option value="Bagbaguin">Bagbaguin</option>
                            <option value="Balangkas">Balangkas</option>
                            <option value="Bignay">Bignay</option>
                            <option value="Bisig">Bisig</option>
                            <option value="Canumay East">Canumay East</option>
                            <option value="Canumay West">Canumay West</option>
                            <option value="Coloong">Coloong</option>
                            <option value="Dalandanan">Dalandanan</option>
                            <option value="Gen. T. de Leon">Gen. T. de Leon</option>
                            <option value="Isla">Isla</option>
                            <option value="Karuhatan">Karuhatan</option>
                            <option value="Lawang Bato">Lawang Bato</option>
                            <option value="Lingunan">Lingunan</option>
                            <option value="Mabolo">Mabolo</option>
                            <option value="Malanday">Malanday</option>
                            <option value="Malinta">Malinta</option>
                            <option value="Mapulang Lupa">Mapulang Lupa</option>
                            <option value="Marulas">Marulas</option>
                            <option value="Maysan">Maysan</option>
                            <option value="Palasan">Palasan</option>
                            <option value="Parada">Parada</option>
                            <option value="Pariancillo Villa">Pariancillo Villa</option>
                            <option value="Paso de Blas">Paso de Blas</option>
                            <option value="Pasolo">Pasolo</option>
                            <option value="Poblacion">Poblacion</option>
                            <option value="Polo">Polo</option>
                            <option value="Punturin">Punturin</option>
                            <option value="Rincon">Rincon</option>
                            <option value="Tagalag">Tagalag</option>
                            <option value="Ugong">Ugong</option>
                            <option value="Veinte Reales">Veinte Reales</option>
                            <option value="Wawang Pulo">Wawang Pulo</option>
                            </select>
                        </div>
                        <!-- Address -->
                        <div>
                            <div class="contact-number form-floating mb-3">
                                <input type="text" name="address" class="form-control form-control-lg bg-light fs-6" id="address" placeholder="Address">
                                <label for="address">Address</label>
                            </div>
                        </div>

                        <div>
                            <div class="contact-number form-floating mb-3">
                                <input type="hidden" name="bio" class="form-control form-control-lg bg-light fs-6" id="bio" placeholder="bio" value="<?php echo "Tell something about you, or your Child. Have a nice day."; ?>">
                            </div>
                        </div>

                        <div class="row btnClass">
                                <div class="col-md-6">
                                    <a class="btn btn-lg btn-secondary fs-6" href="#" role="button" id="backButton-2">Back</a>
                                </div>
                                <div class="col-md-6">
                                    <a class="btn btn-lg btn-primary fs-6" href="#" role="button" id="nextButton-2">Next</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- FORM 3 -->
    <div class="form3 transition d-none bg-container">
        <div class="container d-flex justify-content-center align-items-center min-vh-100">
            <div class="row border rounded-5 p-3 bg-white shadow box-area">
                <div class="col-md-6 rounded-4 d-flex justify-content-center align-items-center flex-column left-box">
                    <img src="../assets/tuteefind.jpg" class="img-fluid custom-height" style="border-radius: 20px 0 0 20px;">
                </div>
                <!-- Forms -->
                <div class="col-md-6 right-box">
                    <div class="row justify-content-center">
                        <div class="header-text text-center mb-4">
                            <h2>Create Account</h2>
                            <p>Fill all the information needed</p>
                            <div class="progress">
                                <div class="progress-bar" role="progressbar" style="width: 90%" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                        <div>
                            <div class="form-floating mb-3">
                                <input type="email" name="emailaddress" class="email form-control form-control-lg bg-light fs-6" style="margin: 10vh 0 5vh 0" id="email" placeholder="Email" required>
                                <label for="email">Email</label>
                            </div>
                        </div>
                        <div class="input-group btnClass">
                            <div class="row justify-content-center">
                                <div class="col-md-4 text-center">
                                    <a class="btn btn-lg btn-secondary fs-6" href="#" role="button" id="backButton-3">Back</a>
                                </div>
                                <div class="col-md-8 text-center">
                                    <button class="btn btn-lg btn-primary fs-6 d-flex justify-content-center align-items-center" onclick="showSpinner(); setTimeout(hideSpinner, 5000);" id="nextButton-3" name="sendEmail">
                                        Send OTP <i class="bi bi-send ms-2"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- FORM 4: OTP Verification -->
    <div class="form4 transition d-none bg-container">
        <div class="container d-flex justify-content-center align-items-center min-vh-100">
            <div class="row border rounded-5 p-3 bg-white shadow box-area">
                <div class="col-md-6 rounded-4 d-flex justify-content-center align-items-center flex-column left-box">
                    <img src="../assets/tuteefind.jpg" class="img-fluid custom-height" style="border-radius: 20px 0 0 20px;">
                </div>
                <div class="col-md-6 right-box">
                    <div class="row justify-content-center">
                        <div class="header-text text-center mb-4">
                            <h2>Email Verification</h2>
                            <p>Enter the OTP sent to your email</p>
                        </div>
                        <div class="form-floating mb-3">
                            <input type="text" name="otp" class="otp form-control form-control-lg bg-light fs-6" style="margin: 10vh 0 5vh 0" id="otp" placeholder="Enter OTP" required>
                            <label for="otp" style="padding: 13vh 0 0 4vh">OTP</label>
                        </div>
                        <div class="input-group btnClass">
                            <div class="row justify-content-center">
                                <div class="col-md-4">
                                    <a class="btn btn-lg btn-secondary fs-6" href="#" role="button" id="backButton-4">Back</a>
                                </div>
                                <div class="col-md-8">
                                    <button class="btn btn-lg btn-primary fs-6" id="nextButton-4" name="verifyOTP" onclick="showSpinner(); setTimeout(hideSpinner, 1000);">Verify OTP</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- FORM 5 -->
    <div class="form5 transition d-none bg-container">
        <div class="container d-flex justify-content-center align-items-center min-vh-100">
            <div class="row border rounded-5 p-3 bg-white shadow box-area">
                <div class="col-md-6 rounded-4 d-flex justify-content-center align-items-center flex-column left-box">
                    <img src="../assets/tuteefind.jpg" class="img-fluid custom-height" style="border-radius: 20px 0 0 20px;">
                </div>
                <!-- Forms -->
                <div class="col-md-6 right-box">
                    <div class="row justify-content-center">
                        <div class="header-text text-center mb-4">
                            <h2>Create Account</h2>
                            <p>Fill all the information needed</p>
                            <div class="progress">
                                <div class="progress-bar" role="progressbar" style="width: 90%" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                        <div>
                            <div class="form-floating mb-3 position-relative">
                                <input type="password" name="password" class="password form-control form-control-lg bg-light fs-6" id="password" placeholder="Password" required>
                                <label for="password">Password</label>
                                <span class="toggle-password position-absolute" id="togglePassword" style="cursor: pointer;">
                                    <i class="bi bi-eye" id="toggleIcon"></i>
                                </span>
                                <div class="invalid-feedback" id="passwordFeedback">Password must be at least 8 characters long and contain at least one number.</div>
                            </div>
                        </div>
                        <div>
                            <div class="form-floating mb-3 position-relative">
                                <input type="password" name="confirmPassword" class="confirm-password form-control form-control-lg bg-light fs-6" id="confirm-password" placeholder="Confirm Password" required>
                                <label for="confirm-password">Confirm Password</label>
                                <span class="toggle-password position-absolute" id="toggleConfirmPassword" style="cursor: pointer;">
                                    <i class="bi bi-eye" id="toggleConfirmIcon"></i>
                                </span>
                                <div class="invalid-feedback" id="confirmPasswordFeedback">Passwords do not match.</div>
                            </div>
                        </div>
                        <div class="input-group btnClass">
                            <div class="row justify-content-center">
                                <div class="col-md-6">
                                    <a class="btn btn-lg btn-secondary fs-6" href="#" role="button" id="backButton-5">Back</a>
                                </div>
                                <div class="col-md-6">
                                    <button type="submit" class="btn btn-lg btn-primary fs-6" >Register</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.js"></script>
        <script src="../tutee/spinner.js"></script>
        <script src="register.js"></script>
        <script>
            
            window.onload = function() {
                var regModal = new bootstrap.Modal(document.getElementById('termCondModal'));
                regModal.show();
            }
            
            document.addEventListener('DOMContentLoaded', function () {
                // Get elements
                const birthdayInput = document.getElementById('userBirthday');
                const submitAgeButton = document.getElementById('submitAge');
                const ageModal = new bootstrap.Modal(document.getElementById('ageModal'), {});
                const resultModal = new bootstrap.Modal(document.getElementById('resultModal'), {});
                const invalidFeedback = document.querySelector('.invalid-feedback');
                const modalMessage = document.getElementById('modalMessage'); // Get the message element
            });

            function showErrorMessage(message) {
                var feedbackElement = document.getElementById('bdayInvalid');
                feedbackElement.innerHTML = message;
                feedbackElement.style.display = 'block';
                document.getElementById('userBirthday').classList.add('is-invalid');
            }

            document.getElementById('tuteeBirthday').addEventListener('change', function() {
                // Get the birthday value from the date input
                var birthdayInput = this.value;

                if (birthdayInput) {
                    var today = new Date();
                    var birthday = new Date(birthdayInput);

                    // Calculate age in years
                    var age = today.getFullYear() - birthday.getFullYear();
                    var monthDiff = today.getMonth() - birthday.getMonth();

                    // Adjust the age if the birthday hasn't occurred yet this year
                    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthday.getDate())) {
                        age--;
                    }

                    // Display the age in the calculated age input field
                    var ageInput = document.getElementById('tuteeCalculatedAge');
                    ageInput.value = age;

                    // Optional: Validate age range (6-11)
                    var feedback = document.getElementById('birthday-feedback');
                    if (age < 6 || age > 11) {
                        ageInput.classList.add('is-invalid'); // Adds error styling
                        feedback.style.display = 'block'; // Show error message
                        showErrorMessage("Sorry! You must be between <b>6</b> and <b>11</b> years old to register.");
                    } else {
                        ageInput.classList.remove('is-invalid'); // Remove error styling
                        feedback.style.display = 'none'; // Hide error message
                    }
                }
            });

            // This replaces the previous page (registration page) in the history stack, so pressing the back button won't navigate to it.
            history.replaceState(null, null, location.href);
        </script>
        <script>
            $(document).ready(function(){
                // Handle the form submission for sending OTP
                $('#nextButton-3').click(function(e) {
                    e.preventDefault();  // Prevent form submission by default

                    const emailAddress = $('input[name="emailaddress"]').val();

                    // Validate email input
                    if (emailAddress === '') {
                        $('input[name="emailaddress"]').addClass('is-invalid');
                        $('#modalMessage').text("Please enter your email address.");
                        $('#resultModal').modal('show');
                        return;
                    } else {
                        $('input[name="emailaddress"]').removeClass('is-invalid');
                    }

                    // Show the spinner while sending the request
                    showSpinner();

                    // Send the request using AJAX
                    $.ajax({
                        type: 'POST',
                        url: '', // Correctly set to your PHP script
                        data: { sendEmail: true, emailaddress: emailAddress },
                        success: function(response) {
                            if (response.trim() === "success") {
                                // Transition to Form 4 if OTP was successfully sent
                                $('.form3').addClass('d-none');
                                $('.form4').removeClass('d-none');
                            } else if (response.trim() === "duplicate") {
                                $('#modalMessage').text("An account with this email already exists.");
                                $('#resultModal').modal('show');
                            } else if (response.startsWith("Error sending OTP")) {
                                $('#modalMessage').text(response);
                                $('#resultModal').modal('show');
                            } else {
                                $('#modalMessage').text("Unexpected response from server.");
                                $('#resultModal').modal('show');
                            }
                            hideSpinner(); // Hide spinner after handling response
                        },
                        error: function(xhr, status, error) {
                            $('#modalMessage').text('Failed to send OTP. Please try again.');
                            $('#resultModal').modal('show');
                            hideSpinner(); // Hide spinner on error
                        }
                    });
                });
            });

            $(document).ready(function() {
    $('#nextButton-4').click(function(e) {
        e.preventDefault(); // Prevent form submission
        const otp = $('input[name="otp"]').val(); // Get entered OTP

        // AJAX request to verify OTP
        $.ajax({
            type: "POST",
            url: "",  // Replace with actual PHP file name for OTP verification
            data: { verifyOTP: true, otp: otp },
            success: function(response) {
                const trimmedResponse = response.trim();

                if (trimmedResponse === "success") {
                    // OTP is correct, proceed to next form (Form 5)
                    $('.form4').addClass('d-none');
                    $('.form5').removeClass('d-none');
                } else {
                    // Display error message for invalid OTP
                    $('#modalMessage').text("Invalid OTP. Please try again.");
                    $('#resultModal').modal('show');
                }
            },
            error: function(xhr, status, error) {
                $('#modalMessage').text('Failed to verify OTP. Please try again.');
                $('#resultModal').modal('show');
            }
        });
    });
});

        </script>
    </body>
</html>