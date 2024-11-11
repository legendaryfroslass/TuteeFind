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

        // Prepare the SQL statement for inserting or updating records
        $sql = "INSERT INTO tutor (firstname, lastname, age, sex, number, barangay, student_id, course, year_section, professor, fblink, emailaddress, password, bio
        ) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) 
                ON DUPLICATE KEY UPDATE 
                firstname = VALUES(firstname), 
                lastname = VALUES(lastname), 
                age = VALUES(age), 
                sex = VALUES(sex),
                number = VALUES(number),
                barangay = VALUES(barangay),
                student_id = VALUES(student_id),
                course = VALUES(course),
                year_section = VALUES(year_section),
                professor = VALUES(professor),
                fblink = VALUES(fblink),
                emailaddress = VALUES(emailaddress),
                password = VALUES(password),
                bio = VALUES(bio)";
                
        $stmt = $conn->prepare($sql);

        // Loop through rows (assuming the first row contains column headers)
        foreach ($sheet->getRowIterator(2) as $row) {
            $data = $row->getCellIterator();
            $values = array();
            foreach ($data as $cell) {
                $values[] = $cell->getValue();
            }

            // Extract data from each row
            $firstname = $values[0];
            $lastname = $values[1];
            $age = $values[2];
            $sex = $values[3];
            $number = $values[4];
            $barangay = $values[5];
            $student_id = $values[6];
            $course = $values[7];
            $year_section = $values[8];
            $professor = $values[9];
            $fblink = $values[10];
            $emailaddress = $values[11];
            $password = password_hash($values[12], PASSWORD_DEFAULT); // Hash the password
            $bio = $values[13];

            // Bind parameters and execute the statement
            $stmt->bind_param("ssisssssssssss", $firstname, $lastname, $age, $sex, $number, $barangay, $student_id, $course, $year_section, $professor, $fblink, $emailaddress, $password, $bio);

            // Execute the statement
            if ($stmt->execute()) {
                $_SESSION['success'] = 'Data imported successfully';
                header('location: tutor.php');
            } else {
                $_SESSION['error'] = 'Error inserting data: ' . $stmt->error;
                header('location: tutor.php');
            }
        }
    } else {
        $_SESSION['error'] = 'Error uploading file';
    }
}

header('location: tutor.php');
?>
