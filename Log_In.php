<?php
session_start();
include("db.php");

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $identifier = isset($_POST['identifier']) ? trim($_POST['identifier']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $selected_role = isset($_POST['selected_role']) ? $_POST['selected_role'] : '';

    if (empty($identifier) || empty($password) || empty($selected_role)) {
        echo "<script>alert('Please enter your credentials')</script>";
    } else {
        $success = false;
        
        try {
            switch($selected_role) {
                case 'admin':
                    $query = "SELECT * FROM admins WHERE (username = ? OR email = ?) AND status = 'active' LIMIT 1";
                    $stmt = $con->prepare($query);
                    $stmt->bind_param("ss", $identifier, $identifier);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result && $result->num_rows > 0) {
                        $admin_data = $result->fetch_assoc();
                        if (password_verify($password, $admin_data['password'])) {
                            $_SESSION['admin_id'] = $admin_data['admin_id'];
                            $_SESSION['username'] = $admin_data['username'];
                            $_SESSION['role'] = $admin_data['role'];
                            
                            // Update last login time
                            $update_query = "UPDATE admins SET last_login = CURRENT_TIMESTAMP WHERE admin_id = ?";
                            $update_stmt = $con->prepare($update_query);
                            $update_stmt->bind_param("i", $admin_data['admin_id']);
                            $update_stmt->execute();
                            $update_stmt->close();
                            
                            header("Location: AdminDashboard.php");
                            exit;
                        }
                    }
                    $stmt->close();
                    break;

                case 'staff':
                    // Try staff login
                    $query = "SELECT * FROM staff WHERE email = ? LIMIT 1";
                    $stmt = $con->prepare($query);
                    $stmt->bind_param("s", $identifier);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result && $result->num_rows > 0) {
                        $staff_data = $result->fetch_assoc();
                        if (password_verify($password, $staff_data['password_hash'])) {
                            $_SESSION['email'] = $staff_data['email'];
                            $_SESSION['fname'] = $staff_data['fname'];
                            $_SESSION['lname'] = $staff_data['lname'];
                            $_SESSION['role'] = 'staff';

                            // Update last login time for staff
                            $update_query = "UPDATE staff SET last_login = CURRENT_TIMESTAMP WHERE email = ?";
                            $update_stmt = $con->prepare($update_query);
                            $update_stmt->bind_param("s", $staff_data['email']);
                            $update_stmt->execute();
                            $update_stmt->close();

                            header("Location: StaffDashboard.php");
                            exit;
                        }
                    }
                    $stmt->close();
                    break;

                case 'student':
                    // Try student login
                    $query = "SELECT * FROM users WHERE StudentID = ? LIMIT 1";
                    $stmt = $con->prepare($query);
                    $stmt->bind_param("s", $identifier);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result && $result->num_rows > 0) {
                        $user_data = $result->fetch_assoc();
                        if (password_verify($password, $user_data['password_hash'])) {
                            $_SESSION['studentID'] = $user_data['StudentID'];
                            $_SESSION['fname'] = $user_data['fname'];
                            $_SESSION['lname'] = $user_data['lname'];
                            $_SESSION['role'] = 'user';

                            // Update last login time for student
                            $update_query = "UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE StudentID = ?";
                            $update_stmt = $con->prepare($update_query);
                            $update_stmt->bind_param("s", $user_data['StudentID']);
                            $update_stmt->execute();
                            $update_stmt->close();

                            header("Location: UserDashboard.php");
                            exit;
                        }
                    }
                    $stmt->close();
                    break;
            }

            echo "<script>alert('Invalid credentials for selected role. Please try again.')</script>";
        } catch (Exception $e) {
            echo "<script>alert('Error: " . $e->getMessage() . "')</script>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>RMMC Lost Item Management System</title>
    <link rel="stylesheet" href="Styles.css" />
    <style>
        .container {
            background-color: rgba(255, 255, 255, 0.7);
            padding: 35px 50px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 500px;
            margin: 20px auto;
            box-sizing: border-box;
        }

        .container h2 {
            margin-bottom: 25px;
            color: #000000;
            font-size: 1.5rem;
            text-align: center;
        }

        .role-selector {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-bottom: 25px;
        }

        .role-btn {
            padding: 8px 15px;
            border: 2px solid #0e0e0e;
            background-color: white;
            color: #0e0e0e;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s;
            width: 100px;
            font-weight: bold;
            font-size: 0.9rem;
        }

        .role-btn.active {
            background-color: #0e0e0e;
            color: white;
        }

        .input-group {
            position: relative;
            margin-bottom: 20px;
            width: 100%;
        }

        .input-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .input-field {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 0.9rem;
            box-sizing: border-box;
            transition: border-color 0.3s;
            background-color: rgba(255, 255, 255, 0.9);
        }

        .input-field:focus {
            border-color: #2980b9;
            outline: none;
        }

        .options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 15px 0;
            font-size: 0.9rem;
        }

        .options a {
            color: #2980b9;
            text-decoration: none;
        }

        .options a:hover {
            text-decoration: underline;
        }

        button[type="submit"] {
            width: 100%;
            padding: 12px;
            background-color: #0e0e0e;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            cursor: pointer;
            transition: background-color 0.3s;
            margin-top: 10px;
        }

        button[type="submit"]:hover {
            background-color: #2980b9;
        }

        .create-account-options {
            margin-top: 25px;
            display: flex;
            justify-content: center;
            gap: 20px;
            width: 100%;
        }

        .create-account-btn {
            padding: 8px 16px;
            background-color: transparent;
            color: #2980b9;
            border: 1px solid #2980b9;
            border-radius: 4px;
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.3s;
            text-align: center;
            box-sizing: border-box;
            flex: 1;
            max-width: 200px;
        }

        .create-account-btn:hover {
            background-color: #2980b9;
            color: white;
        }

        form {
            width: 100%;
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
        <h2>Welcome to RMMC Lost Item Management System</h2>
        
        <div class="role-selector">
            <button type="button" class="role-btn active" data-role="student">Student</button>
            <button type="button" class="role-btn" data-role="staff">Staff</button>
        </div>

        <form id="loginForm" method="POST" action="Log_In.php">
            <input type="hidden" name="selected_role" id="selected_role" value="student">
            
            <div class="input-group">
                <label for="identifier" id="identifierLabel">Student Number</label>
                <input type="text" id="identifier" name="identifier" class="input-field" required placeholder="Enter your student number" />
            </div>

            <div class="input-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="input-field" required placeholder="Enter your password" />
            </div>

            <div class="options">
                <label><input type="checkbox" id="staySignedIn" /> Stay signed in</label>
                <a href="#">Forgot Password?</a>
            </div>

            <button type="submit">Log In</button>
        </form>

        <div class="create-account-options">
            <a href="CreateAccount.php" class="create-account-btn" id="studentRegister">Create Student Account</a>
            <a href="CreateStaff.php" class="create-account-btn" id="staffRegister">Create Staff Account</a>
        </div>
    </div>

    <script>
        // Role selection
        const roleButtons = document.querySelectorAll('.role-btn');
        const selectedRoleInput = document.getElementById('selected_role');
        const studentRegister = document.getElementById('studentRegister');
        const staffRegister = document.getElementById('staffRegister');
        const identifierInput = document.getElementById('identifier');
        const identifierLabel = document.getElementById('identifierLabel');

        roleButtons.forEach(button => {
            button.addEventListener('click', () => {
                // Remove active class from all buttons
                roleButtons.forEach(btn => btn.classList.remove('active'));
                // Add active class to clicked button
                button.classList.add('active');
                // Update hidden input value
                selectedRoleInput.value = button.dataset.role;
                
                // Update input type, label, and placeholder based on role
                switch(button.dataset.role) {
                    case 'student':
                        identifierLabel.textContent = 'Student Number';
                        identifierInput.placeholder = 'Enter your student number';
                        identifierInput.type = 'text';
                        studentRegister.style.display = 'block';
                        staffRegister.style.display = 'none';
                        break;
                    case 'staff':
                        identifierLabel.textContent = 'Email';
                        identifierInput.placeholder = 'Enter your email address';
                        identifierInput.type = 'email';
                        studentRegister.style.display = 'none';
                        staffRegister.style.display = 'block';
                        break;
                }
            });
        });
    </script>
</body>
</html>