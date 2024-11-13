<?php
require 'database.php';
if (isset($_POST['bID'], $_POST['payment_status'])) {
    $bID = $_POST['bID'];
    $payment_status = $_POST['payment_status'];
    $query = "UPDATE bookings SET payment_status='$payment_status' WHERE bID='$bID'";
    if (mysqli_query($conn, $query)) {
        echo "Booking status updated successfully.";
    } else {
        echo "Error updating status: " . mysqli_error($conn);
    }
}
?>
