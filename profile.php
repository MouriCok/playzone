<?php
session_start();
require_once 'database.php';
require_once 'move_old_bookings.php';

// Check if the user is logged in, if not then redirect to home page
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("location: index.php");
    exit;
}

// Check if cUser session variable is set
if (!isset($_SESSION['cUser'])) {
    die("No user session found. Please try again.");
}

$cUser = $_SESSION['cUser'];

// Use prepared statements to avoid SQL injection
$stmt = $conn->prepare("SELECT * FROM customer WHERE cUser = ?");
$stmt->bind_param("s", $cUser);
$stmt->execute();
$result = $stmt->get_result();

if ($result) {
    if ($result->num_rows > 0) {
        // Fetch the user data
        $rows = $result->fetch_assoc();

        // Set avatar session variable
        $_SESSION['cAvatar'] = $rows['cAvatar'];
        $_SESSION['cEmail'] = $rows['cEmail'];

        // Handle logout
        if (isset($_GET["logout"])) {
            unset($_SESSION['logged_in']);
            unset($_SESSION['cUser']);
            unset($_SESSION['cAvatar']);
            unset($_SESSION['cEmail']);
            session_destroy();
            header("Location: index.php");
            exit();
        }
    } else {
        die("No User found for this Username: $cUser,\nPlease make sure that You have registered an account.");
    }
} else {
    die("Error in SQL query: " . $conn->error);
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Settings</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" href="PZ_icon-32x32.png" type="image/png">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
  <link rel="stylesheet" href="stylee.css">
  <link rel="stylesheet" href="form.css">
  <link rel="stylesheet" href="modal.css">
  <link rel="stylesheet" href="button.css">
  <style>
    html, body {
      height: 100%;
      margin: 0;
      display: flex;
      flex-direction: column;
    }
    .container {
      flex: 1;
      margin-top: 34px;
      margin-bottom: 34px;
    }
    .main-body {
        padding: 15px;
    }
    h6 {
      font-weight: bold;
    }
    .profile-card {
        box-shadow: 0 1px 3px 0 rgba(0,0,0,.1), 0 1px 2px 0 rgba(0,0,0,.06);
        background-color: #fff;
        border: 0 solid rgba(0,0,0,.125);
        border-radius: .45rem;
        height: max-content;
    }
    .card-body {
        height: 100%;
    }
    .d-flex {
        margin: 0 auto;
        padding: 30px;
        display: flex;
        flex-direction: column;
        width: 100%;
        height: 100%;
        text-align: center;
        align-items: center;
    }
    .list-group {
      margin-top: 15px;
    }

    .gutters-sm {
        margin-right: -8px;
        margin-left: -8px;
    }

    .gutters-sm>.col, .gutters-sm>[class*=col-] {
        padding-right: 8px;
        padding-left: 8px;
    }
    .mb-0 {
      margin-top: 0px !important;
    }

    .bg-gray-300 {
        background-color: #e2e8f0;
    }
    .h-100 {
        height: 100%!important;
    }
    .shadow-none {
        box-shadow: none!important;
    }
    .active {
      color: darkblue !important;
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
                <span class="icon-bar"></span>
              </button>
            </div>
            <div class="collapse navbar-collapse" id="myNavbar">
              <ul class="nav navbar-nav">
                <!-- <li><img src="PZ_tp.svg" width="40" height="40" alt="Logo"></li> -->
                <li><a href="index.php" class="nav-btn">Home</a></li>
                <li><a href="bookings.php" class="nav-btn">Booking</a></li>
              </ul>
              <ul class="nav navbar-nav navbar-right">
                    <?php
                        // Logout logic
                        if (isset($_GET["logout"])) {
                            unset($_SESSION['logged_in']);
                            unset($_SESSION['cUser']);
                            unset($_SESSION['cAvatar']);
                            session_destroy();
                            header("Location: index.php");
                            exit();
                        }
                    ?>
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
                                echo '<li class="login"><a href="login.php"><span class="glyphicon glyphicon-log-in"></span>&nbsp;&nbsp;Login</a></li>';
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

<div class="container">
  <div class="main-body">
    <div class="row gutters-sm">
      <!-- Profile Picture section -->
      <div class="col-md-4">
        <div class="profile-card">
          <div class="card-body">
            <div class="d-flex flex-column">
              <div class="profile-pic-container">
              <?php
                require_once 'database.php';

                $cUser = $rows['cUser'];
                $custQuery = "SELECT cId, cAvatar FROM customer WHERE cUser = '$cUser'";
                $custResult = $conn->query($custQuery);

                if ($custResult) {
                  if ($custResult->num_rows > 0) {
                    while ($custRow = $custResult->fetch_assoc()) {
                      if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']) {
                        // If user is logged in, check if they have a custom avatar
                        if ($custRow['cAvatar'] !== null) {
                          // If user has a custom avatar, display it
                          echo "<img src='" . $custRow['cAvatar'] . "' alt='user' class='rounded-circle profile-pic'>";
                        } else {
                          // If user has no custom avatar, display the default avatar
                          echo "<img src='default_avatar.png' alt='default_avatar' class='rounded-circle profile-pic'>";
                        }
                      } else {
                        // If user is not logged in, display the default avatar
                        echo "<img src='default_avatar.png' alt='default_avatar' class='rounded-circle profile-pic'>";
                      }
                      echo '<div class="change-btn-overlay">
                              <button type="button" class="change-btn" onclick="changeProfile(' . $custRow["cId"] . ')">
                              <div class="change_icon">
                                  <svg width="256px" height="256px" viewBox="0 0 512.00 512.00" version="1.1" xml:space="preserve" xmlns="http://www.w3.org/2000/svg" 
                                  xmlns:xlink="http://www.w3.org/1999/xlink" fill="#000000" transform="rotate(0)matrix(1, 0, 0, 1, 0, 0)" stroke="#000000"><g id="SVGRepo_bgCarrier" 
                                  stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> 
                                  <style type="text/css"> .st0{fill:#F8F9FA;} .st1{fill:none;stroke:#F8F9FA;stroke-width:0.00512;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10;} 
                                  </style> <g id="Layer_1"></g> <g id="Layer_2"> <g> <g> 
                                  <path class="st0" d="M307.81,212.18c-3.24,0-6.07-2.17-6.91-5.3l-4.82-17.88c-0.84-3.12-3.68-5.3-6.91-5.3h-21.46h-25.44H220.8 
                                  c-3.24,0-6.07,2.17-6.91,5.3l-4.82,17.88c-0.84,3.12-3.68,5.3-6.91,5.3H169.5c-3.96,0-7.16,3.21-7.16,7.16v101.78 c0,3.96,3.21,7.16,7.16,
                                  7.16h170.95c3.96,0,7.16-3.21,7.16-7.16V219.35c0-3.96-3.21-7.16-7.16-7.16H307.81z M282.33,264.94 c-0.86,13.64-11.93,24.71-25.58,25.58c-16.54,
                                  1.05-30.18-12.59-29.14-29.14c0.86-13.64,11.93-24.71,25.58-25.58 C269.74,234.76,283.38,248.4,282.33,264.94z"></path> </g> <g> <path class="st0" 
                                  d="M82.95,272.41c3.82,0,7.53-1.53,10.23-4.23l21.23-21.23c4.74-4.74,6.4-11.92,3.73-18.06 c-2.73-6.29-8.88-8.95-18.84-7.57l-0.27,0.27c15.78-71.56,
                                  79.7-125.27,155.94-125.27c60.72,0,115.41,33.72,142.73,87.99 c3.58,7.11,12.24,9.97,19.34,6.39c7.11-3.58,9.97-12.24,6.39-19.34c-15.47-30.73-39.05-56.66-68.22-75.01 
                                  C325.23,77.47,290.57,67.5,254.98,67.5c-93,0-170.48,67.71-185.75,156.41c-5.38-4.77-13.59-5.18-19.13-0.44 c-6.3,5.39-6.75,14.88-1.13,20.84c0.23,0.24,
                                  5.69,6.03,11.41,11.93c3.41,3.51,6.2,6.33,8.3,8.38c4.23,4.13,7.88,7.69,14.07,7.78 C82.81,272.41,82.88,272.41,82.95,272.41z"></path> </g> <g> 
                                  <path class="st0" d="M464.28,247.82l-26.5-26.5c-2.75-2.75-6.57-4.3-10.44-4.23c-2.33,0.03-4.29,0.56-6.07,1.42 c-0.26,0.12-0.51,0.26-0.76,0.4c-0.04,
                                  0.02-0.08,0.04-0.12,0.06c-0.59,0.33-1.16,0.68-1.69,1.08c-1.88,1.34-3.6,3.03-5.44,4.82 c-2.1,2.05-4.89,4.87-8.3,8.38c-5.72,5.9-11.18,11.68-11.41,
                                  11.93c-5.46,5.79-5.19,14.91,0.6,20.36 c5.75,5.42,14.77,5.18,20.24-0.48c-4.72,83.85-74.42,150.62-159.43,150.62c-70.52,0-131.86-45.23-152.62-112.55 
                                  c-2.35-7.6-10.41-11.86-18.01-9.52c-7.6,2.34-11.86,10.41-9.52,18.01c11.62,37.68,35.48,71.52,67.19,95.28 c32.8,24.59,71.86,37.58,112.96,37.58c100.11,
                                  0,182.23-78.45,188.14-177.1l0.79,0.79c2.81,2.81,6.5,4.22,10.18,4.22 c3.69,0,7.37-1.41,10.18-4.22C469.91,262.57,469.91,253.45,464.28,247.82z">
                                  </path> </g> </g> </g> </g></svg>
                              </div>
                              </button>
                            </div>';
                    }
                  } else {
                      echo "<tr><td colspan='3'>No database found in Customer Table.</td></tr>";
                    }
                } else {
                    die("Error in User query: " . $conn->error);
                  }
              ?>
            </div>
              <script>
                function changeProfile(cId) {
                  var fileInput = document.createElement("input");
                  fileInput.type = "file";
                  fileInput.accept = "image/*"; // Allow only image files

                  fileInput.click();

                  fileInput.addEventListener("change", function () {
                    var selectedFile = fileInput.files[0];
                    if (selectedFile) {
                      // Display the file name (optional)
                      console.log("Selected File: " + selectedFile.name);

                      // Call the function to submit the form with the selected file
                      submitEditAvatar(cId, selectedFile);
                    } else {
                      // User canceled file selection
                      console.log("File selection canceled");
                    }
                  });
                }

                function submitEditAvatar(cId, selectedFile) {
                  // Use FormData to handle file uploads
                  var formData = new FormData();

                  // Append player ID and selected file to the FormData object
                  formData.append("cId", cId);
                  formData.append("cAvatar", selectedFile);

                  // Use XMLHttpRequest to send the FormData to the server
                  var xhr = new XMLHttpRequest();
                  xhr.open("POST", "change_avatar.php", true);

                  // Define a callback function to handle the server's response
                  xhr.onreadystatechange = function () {
                    if (xhr.readyState === 4 && xhr.status === 200) {
                      console.log(xhr.responseText);
                      if (xhr.responseText.includes("Avatar updated successfully")) {
                          // Refresh the image by reloading the page or changing the image source
                          location.reload(); // or refresh only the image
                      } else {
                          alert(xhr.responseText); // Show any errors
                      }
                    }
                  };

                  // Send the FormData to the server
                  xhr.send(formData);
                }
              </script>
              <div class="mt-3">
                <?php
                  require_once 'database.php';

                  $cUser = $rows['cUser'];
                  $custQuery = "SELECT cId, cName, cUser FROM customer WHERE cUser = '$cUser'";
                  $custResult = $conn->query($custQuery);
                  if ($custResult) {
                    if ($custResult->num_rows > 0) {
                      while ($custRow = $custResult->fetch_assoc()) {
                        echo "<h5><strong>" . $custRow['cName'] . "</strong></h5>";
                        echo "<h6>" . $custRow['cUser'] . "</h6>";
                      }
                    } else {
                        echo "<tr><td colspan='3'>No database found.</td></tr>";
                      }
                  } else {
                      die("Error in user query: " . $conn->error);
                    }
                ?>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!--Profile section-->
      <div class="col-md-8">
        <div class="profile-card col-sm-12">
          <div class="h-100">
            <div class="card-body">
              <h5 class="card-title">Player Profile</h5>
                <table class="table table-striped">
                  <tbody>
                    <?php
                      require_once 'database.php';

                      if (!$conn) {
                        die("Connection failed: " . mysqli_connect_error());
                      }

                      $cUser = $rows['cUser'];
                      $custQuery = "SELECT cId, cName, cUser, cEmail, cPhone FROM customer WHERE cUser = '$cUser'";
                      $custResult = mysqli_query($conn, $custQuery);

                      if ($custResult) {
                        if (mysqli_num_rows($custResult) > 0) {
                          while ($custRow = mysqli_fetch_assoc($custResult)) {
                            echo "<tr>";
                            echo "<th>Full Name</th>";
                            echo "<td>" . $custRow['cName'] . "</td>";
                            echo "<td><button class='btn fa fa-edit edit-btn' onclick='editFullName(" . $custRow['cId'] . ")'></button></td>";
                            echo "</tr>";
                            echo "<tr>";
                            echo "<th>Username</th>";
                            echo "<td>" . $custRow['cUser'] . "</td>";
                            echo "<td></td>"; //<button class='btn fa fa-edit edit-btn' onclick='editUsername(" . $custRow['cId'] . ")'></button>
                            echo "</tr>";
                            echo "<tr>";
                            echo "<th>Email</th>";
                            echo "<td>" . $custRow['cEmail'] . "</td>";
                            echo "<td><button class='btn fa fa-edit edit-btn' onclick='editEmail(" . $custRow['cId'] . ")'></button></td>";
                            echo "</tr>";
                            echo "<tr>";
                            echo "<th>Phone</th>";
                            echo "<td>" . $custRow['cPhone'] . "</td>";
                            echo "<td><button class='btn fa fa-edit edit-btn' onclick='editPhone(" . $custRow['cId'] . ")'></button></td>";
                            echo "</tr>";              
                          }
                        } else {
                            echo "<tr><td colspan='3'>No database found.</td></tr>";
                          }
                      } else {
                          die("Error in user query: " . mysqli_error($conn));
                        }
                    ?>
                  </tbody>
                </table>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!--Booked Facilities section-->
    <div class="col-md-12">
                      <div class="profile-card col-sm-12">
                        <div class="h-100">
                          <div class="card-body">
                            <h5 class="card-title">Booked Facilities</h5>
                            <table class="table table-striped">
                              <thead>
                                <tr>
                                  <th>Booking ID</th>
                                  <th>Category</th>
                                  <th>Court Type</th>
                                  <th>Timeslot</th>
                                  <th>Participant</th>
                                  <th>Status</th>
                                  <th>Action</th>
                                </tr>
                              </thead>
                              <tbody>
                                <?php
                                  require_once 'database.php';

                                  if (!$conn) {
                                    die("Connection failed: " . mysqli_connect_error());
                                  }

                                  $cEmail = $_SESSION['cEmail'];
                                  $bookingQuery = "SELECT bID, courtType, preferredCourt, datestart, dateend, people, payment_status FROM bookings WHERE cEmail = '$cEmail'";
                                  $bookingResult = mysqli_query($conn, $bookingQuery);

                                  if ($bookingResult) {
                                      if (mysqli_num_rows($bookingResult) > 0) {
                                          while ($bookingRow = mysqli_fetch_assoc($bookingResult)) {
                                              echo "<tr>";
                                              echo "<td>" . $bookingRow['bID'] . "</td>";
                                              echo "<td>" . $bookingRow['courtType'] . "</td>";
                                              echo "<td>" . $bookingRow['preferredCourt'] . "</td>";
                                              echo "<td>" . $bookingRow['datestart'] . " - " . $bookingRow['dateend'] . "</td>";
                                              echo "<td>" . $bookingRow['people'] . "</td>";
                                              echo "<td>" . $bookingRow['payment_status'] . "</td>";
                                              echo "<td>";
                                              // echo "<button class='btn fa fa-edit edit-btn' onclick='updateBooking(" . $bookingRow['bID'] . ")'></button> ";
                                              echo "<button class='btn fa fa-trash delete-btn' onclick='deleteBooking(" . $bookingRow['bID'] . ")'></button>";
                                              echo "</td>";
                                              echo "</tr>";
                                          }
                                      } else {
                                          echo "<tr><td colspan='3'>No booked facilities found.</td></tr>";
                                      }
                                  } else {
                                      die("Error in booking query: " . mysqli_error($conn));
                                  }

                                  mysqli_close($conn);
                                ?>
                              </tbody>
                            </table>
                          </div>
                        </div>
                      </div>
                    </div>
                    <script>
                      function deleteBooking(bID) {
                      if (confirm("Are you sure you want to delete this booking? Your payment will not be refunded.")) {
                        if (confirm("This action cannot be undone. Are you sure?")) {
                          var form = document.createElement("form");
                          form.method = "post";
                          form.action = "delete_booking.php";

                          var input = document.createElement("input");
                          input.type = "hidden";
                          input.name = "bID";
                          input.value = bID;

                          form.appendChild(input);
                          document.body.appendChild(form);

                          form.submit();
                        }
                      }
                    }
                    </script>
  </div>
</div>

  <!-- Logout Confirmation Modal -->
  <div id="logoutModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="logoutModalLabel" aria-hidden="true">
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

  <!-- Modal for $notify -->
  <div class="modal fade modal-fix" id="notifyModal" tabindex="-1" role="dialog" aria-labelledby="notifyModalLabel">
    <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
      <div class="modal-content">
        <?php echo $notify; ?>
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
      // edit full name
      function editFullName(cId) {
        var editName = prompt("New Full Name:");
        if (editName !== null && editName.trim() !== "") {
          submitFNameForm(cId, editName);
        } else {
          alert("Full Name cannot be empty.");
        }
      }
      function submitFNameForm(cId, editName) {
        var form = document.createElement("form");
        form.method = "post";
        form.action = "edit_profile.php";

        var inputId = document.createElement("input");
        inputId.type = "hidden";
        inputId.name = "cId";
        inputId.value = cId;

        var inputName = document.createElement("input");
        inputName.type = "hidden";
        inputName.name = "cName";
        inputName.value = editName;

        form.appendChild(inputId);
        form.appendChild(inputName);

        document.body.appendChild(form);
        form.submit();
      }

      // edit username
      // function editUsername(cId) {
      //   var editUsername = prompt("New Username:");
      //   if (editUsername !== null && editUsername.trim() !== "") {
      //     submitUsernameForm(cId, editUsername);
      //   } else {
      //     alert("Username cannot be empty.");
      //   }
      // }
      // function submitUsernameForm(cId, editUsername) {
      //   var form = document.createElement("form");
      //   form.method = "post";
      //   form.action = "edit_profile.php";

      //   var inputId = document.createElement("input");
      //   inputId.type = "hidden";
      //   inputId.name = "cId";
      //   inputId.value = cId;

      //   var inputUsername = document.createElement("input");
      //   inputUsername.type = "hidden";
      //   inputUsername.name = "cUser";
      //   inputUsername.value = editUsername;

      //   form.appendChild(inputId);
      //   form.appendChild(inputUsername);

      //   document.body.appendChild(form);
      //   form.submit();
      // }

      // edit email
      function editEmail(cId) {
        var editEmail = prompt("New Email:");
        if (editEmail !== null && editEmail.trim() !== "") {
          submitEmailForm(cId, editEmail);
        } else {
          alert("Email cannot be empty.");
        }
      }
      function submitEmailForm(cId, editEmail) {
        var form = document.createElement("form");
        form.method = "post";
        form.action = "edit_profile.php";

        var inputId = document.createElement("input");
        inputId.type = "hidden";
        inputId.name = "cId";
        inputId.value = cId;

        var inputEmail = document.createElement("input");
        inputEmail.type = "hidden";
        inputEmail.name = "cEmail";
        inputEmail.value = editEmail;

        form.appendChild(inputId);
        form.appendChild(inputEmail);

        document.body.appendChild(form);
        form.submit();
      }

      // edit phone
      function editPhone(cId) {
        var editPhone = prompt("New Phone:");
        if (editPhone !== null && editPhone.trim() !== "") {
          submitPhoneForm(cId, editPhone);
        } else {
          alert("Phone number cannot be empty.");
        }
      }
      function submitPhoneForm(cId, editPhone) {
        var form = document.createElement("form");
        form.method = "post";
        form.action = "edit_profile.php";

        var inputId = document.createElement("input");
        inputId.type = "hidden";
        inputId.name = "cId";
        inputId.value = cId;

        var inputPhone = document.createElement("input");
        inputPhone.type = "hidden";
        inputPhone.name = "cPhone";
        inputPhone.value = editPhone;

        form.appendChild(inputId);
        form.appendChild(inputPhone);

        document.body.appendChild(form);
        form.submit();
      }
  </script>
  <script>
        $(document).ready(function() {
      <?php if (!empty($notify)) { ?>
        $('#notifyModal').modal('show');
      <?php } ?>
    });
  </script>
  <script src="scripts.js"></script>
</body>
</html>