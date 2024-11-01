<?php
session_start();
require_once 'database.php';

$data = json_decode(file_get_contents("php://input"), true);

if (isset($data['email'])) {
    $email = $data['email'];
    $displayName = $data['displayName'];
    $photoURL = $data['photoURL'];
    $uid = $data['uid'];

    // Check if the user already exists in the customer table
    $stmt = $conn->prepare("SELECT * FROM customer WHERE cEmail = ? OR google_uid = ?");
    $stmt->bind_param("ss", $email, $uid);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // User already registered
        echo "Account already registered";
    } else {
        // Register a new Google user
        $insertStmt = $conn->prepare("INSERT INTO customer (cUser, cEmail, cAvatar, google_uid) VALUES (?, ?, ?, ?)");
        $insertStmt->bind_param("ssss", $displayName, $email, $photoURL, $uid);
        if ($insertStmt->execute()) {
            echo "New Google user registered successfully";
        } else {
            echo "Error registering Google user: " . $insertStmt->error;
            exit;
        }
        $insertStmt->close();

        // Set session variables for the newly registered Google user
        $_SESSION['logged_in'] = true;
        $_SESSION['google_user'] = [
            'displayName' => $displayName,
            'email' => $email,
            'photoURL' => $photoURL,
            'uid' => $uid
        ];
    }
    $stmt->close();
} else {
    echo "Failed to set Google signup session";
}
?>
