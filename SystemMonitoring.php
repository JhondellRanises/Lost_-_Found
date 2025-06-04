<?php
session_start();
error_reporting(0);
include('db.php');

// Check if staff is logged in
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'staff') {
    header('Location: Log_In.php');
    exit();
}

// Function to get total counts
function getCount($con, $table) {
    $result = mysqli_query($con, "SELECT COUNT(*) as count FROM $table");
    return ($result) ? mysqli_fetch_assoc($result)['count'] : 0;
}

// Get system statistics
$total_users = getCount($con, 'users');
$total_staff = getCount($con, 'staff');
$total_lost_items = getCount($con, 'lost_items');
$total_found_items = getCount($con, 'found_items');

// Get recent login activity (last 20 entries)
$login_activity_sql = "
    SELECT 'Student' as user_type, StudentID as identifier, last_login, fname, lname 
    FROM users 
    WHERE last_login IS NOT NULL
    UNION ALL
    SELECT 'Staff' as user_type, email as identifier, last_login, fname, lname 
    FROM staff 
    WHERE last_login IS NOT NULL
    ORDER BY last_login DESC 
    LIMIT 20";

$login_activity = mysqli_query($con, $login_activity_sql);
if (!$login_activity) {
    error_log("Login activity query failed: " . mysqli_error($con));
    echo "<div class='error-message'>Error loading login activity data. Please try again later.</div>";
}

// Debug information for development (remove in production)
if ($login_activity && mysqli_num_rows($login_activity) == 0) {
    error_log("No login activity found. SQL: " . $login_activity_sql);
}

// Get recent item reports (last 10 entries for each type)
$recent_lost_sql = "
    SELECT 'Lost' as type, item_name, date_lost as date, reporter_id, reporter_type
    FROM lost_items 
    ORDER BY lost_id DESC 
    LIMIT 10";

$recent_found_sql = "
    SELECT 'Found' as type, item_name, date_found as date, reporter_id, reporter_type
    FROM found_items 
    ORDER BY found_id DESC 
    LIMIT 10";

