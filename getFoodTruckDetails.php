<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "sport_booking";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get food truck ID from query parameters
$foodtruck_id = isset($_GET['foodtruck_id']) ? intval($_GET['foodtruck_id']) : 0;

// SQL query
$sql = "SELECT * FROM foodtruck WHERE foodtruckId = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $foodtruck_id);
$stmt->execute();
$result = $stmt->get_result();

$locations = array();

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $locations[] = $row;
    }
}

echo json_encode($locations);

$stmt->close();
$conn->close();
?>
