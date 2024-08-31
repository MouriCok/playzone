<?php
require_once 'database.php';

// Check if the request contains the id parameter
if (isset($_POST['id'])) {
    // Sanitize the input to prevent SQL injection
    $id = mysqli_real_escape_string($conn, $_POST['id']);

    // Perform the delete operation
    $deleteQuery = "DELETE FROM menu WHERE id = $id";

    if (mysqli_query($conn, $deleteQuery)) {
        // Redirect back to the profile page or any other page
        header("Location: profile.php");
        exit();
    } else {
        // Error message, you can customize this response
        echo "Error deleting menu: " . mysqli_error($conn);
    }
} else {
    // If id is not provided in the request
    echo "Invalid request. Data not exist";
}

// Close the database connection
mysqli_close($conn);
?>