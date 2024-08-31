<?php
require_once 'database.php';

// Check if the request contains the necessary parameters
if (isset($_POST['id'], $_POST['name'], $_POST['price'])) {
    // Sanitize the input to prevent SQL injection
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $MenuName = mysqli_real_escape_string($conn, $_POST['name']);
    $MenuPrice = mysqli_real_escape_string($conn, $_POST['price']);

    // Perform the update operation
    $updateQuery = "UPDATE menu SET name = '$MenuName', price = '$MenuPrice' WHERE id = $id";

    if (mysqli_query($conn, $updateQuery)) {
        // Redirect back to the profile page or any other page
        header("Location: profile.php");
        exit();
    } else {
        // Error message, you can customize this response
        echo "Error updating menu: " . mysqli_error($conn);
    }
} else {
    // If required parameters are not provided in the request
    echo "Invalid request. Please provide all required parameters.";
}

// Close the database connection
mysqli_close($conn);
?>
