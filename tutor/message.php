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
$tutor_id = $userData['id'];


$imagePath = !empty($userData['photo']) ? $userData['photo'] : '../assets/TuteeFindLogoName.jpg';

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

$messagesQuery = $user_login->runQuery("
    SELECT m.tutee_id, t.firstname AS tutee_firstname, t.lastname AS tutee_lastname,
           t.photo AS tutee_photo, m.message, m.created_at, m.sender_type
    FROM messages m
    JOIN tutee t ON m.tutee_id = t.id
    WHERE m.tutor_id = :tutor_id
    AND m.created_at = (
        SELECT MAX(m2.created_at)
        FROM messages m2
        WHERE m2.tutor_id = m.tutor_id
        AND m2.tutee_id = m.tutee_id
    )
    ORDER BY m.created_at DESC
");
$messagesQuery->bindParam(":tutor_id", $tutor_id);
$messagesQuery->execute();
$messages = $messagesQuery->fetchAll(PDO::FETCH_ASSOC);
// Handle AJAX request for fetching and sending messages
if (isset($_POST['fetch_messages'])) {
    $tutee_id = $_POST['tutee_id'];
    // Fetch messages for the selected tutee
    $messageFetchQuery = $user_login->runQuery("
        SELECT sender_type, message, created_at
        FROM messages
        WHERE tutor_id = :tutor_id AND tutee_id = :tutee_id
        ORDER BY created_at ASC
    ");
    $messageFetchQuery->bindParam(":tutor_id", $tutor_id);
    $messageFetchQuery->bindParam(":tutee_id", $tutee_id);
    $messageFetchQuery->execute();
    $messages = $messageFetchQuery->fetchAll(PDO::FETCH_ASSOC);
    // Mark all unread messages as read
    $markAsReadQuery = $user_login->runQuery("
        UPDATE messages
        SET is_read = 1
        WHERE tutor_id = :tutor_id 
        AND tutee_id = :tutee_id 
        AND sender_type = 'tutee'
        AND is_read = 0
    ");
    $markAsReadQuery->bindParam(":tutor_id", $tutor_id);
    $markAsReadQuery->bindParam(":tutee_id", $tutee_id);
    $markAsReadQuery->execute();
    // Output the conversation header (tutee name and picture)
    $tuteeInfoQuery = $user_login->runQuery("
        SELECT firstname, lastname, photo
        FROM tutee
        WHERE id = :tutee_id
    ");
    $tuteeInfoQuery->bindParam(":tutee_id", $tutee_id);
    $tuteeInfoQuery->execute();
    $tuteeInfo = $tuteeInfoQuery->fetch(PDO::FETCH_ASSOC);
    $tuteeImage = !empty($tuteeInfo['photo']) ? $tuteeInfo['photo'] : '../assets/TuteeFindLogoName.jpg';
    // Display the tutee's header
    echo "<div id='tuteeHeader' class='d-flex align-items-center mb-3'>";
    echo "<img src='$tuteeImage' class='rounded-circle me-3' alt='Tutee Picture' style='width: 50px; height: 50px;'>";
    echo "<h5><strong>{$tuteeInfo['firstname']} {$tuteeInfo['lastname']}</strong></h5>";
    echo "</div>";
    // Loop through and display each message in a bubble
    foreach ($messages as $message) {
        $messageClass = $message['sender_type'] == 'tutor' ? 'message-bubble-right' : 'message-bubble-left';
        echo "<div class='message-bubble {$messageClass}'>";
        echo "<p>{$message['message']}</p>";
        echo "</div>";
    }
    exit(); // Ensure that no further output is sent
}
// Handle sending a new message
if (isset($_POST['send_message'])) {
    $tutee_id = $_POST['tutee_id'];
    $message = $_POST['message'];
    $insertMessageQuery = $user_login->runQuery("
        INSERT INTO messages (tutor_id, tutee_id, sender_type, message, created_at)
        VALUES (:tutor_id, :tutee_id, 'tutor', :message, NOW())
    ");
    $insertMessageQuery->bindParam(":tutor_id", $tutor_id);
    $insertMessageQuery->bindParam(":tutee_id", $tutee_id);
    $insertMessageQuery->bindParam(":message", $message);
    $insertMessageQuery->execute();
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="what.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <link rel="icon" href="../assets/TuteeFindLogo.png" type="image/png">
    <title>Messages</title>
    
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
                    <li class="nav-link" data-bs-toggle="tooltip" data-bs-placement="right" title="Home">
                        <a href="../tutor/suggestedtutee">
                            <i class='bx bx-home-alt icon'></i>
                            <span class="text nav-text">Home</span>
                        </a>
                    </li>
                    <li class="nav-link custom-bg" data-bs-toggle="tooltip" data-bs-placement="right" title="Messages">
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
                    <li class="nav-link" data-bs-toggle="tooltip" data-bs-placement="right" title="Notification">
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
                    <li class="nav-link" data-bs-toggle="tooltip" data-bs-placement="right" title="Tutor Progress">
                        <a href="../tutor/progress">
                            <i class='bx bx-bar-chart-alt icon'></i>
                            <span class="text nav-text">Progress</span>
                        </a>
                    </li>
                    <li class="nav-link" data-bs-toggle="tooltip" data-bs-placement="right" title="Current Tutee">
                        <a href="../tutor/currenttutor">
                            <i class='bx bx-user icon'></i>
                            <span class="text nav-text">Tutors</span>
                        </a>
                    </li>
                    <li class="nav-link" data-bs-toggle="tooltip" data-bs-placement="right" title="Settings">
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
                    <div class="card1" style="color:white;">Messages</div>
                </div>
            </div>
        </div>
<div class="container-lg p-3">
    <div class="row">
<!-- Sidebar for conversation list -->
<div class="col-12 col-md-3 mb-3 mb-md-0">
    <div class="card shadow-sm">
        <div class="card-header">
            <h5>Inbox</h5>
        </div>
        <div class="card-body p-0">
            <ul class="list-group">
                <?php foreach ($messages as $message): ?>
                    <li class="list-group-item d-flex align-items-center" onclick="showMessages('<?php echo $message['tutee_id']; ?>')">
                        <img src="<?php echo $message['tutee_photo'] ?: '../assets/TuteeFindLogoName.jpg'; ?>" class="rounded-circle me-3" alt="Profile Picture" style="width: 50px; height: 50px;">
                        <div class="flex-grow-1">
                            <div class="d-flex flex-column">
                                <strong><?php echo $message['tutee_firstname'] . ' ' . $message['tutee_lastname']; ?></strong>
                                <small class="text-muted"><?php echo date('M d, Y', strtotime($message['created_at'])); ?></small>
                            </div>
                            <p class="mb-0">
                                <?php echo $message['sender_type'] == 'tutor' ? 'You: ' : ''; ?>
                                <?php echo htmlspecialchars($message['message']); ?>
                            </p>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>

        <!-- Main content area for message details -->
        <div class="col-12 col-md-9">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5>Messages</h5>
                </div>
                <div class="card-body d-flex flex-column justify-content-center align-items-center" id="messageContent" style="height: 70vh;">
                    <h3>Select a Conversation</h3>
                    <p>Choose from your existing conversations to view or respond to messages.</p>
                </div>
                <!-- Message Input Form (outside the message content area) -->
                <form id="sendMessageForm" class="mt-2" onsubmit="sendMessage(event, currentTuteeId)" style="display: none;">
                    <div class="input-group p-3">
                        <input type="text" id="messageInput" name="message" class="form-control" placeholder="Type your message here..." required>
                        <button class="btn btn-primary" type="submit">Send</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="script1.js"></script>          
</html>