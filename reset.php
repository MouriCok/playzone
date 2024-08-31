<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$conn = mysqli_connect("localhost", "root", "", "sport_booking");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$message = ''; // Variable to hold success or error message

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    // Retrieve the reset token from the URL
    $resetToken = $_GET["token"];

    // Check if the token exists in the database and is still valid
    $sql = "SELECT * FROM customer WHERE reset_token = ? AND reset_expiry > NOW()";
    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        die("Error preparing statement: " . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt, "s", $resetToken);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result && $result->num_rows > 0) {
        // Token is valid, display a form to set a new password
        ?>
        <!DOCTYPE html>
        <html lang="en" dir="ltr">
        <head>
            <title>Reset Password</title>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <link rel="icon" href="PZ_icon-32x32.png" type="image/png">
            <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
            <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
            <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
            <link href="https://fonts.googleapis.com/css?family=Lato:300,400,700&display=swap" rel="stylesheet">
            <link href="https://fonts.googleapis.com/css2?family=League+Spartan:wght@600&display=swap" rel="stylesheet">
            <link href="https://fonts.googleapis.com/css2?family=Anton&family=League+Spartan:wght@600&display=swap" rel="stylesheet">
            <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
            <link rel="stylesheet" href="path/to/font-awesome/css/font-awesome.min.css">
            <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
            <link rel="stylesheet" href="stylee.css">
            <link rel="stylesheet" href="formm.css">
            <style>
                .page-container {
                    margin: 58px;
                    padding: 15px;
                    min-height: 65vh;
                }
                .login-container {
                    padding: 20px;
                    width: 420px;
                    margin-top: 97px;
                    margin-bottom: 95px;
                }
            </style>
            <script>
                function showAlert(message, redirectUrl) {
                    console.log("showAlert called with message: " + message);
                    alert(message);
                    window.location.href = redirectUrl;
                }
            </script>
        </head>
        <body>
            <nav class="navbar navbar-inverse">
                <div class="container-fluid">
                    <div class="navbar-header">
                        <img src="logo1.png" width="100" height="50">  
                    </div>
                </div>
            </nav>

            <?php
            $cPassErr = $rPassErr = "";
            $cPass = $rPass = "";

            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                // Handle form submission
                echo '<pre>'; print_r($_POST); echo '</pre>'; // Debugging

                $cPass = $_POST["cPass"];
                $rPass = $_POST["rPass"];

                // Validate and update password
                if (empty($cPass)) {
                    $cPassErr = "*Password is required";
                } elseif (!preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]{8,}$/", $cPass)) {
                    $cPassErr = "Password should be at least 8 characters in length and should include at least one upper case letter and one number.";
                }

                if (empty($rPass)) {
                    $rPassErr = "*Confirm Password is required";
                } elseif ($rPass != $cPass) {
                    $rPassErr = "Passwords do not match";
                }

                if (empty($cPassErr) && empty($rPassErr)) {
                    // Update password in the database
                    $hashedPassword = password_hash($cPass, PASSWORD_DEFAULT);

                    // Update the password and clear reset token
                    $updateSql = "UPDATE customer SET cPass=?, reset_token=NULL, reset_expiry=NULL WHERE reset_token=?";
                    $stmt = mysqli_prepare($conn, $updateSql);

                    if ($stmt) {
                        mysqli_stmt_bind_param($stmt, "ss", $hashedPassword, $resetToken);
                        mysqli_stmt_execute($stmt);

                        if (mysqli_stmt_affected_rows($stmt) > 0) {
                            $message = "Password updated successfully!";
                        } else {
                            $message = "Failed to update password. Please check if the token is correct or expired.";
                        }
                    } else {
                        $message = "Error updating password: " . mysqli_error($conn);
                    }
                } else {
                    $message = "Error: " . $cPassErr . " " . $rPassErr;
                }
            }

            // Show the alert message and redirect if applicable
            if ($message) {
                echo "<script>showAlert('$message', 'login.php');</script>";
            }
            ?>

            <div class="page-container">
                <div class="login-container">
                    <h2>RESET PASSWORD</h2>
                    <form method="post" name="reset_form">
                        <div class="form-group">
                            <input type="password" id="cPass" name="cPass" required>
                            <label for="cPass"><i class="glyphicon glyphicon-lock"></i> New Password</label>
                            <span class="error"><?php echo $cPassErr;?></span>
                        </div>
                        <div class="form-group">
                            <input type="password" id="rPass" name="rPass" required>
                            <label for="rPass"><i class="glyphicon glyphicon-lock"></i> Confirm New Password</label>
                            <span class="error"><?php echo $rPassErr; ?></span>
                        </div>
                        <button type="submit" name="submitBtn" class="submitBtn btn btn-default btn-primary">Submit</button>
                    </form>
                </div>
            </div>
            <footer class="container-fluid text-center">
                <ul class="nav navbar-nav navbar-right">
                    <li>
                        <h5 style="color: #fff; margin: 18px;">&copy; 2022-2023 BIT Sports. All rights reserved.</h5>
                    </li>
                </ul>
            </footer>
        </body>
        </html>
        <?php
    } else {
        // Invalid or expired token
        echo "Invalid or expired reset token.";
    }

    mysqli_close($conn);
}
?>
