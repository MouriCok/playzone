<?php
require_once 'database.php';
header('Content-Type: application/json');

date_default_timezone_set('Asia/Kuala_Lumpur'); // Ensure correct timezone

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Error logging setup
ini_set('log_errors', 1);
ini_set('error_log', 'fetchSlot_Booking_errors.log');

try {
    if (isset($_POST['courtType']) && isset($_POST['datestart']) && isset($_POST['duration'])) {
        $courtType = $_POST['courtType'];
        $datestart_str = $_POST['datestart'];  // Booking start time from the form
        $duration = (int)$_POST['duration'];

        if (empty($datestart_str)) {
            throw new Exception("Invalid date selected.");
        }

        // Create DateTime object for the booking start time
        $datestart = new DateTime($datestart_str);

        // Clone to calculate end time
        $endTime = clone $datestart;
        $endTime->modify("+$duration hours");

        // Get the day of the week for premises hours lookup
        $day_of_week = $datestart->format('l');

        // Fetch open and close times for the selected day from the database
        $sql_hours = "SELECT open_time, close_time FROM premises_hours WHERE day_of_week = ?";
        $stmt_hours = $conn->prepare($sql_hours);
        if (!$stmt_hours) {
            throw new Exception("Error preparing premises hours query: " . $conn->error);
        }

        $stmt_hours->bind_param("s", $day_of_week);
        if (!$stmt_hours->execute()) {
            throw new Exception("Error executing premises hours query: " . $stmt_hours->error);
        }

        $result_hours = $stmt_hours->get_result();
        $premises_hours = $result_hours->fetch_assoc();

        if (!$premises_hours) {
            echo json_encode(['error' => "Premises are closed on <strong>$day_of_week</strong>. Please select another day."]);
            exit;
        }

        // Convert open_time and close_time from TIME format into full DateTime objects
        $open_time = new DateTime($datestart->format('Y-m-d') . ' ' . $premises_hours['open_time']);
        $close_time = new DateTime($datestart->format('Y-m-d') . ' ' . $premises_hours['close_time']);

        // Compare the booking start and end times against the premises' operating hours
        if ($datestart < $open_time || $endTime > $close_time) {
            echo json_encode([
                'error' => "Selected time is outside the premises' operating hours <strong>(" 
                    . $open_time->format('H:i') . " - " 
                    . $close_time->format('H:i') . ", $day_of_week)</strong>."
            ]);
            exit;
        }

        // error_log("Booking start time: " . $datestart->format('Y-m-d H:i:s'));
        // error_log("Booking end time: " . $endTime->format('Y-m-d H:i:s'));
        // error_log("Premises open time: " . $open_time->format('Y-m-d H:i:s'));
        // error_log("Premises close time: " . $close_time->format('Y-m-d H:i:s'));

        // Fetch total courts for the selected court type
        $sql_total = "SELECT court_id FROM courts WHERE courtType = ?";
        $stmt_total = $conn->prepare($sql_total);

        if (!$stmt_total) {
            throw new Exception("Error preparing court query => " . $conn->error);
        }

        $stmt_total->bind_param("s", $courtType);
        $stmt_total->execute();
        $result_total = $stmt_total->get_result();

        $availableCourts = [];
        while ($court = $result_total->fetch_assoc()) {
            $court_id = $court['court_id'];

            // Check if the court is booked during the selected period
            $sql_bookings = "SELECT COUNT(*) as booked FROM bookings WHERE courtType = ? AND court_id = ? AND (? < dateend AND ? > datestart)";
            $stmt_bookings = $conn->prepare($sql_bookings);

            if (!$stmt_bookings) {
                throw new Exception("Error preparing bookings query => " . $conn->error);
            }

            $start_time_str = $datestart->format('Y-m-d H:i:s');
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
            echo json_encode(['error' => "No available courts for the selected time and date. Please select another time or date."]);
        } else {
            echo json_encode(['slots' => $availableCourts]);
        }
    } else {
        throw new Exception("Invalid request. Missing courtType, datestart, or duration.");
    }
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}

mysqli_close($conn);
?>
