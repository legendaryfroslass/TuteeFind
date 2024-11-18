<?php
error_reporting(1);
session_start();
require_once '../tutor.php';
$user_login = new TUTOR();

require '../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require '../vendor/phpmailer/phpmailer/src/Exception.php';
require '../vendor/phpmailer/phpmailer/src/SMTP.php';
require '../vendor/phpmailer/phpmailer/src/PHPMailer.php';

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
$course = $userData['course'];
$year_section = $userData['year_section'];
$tutor_id = $userData['id'];

$imagePath = !empty($userData['photo']) ? $userData['photo'] : '../assets/TuteeFindLogoName.jpg';

// Get the district of the tutor based on their barangay
$stmt = $user_login->runQuery("SELECT district FROM districts WHERE barangay = :barangay");
$stmt->bindParam(":barangay", $barangay);
$stmt->execute();
$tutorDistrict = $stmt->fetchColumn();

// Prepare the query to select all tutees, prioritizing those in the filtered district (match_count)
$filterQuery = "SELECT t.*, d.district,
    (CASE WHEN d.barangay = :barangay THEN 1 ELSE 0 END) AS match_count
    FROM tutee t
    INNER JOIN districts d ON t.barangay = d.barangay";

// Apply additional filters if set
$filterParams = [];
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!empty($_POST['barangay'])) {
        $filterQuery .= " AND d.barangay = :filter_barangay";
        $filterParams[':filter_barangay'] = $_POST['barangay'];
    }
}

// Exclude tutees to whom the tutor has already sent requests (except for those with a 'removed' status)
$filterQuery .= " WHERE t.id NOT IN (SELECT tutee_id FROM requests WHERE tutor_id = :tutor_id AND status != 'removed') 
    ORDER BY match_count DESC, d.district ASC";  // Prioritize matches first, then sort by district

// Prepare and execute the query
$tuteeStmt = $user_login->runQuery($filterQuery);
$tuteeStmt->bindParam(":barangay", $barangay);
$tuteeStmt->bindParam(":tutor_id", $tutor_id);

// Bind filter parameters if set
if (!empty($filterParams)) {
    foreach ($filterParams as $key => &$value) {
        $tuteeStmt->bindParam($key, $value);
    }
}

$tuteeStmt->execute();
$tutees = $tuteeStmt->fetchAll(PDO::FETCH_ASSOC);

