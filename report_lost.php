<?php
session_start();
header('Content-Type: application/json');
include('../db.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$item_name = $data['item_name'] ?? '';
$date_lost = $data['date_lost'] ?? '';
$color = $data['color'] ?? '';
$time_lost = $data['time_lost'] ?? '';
$location = $data['location'] ?? '';
$specific_location = $data['specific_location'] ?? '';
$description = $data['description'] ?? '';
$status = 'lost';

if (!$item_name || !$date_lost || !$color || !$location) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields']);
    exit;
}

if ($specific_location) {
    $location .= ' - ' . $specific_location;
}

$sql = "INSERT INTO lost_items (reporter_id, reporter_type, item_name, date_lost, color, estimated_time, location_lost, description, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $con->prepare($sql);
$stmt->bind_param(
    "sssssssss",
    $_SESSION['studentID'],
    'student',
    $item_name,
    $date_lost,
    $color,
    $time_lost,
    $location,
    $description,
    $status
);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Lost item reported']);
} else {
    http_response_code(500);
    echo json_encode(['error' => $stmt->error]);
}
?>
