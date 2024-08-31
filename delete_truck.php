<?php
require_once 'database.php';

// Check if the request contains the foodtruckId parameter
if (isset($_POST['foodtruckId'])) {
    // Sanitize the input to prevent SQL injection
    $foodtruckId = mysqli_real_escape_string($conn, $_POST['foodtruckId']);

    // Perform the delete operation
    $deleteQuery = "DELETE FROM foodtruck WHERE foodtruckId = $foodtruckId";

    if (mysqli_query($conn, $deleteQuery)) {
        // Redirect back to the profile page or any other page
        header("Location: profile.php");
        exit();
    } else {
        // Error message, you can customize this response
        echo "Error deleting foodtruck: " . mysqli_error($conn);
    }
} else {
    // If foodtruckId is not provided in the request
    echo "Invalid request. Data not exist";
}

// Close the database connection
mysqli_close($conn);
?>