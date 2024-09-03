<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';

require_once 'database.php';

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Initialize the status variable
$status = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve email from the form
    $cEmail = $_POST["cEmail"];

    // Check if the user exists in the database
    $sql = "SELECT * FROM customer WHERE cEmail='$cEmail'";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);

    if ($result->num_rows > 0) {
        // User is registered, generate and send reset link
        $resetToken = bin2hex(random_bytes(16)); // Generate a unique token

        // Store the reset token and expiry time in the database
        $updateSql = "UPDATE customer SET reset_token='$resetToken', reset_expiry=DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE cEmail='$cEmail'";
        mysqli_query($conn, $updateSql);

        // Send reset link to the user's email
        $to = $cEmail;
        $subject = "Password Reset";
        $message = "Click the following link to reset your password: <a href='http://localhost/playzone/reset.php?token=$resetToken'>Reset Password</a>";

        // Using PHPMailer for sending email
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';  // Replace with your SMTP server
            $mail->SMTPAuth = true;
            $mail->Username = 'mbukhoury.mb@gmail.com';     // Replace with your SMTP username
            $mail->Password = 'heip jymz uria kpxt';     // Replace with your App Password
            $mail->SMTPSecure = 'tls';             // Enable TLS encryption, `ssl` also accepted
            $mail->Port = 587;                     // TCP port to connect to

            $mail->setFrom('mbukhoury.mb@gmail.com', 'PlayZone Customer Service');
            $mail->addAddress($to);

            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $message;

            $mail->send();

            // Set the success status
            $status = "success";
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    } else {
        // User account not registered, display an alert
        echo "<script>alert('Invalid Email or account not registered.');</script>";
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
  <title>Forgot Password</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" href="PZ_icon-32x32.png" type="image/png">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
  <link rel="stylesheet" href="stylee.css">
  <link rel="stylesheet" href="form.css">
  <style>
    .forget-pwd > a{
        color: #dc3545;
        font-weight: 500;
    }
    .forget-password .panel-body{
      background-color: #eed9c4;
      border-radius: 5px;
      box-shadow: 0 8px 16px rgba(0, 0, 0, 0.5);
      padding: 20px;
      width: 400px;
      text-align: center;
      display: flex;
      flex-direction: column;
    }
    .btnForget {
      background: #c0392b;
      border: none;
      width: 50%;
      align-items: center;
      display: block;
      margin: 0 auto; /* Horizontally center the button */
      text-align: center;
      padding: 10px;
      color: white;
      cursor: pointer;
      font-size: 16px;
      border-radius: 5px;
    }
    .forget-password .dropdown{
        width: 100%;
        border: 1px solid #ced4da;
    }
    .forget-password .dropdown button{
        width: 100%;
    }
    .forget-password .dropdown ul{
        width: 100%;
    }
    .forget-password .form-group {
        display: flex;
        flex-direction: column;
    }
    .forget-password select,
    .forget-password input {
      width: 100%;
      padding: 10px;
      margin-bottom: 15px;
      border: 1px solid #ccc;
      border-radius: 6px;
      box-sizing: border-box;
    }
    .forget-password label {
      position: absolute;
      left: 10px;
      top: 50%;
      transform: translateY(-50%);
      color: #bcbcbc;
      font-size: 14px;
      pointer-events: none;
      transition: top 0.3s, font-size 0.3s;
    }
    .forget-password input:focus + label,
    .forget-password input:valid + label {
      top: 10px;
      font-size: 12px;
      color: #82c87e;
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
            <span class="icon-bar"></span>
          </button>
        </div>
        <div class="collapse navbar-collapse" id="myNavbar">
          <ul class="nav navbar-nav">
            <li><img src="PZ_tp.svg" width="40" height="40" alt="Logo"></li>
            <li><a href="javascript:void(0);" onclick="goBack(event);" class="nav-btn"> Back</a></li>
            <li><a href="index.php" class="nav-btn">Home</a></li>
          </ul>
        </div>
      </div>
    </nav>
  </header>

  <div class="page-container forget-password text-center">
      <div class="row">
          <div class="col-md-12">
              <div class="panel panel-default">
                  <div class="panel-body">
                      <div class="text-center">
                          <!-- Display success message if status is set to 'success' -->
                          <?php if ($status === "success"): ?>
                            <div class="alert alert-success">
                              Password reset link sent successfully!
                            </div>
                          <?php endif; ?>

                          <img src="https://i.ibb.co/rshckyB/car-key.png" style="width: 20%;" alt="car-key">
                          <h2 class="text-center">Forgot Password?</h2>
                          <h4>You can reset your password here.</h4>
                          <form id="reset-form" role="form" autocomplete="off" class="form" method="post" action="#">
                            <div class="form-group">
                              <div class="input-group">
                                <input type="email" id="cEmail" name="cEmail" required>
                                <label for="cEmail"><i class="glyphicon glyphicon-envelope"></i> Your registered Email</label>
                              </div>
                            </div>
                            <div class="form-group">
                              <button name="btnForget" class="btn btn-lg btn-primary btn-block btnForget" type="submit">Reset Password</button>
                            </div>
                          </form>
                      </div>
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

  <script src="scripts.js"></script>
	</body>
</html>
