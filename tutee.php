<?php

require_once 'dbconfig.php';

class TUTEE
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

    
// Helper function to log activity
private function logActivity($tutee_id, $activity) {
    try {
        // Set the timezone to Manila (Asia/Manila)
        $datetime = new DateTime('now', new DateTimeZone('Asia/Manila'));
        // Format the datetime as "December 7, 2024 07:20:20 PM"
        $formattedDateTime = $datetime->format('F j, Y h:i:s A'); // 12-hour format with AM/PM

        // Prepare SQL to insert the log into the database
        $stmt = $this->conn->prepare("INSERT INTO tutee_logs (tutee_id, activity, datetime) VALUES (:tutee_id, :activity, :datetime)");
        $stmt->bindParam(":tutee_id", $tutee_id, PDO::PARAM_INT);
        $stmt->bindParam(":activity", $activity, PDO::PARAM_STR);
        $stmt->bindParam(":datetime", $formattedDateTime, PDO::PARAM_STR); // Bind the formatted datetime
        $stmt->execute();
    } catch (PDOException $ex) {
        echo "Error: " . $ex->getMessage();
    }
}



public function updateDetails($firstname, $lastname, $age, $sex, $guardianname, $fblink, $barangay, $number, $emailaddress, $bio, $newPassword, $photo, $userData)
{
    try {
        // Initialize $sql variable
        $sql = "UPDATE tutee SET 
        firstname = :firstname,
        lastname = :lastname,
        age = :age,
        sex = :sex,
        guardianname = :guardianname,
        fblink = :fblink,
        barangay = :barangay,
        bio = :bio,
        emailaddress = :emailaddress,
        number = :number";

        if (!empty($newPassword)) {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $sql .= ", password = :password";
        }

        // Add photo update if provided
        if (!empty($photo) && $photo['error'] == 0) {
            $fileExtension = pathinfo($photo['name'], PATHINFO_EXTENSION);
            $validExtensions = ['jpg', 'jpeg', 'png'];
            if (in_array($fileExtension, $validExtensions) && $photo['size'] <= 104857600) { // 100 MB
                $photoPath = '../uploads/' . $firstname . '_' . $lastname . '.' . $fileExtension;
                move_uploaded_file($photo['tmp_name'], $photoPath);
                $sql .= ", photo = :photo";
            } else {
                throw new Exception("Invalid photo format or size.");
            }
        }

        // Complete the SQL query with WHERE clause using a unique identifier like tutee_id
        $sql .= " WHERE id = :id";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":firstname", $firstname);
        $stmt->bindParam(":lastname", $lastname);
        $stmt->bindParam(":age", $age);
        $stmt->bindParam(":sex", $sex);
        $stmt->bindParam(":guardianname", $guardianname);
        $stmt->bindParam(":fblink", $fblink);
        $stmt->bindParam(":barangay", $barangay);
        $stmt->bindParam(":number", $number);
        $stmt->bindParam(":bio", $bio);
        $stmt->bindParam(":emailaddress", $emailaddress);  // Bind the new email address
        $stmt->bindParam(":id", $userData['id']);  // Use a unique identifier like tutee_id

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

	public function isDuplicate($emailaddress)
    {
        $stmt = $this->conn->prepare("SELECT * FROM tutee WHERE emailaddress = :emailaddress");
        $stmt->bindParam(":emailaddress", $emailaddress, PDO::PARAM_STR);
        $stmt->execute();
        if($stmt->rowCount() > 0) {
            return true;
        } else {
            return false;
        }
    }
	
	// Registration Code
    public function register($firstname, $lastname, $age, $sex, $guardianname, $fblink, $barangay, $number, $emailaddress, $password, $tutee_bday, $school, $grade, $bio, $address)
    {
        try {
            $password = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $this->conn->prepare("INSERT INTO tutee (firstname, lastname, age, sex, guardianname, fblink, barangay, number, emailaddress, password, tutee_bday, school, grade, bio, address) 
                VALUES (:firstname, :lastname, :age, :sex, :guardianname, :fblink, :barangay, :number, :emailaddress, :password, :tutee_bday, :school, :grade, :bio, :address)");

            $stmt->bindParam(":firstname", $firstname, PDO::PARAM_STR);
            $stmt->bindParam(":lastname", $lastname, PDO::PARAM_STR);
			$stmt->bindParam(":age", $age, PDO::PARAM_STR);
			$stmt->bindParam(":sex", $sex, PDO::PARAM_STR);
			$stmt->bindParam(":guardianname", $guardianname, PDO::PARAM_STR);
			$stmt->bindParam(":fblink", $fblink, PDO::PARAM_STR);
            $stmt->bindParam(":barangay", $barangay, PDO::PARAM_STR);
            $stmt->bindParam(":number", $number, PDO::PARAM_STR);
            $stmt->bindParam(":emailaddress", $emailaddress, PDO::PARAM_STR);
            $stmt->bindParam(":password", $password, PDO::PARAM_STR);
            $stmt->bindParam(":tutee_bday", $tutee_bday, PDO::PARAM_STR);
            $stmt->bindParam(":school", $school, PDO::PARAM_STR);
            $stmt->bindParam(":grade", $grade, PDO::PARAM_STR);
            $stmt->bindParam(":bio", $bio, PDO::PARAM_STR);
            $stmt->bindParam(":address", $address, PDO::PARAM_STR);
            if ($stmt->execute()) {
                return true; // Return true on success
            } else {
                return false; // Return false on failure
            }
        } catch (PDOException $ex) {
            return $ex->getMessage(); // Return the actual error message
        }        
    }

	  // Login function
      public function login($emailaddress, $password) {
        try {
            $stmt1 = $this->conn->prepare("SELECT * FROM tutee WHERE emailaddress=:email_id");
            $stmt1->execute(array(":email_id" => $emailaddress));
            $userRow = $stmt1->fetch(PDO::FETCH_ASSOC);
            if ($stmt1->rowCount() == 1) {
                if (password_verify($password, $userRow['password'])) {
                    $_SESSION['userSession'] = $userRow['emailaddress']; // Store emailaddress in session
                    $_SESSION['tuteeRole'] = 'tutee'; // Set role to tutee

                    // Log the login activity
                    $this->logActivity($userRow['id'], 'Login');

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
		if ( isset( $_SESSION[ 'userSession' ] ) ) {
			return true;
		}
	}

	public	function redirect( $url ) {
		header( "Location: $url" );
	}

	 // Logout function
     public function logout() {
        if (isset($_SESSION['userSession'])) {
            $tutee_id = $this->getTuteeIdByEmail($_SESSION['userSession']); // Fetch the tutee's ID based on the email
            // Log the logout activity
            $this->logActivity($tutee_id, 'Logout');
        }

        session_destroy();
        $_SESSION['userSession'] = false;
    }

    // Helper function to fetch tutee ID by email
    private function getTuteeIdByEmail($email) {
        $stmt = $this->conn->prepare("SELECT id FROM tutee WHERE emailaddress = :email");
        $stmt->bindParam(":email", $email, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['id'];
    }
}

	