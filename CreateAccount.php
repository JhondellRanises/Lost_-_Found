<?php
session_start();

include("db.php");

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $firstname = $_POST['fname'];
    $lastname = $_POST['lname'];
    $studentID = $_POST['studentID'];
    $password = $_POST['password'];

   if (!empty($studentID) && !empty($password)) {
    // Check if studentID is only numbers
    if (!ctype_digit($studentID)) {
        echo "<script>alert('Student ID must contain only numbers.')</script>";
    } else {
        // Proceed with password hashing and inserting
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $con->prepare("INSERT INTO users (fname, lname, StudentID, password_hash) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $firstname, $lastname, $studentID, $hashed_password);

        if ($stmt->execute()) {
            echo "<script>
                alert('You created an account successfully.');
                window.location.href = 'Log_In.php';
            </script>";
            exit;
        } else {
            echo "<script>alert('Error: Could not create account.')</script>";
        }

        $stmt->close();
    }
} else {
    echo "<script>alert('Please Enter Valid Information')</script>";
}
}
?>




<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Create an Account - RMMC Lost Item Management System</title>
  <link rel="stylesheet" href="Styles.css" />
</head>

<body>

  <div class="top-logo">
    <img src="LOGO.png" alt="RMMC Logo" />
    <div class="logo-text-group">
      <h1 class="school-name">Ramon Magsaysay</h1>
      <h2 class="school-subtitle">Memorial Colleges</h2>
    </div>
  </div>

  <div class="container">
    <h2>Create an Account</h2>

    <form id="registerForm" method="post">
      <label for="firstname">First Name</label>
      <input type="text" id="firstname" name="fname" required />

      <label for="lastname">Last Name</label>
      <input type="text" id="lastname" name="lname" required />

      <label for="studentID">Student Number</label>
      <input type="text" id="studentID" name="studentID" required />

      <label for="password">Password</label>
      <input type="password" id="password" name="password" required />

      <button type="submit">Create Account</button>
    </form>

    <p class="register-link">Already have an account? <a href="Log_In.php">Log in</a></p>
  </div>

  <script src="Script.js"></script>
</body>

</html>