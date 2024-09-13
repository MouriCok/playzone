<?php
require_once 'database.php';

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

    // Get the current time
    $current_time = new DateTime();
    $open_time = new DateTime($premises_hours['open_time']);
    $close_time = new DateTime($premises_hours['close_time']);

    // Check if the current time is within opening hours
    if ($current_time < $open_time || $current_time > $close_time) {
        echo json_encode(['error' => "The premises are closed right now. Open hours today are from " . $open_time->format('H:i') . " to " . $close_time->format('H:i')]);
        exit;
    }

    // Set the time slot for the next 2 hours
    $start_time = $current_time->format('H:i');
    $end_time = $current_time->modify('+2 hours')->format('H:i');
    
    // If the timeslot exceeds closing time, adjust the end time to the closing time
    if ($current_time > $close_time) {
        $end_time = $close_time->format('H:i');
    }

    // Fetch the total courts for the specified court type
    $sql_total = "SELECT total_courts FROM court_count WHERE courtType = ?";
    $stmt_total = $conn->prepare($sql_total);
    $stmt_total->bind_param("s", $courtType);
    $stmt_total->execute();
    $result_total = $stmt_total->get_result();
    $total_courts_data = $result_total->fetch_assoc();

    if ($total_courts_data) {
        $total_courts = $total_courts_data['total_courts'];

        // Count available courts from `court_availability` that are available within the next 2 hours
        $sql_availability = "
            SELECT COUNT(*) as available_courts 
            FROM court_availability 
            WHERE courtType = ? 
            AND availableFrom <= NOW() 
            AND availableTo >= NOW()";
        $stmt_avail = $conn->prepare($sql_availability);
        $stmt_avail->bind_param("s", $courtType);
        $stmt_avail->execute();
        $result_avail = $stmt_avail->get_result();
        $avail_data = $result_avail->fetch_assoc();

        $available_courts = $avail_data ? $avail_data['available_courts'] : 0;

        // Return the JSON response
        $updated_time = date('d/m/Y H:i');
        echo json_encode([
            'timeslot' => "$start_time - $end_time",
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
