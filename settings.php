<?php
  session_start();
  require_once 'database.php';

  if (!$conn) {
      die("Connection failed: " . mysqli_connect_error());
  }

 // Check for PHP login or Google session
  $isGoogleLoggedIn = false;
  $googleUserData = null;

  if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
      if (isset($_SESSION['google_user'])) {
          $isGoogleLoggedIn = true;
          $googleUserData = $_SESSION['google_user'];
          $cUser = $googleUserData['email']; // Use Google email as identifier
      } elseif (isset($_SESSION['cUser'])) {
          $cUser = $_SESSION['cUser'];
      } else {
          header("location: index.php");
          exit;
      }
  } else {
      header("location: index.php");
      exit;
  }

  // Fetch user data from the database
  $stmt = $conn->prepare("SELECT * FROM customer WHERE cUser = ? OR cEmail = ?");
  $stmt->bind_param("ss", $cUser, $cUser);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result) {
      if ($result->num_rows > 0) {
          $rows = $result->fetch_assoc();
          $_SESSION['cAvatar'] = $isGoogleLoggedIn && isset($googleUserData['photoURL'])
          ? $googleUserData['photoURL']
          : $rows['cAvatar'];
          $_SESSION['cName'] = $rows['cName'];
          $_SESSION['cUser'] = $rows['cUser'];
          $_SESSION['cEmail'] = $rows['cEmail'];

          if (isset($_GET["logout"])) {
              unset($_SESSION['logged_in']);
              unset($_SESSION['cName']);
              unset($_SESSION['cUser']);
              unset($_SESSION['cAvatar']);
              unset($_SESSION['cEmail']);
              unset($_SESSION['google_user']); // Clear Google session
              session_destroy();
              header("Location: index.php");
              exit();
          }
      } else {
          die("No user found for this username or email: $cUser. Please make sure you are registered.");
      }
  } else {
      die("Error in SQL query: " . $conn->error);
  }

  $isGoogleUser = isset($_SESSION['google_user']);
  $emailVerified = isset($_SESSION['email_verified']) ? $_SESSION['email_verified'] : false;
  $phoneVerified = isset($_SESSION['phone_verified']) ? $_SESSION['phone_verified'] : false;

  mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <title>Settings</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <link rel="icon" href="PZ_icon-32x32.png" type="image/png">
    <script src="https://kit.fontawesome.com/cbf02b9426.js" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="stylee.css">
    <link rel="stylesheet" href="form.css">
    <link rel="stylesheet" href="button.css">
    <link rel="stylesheet" href="settings.css">
</head>
<body>
<header>
    <nav class="navbar navbar-inverse">
        <div class="container-fluid">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#myNavbar">
                    <span class="icon-bar"></span>
                </button>
            </div>
            <div class="collapse navbar-collapse" id="myNavbar">
                <ul class="nav navbar-nav">
                    <li><img src="PZ_tp.svg" width="40" height="40" alt="Logo"></li>
                    <li><a href="javascript:void(0);" onclick="goBack(event);" class="nav-btn">Back</a></li>
                </ul>
            </div>
        </div>
    </nav>
</header>

