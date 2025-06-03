<?php
session_start();
include('db.php');

// Check if user is logged in
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (isset($data['found_id'])) {
        $found_id = $data['found_id'];
        $student_id = $_SESSION['studentID'];
        $claim_date = date('Y-m-d H:i:s');
        $status = 'pending';

        try {
            // Check if user has already claimed this item
            $check_stmt = $con->prepare("SELECT * FROM pending_claims WHERE found_id = ? AND student_id = ?");
            $check_stmt->bind_param("is", $found_id, $student_id);
            $check_stmt->execute();
            $result = $check_stmt->get_result();

            if ($result->num_rows > 0) {
                echo json_encode(['success' => false, 'message' => 'You have already claimed this item']);
                exit();
            }

            // Insert new claim
            $stmt = $con->prepare("INSERT INTO pending_claims (found_id, student_id, claim_date, status) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("isss", $found_id, $student_id, $claim_date, $status);

            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Your claim has been submitted successfully']);
            } else {
                throw new Exception("Error submitting claim");
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error processing claim: ' . $e->getMessage()]);
        }
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid request data']);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
?> 