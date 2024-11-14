<?php
	$conn = new mysqli('localhost', 'root', '', 'tuteefind');

	if ($conn->connect_error) {
	    die("Connection failed: " . $conn->connect_error);
	}
	if (!$conn) {
		die("Database connection failed: " . mysqli_connect_error());
	}
?>