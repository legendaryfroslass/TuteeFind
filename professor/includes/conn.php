<?php
	$conn = new mysqli('localhost', 'root', '', 'tuteefind');

	if ($conn->connect_error) {
	    die("Connection failed: " . $conn->connect_error);
	}
	
?>