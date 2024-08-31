<?php
    require_once 'database.php';

    // Retrieve form data
    $courtType = $_POST['courtType'];
    $selected_date = date('Y-m-d', strtotime($_POST['datestart']));

    // Get the day of the week
    $day_of_week = date('l', strtotime($selected_date));

    // Fetch open and close times for the selected day
    $sql = "SELECT open_time, close_time FROM premises_hours WHERE day_of_week = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $day_of_week);
    $stmt->execute();
    $result = $stmt->get_result();
    $hours = $result->fetch_assoc();

    if (!$hours) {
    // Handle closed premises (e.g., Sunday)
    echo "<p>Sorry, the premises are closed on $day_of_week. Please choose another date.</p>";
    exit;
    }

    $open_time = $hours['open_time'];
    $close_time = $hours['close_time'];

    // Fetch existing bookings for the selected court on the selected date
    $sql = "SELECT datestart, dateend FROM bookings WHERE courtType = ? AND DATE(datestart) = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $courtType, $selected_date);
    $stmt->execute();
    $result = $stmt->get_result();

    // Calculate available slots
    $available_slots = [];

    $open_datetime = new DateTime("$selected_date $open_time");
    $close_datetime = new DateTime("$selected_date $close_time");

    $booked_slots = [];
    while ($row = $result->fetch_assoc()) {
        $booked_slots[] = [
            'start' => new DateTime($row['datestart']),
            'end' => new DateTime($row['dateend'])
        ];
    }

    $current_time = $open_datetime;
    $slot_duration = new DateInterval('PT1H'); // 1-hour slots

    while ($current_time < $close_datetime) {
        $next_time = clone $current_time;
        $next_time->add($slot_duration);

        $is_available = true;
        foreach ($booked_slots as $slot) {
            if ($current_time < $slot['end'] && $next_time > $slot['start']) {
                $is_available = false;
                break;
            }
        }

        if ($is_available) {
            $available_slots[] = $current_time->format('H:i') . " - " . $next_time->format('H:i');
        }

        $current_time = $next_time;
    }

    // Display available slots
    if (!empty($available_slots)) {
        echo "<h3>Available Slots:</h3>";
        echo "<ul>";
        foreach ($available_slots as $slot) {
            echo "<li>$slot</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>No available slots for $courtType on $selected_date. Please choose another date or time.</p>";
    }

    mysqli_close($conn);
?>
