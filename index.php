<?php
session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';

require_once 'database.php';

// Establish database connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Logout logic
if (isset($_GET["logout"])) {
    unset($_SESSION['logged_in']);
    unset($_SESSION['cUser']);
    unset($_SESSION['cAvatar']);
    session_destroy();
    header("Location: index.php");
    exit();
}

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['submit'])) {
        $cUser = $_POST['cUser'] ?? '';
        $cPass = $_POST['cPass'] ?? '';

        if (!empty($cUser) && !empty($cPass)) {
            // Prepare the SQL statement
            $stmt = $conn->prepare("SELECT * FROM customer WHERE cUser = ? AND cPass = ?");
            $stmt->bind_param("ss", $cUser, $cPass);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $_SESSION['logged_in'] = true;
                $_SESSION['cUser'] = $cUser;
                header("Location: profile.php");
                exit();
            } else {
                $error_message  = '<div class="modal-header">
                                    <h4 class="modal-title" id="errorModalLabel">Login Error</h4>
                                  </div>
                                  <div class="modal-body" style="color: red; background-color: rgba(255, 77, 77, 0.3);">
                                    <span>Invalid Username or Password</span>
                                  </div>
                                  <div class="modal-footer">
                                    <button type="button" class="btn btn-primary" onclick="window.history.back()">OK</button>
                                  </div>';
            }

            // Close statement
            $stmt->close();
        } else {
            $error_message  = '<div class="modal-header">
                                <h4 class="modal-title" id="errorModalLabel">Login Error</h4>
                              </div>
                              <div class="modal-body" style="color: red; background-color: rgba(255, 77, 77, 0.3);">
                                <span>Please fill in both fields.</span>
                              </div>
                              <div class="modal-footer">
                                <button type="button" class="btn btn-primary" onclick="window.history.back()">OK</button>
                              </div>';
        }
    }
}

$status = '';

// Password reset logic
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['btnForget'])) {
  $cEmail = $_POST["cEmail"] ?? '';

  if (!empty($cEmail)) {
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
              $mail->Host = 'smtp.gmail.com';// Replace with your SMTP server
              $mail->SMTPAuth = true;
              $mail->Username = 'mbukhoury.mb@gmail.com';// Replace with your SMTP username
              $mail->Password = 'heip jymz uria kpxt';// Replace with your App Password
              $mail->SMTPSecure = 'tls';// Enable TLS encryption, `ssl` also accepted
              $mail->Port = 587;// TCP port to connect to

              $mail->setFrom('mbukhoury.mb@gmail.com', 'PlayZone Customer Service');
              $mail->addAddress($to);

              $mail->isHTML(true);
              $mail->Subject = $subject;
              $mail->Body = $message;

              $mail->send();

              // Set the success status
              $status = '<div class="modal-header">
                        <h4 class="modal-title" id="successModalLabel">Status Message</h4>
                      </div>
                      <div class="modal-body" style="color: green; background-color: rgba(77, 255, 77, 0.3);">
                        <span>Password reset link sent successfully!</span>
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-primary" onclick="window.history.back()">OK</button>
                      </div>';
          } catch (Exception $e) {
              $status = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
          }
      } else {
          // User account not registered, display an alert
          $status = '<div class="modal-header">
                      <h4 class="modal-title" id="successModalLabel">Status Message</h4>
                    </div>
                    <div class="modal-body" style="color: red; background-color: rgba(255, 77, 77, 0.3);">
                      <span>Invalid Email or account not registered.</span>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-primary" onclick="window.history.back()">OK</button>
                    </div>';
      }
  } else {
      // If cEmail is empty, display an alert
      $status = '<div class="modal-header">
                  <h4 class="modal-title" id="successModalLabel">Status Message</h4>
                </div>
                <div class="modal-body" style="color: red; background-color: rgba(255, 77, 77, 0.3);">
                  <span>Please enter your registered email.</span>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-primary" onclick="window.history.back()">OK</button>
                </div>';
  }
}

$notify = '';

