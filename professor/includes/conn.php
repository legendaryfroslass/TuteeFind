<?php
	$conn = new mysqli('localhost', 'tuteefind', 'tutee1234Find', 'tuteefind');

	if ($conn->connect_error) {
	    die("Connection failed: " . $conn->connect_error);
	}
	
?>