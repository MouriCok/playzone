<?php
require_once 'database.php';

// Set the correct timezone to ensure the time is displayed in the local timezone
date_default_timezone_set('Asia/Kuala_Lumpur'); // Set this to your local timezone

if (isset($_POST['courtType'])) {
    $courtType = $_POST['courtType'];

    // Get current day of the week (e.g., Monday, Tuesday)
    $current_day = date('l'); // Full name of the day, compatible with ENUM in `premises_hours`

    // Fetch the premises' open and close times for the current day
    $sql_hours = "SELECT open_time, close_time FROM premises_hours WHERE day_of_week = ?";
    $stmt_hours = $conn->prepare($sql_hours);
    $stmt_hours->bind_param("s", $current_day);
    $stmt_hours->execute();
    $result_hours = $stmt_hours->get_result();
    $premises_hours = $result_hours->fetch_assoc();

    if (!$premises_hours) {
        echo json_encode(['error' => "The premises are closed on $current_day."]);
        exit;
    }

    // Get the current time in the correct timezone
    $current_time = new DateTime(); // This will use the timezone set by `date_default_timezone_set`
    $open_time = new DateTime($premises_hours['open_time']);
    $close_time = new DateTime($premises_hours['close_time']);

    // Ensure the timeslot starts at the correct time, not before opening hours
    if ($current_time < $open_time) {
        $start_time = clone $open_time;
    } else {
        // Round the current time to the nearest 2-hour block
        $current_hour = (int)$current_time->format('H');
        $start_hour = $current_hour - ($current_hour % 2); // Round down to nearest even hour
        $start_time = new DateTime($start_hour . ':00:00');
    }

    // Calculate the timeslot for the next 2 hours or until closing time
    $end_time = clone $start_time;
    $end_time->modify('+2 hours');
    if ($end_time > $close_time) {
        $end_time = clone $close_time;
    }

    $timeslot = $start_time->format('H:i') . " - " . $end_time->format('H:i');

    // Fetch the total courts for the selected court type from `court_count`
    $sql_total = "SELECT total_courts FROM court_count WHERE courtType = ?";
    $stmt_total = $conn->prepare($sql_total);
    $stmt_total->bind_param("s", $courtType);
    $stmt_total->execute();
    $result_total = $stmt_total->get_result();
    $total_courts_data = $result_total->fetch_assoc();

    if ($total_courts_data) {
        $total_courts = $total_courts_data['total_courts'];

        // Count the number of bookings for the current time range using `bookings`
        $sql_bookings = "
            SELECT COUNT(*) as booked_courts 
            FROM bookings 
            WHERE courtType = ? 
            AND (datestart <= ? AND dateend >= ?)";
        $stmt_bookings = $conn->prepare($sql_bookings);
        $start_time_str = $start_time->format('Y-m-d H:i:s');
        $end_time_str = $end_time->format('Y-m-d H:i:s');
        $stmt_bookings->bind_param("sss", $courtType, $end_time_str, $start_time_str);
        $stmt_bookings->execute();
        $result_bookings = $stmt_bookings->get_result();
        $bookings_data = $result_bookings->fetch_assoc();

        $booked_courts = $bookings_data ? $bookings_data['booked_courts'] : 0;

        // Calculate the number of available courts
        $available_courts = $total_courts - $booked_courts;
        if ($available_courts < 0) {
            $available_courts = 0; // Prevent negative values
        }

        // Return the response as JSON
        $updated_time = date('d/m/Y, H:i'); // Current date and time
        echo json_encode([
            'timeslot' => $timeslot,
            'total_courts' => $total_courts,
            'available_courts' => $available_courts,
            'updated_time' => $updated_time
        ]);
    } else {
        echo json_encode(['error' => "No data for court type: $courtType"]);
    }
} else {
    echo json_encode(['error' => 'Invalid request. No courtType specified.']);
}

mysqli_close($conn);
?>
