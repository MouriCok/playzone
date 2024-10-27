<?php
session_start();
require_once 'database.php';

$data = json_decode(file_get_contents("php://input"), true);

if (isset($data['email'])) {
    $email = $data['email'];
    $displayName = $data['displayName'];
    $photoURL = $data['photoURL'];
    $uid = $data['uid'];

    // Check if the user exists in the customer table
    $stmt = $conn->prepare("SELECT * FROM customer WHERE cEmail = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        // User not found, insert a new record
        $insertStmt = $conn->prepare("INSERT INTO customer (cUser, cEmail, cAvatar, google_uid) VALUES (?, ?, ?, ?)");
        $insertStmt->bind_param("ssss", $displayName, $email, $photoURL, $uid);
        if ($insertStmt->execute()) {
            echo "New Google user registered.";
        } else {
            echo "Error registering Google user: " . $insertStmt->error;
            exit;
        }
        $insertStmt->close();
    } else {
        echo "Existing Google user logged in.";
    }
    $stmt->close();

    // Set session variables for the Google user
    $_SESSION['logged_in'] = true;
    $_SESSION['google_user'] = [
        'displayName' => $displayName,
        'email' => $email,
        'photoURL' => $photoURL,
        'uid' => $uid
    ];

    echo "Google session set successfully";
} else {
    echo "Failed to set Google session";
}
?>
