<?php
session_start(); 
require_once 'database.php';

header('Content-Type: application/json');

$rowsPerPage = 5; // Number of rows per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max($page, 1); // Ensure page is at least 1
$offset = ($page - 1) * $rowsPerPage;

$cEmail = $_SESSION['cEmail'];

// Count total rows
$countQuery = "SELECT COUNT(*) as total FROM bookings WHERE cEmail = ?";
$stmt = $conn->prepare($countQuery);
$stmt->bind_param("s", $cEmail);
$stmt->execute();
$countResult = $stmt->get_result();
$totalRows = ($countResult && $row = $countResult->fetch_assoc()) ? (int)$row['total'] : 0;
$totalPages = ceil($totalRows / $rowsPerPage);

// Fetch limited rows for the current page
$bookingQuery = "
    SELECT bID, courtType, preferredCourt, datestart, dateend, people, payment_status
    FROM bookings
    WHERE cEmail = ?
    ORDER BY datestart DESC
    LIMIT ? OFFSET ?";
$stmt = $conn->prepare($bookingQuery);
$stmt->bind_param("sii", $cEmail, $rowsPerPage, $offset);
$stmt->execute();
$bookingResult = $stmt->get_result();

// Generate table rows
$tableRows = '';
$rowsDisplayed = 0;

if ($bookingResult && $bookingResult->num_rows > 0) {
    while ($bookingRow = $bookingResult->fetch_assoc()) {
        $datestart = new DateTime($bookingRow['datestart']);
        $dateend = new DateTime($bookingRow['dateend']);

        $formattedDate = $datestart->format('d F Y');
        $formattedTime = $datestart->format('H:i') . ' - ' . $dateend->format('H:i');

        $tableRows .= "<tr>
            <td>" . htmlspecialchars($bookingRow['bID']) . "</td>
            <td>" . htmlspecialchars($bookingRow['courtType']) . "</td>
            <td>" . htmlspecialchars($bookingRow['preferredCourt']) . "</td>
            <td>" . htmlspecialchars($formattedDate) . "</td>
            <td>" . htmlspecialchars($formattedTime) . "</td>
            <td>" . htmlspecialchars($bookingRow['people']) . "</td>
            <td>" . htmlspecialchars($bookingRow['payment_status']) . "</td>
            <td><button class='bin-button' onclick='deleteBooking(" . htmlspecialchars($bookingRow['bID']) . ")'>
                    <svg class='bin-top' viewBox='0 0 39 7' fill='none' xmlns='http://www.w3.org/2000/svg'>
                        <line y1='5' x2='39' y2='5' stroke='white' stroke-width='4'></line>
                        <line x1='12' y1='1.5' x2='26.0357' y2='1.5' stroke='white' stroke-width='3'></line>
                    </svg>
                    <svg class='bin-bottom' viewBox='0 0 33 39' fill='none' xmlns='http://www.w3.org/2000/svg'>
                        <mask id='path-1-inside-1_8_19' fill='white'>
                            <path d='M0 0H33V35C33 37.2091 31.2091 39 29 39H4C1.79086 39 0 37.2091 0 35V0Z'></path>
                        </mask>
                        <path d='M0 0H33H0ZM37 35C37 39.4183 33.4183 43 29 43H4C-0.418278 43 -4 39.4183 -4 35H4H29H37ZM4 43C-0.418278 43 -4 
                        39.4183 -4 35V0H4V35V43ZM37 0V35C37 39.4183 33.4183 43 29 43V35V0H37Z' fill='white' mask='url(#path-1-inside-1_8_19)'>
                        </path>
                        <path d='M12 6L12 29' stroke='white' stroke-width='4'></path>
                        <path d='M21 6V29' stroke='white' stroke-width='4'></path>
                    </svg>
                </button>
            </td>
            </tr>";
            $rowsDisplayed++;
    }
} else {
    $rowsPerPage = 4;
    $tableRows = "<tr style='text-align: center;'><td colspan='8'>No booked facilities found.</td></tr>";
}

while ($rowsDisplayed < $rowsPerPage) {
    $tableRows .= "<tr><td colspan='8'>&nbsp;</td></tr>";
    $rowsDisplayed++;
}

// Generate pagination links
$paginationLinks = '';
if ($totalPages >= 0) {
    // Previous Button
    $paginationLinks .= '<li class="page-item ' . ($page <= 1 ? 'disabled' : '') . '">
        <a class="page-link" href="#" data-page="' . ($page - 1) . '">&laquo;</a>
    </li>';

    // Page Numbers
    for ($i = 1; $i <= $totalPages; $i++) {
        $paginationLinks .= '<li class="page-item ' . ($i == $page ? 'active' : '') . '">
            <a class="page-link" href="#" data-page="' . $i . '">' . $i . '</a>
        </li>';
    }

    // Next Button
    $paginationLinks .= '<li class="page-item ' . ($page >= $totalPages ? 'disabled' : '') . '">
        <a class="page-link" href="#" data-page="' . ($page + 1) . '">&raquo;</a>
    </li>';
}

// Return JSON response
echo json_encode([
    'tableRows' => $tableRows,
    'paginationLinks' => $paginationLinks,
]);

mysqli_close($conn);
?>
