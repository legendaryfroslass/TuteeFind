<?php
include 'includes/session.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

if (isset($_POST['upload'])) {
    // Check if file was uploaded without errors
    if (isset($_FILES["excel_file"]) && $_FILES["excel_file"]["error"] == 0) {
        $file = $_FILES['excel_file']['tmp_name'];

        // Load Excel file using PhpSpreadsheet
        require __DIR__ . '/../vendor/autoload.php';

        // Load Excel file
        $spreadsheet = IOFactory::load($file);

        // Get the first worksheet
        $sheet = $spreadsheet->getActiveSheet();

        // Prepare the SQL statement for checking if record exists
        $check_sql = "SELECT * FROM professor WHERE faculty_id = ?";
        $check_stmt = $conn->prepare($check_sql);

        // Prepare the SQL statement for inserting or updating records
        $sql = "INSERT INTO professor (firstname, lastname, middlename, age, birthday, faculty_id, emailaddress, employment_status, prof_photo, prof_username, prof_password) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, COALESCE(?, 'profile.jpg'), ?, ?) 
                ON DUPLICATE KEY UPDATE 
                firstname = VALUES(firstname), 
                lastname = VALUES(lastname), 
                middlename = VALUES(middlename), 
                age = VALUES(age), 
                birthday = VALUES(birthday), 
                faculty_id = VALUES(faculty_id),
                emailaddress = VALUES(emailaddress),
                employment_status = VALUES(employment_status),
                prof_photo = COALESCE(VALUES(prof_photo), 'profile.jpg'),
                prof_username = VALUES(prof_username),
                prof_password = VALUES(prof_password)";

        $stmt = $conn->prepare($sql);

        // Loop through rows (assuming the first row contains column headers)
        foreach ($sheet->getRowIterator(2) as $row) {
            $data = $row->getCellIterator();
            $data->setIterateOnlyExistingCells(false); // Set this to iterate over all cells
            $values = array();
            foreach ($data as $cell) {
                $values[] = $cell->getValue();
            }

            // Extract data from each row
            $lastname = $values[0];
            $firstname = $values[1];
            $middlename = $values[2];
            $age = $values[3];
            $birthday = $values[4];
            $faculty_id = $values[5];
            $emailaddress = $values[6];
            $employment_status = $values[7];
            $prof_password = $values[8]; // Correct the order here
            $prof_username = $values[9];
            $prof_photo = 'profile.jpg'; // Default value for prof_photo

            // Hash the password
            $hashed_password = password_hash($prof_password, PASSWORD_DEFAULT);

            // Check if record with the same faculty_id already exists
            $check_stmt->bind_param("s", $faculty_id);
            $check_stmt->execute();
            $result = $check_stmt->get_result();

            // Bind parameters and execute the statement
            $stmt->bind_param("ssssissssss", $firstname, $lastname, $middlename, $age, $birthday, $faculty_id, $emailaddress, $employment_status, $prof_photo, $prof_username, $hashed_password);

            if ($result->num_rows > 0) {
                // Record exists, update it
                if ($stmt->execute()) {
                    $_SESSION['success'] = 'Data updated successfully';
                } else {
                    $_SESSION['error'] = 'Error updating data: ' . $stmt->error;
                }
            } else {
                // Record doesn't exist, insert it
                if ($stmt->execute()) {
                    $_SESSION['success'] = 'Data imported successfully';
                } else {
                    $_SESSION['error'] = 'Error inserting data: ' . $stmt->error;
                }
            }
        }
    } else {
        $_SESSION['error'] = 'Error uploading file';
    }
}

header('location: professor.php');
