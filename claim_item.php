<?php
session_start();
header('Content-Type: application/json');
include('../db.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

if (!isset($_SESSION['role'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$item_id = $data['item_id'] ?? null;
$item_type = $data['item_type'] ?? null; // should be 'found'

if (!$item_id || !$item_type) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing item information']);
    exit;
}

if ($item_type !== 'found') {
    http_response_code(400);
    echo json_encode(['error' => 'Claiming is only supported for found items.']);
    exit;
}

$claimant_id = $_SESSION['role'] === 'user' ? $_SESSION['studentID'] : $_SESSION['email'];

$sql = "UPDATE found_items SET claim_status = 'pending', claimed_by = ?, claimed_at = NOW() WHERE found_id = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("si", $claimant_id, $item_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Item claimed']);
} else {
    http_response_code(500);
    echo json_encode(['error' => $stmt->error]);
}
?>
