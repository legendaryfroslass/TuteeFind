
<?php
include 'includes/session.php';

if(isset($_POST['edit'])){
    $id = $_POST['id'];
    $firstname = $_POST['firstname'];
    $middlename = $_POST['middlename'];
    $lastname = $_POST['lastname'];
    $age = $_POST['age'];
    $birthday = $_POST['birthday'];
    $sex = $_POST['sex'];
    $contact = $_POST['contact'];
    $address = $_POST['address'];
    $barangay = $_POST['barangay'];
    $city = $_POST['city'];
    $student_id = $_POST['student_id'];
    $course = $_POST['course'];
    $year = $_POST['year'];
    $section = $_POST['section'];
    $student_type = $_POST['student_type'];
    $preferred_day = $_POST['preferred_day'];
    
    // Check if preferred subjects are set, if not, set it to an empty string
    $preferred_subject = isset($_POST['preferred_subject']) ? implode(', ', $_POST['preferred_subject']) : '';

    // Check if preferred days are set, if not, set it to an empty string
    $preferred_day = isset($_POST['preferred_day']) ? implode(', ', $_POST['preferred_day']) : '';

    // Prepare and execute the SQL statement
    $sql = "UPDATE tutee SET firstname = ?, middlename = ?, lastname = ?, age = ?, birthday = ?, sex = ?, contact = ?, address = ?, barangay = ?, city = ?, student_id = ?, course = ?, year = ?, section = ?, student_type = ?, preferred_day = ?, preferred_subject = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    
    // Adjust the bind parameter string to match the number of variables
	$stmt->bind_param("sssissssssssssssss", $firstname, $middlename, $lastname, $age, $birthday, $sex, $contact, $address, $barangay, $city, $student_id, $course, $year, $section, $student_type, $preferred_day, $preferred_subject, $id);

    if($stmt->execute()){
        $_SESSION['success'] = 'Tutee updated successfully';
    }
    else{
        $_SESSION['error'] = $stmt->error;
    }
    $stmt->close();
}
else{
    $_SESSION['error'] = 'Fill up edit form first';
}

$conn->close();
header('location: tutor.php');
?>
