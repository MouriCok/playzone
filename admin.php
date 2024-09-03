<?php
  session_start();
  require_once 'database.php';

  // Establish database connection
  if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
  }

  // Check if the admin is already logged in
  if (isset($_SESSION['loggedIn']) && $_SESSION['loggedIn'] === true) {
    header("Location: admin_profile.php");
    exit();
  }
  
  if (isset($_POST['submit'])) {
    $aUser = $_POST['aUser'];
    $aPass = $_POST['aPass'];

    // Prepare the SQL statement
    $stmt = $conn->prepare("SELECT * FROM admin WHERE aUser = ? AND aPass = ?");
    
    // Bind parameters
    $stmt->bind_param("ss", $aUser, $aPass);
    
    // Execute the statement
    $stmt->execute();
    
    // Get the result
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
      $_SESSION['loggedIn'] = true;
      $_SESSION['aUser'] = $aUser;
      header("Location: admin_profile.php");
      exit();
    } else {
      $error_message = "Invalid Username or Password";
    }
    
    // Close statement and connection
    $stmt->close();
    mysqli_close($conn);
  }
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
  <title>Admin Login</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" href="PZ_icon-32x32.png" type="image/png">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
  <link rel="stylesheet" href="stylee.css">
  <link rel="stylesheet" href="form.css">
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

  <div class="page-container login-form">
    <div class="login-container">
      <h2>Admin Login</h2>
      <form method="post" action="admin.php" id="login-form" class="form">
        <div class="form-group">
          <input type="text" id="aUser" name="aUser" required>
          <label for="aUser"><i class="glyphicon glyphicon-user"></i> Username</label>
        </div>
        <div class="form-group">
          <input type="password" id="aPass" name="aPass" required>
          <label for="aPass"><i class="glyphicon glyphicon-lock"></i> Password</label>
        </div>
        <button type="submit" name="submit" class="submitBtn btn btn-default btn-primary">Login</button>
      </form>

      <div>
        <br><a href="forgot.php">Forgot Password?</a>
      </div>
      <div>
        Don't have an account? <a href="register.php">Sign Up</a>
      </div>
    </div>
  </div>

  <!-- Modal for Error Message -->
  <div class="modal fade" id="errorModal" tabindex="-1" role="dialog" aria-labelledby="errorModalLabel">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title" id="errorModalLabel">Login Error</h4>
        </div>
        <div class="modal-body">
          <?php echo $error_message; ?>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary" onclick="window.history.back()">OK</button>
        </div>
      </div>
    </div>
  </div>

  <footer class="container-fluid text-center">
    <div class="collapse navbar-collapse" id="myNavbar">
      <ul class="nav navbar-nav navbar-right">
        <li>
          <h5>Open-source Apache Licensed</h5>
        </li>
      </ul>
    </div>
  </footer>

  <script>
    $(document).ready(function() {
      <?php if (!empty($error_message)) { ?>
        $('#errorModal').modal('show');
      <?php } ?>
    });
  </script>
  <script src="scripts.js"></script>
</body>
</html>