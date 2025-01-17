<?php
error_reporting(E_ALL); // Set error reporting to show all errors
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
$barangay = $userData['barangay'];
$tutee_id = $userData['id'];

$imagePath = !empty($userData['photo']) ? $userData['photo'] : '../assets/TuteeFindLogoName.jpg';

// Fetch notifications for the tutee based on the new table structure
$stmt = $user_login->runQuery("SELECT * FROM notifications WHERE receiver_id = :tutee_id AND sent_for = 'tutee' ORDER BY date_sent DESC");
$stmt->bindParam(":tutee_id", $tutee_id);
$stmt->execute();
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch unread notifications count for the current tutee
$unreadNotifQuery = $user_login->runQuery("SELECT COUNT(*) AS unread_count FROM notifications WHERE receiver_id = :tutee_id AND sent_for = 'tutee' AND status = 'unread'");
$unreadNotifQuery->bindParam(":tutee_id", $tutee_id);
$unreadNotifQuery->execute();
$unreadNotifData = $unreadNotifQuery->fetch(PDO::FETCH_ASSOC);
$unreadNotifCount = $unreadNotifData['unread_count'];

// Mark unread notifications as read when the notifications page is visited
$markReadQuery = $user_login->runQuery("UPDATE notifications SET status = 'read' WHERE receiver_id = :tutee_id AND sent_for = 'tutee' AND status = 'unread'");
$markReadQuery->bindParam(":tutee_id", $tutee_id);
$markReadQuery->execute();

// Mark all unread messages as read from the tutee's point of view
$markAsReadQuery = $user_login->runQuery("
UPDATE messages
SET is_read = 1
WHERE tutee_id = :tutee_id 
AND tutor_id = :tutor_id 
AND sender_type = 'tutor'
AND is_read = 0
");
$markAsReadQuery->bindParam(":tutor_id", $tutor_id);  // Tutor's ID
$markAsReadQuery->bindParam(":tutee_id", $tutee_id);  // Tutee's ID
$markAsReadQuery->execute();

// Set unreadNotifCount to 0 for immediate reset in PHP
$unreadNotifCount = 0;

// Fetch notifications for the tutee again
$notifQuery = $user_login->runQuery("SELECT * FROM notifications WHERE receiver_id = :tutee_id AND sent_for = 'tutee' ORDER BY date_sent DESC");
$notifQuery->bindParam(":tutee_id", $tutee_id);
$notifQuery->execute();

// Fetch the results as an associative array or set as an empty array if no notifications found
$notifications = $notifQuery->fetchAll(PDO::FETCH_ASSOC) ?? [];

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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="tutee.css">
    <link rel="stylesheet" href="notif.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <link rel="icon" href="../assets/TuteeFindLogo.png" type="image/png">
    <title>Notifications</title>
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
                        <li class="nav-link navbar-active" data-bs-toggle="tooltip" data-bs-placement="right" title="Notification">
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
                        <div class="card1">Notification</div>
                    </div>
                </div>
            </div>

            <div class="container-lg p-3">
                <ul class="notification-list ps-0 shadow-sm rounded">
                    <?php if (count($notifications) > 0): ?>
                        <?php foreach ($notifications as $notif): ?>
                            <?php
                            if ($notif['title'] === 'New Tutor Request') {
                                $redirect_url = '../tutee/tutee'; // For tutor assignment notifications
                            } else {
                                $redirect_url = '../tutee/notif'; // For updates on tutee requests
                            }
                            ?>
                            <li class="notification-item">
                                <a href="<?php echo htmlspecialchars($redirect_url); ?>" class="text-decoration-none">
                                    <div class="notification-container mb-2">
                                        <div class="row align-items-center" style="max-height: 55px;">
                                            <div class="col text-left">
                                                <strong><?php echo htmlspecialchars($notif['title']); ?></strong>
                                                <p class="mb-0"><?php echo htmlspecialchars($notif['message']); ?></p>
                                            </div>
                                            <div class="col text-end d-flex align-items-center justify-content-end">
                                                <div class="notification-time"><?php echo date('M d Y, h:i A', strtotime($notif['date_sent'])); ?></div>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li class="notification-item shadow-sm rounded-3">
                            <p>No notifications available.</p>
                        </li>
                    <?php endif; ?>
                </ul>
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

    </body>

    <script src="notif.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>    <script src="tutee_sidebar.js"></script>
</html>