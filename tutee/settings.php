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
    $tutee_id = $userData['id'];

    // Check if $userData['photo'] is empty
    if (!empty($userData['photo'])) {
        // If not empty, display the uploaded image
        $imagePath = $userData['photo'];
    } else {
        // If empty, provide a default image path
        $imagePath = '../assets/TuteeFindLogoName.jpg'; // Update this with your default image path
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $firstname = $_POST['firstname'];
        $lastname = $_POST['lastname'];
        $age = $_POST['age'];
        $sex = $_POST['sex'];
        $guardianname = $_POST['guardianname'];
        $fblink = $_POST['fblink'];
        $barangay = $_POST['barangay'];
        $number = $_POST['number'];
        $emailaddress = $_POST['emailaddress'];
        $newPassword = $_POST['password'];
        $photo = $_FILES['photo'];
    
        // Check if email address is already in use by another account
        $emailCheckStmt = $user_login->runQuery("SELECT id FROM tutee WHERE emailaddress = :emailaddress AND id != :current_id");
        $emailCheckStmt->bindParam(":emailaddress", $emailaddress);
        $emailCheckStmt->bindParam(":current_id", $tutee_id);
        $emailCheckStmt->execute();
    
        if ($emailCheckStmt->rowCount() > 0) {
            // Email address already exists, show an error message
            echo "<div class='alert alert-danger'>This email address is already in use. Please choose a different one.</div>";
        } else {
            // If the email is unique, proceed with updating the details
            $user_login->updateDetails($firstname, $lastname, $age, $sex, $guardianname, $fblink, $barangay, $number, $emailaddress, $newPassword, $photo, $userData);
            echo "<div class='alert alert-success'>Your details have been successfully updated.</div>";
        }
    }
    

    // Fetch unread notifications count for the current tutor
    $unreadNotifQuery = $user_login->runQuery("SELECT COUNT(*) AS unread_count FROM notifications WHERE receiver_id = :tutee_id AND status = 'unread'");
    $unreadNotifQuery->bindParam(":tutee_id", $tutee_id);
    $unreadNotifQuery->execute();
    $unreadNotifData = $unreadNotifQuery->fetch(PDO::FETCH_ASSOC);
    $unreadNotifCount = $unreadNotifData['unread_count'];
?>

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
                    <ul class="navbar-nav">
                        <li class="nav-link d-flex justify-content-center align-items-center">
                            <button type="button" class="btn" data-bs-toggle="modal" data-bs-target="#logoutModal">
                                <div class="icon-container d-flex justify-content-center align-items-center">
                                    <i class='bx bx-log-out icon'></i>
                                </div>
                                <span class="text nav-text ms-2">Logout</span>
                            </button>
                        </li>
                    </ul>
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
        <form method="post" class="container-lg" style="padding-right: 4px; padding-left: 4px" enctype="multipart/form-data">
                <div class="container-lg p-3">
                    <div class="career-form headings d-flex justify-content-center mt-3">
                        <div class="row">
                            <div class="card1">Settings</div>
                        </div>
                    </div>
                </div>
                <!-- container1 -->
                <div class="container">
                    <div class="container update shadow-lg rounded-3">
                        <div class="col-4 border-right">
                            <h4 class="p-4 mt-4">Account Settings</span>
                        </div>
                        <div class="d-flex align-items-center py-3">
                            <!-- Image on the left side -->
                            <div class="container">
                                <div class="row">
                                    <!-- Image and upload section -->
                                    <div class="col-12 col-md-4 text-center">
                                        <img id="profile-image" class="rounded-circle my-3 img-fluid" width="200" src="<?php echo $imagePath; ?>">
                                        <label for="file-upload" class="blue p-2 mb-2 rounded-3 w-50">
                                            Upload File
                                            <input id="file-upload" type="file" name="photo" style="display:none;" onchange="previewImage(event)">
                                        </label>
                                        <div class="mb-3">
                                            <div class="labels">- at least 256 x 256 px recommended JPG or PNG.</div>
                                        </div>
                                    </div>
                                    <!-- First Name, Email, Sex, and Password section -->
                                    <div class="col-12 col-md-4">
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
                                        <div class="form-group mt-4">
                                            <div class="col-md mb-4">
                                                <label class="nav-text">Guardian Name</label>
                                                <input name="guardianname" type="text" class="form-control"  placeholder="Guardian Name" value="<?php echo htmlspecialchars($guardianname); ?>">
                                            </div> 
                                        </div>
                                    </div>

                                    <!-- Last Name, Contact, Facebook Link, and Age section -->
                                    <div class="col-12 col-md-4">
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
                                        <div class="select-container">
                                            <label for="confirm-password" class="nav-text info-header">Age</label>
                                            <select name="age" class="form-select mb-4" style="width:100%;" size="1">
                                            <option selected><?php echo htmlspecialchars($age); ?></option>
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
                                



                        
                        <div class="container">
                            <div class="row m-5 rounded-5 mt-3 mb-2 update shadow-lg">
                                <div class="col-4 border-right">
                                    <h4 class="p-4 mt-4">Tutoring Settings</span>
                                </div>
                                <div class="col-4 border-right">
                                    <div class="d-flex flex-column align-items-start text-left p-3 py-5">
                                        <div class="col-md-6" > 
                                            <label class="mt-5" >Barangay</label>
                                            <select class="form-select mb-3" style="width:50vh;" size="1" name="barangay">
                                                <option selected ><?php echo htmlspecialchars($barangay); ?></option>
                                                <option class="option" value="Arkong Bato">Arkong Bato</option>
                                                <option class="option" value="Bagbaguin">Bagbaguin</option>
                                                <option class="option" value="Bignay">Bignay</option>
                                                <option class="option" value="Bisig">Bisig</option>
                                                <option class="option" value="Canumayn">Canumayn</option>
                                                <option class="option" value="Coloong">Coloong</option>
                                                <option class="option" value="Dalandanan">Dalandanan</option>
                                                <option class="option" value="Isla">Isla</option>
                                                <option class="option" value="Karuhatan">Karuhatan</option>
                                                <option class="option" value="Lawang Bato">Lawang Bato</option>
                                                <option class="option" value="Lingunan">Lingunan</option>
                                                <option class="option" value="Mabolo">Mabolo</option>
                                                <option class="option" value="Malanday">Malanday</option>
                                                <option class="option" value="Malinta">Malinta</option>
                                                <option class="option" value="Mapulang Lupa">Mapulang Lupa</option>
                                                <option class="option" value="Maysan">Maysan</option>
                                                <option class="option" value="Palasan">Palasan</option>
                                                <option class="option" value="Pariancillo Villa">Pariancillo Villa</option>
                                                <option class="option" value="Pasolo">Pasolo</option>
                                                <option class="option" value="Paso de Blas">Paso de Blas</option>
                                                <option class="option" value="Poblacion">Poblacion</option>
                                                <option class="option" value="Polo">Polo</option>
                                                <option class="option" value="Punturin">Punturin</option>
                                                <option class="option" value="Rincon">Rincon</option>
                                                <option class="option" value="Tagalag">Tagalag</option>
                                                <option class="option" value="Viente Reales">Viente Reales</option>
                                            </select>
                                        </div>            
                                    </div>
                                </div>
                            </div>                
                        </div>
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
                                            <div class="invalid-feedback"  id="password-feedback">Password must be at least 8 characters long and contain at least one number.</div>
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
                        <div class="align-content-center d-flex justify-content-center">
                            <button type="submit" class="btn btn-primary mb-5" style="width: 50vh;">Save</button>
                        </div>
                    </div>
                </section>
            </div>
        </form>

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