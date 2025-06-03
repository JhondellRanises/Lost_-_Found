<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['role'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

echo json_encode([
    'role' => $_SESSION['role'],
    'email' => $_SESSION['email'] ?? null,
    'studentID' => $_SESSION['studentID'] ?? null,
    'fname' => $_SESSION['fname'] ?? null,
    'lname' => $_SESSION['lname'] ?? null
]);
?>
