<?php
require_once 'database.php';

// Check if the request contains the necessary parameters
if (isset($_POST['foodtruckId'], $_POST['ftname'], $_POST['email'], $_POST['phone'], $_POST['latitude'], $_POST['longitude'], $_POST['dayClose'], $_POST['timeOpen'], $_POST['timeClose'])) {
    // Sanitize the input to prevent SQL injection
    $foodtruckId = mysqli_real_escape_string($conn, $_POST['foodtruckId']);
    $updatedName = mysqli_real_escape_string($conn, $_POST['ftname']);
    $updatedEmail = mysqli_real_escape_string($conn, $_POST['email']);
    $updatedPhone = mysqli_real_escape_string($conn, $_POST['phone']);
    $updatedLat = mysqli_real_escape_string($conn, $_POST['latitude']);
    $updatedLong = mysqli_real_escape_string($conn, $_POST['longitude']);
    $updatedDayClose = mysqli_real_escape_string($conn, $_POST['dayClose']);
    $updatedTimeOpen = mysqli_real_escape_string($conn, $_POST['timeOpen']);
    $updatedTimeClose = mysqli_real_escape_string($conn, $_POST['timeClose']);

    // Perform the update operation
    $updateQuery = "UPDATE foodtruck SET ftname = '$updatedName', email = '$updatedEmail', phone = '$updatedPhone', latitude = '$updatedLat', longitude = '$updatedLong', dayClose = '$updatedDayClose', timeOpen = '$updatedTimeOpen', timeClose = '$updatedTimeClose' WHERE foodtruckId = $foodtruckId";

    if (mysqli_query($conn, $updateQuery)) {
        // Redirect back to the profile page or any other page
        header("Location: profile.php");
        exit();
    } else {
        // Error message, you can customize this response
        echo "Error updating foodtruck: " . mysqli_error($conn);
    }
} else {
    // If required parameters are not provided in the request
    echo "Invalid request. Please provide all required parameters.";
}

// Close the database connection
mysqli_close($conn);
?>
