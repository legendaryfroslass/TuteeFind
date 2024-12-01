<?php
class Database {
    // private $host = "localhost";
    // private $db_name = "tuteefind";
    // private $username = "tuteefind";
    // private $password = "tutee_1234Find";

     private $host = "localhost";
     private $db_name = "tuteefind";
     private $username = "root";
     private $password = "";

    public $conn;

    public function dbConnection() {
        $this->conn = null;    
        try {
            // Make sure the password is being passed correctly
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);    
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }
        return $this->conn;
    }
}
?>
