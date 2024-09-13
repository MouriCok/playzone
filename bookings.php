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
          // Handle the case when the query fails or no rows found
          $cName = $cEmail = $cPhone = "";
      }
  } else {
      // If not logged in, set default values or handle the case as needed
      $cName = $cEmail = $cPhone = "";
  }

  if (isset($_POST['submit'])) {
      // Retrieve form data
      $datestart = $_POST['datestart'];
      $duration = $_POST['duration'];
      $dateend = date('Y-m-d H:i:s', strtotime("+$duration hours", strtotime($datestart)));
      $courtType = $_POST['courtType'];
      $people = $_POST['people'];
      $totalPrice = $_POST['totalPrice'];

      // Store form data in session variables for use in process_payment.php
      $_SESSION['datestart'] = $datestart;
      $_SESSION['dateend'] = $dateend;
      $_SESSION['courtType'] = $courtType;
      $_SESSION['people'] = $people;
      $_SESSION['totalPrice'] = $totalPrice;

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
              $insert_sql = "INSERT INTO bookings (cName, cEmail, cPhone, datestart, dateend, courtType, people, price, payment_status, transaction_id) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Pending', NULL)";
              $stmt = $conn->prepare($insert_sql);
              $stmt->bind_param("sssssssd", $cName, $cEmail, $cPhone, $datestart, $dateend, $courtType, $people, $totalPrice);

              if ($stmt->execute()) {
                  // Redirect to confirmation.php after successful booking and session data setup
                  echo "<script>window.location.href = 'confirmation.php';</script>";
                  exit(); // Ensure the script stops execution after the redirect
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
    <div class="col-md-6">
      <div id="available-slots"></div>
    </div>
    <div class="login-container">
      <h2>Bookings</h2>
      <form method="post" action="bookings.php" class="form booking">
        <div class="reserve">
          
          <div class="column-1">
            <div class="form-group">
              <input type="text" id="cName" name="cName" required value="<?= $cName ?>">
              <label for="cName">Full Name</label>
            </div>
            <div class="form-group">
              <input type="email" id="cEmail" name="cEmail" required value="<?= $cEmail ?>">
              <label for="cEmail">Email</label>
            </div>
            <div class="form-group phone-input-container">
              <input type="text" id="cPhone" name="cPhone" class="phone-input" required value="<?= $cPhone ?>">
              <label for="cPhone">Phone</label>
            </div>
            <div class="form-group">
              <input type="number" id="people" name="people" min="1" required>
              <label for="people">Participants</label>
            </div>
          </div>

          <div class="column-1">
            <div class="form-group">
              Court Type:<br>
              <select class="court" id="courtType" name="courtType" required>
                <option value="Basketball">Basketball</option>
                <option value="Badminton">Badminton</option>
                <option value="Volleyball">Volleyball</option>
                <option value="Tennis">Tennis</option>
                <option value="Futsal" selected>Futsal</option>
                <option value="Bowling">Bowling</option>
                <option value="PSXbox">PS/Xbox</option>
              </select>
            </div>
            <div class="form-group">
              Date:<br>
              <input class="datetime" type="datetime-local" id="datestart" name="datestart" required><br>
            </div>
            <div class="form-group">
              Duration (in hours):<br>
              <input type="number" id="duration" name="duration" min="1" max="12" required>
            </div>
            <div class="form-group">
              Total Price:<br>
              <input type="text" id="totalPrice" name="totalPrice" readonly>
            </div>
          </div>

        </div>
        <button type="submit" name="submit" class="submitBtn btn btn-default">Reserve</button>
      </form>
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
    document.getElementById('datestart').addEventListener('change', fetchAvailableSlots);
    document.getElementById('courtType').addEventListener('change', fetchAvailableSlots);

    function fetchAvailableSlots() {
      const courtType = document.getElementById('courtType').value;
      const datestart = document.getElementById('datestart').value;
      
      if (courtType && datestart) {
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'fetchSlot_Booking.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onreadystatechange = function () {
          if (xhr.readyState === 4 && xhr.status === 200) {
            document.getElementById('available-slots').innerHTML = xhr.responseText;
          }
        };
        xhr.send('courtType=' + courtType + '&datestart=' + datestart);
      }
    }
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
