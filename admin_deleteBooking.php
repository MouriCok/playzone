<?php
require_once 'database.php';

if (isset($_POST['bID'])) {
    // Sanitize the input to prevent SQL injection
    $bID = mysqli_real_escape_string($conn, $_POST['bID']);

    // Perform the delete operation
    $deleteQuery = "DELETE FROM bookings WHERE bID = $bID";

    if (mysqli_query($conn, $deleteQuery)) {
        header("Location: booking_list.php");
        exit();
    } else {
        // Error message, you can customize this response
        echo "Error deleting booking: " . mysqli_error($conn);
    }
} else {
    // If bookingId is not provided in the request
    echo "Invalid request. Data not exist";
}

// Close the database connection
mysqli_close($conn);
?>