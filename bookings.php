<?php
  session_start();
  require_once 'database.php';

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
  $currentStep = 'bookingDetails'; // Default step

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
          $preferredCourt = $_POST['preferredCourt'] ?? '';  // Fix the undefined array key error

          // Store booking details in session
          $_SESSION['datestart'] = $datestart;
          $_SESSION['dateend'] = $dateend;
          $_SESSION['duration'] = $duration;
          $_SESSION['courtType'] = $courtType;
          $_SESSION['people'] = $people;
          $_SESSION['totalPrice'] = $totalPrice;
          $_SESSION['preferredCourt'] = $preferredCourt;

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
                  $insert_sql = "INSERT INTO bookings (cName, cEmail, cPhone, datestart, dateend, courtType, people, price, preferredCourt, payment_status, transaction_id) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending', NULL)";
                  $stmt = $conn->prepare($insert_sql);
                  $stmt->bind_param("sssssssds", $cName, $cEmail, $cPhone, $datestart, $dateend, $courtType, $people, $totalPrice, $preferredCourt);

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
    .form-group label {
      margin-bottom: 10px;
      display: block;
    }

    .form-group input {
      width: 100%;
      padding: 10px;
      margin-bottom: 20px;
      box-sizing: border-box;
    }

    .form-group input[type="radio"] {
      margin-right: 5px; /* Add margin between radio buttons */
    }
    
    .date {
      width: 40% !important;
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
      display: inline-block;
      padding: 10px 20px;
      margin: 0 0 64px 16px;
      border-radius: 5px;
      color: #fff;
      background-color: #626262;
    }
    .active-step {
      background-color: #102A7E; /* 28a745 */
      font-weight: bold;
    }
    .step { display: none; }
    .step.active { display: block; }
    .slots { margin-top: 15px; }
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
    <div id="booking-steps">
    <div class="steps-heading">
      <span class="fa fa-calendar step-header <?= $currentStep === 'bookingDetails' ? 'active-step' : '' ?>"> Booking Details</span>
      <span class="fa fa-check-square-o step-header <?= $currentStep === 'confirmation' ? 'active-step' : '' ?>"> Confirmation</span>
    </div>
    <!-- Step 1: Booking Details -->
    <div class="login-container">
      <div class="step <?= $currentStep === 'bookingDetails' ? 'active' : '' ?>" id="step-bookingDetails">
        <form method="POST" id="booking-details-form" class="form booking" action="bookings.php">
            <div class="reserve">
                <div class="column-1 book_c1">
                    <div class="form-group">
                        <input type="text" name="cName" id="cName" value="<?= htmlspecialchars($cName) ?>" required>
                        <label for="cName">Full Name</label>
                    </div>
                    <div class="form-group">
                        <select name="courtType" id="courtType" required>
                            <option value=""></option>
                            <option value="Basketball" <?= $courtType === 'Basketball' ? 'selected' : '' ?>>Basketball</option>
                            <option value="Badminton" <?= $courtType === 'Badminton' ? 'selected' : '' ?>>Badminton</option>
                            <option value="Volleyball" <?= $courtType === 'Volleyball' ? 'selected' : '' ?>>Volleyball</option>
                            <option value="Tennis" <?= $courtType === 'Tennis' ? 'selected' : '' ?>>Tennis</option>
                            <option value="Futsal" <?= $courtType === 'Futsal' ? 'selected' : '' ?>>Futsal</option>
                        </select>
                        <label for="courtType">Select Court Category</label>
                    </div>
                </div>
                <div class="column-1 book_c2">
                    <div class="form-group">
                        <input type="email" name="cEmail" id="cEmail" value="<?= htmlspecialchars($cEmail) ?>" required>
                        <label for="cEmail">Email</label>
                    </div>
                    <div class="form-group">
                        <input type="datetime-local" class="datetime" name="datestart" id="datestart" value="<?= htmlspecialchars($datestart) ?>" required>
                        <label for="datestart">Date & Time</label>
                    </div>
                </div>
                <div class="column-1 book_c3">
                    <div class="form-group">
                        <input type="text" name="cPhone" id="cPhone" value="<?= htmlspecialchars($cPhone) ?>" required>
                        <label for="cPhone">Phone</label>
                    </div>
                    <div class="form-group">
                        <input type="number" name="duration" id="duration" value="<?= htmlspecialchars($duration) ?>" required min="1" max="12">
                        <label for="duration">Duration (hours)</label>
                    </div>
                </div>
                <div class="column-1 book_c4">
                    <div class="form-group">
                        <input type="number" name="people" id="people" value="<?= htmlspecialchars($people) ?>" required min="1">
                        <label for="people">Number of Participants</label>
                    </div>
                    <div class="form-group">
                        <input type="text" name="totalPrice" id="totalPrice" value="<?= htmlspecialchars($totalPrice) ?>" readonly>
                        <label for="totalPrice">Total Price</label>
                    </div>
                </div>
            </div>
            <div class="reserve">
                <div class="form-group">
                    <label for="preferredCourt">Select your preferred Court Number</label>
                    <div id="available-court">Available court will be shown here</div>
                </div>
            </div>
            <div class="reserve">
                <button type="button" class="checkBtn" id="check-availability">Check Availability</button>
                <button type="submit" class="submitBtn" name="step" value="bookingDetails" id="next-step" disabled>Next</button>
            </div>
        </form>
      </div>

        <!-- Step 2: Confirmation -->
        <div class="step <?= $currentStep === 'confirmation' ? 'active' : '' ?>" id="step-confirmation">
            <h3>Confirmation</h3>
            <p><strong>Full Name:</strong> <?= htmlspecialchars($cName) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($cEmail) ?></p>
            <p><strong>Phone:</strong> <?= htmlspecialchars($cPhone) ?></p>
            <p><strong>Participants:</strong> <?= htmlspecialchars($people) ?></p>
            <p><strong>Court Type:</strong> <?= htmlspecialchars($courtType) ?></p>
            <p><strong>Date & Time:</strong> <?= htmlspecialchars($datestart) ?></p>
            <p><strong>End Date & Time:</strong> <?= htmlspecialchars($dateend) ?></p>
            <p><strong>Duration:</strong> <?= htmlspecialchars($duration) ?> hours</p>
            <p><strong>Total Price:</strong> RM<?= htmlspecialchars($totalPrice) ?></p>
            <p><strong>Preferred Court:</strong> <?= htmlspecialchars($preferredCourt) ?></p>
            
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
    // Automatically set the min date and time for booking (prevent past dates)
    const dateInput = document.getElementById('datestart');
    const now = new Date();
    const formattedDate = now.toISOString().slice(0, 16);
    dateInput.setAttribute('min', formattedDate);

    document.getElementById('check-availability').addEventListener('click', function() {
      const courtType = document.getElementById('courtType').value;
      const datestart = document.getElementById('datestart').value;
      const duration = document.getElementById('duration').value;

      const startDateTime = new Date(datestart);
      const timestart = startDateTime.toTimeString().slice(0, 5); // Extract time in HH:mm format

      if (!courtType || !datestart || !timestart || !duration) {
        alert('Please fill out all fields required to check availability.');
        return;
      }

      // AJAX call to fetch available courts
      const xhr = new XMLHttpRequest();
      xhr.open('POST', 'fetchSlot_Booking.php', true);
      xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
      
      xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            // Check if the response is valid JSON
            try {
                const response = JSON.parse(xhr.responseText);
                const slotsDiv = document.getElementById('available-court');
                
                if (response.error) {
                    slotsDiv.innerHTML = `<p>${response.error}</p>`;
                } else {
                    let slotsHtml = '<h4>Select Preferred Court:</h4>';
                    response.slots.forEach(slot => {
                        slotsHtml += `
                            <div>
                                <input type="radio" name="preferredCourt" value="${slot.court}" required> 
                                Court ${slot.court} (${slot.status})
                            </div><br>`;
                    });
                    slotsDiv.innerHTML = slotsHtml;

                    document.getElementById('next-step').disabled = false; // Enable next button
                }
            } catch (e) {
                console.error('Error parsing JSON:', e);
                console.log('Raw response:', xhr.responseText); // Log raw response for debugging
                alert('An error occurred while checking availability. Please try again.');
            }
        }
      };
      // Send data to server
      xhr.send(`courtType=${courtType}&datestart=${datestart}&duration=${duration}`);
    });
  </script>
  <script>
    document.getElementById('duration').addEventListener('input', calculatePrice);

    function calculatePrice() {
      const courtType = document.getElementById('courtType').value;
      const duration = document.getElementById('duration').value;
      let pricePerHour = 0;

      switch(courtType) {
        case 'Basketball':
          pricePerHour = 8;
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
      document.getElementById('totalPrice').value = totalPrice.toFixed(2);
    }
  </script>
  <script src="scripts.js"></script>
</body>
</html>