if (isset($_POST['send_request'])) {
    // Check the number of accepted tutees for the current tutor
    $stmt = $user_login->runQuery("SELECT COUNT(*) AS num_accepted_tutees FROM requests WHERE tutor_id = :tutor_id AND status = 'accepted'");
    $stmt->bindParam(":tutor_id", $tutor_id);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $numAcceptedTutees = $row['num_accepted_tutees'];

    // Limit to 2 accepted tutees
    if ($numAcceptedTutees >= 2) {
        $_SESSION['request_result'] = "You already have the maximum number of accepted tutees allowed.";
    } else {
        $tutee_id = $_POST['tutee_id'];
        $status = 'pending';

        // Check if there's an existing request between this tutor and tutee
        $stmt = $user_login->runQuery("SELECT request_id, status FROM requests WHERE tutor_id = :tutor_id AND tutee_id = :tutee_id");
        $stmt->bindParam(":tutor_id", $tutor_id);
        $stmt->bindParam(":tutee_id", $tutee_id);
        $stmt->execute();
        $existingRequest = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existingRequest) {
            // If request exists, update its status to 'pending'
            $stmt = $user_login->runQuery("UPDATE requests SET status = :status WHERE request_id = :request_id");
            $stmt->bindParam(":status", $status);
            $stmt->bindParam(":request_id", $existingRequest['request_id']);
            if ($stmt->execute()) {
                $_SESSION['request_result'] = "Tutor request updated to pending.";
                // Insert notification into notifications table
                $title = "New Tutor Request";
                $message = "You have a new tutor request from $firstname $lastname.";
                $stmt = $user_login->runQuery("INSERT INTO notifications (sender_id, receiver_id, title, message, status, date_sent) 
                    VALUES (:tutor_id, :tutee_id, :title, :message, 'unread', NOW())");
                $stmt->bindParam(":tutor_id", $tutor_id);
                $stmt->bindParam(":tutee_id", $tutee_id);
                $stmt->bindParam(":title", $title, PDO::PARAM_STR);
                $stmt->bindParam(":message", $message, PDO::PARAM_STR);
                $stmt->execute();

                // Fetch the tutee's email
                $stmt = $user_login->runQuery("SELECT emailaddress FROM tutee WHERE id = :tutee_id");
                $stmt->bindParam(":tutee_id", $tutee_id);
                $stmt->execute();
                $tuteeEmail = $stmt->fetchColumn();
                
                // Prepare and send the email using PHPMailer
                $mail = new PHPMailer(true);
                try {
                    //Server settings
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'findtutee@gmail.com';
                    $mail->Password = 'tzbb qafz fhar ryzf';
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = 587;
                    
                    $mail->setFrom('findtutee@gmail.com', 'TUTEEFIND');
                    $mail->addAddress($tuteeEmail); // Set the recipient as the email entered in the form

                    // Content
                    $mail->isHTML(true);
                    $mail->Subject = 'New Tutor Request';
                    $mail->Body    = "
                        <h3>You have received a new tutor request</h3>
                        <p><strong>Firstname:</strong> $firstname</p>
                        <p><strong>Lastname:</strong> $lastname</p>
                        <p><strong>Age:</strong> $age</p>
                        <p><strong>Sex:</strong> $sex</p>
                        <p><strong>Contact Number:</strong> $number</p>
                        <p><strong>Barangay:</strong> $barangay</p>
                        <p><strong>Course:</strong> $course</p>
                        <p><strong>Year & Section:</strong> $year_section</p>
                    ";

                    $mail->send();
                } catch (Exception $e) {
                    $_SESSION['request_result'] = "Tutor request sent, but the email could not be sent. Mailer Error: {$mail->ErrorInfo}";
                } 
            } else {
                $_SESSION['request_result'] = "Error updating the tutor request.";
            }
        } else {
            // Insert a new request if none exists
            $stmt = $user_login->runQuery("INSERT INTO requests (tutor_id, tutee_id, status) VALUES (:tutor_id, :tutee_id, :status)");
            $stmt->bindParam(":tutor_id", $tutor_id);
            $stmt->bindParam(":tutee_id", $tutee_id);
            $stmt->bindParam(":status", $status);
            if ($stmt->execute()) {
                $_SESSION['request_result'] = "Tutor request sent successfully!";

                // Insert notification into notifications table
                $title = "New Tutor Request";
                $message = "You have a new tutor request from $firstname $lastname.";
                $stmt = $user_login->runQuery("INSERT INTO notifications (sender_id, receiver_id, title, message, status, date_sent) 
                    VALUES (:tutor_id, :tutee_id, :title, :message, 'unread', NOW())");
                $stmt->bindParam(":tutor_id", $tutor_id);
                $stmt->bindParam(":tutee_id", $tutee_id);
                $stmt->bindParam(":title", $title, PDO::PARAM_STR);
                $stmt->bindParam(":message", $message, PDO::PARAM_STR);
                $stmt->execute();

                // Fetch the tutee's email
                $stmt = $user_login->runQuery("SELECT emailaddress FROM tutee WHERE id = :tutee_id");
                $stmt->bindParam(":tutee_id", $tutee_id);
                $stmt->execute();
                $tuteeEmail = $stmt->fetchColumn();
                
                // Prepare and send the email using PHPMailer
                $mail = new PHPMailer(true);
                try {
                    //Server settings
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'findtutee@gmail.com';
                    $mail->Password = 'tzbb qafz fhar ryzf';
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = 587;
                    
                    $mail->setFrom('findtutee@gmail.com', 'TUTEEFIND');
                    $mail->addAddress($tuteeEmail); // Set the recipient as the email entered in the form

                    // Content
                    $mail->isHTML(true);
                    $mail->Subject = 'New Tutor Request';
                    $mail->Body    = "
                        <h3>You have received a new tutor request</h3>
                        <p><strong>Firstname:</strong> $firstname</p>
                        <p><strong>Lastname:</strong> $lastname</p>
                        <p><strong>Age:</strong> $age</p>
                        <p><strong>Sex:</strong> $sex</p>
                        <p><strong>Contact Number:</strong> $number</p>
                        <p><strong>Barangay:</strong> $barangay</p>
                        <p><strong>Course:</strong> $course</p>
                        <p><strong>Year & Section:</strong> $year_section</p>
                    ";

                    $mail->send();
                } catch (Exception $e) {
                    $_SESSION['request_result'] = "Tutor request sent, but the email could not be sent. Mailer Error: {$mail->ErrorInfo}";
                } 
                }else {
                $_SESSION['request_result'] = "Error sending tutor request.";
            }
        }
    }
}

// This is for the pagination of tutee requests
$items_per_page = 5;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$total_items = count($tutees);
$total_pages = ceil($total_items / $items_per_page);
$offset = ($current_page - 1) * $items_per_page;

$current_items = array_slice($tutees, $offset, $items_per_page);
// Fetch unread notifications count for the current tutor
$unreadNotifQuery = $user_login->runQuery("SELECT COUNT(*) AS unread_count FROM notifications WHERE receiver_id = :tutor_id AND status = 'unread'");
$unreadNotifQuery->bindParam(":tutor_id", $tutor_id);
$unreadNotifQuery->execute();
$unreadNotifData = $unreadNotifQuery->fetch(PDO::FETCH_ASSOC);
$unreadNotifCount = $unreadNotifData['unread_count'];

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
// Check if the form is submitted
if (isset($_POST['send_message'])) {
    $tutee_id = $_POST['tutee_id'];
    $message = $_POST['message'];

    // SQL query with positional placeholders
    $sql = "INSERT INTO messages (tutor_id, tutee_id, sender_type, message, created_at, is_read) VALUES (?, ?, 'tutor', ?, NOW(), 0)";
    $stmt = $user_login->runQuery($sql);
    $stmt->execute([$tutor_id, $tutee_id, $message]);

    // Set a session variable to indicate success
    $_SESSION['message_result'] = 'Message sent successfully!';
}
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
    <title>home</title>
    <script>
        function submitForm() {
            console.log("Form submitted");
            document.getElementById("filter-form").submit();
        }
    </script>
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
                <ul class="menu-links">
                    <li class="nav-link custom-bg" data-bs-placement="right" title="Home" data-bs-placement="right" title="Home">
                        <a href="../tutor/suggestedtutee">
                            <i class='bx bx-home-alt icon'></i>
                            <span class="text nav-text">Home</span>
                        </a>
                    </li>
                    <li class="nav-link">
                        <a href="../tutor/message" data-bs-placement="right" title="Messages">
                            <div style="position: relative;">
                                <i class='bx bxs-inbox icon'></i>
                                <span id="message-count" class="badge bg-danger" style="position: absolute; top: -12px; right: -0px; font-size: 0.75rem;">
                                    <?php echo $unreadMessageCount; ?>
                                </span> <!-- Notification counter -->
                            </div>
                            <span class="text nav-text">Messages</span>
                        </a>
                    </li>
                    <li class="nav-link"  data-bs-placement="right" title="Notification">
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
                    <li class="nav-link" data-bs-placement="right" title="Current Tutee">
                        <a href="../tutor/currenttutor">
                            <i class='bx bx-user icon'></i>
                            <span class="text nav-text">Tutors</span>
                        </a>
                    </li>
                    <li class="nav-link">
                        <a href="../tutor/settings" data-bs-placement="right" title="Settings">
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
        <div class="container-lg p-3">
            <div class="career-form headings d-flex justify-content-center mt-3">
                <div class="row">
                    <div class="card1" style="color:white;">Suggested Tutees</div>
                </div>
            </div>
        </div>
        <div class="container-lg px-3">
            <form id="filter-form" action="" class="career-form headings d-flex justify-content-start mb-2" method="post">
                            <div class="row">
                                <div class="col-12 my-1">
                                    <div class="select-container">
                                    <select class="custom-select" id="barangay" name="barangay" onchange="submitForm()">
                                        <option value="" disabled selected hidden>Barangay</option>
                                        <option <?php if(isset($_POST['barangay']) && $_POST['barangay'] == 'Arkong Bato') echo 'selected'; ?> value="Arkong Bato">Arkong Bato</option>
                                        <option <?php if(isset($_POST['barangay']) && $_POST['barangay'] == 'Bagbaguin') echo 'selected'; ?> value="Bagbaguin">Bagbaguin</option>
                                        <option <?php if(isset($_POST['barangay']) && $_POST['barangay'] == 'Balangkas') echo 'selected'; ?> value="Balangkas">Balangkas</option>
                                        <option <?php if(isset($_POST['barangay']) && $_POST['barangay'] == 'Bignay') echo 'selected'; ?> value="Bignay">Bignay</option>
                                        <option <?php if(isset($_POST['barangay']) && $_POST['barangay'] == 'Bisig') echo 'selected'; ?> value="Bisig">Bisig</option>
                                        <option <?php if(isset($_POST['barangay']) && $_POST['barangay'] == 'CanumayEast') echo 'selected'; ?> value="CanumayEast">Canumay East</option>
                                        <option <?php if(isset($_POST['barangay']) && $_POST['barangay'] == 'CanumayWest') echo 'selected'; ?> value="CanumayWest">Canumay West</option>
                                        <option <?php if(isset($_POST['barangay']) && $_POST['barangay'] == 'Coloong') echo 'selected'; ?> value="Coloong">Coloong</option>
                                        <option <?php if(isset($_POST['barangay']) && $_POST['barangay'] == 'Dalandanan') echo 'selected'; ?> value="Dalandanan">Dalandanan</option>
                                        <option <?php if(isset($_POST['barangay']) && $_POST['barangay'] == 'GenT') echo 'selected'; ?> value="GenT">General T. De Leon</option>
                                        <option <?php if(isset($_POST['barangay']) && $_POST['barangay'] == 'Isla') echo 'selected'; ?> value="Isla">Isla</option>
                                        <option <?php if(isset($_POST['barangay']) && $_POST['barangay'] == 'Karuhatan') echo 'selected'; ?> value="Karuhatan">Karuhatan</option>
                                        <option <?php if(isset($_POST['barangay']) && $_POST['barangay'] == 'Lawang Bato') echo 'selected'; ?> value="Lawang Bato">Lawang Bato</option>
                                        <option <?php if(isset($_POST['barangay']) && $_POST['barangay'] == 'Lingunan') echo 'selected'; ?> value="Lingunan">Lingunan</option>
                                        <option <?php if(isset($_POST['barangay']) && $_POST['barangay'] == 'Mabolo') echo 'selected'; ?> value="Mabolo">Mabolo</option>
                                        <option <?php if(isset($_POST['barangay']) && $_POST['barangay'] == 'Malanday') echo 'selected'; ?> value="Malanday">Malanday</option>
                                        <option <?php if(isset($_POST['barangay']) && $_POST['barangay'] == 'Malinta') echo 'selected'; ?> value="Malinta">Malinta</option>
                                        <option <?php if(isset($_POST['barangay']) && $_POST['barangay'] == 'Mapulang Lupa') echo 'selected'; ?> value="Mapulang Lupa">Mapulang Lupa</option>
                                        <option <?php if(isset($_POST['barangay']) && $_POST['barangay'] == 'Marulas') echo 'selected'; ?> value="Marulas">Marulas</option>
                                        <option <?php if(isset($_POST['barangay']) && $_POST['barangay'] == 'Maysan') echo 'selected'; ?> value="Maysan">Maysan</option>
                                        <option <?php if(isset($_POST['barangay']) && $_POST['barangay'] == 'Palasan') echo 'selected'; ?> value="Palasan">Palasan</option>
                                        <option <?php if(isset($_POST['barangay']) && $_POST['barangay'] == 'Parada') echo 'selected'; ?> value="Parada">Parada</option>
                                        <option <?php if(isset($_POST['barangay']) && $_POST['barangay'] == 'Pariancillo Villa') echo 'selected'; ?> value="Pariancillo Villa">Pariancillo Villa</option>
                                        <option <?php if(isset($_POST['barangay']) && $_POST['barangay'] == 'Paso de Blas') echo 'selected'; ?> value="Paso de Blas">Paso de Blas</option>
                                        <option <?php if(isset($_POST['barangay']) && $_POST['barangay'] == 'Pasolo') echo 'selected'; ?> value="Pasolo">Pasolo</option>
                                        <option <?php if(isset($_POST['barangay']) && $_POST['barangay'] == 'Poblacion') echo 'selected'; ?> value="Poblacion">Poblacion</option>
                                        <option <?php if(isset($_POST['barangay']) && $_POST['barangay'] == 'Polo') echo 'selected'; ?> value="Polo">Polo</option>
                                        <option <?php if(isset($_POST['barangay']) && $_POST['barangay'] == 'Punturin') echo 'selected'; ?> value="Punturin">Punturin</option>
                                        <option <?php if(isset($_POST['barangay']) && $_POST['barangay'] == 'Rincon') echo 'selected'; ?> value="Rincon">Rincon</option>
                                        <option <?php if(isset($_POST['barangay']) && $_POST['barangay'] == 'Tagalag') echo 'selected'; ?> value="Tagalag">Tagalag</option>
                                        <option <?php if(isset($_POST['barangay']) && $_POST['barangay'] == 'Ugong') echo 'selected'; ?> value="Ugong">Ugong</option>
                                        <option <?php if(isset($_POST['barangay']) && $_POST['barangay'] == 'Viente Reales') echo 'selected'; ?> value="Viente Reales">Viente Reales</option>
                                        <option <?php if(isset($_POST['barangay']) && $_POST['barangay'] == 'WawangPulo') echo 'selected'; ?> value="WawangPulo">Wawang Pulo</option>
                                    </select>
                                    </div>
                                </div>
                            </div>
                        </form>
        </div>
        <div class="container-lg px-3">
            <div class="row">
                <div class="col">
                    <div class="career-search">
                        <!-- for filtering result of tutee -->
                        <div class="filter-result">
                            <div class="table-container">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th class="text-center">Photo</th>
                                                <th class="text-center">Name</th>
                                                <th class="text-center">Barangay</th>
                                                <th></th>
                                            </tr>
                                        </thead>
<tbody>
    <?php foreach ($current_items as $tutee): ?>
        <tr class="clickable-row" 
            data-name="<?php echo htmlspecialchars($tutee['firstname'] . ' ' . $tutee['lastname']); ?>"
            data-photo="<?php echo !empty($tutee['photo']) ? htmlspecialchars($tutee['photo']) : '../assets/TuteeFindLogoName.jpg'; ?>"
            data-brgy="<?php echo htmlspecialchars($tutee['barangay']); ?>"
            data-bio="<?php echo !empty($tutee['bio']) ? htmlspecialchars(substr($tutee['bio'], 0, 50)) . (strlen($tutee['bio']) > 50 ? '...' : '') : 'No other information available.'; ?>"
            data-number="<?php echo htmlspecialchars($tutee['number']); ?>"
            data-emailaddress="<?php echo htmlspecialchars($tutee['emailaddress']); ?>"
            data-age="<?php echo htmlspecialchars($tutee['age']); ?>"
            data-guardianname="<?php echo htmlspecialchars($tutee['guardianname']); ?>"
            data-fblink="<?php echo htmlspecialchars($tutee['fblink']); ?>"
            data-grade="<?php echo htmlspecialchars($tutee['grade']); ?>"
            data-address="<?php echo htmlspecialchars($tutee['address']); ?>"
            data-school="<?php echo htmlspecialchars($tutee['school']); ?>"
            onclick="showProfileModal(event, this)">
            <td>
                <div class="img-holder text-center">
                    <img style="height: 45px; width: 45px; border-radius: 65px;" src="<?php echo !empty($tutee['photo']) ? $tutee['photo'] : '../assets/TuteeFindLogoName.jpg'; ?>" alt="Tutee Photo" class="img-fluid">
                </div>
            </td>
            <td>
                <div class="tutor-name fw-bold m-2 text-center">
                    <?php echo $tutee['firstname'] . ' ' . $tutee['lastname']; ?>
                </div>
            </td>
            <td class="tutor-name text-center">
                <?php echo $tutee['barangay']; ?>
            </td>
            <td>
                <form method="post" class="text-center">
                    <button type="button" 
                            class="btn btn-outline-success bx" 
                            data-bs-toggle="modal" 
                            data-bs-target="#messageModal"
                            data-tutee-name="<?php echo $tutee['firstname'] . ' ' . $tutee['lastname']; ?>"
                            data-tutee-id="<?php echo $tutee['id']; ?>"
                            >
                        <i class='bx bx-message-square-dots'></i>
                    </button>
                    
                    <input type="hidden" name="tutee_id" value="<?php echo $tutee['id']; ?>">
                    
                    <button type="submit" 
                            class="btn btn-outline-primary" 
                            name="send_request" 
                            onclick="event.stopPropagation();"> <!-- Prevent row click -->
                        <i class='bx bx-user-plus'></i>
                    </button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
</tbody>

                                    </table>
                                </div>

                                <nav aria-label="Page navigation">
                                    <ul class="pagination justify-content-center">
                                        <?php if ($current_page > 1): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=<?php echo $current_page - 1; ?>" aria-label="Previous">
                                                    <span aria-hidden="true">&laquo;</span>
                                                </a>
                                            </li>
                                        <?php endif; ?>

                                        <?php for ($page = 1; $page <= $total_pages; $page++): ?>
                                            <li class="page-item <?php echo $page == $current_page ? 'active' : ''; ?>">
                                                <a class="page-link" href="?page=<?php echo $page; ?>"><?php echo $page; ?></a>
                                            </li>
                                        <?php endfor; ?>

                                        <?php if ($current_page < $total_pages): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=<?php echo $current_page + 1; ?>" aria-label="Next">
                                                    <span aria-hidden="true">&raquo;</span>
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

    <!-- Message Modal -->
    <div class="modal fade" id="messageModal" tabindex="-1" aria-labelledby="emailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="emailModalLabel">Send Message</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-start d-block">
                    <form method="post" id="messageForm">
                        <div class="mb-3">
                            <label for="recipient" class="form-label">To: </label>
                            <span id="recipient"></span>
                            <input type="hidden" name="tutee_id" id="tutee_id">
                        </div>
                        <div class="mb-3">
                            <label for="message" class="form-label">Message</label>
                            <textarea class="form-control" id="message" name="message" rows="4" placeholder="Enter your message"></textarea>
                        </div>
                        <div>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary" name="send_message">
                                <i class='bx bx-send'></i> Send Message
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

<!-- Modal Structure -->
<div class="modal fade" id="notificationModal" tabindex="-1" aria-labelledby="notificationModalLabel" aria-hidden="true">
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
                Notification sent successfully.
            </div>
            <div class="modal-footer border-0">
                <!-- Footer left empty as per original design -->
            </div>
        </div>
    </div>
</div>

<!-- Profile Modal -->
<div class="modal fade" id="profileModal" tabindex="-1" aria-labelledby="profileModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="profileModalLabel">Profile Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body d-flex flex-column align-items-center">
                <img id="profileModalPhoto" 
                     class="img-fluid" 
                     style="border-radius: 50%; border: 3px solid #007bff; height: 120px; width: 120px;" 
                     alt="Profile Photo">
                <h5 id="profileModalName" class="mt-3 mb-1" style="font-weight: bold; color: #333;"></h5>
                <p class="mb-1" style="color: #555;">
                    <span class="font-weight-bold" id="profileModalBio"></span>
                </p>
                <p class="mb-1" style="color: #555;">
                    Barangay: <span class="font-weight-bold" id="profileModalBrgy"></span>
                </p>
                <!-- Additional Fields -->
                <div class="w-100">
                    <p><strong>Contact Number:</strong> <span id="numberValue"></span></p>
                    <p><strong>Email:</strong> <span id="profileModalEmail"></span></p>
                    <p><strong>Age:</strong> <span id="profileModalAge"></span></p>
                    <p><strong>Guardian's Name:</strong> <span id="profileModalGuardianName"></span></p>
                    <p><strong>Facebook:</strong> <a id="profileModalFbLink" target="_blank" style="text-decoration: none; color: #007bff;"></a></p>
                    <p><strong>Grade:</strong> <span id="profileModalGrade"></span></p>
                    <p><strong>Address:</strong> <span id="profileModalAddress"></span></p>
                    <p><strong>School:</strong> <span id="profileModalSchool"></span></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>






        
    <script src="script1.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>    
    <script>
        <?php if (isset($_SESSION['request_result'])): ?>
            $(document).ready(function() {
                var requestResult = "<?php echo $_SESSION['request_result']; ?>";
                $('#modalBody').text(requestResult);
                $('#notificationModal').modal('show');

                $('#notificationModal').on('hidden.bs.modal', function () {
                    window.location.href = 'suggestedtutee'; // Change this to your desired URL
                });
                <?php unset($_SESSION['request_result']); ?>
            });
        <?php endif; ?>


        function showProfileModal(event, row) {
            if (event.target.closest("button")) return;

            const name = row.dataset.name || "N/A";
            const photo = row.dataset.photo || "../assets/default-photo.jpg";
            const brgy = row.dataset.brgy || "N/A";
            const bio = row.dataset.bio || "N/A";
            const number = row.dataset.number || "N/A";
            const email = row.dataset.emailaddress || "N/A";
            const age = row.dataset.age || "N/A";
            const guardianName = row.dataset.guardianname || "N/A";
            const fbLink = row.dataset.fblink || "#";
            const grade = row.dataset.grade || "N/A";
            const address = row.dataset.address || "N/A";
            const school = row.dataset.school || "N/A";

            // Populate the modal
            document.getElementById("profileModalName").innerText = name;
            document.getElementById("profileModalPhoto").src = photo;
            document.getElementById("profileModalBrgy").innerText = brgy;
            document.getElementById("profileModalBio").innerText = bio;
            document.getElementById("numberValue").innerText = number;
            document.getElementById("profileModalEmail").innerText = email;
            document.getElementById("profileModalAge").innerText = age;
            document.getElementById("profileModalGuardianName").innerText = guardianName;
            document.getElementById("profileModalFbLink").href = fbLink;
            document.getElementById("profileModalFbLink").innerText = "Facebook Profile";
            document.getElementById("profileModalGrade").innerText = grade;
            document.getElementById("profileModalAddress").innerText = address;
            document.getElementById("profileModalSchool").innerText = school;

            // Show the modal
            const profileModal = new bootstrap.Modal(
                document.getElementById("profileModal")
            );
            profileModal.show();
            }
    </script>
</body>
</html>
