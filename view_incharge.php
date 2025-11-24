<?php
include 'conn.php';

// Fetch all Site Incharges ordered by latest
$sql = "SELECT * FROM site_incharge ORDER BY id DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Site Incharge List - Raiban Electrical Solutions</title>

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
    <h2 class="mb-0">Site Incharge List</h2>
    <div>
      <button onclick="window.print()" class="btn btn-secondary">
        <i class="bi bi-printer me-2"></i> Print
      </button>
    </div>
  </div>

  <div class="my-5 container-fluid">
    <!-- Search Bar -->
    <div class="mb-3">
      <input type="text" id="searchInput" class="form-control" placeholder="🔍 Search by Name or Employee Code...">
    </div>

    <!-- ✅ Success Message -->
    <?php if (isset($_GET['msg'])): ?>
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($_GET['msg']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>

    <table class="table table-bordered table-hover text-center align-middle w-100%"id="inchargeTable">
      <thead class="table-dark">
        <tr>
          <th>Sr. No</th>
          <th>Full Name</th>
          <th>Email</th>
          <th>Employee Code</th>
          <th>Phone</th>
          <th>Alt. Phone</th>
          <th>PAN</th>
          <th>Aadhar</th>
          <th>Address</th>
        </tr>
      </thead>
      <tbody>
        <?php 
        if ($result->num_rows > 0): 
          $sr = 1; 
          while($row = $result->fetch_assoc()): 
        ?>
          <tr>
            <td><?= $sr++ ?></td>
            <td><?= htmlspecialchars($row['name']) ?></td>
            <td><?= htmlspecialchars($row['email']) ?></td>
            <td><?= htmlspecialchars($row['employee_code']) ?></td>
            <td><?= htmlspecialchars($row['phone']) ?></td>
            <td><?= htmlspecialchars($row['alt_phone']) ?></td>
            <td><?= htmlspecialchars($row['pan_no']) ?></td>
            <td><?= htmlspecialchars($row['aadhar_no']) ?></td>
            <td><?= htmlspecialchars($row['address']) ?></td>
          </tr>
        <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="9" class="text-center">No Site Incharge Found</td></tr>
        <?php endif; ?>
      </tbody>
    </table>

    
  </div>

  <!-- Bootstrap & JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="script.js"></script>

  <!-- 🔍 Search Script (Name & Employee Code only) -->
  <script>
    document.getElementById("searchInput").addEventListener("keyup", function() {
      let filter = this.value.toLowerCase();
      let rows = document.querySelectorAll("#inchargeTable tbody tr");

      rows.forEach(row => {
        let name = row.cells[1].textContent.toLowerCase(); // Full Name
        let empCode = row.cells[3].textContent.toLowerCase(); // Employee Code

        row.style.display = (name.includes(filter) || empCode.includes(filter)) ? "" : "none";
      });
    });
  </script>
</body>
</html>
