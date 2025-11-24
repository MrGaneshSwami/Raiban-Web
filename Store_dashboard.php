<!-- store dashboard.php -->
<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Store Incharge') {
    header("Location: ../logout.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard - Raiban Electrical Solutions</title>

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
      <li><a href="Store_dashboard.php" class="active"><i class="fas fa-home"></i><span>Home</span></a></li>
      <li><a href="purchase.html"><i class="fas fa-shopping-cart"></i><span>Purchase</span></a></li>
      <li><a href="issue.html"><i class="fas fa-share-square"></i><span>Issue</span></a></li>
      <li><a href="return.html"><i class="fas fa-undo"></i><span>Return</span></a></li>
      <li><a href="view_product.php"><i class="fas fa-boxes"></i><span>Manage Stocks</span></a></li>
      <li><a href="Forms.html"><i class="fas fa-user-edit"></i><span>Forms</span></a></li>
      <li><a href="generate_report.html"><i class="fas fa-file-alt"></i><span>Generate Report</span></a></li>
      <li><a href="unitconverter.html"><i class="fas fa-calculator"></i><span>Unit Converter</span></a></li>
      <li><a href="demand_note.html"><i class="fas fa-file-invoice"></i><span>Demand Note</span></a></li>
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
        <h2>Raiban Electrical <span><b>Solutions</span></h2>
        <p>⚡ Powering Homes and Hearts...!</p>
      </div>
    </div>
  </div>

  <!-- Main Content -->
  <div class="main-content">
    <div class="container">
      <div class="row g-3">

        <div class="col-md-3 col-sm-6">
          <a href="purchase.html" class="dashboard-card">
            <i class="fas fa-shopping-cart"></i>
            <h5>Purchase</h5>
            <p>Record and manage purchases from suppliers.</p>
          </a>
        </div>

        <div class="col-md-3 col-sm-6">
          <a href="issue.html" class="dashboard-card">
            <i class="fas fa-share-square"></i>
            <h5>Issue</h5>
            <p>Issue stock to contractors.</p>
          </a>
        </div>

        <div class="col-md-3 col-sm-6">
          <a href="return.html" class="dashboard-card">
            <i class="fas fa-undo"></i>
            <h5>Return</h5>
            <p>Manage stock returned from contractors.</p>
          </a>
        </div>

        <div class="col-md-3 col-sm-6">
          <a href="manage_stocks_report.php" class="dashboard-card">
            <i class="fas fa-boxes"></i>
            <h5>Manage Stocks</h5>
            <p>View and update current stock levels.</p>
          </a>
        </div>

        <div class="col-md-3 col-sm-6">
          <a href="Forms.html" class="dashboard-card">
            <i class="fas fa-user-edit"></i>
            <h5>Forms</h5>
            <p>Add and manage engineer and contractor details.</p>
          </a>
        </div>

        <div class="col-md-3 col-sm-6">
          <a href="generate_report.html" class="dashboard-card">
            <i class="fas fa-file-alt"></i>
            <h5>Generate Report</h5>
            <p>Create stock, purchase & issue reports.</p>
          </a>
        </div>

        <div class="col-md-3 col-sm-6">
          <a href="unitconverter.html" class="dashboard-card">
            <i class="fas fa-calculator"></i>
            <h5>Unit Converter</h5>
            <p>Convert between different electrical units.</p>
          </a>
        </div>

        <div class="col-md-3 col-sm-6">
          <a href="demand_note.html" class="dashboard-card">
            <i class="fas fa-file-invoice"></i>
            <h5>Demand Note</h5>
            <p>Create and manage demand notes.</p>
          </a>
        </div>

      </div>
    </div>
  </div>

  <!-- JS -->
  <script src="script.js"></script> 
</body>
</html>
