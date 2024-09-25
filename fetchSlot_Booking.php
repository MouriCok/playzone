<?php
require_once 'database.php';

// Set the correct timezone
date_default_timezone_set('Asia/Kuala_Lumpur');

if (isset($_POST['courtType']) && isset($_POST['datestart']) && isset($_POST['timestart']) && isset($_POST['duration'])) {
    $courtType = $_POST['courtType'];
    $selectedDate = new DateTime($_POST['datestart']);
    $startTime = new DateTime($_POST['datestart'] . ' ' . $_POST['timestart']);
    $duration = (int)$_POST['duration'];
    $endTime = clone $startTime;
    $endTime->modify("+$duration hours");

    $day_of_week = $selectedDate->format('l'); // Get the day of the week

    // Fetch premises' open and close times for the selected day
    $sql_hours = "SELECT open_time, close_time FROM premises_hours WHERE day_of_week = ?";
    $stmt_hours = $conn->prepare($sql_hours);
    $stmt_hours->bind_param("s", $day_of_week);
    $stmt_hours->execute();
    $result_hours = $stmt_hours->get_result();
    $premises_hours = $result_hours->fetch_assoc();

    if (!$premises_hours) {
        echo json_encode(['error' => "The premises are closed on $day_of_week."]);
        exit;
    }

    $open_time = new DateTime($premises_hours['open_time']);
    $close_time = new DateTime($premises_hours['close_time']);

    // Check if the selected time is within the operating hours
    if ($startTime < $open_time || $endTime > $close_time) {
        echo json_encode(['error' => "Selected time is outside the premises' operating hours ($open_time - $close_time)."]);
        exit;
    }

    // Prepare an array for the available courts
    $availableCourts = [];

    // Fetch total courts for the selected court type
    $sql_total = "SELECT court_id FROM courts WHERE courtType = ?";
    $stmt_total = $conn->prepare($sql_total);
    $stmt_total->bind_param("s", $courtType);
    $stmt_total->execute();
    $result_total = $stmt_total->get_result();

    // Loop through each court and check its availability
    while ($court = $result_total->fetch_assoc()) {
        $court_id = $court['court_id'];

        // Check if this court is booked during the selected time period
        $sql_bookings = "SELECT COUNT(*) as booked 
                        FROM bookings 
                        WHERE courtType = ? 
                        AND court_id = ? 
                        AND (? < dateend AND ? > datestart)";
        $stmt_bookings = $conn->prepare($sql_bookings);
        $start_time_str = $startTime->format('Y-m-d H:i:s');
        $end_time_str = $endTime->format('Y-m-d H:i:s');
        $stmt_bookings->bind_param("ssss", $courtType, $court_id, $start_time_str, $end_time_str);
        $stmt_bookings->execute();
        $result_bookings = $stmt_bookings->get_result();
        $bookings_data = $result_bookings->fetch_assoc();
        $isBooked = $bookings_data['booked'];

        // If not booked, the court is available
        if ($isBooked == 0) {
            $availableCourts[] = [
                'court' => $court_id,
                'status' => 'Available'
            ];
        }
    }

    // If there are no available courts, return an error
    if (empty($availableCourts)) {
        echo json_encode(['error' => "No available courts for the selected time and date."]);
    } else {
        echo json_encode(['slots' => $availableCourts]);
    }
} else {
    echo json_encode(['error' => 'Invalid request. Missing courtType, datestart, timestart, or duration.']);
}

mysqli_close($conn);
?>
