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
    $stmt = $conn->prepare("SELECT * FROM customer WHERE cEmail = ? OR google_uid = ?");
    $stmt->bind_param("ss", $email, $uid);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['logged_in'] = true;
        $_SESSION['google_user'] = [
            'displayName' => $displayName,
            'email' => $email,
            'photoURL' => $photoURL,
            'uid' => $uid
        ];
        echo "Google login session set successfully";
    } else {
        echo "User not found";
    }
    $stmt->close();
} else {
    echo "Failed to set Google login session";
}
?>
