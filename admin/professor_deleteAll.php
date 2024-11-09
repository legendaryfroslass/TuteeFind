<?php
	include 'includes/session.php';

	$sql = "DELETE FROM archive_professor";
	if($conn->query($sql)){
		$_SESSION['success'] = "List of professor reset successfully";
	}
	else{
		$_SESSION['error'] = "Something went wrong in reseting";
	}

	header('location: archive_professor.php');

?> 