<?php
include 'conn.php';
$sql = "SELECT * FROM contractors ORDER BY contractor_id DESC";
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
    <h2 class="mb-0">Contractors List</h2>
      <div>
        <button onclick="window.print()" class="btn btn-secondary">
          <i class="fas fa-print me-2"></i> Print
        </button>
      </div>
  </div>
  <div class="my-5">

  <!-- Search Bar -->
  <div class="mb-3">
    <input type="text" id="searchInput" class="form-control" placeholder="🔍 Search...">
  </div>

  <!-- Contractors Table -->
  <table class="table table-bordered table-striped" id="contractorsTable">
    <thead class="table-dark">
      <tr>
        <th>ID</th><th>Name</th><th>Email</th><th>Phone</th>
        <th>Alt Phone</th><th>PAN</th><th>Aadhar</th>
        <th>Bank</th><th>Branch</th><th>GST</th><th>IFSC</th><th>Address</th>
      </tr>
    </thead>
    <tbody>
      <?php if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) { ?>
          <tr>
            <td><?= $row['contractor_id'] ?></td>
            <td><?= $row['name'] ?></td>
            <td><?= $row['email'] ?></td>
            <td><?= $row['phone'] ?></td>
            <td><?= $row['alt_phone'] ?></td>
            <td><?= $row['pan_no'] ?></td>
            <td><?= $row['aadhar_no'] ?></td>
            <td><?= $row['bank_name'] ?></td>
            <td><?= $row['bank_branch'] ?></td>
            <td><?= $row['gst_no'] ?></td>
            <td><?= $row['ifsc_code'] ?></td>
            <td><?= $row['address'] ?></td>
          </tr>
      <?php } } else { ?>
          <tr><td colspan="12" class="text-center">No Contractors Found</td></tr>
      <?php } ?>
    </tbody>
  </table>

  <!-- Bootstrap & JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="script.js"></script>
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
