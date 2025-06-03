<?php
session_start();
include('db.php');

// Check if staff is logged in
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'staff') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (isset($data['claim_id']) && isset($data['action'])) {
        $claim_id = $data['claim_id'];
        $action = $data['action'];
        $staff_email = $_SESSION['email'];

        try {
            // Start transaction
            $con->begin_transaction();

            // First verify that this claim is for an item reported by this staff member
            $verify_sql = "SELECT pc.*, fi.* 
                         FROM pending_claims pc 
                         JOIN found_items fi ON pc.found_id = fi.found_id 
                         WHERE pc.claim_id = ? 
                         AND fi.reporter_id = ? 
                         AND fi.reporter_type = 'staff'
                         AND pc.status = 'pending'";
            
            $verify_stmt = $con->prepare($verify_sql);
            $verify_stmt->bind_param("is", $claim_id, $staff_email);
            $verify_stmt->execute();
            $result = $verify_stmt->get_result();

            if ($result->num_rows === 0) {
                throw new Exception("Invalid claim or unauthorized action");
            }

            $claim_data = $result->fetch_assoc();

            if ($action === 'approve') {
                // Insert into claimed_items table with the correct columns
                $insert_claimed_sql = "INSERT INTO claimed_items (
                    item_name,
                    location_found,
                    date_found,
                    claimed_by,
                    claim_date
                ) SELECT 
                    fi.item_name,
                    fi.location_found,
                    fi.date_found,
                    pc.student_id,
                    NOW()
                FROM found_items fi
                JOIN pending_claims pc ON fi.found_id = pc.found_id
                WHERE pc.claim_id = ?";

                $insert_claimed_stmt = $con->prepare($insert_claimed_sql);
                $insert_claimed_stmt->bind_param("i", $claim_id);
                
                if (!$insert_claimed_stmt->execute()) {
                    throw new Exception("Error moving item to claimed items");
                }

                // First delete the claim from pending_claims (child table)
                $delete_claim_sql = "DELETE FROM pending_claims WHERE claim_id = ?";
                $delete_claim_stmt = $con->prepare($delete_claim_sql);
                $delete_claim_stmt->bind_param("i", $claim_id);
                
                if (!$delete_claim_stmt->execute()) {
                    throw new Exception("Error removing claim from pending claims");
                }

                // Then delete from found_items (parent table)
                $delete_found_sql = "DELETE FROM found_items WHERE found_id = ?";
                $delete_found_stmt = $con->prepare($delete_found_sql);
                $delete_found_stmt->bind_param("i", $claim_data['found_id']);
                
                if (!$delete_found_stmt->execute()) {
                    throw new Exception("Error removing item from found items");
                }

                // Commit transaction
                $con->commit();

                echo json_encode([
                    'success' => true, 
                    'message' => 'Claim approved and item moved to claimed items',
                    'reload' => true
                ]);
            } else if ($action === 'deny') {
                // Delete the denied claim
                $delete_sql = "DELETE FROM pending_claims WHERE claim_id = ?";
                $delete_stmt = $con->prepare($delete_sql);
                $delete_stmt->bind_param("i", $claim_id);

                if ($delete_stmt->execute()) {
                    $con->commit();
                    echo json_encode([
                        'success' => true, 
                        'message' => 'Claim has been denied and removed',
                        'reload' => true
                    ]);
                } else {
                    throw new Exception("Error deleting claim");
                }
            } else {
                throw new Exception("Invalid action");
            }
        } catch (Exception $e) {
            // Rollback transaction on error
            $con->rollback();
            
            http_response_code(500);
            echo json_encode([
                'success' => false, 
                'message' => 'Error processing claim: ' . $e->getMessage()
            ]);
        }
    } else {
        http_response_code(400);
        echo json_encode([
            'success' => false, 
            'message' => 'Invalid request data'
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode([
        'success' => false, 
        'message' => 'Method not allowed'
    ]);
}
?> 