<?php
session_start();
require_once 'database.php';

// Check if the user is logged in, if not then redirect to login page
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("location: login.php");
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
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
  <link href="https://fonts.googleapis.com/css2?family=League+Spartan:wght@600&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Anton&family=League+Spartan:wght@600&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <link rel="stylesheet" href="stylee.css">
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
    .card {
        box-shadow: 0 1px 3px 0 rgba(0,0,0,.1), 0 1px 2px 0 rgba(0,0,0,.06);
        background-color: #fff;
        border: 0 solid rgba(0,0,0,.125);
        border-radius: .45rem;
    }
    .card-body {
        flex: 1 1 auto;
        padding: 1rem;
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
    .mb-3, .my-3 {
        margin-bottom: 2rem!important;
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
    <nav class="navbar navbar-inverse">
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
                <li><a href="menu.php" class="nav-btn">Booking List</a></li>
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
                                        <li class="dropdown-item"><a href="profile.php">Settings</a></li>
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
                                <button class="Btn" data-toggle="modal" data-target="#logoutModal">
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
      <div class="col-md-4 mb-3">
        <div class="card">
          <div class="card-body">
            <div class="d-flex flex-column align-items-center text-center">
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
                          echo "<img src='" . $custRow['cAvatar'] . "' alt='user' class='rounded-circle' width='140' height='140'>";
                        } else {
                          // If user has no custom avatar, display the default avatar
                          echo "<img src='default_avatar.png' alt='default_avatar' class='rounded-circle' width='140' height='140'>";
                        }
                      } else {
                        // If user is not logged in, display the default avatar
                        echo "<img src='default_avatar.png' alt='default_avatar' class='rounded-circle' width='140' height='140'>";
                      }
                      echo "<br><br>";
                      echo "<button class='btn btn-primary btn-sm' onclick='changeProfile(" . $custRow['cId'] . ")'>Change</button> ";
                    }
                  } else {
                      echo "<tr><td colspan='3'>No database found.</td></tr>";
                    }
                } else {
                    die("Error in user query: " . $conn->error);
                  }
              ?>
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
        <div class="card col-sm-12 mb-3">
          <div class="h-100">
            <div class="card-body">
              <h5 class="card-title">Profile</h5>
                <table class="table table-striped">
                  <tbody>
                    <?php
                      $conn = mysqli_connect("localhost", "root", "", "sport_booking");

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
                            echo "</tr>";
                            echo "<tr>";
                            echo "<th>Username</th>";
                            echo "<td>" . $custRow['cUser'] . "</td>";
                            echo "</tr>";
                            echo "<tr>";
                            echo "<th>Email</th>";
                            echo "<td>" . $custRow['cEmail'] . "</td>";
                            echo "</tr>";
                            echo "<tr>";
                            echo "<th>Phone</th>";
                            echo "<td>" . $custRow['cPhone'] . "</td>";
                            echo "</tr>";
                            echo "<tr>";
                            echo "<th>";
                            echo "<button class='btn btn-primary btn-sm' onclick='editProfile(" . $custRow['cId'] . ")'>Edit</button> ";
                            echo "</th>";
                            echo "<td></td>";
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
  </div>
</div>

  <!-- Logout Confirmation Modal -->
  <div id="logoutModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="logoutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
      <div class="modal-content">
        <div class="modal-header">
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
        <div class="modal-body">
          Are you sure you want to log out?
        </div>
        <div class="modal-footer">
          <button type="button" class="CBtn" data-dismiss="modal">Cancel</button>
          <button type="button" class="LBtn" onclick="location.href='?logout=true'">Logout</button>
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
                      function editProfile(cId) {
                        var editName = prompt("Enter new Full Name:");
                        var editEmail = prompt("Enter new Email");
                        var editPhone = prompt("Enter new Phone Number:");

                        if (editName !== null && editEmail !== null && editPhone !== null) {
                          submitEditForm(cId, editName, editEmail, editPhone);
                        }
                      }

                      function submitEditForm(cId, editName, editEmail, editPhone) {
                        var form = document.createElement("form");
                        form.method = "post";
                        form.action = "edit_profile.php";

                        var inputId = document.createElement("input");
                        inputId.type = "hidden";
                        inputId.name = "cId";
                        inputId.value = cId;

                        var inputName = document.createElement("input");
                        inputName.type = "hidden";
                        inputName.name = "name";
                        inputName.value = editName;

                        var inputEmail = document.createElement("input");
                        inputEmail.type = "hidden";
                        inputEmail.name = "email";
                        inputEmail.value = editEmail;

                        var inputPhone = document.createElement("input");
                        inputPhone.type = "hidden";
                        inputPhone.name = "phone";
                        inputPhone.value = editPhone;

                        form.appendChild(inputId);
                        form.appendChild(inputName);
                        form.appendChild(inputEmail);
                        form.appendChild(inputPhone);

                        document.body.appendChild(form);
                        
                        form.submit();
                      }
                    </script>
</body>
</html>