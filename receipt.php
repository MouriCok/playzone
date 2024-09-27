<?php
    require_once 'database.php';

    if (isset($_GET['booking_id'])) {
        $booking_id = $_GET['booking_id'];

        $sql = "SELECT * FROM bookings WHERE bID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $booking_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $row = $result->fetch_assoc()) {
            echo "<div class='container mt-5'>";
            echo "<div class='card'>";
            echo "<div class='card-header'><h2>Booking Confirmation</h2></div>";
            echo "<div class='card-body'>";
            echo "<p><strong>Booking ID:</strong> " . $row['bID'] . "</p>";
            echo "<p><strong>Name:</strong> " . $row['cName'] . "</p>";
            echo "<p><strong>Email:</strong> " . $row['cEmail'] . "</p>";
            echo "<p><strong>Phone:</strong> " . $row['cPhone'] . "</p>";
            echo "<p><strong>Court Type:</strong> " . $row['courtType'] . "</p>";
            echo "<p><strong>Preferred Court:</strong> " . $row['preferredCourt'] . "</p>";
            echo "<p><strong>Date & Time Start:</strong> " . $row['datestart'] . "</p>";
            echo "<p><strong>Date & Time End:</strong> " . $row['dateend'] . "</p>";
            echo "<p><strong>Participants:</strong> " . $row['people'] . "</p>";
            echo "<p><strong>Total Price:</strong> RM" . $row['price'] . "</p>";
            echo "<p><strong>Payment Status:</strong> " . $row['payment_status'] . "</p>";
            echo "<p><strong>Transaction ID:</strong> " . $row['transaction_id'] . "</p>";
            echo "<button class='btn btn-primary mt-3' onclick='window.print()'>Print Receipt</button>";
            echo "<button class='btn btn-secondary mt-3' onclick=\"window.location.href='index.php'\">Back to Home</button>";
            echo "</div></div></div>";
        } else {
            echo "Booking not found.";
        }
    } else {
        echo "No booking ID provided.";
    }

    mysqli_close($conn);
?>
