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
                    $_SESSION['tutorRole'] = 'tutor';
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
	public	function logout() {
		session_destroy();
		$_SESSION[ 'tutorSession' ] = false;
	}
    public function updateDetails($firstname, $lastname, $age, $sex, $number, $barangay, $student_id, $course, $year_section, $fblink, $emailaddress, $newPassword, $photo, $userData) {
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