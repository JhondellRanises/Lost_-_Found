<?php
session_start();
include('db.php');

// Check if staff is logged in
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'staff') {
    header('Location: Log_In.php');
    exit();
}

// Initialize variables
$claims_result = null;
$error_message = null;

try {
    // Get pending claims for items reported by the current staff
    $staff_email = $_SESSION['email'];
    
    // First check if the connection is established
    if (!$con) {
        throw new Exception("Database connection failed");
    }

    // Debug output
    echo "<!-- Debug: Staff email = " . htmlspecialchars($staff_email) . " -->\n";

    // First, let's check if there are any claims at all
    $check_claims = $con->query("SELECT COUNT(*) as total FROM pending_claims");
    $total_claims = $check_claims->fetch_assoc()['total'];
    echo "<!-- Debug: Total claims in database = " . $total_claims . " -->\n";

    // Let's check the structure of the users table
    $users_columns = $con->query("SHOW COLUMNS FROM users");
    echo "<!-- Debug: Users table columns: ";
    while ($col = $users_columns->fetch_assoc()) {
        echo $col['Field'] . ", ";
    }
    echo " -->\n";

    // Modified query to match actual database structure
    $claims_sql = "SELECT 
                    pc.*,
                    fi.item_name,
                    fi.description,
                    fi.color,
                    fi.location_found,
                    fi.date_found,
                    fi.reporter_id,
                    fi.reporter_type,
                    u.fname,
                    u.lname,
                    u.StudentID
                   FROM pending_claims pc 
                   JOIN found_items fi ON pc.found_id = fi.found_id 
                   JOIN users u ON pc.student_id = u.StudentID 
                   WHERE fi.reporter_id = ? 
                   AND fi.reporter_type = 'staff'
                   ORDER BY pc.claim_date DESC";
    
    $stmt = $con->prepare($claims_sql);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $con->error);
    }

    $stmt->bind_param("s", $staff_email);
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }

    $claims_result = $stmt->get_result();
    if ($claims_result === false) {
        throw new Exception("Failed to get result: " . $stmt->error);
    }

    // Debug output
    echo "<!-- Debug: Number of claims for this staff = " . $claims_result->num_rows . " -->\n";

} catch (Exception $e) {
    $error_message = "Error: " . $e->getMessage();
    echo "<!-- Debug: Error occurred = " . htmlspecialchars($error_message) . " -->\n";
}

