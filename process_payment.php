<?php
    session_start();
    require_once 'database.php';

    // Check if the required session variables are set
    if (isset($_SESSION['cName']) && isset($_SESSION['cEmail']) && isset($_SESSION['cPhone']) &&
        isset($_SESSION['datestart']) && isset($_SESSION['dateend']) && isset($_SESSION['courtType']) && isset($_SESSION['people']) &&
        isset($_SESSION['totalPrice'])) {

        $cName = $_SESSION['cName'];
        $cEmail = $_SESSION['cEmail'];
        $cPhone = $_SESSION['cPhone'];
        $datestart = $_SESSION['datestart'];
        $dateend = $_SESSION['dateend'];
        $courtType = $_SESSION['courtType'];
        $people = $_SESSION['people'];
        $totalPrice = $_SESSION['totalPrice'];

        // Payment processing logic (assuming payment success)...
        $transaction_id = "sandbox_transaction_id"; // Simulating a transaction ID for sandbox testing

        // Insert the booking into the database
        $insert_sql = "INSERT INTO bookings (cName, cEmail, cPhone, datestart, dateend, courtType, people, price, payment_status, transaction_id) 
                    VALUES ('$cName', '$cEmail', '$cPhone', '$datestart', '$dateend', '$courtType', '$people', '$totalPrice', 'Paid', '$transaction_id')";

        if (mysqli_query($conn, $insert_sql)) {
            header("Location: receipt.php?booking_id=" . mysqli_insert_id($conn));
            exit();
        } else {
            echo "Error: " . $insert_sql . "<br>" . mysqli_error($conn);
        }
    } else {
        echo "Missing session data. Please start the booking process again.";
    }

    mysqli_close($conn);
?>
