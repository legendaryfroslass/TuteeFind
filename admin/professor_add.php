<?php
include 'includes/session.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $middlename = $_POST['middlename'];
    $age = $_POST['age'];
    $faculty_id = $_POST['faculty_id'];
    $emailaddress = $_POST['emailaddress'];
    $prof_username = $_POST['prof_username'];
    $prof_password = password_hash($_POST['prof_password'], PASSWORD_DEFAULT);


    // Check for duplicate faculty_id
    $check_professor_sql = "SELECT * FROM professor WHERE faculty_id = ?";
    $check_professor_stmt = $conn->prepare($check_professor_sql);
    $check_professor_stmt->bind_param("s", $faculty_id);
    $check_professor_stmt->execute();
    $professor_result = $check_professor_stmt->get_result();

    // Check for duplicate emailaddress
    $check_email_sql = "SELECT * FROM professor WHERE emailaddress = ?";
    $check_email_stmt = $conn->prepare($check_email_sql);
    $check_email_stmt->bind_param("s", $emailaddress);
    $check_email_stmt->execute();
    $email_result = $check_email_stmt->get_result();

    if ($professor_result->num_rows > 0) {
        $_SESSION['error'] = 'Duplicate Faculty ID found. Please use a unique Faculty ID.';
    } elseif ($email_result->num_rows > 0) {
        $_SESSION['error'] = 'Duplicate email address found. Please use a unique email address.';
    } else {
        $sql = "INSERT INTO professor (firstname, lastname, middlename, age, faculty_id, emailaddress, prof_username, prof_password) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "sssissss", 
            $firstname, $lastname, $middlename, $age, $faculty_id, $emailaddress, 
            $prof_username, $prof_password
        );

        if ($stmt->execute()) {
            $_SESSION['success'] = 'Professor added successfully';
        } else {
            $_SESSION['error'] = "Database error: " . $stmt->error;
        }
        $stmt->close();
    }

    $check_professor_stmt->close();
    $check_email_stmt->close();
} else {
    $_SESSION['error'] = 'Invalid request';
}

$conn->close();
header('location: professor');
?>
