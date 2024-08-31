<?php
    $servername = "localhost";
    $db_username = "root";
    $db_password = "";
    $db_name = "sport_booking";

    $conn = new mysqli($servername, $db_username, $db_password, $db_name);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
?>