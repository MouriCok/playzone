<?php
session_start();
require_once 'database.php';

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Define constants
$rowsPerPage = 11;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max($page, 1); // Ensure page is at least 1
$offset = ($page - 1) * $rowsPerPage;

// Fetch the total number of records to calculate pages
$cEmail = $_SESSION['cEmail'];
$countQuery = "SELECT COUNT(*) AS total FROM booking_history WHERE cEmail = ?";
$countStmt = $conn->prepare($countQuery);
$countStmt->bind_param("s", $cEmail);
$countStmt->execute();
$countResult = $countStmt->get_result();
$totalRows = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalRows / $rowsPerPage);

// Fetch data for the current page
$bookingQuery = "SELECT bID, courtType, preferredCourt, datestart, dateend, people, payment_status 
                 FROM booking_history 
                 WHERE cEmail = ? 
                 LIMIT ?, ?";
$bookingStmt = $conn->prepare($bookingQuery);
$bookingStmt->bind_param("sii", $cEmail, $offset, $rowsPerPage);
$bookingStmt->execute();
$bookingResult = $bookingStmt->get_result();

// Output booking history rows
$rowCount = 0;
if ($bookingResult && $bookingResult->num_rows > 0) {
    while ($bookingRow = $bookingResult->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($bookingRow["bID"]) . "</td>";
        echo "<td>" . htmlspecialchars($bookingRow["courtType"]) . "</td>";
        echo "<td>" . htmlspecialchars($bookingRow["preferredCourt"]) . "</td>";
        echo "<td>" . htmlspecialchars($bookingRow["datestart"]) . " - " . htmlspecialchars($bookingRow["dateend"]) . "</td>";
        echo "<td>" . htmlspecialchars($bookingRow["people"]) . "</td>";
        echo "<td>" . htmlspecialchars($bookingRow["payment_status"]) . "</td>";
        echo "</tr>";
        $rowCount++;
    }
}

// Fill remaining rows if fewer than 12 records were returned
for ($i = $rowCount; $i < $rowsPerPage; $i++) {
    echo "<tr><td colspan='6'>&nbsp;</td></tr>";
}

// Close connections
$countStmt->close();
$bookingStmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<body>
    <!-- Pagination Controls -->
    <div class="pagination">
            <?php if ($page > 1): ?>
                <button onclick="fetchPage(<?php echo $page - 1; ?>)">Previous</button>
            <?php endif; ?>
                <span class="current">Page <?php echo $page; ?> of <?php echo $totalPages; ?></span>
            <?php if ($page < $totalPages): ?>
                <button onclick="fetchPage(<?php echo $page + 1; ?>)">Next</button>
            <?php endif; ?>
        </div>
</body>
</html>
