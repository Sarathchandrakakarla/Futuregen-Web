<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
error_reporting(0);

if (isset($_POST['Action']) && $_POST['Action'] == "Student") {
    try {
        $filename = $_POST['FileName'];
        $filepath = $_POST['FilePath'];

        // Check if the directory exists
        $uploadDir = "../Images/" . $filepath;
        if (!file_exists($uploadDir)) {
            throw new Exception("Path does not exist");
        }

        // Check if file was uploaded and move it to the target directory
        if (isset($_FILES['File']) && move_uploaded_file($_FILES['File']['tmp_name'], $uploadDir . "/" . $filename)) {
            echo json_encode(["success" => true, "message" => "Image Uploaded Successfully"]);
        } else {
            echo json_encode(["success" => false, "message" => "Failed to Upload Image"]);
        }
    } catch (Exception $err) {
        echo json_encode(["success" => false, "message" => $err->getMessage()]);
    }
} else if (isset($_POST['Action']) && $_POST['Action'] == "Delete") {
    try {
        $filename = $_POST['FileName'];
        $filepath = "../Images/" . $_POST['FilePath'] . "/" . $filename;
        if (file_exists($filepath)) {
            unlink($filepath);
            echo json_encode(["success" => true, "message" => "Image Deleted Successfully"]);
        } else {
            echo json_encode(["success" => false, "message" => "File or Path Does not Exists"]);
        }
    } catch (Exception $err) {
        echo json_encode(["success" => false, "message" => $err->getMessage()]);
    }
} else if (isset($_POST['Action']) && $_POST['Action'] == "Homework") {
    try {
        $filename = $_POST['FileName'];
        $filepath = $_POST['FilePath'];

        // Check if the directory exists
        $uploadDir = "../Files/Homework/" . $filepath . '/';
        if (!file_exists("../Files/Homework/" . explode('/', $filepath)[0])) {         //Class Folder Exists
            mkdir("../Files/Homework/" . explode('/', $filepath)[0]);
            //throw new Exception("Path does not exist");
        }
        if (!file_exists($uploadDir)) {         //Class/Date Folder Exists
            mkdir($uploadDir);
            //throw new Exception("Path does not exist");
        }

        // Check if file was uploaded and move it to the target directory
        if (isset($_FILES['File']) && move_uploaded_file($_FILES['File']['tmp_name'], $uploadDir . $filename)) {
            echo json_encode(["success" => true, "message" => "Image Uploaded Successfully"]);
        } else {
            echo json_encode(["success" => false, "message" => "Failed to Upload Image"]);
        }
    } catch (Exception $err) {
        echo json_encode(["success" => false, "message" => $err->getMessage()]);
    }
} else if (isset($_POST['Action']) && $_POST['Action'] == "Homework_Delete") {
    try {
        $filename = $_POST['FileName'];
        $filepath = "../Files/Homework/" . $_POST['FilePath'] . "/" . $filename;
        if (file_exists($filepath)) {
            unlink($filepath);
            echo json_encode(["success" => true, "message" => "Image Deleted Successfully"]);
        } else {
            echo json_encode(["success" => false, "message" => "File or Path Does not Exists"]);
        }
    } catch (Exception $err) {
        echo json_encode(["success" => false, "message" => $err->getMessage()]);
    }
} else if (isset($_POST['Action']) && $_POST['Action'] == "Rename") {
    try {
        $folderpath = "../Files/Homework/" . $_POST['Path'];  // Path to the folder
        $subject = $_POST['Subject'];

        if (is_dir($folderpath)) {
            $all_files = scandir($folderpath);  // Get all files in the folder

            // Filter out '.' and '..' from the directory list
            $files = array_diff($all_files, array('.', '..'));

            // Initialize an array to store only the Subject .jpg files
            $sub_files = [];

            // Loop through the files and filter out only Subject .jpg files
            foreach ($files as $file) {
                // Check if the file starts with Subject and ends with ".jpg"
                if (strpos($file, $subject) === 0 && strtolower(pathinfo($file, PATHINFO_EXTENSION)) == 'jpg') {
                    $sub_files[] = $file;
                }
            }

            // Sort the Subject .jpg files (optional, to ensure the correct order)
            sort($sub_files);

            // Loop through the filtered Subject .jpg files and rename them
            $counter = 1;
            $flag = true;
            foreach ($sub_files as $file) {
                $file_info = pathinfo($file);  // Get file extension
                $ext = $file_info['extension'];

                // Construct new file name (e.g., Telugu1.jpg, Telugu2.jpg)
                $new_filename = $subject . $counter . "." . $ext;

                // Rename the file
                $old_path = $folderpath . "/" . $file;
                $new_path = $folderpath . "/" . $new_filename;

                if (rename($old_path, $new_path)) {
                    $flag = true;
                } else {
                    $flag = false;
                }

                $counter++;  // Increment counter for next file
            }
            echo json_encode(["success" => true, "message" => "All Files Renamed Successfully"]);
        } else {
            echo json_encode(["success" => true, "message" => "Path does not exist"]);
        }
    } catch (Exception $err) {
        echo json_encode(["success" => false, "message" => $err->getMessage()]);
    }
} else if (isset($_POST['Action']) && $_POST['Action'] == "Subject_Files_Delete") {
    try {
        $subject = $_POST['Subject'];
        $folderpath = "../Files/Homework/" . $_POST['FilePath'] . "/";

        if (is_dir($folderpath)) {
            $all_files = scandir($folderpath);  // Get all files in the folder

            // Filter out '.' and '..' from the directory list
            $files = array_diff($all_files, array('.', '..'));

            // Initialize an array to store only the Subject .jpg files
            $sub_files = [];

            // Loop through the files and filter out only Subject .jpg files
            foreach ($files as $file) {
                // Check if the file starts with Subject and ends with ".jpg"
                if (strpos($file, $subject) === 0) {
                    unlink($folderpath . $file);
                }
            }
            echo json_encode(["success" => true, "message" => "Files Deleted Successfully"]);
        } else {
            echo json_encode(["success" => false, "message" => "File or Path Does not Exists"]);
        }
    } catch (Exception $err) {
        echo json_encode(["success" => false, "message" => $err->getMessage()]);
    }
} else if (isset($_POST['Action']) && $_POST['Action'] == "Application_Creation") {
    try {
        $filename = $_POST['FileName'];
        $filepath = $_POST['FilePath'];

        // Check if the directory exists
        $uploadDir = "../Files/Applications/" . $filepath . '/';
        if (!file_exists($uploadDir)) {         //Branch Folder Exists
            mkdir($uploadDir);
        }

        // Check if file was uploaded and move it to the target directory
        if (isset($_FILES['File']) && move_uploaded_file($_FILES['File']['tmp_name'], $uploadDir . $filename)) {
            echo json_encode(["success" => true, "message" => "Application Uploaded Successfully"]);
        } else {
            echo json_encode(["success" => false, "message" => "Failed to Upload Application"]);
        }
    } catch (Exception $err) {
        echo json_encode(["success" => false, "message" => $err->getMessage()]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid Request"]);
}
