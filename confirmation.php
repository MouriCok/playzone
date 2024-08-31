<?php
session_start();

// Check if session variables are set
if (!isset($_SESSION['datestart']) || !isset($_SESSION['dateend']) || !isset($_SESSION['courtType']) || !isset($_SESSION['people']) || !isset($_SESSION['totalPrice'])) {
    echo "Missing booking details. Please start the booking process again.";
    exit();
}

// Retrieve session data
$cName = $_SESSION['cName'];
$cEmail = $_SESSION['cEmail'];
$cPhone = $_SESSION['cPhone'];
$datestart = $_SESSION['datestart'];
$dateend = $_SESSION['dateend'];
$courtType = $_SESSION['courtType'];
$people = $_SESSION['people'];
$totalPrice = $_SESSION['totalPrice'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Booking Confirmation</title>
    <link href="https://fonts.googleapis.com/css2?family=League+Spartan:wght@600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Anton&family=League+Spartan:wght@600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="path/to/font-awesome/css/font-awesome.min.css">
</head>
<body>
    <h2>Booking Confirmation</h2>
    <p><strong>Full Name:</strong> <?= htmlspecialchars($cName) ?></p>
    <p><strong>Email:</strong> <?= htmlspecialchars($cEmail) ?></p>
    <p><strong>Phone:</strong> <?= htmlspecialchars($cPhone) ?></p>
    <p><strong>Court Type:</strong> <?= htmlspecialchars($courtType) ?></p>
    <p><strong>Start Date & Time:</strong> <?= htmlspecialchars($datestart) ?></p>
    <p><strong>End Date & Time:</strong> <?= htmlspecialchars($dateend) ?></p>
    <p><strong>Participants:</strong> <?= htmlspecialchars($people) ?></p>
    <p><strong>Total Price:</strong> RM<?= htmlspecialchars($totalPrice) ?></p>

    <h3>Please confirm your booking and proceed to payment:</h3>
    <div id="paypal-button-container"></div>

    <script src="https://www.paypal.com/sdk/js?client-id=Adq9ccMWAeJEkGWPnUI_ZE_sDA1WB4POGfjjCfgoOJnGVvY9X143fMioow2H6bLsgr-dvXJNx4nLsLjX&currency=MYR"></script>
    <script>
        paypal.Buttons({
            createOrder: function(data, actions) {
                return actions.order.create({
                    purchase_units: [{
                        amount: {
                            value: '<?= $totalPrice ?>'
                        }
                    }]
                });
            },
            onApprove: function(data, actions) {
                return actions.order.capture().then(function(details) {
                    alert('Transaction completed by ' + details.payer.name.given_name);
                    window.location.href = 'process_payment.php';
                });
            }
        }).render('#paypal-button-container');
    </script>
</body>
</html>
