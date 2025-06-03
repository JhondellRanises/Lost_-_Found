<?php
session_start();
error_reporting(0); // Disable error reporting for production
include('db.php');

// Check if admin is logged in
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: Log_In.php');
    exit();
}

try {
    // Get lost items
    $lost_sql = "SELECT *, 'lost' AS type FROM lost_items ORDER BY lost_id DESC";
    $lost_result = mysqli_query($con, $lost_sql);
    if (!$lost_result) {
        throw new Exception("Error fetching lost items: " . mysqli_error($con));
    }

    // Get found items
    $found_sql = "SELECT *, 'found' AS type FROM found_items ORDER BY found_id DESC";
    $found_result = mysqli_query($con, $found_sql);
    if (!$found_result) {
        throw new Exception("Error fetching found items: " . mysqli_error($con));
    }

    // Count statistics
    $total_lost = mysqli_num_rows($lost_result);
    $total_found = mysqli_num_rows($found_result);
    $claimed_sql = "SELECT COUNT(*) as claimed_count FROM found_items WHERE claim_status = 'approved'";
    $claimed_result = mysqli_query($con, $claimed_sql);
    $claimed_count = mysqli_fetch_assoc($claimed_result)['claimed_count'];
} catch (Exception $e) {
    $error_message = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lost&Found - Admin Dashboard</title>
    <link rel="stylesheet" type="text/css" href="dashboard.css">
    <style>
        .stats-container {
            display: flex;
            justify-content: space-around;
            gap: 20px;
            padding: 20px;
            margin: 20px;
        }

        .stat-card {
            background: #ffffff;
            border-radius: 15px;
            padding: 25px;
            min-width: 200px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: 1px solid #e0e0e0;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        }

        .stat-card h3 {
            color: #333;
            font-size: 1.1rem;
            margin-bottom: 15px;
            font-weight: 600;
        }

        .stat-card p {
            color: #2196F3;
            font-size: 2rem;
            font-weight: bold;
            margin: 0;
        }

        /* Different colors for each stat card */
        .stat-card:nth-child(1) {
            border-left: 5px solid #FF5252; /* Red for lost items */
        }

        .stat-card:nth-child(1) p {
            color: #FF5252;
        }

        .stat-card:nth-child(2) {
            border-left: 5px solid #4CAF50; /* Green for found items */
        }

        .stat-card:nth-child(2) p {
            color: #4CAF50;
        }

        .stat-card:nth-child(3) {
            border-left: 5px solid #2196F3; /* Blue for claimed items */
        }

        .stat-card:nth-child(3) p {
            color: #2196F3;
        }
    </style>
</head>

<body>
    <?php if (isset($error_message)): ?>
        <div class="error-message">
            <?php echo htmlspecialchars($error_message); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['message'])): ?>
        <div class="success-message">
            <?php echo htmlspecialchars($_SESSION['message']); ?>
        </div>
        <?php unset($_SESSION['message']); // Clear the message after displaying ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="error-message">
            <?php echo htmlspecialchars($_SESSION['error']); ?>
        </div>
        <?php unset($_SESSION['error']); // Clear the error after displaying ?>
    <?php endif; ?>

    <!-- NAVBAR-->
    <nav class="navbar">
        <div class="navbar-content">
            <div class="navbar-title">ADMIN DASHBOARD</div>
            
            <div class="navbar-controls">
                <div class="search-container">
                    <input type="text" id="searchInput" placeholder="Search for items..." />
                </div>

                <div class="filter-group">
                    <select class="status">
                        <option value="all">All Items</option>
                        <option value="lost">Lost</option>
                        <option value="found">Found</option>
                    </select>
                </div>

                <div class="filter-group">
                    <select class="location">
                        <option value="all">All Locations</option>
                        <option value="annex">Annex</option>
                        <option value="rmmc main campus">RMMC Main Campus</option>
                        <option value="rmmc is">RMMC IS</option>
                    </select>
                </div>

                <div class="filter-group">
                    <select class="items-dropdown2">
                        <option value="all">All Categories</option>
                        <option value="cellphone">Cellphone</option>
                        <option value="bag">Bag</option>
                        <option value="wallet">Wallet</option>
                        <option value="id">ID</option>
                        <option value="helmet">Helmet</option>
                        <option value="accessories">Accessories</option>
                        <option value="clothes">Clothes</option>
                    </select>
                </div>
            </div>
        </div>
    </nav>

    <!-- SIDEBAR-->
    <aside class="sidebar">
        <ul>
            <li><a href="#" class="profile-link" data-section="profile"><span class="item">Profile</span></a></li>
            <li><a href="AdminDashboard.php" class="active"><span class="item">Dashboard</span></a></li>
            <?php if ($_SESSION['role'] === 'super_admin'): ?>
                <li><a href="AdminRegistration.php"><span class="item">Add Admin</span></a></li>
            <?php endif; ?>
            <li><a href="CreateStaff.php"><span class="item">Manage Staff</span></a></li>
            <li><a href="PendingClaims.php"><span class="item">Pending Claims</span></a></li>
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
                <h2>Admin Information</h2>
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

    <!-- Item Details Modal -->
    <div id="itemDetailsModal" class="item-details-modal">
        <div class="item-details-header">
            <h2>Item Details</h2>
            <button class="close-modal-btn">×</button>
        </div>
        <div class="item-details-content"></div>
    </div>

    <!-- Card Overlay -->
    <div id="cardOverlay" class="card-overlay"></div>

    <!-- MAIN-->
    <main class="main">
        <!-- Statistics Cards -->
        <div class="stats-container">
            <div class="stat-card">
                <h3>Total Lost Items</h3>
                <p><?php echo $total_lost; ?></p>
            </div>
            <div class="stat-card">
                <h3>Total Found Items</h3>
                <p><?php echo $total_found; ?></p>
            </div>
            <div class="stat-card">
                <h3>Items Claimed</h3>
                <p><?php echo $claimed_count; ?></p>
            </div>
        </div>

        <div class="items-section">
            <h2 class="section-title">Items List</h2>
            <div class="card-container">
                <!-- Lost items -->
                <?php while ($row = $lost_result->fetch_assoc()): ?>
                    <div class="item-card" 
                         data-status="lost" 
                         data-location="<?= htmlspecialchars($row['location_lost']) ?>" 
                         data-type="<?= htmlspecialchars($row['item_name']) ?>"
                         data-item-id="<?= htmlspecialchars($row['lost_id']) ?>"
                         data-date="<?= htmlspecialchars($row['date_lost'] ?? '') ?>"
                         data-time="<?= htmlspecialchars($row['estimated_time'] ?? '') ?>"
                         data-specific-location="<?= htmlspecialchars($row['specific_location'] ?? '') ?>"
                         data-additional-info="<?= htmlspecialchars($row['description'] ?? '') ?>">
                        <span class="item-status status-lost">Lost</span>

                        <div class="item-header">
                            <h3><?= htmlspecialchars($row['item_name']) ?></h3>
                        </div>

                        <div class="item-details">
                            <p><strong>Color:</strong> <?= htmlspecialchars($row['color']) ?></p>
                            <p><strong>Location:</strong> <?= htmlspecialchars($row['location_lost']) ?></p>
                            <p><strong>Description:</strong> <?= htmlspecialchars($row['description']) ?></p>
                            <p><strong>Reporter:</strong> <?= htmlspecialchars($row['reporter_type'] === 'student' ? 'Student ID: ' . $row['reporter_id'] : $row['reporter_id']) ?></p>
                        </div>
                    </div>
                <?php endwhile; ?>

                <!-- Found items -->
                <?php while ($row = $found_result->fetch_assoc()): ?>
                    <div class="item-card" 
                         data-status="found" 
                         data-location="<?= htmlspecialchars($row['location_found']) ?>" 
                         data-type="<?= htmlspecialchars($row['item_name']) ?>"
                         data-item-id="<?= htmlspecialchars($row['found_id']) ?>"
                         data-date="<?= htmlspecialchars($row['date_found'] ?? '') ?>"
                         data-time="<?= htmlspecialchars($row['time_found'] ?? '') ?>"
                         data-specific-location="<?= htmlspecialchars($row['specific_location'] ?? '') ?>"
                         data-additional-info="<?= htmlspecialchars($row['description'] ?? '') ?>">
                        <span class="item-status status-found">Found</span>

                        <div class="item-header">
                            <h3><?= htmlspecialchars($row['item_name']) ?></h3>
                        </div>

                        <div class="item-details">
                            <p><strong>Color:</strong> <?= htmlspecialchars($row['color']) ?></p>
                            <p><strong>Location:</strong> <?= htmlspecialchars($row['location_found']) ?></p>
                            <p><strong>Description:</strong> <?= htmlspecialchars($row['description']) ?></p>
                            <p><strong>Reporter:</strong> <?= htmlspecialchars($row['reporter_type'] === 'student' ? 'Student ID: ' . $row['reporter_id'] : $row['reporter_id']) ?></p>
                            <p><strong>Status:</strong> <?= htmlspecialchars($row['claim_status'] ?? 'Unclaimed') ?></p>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </main>

    <script src="Dashboard1.js"></script>
    <script src="itemDetails.js"></script>
    <script src="search.js"></script>
</body>
</html>