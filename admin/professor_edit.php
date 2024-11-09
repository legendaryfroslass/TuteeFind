<?php
	include 'includes/session.php';

	if(isset($_POST['edit'])){
		$id = $_POST['id'];
		$firstname = $_POST['firstname'];
		$lastname = $_POST['lastname'];
		$middlename = $_POST['middlename'];
		$age = $_POST['age'];
		$birthday = $_POST['birthday'];
		$faculty_id = $_POST['faculty_id'];
		$emailaddress = $_POST['emailaddress'];
		$employment_status = $_POST['employment_status'];
		// $prof_username = $_POST['prof_username'];
		// $prof_password = password_hash($_POST['prof_password'], PASSWORD_BCRYPT); // Hash the password

		$sql = "UPDATE professor SET prof_username = '$prof_username', firstname = '$firstname', lastname = '$lastname', middlename = '$middlename', age = '$age', birthday = '$birthday', faculty_id = '$faculty_id', emailaddress = '$emailaddress', employment_status = '$employment_status', prof_password = '$prof_password' WHERE id = '$id'";
		if($conn->query($sql)){
			$_SESSION['success'] = 'Professor updated successfully';
		}
		else{
			$_SESSION['error'] = $conn->error;
		}
	}
	else{
		$_SESSION['error'] = 'Fill up edit form first';
	}

	header('location: professor.php');
?>
