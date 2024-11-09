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

    // Display the tutee's picture at the top of the conversation
    echo "<div class='d-flex align-items-center mb-3'>";
    echo "<img src='$tutorImage' class='rounded-circle me-3' alt='Tutor Picture' style='width: 50px; height: 50px;'>";
    echo "<h5>{$tutorInfo['firstname']} {$tutorInfo['lastname']}</h5>";
    echo "</div>";

    echo "<div class='message-container'>";

    // Display the messages dynamically
    foreach ($messages as $message) {
        $messageClass = $message['sender_type'] == 'tutee' ? 'message-bubble-right' : 'message-bubble-left';
        echo "<div class='message-bubble {$messageClass}'>";
        echo "<p>{$message['message']}</p>";
        echo "</div>";
    }

    echo "</div>";

    // Add a form for sending a new message
    echo '<form id="sendMessageForm" class="mt-3" onsubmit="sendMessage(event, '.$tutor_id.')">
            <div class="input-group">
                <input type="text" id="messageInput" name="message" class="form-control" placeholder="Type your message here..." required>
                <button class="btn btn-primary" type="submit">Send</button>
            </div>
        </form>';

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
        <style>
            body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #f5f5f7;
            overflow: hidden;
        }

        .chat-card {
            display: flex;
            flex-direction: column;
            min-width: 65%;
            height: 500px; /* Set a fixed height for the chat box */
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .chat-header {
            padding: 10px;
            background-color: #f2f2f2;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .chat-header .h2 {
            font-size: 16px;
            color: #333;
            margin: 0;
        }

        .chat-body {
            flex-grow: 1; /* Fills available space */
            padding: 20px;
            max-height: 100%;
            overflow-y: auto;
        }

        .message {
            margin-bottom: 10px;
            padding: 10px;
            border-radius: 5px;
            max-width: 80%;
            animation: chatAnimation 0.3s ease-in-out;
            animation-fill-mode: both;
        }

        .incoming {
            background-color: #e1e1e1;
            align-self: flex-start;
        }

        .outgoing {
            background-color: #d1e7ff;
            align-self: flex-end;
            text-align: right;
            margin-left: auto; /* Aligns outgoing messages to the right */
            max-width: 70%; /* Adjusts width for aesthetics */
        }
        .message p {
            font-size: large;
            color: #333;
            margin: 0;
        }

        .chat-footer {
            padding: 10px;
            background-color: #f2f2f2;
            display: flex;
            height: 15%;
        }

        .chat-footer input[type="text"] {
            flex-grow: 1;
            padding: 5px;
            border: 1px solid #ddd;
            border-radius: 3px;
            font-size: large;
            outline: none;
        }

        .chat-footer button {
            padding: 5px 10px;
            border: none;
            background-color: #4285f4;
            color: #fff;
            font-weight: bold;
            cursor: pointer;
            margin-left: 5px;
            border-radius: 3px;
            transition: background-color 0.3s;
        }

        .chat-footer button:hover {
            background-color: #0f9d58;
        }

        /* Animation for messages */
        @keyframes chatAnimation {
            0% {
                opacity: 0;
                transform: translateY(10px);
            }
            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .chat-card .message:nth-child(even) {
            animation-delay: 0.2s;
        }

        .chat-card .message:nth-child(odd) {
            animation-delay: 0.3s;
        }

        .notification {
            text-align: center;
            color: #888; /* Light grey color */
            font-size: 14px;
            margin-bottom: 10px;
            padding: 5px 0;
        }
        </style>
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
                        <li class="nav-link navbar-active">
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
                        <li class="nav-link">
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
            <div class="container-lg p-3">
                <div class="career-form headings d-flex justify-content-center mt-3">
                    <div class="row">
                        <div class="card1" style="color:white;">Messages</div>
                    </div>
                </div>
            </div>
        

            <div class="container-lg p-10 table table-container">
                <div class="row custom-row">
                    <!-- First column for conversation list -->
                    <div class="col-md-4 message-table message-list">
                        <table class="table">
                            <tbody>
                                <?php foreach ($messages as $message): ?>
                                    <?php
                                        // Use the tutee's profile picture if available; otherwise, use a default image
                                        $tutorImage = !empty($message['tutor_photo']) ? $message['tutor_photo'] : '../assets/profile-user.png';
                                        // Determine the message display style
                                        $messageClass = $message['sender_type'] == 'tutor' ? 'font-weight-bold' : '';
                                    ?>
                                    <tr onclick="showMessages('<?php echo $message['tutor_id']; ?>', true, event)">
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
                    <div class="chat-card col-md-2 message-content" id="messageContent">
                        <div class="chat-header">
                            <div class="h2">Jane Doe</div>
                        </div>
                        <div class="chat-body" id="chatBody">
                            <div class="notification">
                                <p>- This is the start of your conversation -</p>
                            </div>
                            <div class="message incoming">
                                <p>Hello! How can I assist you today?</p>
                            </div>
                        </div>
                        <div class="chat-footer">
                            <input id="messageInput" placeholder="Type your message" type="text">
                            <button id="sendButton">Send</button>
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
    <script srd="notif.js"></script>
    <script>
    let currentTutorId = null;
    let messageInterval = null;

    function fetchNewMessages(tutorId) {
        if (currentTutorId === tutorId) {
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    // Preserve the current input value
                    var messageInput = document.getElementById("messageInput");
                    var currentMessage = messageInput ? messageInput.value : "";

                    // Update the message content
                    document.getElementById('messageContent').innerHTML = xhr.responseText;

                    // Restore the input value
                    if (messageInput) {
                        messageInput.value = currentMessage;
                    }
                }
            };
            xhr.send("fetch_messages=1&tutor_id=" + tutorId);
        }
    }

    function highlightSelectedRow(event) {
        // Check if event exists and has currentTarget
        if (event && event.currentTarget) {
            // Remove the 'selected' class from any previously selected row
            var previousSelected = document.querySelector('.message-table tr.selected');
            if (previousSelected) {
                previousSelected.classList.remove('selected');
            }

            // Add the 'selected' class to the clicked row
            event.currentTarget.classList.add('selected');
        }
    }


    function showMessages(tutorId, event = null, autoScroll = true) {
    // Call highlightSelectedRow and pass the event object if it exists
    if (event) {
        highlightSelectedRow(event);
    }

    if (currentTutorId !== tutorId) {
        currentTutorId = tutorId;

        // Stop previous polling if any
        if (messageInterval) {
            clearInterval(messageInterval);
        }
    }

    var xhr = new XMLHttpRequest();
    xhr.open("POST", "", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

    // Save the current input value and scroll position
    var currentMessage = document.getElementById("messageInput") ? document.getElementById("messageInput").value : "";
    var messageContent = document.getElementById("messageContent");
    var currentScroll = messageContent ? messageContent.scrollTop : 0;

    xhr.onreadystatechange = function() {
        if (xhr.readyState == 4 && xhr.status == 200) {
            messageContent.innerHTML = xhr.responseText;

            // Restore the previously typed message
            if (document.getElementById("messageInput")) {
                document.getElementById("messageInput").value = currentMessage;
            }

            // Restore the scroll position or scroll to the bottom if required
            if (autoScroll) {
                messageContent.scrollTop = messageContent.scrollHeight;
            } else {
                messageContent.scrollTop = currentScroll;  // Restore to previous scroll position
            }
        }
    };
    xhr.send("fetch_messages=1&tutor_id=" + tutorId);
}

    function sendMessage(event, tutorId) {
        event.preventDefault();  // Prevent the form from submitting the traditional way
        var messageInput = document.getElementById("messageInput");
        var message = messageInput.value;

        if (message.trim() === "") {
            return;  // Do not send empty messages
        }

        var xhr = new XMLHttpRequest();
        xhr.open("POST", "", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

        xhr.onreadystatechange = function() {
            if (xhr.readyState == 4 && xhr.status == 200) {
                // After sending the message, reload the messages for this tutor
                showMessages(tutorId);

                // Clear the input field after sending
                messageInput.value = "";
            }
        };

        // Send the message to the server
        xhr.send("send_message=1&tutor_id=" + tutorId + "&message=" + encodeURIComponent(message));
    }





            document.getElementById("sendButton").addEventListener("click", function() {
                const messageInput = document.getElementById("messageInput");
                const messageText = messageInput.value.trim();

                if (messageText) {
                    const chatBody = document.getElementById("chatBody");

                    // Create new message element
                    const newMessage = document.createElement("div");
                    newMessage.classList.add("message", "outgoing");

                    const messageContent = document.createElement("p");
                    messageContent.textContent = messageText;

                    newMessage.appendChild(messageContent);
                    chatBody.appendChild(newMessage);

                    // Clear the input field
                    messageInput.value = "";

                    // Scroll to the bottom of the chat
                    chatBody.scrollTop = chatBody.scrollHeight;
                }
            });

            // Optional: Send message on Enter key press
            document.getElementById("messageInput").addEventListener("keypress", function(e) {
                if (e.key === "Enter") {
                    e.preventDefault();
                    document.getElementById("sendButton").click();
                }
            });
        </script>
    </body>
</html>