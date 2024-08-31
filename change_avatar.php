<?php
session_start();
require_once 'database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['cId']) && isset($_FILES['cAvatar'])) {
        $cId = $_POST['cId'];

        // Check if the file was uploaded without errors
        if (isset($_FILES['cAvatar']) && $_FILES['cAvatar']['error'] === 0) {
            $fileTmpPath = $_FILES['cAvatar']['tmp_name'];
            $fileName = $_FILES['cAvatar']['name'];
            $fileSize = $_FILES['cAvatar']['size'];
            $fileType = $_FILES['cAvatar']['type'];
            $fileNameCmps = explode(".", $fileName);
            $fileExtension = strtolower(end($fileNameCmps));

            // Sanitize file name and set upload directory
            $newFileName = "user_" . $cId . "." . $fileExtension;
            $uploadFileDir = 'uploads/'; // Ensure this directory exists and is writable
            $dest_path = $uploadFileDir . $newFileName;

            // Allow only certain file formats (optional, but recommended)
            $allowedFileExtensions = array('jpg', 'png', 'jpeg', 'gif');

            if (in_array($fileExtension, $allowedFileExtensions)) {
                // Move the file to the correct directory
                if (move_uploaded_file($fileTmpPath, $dest_path)) {
                    // Update the user's avatar in the database
                    $updateQuery = "UPDATE customer SET cAvatar = ? WHERE cId = ?";
                    $stmt = $conn->prepare($updateQuery);
                    $stmt->bind_param("si", $dest_path, $cId);
                    
                    if ($stmt->execute()) {
                        echo "Avatar updated successfully!";
                    } else {
                        echo "Error updating avatar: " . $stmt->error;
                    }
                    $stmt->close();
                } else {
                    echo "There was an error moving the file.";
                }
            } else {
                echo "Invalid file format.";
            }
        } else {
            echo "No file uploaded or there was an error with the upload.";
        }
    } else {
        echo "Required data is missing.";
    }
} else {
    echo "Invalid request method.";
}
?>
