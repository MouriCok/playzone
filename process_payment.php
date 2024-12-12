<?php
session_start();
require_once 'database.php';
require_once 'fpdf/fpdf.php'; // Include FPDF library

$transaction_id = $_POST['transaction_id'] ?? null;

if (!$transaction_id) {
    die("Error: Transaction ID is missing. Please retry the payment.");
}

// Check if the required session variables are set
if (isset($_SESSION['cName'], $_SESSION['cEmail'], $_SESSION['cPhone'],
    $_SESSION['datestart'], $_SESSION['dateend'], $_SESSION['courtType'],
    $_SESSION['people'], $_SESSION['totalPrice'], $_SESSION['preferredCourt'], 
    $_SESSION['court_id'], $_SESSION['booking_id'])) {

    $cName = $_SESSION['cName'];
    $cEmail = $_SESSION['cEmail'];
    $cPhone = $_SESSION['cPhone'];
    $datestart = $_SESSION['datestart'];
    $dateend = $_SESSION['dateend'];
    $courtType = $_SESSION['courtType'];
    $people = $_SESSION['people'];
    $totalPrice = $_SESSION['totalPrice'];
    $preferredCourt = $_SESSION['preferredCourt'];
    $court_id = $_SESSION['court_id'];
    $booking_id = $_SESSION['booking_id']; // The existing booking ID to update

    // Update the booking record with the new payment status and transaction ID
    $update_sql = "UPDATE bookings 
                   SET payment_status = 'Paid', transaction_id = ? 
                   WHERE bID = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("si", $transaction_id, $booking_id);

    if ($stmt->execute()) {
        // Generate a receipt PDF
        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 16);

        // Header
        $pdf->Cell(0, 10, 'Booking Receipt', 0, 1, 'C');
        $pdf->Ln(10);

        // Booking Details
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 10, "Booking ID: $booking_id", 0, 1);
        $pdf->Cell(0, 10, "Name: $cName", 0, 1);
        $pdf->Cell(0, 10, "Email: $cEmail", 0, 1);
        $pdf->Cell(0, 10, "Phone: $cPhone", 0, 1);
        $pdf->Ln(5);

        $pdf->Cell(0, 10, "Court Type: $courtType", 0, 1);
        $pdf->Cell(0, 10, "Preferred Court: $preferredCourt", 0, 1);
        $pdf->Cell(0, 10, "Participants: $people", 0, 1);
        $pdf->Ln(5);

        $formattedStart = date('d F Y H:i', strtotime($datestart));
        $formattedEnd = date('d F Y H:i', strtotime($dateend));
        $pdf->Cell(0, 10, "Date: $formattedStart - $formattedEnd", 0, 1);
        $pdf->Cell(0, 10, "Total Price: RM$totalPrice", 0, 1);
        $pdf->Ln(10);

        $pdf->Cell(0, 10, "Payment Status: Paid", 0, 1);
        $pdf->Cell(0, 10, "Transaction ID: $transaction_id", 0, 1);

        // Save the PDF
        $receiptFileName = "receipts/receipt_$booking_id.pdf";
        $pdf->Output('F', $receiptFileName);

        $_SESSION['receiptFile'] = $receiptFileName;

        // $_SESSION['currentStep'] = 'receipt';

        // Redirect to the receipt step with a link to the PDF
        header("Location: receipt.php?booking_id=" . $booking_id . "&receiptFile=" . urlencode($receiptFileName));
        exit();
    } else {
        echo "Error updating payment status: " . $stmt->error;
    }
} else {
    echo "Missing session data. Please start the booking process again.";
}

mysqli_close($conn);
?>
