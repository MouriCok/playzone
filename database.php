<?php
    $servername = "localhost";
    $db_username = "root";
    $db_password = "";
    $db_name = "spiderman";

    // $servername = "168.138.180.170";
    // $db_username = "spiderman";
    // $db_password = "spiderman2024";
    // $db_name = "spiderman";

    $conn = new mysqli($servername, $db_username, $db_password, $db_name);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
?>