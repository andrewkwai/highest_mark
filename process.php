<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if a file was uploaded
    if (isset($_FILES["csv_file"])) {
        $file_error = $_FILES["csv_file"]["error"];
        
        // Check for upload errors
        if ($file_error === UPLOAD_ERR_OK) {
            $file_name = $_FILES["csv_file"]["name"];
            $tmp_name = $_FILES["csv_file"]["tmp_name"];

            // Check if the uploaded file is a CSV file
            $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);
            if (strtolower($file_extension) === "csv") {
                // Process the CSV file
                $data = [];
                if (($handle = fopen($tmp_name, "r")) !== false) {
                    while (($row = fgetcsv($handle, 1000, ",")) !== false) {
                        // Filter rows based on "Enroll Status" and "Report Period" criteria
                        if ($row[8] === "Active" && $row[9] === "F1") {
                            // Extract the first 6 characters from "Course-Section"
                            $course_section = substr($row[3], 0, 6);
                            $mark = (float) $row[10];
                            $student = $row[4];
                            if (!isset($data[$course_section])) {
                                // Initialize data for this course
                                $data[$course_section] = [
                                    "students" => [],
                                    "highest_mark" => $mark
                                ];
                            }
                            if ($mark > $data[$course_section]["highest_mark"]) {
                                // New highest mark for this course
                                $data[$course_section]["students"] = [$student];
                                $data[$course_section]["highest_mark"] = $mark;
                            } elseif ($mark === $data[$course_section]["highest_mark"]) {
                                // Tie for the highest mark
                                $data[$course_section]["students"][] = $student;
                            }
                        }
                    }
                    fclose($handle);
                }

                // Save the results as JSON
                $json_data = json_encode($data);
                file_put_contents("results.json", $json_data);

                // Redirect to results.html
                header("Location: results.html");
                exit;
            } else {
                echo "Please upload a valid CSV file.";
            }
        } else {
            // Display specific upload error message
            switch ($file_error) {
                case UPLOAD_ERR_INI_SIZE:
                    echo "The uploaded file exceeds the upload_max_filesize directive in php.ini.";
                    break;
                case UPLOAD_ERR_FORM_SIZE:
                    echo "The uploaded file exceeds the MAX_FILE_SIZE directive specified in the HTML form.";
                    break;
                case UPLOAD_ERR_PARTIAL:
                    echo "The uploaded file was only partially uploaded.";
                    break;
                case UPLOAD_ERR_NO_FILE:
                    echo "No file was uploaded.";
                    break;
                case UPLOAD_ERR_NO_TMP_DIR:
                    echo "Missing temporary folder for file uploads.";
                    break;
                case UPLOAD_ERR_CANT_WRITE:
                    echo "Failed to write the file to disk.";
                    break;
                case UPLOAD_ERR_EXTENSION:
                    echo "A PHP extension stopped the file upload.";
                    break;
                default:
                    echo "Unknown upload error.";
                    break;
            }
        }
    } else {
        echo "No file uploaded.";
    }
}
?>
