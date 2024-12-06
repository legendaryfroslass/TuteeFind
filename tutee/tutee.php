<?php
// error_reporting(1);
session_start();
require_once '../tutee.php';
$user_login = new TUTEE();
include('spinner.php');

// $currentPage = 'home'; 
// include 'navbar.php'; 

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

$userSession = $_SESSION['userSession'];
$stmt = $user_login->runQuery("SELECT * FROM tutee WHERE emailaddress = :emailaddress");
$stmt->bindParam(":emailaddress", $userSession);
$stmt->execute();
$userData = $stmt->fetch(PDO::FETCH_ASSOC);

$firstname = $userData['firstname'];
$lastname = $userData['lastname'];
$barangay = $userData['barangay'];
$tutee_id = $userData['id'];

if (!empty($userData['photo'])) {
    $imagePath = $userData['photo'];
} else {
    $imagePath = '../assets/TuteeFindLogoName.jpg';
}

$filterQuery = "SELECT
        requests.*,
        tutor.firstname AS tutor_firstname,
        tutor.lastname AS tutor_lastname,
        tutor.barangay,
        tutor.photo,
        tutor.bio,
        tutor.number,
        tutor.emailaddress,
        tutor.fblink,
        tutor.age
    FROM requests
    JOIN tutor ON requests.tutor_id = tutor.id
    WHERE tutee_id = :tutee_id AND requests.status = 'pending'";
$filterParams = [':tutee_id' => $userData['id']];

// Apply filters if set
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!empty($_POST['barangay'])) {
        $filterQuery .= " AND barangay = :filter_barangay";
        $filterParams[':filter_barangay'] = $_POST['barangay'];
    }
}

$requestStmt = $user_login->runQuery($filterQuery);
$requestStmt->execute($filterParams);
$requests = $requestStmt->fetchAll(PDO::FETCH_ASSOC);

// Function to send email notifications
function sendEmail($to, $subject, $body) {
    $mail = new PHPMailer(true);
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'findtutee@gmail.com';
        $mail->Password = 'tzbb qafz fhar ryzf';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;                                  
        
        // Recipients
        $mail->setFrom('findtutee@gmail.com', 'TUTEEFIND');
        $mail->addAddress($to);                                  
        
        // Content
        $mail->isHTML(true);                                  
        $mail->Subject = $subject;
        $mail->Body    = $body;
        
        $mail->send();
        echo "Message has been sent successfully.";
    } catch (Exception $e) {
        echo "<script>alert('Message could not be sent. Mailer Error: {$mail->ErrorInfo}');</script>";
    }
}

// Function to check if a tutor is already accepted
function hasAcceptedTutor($tutee_id) {
    global $user_login;
    $stmt = $user_login->runQuery("SELECT COUNT(*) FROM requests WHERE tutee_id = :tutee_id AND status = 'accepted'");
    $stmt->bindParam(":tutee_id", $tutee_id);
    $stmt->execute();
    return $stmt->fetchColumn() > 0;
}

