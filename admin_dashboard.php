<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../logout.php");
    exit;
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard - Raiban Electrical Solutions</title>

  <!-- Bootstrap & Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <!-- Custom CSS -->
  <link rel="stylesheet" href="style.css"> 
</head>
<body>

  <!-- Sidebar -->
  <div class="sidebar" id="sidebar">
    <i class="fas fa-bars menu-toggle" id="menuToggle"></i>
    <ul>
      <li><a href="admin_dashboard.php" class="active"><i class="fas fa-home"></i><span>Home</span></a></li>
      <li><a href="store.html"><i class="fas fa-store"></i><span>Store</span></a></li>
    </ul>
    <ul class="bottom-menu">
      <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a></li> 
    </ul>
  </div>
    
  <!-- Header -->
  <div class="header">
    <div class="brand">
      <img src="RLogo.png" alt="Company Logo">
      <div class="brand-text">
        <h2>Raiban Electrical <span><b>Solutions</b></span></h2>
        <p>⚡<b>Powering Homes and Hearts...!</b></p>
      </div>
    </div>
  </div>

  <!-- Main Content -->
  <div class="main-content">
    <div class="container">
      <div class="row g-3">

        <!-- Store Card -->
        <div class="col-md-3 col-sm-6">
          <a href="store.html" class="dashboard-card">
            <i class="fas fa-store"></i>
            <h5>Store</h5>
            <p>Monitor Store Inventories.</p>
          </a>
        </div>

        <!--- Add User Access Card --->
          <div class="col-md-3 col-sm-6">
  <div class="dashboard-card text-center">
    <i class="fas fa-user-shield fa-2x mb-2"></i>
    <h5>Grant Access</h5>
    <p>Manage User Access to Store Software.</p>
    <div class="d-flex justify-content-center gap-2 mt-3">
      <a href="user_access.php" class="btn btn-primary btn-sm">Open Form</a>
      <a href="view_users.php" class="btn btn-outline-primary btn-sm mt-0"> View Users</a>
    </div>
  </div>
</div>

    </div>
  </div>

  <!-- JS -->
  <script src="script.js"></script> 
</body>
</html>
