<?php
require_once 'database.php';

// Set the correct timezone
date_default_timezone_set('Asia/Kuala_Lumpur');

if (isset($_POST['courtType']) && isset($_POST['date'])) {
    $courtType = $_POST['courtType'];
    $selectedDate = new DateTime($_POST['date']);
    $day_of_week = $selectedDate->format('l'); // Get the day of the week, e.g., 'Monday'

    // Fetch premises' open and close times for the selected day
    $sql_hours = "SELECT open_time, close_time FROM premises_hours WHERE day_of_week = ?";
    $stmt_hours = $conn->prepare($sql_hours);
    $stmt_hours->bind_param("s", $day_of_week);
    $stmt_hours->execute();
    $result_hours = $stmt_hours->get_result();
    $premises_hours = $result_hours->fetch_assoc();

    // If the selected day is closed (no opening/closing times)
    if (!$premises_hours) {
        echo json_encode(['error' => "The premises are closed on $day_of_week."]);
        exit;
    }

    $open_time = new DateTime($premises_hours['open_time']);
    $close_time = new DateTime($premises_hours['close_time']);
    $current_time = clone $open_time;

    // Prepare an array for the time slots
    $slots = [];

    // Loop through each hour between open and close time
    while ($current_time < $close_time) {
        $next_time = clone $current_time;
        $next_time->modify('+1 hour');

        // Filter bookings by the exact date and time
        $sql_bookings = "SELECT COUNT(*) as booked_courts 
                         FROM bookings 
                         WHERE courtType = ? 
                         AND (? BETWEEN datestart AND dateend)"; // Filter bookings for the selected date and time
        $stmt_bookings = $conn->prepare($sql_bookings);
        $start_time_str = $selectedDate->format('Y-m-d') . ' ' . $current_time->format('H:i:s'); // Use the selected date and time
        $stmt_bookings->bind_param("ss", $courtType, $start_time_str);
        $stmt_bookings->execute();
        $result_bookings = $stmt_bookings->get_result();
        $bookings_data = $result_bookings->fetch_assoc();
        $booked_courts = $bookings_data['booked_courts'];

        // Fetch total number of courts for the selected court type
        $sql_total = "SELECT total_courts FROM court_count WHERE courtType = ?";
        $stmt_total = $conn->prepare($sql_total);
        $stmt_total->bind_param("s", $courtType);
        $stmt_total->execute();
        $result_total = $stmt_total->get_result();
        $total_courts_data = $result_total->fetch_assoc();
        $total_courts = $total_courts_data['total_courts'];

        // Calculate available courts
        $available_courts = $total_courts - $booked_courts;

        // Store the availability for the current time slot
        $slots[] = [
            'time' => $current_time->format('H:i') . ' - ' . $next_time->format('H:i'),
            'status' => $available_courts > 0 ? 'Available' : 'Not Available'
        ];

        // Move to the next time slot
        $current_time = $next_time;
    }

    echo json_encode(['slots' => $slots]);
} else {
    echo json_encode(['error' => 'Invalid request. CourtType or Date is missing.']);
}

mysqli_close($conn);
?>