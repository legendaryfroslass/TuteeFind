<?php
require_once 'dbconfig.php';
class TUTOR
{	
	private $conn;
	public function __construct()
	{
		$database = new Database();
		$db = $database->dbConnection();
		$this->conn = $db;
    }
	public function runQuery($sql)
	{
		$stmt = $this->conn->prepare($sql);
		return $stmt;
	}
	public function lasdID()
	{
		$stmt = $this->conn->lastInsertId();
		return $stmt;
	}

	public function login($student_id, $password) {
		try {
			$stmt1 = $this->conn->prepare("SELECT * FROM tutor WHERE student_id=:student_id");
			$stmt1->execute(array(":student_id" => $student_id));
			$userRow = $stmt1->fetch(PDO::FETCH_ASSOC);
	
			if ($stmt1->rowCount() == 1) {
				if (password_verify($password, $userRow['password'])) {
					$_SESSION['tutorSession'] = $userRow['student_id'];
					$_SESSION['tutorID'] = $userRow['id']; // Store tutor's ID in the session
					$_SESSION['tutorRole'] = 'tutor';
	
					// Update the last_login timestamp
					$tutor_id = $userRow['id'];
					$currentTimestamp = date("Y-m-d H:i:s");
					$updateLoginTime = $this->conn->prepare("UPDATE tutor SET last_login = :last_login WHERE id = :id");
					$updateLoginTime->bindParam(':last_login', $currentTimestamp);
					$updateLoginTime->bindParam(':id', $tutor_id);
					$updateLoginTime->execute();
	
					// Log the login activity
					$stmt2 = $this->conn->prepare("INSERT INTO tutor_logs (tutor_id, activity) VALUES (:tutor_id, 'Login')");
					$stmt2->bindParam(':tutor_id', $tutor_id);
					$stmt2->execute();
	
					return true;
				} else {
					header("Location: login?notAvail");
					exit;
				}
			} else {
				header("Location: login?error");
				exit;
			}
		} catch (PDOException $ex) {
			echo $ex->getMessage();
		}
	}
	
	
	
	
	public	function is_logged_in() {
		if ( isset( $_SESSION[ 'tutorSession' ] ) ) {
			return true;
		}
	}
	public	function redirect( $url ) {
		header( "Location: $url" );
	}
	
	public function logout() {
		// Log the logout activity
		if (isset($_SESSION['tutorSession']) && isset($_SESSION['tutorID'])) {
			$tutor_id = $_SESSION['tutorID'];  // Use the tutor's ID from session
			$stmt = $this->conn->prepare("INSERT INTO tutor_logs (tutor_id, activity) VALUES (:tutor_id, 'Logout')");
			$stmt->bindParam(':tutor_id', $tutor_id);  // Use the tutor's ID from session
			$stmt->execute();
		}
	
		// Destroy session
		session_unset();
		session_destroy();
		$_SESSION['tutorSession'] = false;
	
		// Redirect to login page
		header("Location: login");
		exit;
	}
	
	
    public function updateDetails($firstname, $lastname, $age, $sex, $number, $barangay, $student_id, $course, $year_section, $fblink, $emailaddress, $bio, $newPassword, $photo, $userData) {
		try {
			$query = "UPDATE tutor SET 
				firstname = :firstname, 
				lastname = :lastname, 
				age = :age, 
				sex = :sex, 
				number = :number, 
				barangay = :barangay, 
				student_id = :student_id, 
				course = :course, 
				year_section = :year_section,
				bio = :bio,
				fblink = :fblink, 
				emailaddress = :emailaddress";
			
			if (!empty($newPassword)) {
				$hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
				$query .= ", password = :password";
			}
	
			if (!empty($photo) && $photo['error'] == 0) {
				$fileExtension = pathinfo($photo['name'], PATHINFO_EXTENSION);
				$validExtensions = ['jpg', 'jpeg', 'png'];
				if (in_array($fileExtension, $validExtensions) && $photo['size'] <= 104857600) { // 100 MB
					$photoPath = '../uploads/' . $student_id . '.' . $fileExtension;
					move_uploaded_file($photo['tmp_name'], $photoPath);
					$query .= ", photo = :photo";
				} else {
					throw new Exception("Invalid photo format or size.");
				}
			}
	
			$query .= " WHERE student_id = :original_student_id";
	
			$stmt = $this->runQuery($query);
			$stmt->bindParam(":firstname", $firstname);
			$stmt->bindParam(":lastname", $lastname);
			$stmt->bindParam(":age", $age);
			$stmt->bindParam(":sex", $sex);
			$stmt->bindParam(":number", $number);
			$stmt->bindParam(":barangay", $barangay);
			$stmt->bindParam(":student_id", $student_id);
			$stmt->bindParam(":course", $course);
			$stmt->bindParam(":year_section", $year_section);
			$stmt->bindParam(":bio", $bio);
			$stmt->bindParam(":fblink", $fblink);
			$stmt->bindParam(":emailaddress", $emailaddress);
			$stmt->bindParam(":original_student_id", $userData['student_id']);
	
			if (!empty($newPassword)) {
				$stmt->bindParam(":password", $hashedPassword);
			}
	
			if (!empty($photo) && $photo['error'] == 0) {
				$stmt->bindParam(":photo", $photoPath);
			}
			$stmt->execute();
			return true;
		} catch (PDOException $e) {
			echo $e->getMessage();
			return false;
		} catch (Exception $e) {
			echo $e->getMessage();
			return false;
		}
	}	
}