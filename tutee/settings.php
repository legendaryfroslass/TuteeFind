<?php
session_start();
require_once '../tutee.php';
$user_login = new TUTEE();

if (!$user_login->is_logged_in()) {
    $user_login->redirect('login');
}

$userSession = $_SESSION['userSession'];
$stmt = $user_login->runQuery("SELECT * FROM tutee WHERE emailaddress = :emailaddress");
$stmt->bindParam(":emailaddress", $userSession);
$stmt->execute();
$userData = $stmt->fetch(PDO::FETCH_ASSOC);

$firstname = $userData['firstname'];
$lastname = $userData['lastname'];
$age = $userData['age'];
$sex = $userData['sex'];
$guardianname = $userData['guardianname'];
$fblink = $userData['fblink'];
$emailaddress = $userData['emailaddress'];
$barangay = $userData['barangay'];
$number = $userData['number'];
$bio = $userData['bio'];
$tutee_id = $userData['id'];

// Default image path
$imagePath = !empty($userData['photo']) ? $userData['photo'] : '../assets/TuteeFindLogoName.jpg'; 

// Initialize flag for changes and toast message
$hasChanges = false;
$toastMessage = '';
$toastClass = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the updated data from the form
    $newFirstname = $_POST['firstname'];
    $newLastname = $_POST['lastname'];
    $newAge = $_POST['age'];
    $newSex = $_POST['sex'];
    $newGuardianname = $_POST['guardianname'];
    $newFblink = $_POST['fblink'];
    $newBarangay = $_POST['barangay'];
    $newNumber = $_POST['number'];
    $newBio = $_POST['bio'];
    $newEmailaddress = $_POST['emailaddress'];
    $newPassword = $_POST['password'];
    $photo = $_FILES['photo'];

    // Check if email is already in use by another account
    $emailCheckStmt = $user_login->runQuery("SELECT id FROM tutee WHERE emailaddress = :emailaddress AND id != :current_id");
    $emailCheckStmt->bindParam(":emailaddress", $newEmailaddress);
    $emailCheckStmt->bindParam(":current_id", $tutee_id);
    $emailCheckStmt->execute();

    if ($emailCheckStmt->rowCount() > 0) {
        // Email address already in use
        $toastMessage = "This email address is already in use. Please choose a different one.";
        $toastClass = "bg-danger";
    } else {
        // Check if there are changes
        if ($newFirstname != $firstname || $newLastname != $lastname || $newAge != $age || $newSex != $sex ||
            $newGuardianname != $guardianname || $newFblink != $fblink || $newBarangay != $barangay || 
            $newNumber != $number || $newBio != $bio || $newEmailaddress != $emailaddress) {
            
            // Mark that changes have been made
            $hasChanges = true;
            
            // Update the user data in the database
            $updateStmt = $user_login->runQuery("UPDATE tutee SET firstname = :firstname, lastname = :lastname, 
                                                 age = :age, sex = :sex, guardianname = :guardianname, 
                                                 fblink = :fblink, barangay = :barangay, number = :number, 
                                                 bio = :bio, emailaddress = :emailaddress WHERE id = :id");
            $updateStmt->bindParam(":firstname", $newFirstname);
            $updateStmt->bindParam(":lastname", $newLastname);
            $updateStmt->bindParam(":age", $newAge);
            $updateStmt->bindParam(":sex", $newSex);
            $updateStmt->bindParam(":guardianname", $newGuardianname);
            $updateStmt->bindParam(":fblink", $newFblink);
            $updateStmt->bindParam(":barangay", $newBarangay);
            $updateStmt->bindParam(":number", $newNumber);
            $updateStmt->bindParam(":bio", $newBio);
            $updateStmt->bindParam(":emailaddress", $newEmailaddress);
            $updateStmt->bindParam(":id", $tutee_id);
            $updateStmt->execute();

            // Set the success toast message
            $toastMessage = "Your details have been successfully updated.";
            $toastClass = "bg-success";
        }
    }
}

// Fetch unread notifications count for the current tutor
$unreadNotifQuery = $user_login->runQuery("SELECT COUNT(*) AS unread_count FROM notifications WHERE receiver_id = :tutee_id AND status = 'unread'");
$unreadNotifQuery->bindParam(":tutee_id", $tutee_id);
$unreadNotifQuery->execute();
$unreadNotifData = $unreadNotifQuery->fetch(PDO::FETCH_ASSOC);
$unreadNotifCount = $unreadNotifData['unread_count'];

?>

<!-- HTML and Toast Code -->
<?php if ($hasChanges && !empty($toastMessage)) : ?>
    <!-- Only show the toast if there are changes -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var toastElement = document.getElementById('statusToast');
            if (toastElement) {
                var toast = new bootstrap.Toast(toastElement, { autohide: true, delay: 5000 });
                toast.show();
            }
        });
    </script>
<?php endif; ?>


