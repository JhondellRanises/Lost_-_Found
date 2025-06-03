<?php
session_start();
include("db.php");

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $firstname = $_POST['fname'];
    $lastname = $_POST['lname'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    if (!empty($email) && !empty($password)) {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert into staff table
        $stmt = $con->prepare("INSERT INTO staff (fname, lname, email, password_hash) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $firstname, $lastname, $email, $hashed_password);

        if ($stmt->execute()) {
            echo "<script>
                alert('You created an account successfully.');
                window.location.href = 'Log_In.php';
            </script>";
            exit;
        } else {
            echo "<script>alert('Error: Could not create account. Email might already exist.')</script>";
        }

        $stmt->close();
    } else {
        echo "<script>alert('Please enter valid information.')</script>";
    }
}
?>




<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Create Staff Account - RMMC Lost Item Management System</title>
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
    <h2>Create a Staff Account</h2>

    <!-- Profile Image Upload Section -->
    <div class="profile-upload">
      <input type="file" id="profileInput" accept="image/*" />
      <label for="profileInput">
        <img src="Profile Icon.png" id="profileImage" alt="Profile Icon" />
      </label>
      <p class="profile-label">Add Profile</p>
    </div>

    <form id="registerForm" method="post">
      <label for="firstname">First Name</label>
      <input type="text" id="firstname" name="fname" required />

      <label for="lastname">Last Name</label>
      <input type="text" id="lastname" name="lname" required />

      <label for="email">Email</label>
      <input type="email" id="email" name="email" required />

      <label for="password">Password</label>
      <input type="password" id="password" name="password" required />

      <button type="submit">Create Account</button>
    </form>

    <p class="register-link">Already have an account? <a href="Log_In.php">Log in</a></p>
  </div>

  <script src="Script.js"></script>
</body>
</html>
