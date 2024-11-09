<?php
	include 'includes/session.php';

	$sql = "DELETE FROM matches";
	if($conn->query($sql)){
		$_SESSION['success'] = "List of Matches reset successfully";
	}
	else{
		$_SESSION['error'] = "Something went wrong in reseting";
	}

	header('location: matches.php');

?>