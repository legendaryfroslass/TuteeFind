<?php
error_reporting(1);
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
$tutor_id = $userData['id']; // This is the tutor's ID used for notifications

$imagePath = !empty($userData['photo']) ? $userData['photo'] : '../assets/TuteeFindLogoName.jpg';

// Fetch unread notifications count for the current tutor
$unreadNotifQuery = $user_login->runQuery("SELECT COUNT(*) AS unread_count FROM notifications WHERE receiver_id = :tutor_id AND status = 'unread'");
$unreadNotifQuery->bindParam(":tutor_id", $tutor_id);
$unreadNotifQuery->execute();
$unreadNotifData = $unreadNotifQuery->fetch(PDO::FETCH_ASSOC);
$unreadNotifCount = $unreadNotifData['unread_count'];

// Fetch notifications for the current tutor
$notifQuery = $user_login->runQuery("SELECT * FROM notifications WHERE receiver_id = :tutor_id ORDER BY date_sent DESC");
$notifQuery->bindParam(":tutor_id", $tutor_id);
$notifQuery->execute();

// Mark unread notifications as read when the notifications page is visited
$markReadQuery = $user_login->runQuery("UPDATE notifications SET status = 'read' WHERE receiver_id = :tutor_id AND status = 'unread'");
$markReadQuery->bindParam(":tutor_id", $tutor_id);
$markReadQuery->execute();

// Set unreadNotifCount to 0 for immediate reset in PHP
$unreadNotifCount = 0;

// Fetch notifications for the current tutor
$notifQuery = $user_login->runQuery("SELECT * FROM notifications WHERE receiver_id = :tutor_id ORDER BY date_sent DESC");
$notifQuery->bindParam(":tutor_id", $tutor_id);
$notifQuery->execute();

// Fetch the results as an associative array or set as an empty array if no notifications found
$notifications = $notifQuery->fetchAll(PDO::FETCH_ASSOC) ?? [];

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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">    <title>Notifications</title>
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
                    <li class="nav-link" data-bs-placement="right" title="Home">
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
                        <a href="../tutor/notification" class="d-flex align-items-center custom-bg">
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
                    <li class="nav-link" data-bs-placement="right" title="Settings">
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
        <div class="container-lg p-3">
            <div class="career-form headings d-flex justify-content-center mt-3">
                <div class="row">
                    <div class="card1" style="color:white;">Notifications</div>
                </div>
            </div>
        </div>

        <div class="container-lg p-3">
<ul class="notification-list">
    <?php if (count($notifications) > 0): ?>
        <?php foreach ($notifications as $notif): ?>
            <?php
            // Determine the redirect URL based on the notification title
            $redirect_url = '';
            if ($notif['title'] === 'Request Accepted') {
                $redirect_url = '../tutor/currenttutor'; // Adjust this path as needed
            } elseif($notif['title'] === 'Request Rejected') {
                $redirect_url = '../tutor/suggestedtutee';
            } else {
                // Set a default redirect URL or leave it empty if not needed
                $redirect_url = '../tutor/notification';
            }
            ?>
<li class="notification-item">
    <a href="<?php echo htmlspecialchars($redirect_url); ?>" class="text-decoration-none">
        <div class="notification-container mb-2">
            <div class="row align-items-center" style="max-height: 55px;">
                <div class="col text-left notification-text">
                    <strong><?php echo htmlspecialchars($notif['title']); ?></strong>
                    <p class="mb-0"><?php echo htmlspecialchars($notif['message']); ?></p>
                </div>
                <div class="col-auto text-end d-flex align-items-center justify-content-end">
                    <div class="notification-time"><?php echo date('M d Y, h:i A', strtotime($notif['date_sent'])); ?></div>
                </div>
            </div>
        </div>
    </a>
</li>


        <?php endforeach; ?>
    <?php else: ?>
        <li class="notification-item">
            <p>No notifications available.</p>
        </li>
    <?php endif; ?>
</ul>

                </div>
    </div>
</body>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="script1.js"></script>
</html>