<?php
include 'includes/session.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

if (isset($_POST['upload'])) {
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

        if (in_array($fileType, $allowedTypes) && in_array($fileExtension, $allowedExtensions)) {
            try {
                require __DIR__ . '../../vendor/autoload.php';
                $spreadsheet = IOFactory::load($file);
                $sheet = $spreadsheet->getActiveSheet();

                $expectedHeaders = [
                    'Firstname', 'Lastname', 'Age', 'Sex', 'Number', 'Barangay',
                    'Student ID', 'Course', 'Year & Section', 'Professor Faculty ID',
                    'fblink', 'Email Address', 'Password', 'Bio'
                ];

                $fileHeaders = [];
                foreach ($sheet->getRowIterator(1, 1) as $headerRow) {
                    $cells = $headerRow->getCellIterator();
                    $cells->setIterateOnlyExistingCells(false);
                    foreach ($cells as $cell) {
                        $fileHeaders[] = trim($cell->getValue());
                    }
                }

                $fileHeaders = array_filter($fileHeaders);
                if ($fileHeaders !== $expectedHeaders) {
                    $_SESSION['error'] = 'Invalid file format. Please use the required template.';
                    header('location: tutor');
                    exit;
                }

                $sql = "INSERT INTO tutor (firstname, lastname, age, sex, number, barangay, student_id, course, year_section, professor, fblink, emailaddress, password, bio) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) 
                        ON DUPLICATE KEY UPDATE 
                        firstname = VALUES(firstname), 
                        lastname = VALUES(lastname), 
                        age = VALUES(age), 
                        sex = VALUES(sex),
                        number = VALUES(number),
                        barangay = VALUES(barangay),
                        course = VALUES(course),
                        year_section = VALUES(year_section),
                        professor = VALUES(professor),
                        fblink = VALUES(fblink),
                        emailaddress = VALUES(emailaddress),
                        password = VALUES(password),
                        bio = VALUES(bio)";
                $stmt = $conn->prepare($sql);

                $updatedStudentIds = [];
                foreach ($sheet->getRowIterator(2) as $row) {
                    $data = $row->getCellIterator();
                    $data->setIterateOnlyExistingCells(false);
                    $values = [];
                    foreach ($data as $cell) {
                        $values[] = $cell->getValue();
                    }

                    if (array_filter($values) === []) {
                        continue;
                    }

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
                    $password = password_hash($values[12], PASSWORD_DEFAULT);
                    $bio = $values[13];

                    $stmt->bind_param("ssisssssssssss", $firstname, $lastname, $age, $sex, $number, $barangay, $student_id, $course, $year_section, $professor, $fblink, $emailaddress, $password, $bio);

                    if (!$stmt->execute()) {
                        $_SESSION['error'] = 'Error inserting data: ' . $stmt->error;
                        header('location: tutor');
                        exit();
                    }

                    if ($stmt->affected_rows === 2) { // Indicates an update occurred
                        $updatedStudentIds[] = $student_id;
                    }
                }

                $message = 'Data imported successfully';
                if (!empty($updatedStudentIds)) {
                    $message .= '. Updated student IDs: ' . implode(', ', $updatedStudentIds);
                }

                $_SESSION['success'] = $message;
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
