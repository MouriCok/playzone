<?php
require_once 'database.php';

// Set the correct timezone
date_default_timezone_set('Asia/Kuala_Lumpur');

$updated_time = date('d/m/Y, H:i'); // Capture the updated time once

if (isset($_POST['courtType'])) {
    $courtType = $_POST['courtType'];
    $current_time = new DateTime();
    $current_day = date('l');

    // Fetch premises' open and close times for the current day
    $sql_hours = "SELECT open_time, close_time FROM premises_hours WHERE day_of_week = ?";
    $stmt_hours = $conn->prepare($sql_hours);
    $stmt_hours->bind_param("s", $current_day);
    $stmt_hours->execute();
    $result_hours = $stmt_hours->get_result();
    $premises_hours = $result_hours->fetch_assoc();

    if (!$premises_hours) {
        echo json_encode([
            'error' => "The premises are closed on $current_day.",
            'updated_time' => $updated_time
        ]);
        exit;
    }

    $open_time = new DateTime($premises_hours['open_time']);
    $close_time = new DateTime($premises_hours['close_time']);
    $date_for_timeslot = date('Y-m-d'); // Default to today

    // If current time is past closing, move to the next day
    if ($current_time > $close_time) {
        $date_for_timeslot = date('Y-m-d', strtotime('+1 day'));
        $current_day = date('l', strtotime('+1 day'));

        // Fetch next day's open/close times
        $sql_hours = "SELECT open_time, close_time FROM premises_hours WHERE day_of_week = ?";
        $stmt_hours = $conn->prepare($sql_hours);
        $stmt_hours->bind_param("s", $current_day);
        $stmt_hours->execute();
        $result_hours = $stmt_hours->get_result();
        $premises_hours = $result_hours->fetch_assoc();

        if (!$premises_hours) {
            echo json_encode([
                'error' => "The premises are closed tomorrow ($current_day). But you can still make a booking for the next day.",
                'updated_time' => $updated_time
            ]);
            exit;
        }

        $open_time = new DateTime($premises_hours['open_time']);
        $close_time = new DateTime($premises_hours['close_time']);
        $current_time = $open_time; // Reset current time to the next day's opening time
    }

    // Determine timeslot based on current or next day's open time
    $start_time = ($current_time < $open_time) ? $open_time : clone $current_time;
    $start_time->setTime((int) $start_time->format('H'), 0, 0); // Round to full hour
    $end_time = clone $start_time;
    $end_time->modify('+2 hours');

    if ($end_time > $close_time) {
        $end_time = $close_time;
    }

    $timeslot = $start_time->format('H:i') . " - " . $end_time->format('H:i');

    // Fetch total courts for the courtType
    $sql_total = "SELECT total_courts FROM court_count WHERE courtType = ?";
    $stmt_total = $conn->prepare($sql_total);
    $stmt_total->bind_param("s", $courtType);
    $stmt_total->execute();
    $result_total = $stmt_total->get_result();
    $total_courts_data = $result_total->fetch_assoc();

    if ($total_courts_data) {
        $total_courts = $total_courts_data['total_courts'];

        // Count the number of bookings for the timeslot range
        $sql_bookings = "SELECT COUNT(*) as booked_courts 
                        FROM bookings 
                        WHERE courtType = ? 
                        AND (datestart <= ? AND dateend >= ?)";
        $start_time_str = $date_for_timeslot . ' ' . $start_time->format('H:i:s');
        $end_time_str = $date_for_timeslot . ' ' . $end_time->format('H:i:s');
        $stmt_bookings = $conn->prepare($sql_bookings);
        $stmt_bookings->bind_param("sss", $courtType, $end_time_str, $start_time_str);
        $stmt_bookings->execute();
        $result_bookings = $stmt_bookings->get_result();
        $bookings_data = $result_bookings->fetch_assoc();

        $booked_courts = $bookings_data ? $bookings_data['booked_courts'] : 0;
        $available_courts = max(0, $total_courts - $booked_courts);

        echo json_encode([
            'date' => $date_for_timeslot, 
            'day' => $current_day,
            'timeslot' => $timeslot,
            'total_courts' => $total_courts,
            'available_courts' => $available_courts,
            'updated_time' => $updated_time
        ]);
    } else {
        echo json_encode([
            'error' => "No data for court type: $courtType",
            'updated_time' => $updated_time
        ]);
    }
} else {
    echo json_encode([
        'error' => 'Invalid request. No courtType specified.',
        'updated_time' => $updated_time
    ]);
}

mysqli_close($conn);
?>
