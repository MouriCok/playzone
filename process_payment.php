<?php
    session_start();
    require_once 'database.php';

    // Check if the required session variables are set
    if (isset($_SESSION['cName']) && isset($_SESSION['cEmail']) && isset($_SESSION['cPhone']) &&
        isset($_SESSION['datestart']) && isset($_SESSION['dateend']) && isset($_SESSION['courtType']) &&
        isset($_SESSION['people']) && isset($_SESSION['totalPrice']) && isset($_SESSION['preferredCourt']) 
        && isset($_SESSION['court_id']) && isset($_SESSION['booking_id'])) {

        $cName = $_SESSION['cName'];
        $cEmail = $_SESSION['cEmail'];
        $cPhone = $_SESSION['cPhone'];
        $datestart = $_SESSION['datestart'];
        $dateend = $_SESSION['dateend'];
        $courtType = $_SESSION['courtType'];
        $people = $_SESSION['people'];
        $totalPrice = $_SESSION['totalPrice'];
        $preferredCourt = $_SESSION['preferredCourt'];
        $court_id = $_SESSION['court_id'];
        $booking_id = $_SESSION['booking_id']; // The existing booking ID to update

        // Payment processing logic (assuming payment success)
        $transaction_id = "sandbox_transaction_id"; // Simulating a transaction ID for sandbox testing

        // Update the booking record with the new payment status and transaction ID
        $update_sql = "UPDATE bookings 
                    SET payment_status = 'Paid', transaction_id = ? 
                    WHERE bID = ?";

        // Prepare the update statement
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("si", $transaction_id, $booking_id); // Bind transaction ID and booking ID

        if ($stmt->execute()) {
            // Redirect to the receipt page with the booking ID after successful update
            header("Location: receipt.php?booking_id=" . $booking_id);
            exit();
        } else {
            // Handle error during the update
            echo "Error: " . $stmt->error;
        }
    } else {
        // If session data is missing, restart the booking process
        echo "Missing session data. Please start the booking process again.";
    }

    mysqli_close($conn);
?>