if(isset($_POST['submitBtn'])){
  $cName = $_POST['cName'];
  $cUser = $_POST['cUser'];
  $cEmail = $_POST['cEmail'];
  $cPhone = $_POST['cPhone'];
  $cPass = $_POST['cPass'];
  $ConPass = $_POST['ConPass'];
  $sql = "INSERT INTO customer (cName, cUser, cEmail, cPhone, cPass)
  VALUES ('$cName', '$cUser', '$cEmail', '$cPhone', '$cPass')";
  if (!empty($cName) && !empty($cUser) && !empty($cEmail) && !empty($cPhone) && !empty($cPass) && !empty($ConPass)
    && preg_match("/^[a-zA-Z-' ]*$/", $cName) && preg_match("/^[a-zA-Z0-9]+$/", $cUser) && filter_var($cEmail, FILTER_VALIDATE_EMAIL)
    && preg_match("/^[0-9]+$/", $cPhone) && preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]{8,}$/", $cPass) 
    && preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]{8,}$/", $ConPass)) {
      if (mysqli_query($conn, $sql)) {
        $notify = '<div class="modal-header">
                    <h4 class="modal-title" id="notifyModalLabel">Status Message</h4>
                  </div>
                  <div class="modal-body" style="color: green; background-color: rgba(77, 255, 77, 0.3);">
                    <span>Registration successful! You may now login.</span>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-primary" onclick="window.history.back()">OK</button>
                  </div>';
      } else {
        echo "Error: " . $sql . "<br>" . mysqli_error($conn);
      }
  } else {
    $notify = '<div class="modal-header">
                <h4 class="modal-title" id="notifyModalLabel">Status Message</h4>
              </div>
              <div class="modal-body" style="color: red; background-color: rgba(255, 77, 77, 0.3);">
                <span>Please fill in all the fields correctly.</span>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-primary" onclick="window.history.back()">OK</button>
              </div>';
  }
}

// Close connection
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <title>Play Zone</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <link rel="icon" href="PZ_icon-32x32.png" type="image/png">
    <script src="https://kit.fontawesome.com/cbf02b9426.js" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="stylee.css">
    <link rel="stylesheet" href="button.css">
    <link rel="stylesheet" href="modal.css">
</head>
<body>
<header>
    <nav class="navbar navbar-inverse">
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
                  <?php
                    if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']) {
                      echo '<li><a href="index.php" class="nav-btn">PlayZone</a></li>
                        <li><a href="bookings.php" class="nav-btn">Booking</a></li>';
                    } else {
                      echo '<li><a href="index.php" class="nav-btn">PlayZone</a></li>';
                    }
                  ?>
                </ul>
                <ul class="nav navbar-nav navbar-right">
                    <ul class="nav navbar-nav navbar-right">
                        <?php
                            if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']) {
                                $avatar = !empty($_SESSION['cAvatar']) ? $_SESSION['cAvatar'] : 'default_avatar.png';
                                $defaultAvatar = 'default_avatar.png';

                                // If user is logged in, show username with a dropdown menu
                                echo '
                                <li class="dropdown">
                                    <a href="profile.php" class="dropdown-toggle">
                                        <span class="glyphicon glyphicon-user"></span>&nbsp; ' . $_SESSION['cUser'] . ' <span class="caret"></span>
                                    </a>
                                    <ul class="dropdown-menu">
                                        <li class="d-m" style="display: flex; align-items: center;">
                                            <img src="' . $avatar . '" alt="$defaultAvatar" class="drop-circle" width="60" height="60" onerror="this.onerror=null; this.src=\'' . htmlspecialchars($defaultAvatar) . '\';">
                                            <div class="details">
                                                <span class="username" style="font-size: 18px; font-weight: bold; display: block;">' . $_SESSION['cUser'] . '</span>
                                                <span class="email" style="font-size: 12px; display: block;">' . $_SESSION['cEmail'] . '</span>
                                            </div>
                                        </li>
                                        <li class="dropdown-item"><a href="profile.php">Profile</a></li>
                                        <li class="dropdown-item"><a href="settings.php">Settings</a></li>
                                    </ul>
                                </li>';
                            } else {
                                // If user is not logged in, show login link
                                echo '<li class="login"><a href="" data-toggle="modal" data-target="#loginModal"><span class="glyphicon glyphicon-log-in"></span>&nbsp;&nbsp;Login</a></li>';
                            }
                        ?>
                        <?php
                            if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']) {

                                // If user is logged in, show logout button
                                echo '
                                <button class="logoutBtn" data-toggle="modal" data-target="#logoutModal">
                                  <div class="sign">
                                    <svg viewBox="0 0 512 512">
                                      <path d="M377.9 105.9L500.7 228.7c7.2 7.2 11.3 17.1 11.3 27.3s-4.1 20.1-11.3 27.3L377.9 406.1c-6.4 6.4-15 9.9-24 
                                      9.9c-18.7 0-33.9-15.2-33.9-33.9l0-62.1-128 0c-17.7 0-32-14.3-32-32l0-64c0-17.7 14.3-32 32-32l128 0 0-62.1c0-18.7 
                                      15.2-33.9 33.9-33.9c9 0 17.6 3.6 24 9.9zM160 96L96 96c-17.7 0-32 14.3-32 32l0 256c0 17.7 14.3 32 32 32l64 0c17.7 
                                      0 32 14.3 32 32s-14.3 32-32 32l-64 0c-53 0-96-43-96-96L0 128C0 75 43 32 96 32l64 0c17.7 0 32 14.3 32 32s-14.3 32-32 32z">
                                    </path></svg></div>
                                  <div class="text">Logout</div>
                                </button>';
                            } else {
                                // If user is not logged in, show nothing
                                echo '';
                            }
                        ?>
                    </ul>
                    
                </ul>
            </div>
        </div>
    </nav>
