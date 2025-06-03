<?php
session_start();
header('Content-Type: application/json');
include('../db.php');

$data = json_decode(file_get_contents('php://input'), true);
$identifier = $data['identifier'] ?? '';
$password = $data['password'] ?? '';

if (!$identifier || !$password) {
    http_response_code(400);
    echo json_encode(['error' => 'Identifier and password required']);
    exit;
}

// Try admin login (by username or email)
$sql = "SELECT * FROM admins WHERE (username = ? OR email = ?) AND status = 'active' LIMIT 1";
$stmt = $con->prepare($sql);
$stmt->bind_param("ss", $identifier, $identifier);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $admin = $result->fetch_assoc();
    if (password_verify($password, $admin['password'])) {
        $_SESSION['admin_id'] = $admin['admin_id'];
        $_SESSION['username'] = $admin['username'];
        $_SESSION['email'] = $admin['email'];
        $_SESSION['role'] = $admin['role'];
        $_SESSION['fname'] = $admin['full_name'];
        echo json_encode(['success' => true, 'role' => $admin['role']]);
        exit;
    }
}

// Try user login (by StudentID)
$sql = "SELECT * FROM users WHERE StudentID = ? LIMIT 1";
$stmt = $con->prepare($sql);
$stmt->bind_param("s", $identifier);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $user = $result->fetch_assoc();
    if (password_verify($password, $user['password_hash'])) {
        $_SESSION['studentID'] = $user['StudentID'];
        $_SESSION['fname'] = $user['fname'];
        $_SESSION['lname'] = $user['lname'];
        $_SESSION['role'] = 'user';
        echo json_encode(['success' => true, 'role' => 'user']);
        exit;
    }
}

// Try staff login (by email)
$sql = "SELECT * FROM staff WHERE email = ? LIMIT 1";
$stmt = $con->prepare($sql);
$stmt->bind_param("s", $identifier);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $staff = $result->fetch_assoc();
    if (password_verify($password, $staff['password_hash'])) {
        $_SESSION['email'] = $staff['email'];
        $_SESSION['fname'] = $staff['fname'];
        $_SESSION['role'] = 'staff';
        echo json_encode(['success' => true, 'role' => 'staff']);
        exit;
    }
}

http_response_code(401);
echo json_encode(['error' => 'Invalid credentials']);
?>
