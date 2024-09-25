<?php
session_start();
require_once 'database.php';
$conn = mysqli_connect("localhost", "root", "", "classicmodels");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Check if the user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo "<script>";
    echo "if (confirm('Sorry for the inconvenience. You need to LOG INTO YOUR ACCOUNT first before making any update. Do you want to go to the login page?')) {";
    echo "  window.location.href='login.php';";
    echo "} else {";
    echo "  history.back();";
    echo "}";
    echo "</script>";
    exit;
}

if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];

    // Query player data
    $sql = "SELECT * FROM user WHERE username='$username'";
    $result = mysqli_query($conn, $sql);

    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $name = $row['name'];
        $email = $row['email'];
        $phone = $row['phone'];
    } else {
        // Handle the case when the query fails
        $name = "";
        $email = "";
        $phone = "";
    }
} else {
    // If not logged in, set default values or handle the case as needed
    $name = "";
    $email = "";
    $phone = "";
}

if (isset($_POST['submit'])) {
    // Retrieve other form data
    $name = $_POST['name'];
    $price = $_POST['price'];
    $foodtruckId = $_POST['foodtruckId'];

    // Insert the booking information into the database
    $sql = "INSERT INTO menu (name, price, foodtruckId) VALUES ('$name', '$price', '$foodtruckId')";

    if (mysqli_query($conn, $sql)) {
      echo "<script>";
      echo "alert('New Menu is added!\\nPress OK to continue.');";
      echo "window.location.href = 'profile.php';";
      echo "</script>";
    } else {
        echo "Error: " . $sql . "<br>" . mysqli_error($conn);
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
  <title>List of Bookings</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" href="PZ_icon-32x32.png" type="image/png">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
  <link rel="stylesheet" href="stylee.css">
  <link rel="stylesheet" href="form.css">
  <style>
    .login-container {
      padding: 20px;
      width: 450px;
      margin-top: 100px;
      margin-bottom: 88px;
    }
  </style>
  <script>
    function goBack(event) {
      event.preventDefault();
      window.history.back();
    }
  </script>
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

  <div class="page-container"></div>

  <footer class="container-fluid text-center">
  <div class="collapse navbar-collapse" id="myNavbar">
    <ul class="nav navbar-nav navbar-right">
      <li>
        <h5 >Open-source Apache Licensed</h5>
      </li>
    </ul>
  </div>
</footer>
	</body>
</html>
