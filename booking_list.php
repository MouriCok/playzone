<?php
session_start();
require 'database.php'; // Database connection

// Check if the admin is logged in, if not then redirect to admin.php
if (!isset($_SESSION['loggedIn']) || $_SESSION['loggedIn'] !== true) {
    header("location: admin.php");
    exit;
}

// Fetch bookings data
$query = "SELECT bID, cName, courtType, preferredCourt, datestart, dateend, people, payment_status FROM bookings";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking List</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="stylee.css">
    <link rel="stylesheet" href="button.css">
    <style>
        body {
            margin: 20px;
        }
        .table-container {
            margin-top: 20px;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Booking List</h2>
    <p>Manage all customer bookings here.</p>
    
    <div class="table-responsive table-container">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Booking ID</th>
                    <th>Customer Name</th>
                    <th>Category</th>
                    <th>Court Type</th>
                    <th>Timeslot</th>
                    <th>Participants</th>
                    <th>Payment Status</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result && mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<tr>";
                        echo "<td>{$row['bID']}</td>";
                        echo "<td>{$row['cName']}</td>";
                        echo "<td>{$row['courtType']}</td>";
                        echo "<td>{$row['preferredCourt']}</td>";
                        echo "<td>{$row['datestart']} - {$row['dateend']}</td>";
                        echo "<td>{$row['people']}</td>";
                        echo "<td>{$row['payment_status']}</td>";
                        echo "<td>
                                <select class='status-dropdown' data-id='{$row['bID']}'>
                                    <option value='Pending' " . ($row['payment_status'] == 'Pending' ? 'selected' : '') . ">Pending</option>
                                    <option value='Confirmed' " . ($row['payment_status'] == 'Confirmed' ? 'selected' : '') . ">Confirmed</option>
                                    <option value='Cancelled' " . ($row['payment_status'] == 'Cancelled' ? 'selected' : '') . ">Cancelled</option>
                                </select>
                              </td>";
                        echo "<td>
                                <button class='btn btn-danger delete-btn' onclick='deleteBooking({$row['bID']})'>Delete</button>
                              </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='9'>No bookings found.</td></tr>";
                }
                mysqli_close($conn);
                ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Update booking status
        $('.status-dropdown').change(function() {
            var bookingId = $(this).data('id');
            var newStatus = $(this).val();
            $.ajax({
                url: 'update_booking_status.php',
                type: 'POST',
                data: { bID: bookingId, payment_status: newStatus },
                success: function(response) {
                    alert(response);
                }
            });
        });
    });

    // Delete booking
    function deleteBooking(bID) {
        if (confirm("Are you sure you want to delete this booking? Your payment will not be refunded.")) {
        if (confirm("This action cannot be undone. Are you sure?")) {
            var form = document.createElement("form");
            form.method = "post";
            form.action = "admin_deleteBooking.php";

            var input = document.createElement("input");
            input.type = "hidden";
            input.name = "bID";
            input.value = bID;

            form.appendChild(input);
            document.body.appendChild(form);

            form.submit();
        }
        }
    }
</script>

</body>
</html>
