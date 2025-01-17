<?php
error_reporting(E_ALL); // Set error reporting to show all errors
session_start();
require_once '../tutee.php';

require '../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require '../vendor/phpmailer/phpmailer/src/Exception.php';
require '../vendor/phpmailer/phpmailer/src/SMTP.php';
require '../vendor/phpmailer/phpmailer/src/PHPMailer.php';

$user_login = new TUTEE();
if (!$user_login->is_logged_in()) {
    $user_login->redirect('login');
}

$userSession = $_SESSION['userSession'];
$stmt = $user_login->runQuery("SELECT * FROM tutee WHERE emailaddress = :emailaddress");
$stmt->bindParam(":emailaddress", $userSession);
$stmt->execute();
$userData = $stmt->fetch(PDO::FETCH_ASSOC);

// Ensure $userData is not false before accessing its fields
if ($userData) {
    $firstname = $userData['firstname'];
    $lastname = $userData['lastname'];
    $age = $userData['age'];
    $sex = $userData['sex'];
    $guardianname = $userData['guardianname'];
    $fblink = $userData['fblink'];
    $barangay = $userData['barangay'];
    $number = $userData['number'];
    $tutee_id = $userData['id']; 
    
    
    // Fetch unread notifications count for the current tutee
$unreadNotifQuery = $user_login->runQuery("SELECT COUNT(*) AS unread_count FROM notifications WHERE receiver_id = :tutee_id AND sent_for = 'tutee' AND status = 'unread'");
$unreadNotifQuery->bindParam(":tutee_id", $tutee_id);
$unreadNotifQuery->execute();
$unreadNotifData = $unreadNotifQuery->fetch(PDO::FETCH_ASSOC);
$unreadNotifCount = $unreadNotifData['unread_count'];

// Fetch count of unique tutors who have unread messages for a specific tutee
$unreadMessagesQuery = $user_login->runQuery("
    SELECT COUNT(DISTINCT tutor_id) AS unread_tutor_count 
    FROM messages 
    WHERE tutee_id = :tutee_id 
    AND sender_type = 'tutee' 
    AND is_read = 0
");
$unreadMessagesQuery->bindParam(":tutee_id", $tutee_id);  // Bind the tutee_id
$unreadMessagesQuery->execute();
$unreadMessagesData = $unreadMessagesQuery->fetch(PDO::FETCH_ASSOC);
$unreadMessageCount = $unreadMessagesData['unread_tutor_count'];

// Mark all unread messages as read from the tutee's point of view
$markAsReadQuery = $user_login->runQuery("
UPDATE messages
SET is_read = 1
WHERE tutor_id = :tutor_id 
AND tutee_id = :tutee_id 
AND sender_type = 'tutee'
AND is_read = 0
");
$markAsReadQuery->bindParam(":tutor_id", $tutor_id);  // Tutor's ID
$markAsReadQuery->bindParam(":tutee_id", $tutee_id);  // Tutee's ID
$markAsReadQuery->execute();

    // Check if $userData['photo'] is empty
    $imagePath = !empty($userData['photo']) ? $userData['photo'] : '../assets/TuteeFindLogoName.jpg';

    $tutorStmt = $user_login->runQuery("
        SELECT 
            tutor.id, tutor.firstname, tutor.lastname, tutor.age, tutor.sex, tutor.number, tutor.barangay, tutor.student_id, 
            tutor.course, tutor.photo, tutor.professor, 
            tutor.fblink, tutor.emailaddress
        FROM 
            tutor
        INNER JOIN 
            requests ON tutor.id = requests.tutor_id
        WHERE 
            requests.status = 'accepted' AND requests.tutee_id = :tutee_id
    ");
    $tutorStmt->bindParam(":tutee_id", $tutee_id);
    $tutorStmt->execute();
    $tutors = $tutorStmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    // Handle the case where no user data is found
    $firstname = $lastname = $age = $sex = $guardianname = $fblink = $barangay = $number = '';
    $imagePath = '../assets/profile-user.png';
    $tutors = [];
}

function removeTutor($firstname, $lastname, $tutor_id, $tutee_id, $removal_reason) {
    global $user_login;
    try {
        // Fetch the tutee's email
        $stmt = $user_login->runQuery("SELECT emailaddress FROM tutor WHERE id = :tutor_id");
        $stmt->bindParam(":tutor_id", $tutor_id);
        $stmt->execute();
        $tutorEmail = $stmt->fetchColumn();
        
        // Prepare and send the email using PHPMailer
        $mail = new PHPMailer(true);
            //Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'findtutee@gmail.com';
            $mail->Password = 'tzbb qafz fhar ryzf';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
            
            $mail->setFrom('findtutee@gmail.com', 'TUTEEFIND');
            $mail->addAddress($tutorEmail); // Set the recipient as the email entered in the form

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'You are been Removed as Tutor.';
            $mail->Body    = "
                <h3>Your Tutee $firstname $lastname, has removed you from being his/her Tutor.</h3>
                <p>Reason: $removal_reason.</p>
            ";

            $mail->send();
        // Update the request status to removed
        $stmt = $user_login->runQuery("UPDATE requests SET status = 'removed' WHERE tutor_id = :tutor_id AND tutee_id = :tutee_id");
        $stmt->bindParam(":tutor_id", $tutor_id);
        $stmt->bindParam(":tutee_id", $tutee_id);
        $stmt->execute();

        // Insert a notification for the tutor about the removal
        $notificationStmt = $user_login->runQuery("INSERT INTO notifications (sender_id, receiver_id, title, message, status, sent_for) 
                                                  VALUES (:sender_id, :receiver_id, 'Your Tutee $firstname $lastname, has removed you from being his/her Tutor.', 'Reason: ' :message, 'unread', 'tutor')");
        $notificationStmt->bindParam(":sender_id", $tutee_id); // Ensure tutor_id is passed
        $notificationStmt->bindParam(":receiver_id", $tutor_id);
        $notificationStmt->bindParam(":message", $removal_reason);
        $notificationStmt->execute();

        return true;
    } catch (PDOException $ex) {
        echo "Error removing tutor: " . $ex->getMessage();
        return false;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['remove_tutor'])) {
        $tutor_id = $_POST['tutor_id'];
        $tutee_id = $_POST['tutee_id'];
        $removal_reason = $_POST['removal_reason']; // Get the reason if provided

        if (removeTutor($firstname, $lastname, $tutor_id, $tutee_id, $removal_reason)) {
            // Refresh the page to reflect the changes
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }
    }
}
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
        <link rel="stylesheet" href="notif.css">
        <link rel="stylesheet" href="tutee.css">
        <link rel="stylesheet" href="tutor.css">
        <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
        <link rel="icon" href="../assets/TuteeFindLogo.png" type="image/png">
        <title>home</title>
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
                            <li class="nav-link" data-bs-toggle="tooltip" data-bs-placement="right" title="Home">
                                <a href="../tutee/tutee">
                                    <i class='bx bx-home-alt icon'></i>
                                    <span class="text nav-text">Home</span>
                                </a>
                            </li>
                            <li class="nav-link" data-bs-toggle="tooltip" data-bs-placement="right" title="Messages">
                                <a href="../tutee/message">
                                    <div style="position: relative;">
                                        <i class='bx bx-envelope icon'></i>
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
                                        </span> <!-- Notification counter -->
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
                            <li class="nav-link navbar-active" data-bs-toggle="tooltip" data-bs-placement="right" title="Tutors">
                                <a href="../tutee/tutor">
                                    <i class='bx bx-user icon'></i>
                                    <span class="text nav-text">Tutors</span>
                                </a> 
                            </li>
                            <li class="nav-link">
                                <a href="../tutee/settings" data-bs-toggle="tooltip" data-bs-placement="right" title="Settings">
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
        <div class="container-lg px-3" style="padding-top: 16px; padding-bottom: 10px;">
            <div class="career-form headings d-flex justify-content-center mt-3">
                <div class="row">
                    <div class="card1">Tutor</div>
                </div>
            </div>
        </div>
        <div class="container-lg">
    <div class="row">
        <div class="filter-result">
            <?php if (!empty($tutors)): ?>
                <?php foreach ($tutors as $tutor): ?>
                    <div class="mb-2">
                        <div id="accordion">
                            <div class="card shadow-lg rounded-3">
                                <div class="card-header" id="heading<?php echo htmlspecialchars($tutor['id']); ?>">
                                    <h5 class="mb-0">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <button class="btn btn-link" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo htmlspecialchars($tutor['id']); ?>" aria-expanded="true" aria-controls="collapse<?php echo htmlspecialchars($tutor['id']); ?>">
                                                <img style="height: 65px; width: 65px; border-radius: 65px;" src="<?php echo !empty($tutor['photo']) ? $tutor['photo'] : '../assets/TuteeFindLogoName.jpg'; ?>" alt="Tutor Photo" class="img-fluid">
                                            </button>
                                            <div class="col" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo htmlspecialchars($tutor['id']); ?>" aria-expanded="true" aria-controls="collapse<?php echo htmlspecialchars($tutor['id']); ?>">
                                                <?php echo htmlspecialchars($tutor['firstname'] . ' ' . $tutor['lastname']); ?>
                                            </div>
                                            <div class="job-right my-4 flex-shrink-0">
                                                <form method="post" class="text-center">
                                                    <button type="button" 
                                                            id="SendMessage" 
                                                            class="btn btn-outline-primary bx" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#messageModal" 
                                                            data-tutor-id="<?php echo $tutor['id']; ?>"
                                                            data-tutor-name="<?php echo $tutor['firstname'] . ' ' . $tutor['lastname']; ?>">
                                                        <i class='bx bx-message-square-dots'></i>
                                                    </button>
                                                    <input type="hidden" name="tutor_id" value="<?php echo htmlspecialchars($tutor['id']); ?>">
                                                    <button type="button" name="remove_tutor" id="removeTutorBtn" class="btn btn-outline-danger bx bx-user-x" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#removeTuteeModal"
                                                            data-tutor-id="<?php echo htmlspecialchars($tutor['id']); ?>"
                                                            data-tutee-id="<?php echo htmlspecialchars($tutee_id); ?>"></button> <!-- Ensure data attributes are set --> 
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </h5>
                                </div>
                                <div id="collapse<?php echo htmlspecialchars($tutor['id']); ?>" class="collapse show" aria-labelledby="heading<?php echo htmlspecialchars($tutor['id']); ?>" data-parent="#accordion">
                                    <div class="card-body">
                                        <h5>Other Information</h5>
                                        <div class="row">
                                            <!-- Course Section -->
                                            <div class="col-12 col-md-4">
                                                <div class="form-group mb-4">
                                                    <label class="nav-text info-header">Course</label>
                                                    <div class="border p-2 rounded bg-light">
                                                        <?php echo htmlspecialchars($tutor['course']); ?>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Barangay Section -->
                                            <div class="col-12 col-md-4">
                                                <div class="form-group mb-4">
                                                    <label class="nav-text info-header">Barangay</label>
                                                    <div class="border p-2 rounded bg-light">
                                                        <?php echo htmlspecialchars($tutor['barangay']); ?>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Contact No Section -->
                                            <div class="col-12 col-md-4">
                                                <div class="form-group mb-4">
                                                    <label class="nav-text info-header">Contact No</label>
                                                    <div class="border p-2 rounded bg-light">
                                                        <?php echo htmlspecialchars($tutor['number']); ?>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Facebook Link Section -->
                                            <div class="col-12 col-md-6">
                                                <div class="form-group mb-4">
                                                    <label class="nav-text info-header">Facebook Link</label>
                                                    <div class="border p-2 rounded bg-light">
                                                        <a href="<?php echo htmlspecialchars($tutor['fblink']); ?>" target="_blank">
                                                            <?php echo htmlspecialchars($tutor['fblink']); ?>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Email Address Section -->
                                            <div class="col-12 col-md-6">
                                                <div class="form-group mb-4">
                                                    <label class="nav-text info-header">Email Address</label>
                                                    <div class="border p-2 rounded bg-light">
                                                        <?php echo htmlspecialchars($tutor['emailaddress']); ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="container d-flex flex-column justify-content-center align-items-center update rounded shadow-lg">
                    <img src="../assets/tutee-blankplaceholder-white.png" alt="Nothing to see here" style="width: 300px; height: 300px;">
                    <h5 class="opacity">No current tutor</h5><br>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

    </div>

        <!-- Remove Tutor Modal -->
<div class="modal fade modal-shake" id="removeTuteeModal" tabindex="-1" role="dialog" aria-labelledby="removeTuteeModalLabel" aria-hidden="true">
    <div>
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header d-flex justify-content-center align-items-center border-0 mt-2">
                    <img src="../assets/remove.png" alt="Remove" class="delete-icon" style="width: 65px; height: 65px;">
                </div>
                <!-- Form is now enclosed correctly inside modal-content -->
                <form id="removeTuteeForm" method="POST">
                    <div class="modal-body text-center">
                        <p>Are you sure you want to remove this tutor?</p>
                        <textarea name="removal_reason" id="removal_reason" class="form-control" placeholder="Enter reason for removal" required></textarea>
                        <div id="reasonError" style="color: red; display: none;">Reason is required.</div> <!-- Error message if not filled -->
                    </div>
                    <div class="modal-footer d-flex justify-content-center border-0">
                        <input type="hidden" name="tutor_id" id="modalTutorId" value="">
                        <input type="hidden" name="tutee_id" id="tutee_id" value="">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" id="confirmTutorRemove" name="remove_tutor" class="btn btn-danger">Remove</button>
                    </div>
                </form>
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
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" id="msg-sendBtn" data-bs-dismiss="modal">
                            <i class='bx bx-send'></i> Send
                        </button>
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
        <!-- Javascript -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
        <script src="tutee_sidebar.js"></script>
        <script src="notif.js"></script>
        <script src="tutor.js"></script>
        <script src="tutee.js"></script>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Handling message modal logic
            const messageModal = document.getElementById('messageModal');
            messageModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const tutorId = button.getAttribute('data-tutor-id');
                const tutorName = button.getAttribute('data-tutor-name');

                // Set tutor_id in the hidden input field
                document.querySelector('#sendMessageForm input[name="tutor_id"]').value = tutorId;

                // Update the recipient name display in the modal
                document.getElementById('recipient').textContent = tutorName;
            });

    // Send message functionality
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
        });
    });
});
</script>
    </body>
</html>