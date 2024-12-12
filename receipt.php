<?php
    require_once 'database.php';
    require_once 'fpdf/fpdf.php';

    $receiptFile = null; // Initialize receipt file path
    $cName = $cEmail = $cPhone = $courtType = $preferredCourt = $formattedDate = $formattedStart = $formattedEnd = $duration = $totalPrice = $payment_status = $transaction_id = $people = null;

    if (isset($_GET['booking_id'])) {
        $booking_id = $_GET['booking_id'];

        // Fetch booking data
        $sql = "SELECT * FROM bookings WHERE bID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $booking_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $row = $result->fetch_assoc()) {
            // Populate variables with booking data
            $cName = $row['cName'];
            $cEmail = $row['cEmail'];
            $cPhone = $row['cPhone'];
            $courtType = $row['courtType'];
            $preferredCourt = $row['preferredCourt'];
            $people = $row['people'];

            $datestart = $row['datestart'];
            $dateend = $row['dateend'];

            $datestartObj = new DateTime($datestart);
            $dateendObj = new DateTime($dateend);

            $formattedDate = $datestartObj->format('d F Y');
            $formattedStart = $datestartObj->format('H:i');
            $formattedEnd = $dateendObj->format('H:i');

            $duration = $datestartObj->diff($dateendObj)->format('%h'); // Calculate duration in hours

            $totalPrice = $row['price'];
            $transaction_id = $row['transaction_id'] ?? 'Transaction ID unavailable';
            $payment_status = $row['payment_status'] ?? 'Pending';

            // Retrieve receipt file path if saved in session (or a default fallback)
            session_start();
            $receiptFile = $_SESSION['receiptFile'] ?? "receipts/receipt_$booking_id.pdf";
        } else {
            // Handle missing booking data
            echo "<script>alert('Booking data not found.');</script>";
            exit;
        }
    } else {
        echo "<script>alert('Booking ID not provided.');</script>";
        exit;
    }

    // Close database connection
    mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="PZ_icon-32x32.png" type="image/png">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="stylee.css">
    <link rel="stylesheet" href="button.css">
    <link rel="stylesheet" href="form.css">
    <title>Receipt</title>
    <style>
        #booking-steps {
        text-align: center;
        margin: 0 auto;
        }
        .step-header {
        font-family: 'League Spartan', sans-serif;
        font-size: 18px;
        display: inline-block;
        padding: 10px 20px;
        margin: 0 0 32px 0px;
        border-radius: 5px;
        color: #fff;
        background-color: #626262;
        }
        .step-finish {
        font-family: 'League Spartan', sans-serif;
        font-size: 18px;
        display: inline-block;
        padding: 10px 20px;
        margin: 0 0 32px 0px;
        border-radius: 5px;
        color: #fff;
        background-color: #28a745;
        }
        .active-step {
        background-color: #102A7E; /* 28a745 */
        font-weight: bold;
        color: #fff;
        }
        .step-container {
        display: flex;
        justify-content: space-between;
        gap: 20px;
        }
        .button-container {
        display: flex;
        flex-direction: row;
        gap: 5px;
        }
        .step { display: none; }
        .step.active { display: block; }
        .slots { margin-top: 15px; }

        .separator {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 10px 0px 10px 0px;
        }
        
        .separator > div {
            width: 100%;
            height: 1px;
            border-radius: 5px;
            background-color: #000;
        }

        /* From Uiverse.io by satyamchaudharydev */ 
        .loader {
            display: inline-block;
            --height-of-loader: 4px;
            --loader-color: #0071e2;
            width: 130px;
            height: var(--height-of-loader);
            border-radius: 30px;
            background-color: rgba(255,255,255,0.75);
            position: relative;
        }

        .loader::before {
            content: "";
            position: absolute;
            background: var(--loader-color);
            top: 0;
            left: 0;
            width: 0%;
            height: 100%;
            border-radius: 30px;
            animation: moving 1s ease-in-out infinite;
            ;
        }

        @keyframes moving {
            50% {
            width: 100%;
            }

            100% {
            width: 0;
            right: 0;
            left: unset;
            }
        }

        .loader-1 {
            display: inline-block;
            width: 130px;
            height: 4px;
            border-radius: 30px;
            background-color: rgba(255,255,255,0.75);
            position: relative;
        }

        .loader-2 {
            display: inline-block;
            width: 130px;
            height: 4px;
            border-radius: 30px;
            background-color: #0071e2;
            position: relative;
        }
    </style>
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="container-fluid">
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#myNavbar">
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                </div>
                <div class="collapse navbar-collapse" id="myNavbar">
                    <ul class="nav navbar-nav">
                        <li><img src="PZ_tp.svg" width="40" height="40" alt="Logo"></li>
                        <li><a href="javascript:void(0);" onclick="goBack(event);" class="nav-btn"> Back</a></li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <div class="page-container">
        <?php
            // Convert datestart and dateend to DateTime objects
            $startDateTime = new DateTime($datestart);
            $endDateTime = new DateTime($dateend);

            // Format them as 'HH:MM (DD Month YYYY)'
            $formattedDate = $startDateTime->format('d F Y');
            $formattedStart = $startDateTime->format('H:i');
            $formattedEnd = $endDateTime->format('H:i');
        ?>
        <div id="booking-steps">
            <div class="steps-heading">
                    <span id="step-1" class="p6 step-finish">
                        <img src="Icons/reserve-icon.svg" width="24" height="24" alt="Booking Icon"> Booking Details
                    </span>
                    <div class="loader-2"></div>
                    <span id="step-2" class="p6 step-finish">
                        <img src="Icons/payment.svg" width="24" height="24" alt="Payment Icon"> Payment
                    </span>
                    <div class="loader-2"></div>
                    <span id="step-3" class="p6 step-header active-step">
                        <img src="Icons/receipt-icon.svg" width="24" height="24" alt="Receipt Icon"> Receipt
                    </span>
            </div>

            <!-- Step 3: Receipt -->
            <div class="step active" id="step-receipt">
                <div class="step-container">
                    <div class="receipt-container">
                        <span class="p7" style="margin-top: 16px;">RECEIPT</span>
                        <div class="separator">
                        <div></div>
                        </div>
                        <span class="p7">Name: <span class="p8"><?= htmlspecialchars($cName) ?></span></span>
                        <span class="p7">Email: <span class="p8"><?= htmlspecialchars($cEmail) ?></span></span>
                        <span class="p7">Phone: <span class="p8"><?= htmlspecialchars($cPhone) ?></span></span>
                        <span class="p7">Participants: <span class="p8"><?= htmlspecialchars($people) ?></span></span>
                        <div class="separator">
                        <div></div>
                        </div>
                        <span class="p7">Court Type: <span class="p8"><?= htmlspecialchars($courtType) ?></span></span>
                        <span class="p7">Preferred Court: <span class="p8"><?= htmlspecialchars($preferredCourt) ?></span></span>
                        <div class="separator">
                        <div></div>
                        </div>
                        <span class="p7">Date: <span class="p8"><?= htmlspecialchars($formattedDate) ?></span></span>
                        <span class="p7">Time: <span class="p8"><?= htmlspecialchars($formattedStart) ?> - <?= htmlspecialchars($formattedEnd) ?></span></span>
                        <span class="p7">Duration: <span class="p8"><?= htmlspecialchars($duration) ?> hours</span></span>
                        <span class="p7">Total Price: RM<span class="p8"><?= htmlspecialchars($totalPrice) ?></span></span>
                        <div class="separator">
                        <div></div>
                        </div>
                        <div class="button-container">
                            <a href="<?= htmlspecialchars($receiptFile) ?>" class="btn btn-primary" download>Download Receipt (PDF)</a>
                            <a href="index.php" class="btn btn-primary">Back to Home</a>
                        </div>
                    </div>
                    <div class="receipt-container">
                        <span class="p7" style="margin-top: 16px;">PAYMENT DETAILS</span>
                        <div class="separator">
                        <div></div>
                        </div>
                        <span class="p7">Payment Status: <span class="p8"><?= htmlspecialchars($payment_status) ?></span></span>
                        <span class="p7">Transaction ID: <span class="p8"><?= htmlspecialchars($transaction_id) ?></span></span>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <script src="scripts.js"></script>
</body>
</html>
