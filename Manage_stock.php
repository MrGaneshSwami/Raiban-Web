<?php
include 'conn.php';

// Fetch all products
$sql = "SELECT * FROM products";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Stocks - Raiban Electrical Solutions</title>

  <!-- Bootstrap & Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

  <!-- Common Stylesheet -->
  <link rel="stylesheet" href="style.css"/>
  <style>
    .stock-card {
      max-width: 95%;
      margin: auto;
    }
  </style>
</head>
<body class="manage-stocks-page">

  <!-- Navbar -->
  <div id="navbar-placeholder"></div>

  <!-- Page Header -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
    <!-- Back button stays on the left -->
    <a href="store.html" class="btn btn-dark">
      <i class="bi bi-arrow-left"></i> Back
    </a>

    <h2 class="page-title text-center flex-grow-1 m-0">
      <i class="bi bi-box-seam me-2"></i> Manage Stocks
    </h2>

  </div>

  <!-- Card Wrapper -->
  <div class="stock-card container my-4 p-4 shadow rounded bg-white">

    <!-- Search Bar -->
    <input type="text" id="searchInput" class="form-control mb-3" placeholder="🔍 Search by Product Code or Name...">

    <!-- ✅ Success / Error Alerts -->
    <?php if (isset($_GET['msg'])): ?>
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($_GET['msg']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>

    <!-- Table -->
    <div class="table-responsive">
      <table id="stockTable" class="table table-bordered table-striped align-middle">
        <thead class="table-dark">
          <tr>
            <th>Sr. No</th>
            <th>Product Code</th>
            <th>Product Name</th>
            <th>Unit</th>
            <th>Quantity</th>
            <th>Price</th>
            <th>Total Value</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php 
          if ($result->num_rows > 0): 
            $sr_no = 1;
            while($row = $result->fetch_assoc()): ?>
              <tr>
                <td><?php echo $sr_no++; ?></td>
                <td><?php echo $row['product_code']; ?></td>
                <td><?php echo $row['product_name']; ?></td>
                <td><?php echo $row['unit']; ?></td>
                <td><?php echo $row['quantity']; ?></td>
                <td>₹<?php echo number_format($row['price'], 2); ?></td>
                <td>₹<?php echo number_format($row['quantity'] * $row['price'], 2); ?></td>
                <td>
                  <a href="edit_product.php?id=<?php echo $row['product_id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                  
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr><td colspan="8" class="text-center">No products found</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

  </div>

  <!-- Bootstrap + Navbar Loader + Search Script -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Load navbar.html into placeholder
    fetch("navbar.html")
      .then(res => res.text())
      .then(data => {
        document.getElementById("navbar-placeholder").innerHTML = data;
      });

    // 🔍 Live Search
    document.getElementById("searchInput").addEventListener("keyup", function() {
      let filter = this.value.toLowerCase();
      let rows = document.querySelectorAll("#stockTable tbody tr");

      rows.forEach(row => {
        let code = row.cells[1].textContent.toLowerCase();
        let name = row.cells[2].textContent.toLowerCase();
        
        if (code.includes(filter) || name.includes(filter)) {
          row.style.display = "";
        } else {
          row.style.display = "none";
        }
      });
    });
  </script>
</body>
</html>