$recent_lost = mysqli_query($con, $recent_lost_sql);
$recent_found = mysqli_query($con, $recent_found_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Monitoring - Lost&Found</title>
    <link rel="stylesheet" type="text/css" href="dashboard.css">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background-color: #f5f6fa;
            overflow-x: hidden;
            padding-top: 90px; 
        }

        .navbar {
            background: linear-gradient(135deg, #1a237e, #0d47a1);
            color: white;
            padding: 1.25rem 2rem;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }

        .navbar-content {
            display: flex;
            align-items: center;
            gap: 2rem;
            max-width: 1400px;
            margin: 0 auto;
            flex-wrap: wrap;
        }

        .navbar-title {
            font-size: 1.5rem;
            font-weight: 600;
            height: 50px;
            margin-top: 10px;
            min-width: 200px;
            letter-spacing: 0.5px;
        }

        .main {
            margin-left: 280px; 
            padding: 20px;
            min-height: calc(100vh - 90px);
            width: calc(100% - 280px);
            position: relative;
            overflow-x: hidden;
            background-color: #f5f6fa;
        }

        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            height: 100%;
            width: 280px;
            background: white;
            padding-top: 5rem;
            box-shadow: 2px 0 8px rgba(0,0,0,0.1);
        }

        .sidebar ul {
            list-style: none;
            padding: 1.5rem;
        }

        .sidebar li {
            margin-bottom: 0.75rem;
        }

        .sidebar a {
            display: flex;
            align-items: center;
            padding: 1rem 1.25rem;
            color: #1a237e;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .sidebar a:hover {
            background-color: rgba(26, 35, 126, 0.08);
            transform: translateX(4px);
        }

        .sidebar a.active {
            background-color: #1a237e;
            color: white;
            box-shadow: 0 2px 4px rgba(26, 35, 126, 0.2);
        }

        .item {
            font-size: 1rem;
            letter-spacing: 0.3px;
        }

        .content-wrapper {
            max-width: 100%;
            margin: 0 auto;
            padding: 0 15px;
        }

        .stats-container {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 24px;
            margin-bottom: 30px;
            width: 100%;
        }

        .stat-card {
            background: white;
            padding: 24px;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            text-align: center;
            transition: transform 0.2s;
            border: 1px solid #eee;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.1);
        }

        .stat-card h3 {
            margin: 0;
            color: #555;
            font-size: 1em;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-card .number {
            font-size: 2.5em;
            font-weight: bold;
            color: #2980b9;
            margin: 15px 0;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
        }

        .activity-section {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            margin-bottom: 30px;
            border: 1px solid #eee;
            width: 100%;
        }

        .activity-section h2 {
            margin: 0 0 20px 0;
            color: #333;
            font-size: 1.2em;
            font-weight: 600;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .table-container {
            width: 100%;
            overflow-x: auto;
            margin-top: 15px;
            -webkit-overflow-scrolling: touch;
        }

        .activity-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .activity-table th,
        .activity-table td {
            padding: 16px;
            text-align: left;
            border-bottom: 1px solid #eee;
            font-size: 0.95em;
        }

        .activity-table th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #444;
            text-transform: uppercase;
            font-size: 0.85em;
            letter-spacing: 0.5px;
            position: sticky;
            top: 0;
            z-index: 1;
            white-space: nowrap;
        }

        .activity-table td {
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .activity-table tr:last-child td {
            border-bottom: none;
        }

        .activity-table tr:hover {
            background-color: #f8f9fa;
        }

        .badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: 500;
            display: inline-block;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .badge-student {
            background-color: #e3f2fd;
            color: #1976d2;
        }

        .badge-staff {
            background-color: #e8f5e9;
            color: #388e3c;
        }

        .badge-lost {
            background-color: #ffebee;
            color: #c62828;
        }

        .badge-found {
            background-color: #e8f5e9;
            color: #388e3c;
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #666;
        }

        .empty-state p {
            margin: 10px 0;
            font-size: 0.95em;
        }

        .identifier {
            font-family: monospace;
            color: #666;
        }

        .timestamp {
            color: #666;
            font-size: 0.9em;
        }

        @media (max-width: 1400px) {
            .stats-container {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .main {
                margin-left: 0;
                width: 100%;
                padding: 15px;
            }

            .content-wrapper {
                padding: 0;
            }

            .stats-container {
                grid-template-columns: 1fr;
            }

            .activity-section {
                padding: 15px;
                margin-bottom: 20px;
            }

            .activity-table th,
            .activity-table td {
                padding: 12px;
            }

            .refresh-btn {
                position: static;
                margin-bottom: 20px;
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <!-- NAVBAR -->
    <nav class="navbar">
        <div class="navbar-content">
            <div class="navbar-title">SYSTEM MONITORING</div>
        </div>
    </nav>

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <ul>
            <li><a href="#" class="profile-link" data-section="profile"><span class="item">Profile</span></a></li>
            <li><a href="StaffDashboard.php"><span class="item">Dashboard</span></a></li>
            <li><a href="reports/ReportLost.php"><span class="item">Report Lost</span></a></li>
            <li><a href="reports/ReportFound.php"><span class="item">Report Found</span></a></li>
            <li><a href="PendingClaims.php"><span class="item">Pending Claims</span></a></li>
            <li><a href="MyReports.php"><span class="item">My Reports</span></a></li>
            <li><a href="SystemMonitoring.php" class="active"><span class="item">System Monitoring</span></a></li>
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
                        <label>Name:</label>
                        <span><?php echo htmlspecialchars($_SESSION['fname']); ?></span>
                    </div>
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

    <!-- Main Content -->
    <main class="main">
        <div class="content-wrapper">
            
            <!-- System Statistics -->
            <div class="stats-container">
                <div class="stat-card">
                    <h3>Total Users</h3>
                    <div class="number"><?php echo $total_users; ?></div>
                </div>
                <div class="stat-card">
                    <h3>Total Staff</h3>
                    <div class="number"><?php echo $total_staff; ?></div>
                </div>
                <div class="stat-card">
                    <h3>Lost Items</h3>
                    <div class="number"><?php echo $total_lost_items; ?></div>
                </div>
                <div class="stat-card">
                    <h3>Found Items</h3>
                    <div class="number"><?php echo $total_found_items; ?></div>
                </div>
            </div>

            <!-- Recent Login Activity -->
            <div class="activity-section">
                <h2>Recent Login Activity</h2>
                <div class="table-container">
                    <table class="activity-table">
                        <thead>
                            <tr>
                                <th>User Type</th>
                                <th>Name</th>
                                <th>Identifier</th>
                                <th>Last Login</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($login_activity && mysqli_num_rows($login_activity) > 0): ?>
                                <?php while ($row = mysqli_fetch_assoc($login_activity)): ?>
                                    <tr>
                                        <td>
                                            <span class="badge badge-<?php echo strtolower($row['user_type']); ?>">
                                                <?php echo htmlspecialchars($row['user_type']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($row['fname'] . ' ' . $row['lname']); ?></td>
                                        <td class="identifier"><?php echo htmlspecialchars($row['identifier']); ?></td>
                                        <td class="timestamp"><?php echo htmlspecialchars($row['last_login']); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="empty-state">
                                        <p>No login activity recorded yet.</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Recent Item Reports -->
            <div class="activity-section">
                <h2>Recent Item Reports</h2>
                <div class="table-container">
                    <table class="activity-table">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Item Name</th>
                                <th>Date</th>
                                <th>Reporter</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $has_reports = false;
                            if ($recent_lost && mysqli_num_rows($recent_lost) > 0): 
                                $has_reports = true;
                                while ($row = mysqli_fetch_assoc($recent_lost)): 
                            ?>
                                <tr>
                                    <td><span class="badge badge-lost">Lost</span></td>
                                    <td><?php echo htmlspecialchars($row['item_name']); ?></td>
                                    <td class="timestamp"><?php echo htmlspecialchars($row['date']); ?></td>
                                    <td class="identifier"><?php echo htmlspecialchars($row['reporter_type'] . ': ' . $row['reporter_id']); ?></td>
                                </tr>
                            <?php 
                                endwhile;
                            endif;
                            
                            if ($recent_found && mysqli_num_rows($recent_found) > 0): 
                                $has_reports = true;
                                while ($row = mysqli_fetch_assoc($recent_found)): 
                            ?>
                                <tr>
                                    <td><span class="badge badge-found">Found</span></td>
                                    <td><?php echo htmlspecialchars($row['item_name']); ?></td>
                                    <td class="timestamp"><?php echo htmlspecialchars($row['date']); ?></td>
                                    <td class="identifier"><?php echo htmlspecialchars($row['reporter_type'] . ': ' . $row['reporter_id']); ?></td>
                                </tr>
                            <?php 
                                endwhile;
                            endif;
                            
                            if (!$has_reports): 
                            ?>
                                <tr>
                                    <td colspan="4" class="empty-state">
                                        <p>No item reports available.</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <script src="Dashboard1.js"></script>
</body>
</html> 