// Function to accept tutor request
function acceptTutorRequest($request_id, $tutee_id) {
    global $user_login;

    // Check if the tutee has already accepted a tutor
    if (hasAcceptedTutor($tutee_id)) {
        echo "<script>alert('You have already accepted a tutor. You can\\'t accept more tutors.');</script>";
        return false;
    }

    try {
        // Fetch the tutor ID associated with the request
        $stmt = $user_login->runQuery("SELECT tutor_id FROM requests WHERE request_id = :request_id");
        $stmt->bindParam(":request_id", $request_id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$result) {
            echo "<script>alert('Invalid request. Tutor not found.');</script>";
            return false;
        }
        $tutor_id = $result['tutor_id'];

        // Check if the tutor already has 2 tutees
        $stmt = $user_login->runQuery("SELECT COUNT(*) AS tutee_count FROM requests WHERE tutor_id = :tutor_id AND status = 'accepted'");
        $stmt->bindParam(":tutor_id", $tutor_id);
        $stmt->execute();
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['tutee_count'];
        if ($count >= 2) {
            echo "<script>alert('This tutor already has 2 tutees. They cannot accept more.');</script>";
            return false;
        }

        // Update the request status to accepted
        $stmt = $user_login->runQuery("UPDATE requests SET status = 'accepted' WHERE request_id = :request_id");
        $stmt->bindParam(":request_id", $request_id);
        $stmt->execute();

        // Fetch tutor details
        $stmt = $user_login->runQuery("SELECT tutor.id, tutor.emailaddress, tutor.firstname AS tutor_firstname, tutor.lastname AS tutor_lastname FROM requests JOIN tutor ON requests.tutor_id = tutor.id WHERE request_id = :request_id");
        $stmt->bindParam(":request_id", $request_id);
        $stmt->execute();
        $tutor = $stmt->fetch(PDO::FETCH_ASSOC);
        $tutorEmail = $tutor['emailaddress'];
        $tutorName = $tutor['tutor_firstname'] . ' ' . $tutor['tutor_lastname'];

        // Fetch tutee details
        $stmt = $user_login->runQuery("SELECT firstname, lastname FROM tutee WHERE id = :tutee_id");
        $stmt->bindParam(":tutee_id", $tutee_id);
        $stmt->execute();
        $tutee = $stmt->fetch(PDO::FETCH_ASSOC);
        $tuteeName = $tutee['firstname'] . ' ' . $tutee['lastname'];

        // Send email notification
        $subject = "Tutor Request Accepted";
        $body = "Dear $tutorName,<br><br>Your tutor request has been accepted by $tuteeName.";
        sendEmail($tutorEmail, $subject, $body);

        // Insert a notification into the notifications table
        $title = "Request Accepted";
        $message = "Your tutor request has been accepted by $tuteeName.";
        $stmt = $user_login->runQuery("INSERT INTO notifications (sender_id, receiver_id, title, message, status, date_sent) 
                    VALUES (:tutee_id, :tutor_id, :title, :message, 'unread', NOW())");
        $stmt->bindParam(":tutee_id", $tutee_id);
        $stmt->bindParam(":tutor_id", $tutor_id);
        $stmt->bindParam(":title", $title);
        $stmt->bindParam(":message", $message);
        $stmt->execute();

        return true;
    } catch (PDOException $ex) {
        echo "<script>alert('Error accepting tutor request: ".$ex->getMessage()."');</script>";
        return false;
    }
}


// Function to reject tutor request
function rejectTutorRequest($request_id, $tutee_id) {
    global $user_login;
    try {
        // Fetch tutor details
        $stmt = $user_login->runQuery("SELECT tutor.id, tutor.emailaddress, tutor.firstname AS tutor_firstname, tutor.lastname AS tutor_lastname FROM requests JOIN tutor ON requests.tutor_id = tutor.id WHERE request_id = :request_id");
        $stmt->bindParam(":request_id", $request_id);
        $stmt->execute();
        $tutor = $stmt->fetch(PDO::FETCH_ASSOC);
        $tutorEmail = $tutor['emailaddress'];
        $tutorName = $tutor['tutor_firstname'] . ' ' . $tutor['tutor_lastname'];
        $tutor_id = $tutor['id'];

        // Fetch tutee details
        $stmt = $user_login->runQuery("SELECT firstname, lastname FROM tutee WHERE id = (SELECT tutee_id FROM requests WHERE request_id = :request_id)");
        $stmt->bindParam(":request_id", $request_id);
        $stmt->execute();
        $tutee = $stmt->fetch(PDO::FETCH_ASSOC);
        $tuteeName = $tutee['firstname'] . ' ' . $tutee['lastname'];

        // Send email notification
        $subject = "Tutor Request Rejected";
        $body = "Dear $tutorName,<br><br>Your tutor request has been rejected by $tuteeName.";
        sendEmail($tutorEmail, $subject, $body);

        // Insert a notification into the notifications table
        $title = "Request Rejected";
        $message = "Your tutor request has been rejected by $tuteeName.";
        $stmt = $user_login->runQuery("INSERT INTO notifications (sender_id, receiver_id, title, message, status, date_sent) 
                    VALUES (:tutee_id, :tutor_id, :title, :message, 'unread', NOW())");
        $stmt->bindParam(":tutee_id", $tutee_id);
        $stmt->bindParam(":tutor_id", $tutor_id);
        $stmt->bindParam(":title", $title);
        $stmt->bindParam(":message", $message);
        $stmt->execute();

        // Delete the request from the database
        $stmt = $user_login->runQuery("DELETE FROM requests WHERE request_id = :request_id");
        $stmt->bindParam(":request_id", $request_id);
        $stmt->execute();

        return true;
    } catch (PDOException $ex) {
        echo "<script>alert('Error rejecting tutor request: ".$ex->getMessage()."');</script>";
        return false;
    }
}
// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['accept_request'])) {
        $request_id = $_POST['request_id'];
        acceptTutorRequest($request_id, $userData['id']);
    } elseif (isset($_POST['reject_request'])) {
        $request_id = $_POST['request_id'];
        rejectTutorRequest($request_id, $userData['id']);
    }
    header("Location: " . $_SERVER['PHP_SELF'] . "?clearError");
}

