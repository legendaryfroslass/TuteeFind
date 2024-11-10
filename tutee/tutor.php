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
$unreadNotifQuery = $user_login->runQuery("SELECT COUNT(*) AS unread_count FROM notifications WHERE receiver_id = :tutee_id AND status = 'unread'");
$unreadNotifQuery->bindParam(":tutee_id", $tutee_id);
$unreadNotifQuery->execute();
$unreadNotifData = $unreadNotifQuery->fetch(PDO::FETCH_ASSOC);
$unreadNotifCount = $unreadNotifData['unread_count'];

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

function removeTutor($tutor_id, $tutee_id) {
    global $user_login;
    try {
        $stmt = $user_login->runQuery("UPDATE requests SET status = 'removed' WHERE tutor_id = :tutor_id AND tutee_id = :tutee_id");
        $stmt->bindParam(":tutor_id", $tutor_id);
        $stmt->bindParam(":tutee_id", $tutee_id);
        $stmt->execute();
        return true;
    } catch (PDOException $ex) {
        echo "Error removing tutor: " . $ex->getMessage();
        return false;
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['remove_tutor'])) {
        $tutor_id = $_POST['tutor_id'];
        removeTutor($tutor_id, $tutee_id); // Pass both tutor_id and tutee_id
        // Refresh the page to reflect the changes
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
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
                                                        <button type="button" name="remove_tutor" id="removeTutorBtn" class="btn btn-outline-danger bx bx-user-x" data-bs-toggle="modal" data-bs-target="#removeTuteeModal"
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#removeTuteeModal"
                                                            data-tutor-id="<?php echo htmlspecialchars($tutor['id']); ?>" 
                                                            data-tutee-id="<?php echo htmlspecialchars($tutee_id); ?>"> <!-- Ensure data attributes are set --> 
                                                        </button>
                                                </form>
                                                    </div>
                                                </div>
                                            </h5>
                                        </div>
                                        <div id="collapse<?php echo htmlspecialchars(string: $tutor['id']); ?>" class="collapse show" aria-labelledby="heading<?php echo htmlspecialchars($tutor['id']); ?>" data-parent="#accordion">
                                            <div class="card-body">
                                                <h5>Other Information</h5>
                                                <div class="table-responsive">
                                                    <table class="table table-bordered">
                                                        <thead>
                                                            <tr>
                                                                <th>Student ID</th>
                                                                <th>Course</th>
                                                                <th>Professor</th>
                                                                <th>Barangay</th>
                                                                <th>Contact No</th>
                                                                <th>Facebook Link</th>
                                                                <th>Email</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr class="text-center align-middle">
                                                                <td><?php echo htmlspecialchars($tutor['student_id']); ?></td>
                                                                <td><?php echo htmlspecialchars($tutor['course']); ?></td>
                                                                <td><?php echo htmlspecialchars($tutor['professor']); ?></td>
                                                                <td><?php echo htmlspecialchars($tutor['barangay']); ?></td>
                                                                <td><?php echo htmlspecialchars($tutor['number']); ?></td>
                                                                <td><a href="<?php echo htmlspecialchars($tutor['fblink']); ?>"><?php echo htmlspecialchars($tutor['fblink']); ?></a></td>
                                                                <td><?php echo htmlspecialchars($tutor['emailaddress']); ?></td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
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

        <!-- Remove Tutee Modal -->
        <div class="modal fade modal-shake" id="removeTuteeModal" tabindex="-1" role="dialog" aria-labelledby="removeTuteeModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header d-flex justify-content-center align-items-center border-0 mt-2">
                        <!-- Centered header content -->
                        <img src="../assets/remove.png" alt="Remove" class="delete-icon" style="width: 65px; height: 65px;">
                    </div>
                    <div class="modal-body d-flex justify-content-center align-items-center" id="modalBody">
                        <p>Are you sure you want to remove this tutor?</p>
                    </div>
                    <div class="modal-footer d-flex justify-content-center border-0">
                    <form id="removeTuteeForm" method="POST">
                        <input type="hidden" name="tutor_id" id="modalTutorId" value=""> <!-- Added hidden input for tutor_id -->
                        <input type="hidden" name="tutee_id" id="tutee_id" value=""> <!-- Ensure tutee_id is here -->
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" id="confirmTutorRemove" name="remove_tutor" class="btn btn-danger">Remove</button>
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
                    <button type="button" class="btn btn-primary" id="msg-sendBtn">
                        <i class='bx bx-send'></i> Send
                    </button>
                </div>
            </div>
        </div>
    </div>

        <!-- Javascript -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
        <script src="tutee_sidebar.js"></script>
        <script src="notif.js"></script>
        <script src="tutor.js"></script>
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
        });
    });
});

</script>
    </body>
</html>