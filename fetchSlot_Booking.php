<?php
require_once 'database.php';

// Set the correct timezone
date_default_timezone_set('Asia/Kuala_Lumpur'); 

if (isset($_POST['courtType'])) {
    $courtType = $_POST['courtType'];

    // Define the days to be displayed (Monday to Saturday)
    $days_of_week = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

    // Fetch premises' open and close times for each day
    $sql_hours = "SELECT day_of_week, open_time, close_time FROM premises_hours WHERE day_of_week IN ('" . implode("', '", $days_of_week) . "')";
    $result_hours = $conn->query($sql_hours);
    $premises_hours = [];
    
    while ($row = $result_hours->fetch_assoc()) {
        $premises_hours[$row['day_of_week']] = [
            'open_time' => new DateTime($row['open_time']),
            'close_time' => new DateTime($row['close_time']),
        ];
    }

    // Prepare an array for the time slots
    $slots = [];

    // Loop through each hour for each day
    foreach ($days_of_week as $day) {
        $open_time = $premises_hours[$day]['open_time'];
        $close_time = $premises_hours[$day]['close_time'];
        $current_time = clone $open_time;

        while ($current_time < $close_time) {
            $next_time = clone $current_time;
            $next_time->modify('+2 hour');

            // Fetch court bookings for the selected time slot and court type
            $sql_bookings = "SELECT COUNT(*) as booked_courts 
                             FROM bookings 
                             WHERE courtType = ? 
                             AND DAYNAME(datestart) = ? 
                             AND (datestart <= ? AND dateend > ?)";
            $stmt = $conn->prepare($sql_bookings);
            $start_time_str = $current_time->format('Y-m-d H:i:s');
            $end_time_str = $next_time->format('Y-m-d H:i:s');
            $stmt->bind_param("ssss", $courtType, $day, $start_time_str, $start_time_str);
            $stmt->execute();
            $result = $stmt->get_result();
            $bookings_data = $result->fetch_assoc();
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
                strtolower($day) => $available_courts > 0 ? 'Available' : 'Not Available'
            ];

            // Move to the next time slot
            $current_time = $next_time;
        }
    }

    echo json_encode(['slots' => $slots]);
} else {
    echo json_encode(['error' => 'Invalid request. CourtType is missing.']);
}

mysqli_close($conn);
?>