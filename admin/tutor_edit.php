<?php
include 'includes/session.php';

if (isset($_POST['edit'])) {
    $id = $_POST['id'];
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
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash the password

    // Prepare and execute the SQL statement
    
    $sql = "UPDATE tutor SET firstname = ?, lastname = ?, age = ?, sex = ?, number = ?, barangay = ?, student_id = ?, course = ?, year_section = ?, professor = ?, fblink = ?, emailaddress = ?, password = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);

    // Adjust the bind parameter string to match the number of variables
    $stmt->bind_param("ssissssssssssi", $firstname, $lastname, $age, $sex, $number, $barangay, $student_id, $course, $year_section, $professor, $fblink, $emailaddress, $password, $id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Tutor updated successfully';
    } else {
        $_SESSION['error'] = $stmt->error;
    }
    $stmt->close();
} else {
    $_SESSION['error'] = 'Fill up edit form first';
}

$conn->close();
header('location: tutor');
?>
