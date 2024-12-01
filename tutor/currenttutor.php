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


// Ensure $userData is not false before accessing its fields
if ($userData) {
    $firstname = $userData['firstname'];
    $lastname = $userData['lastname'];
    $age = $userData['age'];
    $sex = $userData['sex'];
    $number = $userData['number'];
    $barangay = $userData['barangay'];
    $course = $userData['course'];
    $year = $userData['year'];
    $preferred_day = $userData['preferred_day'];
    $professor = $userData['professor'];
    $fblink = $userData['fblink'];
    $emailaddress = $userData['emailaddress'];
    $tutor_id = $userData['id']; // Assuming 'id' is the primary key for the tutee

    // Check if $userData['photo'] is empty
    $imagePath = !empty($userData['photo']) ? $userData['photo'] : '../assets/TuteeFindLogoName.jpg';

    $tuteeStmt = $user_login->runQuery("
        SELECT 
        tutee.id, tutee.firstname, tutee.lastname, tutee.age, tutee.sex, tutee.number, tutee.guardianname, tutee.fblink, 
        tutee.barangay, tutee.emailaddress, tutee.photo
        FROM 
            tutee
        INNER JOIN 
            requests ON tutee.id = requests.tutee_id
        WHERE 
            requests.status = 'accepted' AND requests.tutor_id = :tutor_id
    ");
    $tuteeStmt->bindParam(":tutor_id", $tutor_id);
    $tuteeStmt->execute();
    $tutees = $tuteeStmt->fetchAll(PDO::FETCH_ASSOC);
}

function removeTutee($tutee_id) {
    global $user_login;
    try {
        $stmt = $user_login->runQuery("UPDATE requests SET status = 'removed' WHERE tutee_id = :tutee_id");
        $stmt->bindParam(":tutee_id", $tutee_id);
        $stmt->execute();
        return true;
    } catch (PDOException $ex) {
        echo "Error removing tutee: " . $ex->getMessage();
        return false;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['remove_tutee'])) {
        $tutee_id = $_POST['tutee_id'];
        if (removeTutee($tutee_id)) {
            // Refresh the page to reflect the changes
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }
    }
}




if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['remove_tutee'])) {
        $tutee_id = $_POST['tutee_id'];
        if (removeTutee($tutee_id)) {
            // Refresh the page to reflect the changes
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }
    }
}

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
    <title>Tutor</title>
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
                    <li class="nav-link custom-bg" data-bs-placement="right" title="Current Tutee">
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
                    <div class="card1" style="color:white;">Current tutee</div>
                </div>
            </div>
        </div>
        <div class="container-lg results p-3">
            <div class="row">
                <div class="container-lg">
                    <div class="filter-result">
                        <?php if (!empty($tutees)): ?>
                        <?php foreach ($tutees as $tutee): ?>
                        <div class="mb-2">
                            <div id="accordion">
                                <div class="card shadow-lg rounded-3">
                                    <div class="card-header" id="heading<?php echo htmlspecialchars($tutee['id']); ?>">
                                        <h5 class="mb-0">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <button class="btn btn-link" data-toggle="collapse" data-target="#collapse<?php echo htmlspecialchars($tutee['id']); ?>" aria-expanded="true" aria-controls="collapse<?php echo htmlspecialchars($tutee['id']); ?>">
                                                <img style="height: 65px; width: 65px; border-radius: 65px;" src="<?php echo !empty($tutee['photo']) ? $tutee['photo'] : '../assets/TuteeFindLogoName.jpg'; ?>" alt="Tutee Photo" class="img-fluid">
                                                        </button>
                                                <div class="col" data-toggle="collapse" data-target="#collapse<?php echo htmlspecialchars($tutee['id']); ?>" aria-expanded="true" aria-controls="collapse<?php echo htmlspecialchars($tutee['id']); ?>">
                                                    <?php echo htmlspecialchars($tutee['firstname'] . ' ' . $tutee['lastname']); ?>
                                                </div>
                                                <div class="job-right my-4 flex-shrink-0">
                                                    <button type="button" 
                                                            class="btn btn-outline-success bx" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#messageModal"
                                                            data-tutee-name="<?php echo $tutee['firstname'] . ' ' . $tutee['lastname']; ?>"
                                                            data-tutee-id="<?php echo $tutee['id']; ?>"
                                                            >
                                                        <i class='bx bx-message-square-dots'></i>
                                                    </button>
                                                    <button type="button" class="btn btn-outline-danger bx bx-user-x" data-toggle="modal" data-target="#removeTuteeModal" data-tutee-id="<?php echo htmlspecialchars($tutee['id']); ?>"></button>
                                                </div>
                                            </div>
                                        </h5>
                                    </div>
                                    <div id="collapse<?php echo htmlspecialchars($tutee['id']); ?>" class="collapse show" aria-labelledby="heading<?php echo htmlspecialchars($tutee['id']); ?>" data-parent="#accordion">
                                        <div class="card-body">
                                            <h5>Other Information</h5>
