<?php
session_start();
require_once 'database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['aId']) && isset($_FILES['aAvatar'])) {
        $aId = $_POST['aId'];

        // Check if the file was uploaded without errors
        if (isset($_FILES['aAvatar']) && $_FILES['aAvatar']['error'] === 0) {
            $fileTmpPath = $_FILES['aAvatar']['tmp_name'];
            $fileName = $_FILES['aAvatar']['name'];
            $fileSize = $_FILES['aAvatar']['size'];
            $fileType = $_FILES['aAvatar']['type'];
            $fileNameCmps = explode(".", $fileName);
            $fileExtension = strtolower(end($fileNameCmps));

            // Sanitize file name and set upload directory
            $newFileName = "user_" . $aId . "." . $fileExtension;
            $uploadFileDir = 'uploads/'; // Ensure this directory exists and is writable
            $dest_path = $uploadFileDir . $newFileName;

            // Allow only certain file formats (optional, but recommended)
            $allowedFileExtensions = array('jpg', 'png', 'jpeg', 'gif');

            if (in_array($fileExtension, $allowedFileExtensions)) {
                // Move the file to the correct directory
                if (move_uploaded_file($fileTmpPath, $dest_path)) {
                    // Update the user's avatar in the database
                    $updateQuery = "UPDATE admin SET aAvatar = ? WHERE aId = ?";
                    $stmt = $conn->prepare($updateQuery);
                    $stmt->bind_param("si", $dest_path, $aId);
                    
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
