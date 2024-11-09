<?php
	$conn = new mysqli('localhost', 'root', '', 'tuteeFind');

	if ($conn->connect_error) {
	    die("Connection failed: " . $conn->connect_error);
	}
	
?>