<div class="row">
    <!-- First Name Section -->
    <div class="col-12 col-md-4">
        <div class="form-group mb-4">
            <label class="nav-text info-header">Guardian Name</label>
            <div class="border p-2 rounded bg-light">
                <?php echo htmlspecialchars($tutee['guardianname']); ?>
            </div>
        </div>
    </div>

    <!-- Barangay Section -->
    <div class="col-12 col-md-4">
        <div class="form-group mb-4">
            <label class="nav-text info-header">Barangay</label>
            <div class="border p-2 rounded bg-light">
                <?php echo htmlspecialchars($tutee['barangay']); ?>
            </div>
        </div>
    </div>

    <!-- Contact No Section -->
    <div class="col-12 col-md-4">
        <div class="form-group mb-4">
            <label class="nav-text info-header">Contact No</label>
            <div class="border p-2 rounded bg-light">
                <?php echo htmlspecialchars($tutee['number']); ?>
            </div>
        </div>
    </div>

    <!-- Facebook Link Section -->
    <div class="col-12 col-md-6">
        <div class="form-group mb-4">
            <label class="nav-text info-header">Facebook Link</label>
            <div class="border p-2 rounded bg-light">
                <a href="<?php echo htmlspecialchars($tutee['fblink']); ?>" target="_blank">
                    <?php echo htmlspecialchars($tutee['fblink']); ?>
                </a>
            </div>
        </div>
    </div>

    <!-- Email Address Section -->
    <div class="col-12 col-md-6">
        <div class="form-group mb-4">
            <label class="nav-text info-header">Email Address</label>
            <div class="border p-2 rounded bg-light">
                <?php echo htmlspecialchars($tutee['emailaddress']); ?>
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
                        <?php if (empty($tutees)): ?>
                        <div class="container d-flex justify-content-center align-items-center update rounded shadow-lg" style="height: 100px;">
                            <h5>No current tutee.</h5>
                        </div>
                        <?php endif; ?>
                        <?php else: ?>
    <div class="table-container d-flex justify-content-center align-items-center flex-column">
        <div class="row text-center">
            <img src="../assets/tutee-blankplaceholder-grey.png" alt="" style="width: 200px;">
        </div>
        <div class="row text-center">
            <p class="container medium-font pt-3">No tutees found</p>
        </div>
    </div>
                        <?php endif; ?>
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

        <!-- Remove Tutee Modal -->
        <div class="modal fade" id="removeTuteeModal" tabindex="-1" role="dialog" aria-labelledby="removeTuteeModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header d-flex justify-content-center align-items-center border-0 mt-2">
                        <!-- Centered header content -->
                        <img src="../assets/remove.png" alt="Remove" class="delete-icon" style="width: 65px; height: 65px;">
                    </div>
                    <div class="modal-body d-flex justify-content-center align-items-center" id="modalBody">
                        <p>Are you sure you want to remove this tutee?</p>
                    </div>
                    <div class="modal-footer d-flex justify-content-center border-0">
                        <form id="removeTuteeForm" method="post">
                            <input type="hidden" name="tutee_id" id="tutee_id" value="">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                            <button type="submit" name="remove_tutee" class="btn btn-danger">Remove</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- end modal -->
</div>
    <script>
        // Function to generate a random ID
        function generateRandomId() {
            return Math.random().toString(36).substring(2, 15);
        }

        // Get all collapsible elements
        var collapsibles = document.querySelectorAll('.collapse');

        // Iterate through each collapsible element
        collapsibles.forEach(function(collapsible) {
            // Generate a unique ID for the collapsible
            var collapseId = generateRandomId();
            collapsible.setAttribute('id', collapseId);

            // Get the button that toggles the collapsible
            var button = collapsible.previousElementSibling.querySelector('button');

            // Update the data-target attribute of the button to point to the generated ID
            button.setAttribute('data-target', '#' + collapseId);
        });


    document.addEventListener('DOMContentLoaded', function() {
        $('#removeTuteeModal').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget); // Button that triggered the modal
            var tuteeId = button.data('tutee-id'); // Extract info from data-* attributes
            var modal = $(this);
            modal.find('#tutee_id').val(tuteeId); // Set the tutee ID in the form
        });
    });

document.addEventListener("DOMContentLoaded", function () {
  var messageModal = document.getElementById("messageModal");

  if (messageModal) {
    messageModal.addEventListener("show.bs.modal", function (event) {
      var button = event.relatedTarget;
      var tuteeName = button.getAttribute("data-tutee-name");
      var tuteeId = button.getAttribute("data-tutee-id");

      var recipientField = messageModal.querySelector("#recipient");
      var hiddenInput = messageModal.querySelector("#tutee_id");

      if (recipientField && hiddenInput) {
        recipientField.textContent = tuteeName; // Set recipient name
        hiddenInput.value = tuteeId; // Set hidden input value
      } else {
        console.error("Recipient field or hidden input not found.");
      }
    });
  }
  // else {
  //   console.error("Message modal not found.");
  // }
});
    </script>


    <script src="script.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>    
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>


    
</body>
</html>