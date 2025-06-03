<?php
session_start();
include("db.php");

// Check if user is logged in as admin
// if (!isset($_SESSION['admin_id']) || $_SESSION['role'] !== 'super_admin') {
//     header("Location: Log_In.php");
//     exit();
// }

$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $full_name = trim($_POST['full_name']);
    $role = $_POST['role'];

    // Validation
    if (empty($username) || empty($email) || empty($password) || empty($full_name)) {
        $error_message = "All fields are required!";
    } elseif ($password !== $confirm_password) {
        $error_message = "Passwords do not match!";
    } elseif (strlen($password) < 8) {
        $error_message = "Password must be at least 8 characters long!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email format!";
    } else {
        // Check if username or email already exists
        $check_query = "SELECT * FROM admins WHERE username = ? OR email = ?";
        $check_stmt = $con->prepare($check_query);
        $check_stmt->bind_param("ss", $username, $email);
        $check_stmt->execute();
        $result = $check_stmt->get_result();

        if ($result->num_rows > 0) {
            $error_message = "Username or email already exists!";
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert new admin
            $insert_query = "INSERT INTO admins (username, password, email, full_name, role, status) VALUES (?, ?, ?, ?, ?, 'active')";
            $insert_stmt = $con->prepare($insert_query);
            $insert_stmt->bind_param("sssss", $username, $hashed_password, $email, $full_name, $role);

            if ($insert_stmt->execute()) {
                $success_message = "Admin account created successfully!";
            } else {
                $error_message = "Error creating admin account. Please try again.";
            }
            $insert_stmt->close();
        }
        $check_stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Admin Account - RMMC Lost Item Management System</title>
    <link rel="stylesheet" href="Styles.css">
    <style>
        .container {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .message {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .password-requirements {
            font-size: 0.9em;
            color: #666;
            margin-top: 5px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        select {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
    </style>
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
        <h2>Create New Admin Account</h2>
        
        <?php if ($success_message): ?>
            <div class="message success"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>
        
        <?php if ($error_message): ?>
            <div class="message error"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required 
                       value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                       placeholder="Enter username">
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required 
                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                       placeholder="Enter email">
            </div>

            <div class="form-group">
                <label for="full_name">Full Name</label>
                <input type="text" id="full_name" name="full_name" required 
                       value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>"
                       placeholder="Enter full name">
            </div>

            <div class="form-group">
                <label for="role">Role</label>
                <select id="role" name="role" required>
                    <option value="admin" <?php echo (isset($_POST['role']) && $_POST['role'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
                    <option value="super_admin" <?php echo (isset($_POST['role']) && $_POST['role'] == 'super_admin') ? 'selected' : ''; ?>>Super Admin</option>
                </select>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required placeholder="Enter password">
                <div class="password-requirements">
                    Password must be at least 8 characters long
                </div>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required placeholder="Confirm password">
            </div>

            <button type="submit">Create Admin Account</button>
            <a href="AdminDashboard.php" style="display: block; text-align: center; margin-top: 10px;">Back to Dashboard</a>
        </form>
    </div>

    <script>
        // Add password validation
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const requirements = document.querySelector('.password-requirements');
            
            if (password.length < 8) {
                requirements.style.color = '#dc3545';
            } else {
                requirements.style.color = '#28a745';
            }
        });

        // Add confirm password validation
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            
            if (password !== confirmPassword) {
                this.setCustomValidity('Passwords do not match');
            } else {
                this.setCustomValidity('');
            }
        });
    </script>
</body>
</html> 