<div class="container" id="mainContainer">
  <!-- Settings content -->
  <div id="settingsContent">
    <span class="p4">Settings</span><br><br>

    <div class="flex-container">
      <!-- Account Information Section -->
      <section id="account-information" class="section box">
          <span class="category">Account Information</span><br>
          <table>
            <tbody>
              <tr>
                <td class="label">Full Name:</td>
                <?php
                if ($rows["cName"] == "") {
                ?>
                  <td class="value">Not set</td>
                <?php
                } else {
                ?>
                <td class="value"><?php echo $rows["cName"] ?></td>
                <?php
                }
                ?>
                <td></td>
              </tr>
              <tr>
                <td class="label">Username:</td>
                <?php
                if ($rows["cUser"] == "") {
                ?>
                  <td class="value">Not set</td>
                <?php
                } else {
                ?>
                <td class="value"><?php echo $rows["cUser"] ?></td>
                <?php
                }
                ?>
                <td></td>
              </tr>
              <tr>
                <td class="label">Email:</td>
                <td class="value">
                    <?php if ($rows["cEmail"] == ""): ?>
                        Not set
                    <?php else: ?>
                        <?php echo htmlspecialchars($rows["cEmail"]); ?>
                        <?php if (!$isGoogleUser && !$emailVerified): ?>
                        <span class="unverified">Not verified</span>
                        <img src="Icons/times-circle.svg" alt="Unverified" width="20" height="20">
                        <?php else: ?>
                            <span class="verified">Verified</span>
                            <img src="Icons/checkmark-circle.svg" alt="Verified" width="18" height="18">
                        <?php endif; ?>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($rows["cEmail"] == ""): ?>
                      <td></td>
                    <?php else: ?>
                      <?php if (!$isGoogleUser && !$emailVerified): ?>
                          <button onclick="verifyEmail()">
                              <div class="svg-wrapper-1">
                                  <div class="svg-wrapper">
                                      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="12" height="12">
                                          <path fill="none" d="M0 0h24v24H0z"></path>
                                          <path fill="currentColor" d="M1.946 9.315c-.522-.174-.527-.455.01-.634l19.087-6.362c.529-.176.832.12.684.638l-5.454 19.086c-.15.529-.455.547-.679.045L12 14l6-8-8 6-8.054-2.685z"></path>
                                      </svg>
                                  </div>
                              </div>
                              <span>Send</span>
                          </button>
                      <?php endif; ?>
                    <?php endif; ?>
                </td>
              </tr>
              <tr>
                <td class="label">Phone Number:</td>
                <td class="value">
                    <?php if ($rows["cPhone"] == ""): ?>
                        Not set
                    <?php else: ?>
                        <?php echo htmlspecialchars($rows["cPhone"]); ?>
                        <?php if (!$phoneVerified): ?>
                        <span class="unverified">Not verified</span>
                        <img src="Icons/times-circle.svg" alt="Unverified" width="20" height="20">
                        <?php else: ?>
                            <span class="verified">Verified</span>
                            <img src="Icons/checkmark-circle.svg" alt="Verified" width="18" height="18">
                        <?php endif; ?>
                    <?php endif; ?>
                </td>
                <td>
                  <?php if ($rows["cPhone"] == ""): ?>
                    <td></td>
                  <?php else: ?>
                    <?php if (!$phoneVerified): ?>
                        <button onclick="verifyPhone()">
                            <div class="svg-wrapper-1">
                                <div class="svg-wrapper">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="12" height="12">
                                        <path fill="none" d="M0 0h24v24H0z"></path>
                                        <path fill="currentColor" d="M1.946 9.315c-.522-.174-.527-.455.01-.634l19.087-6.362c.529-.176.832.12.684.638l-5.454 19.086c-.15.529-.455.547-.679.045L12 14l6-8-8 6-8.054-2.685z"></path>
                                    </svg>
                                </div>
                            </div>
                            <span>Send</span>
                        </button>
                    <?php endif; ?>
                  <?php endif; ?>
                </td>
              </tr>
              <tr>
                <?php if ($isGoogleUser): ?>
                    <td class="label">Add Password:</td>
                    <td class="value">
                      <!-- Input field visible when user click Add button(addPassword()) -->
                    </td>
                    <td>
                      <button onclick="addPassword()">
                        <div class="svg-wrapper-1">
                          <div class="svg-wrapper">
                            <svg version="1.1" id="Uploaded_to_svgrepo.com" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="12" height="12" viewBox="0 0 32 32" xml:space="preserve" fill="#ffffff">
                              <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                              <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                              <g id="SVGRepo_iconCarrier">
                                <style type="text/css"> .puchipuchi_een { fill: #ffffff; } </style>
                                <path class="puchipuchi_een" d="M29.586,9.414L26,13l-7-7l3.586-3.586c0.778-0.778,2.051-0.778,2.828,0l4.172,4.172 C30.364,7.364,30.364,8.636,29.586,9.414z M18,7l7,7L10.707,28.293C10.318,28.682,9.55,29,9,29H4c-0.55,0-1-0.45-1-1v-5 c0-0.55,0.318-1.318,0.707-1.707L18,7z M8.464,26.293l-2.757-2.757C5.318,23.147,5,23.278,5,23.828V26c0,0.55,0.45,1,1,1h2.172 C8.722,27,8.853,26.682,8.464,26.293z"></path>
                              </g>
                            </svg>
                          </div>
                        </div>
                        <span>Add</span>
                      </button>
                    </td>
                <?php else: ?>
                    <td class="label">New Password:</td>
                    <td class="value">
                      <!-- Input field visible when user click Change button(changePassword()) -->
                    </td>
                    <td>
                        <button onclick="changePassword()">
                            <div class="svg-wrapper-1">
                                <div class="svg-wrapper">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="12" height="12">
                                        <path fill="none" d="M0 0h24v24H0z"></path>
                                        <path fill="currentColor" d="M17 7h5v2h-5v13l-6-3l-6 3V7z"></path>
                                    </svg>
                                </div>
                            </div>
                            <span>New</span>
                        </button>
                    </td>
                <?php endif; ?>
              </tr>
            </tbody>
          </table>
      </section>

      <!-- Linked Account Section -->
      <section id="linked-account" class="section box">
          <span class="category">Linked Account</span>
          <ul>
              <?php if (!$isGoogleUser): ?>
                  <li class="heading">
                      <label>Connect Google Account:</label>
                      <button onclick="connectGoogle()">Connect Google</button>
                      <span class="notice">Link a Google account for easier login and as a recovery email.</span>
                  </li>
              <?php endif; ?>
              <li class="heading">
                  <label>Connect WhatsApp:</label>
                  <button onclick="connectWhatsApp()">Connect WhatsApp</button>
              </li>
              <li class="heading">
                  <label>Connect Telegram:</label>
                  <button onclick="connectTelegram()">Connect Telegram</button>
              </li>
          </ul>
      </section>
    </div>

    <div class="flex-container">
      <!-- Notifications Section -->
      <section id="notifications" class="section box">
          <span class="category">Notifications</span>
          <form id="notification-settings">
              <div>
                  <label><input type="checkbox" <?php if ($phoneVerified) echo 'checked'; ?>> Enable SMS Notifications</label>
              </div>
              <div>
                  <label><input type="checkbox" <?php if ($emailVerified) echo 'checked'; ?>> Enable Email Notifications</label>
              </div>
              <div>
                  <label><input type="checkbox"> Enable WhatsApp Notifications (Requires WhatsApp linked)</label>
              </div>
              <div>
                  <label><input type="checkbox"> Enable Telegram Notifications (Requires Telegram linked)</label>
              </div>
          </form>
      </section>

      <!-- Booking History Section -->
      <section id="booking-history" class="section box">
          <span class="category">Booking History</span>
          <button onclick="showBookingHistory()">View Booking History</button>
      </section>
    </div>

    <div class="flex-container">
      <!-- Support & About Section -->
      <section id="support-about" class="section box">
          <span class="category">Support & About</span>
          <ul>
              <li><a href="report_problem.php">Report a Problem</a></li>
              <li><a href="terms_policies.php">Terms & Policies</a></li>
          </ul>
      </section>

      <!-- Account Deactivation Section -->
      <section id="account-deactivation" class="section box">
          <span class="category">Account Deactivation</span>
          <button onclick="deactivateAccount()">Delete Account</button>
          <span class="notice">To delete your account, you must enter your email and password.</span>
      </section>
    </div>
  </div>
  
  <div id="bookingTable" style="display: none;">
    <div class="container">
      <span class="p4">Booking History</span><br><br>
      
      <section id="bookingHistory" class="section table-box">
        <button onclick="hideBookingHistory()" class="back-button">Back to Settings</button>
        <table class="table">
            <thead>
                <tr>
                    <th>Booking ID</th>
                    <th>Category</th>
                    <th>Court Type</th>
                    <th>Timeslot</th>
                    <th>Participant</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody id="bookingList">
                <!-- Booking history will be dynamically loaded here -->
            </tbody>
        </table>
      </section>
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
    function verifyEmail() {
        alert("Email verification link sent. Please check your inbox.");
    }

    function verifyPhone() {
        alert("Phone verification process started.");
    }

    function addPassword() {
        alert("You can add a new password in the profile settings.");
    }

    function connectGoogle() {
        alert("Connecting to Google...");
    }

    function connectWhatsApp() {
        alert("WhatsApp connection initiated.");
    }

    function connectTelegram() {
        alert("Telegram connection initiated.");
    }

    function showBookingHistory() {
      document.getElementById("settingsContent").style.display = "none";
      document.getElementById("bookingTable").style.display = "block";
    }

    function hideBookingHistory() {
      document.getElementById("bookingTable").style.display = "none";
      document.getElementById("settingsContent").style.display = "block";
    }

    function deactivateAccount() {
        if (confirm("Please enter your email and password to proceed with account deletion.")) {
            alert("Account deletion process initiated.");
        }
    }

    function goBack(event) {
        event.preventDefault();
        history.back();
    }
</script>
</body>
</html>
