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
$barangay = $userData['barangay'];
$tutee_id = $userData['id'];

$imagePath = !empty($userData['photo']) ? $userData['photo'] : '../assets/TuteeFindLogoName.jpg';

// Fetch unread notifications count for the current tutee
$unreadNotifQuery = $user_login->runQuery("SELECT COUNT(*) AS unread_count FROM notifications WHERE receiver_id = :tutee_id AND status = 'unread'");
$unreadNotifQuery->bindParam(":tutee_id", $tutee_id);
$unreadNotifQuery->execute();
$unreadNotifData = $unreadNotifQuery->fetch(PDO::FETCH_ASSOC);
$unreadNotifCount = $unreadNotifData['unread_count'];

$messagesQuery = $user_login->runQuery("
    SELECT m.tutor_id, t.firstname AS tutor_firstname, t.lastname AS tutor_lastname,
            t.photo AS tutor_photo, m.message, m.created_at, m.sender_type
    FROM messages m
    JOIN tutor t ON m.tutor_id = t.id
    WHERE m.tutee_id = :tutee_id
    AND m.created_at = (
        SELECT MAX(m2.created_at)
        FROM messages m2
        WHERE m2.tutee_id = m.tutee_id
        AND m2.tutor_id = m.tutor_id
    )
    ORDER BY m.created_at DESC
");
$messagesQuery->bindParam(":tutee_id", $tutee_id);
$messagesQuery->execute();
$messages = $messagesQuery->fetchAll(PDO::FETCH_ASSOC);

if (isset($_POST['fetch_messages'])) {
    $tutor_id = $_POST['tutor_id'];
    
    // Query for fetching messages
    $messageFetchQuery = $user_login->runQuery("
        SELECT sender_type, message, created_at
        FROM messages
        WHERE tutee_id = :tutee_id AND tutor_id = :tutor_id
        ORDER BY created_at ASC
    ");
    $messageFetchQuery->bindParam(":tutee_id", $tutee_id);
    $messageFetchQuery->bindParam(":tutor_id", $tutor_id);
    $messageFetchQuery->execute();
    $messages = $messageFetchQuery->fetchAll(PDO::FETCH_ASSOC);
    // Send the messages in JSON format

    // Fetch tutor info for the header
    $tutorInfoQuery = $user_login->runQuery("
        SELECT firstname, lastname, photo
        FROM tutor
        WHERE id = :tutor_id
    ");
    $tutorInfoQuery->bindParam(":tutor_id", $tutor_id);
    $tutorInfoQuery->execute();
    $tutorInfo = $tutorInfoQuery->fetch(PDO::FETCH_ASSOC);

    $tutorImage = !empty($tutorInfo['photo']) ? $tutorInfo['photo'] : '../assets/profile-user.png';

    // Generate HTML response
    ob_start();
    echo "<div class='chat-header'>";
    echo "<div class='h2'>" . htmlspecialchars($tutorInfo['firstname']) . " " . htmlspecialchars($tutorInfo['lastname']) . "</div>";
    echo "</div>";

    echo "<div class='chat-body' id='chatBody'>";
    echo "<div class='notification'><p>- This is the start of your conversation -</p></div>";

    foreach ($messages as $message) {
        $messageClass = $message['sender_type'] == 'tutee' ? 'message outgoing' : 'message incoming';
        echo "<div class='{$messageClass}'><p>" . htmlspecialchars($message['message']) . "</p></div>";
    }

    echo "</div>"; // Close chat-body
    echo "</div>"; // Close messageContent

    // Output the chat footer (message input) separately
    echo "<div class='chat-footer'>";
    echo "<input id='messageInput' placeholder='Type your message' type='text' class='form-control' required>";
    echo "<button id='sendButton' class='btn btn-primary' onclick='sendMessage(event, {$tutor_id})'>Send</button>";
    echo "</div>";

    exit();
}


// Handle sending a new message
if (isset($_POST['send_message'])) {
    $tutor_id = $_POST['tutor_id'];
    $message = $_POST['message'];

    $insertMessageQuery = $user_login->runQuery("
        INSERT INTO messages (tutee_id, tutor_id, sender_type, message, created_at)
        VALUES (:tutee_id, :tutor_id, 'tutee', :message, NOW())
    ");
    $insertMessageQuery->bindParam(":tutee_id", $tutee_id);
    $insertMessageQuery->bindParam(":tutor_id", $tutor_id);
    $insertMessageQuery->bindParam(":message", $message);
    $insertMessageQuery->execute();

    echo "Message sent successfully";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="notif.css">
        <link rel="stylesheet" href="tutee.css">
        <link rel="stylesheet" href="message.css">
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
                        <li class="nav-link" data-bs-toggle="tooltip" data-bs-placement="right" title="Home">
                            <a href="../tutee/tutee">
                                <i class='bx bx-home-alt icon'></i>
                                <span class="text nav-text">Home</span>
                            </a>
                        </li>
                        <li class="nav-link navbar-active" data-bs-toggle="tooltip" data-bs-placement="right" title="Messages">
                            <a href="../tutee/message">
                                <i class='bx bxs-inbox icon' ></i>
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
                        <li class="nav-link" data-bs-toggle="tooltip" data-bs-placement="right" title="Tutor">
                            <a href="../tutee/tutor">
                                <i class='bx bx-user icon'></i>
                                <span class="text nav-text">Tutors</span>
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
                                <li class="list-group-item d-flex align-items-center" onclick="showMessages('<?php echo $message['tutor_id']; ?>')">
                                    <img src="<?php echo $message['tutor_photo'] ?: '../assets/TutorFindLogoName.jpg'; ?>" class="rounded-circle me-3" alt="Profile Picture" style="width: 50px; height: 50px;">
                                    <div class="flex-grow-1">
                                        <div class="d-flex flex-column">
                                            <strong><?php echo $message['tutor_firstname'] . ' ' . $message['tutor_lastname']; ?></strong>
                                            <small class="text-muted"><?php echo date('M d, Y', strtotime($message['created_at'])); ?></small>
                                        </div>
                                        <p class="mb-0">
                                            <?php echo $message['sender_type'] == 'tutee' ? 'You: ' : ''; ?>
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
                    <div class="card-body d-flex flex-column" id="messageContent" style="height: 70vh;">
                        <h3>Select a Conversation</h3>
                        <p>Choose from your existing conversations to view or respond to messages.</p>
                    </div>
                    <!-- Message Input Form (outside the message content area) -->
                    <form id="sendMessageForm" class="mt-2" onsubmit="sendMessage(event, currentTutorId)" style="display: none;">
                        <div class="input-group p-3">
                            <input type="text" id="messageInput" name="message" class="form-control" placeholder="Type your message here..." required>
                            <button class="btn btn-primary" type="submit">Send</button>
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

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="notif.js"></script>
    <script src="message.js"></script>
    <script src="tutee_sidebar.js"></script>
    </body>
</html>