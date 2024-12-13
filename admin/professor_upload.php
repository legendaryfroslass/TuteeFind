<?php
include 'includes/session.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

if (isset($_POST['upload'])) {
    if (isset($_FILES["excel_file"]) && $_FILES["excel_file"]["error"] == 0) {
        $file = $_FILES['excel_file']['tmp_name'];
        $fileName = $_FILES['excel_file']['name'];
        $fileType = mime_content_type($file);
        $allowedTypes = [
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        ];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedExtensions = ['xls', 'xlsx'];

        // Validate file type and extension
        if (in_array($fileType, $allowedTypes) && in_array($fileExtension, $allowedExtensions)) {
            try {
                require __DIR__ . '../../vendor/autoload.php';
                $spreadsheet = IOFactory::load($file);
                $sheet = $spreadsheet->getActiveSheet();

                // Expected headers
                $expectedHeaders = ['last name', 'first name', 'middle name', 'age', 'faculty id', 'email address', 'password', 'username'];

                // Extract headers from the Excel file
                $fileHeaders = [];
                foreach ($sheet->getRowIterator(1, 1) as $headerRow) {
                    $cells = $headerRow->getCellIterator();
                    $cells->setIterateOnlyExistingCells(false);
                    foreach ($cells as $cell) {
                        $fileHeaders[] = strtolower(trim($cell->getValue()));
                    }
                }

                // Remove empty headers and compare
                $fileHeaders = array_filter($fileHeaders);
                if ($fileHeaders !== $expectedHeaders) {
                    $_SESSION['error'] = 'Invalid file format. Please use the required template.';
                    header('location: professor');
                    exit;
                }

                // SQL statements for checking and inserting/updating
                $check_sql = "SELECT * FROM professor WHERE faculty_id = ?";
                $check_stmt = $conn->prepare($check_sql);
                $sql = "INSERT INTO professor (firstname, lastname, middlename, age, faculty_id, emailaddress, prof_photo, prof_username, prof_password)
                        VALUES (?, ?, ?, ?, ?, ?, COALESCE(?, 'profile.jpg'), ?, ?)
                        ON DUPLICATE KEY UPDATE 
                        firstname = VALUES(firstname),
                        lastname = VALUES(lastname),
                        middlename = VALUES(middlename),
                        age = VALUES(age),
                        emailaddress = VALUES(emailaddress),
                        prof_photo = COALESCE(VALUES(prof_photo), 'profile.jpg'),
                        prof_username = VALUES(prof_username),
                        prof_password = VALUES(prof_password)";
                $stmt = $conn->prepare($sql);

                foreach ($sheet->getRowIterator(2) as $row) {
                    $data = $row->getCellIterator();
                    $data->setIterateOnlyExistingCells(false);
                    $values = array();

                    foreach ($data as $cell) {
                        $values[] = $cell->getValue();
                    }

                    $lastname = $values[0];
                    $firstname = $values[1];
                    $middlename = $values[2];
                    $age = $values[3];
                    $faculty_id = $values[4];
                    $emailaddress = $values[5];
                    $prof_password = $values[6];
                    $prof_username = $values[7];
                    $prof_photo = 'profile.jpg';
                    $hashed_password = password_hash($prof_password, PASSWORD_DEFAULT);

                    // Check for duplicate faculty_id
                    $check_stmt->bind_param("s", $faculty_id);
                    $check_stmt->execute();
                    $result = $check_stmt->get_result();

                    $stmt->bind_param("sssssssss", $firstname, $lastname, $middlename, $age, $faculty_id, $emailaddress, $prof_photo, $prof_username, $hashed_password);

                    if ($result->num_rows > 0) {
                        if (!$stmt->execute()) {
                            $_SESSION['error'] = 'Error updating data: ' . $stmt->error;
                        }
                    } else {
                        if (!$stmt->execute()) {
                            $_SESSION['error'] = 'Error inserting data: ' . $stmt->error;
                        }
                    }
                }

                $_SESSION['success'] = 'Data processed successfully';
            } catch (Exception $e) {
                $_SESSION['error'] = 'Error reading Excel file: ' . $e->getMessage();
            }
        } else {
            $_SESSION['error'] = 'Invalid file type. Please upload an Excel file (.xls or .xlsx).';
        }
    } else {
        $_SESSION['error'] = 'Error uploading file. Please try again.';
    }

    header('location: professor');
}
?>
