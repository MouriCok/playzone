<?php
  require_once 'database.php';

  if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
  }

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
          echo "<script>
                  alert('Registration successful! You may now login.');
                  window.location.href = 'login.php';
                </script>";
        } else {
          echo "Error: " . $sql . "<br>" . mysqli_error($conn);
        }
    } else {
      echo "<script>window.alert('Please fill in all the fields correctly.');</script>";
    }
    mysqli_close($conn);
  }
?>
<html>
<head>
  <title>Sports Facility Management System</title>
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

  <?php
    $cNameErr = $cUserErr = $cEmailErr = $cPhoneErr = $addressErr = $cPassErr = $ConPassErr = "";
    $cName = $cUser = $cEmail = $cPhone = $address = $cPass = $ConPass = "";

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (empty($_POST["cName"])) {
            $cNameErr = "*Full Name is required";
        } else {
            $cName = test_input($_POST["cName"]);
            if (!preg_match("/^[a-zA-Z-' ]*$/",$cName)) {
                $cNameErr = "Only letters and white space allowed";
            }
        }

        if (empty($_POST["cUser"])) {
          $cUserErr = "*Username is required";
        } else {
            $cUser = test_input($_POST["cUser"]);
            if (!preg_match("/^[a-zA-Z0-9]+$/",$cUser)) {
                $cUserErr = "Only letters and numbers allowed, no spaces";
            }
        }
        
        if (empty($_POST["cEmail"])) {
            $cEmailErr = "*Email is required";
        } else {
            $cEmail = test_input($_POST["cEmail"]);
            if (!filter_var($cEmail, FILTER_VALIDATE_EMAIL)) {
                $cEmailErr = "Invalid email format";
            }
        }

        if (empty($_POST["cPhone"])) {
          $cPhoneErr = "*Phone is required";
        } else {
            $cPhone = test_input($_POST["cPhone"]);
            if (!preg_match("/^[0-9]+$/",$cPhone)) {
                $cPhoneErr = "Only numbers allowed, no spaces";
            }
        }

        // if (empty($_POST["address"])) {
        //   $addressErr = "*Address is required";
        // } else {
        //     $address = test_input($_POST["address"]);
        //     if (!preg_match("/^[a-zA-Z0-9-' ]*$/",$address)) {
        //         $addressErr = "Only letters and numbers allowed";
        //     }
        // }

        if (empty($_POST["cPass"])) {
          $cPassErr = "*Password is required";
        } else {
          $cPass = test_input($_POST["cPass"]);
          if (!preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]{8,}$/",$cPass)) {
            $cPassErr = "Password should be at least 8 characters in length and should include at least one upper case letter and one number.";
          }
        }

        if (empty($_POST["ConPass"])) {
            $ConPassErr = "*Confirm Password is required";
        } else {
            $ConPass = test_input($_POST["ConPass"]);
            if ($ConPass != $cPass) {
                $ConPassErr = "Passwords do not match";
            }
        }
    }

    function test_input($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }
  ?>

<div class="page-container">
    <div class="login-container">
      <h2>User Registration</h2>
      <form method="post" name="reg_form">
      <div class="col-sm-6">
        <div class="form-group">
          <input type="text" id="cName" name="cName" required>
          <label for="cName"><i class="glyphicon glyphicon-text-background"></i> Full Name</label>
          <span class="error"><?php echo $cNameErr;?></span>
        </div>
        <div class="form-group">
          <input type="email" id="cEmail" name="cEmail" required>
          <label for="cEmail"><i class="glyphicon glyphicon-envelope"></i> Email</label>
          <span class="error"><?php echo $cEmailErr;?></span>
        </div>
      </div>
      <div class="col-sm-6">
        <div class="form-group">
          <input type="text" id="cUser" name="cUser" required>
          <label for="cUser"><i class="glyphicon glyphicon-user"></i> Username</label>
          <span class="error"><?php echo $cUserErr;?></span>
        </div>
        <div class="form-group">
          <input type="text" id="cPhone" name="cPhone" required>
          <label for="cPhone"><i class="glyphicon glyphicon-phone-alt"></i> Phone</label>
          <span class="error"><?php echo $cPhoneErr;?></span>
        </div>
      </div>
      <!-- <div class="col-sm-12">
        <div class="form-group">
          <input type="text" id="address" name="address" required>
          <label for="address"><i class="glyphicon glyphicon-map-marker"></i> Address</label>
          <span class="error"><?php //echo $addressErr;?></span>
        </div>
      </div> -->
      <div class="col-sm-6">
        <div class="form-group">
          <input type="password" id="cPass" name="cPass" required>
          <label for="cPass"><i class="glyphicon glyphicon-lock"></i> Password</label>
          <span class="error"><?php echo $cPassErr;?></span>
        </div>
      </div>
      <div class="col-sm-6">
        <div class="form-group">
          <input type="password" id="ConPass" name="ConPass" required>
          <label for="ConPass"><i class="glyphicon glyphicon-lock"></i> Confirm Password</label>
          <span class="error"><?php echo $ConPassErr; ?></span>
        </div>
      </div>
        <button name="submitBtn" class="submitBtn btn btn-default btn-primary" type="submit">Sign me up!</button>
      </form>
    </div>
    <!-- End register form -->
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