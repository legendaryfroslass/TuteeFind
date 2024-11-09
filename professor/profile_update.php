<?php
include 'includes/session.php';

if(isset($_GET['return'])){
    $return = $_GET['return'];
} else {
    $return = 'home.php';
}

if(isset($_POST['save'])){
    $curr_password = $_POST['curr_password'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $photo = $_FILES['photo']['name'];

    if(password_verify($curr_password, $user['prof_password'])){
        if(!empty($photo)){
            move_uploaded_file($_FILES['photo']['tmp_name'], '../images/'.$photo);
            $filename = $photo;  
        } else {
            $filename = $user['prof_photo'];
        }

        if($password != $user['prof_password']){
            $password = password_hash($password, PASSWORD_DEFAULT);
        }

        // Prepare the SQL statement
        $sql = "UPDATE professor SET prof_username = ?, prof_password = ?, firstname = ?, lastname = ?, prof_photo = ? WHERE id = ?";

        // Prepare and bind parameters
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssi", $username, $password, $firstname, $lastname, $filename, $user['id']);

        // Execute the statement
        if($stmt->execute()){
            $_SESSION['success'] = 'Professor profile updated successfully';
        } else {
            $_SESSION['error'] = $stmt->error;
        }
    } else {
        $_SESSION['error'] = 'Incorrect password';
    }
} else {
    $_SESSION['error'] = 'Fill up required details first';
}

header('location:'.$return);
?>
