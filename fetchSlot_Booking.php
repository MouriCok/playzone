<?php
require_once 'database.php';
header('Content-Type: application/json'); // Ensure we're sending back JSON

date_default_timezone_set('Asia/Kuala_Lumpur');

// Enable error reporting to debug the issue
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

// Error logging setup
// ini_set('log_errors', 1);
// ini_set('error_log', 'fetchSlot_Booking_errors.log');

try {
    if (isset($_POST['courtType']) && isset($_POST['datestart']) && isset($_POST['duration'])) {
        $courtType = $_POST['courtType'];
        $datestart = new DateTime($_POST['datestart']);
        $startTime = new DateTime($_POST['datestart']);
        $duration = (int)$_POST['duration'];
        $endTime = clone $startTime;
        $endTime->modify("+$duration hours");

        $day_of_week = $datestart->format('l');

        // Fetch premises' open and close times for the selected day
        $sql_hours = "SELECT open_time, close_time FROM premises_hours WHERE day_of_week = ?";
        $stmt_hours = $conn->prepare($sql_hours);

        if (!$stmt_hours) {
            throw new Exception("Error preparing hours query: " . $conn->error);
        }

        $stmt_hours->bind_param("s", $day_of_week);
        $stmt_hours->execute();
        $result_hours = $stmt_hours->get_result();
        $premises_hours = $result_hours->fetch_assoc();

        if (!$premises_hours) {
            throw new Exception("Premises are closed on $day_of_week.");
        }

        $open_time = new DateTime($premises_hours['open_time']);
        $close_time = new DateTime($premises_hours['close_time']);

        // Check if the selected time is within the operating hours
        if ($startTime < $open_time || $endTime > $close_time) {
            throw new Exception("Selected time is outside the premises' operating hours ($open_time - $close_time).");
        }

        // Prepare an array for the available courts
        $availableCourts = [];

        // Fetch total courts for the selected court type
        $sql_total = "SELECT court_id FROM courts WHERE courtType = ?";
        $stmt_total = $conn->prepare($sql_total);

        if (!$stmt_total) {
            throw new Exception("Error preparing court query: " . $conn->error);
        }

        $stmt_total->bind_param("s", $courtType);
        $stmt_total->execute();
        $result_total = $stmt_total->get_result();

        while ($court = $result_total->fetch_assoc()) {
            $court_id = $court['court_id'];

            // Check if this court is booked during the selected time period
            $sql_bookings = "SELECT COUNT(*) as booked 
                            FROM bookings 
                            WHERE courtType = ? 
                            AND court_id = ? 
                            AND (? < dateend AND ? > datestart)";
            $stmt_bookings = $conn->prepare($sql_bookings);

            if (!$stmt_bookings) {
                throw new Exception("Error preparing bookings query: " . $conn->error);
            }

            $start_time_str = $startTime->format('Y-m-d H:i:s');
            $end_time_str = $endTime->format('Y-m-d H:i:s');
            $stmt_bookings->bind_param("ssss", $courtType, $court_id, $start_time_str, $end_time_str);
            $stmt_bookings->execute();
            $result_bookings = $stmt_bookings->get_result();
            $bookings_data = $result_bookings->fetch_assoc();
            $isBooked = $bookings_data['booked'];

            if ($isBooked == 0) {
                $availableCourts[] = [
                    'court' => $court_id,
                    'status' => 'Available'
                ];
            }
        }

        if (empty($availableCourts)) {
            echo json_encode(['error' => "No available courts for the selected time and date."]);
        } else {
            echo json_encode(['slots' => $availableCourts]);
        }
    } else {
        throw new Exception("Invalid request. Missing courtType, datestart, or duration.");
    }
} catch (Exception $e) {
    // Log the exception and return a generic error message
    error_log($e->getMessage());
    echo json_encode(['error' => 'An internal error occurred. Please try again later.']);
}

mysqli_close($conn);