// Fetch unread notifications count for the current tutee
$unreadNotifQuery = $user_login->runQuery("SELECT COUNT(*) AS unread_count FROM notifications WHERE receiver_id = :tutee_id AND status = 'unread'");
$unreadNotifQuery->bindParam(":tutee_id", $tutee_id);
$unreadNotifQuery->execute();
$unreadNotifData = $unreadNotifQuery->fetch(PDO::FETCH_ASSOC);
$unreadNotifCount = $unreadNotifData['unread_count'];

// Fetch count of unique tutors who have unread messages for a specific tutee
$unreadMessagesQuery = $user_login->runQuery("
    SELECT COUNT(DISTINCT tutee_id) AS unread_tutee_count 
    FROM messages 
    WHERE tutor_id = :tutor_id 
    AND sender_type = 'tutor' 
    AND is_read = 0
");
$unreadMessagesQuery->bindParam(":tutor_id", $tutor_id);  // Bind the tutee_id
$unreadMessagesQuery->execute();
$unreadMessagesData = $unreadMessagesQuery->fetch(PDO::FETCH_ASSOC);
$unreadMessageCount = $unreadMessagesData['unread_tutee_count'];


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tutee_id = $_POST['tutee_id'] ?? null;
    $tutor_id = $_POST['tutor_id'] ?? null;
    $message = $_POST['message'] ?? null;

    if ($tutee_id && $tutor_id && $message) {
        // Insert message into the database
        $sql = "INSERT INTO messages (tutor_id, tutee_id, sender_type, message, created_at, is_read)
                VALUES (?, ?, 'tutee', ?, NOW(), 0)";
        $stmt = $user_login->runQuery($sql);
        
        if ($stmt->execute([$tutor_id, $tutee_id, $message])) {
            echo "Message sent successfully!";
        } else {
            echo "Failed to send message.";
        }
    } else {
        echo "Missing required fields.";
    }
}
?>

