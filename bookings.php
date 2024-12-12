<?php
  session_start();
  date_default_timezone_set('Asia/Kuala_Lumpur');
  require_once 'database.php';
  require_once 'fpdf/fpdf.php';

  if (!$conn) {
      die("Connection failed: " . mysqli_connect_error());
  }

  // Check if the user is logged in, if not then redirect to home page
  if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
      header("location: index.php");
      exit;
  }

  if (isset($_SESSION['cUser'])) {
      $cUser = $_SESSION['cUser'];

      // Query player data
      $sql = "SELECT * FROM customer WHERE cUser='$cUser'";
      $result = mysqli_query($conn, $sql);

      if ($result && mysqli_num_rows($result) > 0) {
          $row = mysqli_fetch_assoc($result);
          $cName = $row['cName'];
          $cEmail = $row['cEmail'];
          $cPhone = $row['cPhone'];

          // Store in session variables
          $_SESSION['cName'] = $cName;
          $_SESSION['cEmail'] = $cEmail;
          $_SESSION['cPhone'] = $cPhone;
      } else {
          $cName = $cEmail = $cPhone = "";
      }
  }

  // Manage form steps
  $currentStep = 'bookingDetails'; // Preserve session step

  // Step 1: Handle Booking Details Submission
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      if (isset($_POST['step']) && $_POST['step'] === 'bookingDetails') {
          // Correcting the order: Define duration first
          $duration = $_POST['duration']; // Move duration before using it
          $datestart = $_POST['datestart'];
          $dateend = date('Y-m-d H:i:s', strtotime("+$duration hours", strtotime($datestart)));
          
          $courtType = $_POST['courtType'];
          $people = $_POST['people'];
          $totalPrice = $_POST['totalPrice'];
          $preferredCourt = $_POST['preferredCourt'];
          $court_id = $_POST['preferredCourt'];

          // Store booking details in session
          $_SESSION['datestart'] = $datestart;
          $_SESSION['dateend'] = $dateend;
          $_SESSION['duration'] = $duration;
          $_SESSION['courtType'] = $courtType;
          $_SESSION['people'] = $people;
          $_SESSION['totalPrice'] = $totalPrice;
          $_SESSION['preferredCourt'] = $preferredCourt;
          $_SESSION['court_id'] = $court_id;

          // Get the total number of courts for the selected court type
          $court_sql = "SELECT total_courts FROM court_count WHERE courtType = ?";
          $stmt = $conn->prepare($court_sql);
          $stmt->bind_param("s", $courtType);
          $stmt->execute();
          $result = $stmt->get_result();

          if ($result && $result->num_rows > 0) {
              $court_data = $result->fetch_assoc();
              $total_courts = $court_data['total_courts'];

              // Check how many courts are booked during the selected time period
              $availability_sql = "SELECT COUNT(*) as booked_courts FROM bookings 
                                  WHERE courtType = ? 
                                  AND (? < dateend AND ? > datestart)";
              $stmt = $conn->prepare($availability_sql);
              $stmt->bind_param("sss", $courtType, $datestart, $dateend);
              $stmt->execute();
              $result = $stmt->get_result();
              $availability_data = $result->fetch_assoc();
              $booked_courts = $availability_data['booked_courts'];

              if ($booked_courts < $total_courts) {
                  // Court is available, proceed with the booking
                  $insert_sql = "INSERT INTO bookings (cName, cEmail, cPhone, datestart, dateend, courtType, people, price, preferredCourt, court_id, payment_status, transaction_id) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending', NULL)";
                  $stmt = $conn->prepare($insert_sql);
                  $stmt->bind_param("sssssssdss", $_SESSION['cName'], $_SESSION['cEmail'], $_SESSION['cPhone'], $datestart, $dateend, $courtType, $people, $totalPrice, $preferredCourt, $court_id);

                  if ($stmt->execute()) {
                      $_SESSION['booking_id'] = $stmt->insert_id;
                      // Move to confirmation step
                      $currentStep = 'confirmation';
                  } else {
                      echo "Error: " . $stmt->error;
                  }
              } else {
                  // All courts are booked, show an error message
                  echo "<script>";
                  echo "alert('All courts of the selected type are fully booked during the chosen time period. Please choose another time or court.');";
                  echo "history.back();";
                  echo "</script>";
              }
          } else {
              // Handle the case when no data is returned from court_count
              echo "<script>";
              echo "alert('Court type not found. Please select a valid court type.');";
              echo "history.back();";
              echo "</script>";
          }
      }
  }

  // Initialize form fields
  $cName = $_SESSION['cName'] ?? '';
  $cEmail = $_SESSION['cEmail'] ?? '';
  $cPhone = $_SESSION['cPhone'] ?? '';
  $datestart = $_SESSION['datestart'] ?? '';
  $dateend = $_SESSION['dateend'] ?? '';
  $duration = $_SESSION['duration'] ?? '';
  $courtType = $_SESSION['courtType'] ?? '';
  $people = $_SESSION['people'] ?? '';
  $preferredCourt = $_SESSION['preferredCourt'] ?? '';
  $court_id = $_SESSION['court_id'] ?? '';
  $totalPrice = $_SESSION['totalPrice'] ?? '';

  mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
  <title>Bookings</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" href="PZ_icon-32x32.png" type="image/png">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
  <link rel="stylesheet" href="stylee.css">
  <link rel="stylesheet" href="button.css">
  <link rel="stylesheet" href="form.css">
  <style>
    .date {
      width: 40% !important;
    }

    div > #availability {
      width: 51rem;
      height: 51rem;
    }

    #availability-head {
      display: flex;
      flex-direction: column;
    }

    #available-slots {
      background-color: #07a0c3;
      padding: 10px;
      border-radius: 15px;
    }

    .radio-columns {
      display: flex;
    }
    .radio-column {
      flex: 1;
      display: flex;
      flex-direction: column;
    }
    .radio-column span {
      width: 10px;
      margin-left: 50px;
      font-size: 18px;
      display: flex;
      font-family: 'League Spartan', sans-serif;
    }
    .radio-column input {
      margin-right: 5px;
    }
    .reserve {
      display: flex;
    }
    .column-1 {
      flex: 1;
    }
    .phone-input-container {
      display: flex;
      margin-bottom: 15px;
    }

    .country-code-select {
      width: 25%; /* Adjust the width as needed */
      padding: 10px;
      margin-bottom: 15px;
      border: 1px solid #ccc;
      border-radius: 4px;
      box-sizing: border-box;
    }

    .phone-input {
      width: 75%; /* Adjust the width as needed */
    }
    /* Calendar table styling */
    .calendar-table {
      width: 100%;
      border-collapse: collapse;
    }
    .calendar-table th, .calendar-table td {
      border: 1px solid #ddd;
      padding: 8px;
      text-align: center;
    }
    .calendar-table th {
      background-color: #f2f2f2;
      font-weight: bold;
    }
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
    .step { display: none; }
    .step.active { display: block; }
    .slots { margin-top: 15px; }

    #next-step {
        background-color: gray; /* Default disabled state */
        color: white;
        border: none;
        padding: 10px 20px;
        cursor: not-allowed;
    }

    #next-step:enabled {
        cursor: pointer;
    }

  /* Custom styles */
  .section {
      display: flex;
      flex-direction: column;
  }
  .table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 16px;
      margin-bottom: 0px;
  }
  .table th, .table td {
      border: 1px solid #ddd;
      padding: 8px;
  }
  .table th {
      background-color: #f2f2f2;
      text-align: left;
  }
  .table-box {
    box-shadow: var(--hover-shadows);
    --hover-shadows: 16px 16px 33px #07A0C3, -16px -16px 33px #8FEBFF;
    width: 100%;
    height: 100%;
    padding: 10px;
    background-color: #fff;
    transition: box-shadow 0.3s ease-in-out;
    border-radius: 10px;
  }
  .flex-container {
    display: flex;
    justify-content: space-between;
    gap: 30px; /* Adjust spacing between sections as desired */
  }
  #bookingForm {
    padding-top: 16px;
    padding-left: 16px;
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

  /* payment method */
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

  .payment-options {
    display: flex;
    flex-direction: column;
    /* justify-content: space-between; */
    align-items: center;
    margin-top: 10px;
  }

  #paypal-section {
      flex: 1;
      text-align: left;
      height: 4rem;
      width: 100%;
  }

  #paypal-button-container {
      margin-left: 0; /* Align to the left */
  }

  #tng-section {
      flex: 1;
      text-align: center;
      width: 100%;
      margin-bottom: 16px;
  }

  #tng-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background-color: #012169; /* Touch 'n Go Blue */
        color: white;
        font-family: Arial, sans-serif;
        font-size: 16px;
        font-weight: bolder;
        font-style: italic;
        padding: 10px 0px 8px 0px;
        width: 100%;
        height: max-content;
        border: none;
        border-radius: 3px;
        cursor: pointer;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
    }

    #tng-button img {
        width: 24px;
        height: 24px;
        margin-right: 10px;
    }

    #tng-button:hover {
        background-color: #0a2c86; /* Darker Blue */
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
    }

    #tng-button:active {
        background-color: #001f4d; /* Even Darker Blue */
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.5);
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
        <?php if ($currentStep === 'bookingDetails'): ?>
            <span id="step-1" class="p6 step-header active-step">
                <img src="Icons/reserve-icon.svg" width="24" height="24" alt="Booking Icon"> Booking Details
            </span>
            <div class="loader"></div>
            <span id="step-2" class="p6 step-header">
                <img src="Icons/payment.svg" width="24" height="24" alt="Payment Icon"> Payment
            </span>
            <div class="loader-1"></div>
            <span id="step-3" class="p6 step-header">
                <img src="Icons/receipt-icon.svg" width="24" height="24" alt="Receipt Icon"> Receipt
            </span>
        <?php elseif ($currentStep === 'confirmation'): ?>
            <span id="step-1" class="p6 step-finish">
                <img src="Icons/reserve-icon.svg" width="24" height="24" alt="Booking Icon"> Booking Details
            </span>
            <div class="loader-2"></div>
            <span id="step-2" class="p6 step-header active-step">
                <img src="Icons/payment.svg" width="24" height="24" alt="Payment Icon"> Payment
            </span>
            <div class="loader"></div>
            <span id="step-3" class="p6 step-header">
                <img src="Icons/receipt-icon.svg" width="24" height="24" alt="Receipt Icon"> Receipt
            </span>
        <?php elseif ($currentStep === 'receipt'): ?>
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
        <?php endif; ?>
    </div>
    <!-- Step 1: Booking Details -->
    <div id="bookingContent" class="booking-section">
        <div class="step <?= $currentStep === 'bookingDetails' ? 'active' : '' ?>" id="step-bookingDetails">
          <div class="flex-container">

            <div class="login-container">
              <section id="bookingForm" class="section">
                <form method="POST" id="booking-details-form" class="form booking" action="bookings.php">
                  <div class="reserve">
                      <div class="column-1 book_c1">
                          <div class="form-group">
                              <label for="cName">Full Name</label>
                              <input type="text" name="cName" id="cName" value="<?= htmlspecialchars($cName) ?>" required>
                          </div>
                          <div class="form-group">
                              <label for="cEmail">Email</label>
                              <input type="email" name="cEmail" id="cEmail" value="<?= htmlspecialchars($cEmail) ?>" required>
                          </div>
                          <div class="form-group">
                              <label for="cPhone">Phone</label>
                              <input type="tel" name="cPhone" id="cPhone" value="<?= htmlspecialchars($cPhone) ?>" required>
                          </div>
                          <div class="form-group2">
                              <label for="people">Number of Participants</label>
                              <input type="number" name="people" id="people" value="<?= htmlspecialchars($people) ?>" required min="1">
                          </div>
                      </div>
                      <div class="column-1 book_c2">
                          <div class="form-group">
                              <label for="courtType">Court Type</label>
                              <select name="courtType" id="courtType" required>
                                  <option value=""></option>
                                  <option value="Basketball" <?= $courtType === 'Basketball' ? 'selected' : '' ?>>&nbsp;&nbsp;Basketball</option>
                                  <option value="Badminton" <?= $courtType === 'Badminton' ? 'selected' : '' ?>>&nbsp;&nbsp;Badminton</option>
                                  <option value="Volleyball" <?= $courtType === 'Volleyball' ? 'selected' : '' ?>>&nbsp;&nbsp;Volleyball</option>
                                  <option value="Tennis" <?= $courtType === 'Tennis' ? 'selected' : '' ?>>&nbsp;&nbsp;Tennis</option>
                                  <option value="Futsal" <?= $courtType === 'Futsal' ? 'selected' : '' ?>>&nbsp;&nbsp;Futsal</option>
                                  <option value="Bowling" <?= $courtType === 'Bowling' ? 'selected' : '' ?>>&nbsp;&nbsp;Bowling</option>
                                  <option value="PSXbox" <?= $courtType === 'PSXbox' ? 'selected' : '' ?>>&nbsp;&nbsp;PS/Xbox</option>
                              </select>
                          </div>
                          <div class="form-group">
                              <label for="datestart">Date & Time</label>
                              <input type="datetime-local" class="datetime" name="datestart" id="datestart" value="<?= htmlspecialchars($datestart) ?>" required>
                          </div>
                          <div class="form-group">
                              <label for="duration">Duration (hours)</label>
                              <input type="number" name="duration" id="duration" value="<?= htmlspecialchars($duration) ?>" required min="1" max="12">
                          </div>
                          <div class="form-group2">
                              <label for="totalPrice">Total Price (RM)</label>
                              <input type="text" name="totalPrice" id="totalPrice" value="<?= htmlspecialchars($totalPrice) ?>" readonly>
                          </div>
                      </div>
                  </div>
                  <div class="reserve-details">
                    <span class="preferredCourt">Select your preferred Court Number</span>
                      <div class="details-group">
                          <div id="available-court">Available options will be shown here</div>
                      </div>
                  </div>
                  <div class="reserve reserveBtn">
                      <button type="reset" class="clearBtn" id="clear-form">Clear</button>
                      <button type="submit" class="submitBtn" name="step" value="bookingDetails" id="next-step" disabled>Next</button>
                  </div>
                </form>
              </section>
            </div>

            <div id="availability" class="availability-table">
              <section id="availabilityTable" class="section table-box">
                <span class="p5">Check Availability</span>
                <div id="availability-head">
                  <span class="p7">Court Type: <span id="chosen-court-type" class="p8">Please choose a court type</span></span>
                  <span class="p7">Date: <span id="chosen-date" class="p8">Please choose a date</span></span>
                </div>

                <table id="availability-table" class="table">
                  <thead>
                    <tr>
                      <th>Time</th>
                      <th>Court Number</th>
                      <th>Status</th>
                    </tr>
                  </thead>
                  <tbody id="availability-body">
                    <tr>
                      <td colspan='3'>Choose a type & date to check availability</td>
                    </tr>
                  </tbody>
                </table>
              </section>
            </div>

          </div>
        </div>
    </div>

        <!-- Step 2: Payment -->
        <div class="step <?= $currentStep === 'confirmation' ? 'active' : '' ?>" id="step-confirmation">
          <div class="step-container">
            <div class="payment-container">
                <span class="p7" style="margin-top: 16px;">CHECKOUT</span>
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
            </div>
            <div class="method-container">
            <div class="">
                  <span class="p7">PAYMENT METHOD</span>
                  <div class="payment-options">
                    <!-- Touch 'n Go QR Code -->
                    <div id="tng-section">
                      <button type="button" id="tng-button" class="p6">
                        <img src="Icons/tng-icon.png" alt="TNG Icon"> Touch 'n Go
                      </button>
                    </div>
                    
                    <!-- PayPal Button -->
                    <div id="paypal-section">
                      <div id="paypal-button-container"></div>
                    </div>
                  </div>
                </div>

                <script src="https://www.paypal.com/sdk/js?client-id=Adq9ccMWAeJEkGWPnUI_ZE_sDA1WB4POGfjjCfgoOJnGVvY9X143fMioow2H6bLsgr-dvXJNx4nLsLjX&currency=MYR"></script>
                <script>
                  paypal.Buttons({
                      style: {
                          layout: 'vertical',
                          color: 'gold',
                          shape: 'rect',
                          label: 'paypal',
                          tagline: false,
                          height: 40
                      },
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
                              console.log('Transaction completed by: ', details);

                              const transactionId = details.id;
                              const xhr = new XMLHttpRequest();
                              xhr.open('POST', 'process_payment.php', true);
                              xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                              xhr.onreadystatechange = function () {
                                  if (xhr.readyState === 4 && xhr.status === 200) {
                                      // Redirect to the receipt
                                      window.location.href = 'receipt.php?booking_id=' + encodeURIComponent(<?= $_SESSION['booking_id'] ?>);
                                  }
                              };
                              xhr.send('transaction_id=' + encodeURIComponent(transactionId));
                          });
                      }
                  }).render('#paypal-button-container');
                </script>
            </div>
          </div>
        </div>
        
      </div>
    </div>

  <footer class="container-fluid text-center">
      <div class="collapse navbar-collapse" id="myNavbar">
        <ul class="nav navbar-nav navbar-right">
          <li>
            <h5 >Open-source Apache Licensed</h5>
          </li>
        </ul>
      </div>
    </footer>

  <script>
    // Redirect when the button is clicked
    document.getElementById('tng-button').addEventListener('click', function () {
        window.location.href = 'https://payment.tngdigital.com.my/sc/bDLnVG4UG0';
    });
  </script>
  <script>
    // Automatically set the min date and time for booking (prevent past dates)
    const dateInput = document.getElementById('datestart');
    const now = new Date();
    const formattedDate = now.toISOString().slice(0, 16); // Ensures correct format YYYY-MM-DDTHH:MM
    dateInput.setAttribute('min', formattedDate); // Set minimum to current date and time

    const courtType = document.getElementById('courtType');
    const datestart = document.getElementById('datestart');
    const duration = document.getElementById('duration');
    const nextStepButton = document.getElementById('next-step');
    const slotsDiv = document.getElementById('available-court');
    const availabilityBody = document.getElementById('availability-body');
    const chosenDateEl = document.getElementById('chosen-date');
    const chosenCourtTypeEl = document.getElementById('chosen-court-type');

    // Helper to enable/disable the next button
    function toggleNextButton(state) {
        nextStepButton.disabled = !state;
        nextStepButton.style.backgroundColor = state ? '#82c87e' : 'gray';  // Change button color
    }

    function updateAvailabilityHead(courtTypeVal, datestartVal) {
      const formattedDate = datestartVal 
        ? new Date(datestartVal).toLocaleDateString('en-GB', {
              day: '2-digit',
              month: 'long',
              year: 'numeric',
          })
        : 'Please choose a date';

        chosenDateEl.textContent = formattedDate;
        chosenCourtTypeEl.textContent = courtTypeVal || 'Please choose a court type';
    }

    function updateAvailabilityTable(data) {
      if (data.bookings && data.bookings.length > 0) {
          let tableHtml = '';
          data.bookings.forEach(booking => {
              tableHtml += `
                  <tr>
                      <td>${booking.time}</td>
                      <td>Court ${booking.court}</td>
                      <td>${booking.status}</td>
                  </tr>`;
          });
          availabilityBody.innerHTML = tableHtml;
      } else if (data.message) {
          availabilityBody.innerHTML = `
              <tr>
                  <td colspan="3">${data.message}</td>
              </tr>`;
      }
    }

    function checkAvailabilityTable() {
      const courtTypeVal = courtType.value;
      const datestartVal = datestart.value;

      updateAvailabilityHead(courtTypeVal, datestartVal);

      if (!courtTypeVal || !datestartVal) {
          availabilityBody.innerHTML = `
              <tr>
                  <td colspan="3">Please select a court type and date to check availability.</td>
              </tr>`;
          return;
      }

      const xhr = new XMLHttpRequest();
      xhr.open('POST', 'fetchBookedCourts.php', true);
      xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

      xhr.onreadystatechange = function () {
          if (xhr.readyState === 4) {
              try {
                  const response = JSON.parse(xhr.responseText);
                  updateAvailabilityTable(response);
              } catch (e) {
                  console.error('Error parsing response:', e);
                  availabilityBody.innerHTML = `
                      <tr>
                          <td colspan="3">An error occurred while fetching availability.</td>
                      </tr>`;
              }
          }
      };

      xhr.send(`courtType=${courtTypeVal}&datestart=${datestartVal}`);
    }

    // Function to check court availability when any field changes
    function checkAvailability() {
        const courtTypeVal = courtType.value;
        const datestartVal = datestart.value;
        const durationVal = duration.value;

        if (!courtTypeVal || !datestartVal || !durationVal) {
            slotsDiv.innerHTML = "Please fill out <strong>Court Type, Date and Duration</strong> fields.";
            toggleNextButton(false);
            return;
        }

        // AJAX call to fetch available courts
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'fetchSlot_Booking.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

        xhr.onreadystatechange = function() {
          if (xhr.readyState === 4) {
              try {
                  const response = JSON.parse(xhr.responseText);
                  if (response.error) {
                      slotsDiv.innerHTML = `<p>${response.error}</p>`;
                      toggleNextButton(false);
                  } else {
                      let slotsHtml = '';
                      response.slots.forEach(slot => {
                          slotsHtml += `
                              <div class="court-card">
                                  <input type="radio" id="${slot.court}" name="preferredCourt" value="${slot.court}" required>
                                  <span for="${slot.court}">${slot.court}</span>
                              </div>`;
                      });
                      slotsDiv.innerHTML = slotsHtml;
                      toggleNextButton(true); // Enable next button when courts are available
                  }
              } catch (e) {
                  console.error('Error parsing JSON:', e);
                  slotsDiv.innerHTML = "An error occurred while displaying options. Please try again.";
                  toggleNextButton(false);
              }
          }
        };

        // Send data to server
        xhr.send(`courtType=${courtTypeVal}&datestart=${datestartVal}&duration=${durationVal}`);
    }

    // Attach change event listeners to trigger availability check
    courtType.addEventListener('change', checkAvailability);
    datestart.addEventListener('change', checkAvailability);
    duration.addEventListener('input', checkAvailability);
    courtType.addEventListener('change', checkAvailabilityTable);
    datestart.addEventListener('change', checkAvailabilityTable);

    // Clear form and reset availability
    document.getElementById('clear-form').addEventListener('click', function(event) {
        event.preventDefault();

        document.getElementById('cName').value = '';
        document.getElementById('courtType').value = '';
        document.getElementById('cEmail').value = '';
        document.getElementById('datestart').value = '';
        document.getElementById('cPhone').value = '';
        document.getElementById('duration').value = '';
        document.getElementById('people').value = '';
        document.getElementById('totalPrice').value = '';

        slotsDiv.innerHTML = "Available options will be shown here";
        toggleNextButton(false);
        dateInput.setAttribute('min', formattedDate);
    });
  </script>
  <script>
    document.getElementById('courtType').addEventListener('change', calculatePrice);
    document.getElementById('duration').addEventListener('input', calculatePrice);

    function calculatePrice() {
      const courtType = document.getElementById('courtType').value;
      const duration = document.getElementById('duration').value;
      let pricePerHour = 0;

      switch(courtType) {
        case 'Basketball':
          pricePerHour = 0.01;
          break;
        case 'Badminton':
          pricePerHour = 6;
          break;
        case 'Volleyball':
          pricePerHour = 7;
          break;
        case 'Tennis':
          pricePerHour = 8;
          break;
        case 'Futsal':
          pricePerHour = 12;
          break;
        case 'Bowling':
          pricePerHour = 15;
          break;
        case 'PSXbox':
          pricePerHour = 8;
          break;
        default:
          pricePerHour = 0;
      }

      const totalPrice = pricePerHour * duration;
      document.getElementById('totalPrice').value = totalPrice ? `${totalPrice.toFixed(2)}` : '';
    }
  </script>
  <script src="scripts.js"></script>
</body>
</html>