// Add debugging information to the page
if (isset($_SESSION['email'])) {
    echo "<!-- Debug: Staff is logged in with email: " . htmlspecialchars($_SESSION['email']) . " -->\n";
} else {
    echo "<!-- Debug: No staff email in session -->\n";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending Claims - Staff Dashboard</title>
    <link rel="stylesheet" type="text/css" href="dashboard.css">
    <style>
        .claims-container {
            padding: 20px;
        }
        .claim-card {
            background: white;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .claim-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        .claim-actions {
            display: flex;
            gap: 10px;
        }
        .approve-btn, .deny-btn {
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
        }
        .approve-btn {
            background-color: #4CAF50;
            color: white;
        }
        .deny-btn {
            background-color: #f44336;
            color: white;
        }
        .claim-details {
            margin-top: 10px;
        }
        .claim-status {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.9em;
            font-weight: bold;
            display: inline-block;
        }
        .status-pending {
            background-color: #ffd700;
            color: #000;
        }
        .status-approved {
            background-color: #4CAF50;
            color: white;
        }
        .status-denied {
            background-color: #f44336;
            color: white;
        }
        .error-message {
            background-color: #ffebee;
            color: #c62828;
            padding: 10px;
            margin: 10px;
            border-radius: 4px;
            border: 1px solid #ef9a9a;
        }
        .item-info {
            background-color: #f5f5f5;
            padding: 10px;
            border-radius: 4px;
            margin-top: 10px;
        }
        .student-info {
            background-color: #e3f2fd;
            padding: 10px;
            border-radius: 4px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <?php if (isset($error_message)): ?>
        <div class="error-message">
            <?php echo htmlspecialchars($error_message); ?>
        </div>
    <?php endif; ?>

    <!-- NAVBAR-->
    <nav class="navbar">
        <div class="navbar-content">
            <div class="navbar-title">PENDING CLAIMS</div>
        </div>
    </nav>

    <!-- SIDEBAR-->
    <aside class="sidebar">
        <ul>
            <li><a href="#" class="profile-link" data-section="profile"><span class="item">Profile</span></a></li>
            <li><a href="StaffDashboard.php"><span class="item">Dashboard</span></a></li>
            <li><a href="reports/ReportLost.php"><span class="item">Report Lost</span></a></li>
            <li><a href="reports/ReportFound.php"><span class="item">Report Found</span></a></li>
            <li><a href="PendingClaims.php" class="active"><span class="item">Pending Claims</span></a></li>
            <li><a href="MyReports.php"><span class="item">My Reports</span></a></li>
        </ul>
    </aside>

    <!-- Profile Panel -->
    <div id="profilePanel" class="side-panel">
        <button id="closeProfilePanel" class="close-btn">×</button>
        <div class="panel-content">
            <div class="profile-info">
                <div class="profile-image-container">
                    <img src="Profile Icon.png" alt="Profile Icon">
                </div>
                <h2>Staff Information</h2>
                <?php if (isset($_SESSION['email'])): ?>
                    <div class="info-item">
                        <label>Email:</label>
                        <span><?php echo htmlspecialchars($_SESSION['email']); ?></span>
                    </div>
                    <div class="info-item">
                        <label>Role:</label>
                        <span><?php echo ucfirst(htmlspecialchars($_SESSION['role'])); ?></span>
                    </div>
                    <div class="info-item logout-container">
                        <a href="Log_In.php" class="logout-btn">Logout</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- MAIN-->
    <main class="main">
        <div class="claims-container">
            <h2>Claims for Your Found Items</h2>
            <?php if ($claims_result && $claims_result->num_rows > 0): ?>
                <?php while ($claim = $claims_result->fetch_assoc()): ?>
                    <div class="claim-card" data-claim-id="<?= htmlspecialchars($claim['claim_id']) ?>">
                        <div class="claim-header">
                            <h3><?= htmlspecialchars($claim['item_name']) ?></h3>
                            <div class="claim-actions">
                                <?php if ($claim['status'] === 'pending'): ?>
                                    <button class="approve-btn" onclick="handleClaim(<?= $claim['claim_id'] ?>, 'approve')">Approve</button>
                                    <button class="deny-btn" onclick="handleClaim(<?= $claim['claim_id'] ?>, 'deny')">Deny</button>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="item-info">
                            <p><strong>Item Details:</strong></p>
                            <p><strong>Color:</strong> <?= htmlspecialchars($claim['color']) ?></p>
                            <p><strong>Location Found:</strong> <?= htmlspecialchars($claim['location_found']) ?></p>
                            <p><strong>Date Found:</strong> <?= htmlspecialchars($claim['date_found']) ?></p>
                            <p><strong>Description:</strong> <?= htmlspecialchars($claim['description']) ?></p>
                        </div>

                        <div class="student-info">
                            <p><strong>Student Details:</strong></p>
                            <p><strong>Name:</strong> <?= htmlspecialchars($claim['fname'] . ' ' . $claim['lname']) ?></p>
                            <p><strong>Student ID:</strong> <?= htmlspecialchars($claim['student_id']) ?></p>
                        </div>

                        <div class="claim-status">
                            Status: <span class="status-<?= htmlspecialchars($claim['status']) ?>"><?= ucfirst(htmlspecialchars($claim['status'])) ?></span>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No pending claims found for your items.</p>
            <?php endif; ?>
        </div>
    </main>

    <script src="Dashboard1.js"></script>
    <script>
        function handleClaim(claimId, action) {
            if (!confirm(`Are you sure you want to ${action} this claim?`)) {
                return;
            }

            fetch('StaffHandleClaim.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    claim_id: claimId,
                    action: action
                })
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
                if (data.success && data.reload) {
                    window.location.reload();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while processing the claim. Please try again.');
            });
        }

        // Auto-hide messages after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const messages = document.querySelectorAll('.success-message, .error-message');
            messages.forEach(message => {
                setTimeout(() => {
                    message.style.opacity = '0';
                    setTimeout(() => message.remove(), 300);
                }, 5000);
            });
        });
    </script>
</body>
</html> 