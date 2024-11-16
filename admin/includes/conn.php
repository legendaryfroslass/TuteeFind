<?php
	$conn = new mysqli('localhost', 'tuteefind', 'tutee_1234Find', 'tuteefind');
	// $conn = new mysqli('localhost', 'root', '', 'tuteefind');
	if ($conn->connect_error) {
	    die("Connection failed: " . $conn->connect_error);
	}
?>