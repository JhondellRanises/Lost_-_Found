<?php
session_start();
error_reporting(0); // Disable error reporting for production
include('db.php');

// Check if staff is logged in
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'staff') {
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
} catch (Exception $e) {
    $error_message = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Lost&Found - Staff Dashboard</title>
  <link rel="stylesheet" type="text/css" href="dashboard.css">
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
      <div class="navbar-title">DASHBOARD</div>
      
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
      <li><a href="StaffDashboard.php" class="active"><span class="item">Dashboard</span></a></li>
      <li><a href="reports/ReportLost.php"><span class="item">Report Lost</span></a></li>
      <li><a href="reports/ReportFound.php"><span class="item">Report Found</span></a></li>
      <li><a href="PendingClaims.php"><span class="item">Pending Claims</span></a></li>
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
                <h2>Student Information</h2>
                <?php if (isset($_SESSION['studentID'])): ?>
                    <div class="info-item">
                        <label>Full Name:</label>
                        <span><?php echo htmlspecialchars($_SESSION['fname'] . ' ' . $_SESSION['lname']); ?></span>
                    </div>
                    <div class="info-item">
                        <label>Student ID:</label>
                        <span><?php echo htmlspecialchars($_SESSION['studentID']); ?></span>
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

            <?php
              $canEdit = false;
              if (isset($_SESSION['role']) && $_SESSION['role'] === 'staff') {
                  if ($row['reporter_type'] === 'staff' && $row['reporter_id'] === $_SESSION['email']) {
                      $canEdit = true;
                  }
              }
            ?>

            <?php if ($canEdit): ?>
              <button class="card-menu">⋮</button>
              <div class="card-dropdown hidden">
                <button class="update-btn">Update</button>
                <button class="delete-btn">Delete</button>
              </div>
            <?php endif; ?>

            <div class="item-header">
              <h3><?= htmlspecialchars($row['item_name']) ?></h3>
            </div>

            <div class="item-details">
              <p><strong>Color:</strong> <?= htmlspecialchars($row['color']) ?></p>
              <p><strong>Location:</strong> <?= htmlspecialchars($row['location_lost']) ?></p>
              <p><strong>Description:</strong> <?= htmlspecialchars($row['description']) ?></p>
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

            <?php
              $canEdit = false;
              if (isset($_SESSION['role']) && $_SESSION['role'] === 'staff') {
                  if ($row['reporter_type'] === 'staff' && $row['reporter_id'] === $_SESSION['email']) {
                      $canEdit = true;
                  }
              }
            ?>

            <?php if ($canEdit): ?>
              <button class="card-menu">⋮</button>
              <div class="card-dropdown hidden">
                <button class="update-btn">Update</button>
                <button class="delete-btn">Delete</button>
              </div>
            <?php endif; ?>

            <div class="item-header">
              <h3><?= htmlspecialchars($row['item_name']) ?></h3>
            </div>

            <div class="item-details">
              <p><strong>Color:</strong> <?= htmlspecialchars($row['color']) ?></p>
              <p><strong>Location:</strong> <?= htmlspecialchars($row['location_found']) ?></p>
              <p><strong>Description:</strong> <?= htmlspecialchars($row['description']) ?></p>
            </div>
          </div>
        <?php endwhile; ?>
      </div>
    </div>
  </main>

  <!-- Add update form container -->
  <div id="updateFormContainer"></div>

  <script src="Dashboard1.js"></script>
  <script src="itemDetails.js"></script>
</body>
</html>