</header>
<div class="jumbo">
    <div class="container">
        <div class="">
            <div class="col-sm-9 text-left">
                <span class="p0">PlayZone</span><br>
                <span class="p1">"Where Passion Meets Play"</span><br>
                <span class="p2">
                  Easily book sports facilities through our website and app,<br>
                  rent high-quality sports equipment for any game,<br>
                  and enjoy fast and secure payments with QR code technology.<br>
                  Join community events, leagues, and activities.</span><br><br>
            </div>
            <div class="col-sm-3">
                <img src="PZ_tp.svg" class="pull-right no-shadow" style="width:150%;" alt="Logo">
            </div>
        </div>
    </div>
</div>

<div class="container scroll-card">
  <span class="p4">Explore Our Facilities</span>
  <div class="row horizontal-scroll">
    <div class="card" data-court-type="Basketball">
      <div class="card-image"><img src="backgrounds/basketball.jpg" alt="Basketball" class="img-card"></div>
      <div class="category"> Basketball </div>
      <div class="heading">
        Date : Loading...<br>Timeslot : Loading...<br>Total Courts : Loading...<br>Available : Loading...
        <div class="author"> Updated on <span class="name">Loading...</span></div>
      </div>
    </div>
    <div class="card" data-court-type="Badminton">
      <div class="card-image"><img src="backgrounds/badminton.jpg" alt="Badminton" class="img-card"></div>
      <div class="category"> Badminton </div>
      <div class="heading">
        Date : Loading...<br>Timeslot : Loading...<br>Total Courts : Loading...<br>Available : Loading...
        <div class="author"> Updated on <span class="name">Loading...</span></div>
      </div>
    </div>
    <div class="card" data-court-type="Volleyball">
      <div class="card-image"><img src="backgrounds/volleyball.jpg" alt="Volleyball" class="img-card"></div>
      <div class="category"> Volleyball </div>
      <div class="heading">
        Date : Loading...<br>Timeslot : Loading...<br>Total Courts : Loading...<br>Available : Loading...
        <div class="author"> Updated on <span class="name">Loading...</span></div>
      </div>
    </div>
    <div class="card" data-court-type="Tennis">
      <div class="card-image"><img src="backgrounds/tennis.jpg" alt="Tennis" class="img-card"></div>
      <div class="category"> Tennis </div>
      <div class="heading">
        Date : Loading...<br>Timeslot : Loading...<br>Total Courts : Loading...<br>Available : Loading...
        <div class="author"> Updated on <span class="name">Loading...</span></div>
      </div>
    </div>
    <div class="card" data-court-type="Futsal">
      <div class="card-image"><img src="backgrounds/futsal.jpg" alt="Futsal" class="img-card"></div>
      <div class="category"> Futsal </div>
      <div class="heading">
        Date : Loading...<br>Timeslot : Loading...<br>Total Courts : Loading...<br>Available : Loading...
        <div class="author"> Updated on <span class="name">Loading...</span></div>
      </div>
    </div>
    <div class="card" data-court-type="Bowling">
      <div class="card-image"><img src="backgrounds/bowling.jpg" alt="Bowling" class="img-card"></div>
      <div class="category"> Bowling </div>
      <div class="heading">
        Date : Loading...<br>Timeslot : Loading...<br>Total Courts : Loading...<br>Available : Loading...
        <div class="author"> Updated on <span class="name">Loading...</span></div>
      </div>
    </div>
    <div class="card" data-court-type="PSXbox">
      <div class="card-image"><img src="backgrounds/psxbox.jpg" alt="PSXbox" class="img-card"></div>
      <div class="category"> PS/Xbox </div>
      <div class="heading">
        Date : Loading...<br>Timeslot : Loading...<br>Total Courts : Loading...<br>Available : Loading...
        <div class="author"> Updated on <span class="name">Loading...</span></div>
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

  <!-- Login Modal -->
  <div id="loginModal" class="modal fade modal-fix" tabindex="-1" role="dialog" aria-labelledby="loginModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
      <div class="modal-content" id="loginModalContent">
        <div class="modal-body" id="loginModalBody">
          <form method="post" action="index.php" id="login-form" class="form">
            <p class="modal-title" id="loginModalLabel">Welcome,<span>sign in to continue</span></p>
                <button type="button" class="oauthButton google">
                  <svg class="icon" viewBox="0 0 24 24">
                    <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"></path>
                    <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"></path>
                    <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"></path>
                    <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"></path>
                    <path d="M1 1h22v22H1z" fill="none"></path>
                  </svg>
                  Continue with Google
                </button>
              <div class="separator">
                <div></div>
                <span>OR</span>
                <div></div>
              </div>
              <input type="text" placeholder="Username" id="cUser" name="cUser" required>
              <input type="password" placeholder="Password" id="cPass" name="cPass" required>
              <div class="forgotLink" >
                <a href="#" data-toggle="modal" data-target="#forgotModal" data-dismiss="modal"><span>Forgot Password?</span></a>
              </div>
              <button type="submit" name="submit" class="submitBtn oauthButton">
                Continue
                <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 17 5-5-5-5"></path><path d="m13 17 5-5-5-5"></path></svg>
              </button>
              <div class="signupLink">
                <p>Don't have an account? <a href="#" data-toggle="modal" data-target="#signupModal" data-dismiss="modal">Sign Up</a></p>
              </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Forgot Password Modal -->
  <div id="forgotModal" class="modal fade modal-fix" tabindex="-1" role="dialog" aria-labelledby="forgotModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
      <div class="modal-content" id="forgotModalContent">
        <div class="modal-body" id="forgotModalBody">
          <form method="post" action="index.php" id="reset-form" class="form" autocomplete="off">
            <p class="modal-title" id="forgotModalLabel">Hi,<span>just type your <i>registered</i> email and We send you the reset link</span></p>
            <input type="email" placeholder="Email" id="cEmail" name="cEmail" required>
              <button type="submit" name="btnForget" class="submitBtn oauthButton">
                Get reset link
                <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 17 5-5-5-5"></path><path d="m13 17 5-5-5-5"></path></svg>
              </button>
              <a href="" data-toggle="modal" data-target="#loginModal" data-dismiss="modal">Back to Login</a>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Signup Modal -->
  <div id="signupModal" class="modal fade modal-fix" tabindex="-1" role="dialog" aria-labelledby="signupModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content" id="signupModalContent">
        <div class="modal-body" id="signupModalBody">
          <form method="post" action="index.php" id="reg-form" class="reg_form" name="reg_form">
            <p class="modal-title" id="signupModalLabel">Join Us,<span>and be part of our PlayZone Family</span></p>
              <div class="authButton-group">
                <button type="button" class="authButton google">
                  <svg class="icon" viewBox="0 0 24 24">
                    <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"></path>
                    <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"></path>
                    <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"></path>
                    <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"></path>
                    <path d="M1 1h22v22H1z" fill="none"></path>
                  </svg>
                  Signup with Google
                </button>
              </div>
              <div class="separator">
                <div></div>
                <span>OR</span>
                <div></div>
              </div>
              <div class="signupInput-group">
              <input type="text" class="signupInput" placeholder="Username" id="cUser" name="cUser" required>
              <input type="email" class="signupInput" placeholder="Email" id="cEmail" name="cEmail" required>
              </div>
              <div class="signupInput-group">
              <input type="text" class="signupInput" placeholder="Full Name" id="cName" name="cName" required>
              <input type="text" class="signupInput" placeholder="Phone" id="cPhone" name="cPhone" required>
              </div>
              <div class="signupInput-group">
              <input type="password" class="signupInput" placeholder="Password" id="cPass" name="cPass" required>
              <input type="password" class="signupInput" placeholder="Confirm Password" id="ConPass" name="ConPass" required>
              </div>
              <button type="submit" name="submitBtn" class="submitBtn oauthButton">
                Register
                <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 17 5-5-5-5"></path><path d="m13 17 5-5-5-5"></path></svg>
              </button>
              <div class="loginLink">
                <p>Already have an account? <a href="#" data-toggle="modal" data-target="#loginModal" data-dismiss="modal">Login</a></p>
              </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Logout Confirmation Modal -->
  <div id="logoutModal" class="modal fade modal-fix" tabindex="-1" role="dialog" aria-labelledby="logoutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
      <div class="modal-content" id="logoutModalContent">
        <div class="modal-header" id="logoutModalHeader">
          <svg width="64px" height="64px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="" class="logout-svg">
            <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
            <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
            <g id="SVGRepo_iconCarrier"> <path d="M22 6.62219V17.245C22 18.3579 21.2857 19.4708 20.1633 19.8754L15.0612 21.7977C14.7551 21.8988 
            14.449 22 14.0408 22C13.5306 22 12.9184 21.7977 12.4082 21.4942C12.2041 21.2918 11.898 21.0895 11.7959 20.8871H7.91837C6.38776 20.8871 
            5.06122 19.6731 5.06122 18.0544V17.0427C5.06122 16.638 5.36735 16.2333 5.87755 16.2333C6.38776 16.2333 6.69388 16.5368 6.69388 
            17.0427V18.0544C6.69388 18.7626 7.30612 19.2684 7.91837 19.2684H11.2857V4.69997H7.91837C7.20408 4.69997 6.69388 5.20582 6.69388 
            5.91401V6.9257C6.69388 7.33038 6.38776 7.73506 5.87755 7.73506C5.36735 7.73506 5.06122 7.33038 5.06122 6.9257V5.91401C5.06122 4.39646 
            6.28572 3.08125 7.91837 3.08125H11.7959C12 2.87891 12.2041 2.67657 12.4082 2.47423C13.2245 1.96838 14.1429 1.86721 15.0612 2.17072L20.1633 
            4.09295C21.1837 4.39646 22 5.50933 22 6.62219Z" fill="#030D45"></path> 
            <path d="M4.85714 14.8169C4.65306 14.8169 4.44898 14.7158 4.34694 14.6146L2.30612 12.5912C2.20408 12.49 2.20408 12.3889 2.10204 
            12.3889C2.10204 12.2877 2 12.1865 2 12.0854C2 11.9842 2 11.883 2.10204 11.7819C2.10204 11.6807 2.20408 11.5795 2.30612 11.5795L4.34694 
            9.55612C4.65306 9.25261 5.16327 9.25261 5.46939 9.55612C5.77551 9.85963 5.77551 10.3655 5.46939 10.669L4.7551 11.3772H8.93878C9.34694 
            11.3772 9.7551 11.6807 9.7551 12.1865C9.7551 12.6924 9.34694 12.7936 8.93878 12.7936H4.65306L5.36735 13.5017C5.67347 13.8052 5.67347 
            14.3111 5.36735 14.6146C5.26531 14.7158 5.06122 14.8169 4.85714 14.8169Z" fill="#030D45"></path> </g></svg>
          <span class="modal-title" id="logoutModalLabel">Confirm Logout</span>
        </div>
        <div class="modal-body" id="logoutModalBody">
          Are you sure you want to log out?
        </div>
        <div class="modal-footer" id="logoutModalFooter">
          <button type="button" class="CBtn" data-dismiss="modal">Cancel</button>
          <button type="button" class="LBtn" onclick="location.href='?logout=true'">Logout</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal for $error_message -->
  <div class="modal fade modal-fix" id="errorModal" tabindex="-1" role="dialog" aria-labelledby="errorModalLabel">
    <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
      <div class="modal-content">
        <?php echo $error_message; ?>
      </div>
    </div>
  </div>
  <!-- Modal for $status -->
  <div class="modal fade modal-fix" id="successModal" tabindex="-1" role="dialog" aria-labelledby="successModalLabel">
    <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
      <div class="modal-content">
        <?php echo $status; ?>
      </div>
    </div>
  </div>
  <!-- Modal for $notify -->
  <div class="modal fade modal-fix" id="notifyModal" tabindex="-1" role="dialog" aria-labelledby="notifyModalLabel">
    <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
      <div class="modal-content">
        <?php echo $notify; ?>
      </div>
    </div>
  </div>

