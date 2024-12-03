<?php
require_once 'database.php';
header('Content-Type: application/json');
date_default_timezone_set('Asia/Kuala_Lumpur');

try {
    if (!isset($_POST['courtType'], $_POST['datestart'])) {
        throw new Exception("Invalid request. Missing court type or date.");
    }

    $courtType = $_POST['courtType'];
    $datestart = new DateTime($_POST['datestart']);
    $date = $datestart->format('Y-m-d'); // Extract date only

    // Fetch all bookings for the chosen court type and date
    $sql = "
        SELECT 
            b.datestart, b.dateend, b.court_id 
        FROM 
            bookings b 
        WHERE 
            b.courtType = ? 
            AND DATE(b.datestart) = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $courtType, $date);
    $stmt->execute();
    $result = $stmt->get_result();

    $bookedCourts = [];
    while ($row = $result->fetch_assoc()) {
        $startTime = (new DateTime($row['datestart']))->format('H:i');
        $endTime = (new DateTime($row['dateend']))->format('H:i');

        $bookedCourts[] = [
            'time' => "$startTime - $endTime",
            'court' => $row['court_id'],
            'status' => 'Not Available',
        ];
    }

    if (count($bookedCourts) > 0) {
        echo json_encode(['bookings' => $bookedCourts]);
    } else {
        echo json_encode([
            'message' => "All courts for $courtType are available on " . $datestart->format('d F Y') . "."
        ]);
    }
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}

$conn->close();
?>
