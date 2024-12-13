<?php
include 'includes/session.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

if (isset($_POST['upload'])) {
    // Check if file was uploaded without errors
    if (isset($_FILES["excel_file"]) && $_FILES["excel_file"]["error"] == 0) {
        $file = $_FILES['excel_file']['tmp_name'];
        $fileName = $_FILES['excel_file']['name'];
        $fileType = mime_content_type($file);
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedTypes = [
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        ];
        $allowedExtensions = ['xls', 'xlsx'];

        // Validate file type and extension
        if (in_array($fileType, $allowedTypes) && in_array($fileExtension, $allowedExtensions)) {
            try {
                // Load Excel file using PhpSpreadsheet
                require __DIR__ . '../../vendor/autoload.php';
                $spreadsheet = IOFactory::load($file);
                $sheet = $spreadsheet->getActiveSheet();

                // Expected headers
                $expectedHeaders = [
                    'Firstname', 'Lastname', 'Age', 'Sex', 'Number', 'Barangay',
                    'Student ID', 'Course', 'Year & Section', 'Professor Faculty ID',
                    'fblink', 'Email Address', 'Password', 'Bio'
                ];

                // Extract headers from the file
                $fileHeaders = [];
                foreach ($sheet->getRowIterator(1, 1) as $headerRow) {
                    $cells = $headerRow->getCellIterator();
                    $cells->setIterateOnlyExistingCells(false); // Include all cells in the row
                    foreach ($cells as $cell) {
                        $fileHeaders[] = trim($cell->getValue());
                    }
                }

                // Remove empty headers and compare
                $fileHeaders = array_filter($fileHeaders);
                if ($fileHeaders !== $expectedHeaders) {
                    $_SESSION['error'] = 'Invalid file format. Please use the required template.';
                    header('location: tutor');
                    exit;
                }

                // Prepare the SQL statement for inserting or updating records
                $sql = "INSERT INTO tutor (firstname, lastname, age, sex, number, barangay, student_id, course, year_section, professor, fblink, emailaddress, password, bio) 
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

                // Loop through rows (starting from the second row for data)
                foreach ($sheet->getRowIterator(2) as $row) {
                    $data = $row->getCellIterator();
                    $data->setIterateOnlyExistingCells(false);
                    $values = [];
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

                    if (!$stmt->execute()) {
                        $_SESSION['error'] = 'Error inserting data: ' . $stmt->error;
                        header('location: tutor');
                        exit();
                    }
                }

                $_SESSION['success'] = 'Data imported successfully';
                header('location: tutor');
            } catch (Exception $e) {
                $_SESSION['error'] = 'Error reading Excel file: ' . $e->getMessage();
                header('location: tutor');
            }
        } else {
            $_SESSION['error'] = 'Invalid file type. Please upload an Excel file (.xls or .xlsx).';
            header('location: tutor');
        }
    } else {
        $_SESSION['error'] = 'Error uploading file. Please try again.';
        header('location: tutor');
    }
}
?>
