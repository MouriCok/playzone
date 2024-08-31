<?php
require_once 'database.php';

// Check if the request contains the necessary parameters
if (isset($_POST['id'], $_POST['name'], $_POST['email'], $_POST['phone'])) {
    // Sanitize the input to prevent SQL injection
    $playerId = mysqli_real_escape_string($conn, $_POST['id']);
    $editName = mysqli_real_escape_string($conn, $_POST['name']);
    $editEmail = mysqli_real_escape_string($conn, $_POST['email']);
    $editPhone = mysqli_real_escape_string($conn, $_POST['phone']);

    // Perform the update operation
    $editQuery = "UPDATE user SET name = '$editName', email = '$editEmail', phone = '$editPhone' WHERE id = $playerId";

    if (mysqli_query($conn, $editQuery)) {
        // Redirect back to the profile page or any other page
        header("Location: profile.php");
        exit();
    } else {
        // Error message, you can customize this response
        echo "Error editing profile: " . mysqli_error($conn);
    }
} else {
    // If required parameters are not provided in the request
    echo "Invalid request. Please provide all required parameters.";
}

// Close the database connection
mysqli_close($conn);
?>