<script src="google-login.js" type="module"></script>
<script src="google-signup.js" type="module"></script>

  <script>
    document.addEventListener("DOMContentLoaded", function() {
        // Initial fetch of available slots
        fetchAvailableSlots();

        // Set an interval to refresh slots every 10 minute (600,000 ms)
        setInterval(fetchAvailableSlots, 600000);

        // Function to fetch the current timeslot and available courts for each card
        function fetchAvailableSlots() {
            const cards = document.querySelectorAll('.card[data-court-type]');
            console.log("Fetching available slots...");

            cards.forEach(function(card) {
                const courtType = card.getAttribute('data-court-type');
                const xhr = new XMLHttpRequest();
                xhr.open('POST', 'fetch_slots.php', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.onreadystatechange = function () {
                    if (xhr.readyState === 4 && xhr.status === 200) {
                        try {
                            const response = JSON.parse(xhr.responseText);
                            console.log("Response for " + courtType + ": ", response);

                            if (response.error) {
                                updateCardWithError(card, response.error, response.updated_time);
                            } else {
                                updateCard(card, response.date, response.day, response.timeslot, response.total_courts, response.available_courts, response.updated_time);
                            }
                        } catch (e) {
                            console.error("Error parsing JSON response: ", e);
                            console.log("Response text: ", xhr.responseText);
                        }
                    }
                };
                xhr.onerror = function () {
                    console.error("Request failed for court type: " + courtType);
                };
                xhr.send('courtType=' + encodeURIComponent(courtType));
            });
        }

        function updateCard(card, date, day, timeslot, total_courts, available_courts, updated_time) {
            const heading = card.querySelector('.heading');
            heading.innerHTML = `
                Date: ${date} (${day})<br>
                Timeslot: ${timeslot}<br>
                Total Courts: ${total_courts}<br>
                Available: ${available_courts} left
                <div class="author">Updated on <span class="name">${updated_time}</span></div>
            `;
        }

        function updateCardWithError(card, errorMessage, updated_time) {
            const heading = card.querySelector('.heading');
            heading.innerHTML = `
                ${errorMessage}
                <div class="author">Updated on <span class="name">${updated_time}</span></div>
            `;
        }
    });
</script>

  <script>
    document.getElementById("login-form").addEventListener("keydown", function(event) {
      if (event.key === "Enter") {
        event.preventDefault();
        document.querySelector('button[type="submit"]').click();
      }
    });

    $(document).on('hidden.bs.modal', function () {
      if ($('.modal.show').length) {
          $('body').addClass('modal-open');
      } else {
          $('body').css('padding-right', '0');
      }
    });

    $('#forgotModal').on('show.bs.modal', function () {
        $('body').css('padding-right', '0');
    });

    $(document).ready(function() {
      <?php if (!empty($error_message)) { ?>
        $('#errorModal').modal('show');
      <?php } ?>
    });

    $(document).ready(function() {
      <?php if (!empty($status)) { ?>
        $('#successModal').modal('show');
      <?php } ?>
    });

    $(document).ready(function() {
      <?php if (!empty($notify)) { ?>
        $('#notifyModal').modal('show');
      <?php } ?>
    });
  </script>
  
  <script>
  const scrollContainers = document.querySelectorAll('.horizontal-scroll');

  scrollContainers.forEach((scrollContainer) => {
    let isDown = false;
    let startX;
    let scrollLeft;

    scrollContainer.addEventListener('mousedown', (e) => {
      isDown = true;
      scrollContainer.classList.add('active'); // Optional: Add a class to indicate dragging
      startX = e.pageX - scrollContainer.offsetLeft;
      scrollLeft = scrollContainer.scrollLeft;
    });

    scrollContainer.addEventListener('mouseleave', () => {
      isDown = false;
      scrollContainer.classList.remove('active'); // Optional: Remove the class
    });

    scrollContainer.addEventListener('mouseup', () => {
      isDown = false;
      scrollContainer.classList.remove('active'); // Optional: Remove the class
    });

    scrollContainer.addEventListener('mousemove', (e) => {
      if (!isDown) return; // Stop the function from running if not clicking
      e.preventDefault();
      const x = e.pageX - scrollContainer.offsetLeft;
      const walk = (x - startX) * 3; // Scroll speed
      scrollContainer.scrollLeft = scrollLeft - walk;
    });
  });
</script>

  <script src="scripts.js"></script>
</body>
</html>
