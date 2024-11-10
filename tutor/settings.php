<?php
session_start();
require_once '../tutor.php';
$user_login = new TUTOR();
if (!$user_login->is_logged_in()) {
    $user_login->redirect('login');
}
$tutorSession = $_SESSION['tutorSession'];
$stmt = $user_login->runQuery("SELECT * FROM tutor WHERE student_id = :student_id");
$stmt->bindParam(":student_id", $tutorSession);
$stmt->execute();
$userData = $stmt->fetch(PDO::FETCH_ASSOC);
$firstname = $userData['firstname'];
$lastname = $userData['lastname'];
$age = $userData['age'];
$sex = $userData['sex'];
$number = $userData['number'];
$barangay = $userData['barangay'];
$student_id = $userData['student_id'];
$course = $userData['course'];
$year_section = $userData['year_section'];
$fblink = $userData['fblink'];
$emailaddress = $userData['emailaddress'];
$tutor_id = $userData['id'];

// Check if $userData['photo'] is empty
if (!empty($userData['photo'])) {
    // If not empty, display the uploaded image
    $imagePath = $userData['photo'];
} else {
    // If empty, provide a default image path
    $imagePath = '../assets/TuteeFindLogoName.jpg'; // Update this with your default image path
}
// Fetch unread notifications count for the current tutor
$unreadNotifQuery = $user_login->runQuery("SELECT COUNT(*) AS unread_count FROM notifications WHERE receiver_id = :tutor_id AND status = 'unread'");
$unreadNotifQuery->bindParam(":tutor_id", $tutor_id);
$unreadNotifQuery->execute();
$unreadNotifData = $unreadNotifQuery->fetch(PDO::FETCH_ASSOC);
$unreadNotifCount = $unreadNotifData['unread_count'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $fblink = $_POST['fblink'];
    $number = $_POST['number'];
    $barangay = $_POST['barangay'];
    $emailaddress = $_POST['emailaddress'];
    $newPassword = $_POST['password'];
    $photo = $_FILES['photo'];

    // Assuming you have $userData available before calling updateDetails
    $user_login->updateDetails($firstname, $lastname, $age, $sex, $number, $barangay, $student_id, $course, $year_section, $fblink, $emailaddress, $newPassword, $photo, $userData);

}
// Fetch count of unique tutees who have unread messages
$unreadMessagesQuery = $user_login->runQuery("
    SELECT COUNT(DISTINCT tutee_id) AS unread_tutee_count 
    FROM messages 
    WHERE tutor_id = :tutor_id 
    AND sender_type = 'tutee' 
    AND is_read = 0
");
$unreadMessagesQuery->bindParam(":tutor_id", $tutor_id);
$unreadMessagesQuery->execute();
$unreadMessagesData = $unreadMessagesQuery->fetch(PDO::FETCH_ASSOC);
$unreadMessageCount = $unreadMessagesData['unread_tutee_count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="what.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="icon" href="../assets/TuteeFindLogo.png" type="image/png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <title>Settings</title>
</head>
<body>
<nav class="sidebar close">
        <header>
            <div class="image-text">
                <span class="image">
                <img src="<?php echo $imagePath;?>" alt="logo">
                </span>

                <div class="div text header-text">
                <span class="name"><?php echo $firstname . ' ' . $lastname; ?></span>
                <span class="name"><?php echo $tutorSession; ?></span>
                    <span class="position">Tutor</span>
                </div>
            </div>

            <i class='bx bxs-chevron-right toggle'></i>
        </header>

        <div class="menu-bar">
        <div class="menu">
                <ul class="menu-links" data-bs-placement="right" title="Home">
                    <li class="nav-link">
                        <a href="../tutor/suggestedtutee">
                            <i class='bx bx-home-alt icon'></i>
                            <span class="text nav-text">Home</span>
                        </a>
                    </li>
                    <li class="nav-link" data-bs-placement="right" title="Messages">
                        <a href="../tutor/message">
                            <div style="position: relative;">
                                <i class='bx bxs-inbox icon'></i>
                                <span id="message-count" class="badge bg-danger" style="position: absolute; top: -12px; right: -0px; font-size: 0.75rem;">
                                    <?php echo $unreadMessageCount; ?>
                                </span> <!-- Notification counter -->
                            </div>
                            <span class="text nav-text">Messages</span>
                        </a>
                    </li>
                    <li class="nav-link" data-bs-placement="right" title="Notification">
                        <a href="../tutor/notification" class="d-flex align-items-center">
                            <div style="position: relative;">
                                <i class='bx bx-bell icon'></i>
                                <span id="notif-count" class="badge bg-danger" style="position: absolute; top: -12px; right: -0px; font-size: 0.75rem;">
                                    <?php echo $unreadNotifCount; ?>
                                </span> <!-- Notification counter -->
                            </div>
                            <span class="text nav-text">Notification</span>
                        </a>
                    </li>
                    <li class="nav-link" data-bs-placement="right" title="Tutor Progress">
                        <a href="../tutor/progress">
                            <i class='bx bx-bar-chart-alt icon'></i>
                            <span class="text nav-text">Progress</span>
                        </a>
                    </li>
                    <li class="nav-link">
                        <a href="../tutor/currenttutor" data-bs-placement="right" title="Current Tutee">
                            <i class='bx bx-user icon'></i>
                            <span class="text nav-text">Tutors</span>
                        </a>
                    </li>
                    <li class="nav-link custom-bg" data-bs-placement="right" title="Settings">
                        <a href="../tutor/settings">
                            <i class='bx bx-cog icon'></i>
                            <span class="text nav-text">Settings</span>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="bottom-content">
                <li class="">
                <a href="../tutor/logout">
                        <i class='bx bx-log-out icon'></i>
                        <span class="text nav-text">Logout</span>
                    </a>
                </li>

                <li class="mode">
                    <div class="moon-sun">
                        <i class='bx bx-moon icon moon'></i>
                        <i class='bx bx-sun icon sun'></i>
                    </div>
                    <span class="mode-text text">Dark Mode</span>

                    <div class="toggle-switch">
                        <span class="switch"></span>
                    </div>

                </li>
            </div>
        </div>
    </nav>
    <div class="home">
    <form method="post" class="container " enctype="multipart/form-data">
        <div class="container-lg py-3">
            <div class="career-form headings d-flex justify-content-center mt-3">
                <div class="row">
                    <div class="card1" style="color:white;">Edit Profile</div>
                </div>
            </div>
        </div>
        
        <!-- container1 -->
<div class="container">
    <div class="container update shadow-lg rounded-3">
        <div class="d-flex align-items-center pt-3">
            <!-- Image and Bio on the left side -->
            <div class="container p-3">
                <div class="row">
                    <!-- Image and upload section with Bio -->
                    <div class="col-12 col-md-4 text-center">
                        <img id="profile-image" class="rounded-circle my-3 img-fluid" width="153" src="<?php echo $imagePath; ?>">
                        <label for="file-upload" class="blue p-2 mb-2 rounded-3 w-100">
                            Upload File
                            <input id="file-upload" type="file" name="photo" style="display:none;" onchange="previewImage(event)">
                        </label>
                        <div class="mb-3">
                            <div class="labels">- at least 256 x 256 px recommended JPG or PNG.</div>
                        </div>
                        <!-- Bio section under the image upload -->
                        <div class="form-group text-start mb-4">
                            <label class="nav-text info-header">Bio</label>
                            <input class="form-control custom-input" name="bio" id="bio" rows="1" placeholder="Tell us a little about yourself..."></input>
                        </div>
                    </div>

                    <!-- First Name, Email, Barangay, and Password section -->
                    <div class="col-12 col-md-4">
                        <div class="form-group mb-4">
                            <label class="nav-text info-header">First Name</label>
                            <input type="text" class="form-control custom-input" id="firstname" name="firstname" placeholder="First name" value="<?php echo htmlspecialchars($firstname); ?>">
                        </div>
                        <div class="form-group mb-4">
                            <label class="nav-text info-header">Email Address</label>
                            <input type="text" class="form-control" placeholder="Email Address" name="emailaddress" value="<?php echo htmlspecialchars($emailaddress); ?>">
                        </div>
                        <div class="form-group mb-3">
                            <label class="nav-text info-header">Barangay</label>
                            <input name="barangay" type="text" class="form-control" placeholder="Barangay" value="<?php echo htmlspecialchars($barangay); ?>">
                        </div>
                        <hr class="w-100">
                        <div class="form-group mt-3 mb-4">
                            <label for="password" class="nav-text info-header">New Password</label>
                            <input type="password" class="form-control" placeholder="New Password" name="password" id="password">
                            <div class="invalid-feedback" id="password-feedback">Password must be at least 8 characters long and contain at least one number.</div>
                        </div>
                    </div>

                    <!-- Last Name, Contact, Facebook Link, and Confirm Password section -->
                    <div class="col-12 col-md-4">
                        <div class="form-group mb-4">
                            <label class="nav-text info-header">Last Name</label>
                            <input type="text" class="form-control" placeholder="Last name" name="lastname" value="<?php echo htmlspecialchars($lastname); ?>">
                        </div>
                        <div class="form-group mb-4">
                            <label class="nav-text info-header">Contact Number</label>
                            <input type="text" class="form-control" placeholder="Contact number" name="number" value="<?php echo htmlspecialchars($number); ?>">
                        </div>
                        <div class="form-group mb-3">
                            <label class="nav-text info-header">Facebook Link</label>
                            <input type="text" class="form-control" placeholder="Facebook Link" name="fblink" value="<?php echo htmlspecialchars($fblink); ?>">
                        </div>
                        <hr class="w-100">
                        <div class="form-group mt-3 mb-0">
                            <label for="confirm-password" class="nav-text info-header">Confirm Password</label>
                            <input type="password" class="form-control" placeholder="Confirm Password" name="confirm_password" id="confirm-password">
                            <div class="invalid-feedback">Passwords do not match.</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Save Button -->
            </div>
                <div class="align-content-center d-flex justify-content-center">
                    <button type="submit" class="blue m-3 rounded-3" id="saveButton" style="width: 30%; height: 40px;">Save</button>
                </div>
            </div>
        </div>
    </div>
</div>

        <!-- Success Modal -->
        <div class="modal fade" id="updateModal" tabindex="-1" aria-labelledby="updateModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header d-flex justify-content-center align-items-center border-0 mt-2">
                        <div class="checkmark-container">
                            <svg class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                                <circle class="checkmark__circle" cx="26" cy="26" r="25" fill="none"/>
                                <path class="checkmark__check" fill="none" d="M14 27l7.5 7.5L38 18"/>
                            </svg>
                        </div>
                    </div>
                    <div class="modal-body d-flex justify-content-center align-items-center" id="modalBody">
                        Updated successfully.
                    </div>
                    <div class="modal-footer border-0">
                        <!-- Footer left empty as per original design -->
                    </div>
                </div>
            </div>
        </div>

    </div>
    </form>
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
            const confirmPasswordFeedback = confirmPasswordInput.nextElementSibling;

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
    </script>
    <script>
            function previewImage(event) {
            var input = event.target;
            var reader = new FileReader();

            reader.onload = function(){
                var img = document.getElementById('profile-image');
                img.src = reader.result;
            };

            reader.readAsDataURL(input.files[0]);
        }

document.addEventListener('DOMContentLoaded', function () {
    const saveButton = document.getElementById('saveButton');
    const form = saveButton.closest('form'); // Find the closest form element
    const updateModal = new bootstrap.Modal(document.getElementById('updateModal'));

    saveButton.addEventListener('click', function(event) {
        event.preventDefault(); // Prevent form submission initially

        // Show the success modal
        updateModal.show();

        // After the modal is hidden, submit the form
        document.getElementById('updateModal').addEventListener('hidden.bs.modal', function () {
            form.submit(); // Submit the form after modal is closed
        });
    });
});

    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="script.js"></script>
</body>
</html>