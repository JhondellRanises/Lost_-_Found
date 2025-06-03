<?php
session_start();
header('Content-Type: application/json');
include('../db.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'staff') {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$item_name = $data['item_name'] ?? '';
$date_found = $data['date_found'] ?? '';
$color = $data['color'] ?? '';
$time_found = $data['time_found'] ?? '';
$location = $data['location'] ?? '';
$specific_location = $data['specific_location'] ?? '';
$description = $data['description'] ?? '';
$status = 'found';

if (!$item_name || !$date_found || !$color || !$location) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields']);
    exit;
}

if ($specific_location) {
    $location .= ' - ' . $specific_location;
}

$sql = "INSERT INTO found_items (reporter_id, reporter_type, item_name, date_found, color, estimated_time, location_found, description, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $con->prepare($sql);
$stmt->bind_param(
    "sssssssss",
    $_SESSION['email'],
    'staff',
    $item_name,
    $date_found,
    $color,
    $time_found,
    $location,
    $description,
    $status
);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Found item reported']);
} else {
    http_response_code(500);
    echo json_encode(['error' => $stmt->error]);
}
?>