<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <link rel="stylesheet" href="notif.css">
        <link rel="stylesheet" href="tutee.css">
        <link rel="stylesheet" href="spinner.css">
        <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
        <link rel="icon" href="../assets/TuteeFindLogo.png" type="image/png">
        <title>Tutee Home</title>
        <style>
            :root {
                --sub-text: #915f37;
            }
            html, body {
                margin: 0;
                padding: 0;
                width: 100%;
                height: 100%;
                overflow-x: hidden; /* Prevent horizontal scrolling */
            }
        </style>
    </head> 
    <div>
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
                        <li class="nav-link navbar-active" data-bs-toggle="tooltip" data-bs-placement="right" title="Home">
                            <a href="../tutee/tutee">
                                <i class='bx bx-home-alt icon'></i>
                                <span class="text nav-text">Home</span>
                            </a>
                        </li>
                        <li class="nav-link" data-bs-toggle="tooltip" data-bs-placement="right" title="Messages">
                            <a href="../tutee/message">
                                <div style="position: relative;">
                                    <i class='bx bxs-inbox icon'></i>
                                    <span id="message-count" class="badge bg-danger" style="position: absolute; top: -12px; right: -0px; font-size: 0.75rem;">
                                        <?php echo $unreadMessageCount; ?>
                                    </span> <!-- Notification counter -->
                                </div>
                                <span class="text nav-text">Messages</span>
                            </a>
                        </li>
                        <li class="nav-link" data-bs-toggle="tooltip" data-bs-placement="right" title="Notification">
                            <a href="../tutee/notif" class="d-flex align-items-center">
                                <div style="position: relative;">
                                    <i class='bx bx-bell icon'></i>
                                    <span id="notif-count" class="badge bg-danger" style="position: absolute; top: -12px; right: -0px; font-size: 0.75rem;">
                                        <?php echo $unreadNotifCount; ?>
                                    </span>
                                </div>
                                <span class="text nav-text">Notification</span>
                            </a>
                        </li>                         
                        <li class="nav-link" data-bs-toggle="tooltip" data-bs-placement="right" title="Progress">
                            <a href="../tutee/progress">
                                <i class='bx bx-bar-chart-alt icon'></i>
                                <span class="text nav-text">Progress</span>
                            </a>
                        </li>
                        <li class="nav-link" data-bs-toggle="tooltip" data-bs-placement="right" title="Tutor">
                            <a href="../tutee/tutor">
                                <i class='bx bx-user icon'></i>
                                <span class="text nav-text">Tutor</span>
                            </a>
                        </li>
                        <li class="nav-link" data-bs-toggle="tooltip" data-bs-placement="right" title="Settings">
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
            <div class="container-lg p-3">
                <div class="career-form headings d-flex justify-content-center mt-3">
                    <div class="row">
                        <div class="card1">Requested Tutors</div>
                    </div>
                </div>
            </div>
            <div class="container-lg">
                <form id="filter-form" class="career-form headings d-flex justify-content-start" method="post">
                    <div class="row">
                        <div class="col-md-4 my-1">
                            <div class="select-container">
                            <select class="custom-select" id="barangay" name="barangay" onchange="submitForm()">
                                <option <?php if(!empty($_POST['barangay'])) echo 'selected'; ?> value="">All Barangay</option>
                                <option <?php if(isset($_POST['barangay']) && $_POST['barangay'] == 'Arkong Bato') echo 'selected'; ?> value="Arkong Bato">Arkong Bato</option>
                                <option <?php if(isset($_POST['barangay']) && $_POST['barangay'] == 'Bagbaguin') echo 'selected'; ?> value="Bagbaguin">Bagbaguin</option>
                                <option <?php if(isset($_POST['barangay']) && $_POST['barangay'] == 'Balangkas') echo 'selected'; ?> value="Balangkas">Balangkas</option>
                                <option <?php if(isset($_POST['barangay']) && $_POST['barangay'] == 'Bignay') echo 'selected'; ?> value="Bignay">Bignay</option>
                                <option <?php if(isset($_POST['barangay']) && $_POST['barangay'] == 'Bisig') echo 'selected'; ?> value="Bisig">Bisig</option>
                                <option <?php if(isset($_POST['barangay']) && $_POST['barangay'] == 'Canumay East') echo 'selected'; ?> value="Canumay East">Canumay East</option>
                                <option <?php if(isset($_POST['barangay']) && $_POST['barangay'] == 'Canumay West') echo 'selected'; ?> value="Canumay West">Canumay West</option>
                                <option <?php if(isset($_POST['barangay']) && $_POST['barangay'] == 'Coloong') echo 'selected'; ?> value="Coloong">Coloong</option>
                                <option <?php if(isset($_POST['barangay']) && $_POST['barangay'] == 'Dalandanan') echo 'selected'; ?> value="Dalandanan">Dalandanan</option>
                                <option <?php if(isset($_POST['barangay']) && $_POST['barangay'] == 'Gen. T. de Leon') echo 'selected'; ?> value="Gen. T. de Leon">Gen. T. de Leon</option>
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
                                <option <?php if(isset($_POST['barangay']) && $_POST['barangay'] == 'Veinte Reales') echo 'selected'; ?> value="Veinte Reales">Veinte Reales</option>
                                <option <?php if(isset($_POST['barangay']) && $_POST['barangay'] == 'Wawang Pulo') echo 'selected'; ?> value="Wawang Pulo">Wawang Pulo</option>
                            </select>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="container-lg pt-3">
                <div class="row">
                    <div class="col">
                            <div class="filter-result">
                                <?php if (!empty($requests)): ?>
                                <div class="table-container">
                                    <div class="table-responsive">
                                        <table class="table table-striped tutee-thead">
                                                <thead>
                                                    <tr class="tutee-trow">
                                                        <th class="text-center">Photo</th>
                                                        <th class="text-center">Name</th>
                                                        <th class="text-center">Barangay</th>
                                                        <th class="text-center">Actions</th>
                                                    </tr>
                                                </thead>
                                            <?php endif; ?>
                                            <tbody>
                                                <?php if (!empty($requests)): ?>
                                                    <?php foreach ($requests as $request): ?>
                                                        <tr>
                                                            <td class="text-center justify-content-center">
                                                                <div class="img-holder">
                                                                    <img style="height: 65px; width: 65px; border-radius: 65px;" 
                                                                        src="<?php echo !empty($request['photo']) ? $request['photo'] : '../assets/TuteeFindLogoName.jpg'; ?>" 
                                                                        alt="Tutor Photo" 
                                                                        class="img-fluid">
                                                                </div>
                                                            </td>
                                                            <td class="text-center justify-content-center">
                                                                <div class="tutor-name fw-bold m-2">
                                                                    <?php echo $request['tutor_firstname'] . ' ' . $request['tutor_lastname']; ?>
                                                                </div>
                                                            </td>
                                                            <td class="text-center justify-content-center">
                                                                <?php echo $request['barangay']; ?>
                                                            </td>
                                                            <td class="text-center justify-content-center">
                                                                <form method="post" class="text-center">
                                                                    <button type="button" 
                                                                            class="btn btn-outline-primary bi bi-person-vcard" 
                                                                            data-bs-toggle="modal" 
                                                                            data-bs-target="#profileModal" 
                                                                            data-request-id="<?php echo $request['request_id']; ?>"
                                                                            data-name="<?php echo $request['tutor_firstname'] . ' ' . $request['tutor_lastname']; ?>"
                                                                            data-photo="<?php echo !empty($request['photo']) ? $request['photo'] : '../assets/TuteeFindLogoName.jpg'; ?>"
                                                                            data-brgy="<?php echo $request['barangay']; ?>"
                                                                            data-number="<?php echo $request['number']; ?>"
                                                                            data-age="<?php echo $request['age']; ?>"
                                                                            data-emailaddress="<?php echo $request['emailaddress']; ?>"
                                                                            data-fblink="<?php echo $request['fblink']; ?>"
                                                                            data-bio="<?php echo !empty($request['bio']) ? htmlspecialchars(substr($request['bio'], 0, 50)) . (strlen($request['bio']) > 50 ? '...' : '') : 'No additional information available.'; ?>"
                                                                            onclick="event.stopPropagation();"
                                                                            data-bs-toggle="tooltip" 
                                                                            title="View Tutor's Profile">
                                                                    </button>
                                                                    <button type="button" 
                                                                            class="btn btn-outline-primary bx" 
                                                                            data-bs-toggle="modal" 
                                                                            data-bs-target="#messageModal" 
                                                                            data-tutor-id="<?php echo $request['tutor_id']; ?>"
                                                                            data-tutor-name="<?php echo $request['tutor_firstname'] . ' ' . $request['tutor_lastname']; ?>"
                                                                            onclick="event.stopPropagation();" 
                                                                            data-bs-toggle="tooltip">
                                                                        <i class='bx bx-message-square-dots'
                                                                            title="Send a message to the tutor"></i>
                                                                    </button>
                                                                    <?php if ($request['status'] != 'accepted'): ?>
                                                                        <button type="button" class="btn btn-outline-success bx bx-check" 
                                                                        data-bs-toggle="modal" data-bs-target="#acceptRequestModal" 
                                                                        data-request-id="<?php echo $request['request_id']; ?>" onclick="setRequestId('accept', this)">
                                                                        </button>

                                                                        <button type="button" class="btn btn-outline-danger bx bx-x" 
                                                                        data-bs-toggle="modal" data-bs-target="#removeTuteeModal" 
                                                                        data-request-id="<?php echo $request['request_id']; ?>" onclick="setRequestId('reject', this)">
                                                                        </button>
                                                                    <?php else: ?>
                                                                        <button class="btn btn-success" disabled>Already Accepted</button>
                                                                    <?php endif; ?>
                                                                </form>
                                                            </td> 
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="4" class="text-center">
                                                            <div class="container d-flex flex-column justify-content-center align-items-center update rounded shadow-lg">
                                                                <img src="../assets/tutee-blankplaceholder-white.png" alt="Nothing to see here" style="width: 300px; height: 300px;">
                                                                <h5 class="opacity">No current tutor request</h5><br>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
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
            </div>
            <div class="modal-body d-flex flex-column align-items-center">
                <img id="profileModalPhoto" 
                    class="img-fluid" 
                    style="border-radius: 50%; border: 3px solid #007bff; height: 120px; width: 120px;" 
                    alt="Profile Photo">
                <h5 id="profileModalName" class="mt-3 mb-1" style="font-weight: bold; color: #333;"></h5>
                <p id="profileModalBrgy" class="mb-1" style="color: #555;">
                    Barangay: <span class="font-weight-bold" id="brgyValue"></span>
                </p>
                <p id="profileModalBio" class="mb-1" style="color: #555;">
                    <span class="font-weight-bold" id="bioValue"></span>
                </p>
                <p id="profileModalAge" class="mb-1" style="color: #555;">
                    Age: <span class="font-weight-bold" id="ageValue"></span>
                </p>
                <p id="profileModalNumber" class="mb-1" style="color: #555;">
                    Contact Number: <span class="font-weight-bold" id="numberValue"></span>
                </p>
                <p id="profileModalEmailaddress" class="mb-1" style="color: #555;">
                    Email Address: <span class="font-weight-bold" id="emailaddressValue"></span>
                </p>
                <p id="profileModalFblink" class="mb-1" style="color: #555;">
                    Facebook Profile: <span class="font-weight-bold" id="fblinkValue"></span>
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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
                    <form id="sendMessageForm" method="POST">
                        <input type="hidden" name="tutor_id" id="tutor_id">
                        <input type="hidden" name="tutee_id" value="<?php echo $tutee_id; ?>">
                        <div class="mb-3">
                            <label for="recipient" class="form-label">To: </label>
                            <span id="recipient"></span>
                        </div>
                        <div class="mb-3">
                            <label for="message" class="form-label">Message</label>
                            <textarea class="form-control" name="message" id="message" rows="4" placeholder="Enter your message"></textarea>
                        </div>
                    </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-primary" id="msg-sendBtn">
                            <i class='bx bx-send'></i> Send
                        </button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notification Modal for Tutee's Side -->
        <div class="modal fade" id="tuteeNotificationModal" tabindex="-1" role="dialog" aria-labelledby="tuteeModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header d-flex justify-content-center align-items-center border-0 mt-2">
                        <!-- Centered header content -->
                        <img src="../assets/check.png" alt="Success" class="success-icon" style="width: 65px; height: 65px;">
                    </div>
                    <div class="modal-body d-flex justify-content-center align-items-center" id="tuteeModalBody">
                        <!-- Message will be injected here -->
                    </div>
                    <div class="modal-footer border-0">
                    </div>
                </div>
            </div>
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
        
        <!-- Message Sent Success Toast -->
        <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
            <div id="toastMsgSent" class="toast" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="true">
                <div class="toast-header">
                    <!-- <img src="..." class="rounded me-2" alt="..."> -->
                    <strong class="me-auto">TuteeFind</strong>
                    <small>Just now</small>
                    <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
                <div class="toast-body">
                    Message sent successfully!
                </div>
            </div>
        </div>

        <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
            <div id="toasttwoTutee" class="toast" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="true">
                <div class="toast-header">
                    <!-- <img src="..." class="rounded me-2" alt="..."> -->
                    <strong class="me-auto">TuteeFind</strong>
                    <small>Just now</small>
                    <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
                <div class="toast-body">
                    Tutor has already 2 Tutees, You can't accept anymore
                </div>
            </div>
        </div>

        <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
            <div id="toasthaveTutee" class="toast" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="true">
                <div class="toast-header">
                    <!-- <img src="..." class="rounded me-2" alt="..."> -->
                    <strong class="me-auto">TuteeFind</strong>
                    <small>Just now</small>
                    <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
                <div class="toast-body">
                    You have already accepted a Tutor.
                </div>
            </div>
        </div>

        <!-- Accept Request Modal -->
        <div class="modal fade" id="acceptRequestModal" tabindex="-1" aria-labelledby="acceptRequestModalLabel" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header d-flex justify-content-center align-items-center border-0 mt-2">
                        <img src="../assets/check.png" alt="Remove" class="delete-icon" style="width: 65px; height: 65px;">
                    </div>
                    <div class="modal-body d-flex justify-content-center align-items-center">
                        Are you sure you want to accept this request?
                    </div>
                    <div class="modal-footer">
                        <form method="post" id="acceptForm">
                        <input type="hidden" name="request_id" id="acceptRequestId">
                        <button type="submit" name="accept_request" class="btn btn-outline-success" id="reqAccpt" data-bs-toggle="modal" data-bs-target="#uploadSuccessModal" data>Accept</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Remove Tutor Modal -->
        <div class="modal fade" id="removeTuteeModal" tabindex="-1" role="dialog" aria-labelledby="removeTuteeModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header d-flex justify-content-center align-items-center border-0 mt-2">
                        <!-- Centered header content -->
                        <img src="../assets/remove.png" alt="Remove" class="delete-icon" style="width: 65px; height: 65px;">
                    </div>
                    <div class="modal-body" id="modalBody">
                        <p>Are you sure you want to remove this tutor?</p>
                    </div>
                    <div class="modal-footer">
                    <form id="removeTuteeForm" method="POST">
                        <input type="hidden" name="request_id" id="rejectRequestId"> <!-- Added hidden input for tutor_id -->
                        <button type="submit" id="confirmTutorRemove" name="reject_request" class="btn btn-outline-danger">Remove</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Success Modal -->
        <div class="modal fade" id="uploadSuccessModal" tabindex="-1" aria-labelledby="uploadSuccessModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                <div class="modal-header d-flex justify-content-center align-items-center border-0 mt-2">
                    <!-- Boxicons Checkmark with Circular Swipe Animation -->
                    <div class="checkmark-container">
                    <svg class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                        <circle class="checkmark__circle" cx="26" cy="26" r="25" fill="none"/>
                        <path class="checkmark__check" fill="none" d="M14 27l7.5 7.5L38 18"/>
                    </svg>
                    </div>
                </div>
                <div class="modal-body d-flex justify-content-center align-items-center" id="modalBody">
                    Tutor request accepted successfully.
                </div>
                </div>
            </div>

        <!-- Reject Success Modal -->
        <div class="modal fade" id="rejectSuccModal" tabindex="-1" aria-labelledby="rejectSuccModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                <div class="modal-header d-flex justify-content-center align-items-center border-0 mt-2">
                    <!-- Boxicons Checkmark with Circular Swipe Animation -->
                    <div class="crossmark-container">
                        <svg class="crossmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                            <circle class="crossmark__circle" cx="26" cy="26" r="25" fill="none" />
                            <path class="crossmark__cross" fill="none" stroke="#FF0000" stroke-width="5" d="M16 16 L36 36 M36 16 L16 36"/>
                        </svg>
                    </div>
                </div>
                <div class="modal-body d-flex justify-content-center align-items-center" id="rejectModalBody">
                    Tutor request rejected successfully.
                </div>
                <div class="modal-footer border-0">
                    <!-- Footer left empty as per original design -->
                </div>
                </div>
            </div>
        </div>

        <!-- Error Upload Modal -->
        <div class="modal fade" id="uploadErrorModal1" tabindex="-1" aria-labelledby="uploadErrorModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                <div class="modal-header d-flex justify-content-center align-items-center border-0 mt-2">
                    <!-- SVG Circular Swipe Error Icon -->
                    <div class="error-icon-container">
                    <svg class="error-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                        <circle class="error-icon__circle" cx="26" cy="26" r="25" fill="none"/>
                        <path class="error-icon__cross" fill="none" d="M16 16 L36 36 M36 16 L16 36"/>
                    </svg>
                    </div>
                </div>
                <div class="modal-body" id="errorMessage1">
                    <!-- Error message will be dynamically added here -->
                    An error occurred while uploading the file.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
                </div>
            </div>
        </div>

        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
        <script src="spinner.js"></script>
        <script src="tutee_sidebar.js"></script>
        <script src="notif.js"></script>
        <script src="tutee.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const messageModal = document.getElementById('messageModal');
                messageModal.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;
                    const tutorId = button.getAttribute('data-tutor-id');
                    const tutorName = button.getAttribute('data-tutor-name');

                    // Set tutor_id in the hidden input field
                    const tutorIdInput = document.querySelector('#sendMessageForm input[name="tutor_id"]');
                    tutorIdInput.value = tutorId;

                    // Update the recipient name display in the modal
                    const recipientDisplay = document.getElementById('recipient');
                    recipientDisplay.textContent = tutorName;
                });

                document.getElementById('msg-sendBtn').addEventListener('click', function() {
                    const form = document.getElementById('sendMessageForm');
                    const formData = new FormData(form);

                    fetch('', { // Add your PHP file here
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.text())
                    .then(result => {
                        form.reset(); // Clear the form
                        $('#messageModal').modal('hide'); // Close the modal
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Message could not be sent.');
                    });
                });
            });


            // Function to submit the form when select options change
            function submitForm() {
                console.log("Form submitted");
                document.getElementById('filter-form').submit();
            }

            $(document).ready(function() {
                <?php if (isset($_SESSION['request_result'])): ?>
                    var requestResult = "<?php echo $_SESSION['request_result']; ?>";
                    $('#tuteeModalBody').text(requestResult);
                    $('#tuteeNotificationModal').modal('show');
                <?php unset($_SESSION['request_result']); ?>
                <?php endif; ?>
            });

            // This script will dynamically populate the profile modal
            document.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget; // Button that triggered the modal
                var name = button.getAttribute('data-name');
                var photo = button.getAttribute('data-photo');
                var brgy = button.getAttribute('data-brgy');
                var bio = button.getAttribute('data-bio');
                var age = button.getAttribute('data-age');
                var number = button.getAttribute('data-number');
                var emailaddress = button.getAttribute('data-emailaddress');
                var fblink = button.getAttribute('data-fblink');
                // Update the modal's content
                var modal = document.getElementById('profileModal');
                modal.querySelector('#profileModalName').textContent = name;
                modal.querySelector('#profileModalPhoto').src = photo;
                modal.querySelector('#brgyValue').textContent = brgy;
                modal.querySelector('#ageValue').textContent = age;
                modal.querySelector('#numberValue').textContent = number;
                modal.querySelector('#emailaddressValue').textContent = emailaddress;
                modal.querySelector('#bioValue').textContent = bio;
                modal.querySelector('#fblinkValue').textContent = fblink;
            });

            function setRequestId(action, button) {
                var requestId = button.getAttribute('data-request-id');
                if (action === 'accept') {
                document.getElementById('acceptRequestId').value = requestId;
                } else if (action === 'reject') {
                document.getElementById('rejectRequestId').value = requestId;
                }
            }
        </script>
    </body>
</html>