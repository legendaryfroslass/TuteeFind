<?php
	session_start();
	include 'includes/conn.php';

	if(isset($_POST['login'])){
		$professor = $_POST['professor'];
		$password = $_POST['password'];

		$sql = "SELECT * FROM professor WHERE professor_id = '$professor'";
		$query = $conn->query($sql);

		if($query->num_rows < 1){
			$_SESSION['error'] = 'Cannot find professor with the ID';
		}
		else{
			$row = $query->fetch_assoc();
			if(password_verify($password, $row['password'])){
				$_SESSION['professor'] = $row['id'];
			}
			else{
				$_SESSION['error'] = 'Incorrect password';
			}
		}
		
	}
	else{
		$_SESSION['error'] = 'Input professor credentials first';
	}

	header('location: index.php');

?>