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
                      echo '<li><a href="index.php" class="nav-btn">PlayZone</a></li>
                      <li><a href="contact.php" class="nav-btn">Contact</a></li>';
                    }
                  ?>
                </ul>
                <ul class="nav navbar-nav navbar-right">
                    <ul class="nav navbar-nav navbar-right">
                        <?php
                            if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']) {
                                $avatar = !empty($_SESSION['cAvatar']) ? $_SESSION['cAvatar'] : 'default_avatar.png';

                                // If user is logged in, show username with a dropdown menu
                                echo '
                                <li class="dropdown">
                                    <a href="profile.php" class="dropdown-toggle">
                                        <span class="glyphicon glyphicon-user"></span>&nbsp; ' . $_SESSION['cUser'] . ' <span class="caret"></span>
                                    </a>
                                    <ul class="dropdown-menu">
                                        <li class="d-m" style="display: flex; align-items: center;">
                                            <img src="' . $avatar . '" alt="user" class="drop-circle" width="60" height="60">
                                            <div class="details">
                                                <span class="username" style="font-size: 18px; font-weight: bold; display: block;">' . $_SESSION['cUser'] . '</span>
                                                <span class="email" style="font-size: 12px; display: block;">' . $_SESSION['cEmail'] . '</span>
                                            </div>
                                        </li>
                                        <li class="dropdown-item"><a href="profile.php">Profile</a></li>
                                        <li class="dropdown-item"><a href="settings.php">Settings</a></li>
                                        <li class="dropdown-item"><a href="contact.php">Contact</a></li>
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
                  <button class="redirect-button" onclick="window.open('https://github.com/MouriCok/playzone.git', '_blank')">
                    <svg width="64px" height="64px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="" class="icon">
                      <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                      <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                      <g id="SVGRepo_iconCarrier"> 
                        <path d="M18.6713 2.62664C18.5628 2.36483 18.3534 2.16452 18.0959 2.07627L18.094 2.07564L18.0922 2.07501L18.0884 
                        2.07374L18.0805 2.07114L18.0636 2.06583C18.0518 2.06223 18.039 2.05856 18.0252 2.05487C17.9976 2.04749 17.966 2.04007 
                        17.9305 2.03319C17.8593 2.01941 17.7728 2.00787 17.6708 2.00279C17.466 1.99259 17.2037 2.00858 16.8817 2.08054C16.3447 
                        2.20053 15.6476 2.47464 14.7724 3.03631C14.7152 3.07302 14.6572 3.11096 14.5985 3.15016C14.5397 3.13561 14.4809 3.12155 
                        14.422 3.108C12.8261 2.74083 11.1742 2.74083 9.57825 3.108C9.51933 3.12156 9.46049 3.13561 9.40173 3.15017C9.34298 3.11096 
                        9.28499 3.07302 9.22775 3.03631C8.35163 2.47435 7.65291 2.20029 7.11455 2.08039C6.79179 2.00852 6.52891 1.99262 6.324 
                        2.00278C6.22186 2.00784 6.13536 2.01931 6.06428 2.03299C6.0288 2.03982 5.99732 2.04717 5.96983 2.05447C5.95609 2.05812 
                        5.94336 2.06176 5.93163 2.06531L5.91481 2.07056L5.90698 2.07311L5.9032 2.07437L5.90135 2.07499L5.89952 2.07561C5.63979 
                        2.16397 5.42877 2.36623 5.32049 2.63061C4.91716 3.6154 4.8101 4.70134 5.00435 5.74306C5.01379 5.79367 5.02394 5.84418 
                        5.0348 5.89458C4.99316 5.95373 4.9527 6.01368 4.91343 6.07439C4.30771 7.01089 3.98553 8.12791 4.00063 9.27493C4.00208 
                        11.7315 4.71965 13.4139 5.9332 14.4965C6.62014 15.1093 7.41743 15.4844 8.21873 15.7208C8.31042 15.7479 8.40217 15.7731 
                        8.49381 15.7967C8.48043 15.8432 8.46796 15.8901 8.45641 15.9373C8.40789 16.1357 8.37572 16.3394 8.36083 16.5461C8.35948 
                        16.5648 8.35863 16.5835 8.35829 16.6022L8.32436 18.421L8.32417 18.4407C8.32417 18.4464 8.32417 18.4521 8.32417 18.4577C8.26262 
                        18.473 8.20005 18.4843 8.13682 18.4916C7.942 18.5141 7.74467 18.4977 7.5561 18.4434C7.36752 18.3891 7.19127 18.2979 7.03752 
                        18.1749C6.88377 18.0519 6.75553 17.8994 6.66031 17.7261L6.6505 17.7087C6.38836 17.2535 6.02627 16.8639 5.59142 16.5695C5.15656 
                        16.275 4.6604 16.0836 4.14047 16.0099C3.59365 15.9324 3.08753 16.3128 3.01002 16.8597C2.93251 17.4065 3.31296 17.9126 3.85978 
                        17.9901C4.07816 18.0211 4.28688 18.1015 4.47012 18.2256C4.65121 18.3482 4.80277 18.5103 4.9134 18.7C5.1346 19.0992 5.43165 
                        19.4514 5.78801 19.7365C6.14753 20.0242 6.56032 20.2379 7.00272 20.3653C7.43348 20.4893 7.88392 20.5291 8.32949 20.4825C8.33039 
                        20.7224 8.33103 20.9065 8.33103 21C8.33103 21.5523 8.75521 22 9.27847 22H14.7558C15.279 22 15.7032 21.5523 15.7032 
                        21V17.2095C15.729 16.7802 15.685 16.3499 15.5738 15.9373C15.5585 15.8805 15.5419 15.824 15.5241 15.7679C15.5838 15.753 
                        15.6435 15.7373 15.7032 15.7208C16.5277 15.4937 17.3513 15.1224 18.0588 14.4983C19.2791 13.4217 19.9982 11.7379 19.9996 
                        9.27493C20.0147 8.12791 19.6925 7.01089 19.0868 6.07439C19.0475 6.01358 19.007 5.95354 18.9652 5.89429C18.976 5.84399 18.9861 
                        5.79358 18.9955 5.74306C19.1893 4.69934 19.0795 3.61142 18.6713 2.62664Z" fill="#ffffff"></path> 
                      </g>
                    </svg>
                    <span class="text-1">GitHub</span>
                  </button>&nbsp;&nbsp;&nbsp;
                  <button class="download-button" onclick="downloadFile()">
                    <svg width="64px" height="64px" viewBox="0 0 24 24" version="1.1" xmlns="http://www.w3.org/2000/svg" 
                      xmlns:xlink="http://www.w3.org/1999/xlink" fill="#ffffff" class="icon">
                      <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                      <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                      <g id="SVGRepo_iconCarrier"> <title>Android_2_fill</title> 
                      <g id="页面-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"> 
                        <g id="Logo" transform="translate(0.000000, -144.000000)"> 
                          <g id="Android_2_fill" transform="translate(0.000000, 144.000000)"> 
                            <path d="M24,0 L24,24 L0,24 L0,0 L24,0 Z M12.5934901,23.257841 L12.5819402,23.2595131 L12.5108777,23.2950439 L12.4918791,
                            23.2987469 L12.4918791,23.2987469 L12.4767152,23.2950439 L12.4056548,23.2595131 C12.3958229,23.2563662 12.3870493,
                            23.2590235 12.3821421,23.2649074 L12.3780323,23.275831 L12.360941,23.7031097 L12.3658947,23.7234994 L12.3769048,
                            23.7357139 L12.4804777,23.8096931 L12.4953491,23.8136134 L12.4953491,23.8136134 L12.5071152,23.8096931 L12.6106902,
                            23.7357139 L12.6232938,23.7196733 L12.6232938,23.7196733 L12.6266527,23.7031097 L12.609561,23.275831 C12.6075724,
                            23.2657013 12.6010112,23.2592993 12.5934901,23.257841 L12.5934901,23.257841 Z M12.8583906,23.1452862 L12.8445485,
                            23.1473072 L12.6598443,23.2396597 L12.6498822,23.2499052 L12.6498822,23.2499052 L12.6471943,23.2611114 L12.6650943,
                            23.6906389 L12.6699349,23.7034178 L12.6699349,23.7034178 L12.678386,23.7104931 L12.8793402,23.8032389 C12.8914285,
                            23.8068999 12.9022333,23.8029875 12.9078286,23.7952264 L12.9118235,23.7811639 L12.8776777,23.1665331 C12.8752882,
                            23.1545897 12.8674102,23.1470016 12.8583906,23.1452862 L12.8583906,23.1452862 Z M12.1430473,23.1473072 C12.1332178,
                            23.1423925 12.1221763,23.1452606 12.1156365,23.1525954 L12.1099173,23.1665331 L12.0757714,23.7811639 C12.0751323,
                            23.7926639 12.0828099,23.8018602 12.0926481,23.8045676 L12.108256,23.8032389 L12.3092106,23.7104931 L12.3186497,
                            23.7024347 L12.3186497,23.7024347 L12.3225043,23.6906389 L12.340401,23.2611114 L12.337245,23.2485176 L12.337245,
                            23.2485176 L12.3277531,23.2396597 L12.1430473,23.1473072 Z" id="MingCute" fill-rule="nonzero"> </path> 
                            <path d="M18.4472,4.10555 C18.9412,4.35254 19.1414,4.95321 18.8944,5.44719 L17.7199,7.79631 C20.3074,9.6038 22,
                            12.6042 22,16 L22,17 C22,18.1046 21.1046,19 20,19 L4,19 C2.89543,19 2,18.1046 2,17 L2,16 C2,12.6042 3.69259,
                            9.60379 6.28014,7.79631 L5.10558,5.44719 C4.85859,4.95321 5.05881,4.35254 5.55279,4.10555 C6.04677,3.85856 6.64744,
                            4.05878 6.89443,4.55276 L8.028,6.8199 C9.24553,6.29239 10.5886,6 12,6 C13.4114,6 14.7545,6.29239 15.972,6.81991 L17.1056,
                            4.55276 C17.3526,4.05878 17.9532,3.85856 18.4472,4.10555 Z M7.5,12 C6.67157,12 6,12.6716 6,13.5 C6,14.3284 6.67157,15 7.5,
                            15 C8.32843,15 9,14.3284 9,13.5 C9,12.6716 8.32843,12 7.5,12 Z M16.5,12 C15.6716,12 15,12.6716 15,13.5 C15,14.3284 15.6716,
                            15 16.5,15 C17.3284,15 18,14.3284 18,13.5 C18,12.6716 17.3284,12 16.5,12 Z" id="形状结合" fill="#ffffff"> </path> 
                          </g> 
                        </g> 
                      </g> 
                      </g>
                    </svg>
                    <span class="text-1">Download</span>
                  </button>
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
              <button class="oauthButton">
                <svg class="icon" viewBox="0 0 24 24">
                  <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"></path>
                  <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"></path>
                  <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"></path>
                  <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"></path>
                  <path d="M1 1h22v22H1z" fill="none"></path>
                </svg>
                Continue with Google
              </button>
              <button class="oauthButton">
                <svg class="icon" viewBox="0 0 24 24">
                  <path d="M12 .297c-6.63 0-12 5.373-12 12 0 5.303 3.438 9.8 8.205 11.385.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61C4.422 18.07 3.633 17.7 3.633 17.7c-1.087-.744.084-.729.084-.729 1.205.084 1.838 1.236 1.838 1.236 1.07 1.835 2.809 1.305 3.495.998.108-.776.417-1.305.76-1.605-2.665-.3-5.466-1.332-5.466-5.93 0-1.31.465-2.38 1.235-3.22-.135-.303-.54-1.523.105-3.176 0 0 1.005-.322 3.3 1.23.96-.267 1.98-.399 3-.405 1.02.006 2.04.138 3 .405 2.28-1.552 3.285-1.23 3.285-1.23.645 1.653.24 2.873.12 3.176.765.84 1.23 1.91 1.23 3.22 0 4.61-2.805 5.625-5.475 5.92.42.36.81 1.096.81 2.22 0 1.606-.015 2.896-.015 3.286 0 .315.21.69.825.57C20.565 22.092 24 17.592 24 12.297c0-6.627-5.373-12-12-12"></path>
                </svg>
                Continue with Github
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
                <button class="authButton google">
                  <svg class="icon" viewBox="0 0 24 24">
                    <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"></path>
                    <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"></path>
                    <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"></path>
                    <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"></path>
                    <path d="M1 1h22v22H1z" fill="none"></path>
                  </svg>
                  Signup with Google
                </button>
                <button class="authButton github">
                  <svg class="icon" viewBox="0 0 24 24">
                    <path d="M12 .297c-6.63 0-12 5.373-12 12 0 5.303 3.438 9.8 8.205 11.385.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61C4.422 18.07 3.633 17.7 3.633 17.7c-1.087-.744.084-.729.084-.729 1.205.084 1.838 1.236 1.838 1.236 1.07 1.835 2.809 1.305 3.495.998.108-.776.417-1.305.76-1.605-2.665-.3-5.466-1.332-5.466-5.93 0-1.31.465-2.38 1.235-3.22-.135-.303-.54-1.523.105-3.176 0 0 1.005-.322 3.3 1.23.96-.267 1.98-.399 3-.405 1.02.006 2.04.138 3 .405 2.28-1.552 3.285-1.23 3.285-1.23.645 1.653.24 2.873.12 3.176.765.84 1.23 1.91 1.23 3.22 0 4.61-2.805 5.625-5.475 5.92.42.36.81 1.096.81 2.22 0 1.606-.015 2.896-.015 3.286 0 .315.21.69.825.57C20.565 22.092 24 17.592 24 12.297c0-6.627-5.373-12-12-12"></path>
                  </svg>
                  Signup with Github
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
