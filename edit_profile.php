<?php
require_once 'database.php';

// Check if the request contains the necessary parameters
if (isset($_POST['cId'])) {
    $cId = mysqli_real_escape_string($conn, $_POST['cId']);

    $editName = isset($_POST['cName']) && !empty(trim($_POST['cName'])) ? mysqli_real_escape_string($conn, $_POST['cName']) : null;
    // $editUsername = isset($_POST['cUser']) && !empty(trim($_POST['cUser'])) ? mysqli_real_escape_string($conn, $_POST['cUser']) : null;
    $editEmail = isset($_POST['cEmail']) && !empty(trim($_POST['cEmail'])) ? mysqli_real_escape_string($conn, $_POST['cEmail']) : null;
    $editPhone = isset($_POST['cPhone']) && !empty(trim($_POST['cPhone'])) ? mysqli_real_escape_string($conn, $_POST['cPhone']) : null;

    $updateFields = [];

    // Build the update query dynamically based on which fields are provided
    if ($editName !== null) {
        $updateFields[] = "cName = '$editName'";
    }
    // if ($editUsername !== null) {
    //     $updateFields[] = "cUser = '$editUsername'";
    // }
    if ($editEmail !== null) {
        $updateFields[] = "cEmail = '$editEmail'";
    }
    if ($editPhone !== null) {
        $updateFields[] = "cPhone = '$editPhone'";
    }

    if (!empty($updateFields)) {
        $editQuery = "UPDATE customer SET " . implode(", ", $updateFields) . " WHERE cId = $cId";

        if (mysqli_query($conn, $editQuery)) {
            header ("location: profile.php");
        } else {
            echo "Error updating profile: " . mysqli_error($conn);
        }
    } else {
        echo "No valid fields provided for update.";
    }
} else {
    echo "Invalid request. Please provide all required parameters.";
}

// Close the database connection
mysqli_close($conn);
?>