<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="notif.css">
        <link rel="stylesheet" href="tutee.css">
        <link rel="stylesheet" href="settings.css">
        <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
        <link rel="icon" href="../assets/TuteeFindLogo.png" type="image/png">
        <title>Document</title>
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
                        <span class="position">Tutee</span>
                    </div>
                </div>
                <i class='bx bxs-chevron-right toggle'></i>
            </header>
            <div class="menu-bar">
                <div class="menu">
                    <ul class="menu-links">
                        <li class="nav-link">
                            <a href="../tutee/tutee">
                                <i class='bx bx-home-alt icon'></i>
                                <span class="text nav-text">Home</span>
                            </a>
                        </li>
                        <li class="nav-link">
                            <a href="../tutee/message">
                                <i class='bx bxs-inbox icon' ></i>
                                <span class="text nav-text">Messages</span>
                            </a>
                        </li>
                        <li class="nav-link">
                            <a href="../tutee/notif" class="d-flex align-items-center">
                                <div style="position: relative;">
                                    <i class='bx bx-bell icon'></i>
                                    <span id="notif-count" class="badge bg-danger" style="position: absolute; top: -12px; right: -0px; font-size: 0.75rem;">
                                        <?php echo $unreadNotifCount; ?>
                                    </span> <!-- Notification counter -->
                                </div>
                            <span class="text nav-text">Notification</span>
                            </a>
                        </li>              
                        <li class="nav-link">
                            <a href="../tutee/progress">
                                <i class='bx bx-bar-chart-alt icon'></i>
                                <span class="text nav-text">Progress</span>
                            </a>
                        </li>
                        <li class="nav-link">
                            <a href="../tutee/tutor">
                                <i class='bx bx-user icon'></i>
                                <span class="text nav-text">Tutors</span>
                            </a>
                        </li>
                        <li class="nav-link navbar-active">
                            <a href="../tutee/settings">
                                <i class='bx bx-cog icon'></i>
                                <span class="text nav-text">Settings</span>
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="bottom-content">
                    <ul class="menu-links">
                        <li class="nav-link" data-bs-toggle="tooltip" data-bs-placement="right" title="Logout">
                            <a href="#" data-bs-toggle="modal" data-bs-target="#logoutModal">
                                <i class='bx bx-log-out icon'></i>
                                <span class="text nav-text">Logout</span>
                            </a>
                        </li>
                    </ul>
                    <li class="mode" data-bs-toggle="tooltip" data-bs-placement="right" title="Toggle dark mode">
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
    <form method="post" class="container-lg" style="padding-right: 4px; padding-left: 4px" enctype="multipart/form-data">
        <div class="container-lg p-3">
            <div class="career-form headings d-flex justify-content-center mt-3">
                <div class="row">
                    <div class="card1">Settings</div>
                </div>
            </div>

            <!-- Toast Container -->
            <div aria-live="polite" aria-atomic="true" class="position-relative">
                <div class="toast-container position-absolute top-0 end-0 p-3">
                    <div id="statusToast" class="toast align-items-center border-0" role="alert" aria-live="assertive" aria-atomic="true">
                        <div class="d-flex">
                            <div class="toast-body">
                                <?php if (isset($toastMessage)) echo htmlspecialchars($toastMessage); ?>
                            </div>
                            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- container1 -->
        <div class="container">
            <div class="container update shadow-lg rounded-3" style="width: 80%; padding: 4vh;">
                <div class="col-12 border-right text-center">
                    <h4 class="p-4 mt-4">Account Settings</h4>
                </div>
                <div class="d-flex align-items-start p-3 flex-wrap">
                    <!-- Image on the left side -->
                    <div class="col-12 col-md-3 text-center">
                        <img id="profile-image" class="rounded-circle my-3 img-fluid" style="max-width: 50%;" width="200" src="<?php echo $imagePath; ?>">
                        <label for="file-upload" class="blue p-2 mb-2 rounded-3 w-50">
                            Upload File
                            <input id="file-upload" type="file" name="photo" style="display:none;" onchange="previewImage(event)">
                        </label>
                        <div class="mb-3">
                            <div class="labels">- at least 256 x 256 px recommended JPG or PNG.</div>
                        </div>
                    </div>
                    <!-- Bio on the right side, occupying more space, and centered -->
                    <div class="col-12 col-md-9 d-flex align-items-center justify-content-center">
                        <div class="form-group my-4  w-100">
                            <label class="nav-text info-header">Tutee Bio</label>
                            <textarea class="form-control" name="bio" id="bio" rows="3" style="min-height: 100px; resize: none;" placeholder="<?php echo htmlspecialchars($bio); ?>"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Section for first name, last name, email, contact number, sex, Facebook link, guardian name, and age -->
                <div class="row p-3">
                    <!-- First Name, Last Name, Email, Contact Number, Sex and Guardian Name section -->
                    <div class="col-12 col-md-6">
                        <div class="form-group mb-4">
                            <label class="nav-text info-header">First Name</label>
                            <input type="text" class="form-control custom-input" id="firstname" name="firstname" placeholder="First name" value="<?php echo htmlspecialchars($firstname); ?>">
                        </div>
                        <div class="form-group mb-4">
                            <label class="nav-text info-header">Email Address</label>
                            <input type="email" class="form-control" placeholder="Email Address" name="emailaddress" value="<?php echo htmlspecialchars($emailaddress); ?>">
                        </div>
                        <div class="form-group mb-4">
                            <label class="nav-text info-header">Sex</label>
                            <select name="sex" class="form-select mb-4" style="width:100%;" size="1">
                                <option selected><?php echo htmlspecialchars($sex); ?></option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </div>
                        <div class="form-group mb-4">
                            <label class="nav-text info-header">Guardian Name</label>
                            <input type="text" class="form-control" placeholder="Guardian Name" name="guardianname" value="<?php echo htmlspecialchars($guardianname); ?>">
                        </div>
                    </div>

                    <!-- Last Name, Contact Number, Facebook Link, and Age section -->
                    <div class="col-12 col-md-6">
                        <div class="form-group mb-4">
                            <label class="nav-text info-header">Last Name</label>
                            <input type="text" class="form-control" placeholder="Last name" name="lastname" value="<?php echo htmlspecialchars($lastname); ?>">
                        </div>
                        <div class="form-group mb-4">
                            <label class="nav-text info-header">Contact Number</label>
                            <input type="text" class="form-control" placeholder="Contact number" name="number" value="<?php echo htmlspecialchars($number); ?>">
                        </div>
                        <div class="form-group mb-4">
                            <label class="nav-text info-header">Facebook Link</label>
                            <input type="text" class="form-control" placeholder="Facebook Link" name="fblink" value="<?php echo htmlspecialchars($fblink); ?>">
                        </div>
                        
                        <div class="form-group mb-4">
                            <label class="nav-text info-header">Age</label>
                            <select name="age" class="form-select mb-4" style="width:100%;" size="1">
                                <option selected disabled><?php echo htmlspecialchars($age); ?></option>
                                <option value="6">6</option>
                                <option value="7">7</option>
                                <option value="8">8</option>
                                <option value="9">9</option>
                                <option value="10">10</option>
                                <option value="11">11</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Tutoring Settings Section -->
                <div class="container">
                    <div class="row m-5 rounded-5 mt-3 mb-2 update shadow-lg">
                        <div class="col-4 border-right">
                            <h4 class="p-4 mt-4">Tutoring Settings</h4>
                        </div>
                        <div class="col-4 border-right">
                            <div class="d-flex flex-column align-items-start text-left p-3 py-5">
                                <div class="col-md-6">
                                    <label class="mt-5">Barangay</label>
                                    <select class="form-select mb-3" style="width:50vh;" size="1" name="barangay">
                                        <option selected><?php echo htmlspecialchars($barangay); ?></option>
                                        <option value="Arkong Bato">Arkong Bato</option>
                                        <!-- Add more options as needed -->
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Change Password Section -->
                <div class="container">
                    <div class="row m-5 rounded-5 mt-3 mb-5 update shadow-lg">
                        <div class="col-4 border-right">
                            <h4 class="p-4 mt-4">Change password</h4>
                        </div>
                        <div class="col-4 border-right">
                            <div class="d-flex flex-column align-items-start text-left p-3 py-5">
                                <div class="col-md">
                                    <label class="nav-text">New Password</label>
                                    <input type="password" class="form-control mb-2" style="width:50vh;" placeholder="New Password" name="password" id="password">
                                    <div class="invalid-feedback" id="password-feedback">Password must be at least 8 characters long and contain at least one number.</div>
                                </div>
                                <div class="col-md">
                                    <label class="nav-text">Confirm Password</label>
                                    <input type="password" class="form-control mb-4" style="width:50vh;" placeholder="Confirm Password" name="confirm_password" id="confirm-password">
                                    <div class="invalid-feedback">Passwords do not match.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Save Button -->
                <div class="align-content-center d-flex justify-content-center">
                    <button type="submit" class="btn btn-primary mb-5" style="width: 50vh;">Save</button>
                </div>
            </div>
        </div>
    </form>
</div>



        <!-- Logout Confirmation Modal -->
        <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="logoutModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header d-flex justify-content-center align-items-center border-0 mt-2">
                        <!-- Centered header content -->
                        <img src="../assets/icon-logout.png" alt="Remove" class="delete-icon" style="width: 65px; height: 65px;">
                    </div>
                    <div class="modal-body d-flex justify-content-center align-items-center" id="modalBody">
                        <p>Are you sure you want to logout?</p>
                    </div>
                    <div class="modal-footer d-flex justify-content-center border-0">
                        <button type="button" class="btn btn-secondary" name="logoutModalNo" data-bs-dismiss="modal">No</button>
                        <a href="../tutee/logout">
                            <button type="button" class="btn btn-danger" name="logoutModalYes">Yes</button>
                        </a>
                    </div>
                </div>
            </div>
        </div>

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

            function previewImage(event) {
            var input = event.target;
            var reader = new FileReader();

            reader.onload = function(){
                var img = document.getElementById('profile-image');
                img.src = reader.result;
            };

            reader.readAsDataURL(input.files[0]);
        }
        </script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
        <script src="notif.js"></script>
        <script src="tutee.js"></script>
        <script src="tutee_sidebar.js"></script>
    </body>
</html>