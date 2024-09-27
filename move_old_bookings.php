<?php
require_once 'database.php';

function getLastRunTime() {
    // This function retrieves the last run time from a file or a database.
    // Example: Read from a file
    $file = 'last_run_time.txt';

    if (file_exists($file)) {
        $lastRunTime = file_get_contents($file);
        return new DateTime($lastRunTime); // Return as DateTime object
    }

    // If file doesn't exist, return yesterday midnight as default
    return new DateTime('yesterday midnight');
}

function setLastRunTime($currentDateTime) {
    // This function stores the last run time into a file
    $file = 'last_run_time.txt';
    file_put_contents($file, $currentDateTime->format('Y-m-d H:i:s')); // Save the current time
}

function moveExpiredBookings() {
    global $conn;

    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }

    // Get the current time
    $currentDateTime = date('Y-m-d H:i:s');

    // Select expired bookings where the end date has passed
    $expiredBookingsQuery = "SELECT * FROM bookings WHERE dateend < '$currentDateTime'";
    $expiredBookingsResult = mysqli_query($conn, $expiredBookingsQuery);

    if ($expiredBookingsResult && mysqli_num_rows($expiredBookingsResult) > 0) {
        while ($booking = mysqli_fetch_assoc($expiredBookingsResult)) {
            // Insert the expired booking into booking_history
            $insertHistoryQuery = "
                INSERT INTO booking_history (bID, cEmail, courtType, preferredCourt, datestart, dateend, people, payment_status)
                VALUES ('{$booking['bID']}', '{$booking['cEmail']}', '{$booking['courtType']}', '{$booking['preferredCourt']}',
                        '{$booking['datestart']}', '{$booking['dateend']}', '{$booking['people']}', '{$booking['payment_status']}')";
            
            if (mysqli_query($conn, $insertHistoryQuery)) {
                // After successful insert, delete the booking from the main table
                $deleteBookingQuery = "DELETE FROM bookings WHERE bID = '{$booking['bID']}'";
                mysqli_query($conn, $deleteBookingQuery);
            }
        }
    } else {
        echo "No expired bookings to move.";
    }

    mysqli_close($conn);
}

function checkAndMoveBookings() {
    $currentDateTime = new DateTime();   // Get current date and time
    $midnightToday = new DateTime('today midnight');   // Get today's midnight
    
    $lastRunTime = getLastRunTime();   // Get the last run time from file or DB

    // If last run time is before today's midnight, we haven't run the script today
    if ($lastRunTime < $midnightToday) {
        // Move expired bookings to history
        moveExpiredBookings();
        
        // Update the last run time to now
        setLastRunTime($currentDateTime);
        echo "Expired bookings moved to history.";
    } else {
        // echo "Script has already run today. No action needed.";
    }
}

// Call the function to check if it needs to run
checkAndMoveBookings();
?>
