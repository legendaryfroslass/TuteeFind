<?php
session_start();

// spinner
include('spinner.php');
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

$firstname = $userData['firstname'];
$lastname = $userData['lastname'];
$barangay = $userData['barangay'];
$tutee_id = $userData['id'];

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

if ($userData) {
    $firstname = $userData['firstname'];
    $lastname = $userData['lastname'];
    $tutee_id = $userData['id'];
    $imagePath = !empty($userData['photo']) ? $userData['photo'] : '../assets/TuteeFindLogoName.jpg';

    $tutorStmt = $user_login->runQuery("
        SELECT tutor.id, tutor.firstname, tutor.lastname
        FROM tutor
        INNER JOIN requests ON tutor.id = requests.tutor_id
        WHERE requests.status = 'accepted' AND requests.tutee_id = :tutee_id
    ");
    $tutorStmt->bindParam(":tutee_id", $tutee_id);
    $tutorStmt->execute();
    $tutors = $tutorStmt->fetchAll(PDO::FETCH_ASSOC);

    // New code: Store tutor names in an array
    $tutorNames = [];
    foreach ($tutors as $tutor) {
        $tutorNames[$tutor['id']] = $tutor['firstname'] . ' ' . $tutor['lastname'];
    }

    $progressData = [];
    foreach ($tutors as $tutor) {
        $progressStmt = $user_login->runQuery("
            SELECT week_number, uploaded_files, description, date
            FROM tutee_progress 
            WHERE tutor_id = :tutor_id AND tutee_id = :tutee_id
            ORDER BY week_number ASC
        ");
        $progressStmt->bindParam(":tutor_id", $tutor['id']);
        $progressStmt->bindParam(":tutee_id", $tutee_id);  // make sure $tutee_id is available
        $progressStmt->execute();
        $progressData[$tutor['id']] = $progressStmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
// Fetch status and rating existence for each tutor
$statusData = [];
foreach ($tutors as $tutor) {
    // Fetch session status
    $statusStmt = $user_login->runQuery("
        SELECT status 
        FROM tutor_sessions 
        WHERE tutee_id = :tutee_id AND tutor_id = :tutor_id
    ");
    $statusStmt->bindParam(':tutee_id', $tutee_id);
    $statusStmt->bindParam(':tutor_id', $tutor['id']);
    $statusStmt->execute();
    $status = $statusStmt->fetch(PDO::FETCH_ASSOC);

    // Check if rating exists
    $ratingStmt = $user_login->runQuery("
        SELECT COUNT(*) AS rating_exists 
        FROM tutor_ratings 
        WHERE tutee_id = :tutee_id AND tutor_id = :tutor_id
    ");
    $ratingStmt->bindParam(':tutee_id', $tutee_id);
    $ratingStmt->bindParam(':tutor_id', $tutor['id']);
    $ratingStmt->execute();
    $rating = $ratingStmt->fetch(PDO::FETCH_ASSOC);

    $statusData[$tutor['id']] = [
        'status' => $status ? $status['status'] : '',
        'rating_exists' => $rating['rating_exists'] > 0
    ];
}
require_once '../vendor/autoload.php';
// Decode JSON input
$requestData = json_decode(file_get_contents("php://input"), true);
// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $requestData['action'] ?? null;
    try {
        if ($action === 'confirmFinishBtnModal') {
            // Finish session action
            $tutor_id = $requestData['tutor_id'];
            $tutee_id = $requestData['tutee_id'];
        
            // Run SQL query to update the session status
            $updateStmt = $user_login->runQuery("
                UPDATE tutor_sessions 
                SET status = 'completed' 
                WHERE tutee_id = :tutee_id AND tutor_id = :tutor_id
            ");
            $updateStmt->bindParam(':tutee_id', $tutee_id);
            $updateStmt->bindParam(':tutor_id', $tutor_id);
            $updateStmt->execute();
        
            // Fetch tutee and tutor information
            $tuteeData = $user_login->runQuery("SELECT emailaddress, firstname, lastname FROM tutee WHERE id = :tutee_id");
            $tuteeData->bindParam(':tutee_id', $tutee_id);
            $tuteeData->execute();
            $tutee = $tuteeData->fetch(PDO::FETCH_ASSOC);
        
            $tutorData = $user_login->runQuery("SELECT emailaddress, firstname, lastname FROM tutor WHERE id = :tutor_id");
            $tutorData->bindParam(':tutor_id', $tutor_id);
            $tutorData->execute();
            $tutor = $tutorData->fetch(PDO::FETCH_ASSOC);
        
            // Check if both tutee and tutor data were successfully retrieved
            if ($tutee && $tutor) {
                $tuteeEmail = $tutee['emailaddress'];
                $tuteeName = $tutee['firstname'] . ' ' . $tutee['lastname'];
                $tutorEmail = $tutor['emailaddress'];
                $tutorName = $tutor['firstname'] . ' ' . $tutor['lastname'];
                // Insert notification into notifications table
                $title = "Finish Tutoring Feed Back";
                $message = "Your tutee $tuteeName already validated you request.";
                $stmt = $user_login->runQuery("INSERT INTO notifications (sender_id, receiver_id, title, message, status, date_sent) 
                    VALUES (:tutee_id, :tutor_id, :title, :message, 'unread', NOW())");
                $stmt->bindParam(":tutor_id", $tutor_id);
                $stmt->bindParam(":tutee_id", $tutee_id);
                $stmt->bindParam(":title", $title, PDO::PARAM_STR);
                $stmt->bindParam(":message", $message, PDO::PARAM_STR);
                $stmt->execute();
                // Send email to the tutee
                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'findtutee@gmail.com';
                    $mail->Password = 'tzbb qafz fhar ryzf';
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = 587;  
        
                    // Email to tutee
                    $mail->setFrom('findtutee@gmail.com', 'Tutee Finder');
                    $mail->addAddress($tuteeEmail, $tuteeName);
                    $mail->Subject = "Thank You for Your Participation";
                    $mail->Body = "Dear $tuteeName,\n\nThank you for participating in the tutoring sessions. We hope you found it beneficial. Best of luck in your continued learning journey!\n\nBest regards,\nTutee Finder";
        
                    $mail->send();
                } catch (Exception $e) {
                    error_log("Error sending email to tutee: {$mail->ErrorInfo}");
                }
        
                // Send email to the tutor
                try {
                    $mail->clearAddresses();
                    $mail->addAddress($tutorEmail, $tutorName);
                    $mail->Subject = "Session Completed with $tuteeName";
                    $mail->Body = "Dear $tutorName,\n\nThis is a notification that your session with $tuteeName has been marked as completed. Thank you for your dedication and support!\n\nBest regards,\nTutee Finder";
        
                    $mail->send();
                } catch (Exception $e) {
                    error_log("Error sending email to tutor: {$mail->ErrorInfo}");
                }
            }
        
            echo json_encode(['success' => true, 'message' => 'Session marked as completed and notifications sent.']);
            exit;        
        } elseif ($action === 'submit_comment') {
            $data = $requestData['data'] ?? null;
            if (!$data) {
                echo json_encode(['success' => false, 'message' => 'No data received.']);
                exit;
            }
            $tutor_id = $data['tutor_id'] ?? null;
            $answers = $data['answers'] ?? null;
            $comment = $data['comment'] ?? null;

            // New code: Get the tutor's name
            $tutor_name = isset($tutorNames[$tutor_id]) ? $tutorNames[$tutor_id] : 'Unknown Tutor'; // Get the tutor's name
            $tutee_name = "$firstname $lastname"; // Get the tutee's name

            if (!$tutor_id || !$answers || !$comment) {
                echo json_encode(['success' => false, 'message' => 'Missing required fields.']);
                exit;
            }
        
            // Define the questions
            $questions = [
                "Q1. Respect others' ideas and opinions",
                "Q2. Observes proprietary and good taste in language.",
                "Q3. Establishes good rapport with Tutee.",
                "Q4. Observes appropriate grooming.",
                "Q5. Performs tasks cheerfully and willingly.",
                "Q6. Comes to service regularly and on time.",
                "Q7. Shows interest in teaching.",
                "Q8. Uses resources wisely."
            ];
        
            // Initialize TCPDF and create the PDF
            $pdf = new TCPDF();
            $pdf->AddPage();
            $pdf->SetFont('helvetica', 'B', 14);
            $pdf->Cell(0, 10, "Tutor Rating", 0, 1, 'C');
        
            // Add the legend
            $pdf->SetFont('helvetica', '', 12);
                $legend = "Tutor will be assessed through the following scale:\n\n" .
                        "5 = Excellent. Far above and beyond the expected performance\n" .
                        "4 = Very Good. Above the expected performance\n" .
                        "3 = Good. Satisfactory expected level of performance\n" .
                        "2 = Fair. Slightly below expected level of performance\n" .
                        "1 = Poor. Below expected level of performance\n\n";
            $pdf->MultiCell(0, 10, $legend, 0, 'L', 0, 1);
        
            // Add names of the Tutee and Tutor
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->Cell(0, 10, "Tutee Name: $tutee_name", 0, 1);
            $pdf->Cell(0, 10, "Tutor Name: $tutor_name", 0, 1);
        
            // Add table header for questions and answers
            $pdf->Ln(5); // Add some spacing
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->Cell(120, 10, "Question", 1, 0, 'C');
            $pdf->Cell(30, 10, "Rating", 1, 1, 'C');
        
            // Add each question and its corresponding answer to the table
            $pdf->SetFont('helvetica', '', 12);
            foreach ($questions as $index => $question) {
                $answer = $answers[$index] ?? 'N/A'; // Fallback to 'N/A' if answer is missing
                $pdf->Cell(120, 10, $question, 1, 0, 'L');
                $pdf->Cell(30, 10, $answer, 1, 1, 'C');
            }
        
            // Add the comment section
            $pdf->Ln(5); // Add spacing before comments
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->Cell(0, 10, "Comment:", 0, 1);
            $pdf->SetFont('helvetica', '', 12);
            $pdf->MultiCell(0, 10, $comment, 0, 'L');
        
            // Output PDF to a string
            $pdfContent = $pdf->Output('', 'S'); // 'S' returns the PDF as a string
        
            // Validate PDF content
            if ($pdfContent === false || empty($pdfContent)) {
                throw new Exception('Failed to generate PDF.');
            }
        
            // Insert the PDF into the database
            $stmt = $user_login->runQuery("
                INSERT INTO tutor_ratings (tutee_id, tutor_id, rating, comment, pdf_content)
                VALUES (:tutee_id, :tutor_id, :rating, :comment, :pdf_content)
            ");
            $stmt->bindParam(':tutee_id', $data['tutee_id']);
            $stmt->bindParam(':tutor_id', $tutor_id);
            $stmt->bindParam(':rating', ($answers)); // Store answers as JSON
            $stmt->bindParam(':comment', $comment);
            $stmt->bindParam(':pdf_content', $pdfContent, PDO::PARAM_LOB);

            // Check for execution success
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Rating and PDF saved successfully.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to save rating to database.']);
            }
            exit;        
        }
    } catch (PDOException $ex) {
        echo json_encode(['success' => false, 'message' => $ex->getMessage()]);
        exit;
    }
}

function removeTutor($tutor_id, $tutee_id, $removal_reason) {
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
                <h3>Your Tutee has removed you from being his/her Tutor.</h3>
                <p>Reason: $removal_reason.</p>
            ";

            $mail->send();
        // Update the request status to removed
        $stmt = $user_login->runQuery("UPDATE requests SET status = 'removed' WHERE tutor_id = :tutor_id AND tutee_id = :tutee_id");
        $stmt->bindParam(":tutor_id", $tutor_id);
        $stmt->bindParam(":tutee_id", $tutee_id);
        $stmt->execute();

        // Insert a notification for the tutor about the removal
        $notificationStmt = $user_login->runQuery("INSERT INTO notifications (sender_id, receiver_id, title, message, status) 
                                                  VALUES (:sender_id, :receiver_id, 'Your Tutee has removed you from being his/her Tutor.', 'Reason: ' :message, 'unread')");
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

        if (removeTutor($tutor_id, $tutee_id, $removal_reason)) {
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
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="tutee.css">
    <link rel="stylesheet" href="notif.css">
    <link rel="stylesheet" href="progress.css">
    <link rel="stylesheet" href="spinner.css">
    <link rel="icon" href="../assets/TuteeFindLogo.png" type="image/png">  
    <title>Progress</title>
</head>
<form>
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
                    <li class="nav-link navbar-active" data-bs-toggle="tooltip" data-bs-placement="right" title="Progress">
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
        <div class="container-lg px-3" style="padding-top: 16px; padding-bottom: 10px;">
            <div class="career-form headings d-flex justify-content-center mt-3">
                <div class="row">
                    <div class="card1">Progress</div>
                </div>
            </div>
        </div>
        <?php foreach ($tutors as $tutor): ?>
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
                                <div id="collapse<?php echo htmlspecialchars($tutor['id']); ?>" class="collapse show" aria-labelledby="heading<?php echo htmlspecialchars($tutor['id']); ?>" data-parent="#accordion">
                                    <div class="card-body">
                                        <h5>Weekly Sessions</h5>
                                        <div class="table-responsive">
                                            <table class="table table-bordered">
                                                <thead>
                                                    <tr class="text-center">
                                                        <th>Week</th>
                                                        <th>Description</th>
                                                        <th>Date</th>
                                                        <th>File</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="file-upload-list-<?php echo htmlspecialchars($tutor['id']); ?>">
                                                    <?php
                                                    // Get the progress data for this tutor
                                                    $weeks = $progressData[$tutor['id']] ?? []; // Ensure weeks is an array or fallback to empty array

                                                    if (!empty($weeks)): 
                                                        foreach ($weeks as $week): ?>
                                                        <tr>
                                                            <td>
                                                                <input type="checkbox" class="form-check-input checkbox" id="checkbox<?php echo htmlspecialchars($tutor['id']); ?>-<?php echo $week['week_number']; ?>" checked disabled>
                                                                Week <?php echo htmlspecialchars($week['week_number']); ?>
                                                            </td>
                                                            <td>
                                                                <textarea class="form-control description-input custom-tb" id="scrollableInput<?php echo htmlspecialchars($tutor['id']); ?>-<?php echo $week['week_number']; ?>" placeholder="No description" data-week-number="<?php echo htmlspecialchars($week['week_number']); ?>" data-tutee-id="<?php echo htmlspecialchars($tutor['id']); ?>" disabled><?php echo htmlspecialchars($week['description']); ?></textarea>
                                                            </td>
                                                            <td>
                                                                <textarea class="form-control description-input custom-tb" id="scrollableInput<?php echo htmlspecialchars($tutor['id']); ?>-<?php echo $week['week_number']; ?>" placeholder="No description" data-week-number="<?php echo htmlspecialchars($week['week_number']); ?>" data-tutee-id="<?php echo htmlspecialchars($tutor['id']); ?>" disabled><?php echo htmlspecialchars($week['date']); ?></textarea>
                                                            </td>
                                                            <td class="text-center">
                                                                <?php if (!empty($week['uploaded_files'])): ?>
                                                                    <a href="<?php echo htmlspecialchars($week['uploaded_files']); ?>" target="_blank" class="btn btn-secondary">
                                                                        View File
                                                                    </a>
                                                                <?php else: ?>
                                                                    <input type="file" id="file-upload<?php echo htmlspecialchars($tutor['id']); ?>-<?php echo $week['week_number']; ?>" class="file-upload" style="display: none;" accept="*/*" data-tutee-id="<?php echo htmlspecialchars($tutor['id']); ?>" data-week-number="<?php echo htmlspecialchars($week['week_number']); ?>">
                                                                    <button id="custom-file-upload<?php echo htmlspecialchars($tutor['id']); ?>-<?php echo $week['week_number']; ?>" class="btn btn-secondary" title="Upload Assessment and Session photo">
                                                                        <i class='bx bx-upload'></i>
                                                                    </button>
                                                                <?php endif; ?>
                                                            </td>
                                                        </tr>
                                                        <?php 
                                                        endforeach;
                                                    else: ?>
                                                        <tr><td colspan="3">No progress yet</td></tr>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <!-- Confirm Finish Button -->
                                <div class="d-flex justify-content-center align-items-center mb-3" id="buttonContainer">
                                    <button type="button" class="btn btn-danger m-2 finish-btn" id="confirmFinishBtn" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#confirmationModal" 
                                        data-tutor-id="<?php echo $tutor['id']; ?>" 
                                        data-tutee-id="<?php echo htmlspecialchars($tutee_id); ?>" 
                                        <?php echo $statusData[$tutor['id']]['status'] === 'requested' ? '' : 'disabled'; ?>>
                                        Confirm Finish
                                    </button>
                                    <button type="button" class="btn btn-primary my-2" 
                                            id="rateTutorBtn-<?php echo $tutor['id']; ?>" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#rateTutorModal-<?php echo $tutor['id']; ?>"
                                            <?php echo $statusData[$tutor['id']]['status'] === 'completed' && !$statusData[$tutor['id']]['rating_exists'] ? '' : 'disabled'; ?>>
                                        Rate Tutor
                                    </button>
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


        <?php endforeach; ?>


            
    <?php if (empty($tutors)): ?>
        <div class="container-lg px-3 " style="padding-bottom: 10px;">
            <div class="career-form headings d-flex justify-content-center mt-3 update">
                <div class="row">
                    <div class="card1 d-flex flex-column justify-content-center align-items-center">
                        <img src="../assets/tutee-blankplaceholder-white.png" alt="Nothing to see here" style="width: 300px; height: 300px;">
                        <h5 class="opacity">No current tutors</h5>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
                        

    <!-- Confirmation Modal -->
    <div class="modal fade" id="confirmationModal" tabindex="-1" role="dialog" aria-labelledby="confirmationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmationModalLabel">Confirm Finish Session</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to confirm the finish for this session?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal" id="confirmFinishBtnModal">Confirm</button>
                </div>
            </div>
        </div>
    </div>

<form id="rateTutorForm" method="POST">
    <div class="modal fade" id="rateTutorModal-<?php echo $tutor['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="rateTutorModal-<?php echo $tutor['id']; ?>" aria-hidden="true" data-bs-backdrop="true" data-keyboard="false">
    <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header feedback-header">
                    <h5 class="modal-title" id="rateTutorModal">Rating</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" data-bs-backdrop="dismiss" aria-label="Close" id="closeModalBtn"></button>
                </div> 
                <div class="modal-body">
                    <div id="pagination-container">
                        <!-- Page 1 -->
                        <div class="progress">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%" aria-valuenow="10" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <div class="page" id="page-1">
                            <h2>Rate your Tutor</h2>
                            <p class="text-start mb-4">
                                Tutor will be assessed through the following scale:<br><br>
                                5 = Excellent. Far above and beyond the expected performance<br>
                                4 = Very Good. Above the expected performance<br>
                                3 = Good. Satisfactory expected level of performance<br>
                                2 = Fair. Slightly below expected level of performance<br>
                                1 = Poor. Below expected level of performance<br><br>
                            </p>
                            <h5 class="text-start mb-4">Q1. Respect others' ideas and opinions</h5>
                                <div class="likert-scale">
                                    <div class="row justify-content-center">
                                        <div class="col-auto">
                                            <button type="button" class="likert-btn" data-value="5">5</button>
                                        </div>
                                        <div class="col-auto">
                                            <button type="button" class="likert-btn" data-value="4">4</button>
                                        </div>
                                        <div class="col-auto">
                                            <button type="button" class="likert-btn" data-value="3">3</button>
                                        </div>
                                        <div class="col-auto">
                                            <button type="button" class="likert-btn" data-value="2">2</button>
                                        </div>
                                        <div class="col-auto">
                                            <button type="button" class="likert-btn" data-value="1">1</button>
                                        </div>
                                    </div>
                                </div>  
                                <div class="d-flex flex-column align-items-center mt-4">
                                    <button type="button" class="btn btn-primary" id="nextBtn-1" disabled>Next</button>
                                </div>
                                </div>
                                <!-- Page 2 -->
                                <div class="page" id="page-2">
                                    <h2>Rate your Tutor</h2>
                                    <p class="text-start mb-4">
                                        Tutor will be assessed through the following scale:<br><br>
                                        5 = Excellent. Far above and beyond the expected performance<br>
                                        4 = Very Good. Above the expected performance<br>
                                        3 = Good. Satisfactory expected level of performance<br>
                                        2 = Fair. Slightly below expected level of performance<br>
                                        1 = Poor. Below expected level of performance<br><br>
                                    </p>
                                    <h5 class="text-start mb-4">Q2. Observes proprietary and good taste in language.</h5>
                                    <div class="likert-scale">
                                        <div class="row justify-content-center">
                                            <div class="col-auto">
                                                <button type="button" class="likert-btn" data-value="5">5</button>
                                            </div>
                                            <div class="col-auto">
                                                <button type="button" class="likert-btn" data-value="4">4</button>
                                            </div>
                                            <div class="col-auto">
                                                <button type="button" class="likert-btn" data-value="3">3</button>
                                            </div>
                                            <div class="col-auto">
                                                <button type="button" class="likert-btn" data-value="2">2</button>
                                            </div>
                                            <div class="col-auto">
                                                <button type="button" class="likert-btn" data-value="1">1</button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row justify-content-center mt-4">
                                        <div class="col-auto">
                                            <button type="button" class="btn btn-secondary" id="prevBtn-2">Back</button>
                                        </div>
                                        <div class="col-auto">
                                            <button type="button" class="btn btn-primary" id="nextBtn-2" disabled>Next</button>
                                        </div>
                                    </div>
                                </div>
                                <!-- Page 3 -->
                                <div class="page" id="page-3">
                                    <h2>Rate your Tutor</h2>
                                    <p class="text-start mb-4">
                                        Tutor will be assessed through the following scale:<br><br>
                                        5 = Excellent. Far above and beyond the expected performance<br>
                                        4 = Very Good. Above the expected performance<br>
                                        3 = Good. Satisfactory expected level of performance<br>
                                        2 = Fair. Slightly below expected level of performance<br>
                                        1 = Poor. Below expected level of performance<br><br>
                                    </p>
                                    <h5 class="text-start mb-4">Q3. Establishes good rapport with Tutee.</h5>
                                    <div class="likert-scale">
                                        <div class="row justify-content-center">
                                            <div class="col-auto">
                                                <button type="button" class="likert-btn" data-value="5">5</button>
                                            </div>
                                            <div class="col-auto">
                                                <button type="button" class="likert-btn" data-value="4">4</button>
                                            </div>
                                            <div class="col-auto">
                                                <button type="button" class="likert-btn" data-value="3">3</button>
                                            </div>
                                            <div class="col-auto">
                                                <button type="button" class="likert-btn" data-value="2">2</button>
                                            </div>
                                            <div class="col-auto">
                                                <button type="button" class="likert-btn" data-value="1">1</button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row justify-content-center mt-4">
                                        <div class="col-auto">
                                            <button type="button" class="btn btn-secondary" id="prevBtn-3">Back</button>
                                        </div>
                                        <div class="col-auto">
                                            <button type="button" class="btn btn-primary" id="nextBtn-3" disabled>Next</button>
                                        </div>
                                    </div>
                                </div>
                                <!-- Page 4 -->
                                <div class="page" id="page-4">
                                    <h2>Rate your Tutor</h2>
                                    <p class="text-start mb-4">
                                        Tutor will be assessed through the following scale:<br><br>
                                        5 = Excellent. Far above and beyond the expected performance<br>
                                        4 = Very Good. Above the expected performance<br>
                                        3 = Good. Satisfactory expected level of performance<br>
                                        2 = Fair. Slightly below expected level of performance<br>
                                        1 = Poor. Below expected level of performance<br><br>
                                    </p>
                                    <h5 class="text-start mb-4">Q4. Observes appropriate grooming.</h5>
                                    <div class="likert-scale">
                                        <div class="row justify-content-center">
                                            <div class="col-auto">
                                                <button type="button" class="likert-btn" data-value="5">5</button>
                                            </div>
                                            <div class="col-auto">
                                                <button type="button" class="likert-btn" data-value="4">4</button>
                                            </div>
                                            <div class="col-auto">
                                                <button type="button" class="likert-btn" data-value="3">3</button>
                                            </div>
                                            <div class="col-auto">
                                                <button type="button" class="likert-btn" data-value="2">2</button>
                                            </div>
                                            <div class="col-auto">
                                                <button type="button" class="likert-btn" data-value="1">1</button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row justify-content-center mt-4">
                                        <div class="col-auto">
                                            <button type="button" class="btn btn-secondary" id="prevBtn-4">Back</button>
                                        </div>
                                        <div class="col-auto">
                                            <button type="button" class="btn btn-primary" id="nextBtn-4" disabled>Next</button>
                                        </div>
                                    </div>
                                </div>
                                <!-- Page 5 -->
                                <div class="page" id="page-5">
                                    <h2>Rate your Tutor</h2>
                                    <p class="text-start mb-4">
                                        Tutor will be assessed through the following scale:<br><br>
                                        5 = Excellent. Far above and beyond the expected performance<br>
                                        4 = Very Good. Above the expected performance<br>
                                        3 = Good. Satisfactory expected level of performance<br>
                                        2 = Fair. Slightly below expected level of performance<br>
                                        1 = Poor. Below expected level of performance<br><br>
                                    </p>
                                    <h5 class="text-start mb-4">Q5. Performs tasks cheerfully and willingly.</h5>
                                    <div class="likert-scale">
                                        <div class="row justify-content-center">
                                            <div class="col-auto">
                                                <button type="button" class="likert-btn" data-value="5">5</button>
                                            </div>
                                            <div class="col-auto">
                                                <button type="button" class="likert-btn" data-value="4">4</button>
                                            </div>
                                            <div class="col-auto">
                                                <button type="button" class="likert-btn" data-value="3">3</button>
                                            </div>
                                            <div class="col-auto">
                                                <button type="button" class="likert-btn" data-value="2">2</button>
                                            </div>
                                            <div class="col-auto">
                                                <button type="button" class="likert-btn" data-value="1">1</button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row justify-content-center mt-4">
                                        <div class="col-auto">
                                            <button type="button" class="btn btn-secondary" id="prevBtn-5">Back</button>
                                        </div>
                                        <div class="col-auto">
                                            <button type="button" class="btn btn-primary" id="nextBtn-5" disabled>Next</button>
                                        </div>
                                    </div>
                                </div>
                                <!-- Page 6 -->
                                <div class="page" id="page-6">
                                    <h2>Rate your Tutor</h2>
                                    <p class="text-start mb-4">
                                        Tutor will be assessed through the following scale:<br><br>
                                        5 = Excellent. Far above and beyond the expected performance<br>
                                        4 = Very Good. Above the expected performance<br>
                                        3 = Good. Satisfactory expected level of performance<br>
                                        2 = Fair. Slightly below expected level of performance<br>
                                        1 = Poor. Below expected level of performance<br><br>
                                    </p>
                                    <h5 class="text-start mb-4">Q6. Comes to service regularly and on time.</h5>
                                    <div class="likert-scale">
                                        <div class="row justify-content-center">
                                            <div class="col-auto">
                                                <button type="button" class="likert-btn" data-value="5">5</button>
                                            </div>
                                            <div class="col-auto">
                                                <button type="button" class="likert-btn" data-value="4">4</button>
                                            </div>
                                            <div class="col-auto">
                                                <button type="button" class="likert-btn" data-value="3">3</button>
                                            </div>
                                            <div class="col-auto">
                                                <button type="button" class="likert-btn" data-value="2">2</button>
                                            </div>
                                            <div class="col-auto">
                                                <button type="button" class="likert-btn" data-value="1">1</button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row justify-content-center mt-4">
                                        <div class="col-auto">
                                            <button type="button" class="btn btn-secondary" id="prevBtn-6">Back</button>
                                        </div>
                                        <div class="col-auto">
                                            <button type="button" class="btn btn-primary" id="nextBtn-6" disabled>Next</button>
                                        </div>
                                    </div>
                                </div>
                                <!-- Page 7 -->
                                <div class="page" id="page-7">
                                    <h2>Rate your Tutor</h2>
                                    <p class="text-start mb-4">
                                        Tutor will be assessed through the following scale:<br><br>
                                        5 = Excellent. Far above and beyond the expected performance<br>
                                        4 = Very Good. Above the expected performance<br>
                                        3 = Good. Satisfactory expected level of performance<br>
                                        2 = Fair. Slightly below expected level of performance<br>
                                        1 = Poor. Below expected level of performance<br><br>
                                    </p>
                                    <h5 class="text-start mb-4">Q7. Shows interest in teaching.</h5>
                                    <div class="likert-scale">
                                        <div class="row justify-content-center">
                                            <div class="col-auto">
                                                <button type="button" class="likert-btn" data-value="5">5</button>
                                            </div>
                                            <div class="col-auto">
                                                <button type="button" class="likert-btn" data-value="4">4</button>
                                            </div>
                                            <div class="col-auto">
                                                <button type="button" class="likert-btn" data-value="3">3</button>
                                            </div>
                                            <div class="col-auto">
                                                <button type="button" class="likert-btn" data-value="2">2</button>
                                            </div>
                                            <div class="col-auto">
                                                <button type="button" class="likert-btn" data-value="1">1</button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row justify-content-center mt-4">
                                        <div class="col-auto">
                                            <button type="button" class="btn btn-secondary" id="prevBtn-7">Back</button>
                                        </div>
                                        <div class="col-auto">
                                            <button type="button" class="btn btn-primary" id="nextBtn-7" disabled>Next</button>
                                        </div>
                                    </div>
                                </div>
                                <!-- Page 8 -->
                                <div class="page" id="page-8">
                                    <h2>Rate your Tutor</h2>
                                    <p class="text-start mb-4">
                                        Tutor will be assessed through the following scale:<br><br>
                                        5 = Excellent. Far above and beyond the expected performance<br>
                                        4 = Very Good. Above the expected performance<br>
                                        3 = Good. Satisfactory expected level of performance<br>
                                        2 = Fair. Slightly below expected level of performance<br>
                                        1 = Poor. Below expected level of performance<br><br>
                                    </p>
                                    <h5 class="text-start mb-4">Q8. Uses resources wisely.</h5>
                                    <div class="likert-scale">
                                        <div class="row justify-content-center">
                                            <div class="col-auto">
                                                <button type="button" class="likert-btn" data-value="5">5</button>
                                            </div>
                                            <div class="col-auto">
                                                <button type="button" class="likert-btn" data-value="4">4</button>
                                            </div>
                                            <div class="col-auto">
                                                <button type="button" class="likert-btn" data-value="3">3</button>
                                            </div>
                                            <div class="col-auto">
                                                <button type="button" class="likert-btn" data-value="2">2</button>
                                            </div>
                                            <div class="col-auto">
                                                <button type="button" class="likert-btn" data-value="1">1</button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row justify-content-center mt-4">
                                        <div class="col-auto">
                                            <button type="button" class="btn btn-secondary" id="prevBtn-8">Back</button>
                                        </div>
                                        <div class="col-auto">
                                            <button type="button" class="btn btn-primary" id="nextBtn-8" disabled>Next</button>
                                        </div>
                                    </div>
                                </div>
                                <!-- Page 9 -->
                                <div class="page" id="page-9" style="display: none;">
                                    <h5 class="mt-4">Comments</h5>
                                    <div class="form-group">
                                        <textarea class="form-control mb-5" id="commentText-1" rows="5" placeholder="Enter your comment here"></textarea>
                                    </div>
                                    <div class="d-flex justify-content-between mt-4">
                                        <button type="button" class="btn btn-secondary" id="prevBtn-9">Back</button>
                                        <button type="button" class="btn btn-primary" id="submitCommentButton" data-bs-dismiss="modal">Submit Comment</button>
                                    </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

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
                    <div class="modal-body d-flex justify-content-center align-items-center" id="modalBody">
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

    <!-- Thank You Modal -->
    <div class="modal fade" id="thankYouModal" tabindex="-1" aria-labelledby="thankYouModal" aria-hidden="true">
        <div>
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="thankYouModal">Thank You!</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Thank you for your comment!</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="location.reload()">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Thank You Modal -->
    <div class="modal fade" id="thankYouModal1" tabindex="-1" aria-labelledby="thankYouModal1" aria-hidden="true">
        <div>
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="thankYouModal1">Thank You!</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Thank you for your validation!</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="location.reload()">Close</button>
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

        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.min.js"></script>
        <script src="progress.js"></script>
        <script src="tutee_sidebar.js"></script>
        <script src="notif.js"></script>
        <script src="tutee.js"></script>

        <script>
           document.addEventListener('DOMContentLoaded', function () {
    // Handle confirmation of finishing the session
    document.getElementById('confirmFinishBtnModal').addEventListener('click', function () {
        // Retrieve tutor_id and tutee_id
        const tutorId = document.getElementById('confirmFinishBtn').getAttribute('data-tutor-id');
        const tuteeId = document.getElementById('confirmFinishBtn').getAttribute('data-tutee-id');

        // Check if both IDs are present
        if (!tutorId || !tuteeId) {
            alert("Tutor or Tutee ID is missing.");
            return;
        }
        
        showSpinner();
        // Send AJAX request to confirm session finish
        fetch('', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'confirmFinishBtnModal', tutor_id: tutorId, tutee_id: tuteeId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
                // Enable the "Rate Tutor" button
                const rateTutorButton = document.querySelector(`#rateTutorBtn[data-tutor-id="${tutorId}"]`);
                if (rateTutorButton) {
                    rateTutorButton.classList.remove('disabled');
                    rateTutorButton.disabled = false; // Ensure button is enabled
                }

            } else {
                alert("Error: " + data.message); // Show error message
            }
        })
        .catch(error => console.error("AJAX request failed:", error));
    });
    document.querySelectorAll(`.likert-scale .likert-btn.active`).forEach(button => {
    ratingData.answers.push(button.getAttribute('data-value'));
});

document.querySelectorAll('.likert-btn').forEach(button => {
    button.addEventListener('click', function() {
        const value = this.getAttribute('data-value');
        const page = this.closest('.page');
        page.querySelector('.likert-btn').classList.remove('selected'); // Clear previous selection
        this.classList.add('selected'); // Highlight selected button

        // Enable the next button
        const nextButton = page.querySelector('.btn-primary[id^="nextBtn-"]');
        if (nextButton) {
            nextButton.disabled = false;
        }
    });
});

// Manage page navigation
document.querySelectorAll('[id^="nextBtn-"], [id^="prevBtn-"]').forEach(button => {
    button.addEventListener('click', function() {
        const currentPage = this.closest('.page');
        const currentPageId = currentPage.id;

        if (this.id.startsWith('nextBtn-')) {
            currentPage.style.display = 'none';
            const nextPage = document.getElementById(`page-${parseInt(currentPageId.split('-')[1]) + 1}`);
            if (nextPage) {
                nextPage.style.display = 'block';
            }
        } else {
            currentPage.style.display = 'none';
            const prevPage = document.getElementById(`page-${parseInt(currentPageId.split('-')[1]) - 1}`);
            if (prevPage) {
                prevPage.style.display = 'block';
            }
        }
    });
});

document.getElementById('submitCommentButton').addEventListener('click', function() {
    const answers = [...document.querySelectorAll('.likert-btn.selected')].map(button => button.getAttribute('data-value'));
    const comment = document.getElementById('commentText-1').value;
    const tutorId = <?php echo $tutor['id']; ?>;
    const tuteeId = <?php echo $tutee_id; ?>;

    const data = {
        action: 'submit_comment',
        data: {
            tutor_id: tutorId,
            tutee_id: tuteeId,
            answers: answers,
            comment: comment
        }
    };
    const thankYouModal = new bootstrap.Modal(document.getElementById('thankYouModal'));
    
            // Show the thank you modal after the rate tutor modal has hidden
            thankYouModal.show();
    fetch('', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Hide the rate tutor modal and then show the thank you modal
            const rateTutorModal = document.getElementById(`rateTutorModal-<?php echo $tutor['id']; ?>`);
            // Hide the rate tutor modal
            const bootstrapModal = bootstrap.Modal.getInstance(rateTutorModal);
            bootstrapModal.hide();
            // location.reload()
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => console.error('Error:', error));
});




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

    // Handling the rate tutor button's display logic
    document.getElementById('rateTutorBtn').addEventListener('click', function() {
        const targetModal = this.getAttribute('data-bs-target');
        const modalElement = document.querySelector(targetModal);
        const bootstrapModal = new bootstrap.Modal(modalElement);
        bootstrapModal.show();
    });

    // Handle modal close logic
    document.querySelectorAll('.closeModalBtn').forEach(button => {
        button.addEventListener('click', function () {
            const modalElement = this.closest('.modal');
            const bootstrapModal = bootstrap.Modal.getInstance(modalElement);
            bootstrapModal.hide();
            // Reset body style and ensure modal backdrop is removed
            document.body.style.overflow = 'auto';
            document.body.style.paddingRight = '0';
            document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
        });
    });

    document.getElementById('confirmationModalconfirmBtn').addEventListener('click', function() {
    var rateTutorButton = document.getElementById('rateTutorBtn'); // Make sure to target the right button
    rateTutorButton.classList.remove('disabled');
    rateTutorButton.disabled = false; // Enable the button

    // Hide the "Confirm Finish" button (if needed)
    var confirmFinishButton = document.getElementById('confirmFinishBtn');
    confirmFinishButton.disabled = true;
});

// Disable the button initially based on status
document.addEventListener('DOMContentLoaded', function() {
    const tutorIds = <?php echo json_encode(array_column($tutors, 'id')); ?>; // Assuming you have a way to get all tutor IDs

    tutorIds.forEach(tutorId => {
        const rateTutorButton = document.getElementById(`rateTutorBtn-${tutorId}`);
        if (statusData[tutorId] !== 'completed') {
            rateTutorButton.disabled = true; // Disable if not completed
        }
    });
});
});
        </script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Handle removal of tutor logic (Button click)
    const removeTutorButtons = document.querySelectorAll('#removeTutorBtn');
    removeTutorButtons.forEach(button => {
        button.addEventListener('click', function() {
            const tutorId = this.getAttribute('data-tutor-id');
            const tuteeId = this.getAttribute('data-tutee-id');

            // Set tutor_id and tutee_id in the modal form
            document.getElementById('modalTutorId').value = tutorId;
            document.getElementById('tutee_id').value = tuteeId;
        });
    });

    // Handle form submission for removing a tutor
    document.getElementById('removeTuteeForm').addEventListener('submit', function (event) {
        const removalReason = document.getElementById('removal_reason').value.trim();

        // Check if the reason is provided
        if (!removalReason) {
            event.preventDefault(); // Prevent form submission
            document.getElementById('reasonError').style.display = 'block'; // Show error message
        } else {
            document.getElementById('reasonError').style.display = 'none'; // Hide error message
            
            // Send AJAX request to remove tutor
            const tutorId = document.getElementById('modalTutorId').value;
            const tuteeId = document.getElementById('tutee_id').value;

            fetch('', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'remove_tutor',
                    tutor_id: tutorId,
                    tutee_id: tuteeId,
                    removal_reason: removalReason
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload(); // Refresh the page to reflect the changes
                } else {
                    alert("Error: " + data.message); // Show error message
                }
            })
            .catch(error => console.error("AJAX request failed:", error));
        }
    });
});
</script>
    </body>
</html>