<?php
include 'includes/session.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $age = $_POST['age'];
    $sex = $_POST['sex'];
    $number = $_POST['number'];
    $barangay = $_POST['barangay'];
    $student_id = $_POST['student_id'];
    $course = $_POST['course'];
    $year_section = $_POST['year_section'];
    $professor = $_POST['professor'];
    $fblink = $_POST['fblink'];
    $emailaddress = $_POST['emailaddress'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Check for duplicate student_id
    $check_student_sql = "SELECT * FROM tutor WHERE student_id = ?";
    $check_student_stmt = $conn->prepare($check_student_sql);
    $check_student_stmt->bind_param("s", $student_id);
    $check_student_stmt->execute();
    $student_result = $check_student_stmt->get_result();

    // Check for duplicate emailaddress
    $check_email_sql = "SELECT * FROM tutor WHERE emailaddress = ?";
    $check_email_stmt = $conn->prepare($check_email_sql);
    $check_email_stmt->bind_param("s", $emailaddress);
    $check_email_stmt->execute();
    $email_result = $check_email_stmt->get_result();

    if ($student_result->num_rows > 0) {
        $_SESSION['error'] = 'Duplicate student ID found. Please use a unique student ID.';
    } elseif ($email_result->num_rows > 0) {
        $_SESSION['error'] = 'Duplicate email address found. Please use a unique email address.';
    } else {
        $sql = "INSERT INTO tutor (firstname, lastname, age, sex, number, barangay, student_id, course, year_section, professor, fblink, emailaddress, password) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "ssissssssssss", 
            $firstname, $lastname, $age, $sex, $number, $barangay, 
            $student_id, $course, $year_section, $professor, 
            $fblink, $emailaddress, $password
        );

        if ($stmt->execute()) {
            $_SESSION['success'] = 'Tutor added successfully';
        } else {
            $_SESSION['error'] = "Database error: " . $stmt->error;
        }
        $stmt->close();
    }

    $check_student_stmt->close();
    $check_email_stmt->close();
} else {
    $_SESSION['error'] = 'Invalid request';
}

$conn->close();
header('location: tutor');
?>
