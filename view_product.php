<?php
include 'conn.php';

// Fetch all products ordered by latest
$sql = "SELECT * FROM products ORDER BY product_id DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Add Contractor - Raiban Electrical Solutions</title>

  <!-- Bootstrap & Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

  <!-- Common Stylesheet -->
  <link rel="stylesheet" href="style.css"/>
</head>
<body>
  <!-- Navbar -->
  <div id="header-placeholder"></div>
  
  <!-- Header Bar -->
  <div class="header-bar d-flex justify-content-between align-items-center">
    <div class="d-flex align-items-center">
      <button class="btn btn-secondary btn-sm me-3" onclick="history.back()">
        <i class="bi bi-arrow-left"></i> Back
      </button>
    </div>
    <h2 class="mb-0">Products List</h2>
      <div>
        <button onclick="window.print()" class="btn btn-secondary">
          <i class="fas fa-print me-2"></i> Print
        </button>
      </div>
  </div>
  <div class="my-5">

  <!-- Search Bar -->
  <div class="mb-3">
    <input type="text" id="searchInput" class="form-control" placeholder="🔍 Search by Product Code or Name...">
  </div>

    <!-- ✅ Success Message (if redirected with msg) -->
    <?php if (isset($_GET['msg'])): ?>
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($_GET['msg']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>

    <table class="table table-bordered table-striped" id="productsTable">
      <thead class="table-dark">
        <tr>
          <th>Sr. No</th>
          <th>Product Code</th>
          <th>Product Name</th>
          <th>Unit</th>
          <th>Quantity</th>
          <th>Price (₹)</th>
          <th>Total Value (₹)</th>
        </tr>
      </thead>
      <tbody>
        <?php 
        if ($result->num_rows > 0): 
          $sr = 1; // Initialize serial number
          while($row = $result->fetch_assoc()): 
        ?>
            <tr>
              <td><?= $sr++ ?></td>
              <td><?= $row['product_code'] ?></td>
              <td><?= $row['product_name'] ?></td>
              <td><?= $row['unit'] ?></td>
              <td><?= $row['quantity'] ?></td>
              <td><?= number_format($row['price'], 2) ?></td>
              <td><?= number_format($row['quantity'] * $row['price'], 2) ?></td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="7" class="text-center">No Products Found</td></tr>
        <?php endif; ?>
      </tbody>
    </table>

    <div class="col-12 text-center">
      <a href="product.html" class="btn btn-success">+ Add New Product</a>
    </div>

  <!-- Bootstrap & JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="script.js"></script>

  <!-- 🔍 Search Script (Product Code & Name only) -->
  <script>
    document.getElementById("searchInput").addEventListener("keyup", function() {
      let filter = this.value.toLowerCase();
      let rows = document.querySelectorAll("#productsTable tbody tr");

      rows.forEach(row => {
        let code = row.cells[1].textContent.toLowerCase(); // Product Code column
        let name = row.cells[2].textContent.toLowerCase(); // Product Name column

        row.style.display = (code.includes(filter) || name.includes(filter)) ? "" : "none";
      });
    });
  </script>
</body>
</html>
