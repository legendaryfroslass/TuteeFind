<?php
error_reporting(E_ALL);
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

if ($userData) {
    $firstname = $userData['firstname'];
    $lastname = $userData['lastname'];
    $tutor_id = $userData['id'];
    $imagePath = !empty($userData['photo']) ? $userData['photo'] : '../assets/TuteeFindLogoName.jpg';

    // Modified query to include tutees with completed weeks
    $tuteeStmt = $user_login->runQuery("
        SELECT tutee.id, tutee.firstname, tutee.lastname, summary.completed_weeks, summary.registered_weeks
        FROM tutee
        INNER JOIN requests ON tutee.id = requests.tutee_id
        LEFT JOIN tutee_summary AS summary ON tutee.id = summary.tutee_id
        WHERE (requests.tutor_id = :tutor_id AND requests.status = 'accepted')
        OR summary.completed_weeks > 0
    ");
    $tuteeStmt->bindParam(":tutor_id", $tutor_id);
    $tuteeStmt->execute();
    $tutees = $tuteeStmt->fetchAll(PDO::FETCH_ASSOC);

    $progressData = [];
    foreach ($tutees as $tutee) {
        $progressStmt = $user_login->runQuery("
            SELECT *
            FROM tutee_progress 
            WHERE tutee_id = :tutee_id AND tutor_id = :tutor_id
            ORDER BY week_number ASC
        ");
        $progressStmt->bindParam(":tutee_id", $tutee['id']);
        $progressStmt->bindParam(":tutor_id", $tutor_id);
        $progressStmt->execute();
        $progressData[$tutee['id']] = $progressStmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['action'])) {
            if ($_POST['action'] === 'add_week') {
            // Add a new week if description is saved successfully
            $stmt = $user_login->runQuery("
                INSERT INTO tutee_progress (tutee_id, week_number, uploaded_files, tutor_id, description, rendered_hours, location, subject) 
                VALUES (:tutee_id, :week_number, '', :tutor_id, :description, :rendered_hours, :location, :subject)
            ");
            $stmt->bindParam(':tutee_id', $_POST['tutee_id']);
            $stmt->bindParam(':week_number', $_POST['week_number']);
            $stmt->bindParam(':tutor_id', $tutor_id);
            $stmt->bindParam(':description', $_POST['description']);
            $stmt->bindParam(':rendered_hours', $_POST['rendered_hours']);
            $stmt->bindParam(':location', $_POST['location']);
            $stmt->bindParam(':subject', $_POST['subject']);
            $stmt->execute();

            $summaryStmt = $user_login->runQuery("
                INSERT INTO tutee_summary (tutee_id, tutor_id, registered_weeks) 
                VALUES (:tutee_id, :tutor_id, 1)
                ON DUPLICATE KEY UPDATE registered_weeks = registered_weeks + 1
            ");
            $summaryStmt->bindParam(':tutee_id', $_POST['tutee_id']);
            $summaryStmt->bindParam(':tutor_id', $tutor_id);
            $summaryStmt->execute();

            // Handle file upload if a file is uploaded
            if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['file'];
                $uploadDir = '../uploads/';
                $filename = basename($file['name']);
                $targetPath = $uploadDir . $filename;

                if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                    // Update the tutee_progress table with the file path
                    $updateStmt = $user_login->runQuery("
                        UPDATE tutee_progress 
                        SET uploaded_files = :uploaded_files, date = NOW()
                        WHERE tutee_id = :tutee_id AND week_number = :week_number
                    ");
                    $updateStmt->bindParam(':uploaded_files', $targetPath);
                    $updateStmt->bindParam(':tutee_id', $_POST['tutee_id']);
                    $updateStmt->bindParam(':week_number', $_POST['week_number']);
                    $updateStmt->execute();

                    // Update completed_weeks in tutee_summary table
                    $summaryStmt = $user_login->runQuery("
                        SELECT completed_weeks, registered_weeks 
                        FROM tutee_summary 
                        WHERE tutee_id = :tutee_id
                    ");
                    $summaryStmt->bindParam(':tutee_id', $_POST['tutee_id']);
                    $summaryStmt->execute();
                    $summaryData = $summaryStmt->fetch(PDO::FETCH_ASSOC);

                    if ($summaryData['completed_weeks'] !== $summaryData['registered_weeks']) {
                        $completedWeeksStmt = $user_login->runQuery("
                            UPDATE tutee_summary 
                            SET completed_weeks = completed_weeks + 1 
                            WHERE tutee_id = :tutee_id
                        ");
                        $completedWeeksStmt->bindParam(':tutee_id', $_POST['tutee_id']);
                        $completedWeeksStmt->execute();
                    }

                    echo json_encode(['success' => true, 'message' => 'Description, week, and file uploaded successfully.']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'File upload failed.']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'No file uploaded.']);
            }
        } elseif ($_POST['action'] === 'edit_week') {
                    $tutee_id = $_POST['tutee_id'];
                    $week_number = $_POST['week_number'];
                    $description = $_POST['description'];
                    $rendered_hours = $_POST['rendered_hours'];
                    $location = $_POST['location'];
                    $subject = $_POST['subject'];
                    $file_path = null;

                    // Handle file upload if provided
                    if (isset($_FILES['file-upload']) && $_FILES['file-upload']['error'] === UPLOAD_ERR_OK) {
                        $uploadDir = '../uploads/';
                        $filename = basename($_FILES['file-upload']['name']);
                        $file_path = $uploadDir . $filename;
                        move_uploaded_file($_FILES['file-upload']['tmp_name'], $file_path);
                    }

                    $stmt = $user_login->runQuery("
                        UPDATE tutee_progress 
                        SET week_number = :week_number, 
                            description = :description, 
                            rendered_hours = :rendered_hours, 
                            location = :location, 
                            subject = :subject, 
                            uploaded_files = COALESCE(:uploaded_files, uploaded_files)
                        WHERE tutee_id = :tutee_id AND week_number = :week_number
                    ");
                    $stmt->bindParam(':week_number', $week_number);
                    $stmt->bindParam(':description', $description);
                    $stmt->bindParam(':rendered_hours', $rendered_hours);
                    $stmt->bindParam(':location', $location);
                    $stmt->bindParam(':subject', $subject);
                    $stmt->bindParam(':uploaded_files', $file_path);
                    $stmt->bindParam(':tutee_id', $tutee_id);

                    if ($stmt->execute()) {
                        echo json_encode(['success' => true]);
                    } else {
                        echo json_encode(['success' => false, 'error' => 'Failed to update record.']);
                    }
                }
        elseif ($_POST['action'] === 'finish_session') {
            $tutor_id = $_POST['tutor_id'];
            $tutee_id = $_POST['tutee_id'];

            // Check if there is any progress for the tutee
            $checkProgressStmt = $user_login->runQuery("
                SELECT COUNT(*) as progress_count FROM tutee_progress 
                WHERE tutor_id = :tutor_id AND tutee_id = :tutee_id
            ");
            $checkProgressStmt->bindParam(':tutor_id', $tutor_id);
            $checkProgressStmt->bindParam(':tutee_id', $tutee_id);
            $checkProgressStmt->execute();
            $progressRow = $checkProgressStmt->fetch(PDO::FETCH_ASSOC);

            // If no progress exists, prevent finishing the session
            if ($progressRow['progress_count'] == 0) {
                echo json_encode(['success' => false, 'message' => 'No progress data found for this tutee. Please ensure progress is recorded before finishing the session.']);
                return;
            }

            // Check if a finish request has already been made
            $checkStmt = $user_login->runQuery("
                SELECT status FROM tutor_sessions 
                WHERE tutor_id = :tutor_id AND tutee_id = :tutee_id
            ");
            $checkStmt->bindParam(':tutor_id', $tutor_id);
            $checkStmt->bindParam(':tutee_id', $tutee_id);
            $checkStmt->execute();
            $existingRequest = $checkStmt->fetch(PDO::FETCH_ASSOC);

            if ($existingRequest && $existingRequest['status'] === 'requested') {
                echo json_encode(['success' => false, 'message' => 'A finish request has already been made for this tutee.']);
            } else {
                $stmt = $user_login->runQuery("
                    INSERT INTO tutor_sessions (tutor_id, tutee_id, status) 
                    VALUES (:tutor_id, :tutee_id, 'requested') 
                    ON DUPLICATE KEY UPDATE status = 'requested'
                ");
                $stmt->bindParam(':tutor_id', $tutor_id);
                $stmt->bindParam(':tutee_id', $tutee_id);
                $stmt->execute();
                echo json_encode(['success' => true]);
            }
        }
 elseif($_POST['action'] === 'get_session_status') {
                $checkStmt = $user_login->runQuery("SELECT status FROM tutor_sessions WHERE tutor_id = :tutor_id AND tutee_id = :tutee_id");
                $checkStmt->bindParam(':tutor_id', $tutor_id); // Add appropriate tutor ID if necessary
                $checkStmt->bindParam(':tutee_id', $_POST['tutee_id']);
                $checkStmt->execute();
                $sessionStatus = $checkStmt->fetch(PDO::FETCH_ASSOC);

                echo json_encode(['status' => $sessionStatus['status']]);
            }
            elseif ($_POST['action'] === 'add_event') {

                $event_name = $_POST['event_name'];
                $rendered_hours = $_POST['rendered_hours'];
                $description = $_POST['description'];

                // Handle file upload
                if (isset($_FILES['attached_file']) && $_FILES['attached_file']['error'] === UPLOAD_ERR_OK) {
                    $file_name = $_FILES['attached_file']['name'];
                    $file_tmp = $_FILES['attached_file']['tmp_name'];
                    $file_size = $_FILES['attached_file']['size'];
                    $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);

                    // Check file type (allow only image formats)
                    if (in_array($file_ext, ['jpg', 'jpeg', 'png'])) {
                        // Check file size (limit to 10MB)
                        if ($file_size <= 10485760) {
                            // Move the file to your upload directory
                            $upload_dir = '../uploads/events/';
                            $upload_file = $upload_dir . basename($file_name);

                            if (move_uploaded_file($file_tmp, $upload_file)) {
                                // Insert into database
                                $query = "INSERT INTO events (tutor_id, event_name, rendered_hours, description, attached_file) VALUES (:tutor_id, :event_name, :rendered_hours, :description, :attached_file)";
                                $stmt = $user_login->runQuery($query);
                                $stmt->bindParam(':tutor_id', $userData['id']); // Use the current tutor's ID
                                $stmt->bindParam(':event_name', $event_name);
                                $stmt->bindParam(':rendered_hours', $rendered_hours);
                                $stmt->bindParam(':description', $description);
                                $stmt->bindParam(':attached_file', $file_name);

                                if ($stmt->execute()) {
                                    echo json_encode(['success' => true]);
                                } else {
                                    echo json_encode(['success' => false, 'message' => 'Database insertion failed']);
                                }
                            } else {
                                echo json_encode(['success' => false, 'message' => 'File upload failed']);
                            }
                        } else {
                            echo json_encode(['success' => false, 'message' => 'File size exceeds 10MB']);
                        }
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPG, JPEG, PNG allowed']);
                    }
                } else {
                    echo json_encode(['success' => false, 'message' => 'File upload error']);
                }
            } elseif ($_POST['action'] === 'edit_event') {
                // Get the form data
                $event_id = $_POST['event_id'];
                $event_name = $_POST['event_name'];
                $rendered_hours = $_POST['rendered_hours'];
                $description = $_POST['description'];
            
                // Initialize file name variable (for the new file)
                $file_name = null;
            
                // Handle file upload if there is a new file
                if (isset($_FILES['attached_file']) && $_FILES['attached_file']['error'] === UPLOAD_ERR_OK) {
                    $file_name = $_FILES['attached_file']['name'];
                    $file_tmp = $_FILES['attached_file']['tmp_name'];
                    $file_size = $_FILES['attached_file']['size'];
                    $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
            
                    // Check file type (allow only image formats)
                    if (in_array($file_ext, ['jpg', 'jpeg', 'png', 'pdf'])) {
                        // Check file size (limit to 10MB)
                        if ($file_size <= 10485760) {
                            // Set upload directory
                            $upload_dir = '../uploads/events/';
                            $upload_file = $upload_dir . basename($file_name);
            
                            // Move the file to the upload directory
                            if (move_uploaded_file($file_tmp, $upload_file)) {
                                // File uploaded successfully
                            } else {
                                echo json_encode(['success' => false, 'message' => 'File upload failed']);
                                exit;
                            }
                        } else {
                            echo json_encode(['success' => false, 'message' => 'File size exceeds 10MB']);
                            exit;
                        }
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPG, JPEG, PNG, and PDF allowed']);
                        exit;
                    }
                }
            
                // Update the event in the database
                try {
                    // Prepare the SQL query to update event details
                    $query = "UPDATE events 
                              SET event_name = :event_name, 
                                  rendered_hours = :rendered_hours, 
                                  description = :description, 
                                  created_at = CURRENT_TIMESTAMP";  // Add this line to update created_at
            
                    // If there is a new file, include it in the update query
                    if ($file_name) {
                        $query .= ", attached_file = :attached_file";
                    }
            
                    $query .= " WHERE id = :event_id AND tutor_id = :tutor_id"; // Ensure you're updating the correct event
            
                    // Prepare the statement
                    $stmt = $user_login->runQuery($query);
            
                    // Bind the parameters
                    $stmt->bindParam(':event_name', $event_name);
                    $stmt->bindParam(':rendered_hours', $rendered_hours);
                    $stmt->bindParam(':description', $description);
            
                    // Bind the file parameter if there is a file
                    if ($file_name) {
                        $stmt->bindParam(':attached_file', $file_name);
                    }
            
                    $stmt->bindParam(':event_id', $event_id);
                    $stmt->bindParam(':tutor_id', $tutor_id); // Use the logged-in tutor ID for validation
            
                    // Execute the query
                    if ($stmt->execute()) {
                        echo json_encode(['success' => true, 'message' => 'Event updated successfully']);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Database update failed']);
                    }
                } catch (PDOException $e) {
                    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
                }
            } elseif ($_POST['action'] === 'delete_event') {
                $event_id = $_POST['event_id'];
            
                // Validate the event belongs to the tutor (replace $tutor_id with the current tutor's ID)
                $stmt = $user_login->runQuery("
                    SELECT id FROM events WHERE id = :event_id AND tutor_id = :tutor_id
                ");
                $stmt->bindParam(':event_id', $event_id);
                $stmt->bindParam(':tutor_id', $tutor_id);
                $stmt->execute();
            
                if ($stmt->rowCount() > 0) {
                    // Delete the event
                    $deleteStmt = $user_login->runQuery("
                        DELETE FROM events WHERE id = :event_id
                    ");
                    $deleteStmt->bindParam(':event_id', $event_id);
            
                    if ($deleteStmt->execute()) {
                        echo json_encode(['success' => true, 'message' => 'Event deleted successfully.']);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Failed to delete event.']);
                    }
                } else {
                    echo json_encode(['success' => false, 'message' => 'Event not found or you do not have permission to delete it.']);
                }
            }
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
    }
    exit();
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

$query = "SELECT * FROM events WHERE tutor_id = :tutor_id";
$stmt = $user_login->runQuery($query);
$tutor_id = $userData['id'];
$stmt->bindParam(':tutor_id', $tutor_id, PDO::PARAM_INT);
$stmt->execute();
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);


// Assuming session start and database connection is already done above
$tutor_id = $userData['id']; // Use logged-in tutor's student_id

// Total hours goal
$target_hours = 90;

// Initialize total rendered hours
$total_rendered_hours = 0;

// Fetch rendered hours from `events` table
$query = "SELECT COALESCE(SUM(rendered_hours), 0) as total_rendered_hours FROM events WHERE tutor_id = :tutor_id";
$stmt = $user_login->runQuery($query);
$stmt->bindParam(":tutor_id", $tutor_id);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$events_rendered_hours = $row['total_rendered_hours'];

// Fetch rendered hours from `tutee_progress` table
$tutee_rendered_hours = [];
$query = "SELECT tutee_id, COALESCE(SUM(rendered_hours), 0) as tutee_rendered_hours FROM tutee_progress WHERE tutor_id = :tutor_id GROUP BY tutee_id";
$stmt = $user_login->runQuery($query);
$stmt->bindParam(":tutor_id", $tutor_id);
$stmt->execute();

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $tutee_rendered_hours[$row['tutee_id']] = $row['tutee_rendered_hours'];
}

// Total rendered hours including tutees and events
$total_rendered_hours = array_sum($tutee_rendered_hours) + $events_rendered_hours;

// Calculate progress percentage
$progress_percentage = min(($total_rendered_hours / $target_hours) * 100, 100);

// Determine if hours came from events or tutee progress
$has_events_data = $events_rendered_hours > 0;
$has_tutee_data = count($tutee_rendered_hours) > 0;




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
    <title>Progress</title>


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
                    <li class="nav-link custom-bg" data-bs-placement="right" title="Tutor Progress">
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
    <div class="home" >
        <div class="container-lg p-3">
            <div class="career-form headings d-flex justify-content-center mt-3">
                <div class="row">
                    <div class="card1" style="color:white;">Progress</div>
                </div>
            </div>
        </div>

<!-- Progress bar -->
<div class="progress-container">
  <div class="progress-ring">
    <svg width="200" height="200" viewBox="0 0 200 200">
      <circle class="progress-ring-background" cx="100" cy="100" r="80" stroke="#2c2c2c" stroke-width="15" fill="transparent"/>
      <circle class="progress-ring-fill" cx="100" cy="100" r="80" stroke="url(#progress-gradient)" stroke-width="15" fill="transparent" stroke-dasharray="502.654" stroke-dashoffset="502.654" transform="rotate(-90 100 100)"/>
    </svg>
    <div class="progress-text">
      <span id="progress-value"><?php echo $total_rendered_hours; ?>/<?php echo $target_hours; ?></span>
    </div>
  </div>
</div>

<!-- Legend -->
<div class="legend">
  <p>
    <?php 
    // Define colors for each tutee and a default color for other events
    $colors = ["#0000FF", "#FF0000", "#00FF00", "#FFA500", "#8A2BE2"]; // Add more colors if needed
    $index = 0;

    // Display legend for each tutee if there is data in tutee progress
    if ($has_tutee_data) {
        foreach ($tutee_rendered_hours as $tutee_id => $rendered_hours) {
            // Fetch tutee's first and last name based on tutee_id
            $tutee_query = "SELECT firstname, lastname FROM tutee WHERE id = :tutee_id";
            $tutee_stmt = $user_login->runQuery($tutee_query);
            $tutee_stmt->bindParam(":tutee_id", $tutee_id);
            $tutee_stmt->execute();
            $tutee_row = $tutee_stmt->fetch(PDO::FETCH_ASSOC);

            // If the tutee data exists, display the name
            if ($tutee_row) {
                $color = $colors[$index % count($colors)]; // Cycle through colors if there are more tutees than colors
                echo "<span class='legend-box' style='background-color: $color;'></span> " . htmlspecialchars($tutee_row['firstname'] . " " . $tutee_row['lastname']) . " ";
                $index++;
            }
        }
    }

    // Display legend for events data only if present
    if ($has_events_data) {
        echo "<span class='legend-box' style='background-color: #FFFF00;'></span> Events (Seminars)";
    }
    ?>
  </p>
</div>


<svg width="0" height="0">
  <defs>
    <linearGradient id="progress-gradient" x1="0%" y1="100%" x2="100%" y2="0%">
      <?php
      // Display color stops based on the data available
      $index = 0;
      if ($has_tutee_data) {
          foreach ($tutee_rendered_hours as $tutee_id => $rendered_hours) {
              $color = $colors[$index % count($colors)];
              $offset = ($index / count($tutee_rendered_hours)) * 100; // Adjust the offset based on the number of tutees
              echo "<stop offset='{$offset}%' style='stop-color: $color; stop-opacity: 1' />";
              $index++;
          }
      }

      // Add the event color stop only if event data exists
      if ($has_events_data) {
          echo "<stop offset='100%' style='stop-color: #FFFF00; stop-opacity: 1' />"; // Other Events (Yellow)
      }
      ?>
    </linearGradient>
  </defs>
</svg>





<!-- Other Events Accordion Section -->
<div class="container-lg p-3 text-center">
    <div class="accordion shadow-lg" id="otherEventsAccordion">
        <div class="accordion-item">
            <h4 class="accordion-header" id="headingOtherEvents">
                <button class="accordion-button custom-accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOtherEvents" aria-expanded="true" aria-controls="collapseOtherEvents">
                    <h2 class="mb-0">Other Events</h2>
                </button>
            </h4>
            <div id="collapseOtherEvents" class="accordion-collapse collapse show" aria-labelledby="headingOtherEvents" data-bs-parent="#otherEventsAccordion">
                <div class="accordion-body">
                    <div class="d-flex justify-content-end mb-3">
                        <button id="addEntryBtn" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#confirmAddEventModal">Add Entry</button>
                    </div>
                    <div class="table-wrapper">
                        <table class="table mx-auto" id="eventsTable" style="max-width: 100%; text-align: center;">
                            <thead class="background1">
                                <tr>
                                    <th>Event</th>
                                    <th>Rendered Hours</th>
                                    <th>Description</th>
                                    <th>Attached File</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($events as $event): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($event['event_name']); ?></td>
                                    <td><?php echo htmlspecialchars($event['rendered_hours']); ?> hours</td>
                                    <td><?php echo htmlspecialchars($event['description']); ?></td>
                                    <td class="file-name-cell">
                                        <?php if ($event['attached_file']): ?>
                                            <a href="../uploads/events/<?php echo htmlspecialchars($event['attached_file']); ?>" target="_blank" class="btn btn-primary">View File</a>
                                        <?php else: ?>
                                            <span>No file uploaded</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="d-flex justify-content-center">
                                        <button class="btn btn-primary me-2 editEventBtn" 
                                        data-id="<?php echo $event['id']; ?>" 
                                        data-name="<?php echo htmlspecialchars($event['event_name']); ?>" 
                                        data-hours="<?php echo htmlspecialchars($event['rendered_hours']); ?>" 
                                        data-description="<?php echo htmlspecialchars($event['description']); ?>" 
                                        data-file="<?php echo htmlspecialchars($event['attached_file']); ?>"><i class='bx bxs-edit'
                                        data-bs-toggle="modal" data-bs-target="#editEventModal"></i></button>

                                        <!-- Delete Event Button -->
                                        <button class="btn btn-danger deleteEventBtn" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#deleteEventModal">
                                            <i class='bx bx-trash' data-id="<?php echo $event['id']; ?>"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- TUTEES -->
<div class="container-lg p-3 text-center">
    <h2 class="tutee-title">TUTEES</h2>
</div>

<div class="container-lg p-3">
    <?php if (!empty($tutees)): ?>
        <?php foreach ($tutees as $tutee): ?>
            <!-- Separate wrapper for each tutee's content -->
            <div class="shadow-lg mb-3">
                <div class="container-lg p-3 table-container">
                    <!-- Centering the tutee's name -->
                    <div class="d-flex justify-content-center align-items-center mb-4">
                        <h3><?php echo htmlspecialchars($tutee['firstname'] . ' ' . $tutee['lastname']); ?></h3>
                    </div>
                    <!-- Centering the table -->
                    <div class="table-wrapper">
                        <table class="table text-center">
                            <thead class="background1">
                                <tr>
                                    <th>Week</th>
                                    <th>Attached File</th>
                                    <th>Description</th>
                                    <th>Rendered Hours</th>
                                    <th>Location</th>
                                    <th>Subject</th>
                                    <th>Date Submitted</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="file-upload-list-<?php echo $tutee['id']; ?>">
                                <?php if (isset($progressData[$tutee['id']])): ?>
                                    <?php foreach ($progressData[$tutee['id']] as $progress): ?>
                                        <?php $checkboxChecked = !empty($progress['uploaded_files']) && !empty($progress['description']); ?>
                                        <tr>
                                            <td>
                                                <input type="checkbox" class="form-check-input checkbox" id="checkbox<?php echo $tutee['id'] . '-' . $progress['week_number']; ?>" <?php echo $checkboxChecked ? 'checked' : ''; ?> disabled>
                                                Week <?php echo htmlspecialchars($progress['week_number']); ?>
                                            </td>
                                            <td class="file-name-cell">
                                                <?php if ($progress['uploaded_files']): ?>
                                                    <a href="../uploads/<?php echo htmlspecialchars($progress['uploaded_files']); ?>" target="_blank" class="btn btn-primary">View File</a>
                                                <?php else: ?>
                                                    <span>No file uploaded</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($progress['description'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($progress['rendered_hours'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($progress['location'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($progress['subject'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($progress['date'] ?? ''); ?></td>
                                            <td class="text-center">
                                                <button class="btn btn-primary me-2 edit-btn" data-bs-toggle="modal" data-bs-target="#editModal" 
                                                    data-tutee-id="<?php echo $tutee['id']; ?>" 
                                                    data-week-number="<?php echo $progress['week_number']; ?>"
                                                    data-description="<?php echo htmlspecialchars($progress['description']); ?>"
                                                    data-rendered-hours="<?php echo $progress['rendered_hours']; ?>"
                                                    data-location="<?php echo htmlspecialchars($progress['location']); ?>"
                                                    data-subject="<?php echo htmlspecialchars($progress['subject']); ?>"
                                                    data-file="<?php echo htmlspecialchars($progress['uploaded_files']); ?>">
                                                    <i class='bx bx-edit'></i>
                                                </button>
                                                <button class="btn btn-danger delete-btn" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#deleteModal"
                                                    data-tutee-id="<?php echo $tutee['id']; ?>" 
                                                    data-week-number="<?php echo $progress['week_number']; ?>">
                                                    <i class='bx bx-trash'></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <!-- Centering the buttons for each tutee -->
                    <div class="d-flex justify-content-center flex-column flex-md-row text-center">
                        <button id="add-week-btn-<?php echo $tutee['id']; ?>" 
                                class="btn btn-primary m-2" 
                                data-tutee-id="<?php echo $tutee['id']; ?>" 
                                data-bs-toggle="modal" 
                                data-bs-target="#addWeekModal-<?php echo $tutee['id']; ?>"
                                <?php
                                // Fetch the status of the current session
                                $stmt = $user_login->runQuery("SELECT status FROM tutor_sessions WHERE tutor_id = :tutor_id AND tutee_id = :tutee_id");
                                $stmt->bindParam(':tutor_id', $tutor_id);
                                $stmt->bindParam(':tutee_id', $tutee['id']);
                                $stmt->execute();
                                $sessionStatus = $stmt->fetchColumn();

                                // Disable the button if status is 'requested' or 'completed'
                                if ($sessionStatus === 'requested' || $sessionStatus === 'completed') {
                                    echo 'disabled';
                                }
                            ?>>
                            Add Week
                        </button>

                        <button id="finish-btn-<?php echo $tutee['id']; ?>" class="btn btn-danger m-2 finish-btn" 
                            data-tutor-id="<?php echo $tutor_id; ?>" data-tutee-id="<?php echo $tutee['id']; ?>" 
                            <?php
                                // Fetch the status of the current session
                                $stmt = $user_login->runQuery("SELECT status FROM tutor_sessions WHERE tutor_id = :tutor_id AND tutee_id = :tutee_id");
                                $stmt->bindParam(':tutor_id', $tutor_id);
                                $stmt->bindParam(':tutee_id', $tutee['id']);
                                $stmt->execute();
                                $sessionStatus = $stmt->fetchColumn();

                                // Disable the button if status is 'requested' or 'completed'
                                if ($sessionStatus === 'requested' || $sessionStatus === 'completed') {
                                    echo 'disabled';
                                }
                            ?>>
                            Request Finish
                        </button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
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



<!-- MODALS -->


<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Edit Week Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editForm">
                    <input type="hidden" name="tutee_id" id="edit-tutee-id">
                    <div class="mb-3">
                        <label for="edit-week-number" class="form-label">Week Number</label>
                        <input type="number" class="form-control" id="edit-week-number" name="week_number" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit-description" class="form-label">Description</label>
                        <textarea class="form-control" id="edit-description" name="description" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="edit-rendered-hours" class="form-label">Rendered Hours</label>
                        <input type="number" class="form-control" id="edit-rendered-hours" name="rendered_hours" 
                            required min="0" max="4" oninput="this.value = Math.min(this.value, 4)">
                    </div>
                    <div class="mb-3">
                        <label for="edit-location" class="form-label">Location</label>
                        <input type="text" class="form-control" id="edit-location" name="location" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit-subject" class="form-label">Subject</label>
                        <input type="text" class="form-control" id="edit-subject" name="subject" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit-file-upload" class="form-label">Upload File</label>
                        <input type="file" class="form-control" id="edit-file-upload" name="file-upload">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveWeekBtn">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<!-- Add Week Modal -->
<?php foreach ($tutees as $tutee): ?>
<div class="modal fade" id="addWeekModal-<?php echo $tutee['id']; ?>" tabindex="-1" aria-labelledby="addWeekModalLabel-<?php echo $tutee['id']; ?>" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-dialog-centered" style="max-width: auto;"> <!-- Adjusted width -->
    <div class="modal-content">
      <div class="modal-header d-flex justify-content-center align-items-center border-0 mt-2 flex-column">
        <h5 class="modal-title" id="addWeekModalLabel-<?php echo $tutee['id']; ?>">Add Week for <?php echo htmlspecialchars($tutee['firstname'] . ' ' . $tutee['lastname']); ?></h5>
      </div>
      <div class="modal-body">
        <form id="add-week-form-<?php echo $tutee['id']; ?>" enctype="multipart/form-data">
          <div class="row mb-3">
            <div class="col-md-6">
              <label for="week-number-<?php echo $tutee['id']; ?>" class="form-label">Week Number</label>
              <input type="number" class="form-control" id="week-number-<?php echo $tutee['id']; ?>" placeholder="Enter week number" required>
            </div>
            <div class="col-md-6">
                <label for="rendered-hours-<?php echo $tutee['id']; ?>" class="form-label">Rendered Hours</label>
                <input type="number" class="form-control" id="rendered-hours-<?php echo $tutee['id']; ?>" 
                    placeholder="Enter rendered hours" required min="0" max="4" oninput="this.value = Math.min(this.value, 4)">
            </div>
          </div>
          <div class="row mb-3">
            <div class="col-md-6">
              <label for="location-<?php echo $tutee['id']; ?>" class="form-label">Location</label>
              <input type="text" class="form-control" id="location-<?php echo $tutee['id']; ?>" placeholder="Enter Location" required>
            </div>
            <div class="col-md-6">
              <label for="subject-<?php echo $tutee['id']; ?>" class="form-label">Subject</label>
              <input type="text" class="form-control" id="subject-<?php echo $tutee['id']; ?>" placeholder="Enter Subject" required>
            </div>
          </div>
          <div class="mb-3">
            <label for="description-<?php echo $tutee['id']; ?>" class="form-label">Description</label>
            <textarea class="form-control" id="description-<?php echo $tutee['id']; ?>" placeholder="Describe the week..." required></textarea>
          </div>
          <div class="mb-3">
            <label for="file-upload-<?php echo $tutee['id']; ?>" class="form-label">Upload Files</label>
            <input type="file" class="form-control" id="file-upload-<?php echo $tutee['id']; ?>" accept="*/*">
          </div>
      </div>
      <div class="modal-footer d-flex justify-content-center border-0">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">Save Week</button>
        </form>
      </div>
    </div>
  </div>
</div>
<?php endforeach; ?>


<!-- Confirmation Delete Event Modal -->
<div class="modal fade" id="deleteEventModal" tabindex="-1" aria-labelledby="deleteEventModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" id="deleteEventForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteEventModalLabel">Confirm Deletion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this event? This action cannot be undone.
                    <input type="hidden" name="event_id" id="event_id">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger" id="deleteBtn">Delete</button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- Confirmation Add Event Modal -->
<div class="modal fade" id="confirmAddEventModal" tabindex="-1" aria-labelledby="confirmAddEventModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered" style="max-width: auto;"> <!-- Adjusted width -->
        <div class="modal-content">
            <div class="modal-header d-flex justify-content-center align-items-center border-0 mt-2 flex-column">
                <h5 class="modal-title" id="confirmAddEventModalLabel">Add New Event</h5>
            </div>
            <div class="modal-body">
                <!-- Start of the Form -->
                <form id="addEventForm" method="POST" enctype="multipart/form-data">
                    <!-- Other Event Fields -->
                    <div id="otherFields">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="eventName" class="form-label">Event</label>
                                <input type="text" class="form-control" id="eventName" name="event_name" placeholder="Enter event name" required>
                            </div>
                            <div class="col-md-6">
                                <label for="renderedHoursOther" class="form-label">Rendered Hours</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="renderedHoursOther" name="rendered_hours" placeholder="hours" min="0" max="90" step="1" required oninput="this.value = Math.min(this.value, 90)">
                                </div>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="descriptionOther" class="form-label">Description</label>
                                <input type="text" class="form-control" id="descriptionOther" name="description" placeholder="Enter description" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="attachFileOther" class="form-label">Attach File (Pictures Only, Max 10mb)</label>
                            <input type="file" class="form-control" id="attachFileOther" name="attached_file" accept="image/*" required>
                        </div>
                    </div>
                </form>
                <!-- End of the Form -->
            </div>
            <div class="modal-footer d-flex justify-content-center border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary" id="saveEventBtn">Add Event</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this item?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Event Modal -->
<div class="modal fade" id="editEventModal" tabindex="-1" aria-labelledby="editEventModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editEventModalLabel">Edit Event</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Edit Event Form -->
                <form id="editEventForm" method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="editEventName" class="form-label">Event Name</label>
                        <input type="text" class="form-control" id="editEventName" name="event_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="editRenderedHours" class="form-label">Rendered Hours</label>
                        <input type="number" class="form-control" id="editRenderedHours" name="rendered_hours" min="0" max="90" required oninput="this.value = Math.min(this.value, 90)">
                    </div>
                    <div class="mb-3">
                        <label for="editDescription" class="form-label">Description</label>
                        <input type="text" class="form-control" id="editDescription" name="description" required>
                    </div>
                    <div class="mb-3">
                        <label for="editAttachFile" class="form-label">Attach File (Optional)</label>
                        <input type="file" class="form-control" id="editAttachFile" name="attached_file" accept="image/*">
                    </div>
                    <input type="hidden" name="event_id" id="editEventId">
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <!-- Save Changes Button with action -->
                        <button type="submit" class="btn btn-primary" id="saveChangesBtn">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Error Modal -->
<div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="errorModalLabel">Error</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Please fill all fields and attach a file.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

        <!-- Success Upload Modal UNUSED-->
        <div class="modal fade" id="uploadSuccessModal" tabindex="-1" aria-labelledby="uploadSuccessModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                <div class="modal-header d-flex justify-content-center align-items-center border-0 mt-2">
                    <!-- Boxicons Checkmark with Circular Swipe Animation -->
                    <div class="checkmark-container">
                    <svg class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                        <circle class="checkmark__circle" cx="26" cy="26" r="25" fill="none"/>
                        <path class="checkmark__check" fill="none" d="M14 27l7.5 7.5L38 18"/>
                    </svg>
                    </div>
                </div>
                <div class="modal-body d-flex justify-content-center align-items-center" id="modalBody">
                    File uploaded successfully.
                </div>
                <div class="modal-footer border-0">
                    <!-- Footer left empty as per original design -->
                </div>
                </div>
            </div>
        </div>

        <!-- Error Upload Modal UNUSED-->
        <div class="modal fade" id="uploadErrorModal1" tabindex="-1" aria-labelledby="uploadErrorModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                <div class="modal-header d-flex justify-content-center align-items-center border-0 mt-2">
                    <!-- SVG Circular Swipe Error Icon -->
                    <div class="error-icon-container">
                    <svg class="error-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                        <circle class="error-icon__circle" cx="26" cy="26" r="25" fill="none"/>
                        <path class="error-icon__cross" fill="none" d="M16 16 L36 36 M36 16 L16 36"/>
                    </svg>
                    </div>
                </div>
                <div class="modal-body" id="errorMessage1">
                    <!-- Error message will be dynamically added here -->
                    An error occurred while uploading the file.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
                </div>
            </div>
        </div>

        <!-- Error Add Week Modal -->
        <div class="modal fade" id="errorModal2" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="errorModalLabel">Error</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="errorMessage2">
                    <!-- Error message will be dynamically added here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
                </div>
            </div>
        </div>

        <!-- Confirmation Finish Modal -->
        <div class="modal fade" id="confirmFinishModal" tabindex="-1" aria-labelledby="confirmFinishModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header d-flex justify-content-center align-items-center border-0 mt-2">
                        <!-- Centered header content -->
                        <img src="../assets/medal.png" alt="Delete" class="delete-icon" style="width: 65px; height: 65px;">
                    </div>
                    <div class="modal-body d-flex justify-content-center align-items-center" id="modalBody">
                        <p>Are you sure you want to finish this tutor session?</p>
                    </div>
                    <div class="modal-footer d-flex justify-content-center border-0">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" id="confirmFinish">Finish Session</button>
                    </div>
                </div>
            </div>
        </div>


    <!-- Error Finish Modal -->
        <div class="modal fade" id="errorModal3" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                <div class="modal-header d-flex justify-content-center align-items-center border-0 mt-2">
                    <!-- Centered header content -->
                    <img src="../assets/error.png" alt="Error" class="error-icon" style="width: 65px; height: 65px;">
                </div>
                <div class="modal-body d-flex justify-content-center align-items-center" id="errorMessage3">
                    <p>An error occurred. Please try again.</p>
                </div>
                <div class="modal-footer d-flex justify-content-center border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
                </div>
            </div>
        </div>

<!-- Success Modal -->
<div class="modal fade" id="datesuccess" tabindex="-1" aria-labelledby="datesuccessLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header d-flex justify-content-center align-items-center border-0 mt-2">
                <!-- Boxicons Checkmark with Circular Swipe Animation -->
                <div class="checkmark-container">
                    <svg class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                        <circle class="checkmark__circle" cx="26" cy="26" r="25" fill="none"/>
                        <path class="checkmark__check" fill="none" d="M14 27l7.5 7.5L38 18"/>
                    </svg>
                </div>
            </div>
            <div class="modal-body d-flex justify-content-center align-items-center" id="modalBody">
                Description and date saved successfully.
            </div>
            <div class="modal-footer border-0">
                <!-- Footer left empty as per original design -->
            </div>
        </div>
    </div>
</div>


<!-- Error Modal -->
<div class="modal fade" id="dateerror" tabindex="-1" aria-labelledby="dateerrorLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="dateerrorLabel">Error</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="errorMessage">
                <!-- Error message will be injected here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Adding Event -->
<div class="modal fade" id="confirmAddEventModal" tabindex="-1" aria-labelledby="confirmAddEventModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 600px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmAddEventModalLabel">Add New Event</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="eventName" class="form-label">Event Name</label>
                    <input type="text" class="form-control" id="eventName" placeholder="Enter event name">
                </div>
                <div class="mb-3">
                    <label for="renderedHours" class="form-label">Rendered Hours</label>
                    <input type="number" class="form-control" id="renderedHours" placeholder="Enter hours" min="0" step="1">
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <input type="text" class="form-control" id="description" placeholder="Enter description">
                </div>
                <div class="mb-3">
                    <label for="fileInput" class="form-label">Attach File</label>
                    <input type="file" class="form-control" id="fileInput" accept=".pdf" onchange="handleFileUpload(event)">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveEventBtn">Save Event</button>
            </div>
        </div>
    </div>
</div>

</div>
</div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="script1.js"></script>
    <script>

            // Variable to store the row and data to be deleted
            let deleteData = { row: null, tuteeId: null, weekNumber: null };

            // Show the modal when the delete button is clicked
            $(document).on('click', '.delete-btn', function() {
                const tuteeId = $(this).data('tutee-id');
                const weekNumber = $(this).data('week-number');

                // Set the data to be deleted
                deleteData = { tuteeId: tuteeId, weekNumber: weekNumber };

                // Show the modal
                $('#deleteModal').modal('show');
            });


            // Handle confirmation of deletion
            $(document).on('click', '#confirmDeleteBtn', function() {
            const { tuteeId, weekNumber } = deleteData;

            $.ajax({
                url: 'delete_week', // Adjust the URL to your actual server-side script
                type: 'POST',
                data: { tutee_id: tuteeId, week_number: weekNumber },
                success: function(response) {
                    const res = JSON.parse(response);
                    if (res.success) {
                        // Reload the page or remove the row from the table
                        window.location.reload();  // Refresh the page to show the new event
                    } else {
                        alert(res.message); // Show any error message
                    }
                }
            });

            // Hide the modal after the deletion attempt
            $('#deleteModal').modal('hide');
        });


    $(document).ready(function() {
    let tuteeIdGlobal;

    // Function to enable or disable the 'Request Finish' button based on session status
    function updateFinishButtonState(tuteeId) {
        // Get the session status from the backend (via AJAX)
        $.ajax({
            url: '', // Current PHP file
            type: 'POST',
            data: {
                action: 'get_session_status',
                tutee_id: tuteeId
            },
            success: function(response) {
                const res = JSON.parse(response);
                if (res.status === 'requested' || res.status === 'completed') {
                    $(`#finish-btn-${tuteeId}`).prop('disabled', true);
                } else {
                    $(`#finish-btn-${tuteeId}`).prop('disabled', false);
                }
            }
        });
    }

    // Apply button states on page load
    $('.finish-btn').each(function() {
        const tuteeId = $(this).data('tutee-id');
        updateFinishButtonState(tuteeId);
    });

    $(document).on('click', '.finish-btn', function() {
        tuteeIdGlobal = this.getAttribute('data-tutee-id');

        // Check if all checkboxes are checked
        var allChecked = true;
        var checkboxes = document.querySelectorAll(`#file-upload-list-${tuteeIdGlobal} .checkbox`);
        checkboxes.forEach(function(checkbox) {
            if (!checkbox.checked) {
                allChecked = false;
            }
        });

        if (allChecked) {
            // Show confirmation modal
            $('#confirmFinishModal').modal('show');
        } else {
            // Set error message and show error modal
            $('#errorMessage3').text('Please complete all needed details per week before finishing the session.');
            $('#errorModal3').modal('show');
        }
    });

    $('#confirmFinish').on('click', function() {
        var tutorId = $('.finish-btn[data-tutee-id="' + tuteeIdGlobal + '"]').data('tutor-id');

        $.ajax({
            url: '', // Current PHP file
            type: 'POST',
            data: {
                action: 'finish_session',
                tutor_id: tutorId,
                tutee_id: tuteeIdGlobal
            },
            success: function(response) {
                const res = JSON.parse(response);
                if (res.success) {
                    // Disable buttons and set success message
                    document.querySelector(`#finish-btn-${tuteeIdGlobal}`).disabled = true;
                    $('#successMessage').text('Tutor session finished successfully.');
                    $('#successModal').modal('show');
                    // Hide the confirmation modal
                    $('#confirmFinishModal').modal('hide');
                } else {
                    // Set error message and show error modal
                    $('#errorMessage3').text(res.message);
                    $('#errorModal3').modal('show');
                }
            }
        });
    });

});
    </script>

    <script>
document.getElementById("saveEventBtn").addEventListener("click", function (e) {
    e.preventDefault();  // Prevent the default form submission

    // Get form values
    let eventName = document.getElementById("eventName").value;
    let renderedHours = document.getElementById("renderedHoursOther").value;
    let description = document.getElementById("descriptionOther").value;
    let fileInput = document.getElementById("attachFileOther");
    let file = fileInput.files[0]; // Get the file

    // Check if all fields are filled
    if (!eventName || !renderedHours || !description || !file) {
        // Show the error modal with a bounce effect
        $('#errorModal').modal('show');  // Bootstrap modal (assuming you have an error modal with this ID)
        
        // Add bounce animation
        $('#errorModal').addClass('animate__animated animate__bounce');
        
        // Optionally, remove the bounce class after animation ends to avoid repeat animation
        $('#errorModal').on('hidden.bs.modal', function () {
            $(this).removeClass('animate__animated animate__bounce');
        });

        return;
    }

    // Check file size (max 10MB) and type (only .jpg, .jpeg, .png allowed)
    if (file.size > 10485760) {
        alert("File size exceeds 10MB. Please upload a smaller file.");
        return;
    }

    // Prepare FormData to send via AJAX
    let formData = new FormData();
    formData.append("event_name", eventName);
    formData.append("rendered_hours", renderedHours);
    formData.append("description", description);
    formData.append("attached_file", file);  // The file
    formData.append("action", "add_event");  // Action to distinguish from other requests

    // Send data via AJAX (POST request)
    let xhr = new XMLHttpRequest();
    xhr.open("POST", "", true); // Use the correct PHP file to process the request
    xhr.onload = function () {
        if (xhr.status === 200) {
            let response = JSON.parse(xhr.responseText);
            if (response.success) {
                window.location.reload();  // Refresh the page to show the new event
            } else {
                alert("Failed to add event. Please try again.");
            }
        } else {
            alert("Error occurred. Please try again.");
        }
    };
    xhr.send(formData);
});


        document.querySelectorAll('.editEventBtn').forEach(button => {
        button.addEventListener('click', function() {
            // Get data from the button
            const eventId = this.getAttribute('data-id');
            const eventName = this.getAttribute('data-name');
            const renderedHours = this.getAttribute('data-hours');
            const description = this.getAttribute('data-description');
            const fileName = this.getAttribute('data-file');

            // Populate the modal with event data
            document.getElementById('editEventId').value = eventId;
            document.getElementById('editEventName').value = eventName;
            document.getElementById('editRenderedHours').value = renderedHours;
            document.getElementById('editDescription').value = description;

            // Optional: Display the existing file name (if any)
            if (fileName) {
                // Optionally, display the current file name
                // You can choose to display the file name in a label or similar
                console.log('Current file: ' + fileName);
            }
        });
    });

    document.getElementById('saveChangesBtn').addEventListener('click', function (e) {
        e.preventDefault(); // Prevent default form submission

        // Get the form element
        var form = document.getElementById('editEventForm');

        // Get all form data
        var formData = new FormData(form);

        // Append action type to distinguish
        formData.append('action', 'edit_event');

        // Create an AJAX request
        var xhr = new XMLHttpRequest();
        xhr.open('POST', '', true); // Replace with your edit event handler script
        xhr.onload = function () {
            if (xhr.status === 200) {
                var response = JSON.parse(xhr.responseText);
                if (response.success) {
                    location.reload(); // Refresh the page to reflect the changes
                } else {
                    alert('Failed to update event. Please try again.');
                }
            } else {
                alert('Error occurred. Please try again.');
            }
        };
        xhr.send(formData); // Send the data
    });


    // When the delete button is clicked
$('.deleteEventBtn').on('click', function() {
    var eventId = $(this).find('i').data('id');  // Get the event ID from the icon's data-id
    $('#event_id').val(eventId);  // Set the event ID in the hidden input field inside the modal
    $('#deleteEventMessage').text('');  // Clear any previous messages
});

// Handle form submission
$('#deleteEventForm').on('submit', function(e) {
    e.preventDefault();  // Prevent the default form submission

    var eventId = $('#event_id').val();  // Get the event ID from the hidden input field

    $.ajax({
        url: '',  
        method: 'POST',
        data: {
            action: 'delete_event',
            event_id: eventId
        },
        success: function(response) {
            var data = JSON.parse(response);
            if (data.success) {
                $('#deleteEventMessage').text(data.message).removeClass('text-danger').addClass('text-success');  // Display success message in modal
                setTimeout(function() {
                    $('#deleteEventModal').modal('hide');  // Hide the modal after a short delay
                    location.reload();  // Optionally reload the page to remove the event from the list
                }, 1500);  // Delay to show the success message
            } else {
                $('#deleteEventMessage').text(data.message).removeClass('text-success').addClass('text-danger');  // Display error message in modal
            }
        },
        error: function(xhr, status, error) {
            $('#deleteEventMessage').text('An error occurred: ' + error).removeClass('text-success').addClass('text-danger');
        }
    });
});


// progress bar script
document.addEventListener("DOMContentLoaded", function () {
  const targetHours = <?php echo $target_hours; ?>;
  const renderedHours = <?php echo $total_rendered_hours; ?>;
  const progressPercentage = <?php echo $progress_percentage; ?>;

  // Update the text
  document.getElementById("progress-value").textContent = `${renderedHours}/${targetHours}`;

  // Update the circular progress
  const progressRingFill = document.querySelector(".progress-ring-fill");
  const strokeDashArray = 502.654;
  const offset = strokeDashArray - (progressPercentage / 100) * strokeDashArray;
  progressRingFill.style.strokeDashoffset = offset;
});
</script>

<!-- PINAKA BAGOO -->
<script>
   $(document).ready(function() {
    // Handle add-week form submission
    $(document).on('submit', '[id^="add-week-form-"]', function(e) {
        e.preventDefault();

        const tuteeId = $(this).attr('id').split('-').pop(); // Extract tutee ID from the form's ID
        const weekNumber = $(`#week-number-${tuteeId}`).val();
        const description = $(`#description-${tuteeId}`).val().trim();
        const location = $(`#location-${tuteeId}`).val().trim();
        const subject = $(`#subject-${tuteeId}`).val().trim();
        const renderedHours = $(`#rendered-hours-${tuteeId}`).val();
        const fileUpload = $(`#file-upload-${tuteeId}`).prop('files')[0]; // Get the file object

        const formData = new FormData();
        formData.append('action', 'add_week');
        formData.append('tutee_id', tuteeId);
        formData.append('week_number', weekNumber);
        formData.append('description', description);
        formData.append('rendered_hours', renderedHours);
        formData.append('location', location);
        formData.append('subject', subject);
        if (fileUpload) {
            formData.append('file', fileUpload); // Append file if exists
        }

        $.ajax({
            url: '', // Adjust the URL to your server-side script
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                // Handle success (e.g., update the UI, close the modal, etc.)
                $('#addWeekModal-' + tuteeId).modal('hide'); // Hide modal after success
                window.location.reload(true); // Force reload and bypass cache
            },
            error: function(xhr, status, error) {
                // Handle error
                alert('An error occurred: ' + error);
            }
        });
    });

    // Open the modal when the "Add Week" button is clicked
    $(document).on('click', '[id^="add-week-btn-"]', function() {
        const tuteeId = $(this).data('tutee-id');
        
        // Clear previous input values
        $(`#week-number-${tuteeId}`).val('');
        $(`#description-${tuteeId}`).val('');
        $(`#rendered-hours-${tuteeId}`).val('');
        $(`#location-${tuteeId}`).val('');
        $(`#subject-${tuteeId}`).val('');
        $(`#file-upload-${tuteeId}`).val('');

        // Open the modal for the correct tutee
        $(`#addWeekModal-${tuteeId}`).modal('show');
    });
});



// Open the modal when the "Add Week" button is clicked
$(document).on('click', '[id^="add-week-btn-"]', function() {
    const tuteeId = $(this).data('tutee-id');
    
    // Clear previous input values
    $(`#week-number-${tuteeId}`).val('');
    $(`#description-${tuteeId}`).val('');
    $(`#rendered-hours-${tuteeId}`).val('');
    $(`#location-${tuteeId}`).val('');
    $(`#subject-${tuteeId}`).val('');
    $(`#file-upload-${tuteeId}`).val('');

    // Open the modal for the correct tutee
    $(`#addWeekModal-${tuteeId}`).modal('show');
});


// Handle saving the new week entry when the "Save" button is clicked
$('#save-week-btn').on('click', function() {
    const tuteeId = $('#add-week-btn-<?php echo $tutee['id']; ?>').data('tutee-id');
    const weekNumber = $('#week-number').val();
    const uploadedFile = $('#uploaded-file').val();  // File handling logic will go here
    const weekDescription = $('#week-description').val();
    const renderedHours = $('#rendered-hours').val();
    const location = $('#location').val();
    const subject = $('#subject').val();
    const dateSubmitted = new Date().toLocaleDateString();  // Set current date as the submission date
    
    // If any required field is empty, show an error message
    if (!weekNumber || !weekDescription || !renderedHours || !location || !subject) {
        alert('Please fill in all fields.');
        return;
    }

    // Append the new row to the table
    const newRow = `
        <tr>
            <td>
                Week ${weekNumber}
            </td>
            <td>
                <span class="file-name">${uploadedFile}</span>
            </td>
            <td>
                ${weekDescription}
            </td>
            <td>
                ${renderedHours}
            </td>
            <td>
                ${dateSubmitted}
            </td>
            <td>
                ${location}
            </td>
            <td>
                ${subject}
            </td>
            <td class="text-center">
            <button class="btn btn-primary edit-btn">
                    <i class="bx bx-edit"></i>
                </button>
                <button class="btn btn-danger delete-btn">
                    <i class="bx bx-trash"></i>
                </button>
            </td>
        </tr>
    `;

    // Append the new row to the table body
    $('#file-upload-list-<?php echo $tutee['id']; ?>').append(newRow);
    
    // Close the modal
    $('#addWeekModal').modal('hide');
});


document.querySelectorAll('.edit-btn').forEach(button => {
    button.addEventListener('click', function() {
        // Get data from the button's data attributes
        document.getElementById('edit-tutee-id').value = this.dataset.tuteeId;
        document.getElementById('edit-week-number').value = this.dataset.weekNumber;
        document.getElementById('edit-description').value = this.dataset.description;
        document.getElementById('edit-rendered-hours').value = this.dataset.renderedHours;
        document.getElementById('edit-location').value = this.dataset.location;
        document.getElementById('edit-subject').value = this.dataset.subject;
    });
});

document.getElementById('saveWeekBtn').addEventListener('click', function() {
    const form = document.getElementById('editForm');
    const formData = new FormData(form);
    formData.append('action', 'edit_week');  // Add the action

    fetch('', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload(); // Reload to reflect changes
        } else {
            alert('Error updating the record: ' + (data.error || 'Unknown error'));
        }
    })
    .catch(error => console.error('Error:', error));
});


</script>
</body>
</html>
