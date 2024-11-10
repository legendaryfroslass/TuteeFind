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

// Handle AJAX request for fetching and sending messages
if (isset($_POST['fetch_messages'])) {
    $tutor_id = $_POST['tutor_id'];

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

    // Fetch the tutee's photo for the conversation view
    $tutorInfoQuery = $user_login->runQuery("
        SELECT firstname, lastname, photo
        FROM tutor
        WHERE id = :tutor_id
    ");
    $tutorInfoQuery->bindParam(":tutor_id", $tutor_id);
    $tutorInfoQuery->execute();
    $tutorInfo = $tutorInfoQuery->fetch(PDO::FETCH_ASSOC);
    $tutorImage = !empty($tutorInfo['photo']) ? $tutorInfo['photo'] : '../assets/profile-user.png';

    // Chat header with tutor's name
    echo "<div class='chat-header'>";
    echo "<div class='h2'>" . htmlspecialchars($tutorInfo['firstname']) . " " . htmlspecialchars($tutorInfo['lastname']) . "</div>";
    echo "</div>";

    // Chat body with a notification and messages
    echo "<div class='chat-body' id='chatBody'>";
    echo "<div class='notification'>";
    echo "<p>- This is the start of your conversation -</p>";
    echo "</div>";

// Display the messages dynamically
foreach ($messages as $message) {
    // Adjust message class logic: 'outgoing' for user, 'incoming' for tutor
    $messageClass = $message['sender_type'] == 'tutee' ? 'message outgoing' : 'message incoming';
    echo "<div class='{$messageClass}'>";
    echo "<p>" . htmlspecialchars($message['message']) . "</p>";
    echo "</div>";
}

echo "</div>"; // End of chat-body

// Ensure that the scroll happens after the messages are appended
echo "<script>
    // Wait for the chatBody element to be populated with messages
    window.setTimeout(function() {
        const chatBody = document.getElementById('chatBody');
        chatBody.scrollTop = chatBody.scrollHeight;
    }, 100); // Small delay to ensure messages are rendered first
</script>";

// Chat footer with input and send button
echo "<div class='chat-footer'>";
echo "<input id='messageInput' placeholder='Type your message' type='text' class='form-control' required>";
echo "<button id='sendButton' class='btn btn-primary' onclick='sendMessage(event, {$tutor_id})'>Send</button>";
echo "</div>"; // End of chat-footer

echo "</div>"; // End of chat-card

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
        

            <div class="container-lg p-10 table table-container">
                <div class="row custom-row p-3">
                    <!-- First column for conversation list -->
                    <div class="col-md-4 message-table">
                        <table class="table">
                            <tbody>
                                <?php foreach ($messages as $message): ?>
                                    <?php
                                        // Use the tutee's profile picture if available; otherwise, use a default image
                                        $tutorImage = !empty($message['tutor_photo']) ? $message['tutor_photo'] : '../assets/profile-user.png';
                                        // Determine the message display style
                                        $messageClass = $message['sender_type'] == 'tutor' ? 'font-weight-bold' : '';
                                    ?>
                                    <tr onclick="showMessages('<?php echo $message['tutor_id']; ?>')">
                                        <td class="d-flex align-items-center">
                                            <img src="<?php echo $tutorImage; ?>" class="rounded-circle me-3" alt="Profile Picture" style="width: 50px; height: 50px;">
                                            <div>
                                                <div class="mb-1">
                                                    <strong><?php echo $message['tutor_firstname'] . ' ' . $message['tutor_lastname']; ?></strong>
                                                    <small class="text-muted"><?php echo date('M d, Y', strtotime($message['created_at'])); ?></small>
                                                </div>
                                                <!-- Display the most recent message with styling based on sender -->
                                                <p class="mb-0 truncateMessage <?php echo $messageClass; ?>">
                                                    <?php echo $message['sender_type'] == 'tutee' ? 'You: ' : ''; ?>
                                                    <?php echo htmlspecialchars($message['message']); ?>
                                                </p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Second column for message content -->
                    <div class="chat-card col-md-8 message-content" id="messageContent">
                        <div class="chat-header">
                        </div>
                        <div class="chat-body" id="chatBody">
                            <div class="notification">
                                <h3>Select a Message</h3>
                                <p>Choose from your existing conversations</p>
                            </div>
                        </div>
                    </div>
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