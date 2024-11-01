<?php
require_once 'database.php';

// function logMessage($message) {
//     $logFile = 'move_bookings_log.txt';
//     file_put_contents($logFile, date('Y-m-d H:i:s') . " - $message" . PHP_EOL, FILE_APPEND);
// }

function moveExpiredBookings() {
    global $conn;

    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }

    $currentDateTime = date('Y-m-d H:i:s');

    // Select expired bookings where the end date has passed
    $expiredBookingsQuery = "SELECT * FROM bookings WHERE dateend < '$currentDateTime'";
    $expiredBookingsResult = mysqli_query($conn, $expiredBookingsQuery);

    if ($expiredBookingsResult && mysqli_num_rows($expiredBookingsResult) > 0) {
        $movedCount = 0;
        while ($booking = mysqli_fetch_assoc($expiredBookingsResult)) {
            $insertHistoryQuery = "
                INSERT INTO booking_history (bID, cEmail, courtType, preferredCourt, datestart, dateend, people, payment_status)
                VALUES ('{$booking['bID']}', '{$booking['cEmail']}', '{$booking['courtType']}', '{$booking['preferredCourt']}',
                        '{$booking['datestart']}', '{$booking['dateend']}', '{$booking['people']}', '{$booking['payment_status']}')";
            
            if (mysqli_query($conn, $insertHistoryQuery)) {
                $deleteBookingQuery = "DELETE FROM bookings WHERE bID = '{$booking['bID']}'";
                if (mysqli_query($conn, $deleteBookingQuery)) {
                    $movedCount++;
                }
            }
        }
        return $movedCount; // Return how many bookings were moved
    }

    return 0; // No expired bookings to move
}

function checkAndMoveBookings() {
    $movedCount = moveExpiredBookings();

    // Log the action
    if ($movedCount > 0) {
        // logMessage("Expired bookings moved: $movedCount");
        return "Expired bookings moved to history."; // Return message to show notification
    } else {
        // logMessage("No expired bookings to move.");
        return ""; // No need to show notification if nothing happened
    }
}

// Call the function and capture the message
$statusMessage = checkAndMoveBookings();
?>
