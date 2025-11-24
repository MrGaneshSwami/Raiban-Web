<?php
include '../conn.php';

// Fetch stock issues joined with items
$sql = "SELECT s.issue_id, s.store_incharge, s.site_name, s.site_incharge, s.site_supervisor,
               s.contractor, s.circle, s.division, s.subdivision, s.section_name, s.location,
               s.final_cost,s.issued_at,
               i.material_code, i.material_name, i.unit, i.qty, i.rate, i.total
        FROM stock_issue s
        LEFT JOIN stock_issue_items i ON s.issue_id = i.issue_id
        ORDER BY s.issue_id DESC";

$result = $conn->query($sql);

if (!$result) {
    die("Database query failed: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Issued Stocks - Raiban Electrical Solutions</title>

  <!-- Bootstrap & Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

  <link rel="stylesheet" href="style.css"/>
</head>
<body>
 
<!-- Navbar -->
  <div id="navbar-placeholder"></div>
  
  <!-- Header Bar -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
      <!-- Left: Back Button -->
      <a href="store.html" class="btn btn-dark">
        <i class="fas fa-arrow-left me-2"></i> Back
      </a>
     <h2 class="text-center m-0"><i class="fas fa-box-open"></i> Issued Stock</h2>
    <div>
      
    </div>
  </div>

  <div class="container-fluid">
    <!-- Search & Filter -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
        <input type="text" id="searchInput" class="form-control search-bar" placeholder="🔍 Search by Site Name, Contractor, Material...">
    </div>

    <!-- Issued Stocks Table -->
    <table class="table table-bordered table-striped text-center align-middle" id="issueTable">
      <thead class="table-dark">
        <tr>
          <th>Sr No</th>
          <th>Date</th>
          <th>Store Incharge</th>
          <th>Site Name</th>
          <th>Site Incharge</th>
          <th>Supervisor</th>
          <th>Contractor</th>
          <th>Circle</th>
          <th>Division</th>
          <th>Sub Division</th>
          <th>Section</th>
          <th>Location</th>
          <th>Final Cost</th>
          <th>Material Code</th>
          <th>Material Name</th>
          <th>Unit</th>
          <th>Qty</th>
          <th>Rate</th>
          <th>Total</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($result && $result->num_rows > 0) {
          $sr=1;
          while($row = $result->fetch_assoc()) { ?>
            <tr>
              <td><?=$sr++?></td>
              <td class="date-cell"><?= date('d-m-Y', strtotime($row['issued_at'])) ?></td>
              <td><?= $row['store_incharge'] ?></td>
              <td><?= $row['site_name'] ?></td>
              <td><?= $row['site_incharge'] ?></td>
              <td><?= $row['site_supervisor'] ?></td>
              <td><?= $row['contractor'] ?></td>
              <td><?= $row['circle'] ?></td>
              <td><?= $row['division'] ?></td>
              <td><?= $row['subdivision'] ?></td>
              <td><?= $row['section_name'] ?></td>
              <td><?= $row['location'] ?></td>
              <td>₹<?= $row['final_cost'] ?></td>
              <td><?= $row['material_code'] ?></td>
              <td><?= $row['material_name'] ?></td>
              <td><?= $row['unit'] ?></td>
              <td><?= $row['qty'] ?></td>
              <td>₹<?= $row['rate'] ?></td>
              <td>₹<?= $row['total'] ?></td>
              <td>
                <a href="edit_issue.php?issue_id=<?= $row['issue_id'] ?>" class="btn btn-sm btn-primary">
                  <i class="bi bi-pencil-square"></i> Edit
                </a>
              </td>
            </tr>
        <?php } } else { ?>
            <tr><td colspan="19" class="text-center">No Issued Stocks Found</td></tr>
        <?php } ?>
      </tbody>
    </table>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="script.js"></script>
  <script>
    const searchInput = document.getElementById("searchInput");
    const rows = document.querySelectorAll("#issueTable tbody tr");

    function filterTable() {
      const search = searchInput.value.toLowerCase();

      rows.forEach(row => {
        const text = row.innerText.toLowerCase();
        const rowStatus = row.querySelector("td:nth-last-child(2)").innerText;

        const matchesSearch = text.includes(search);
        const matchesStatus = !status || rowStatus === status;

        row.style.display = matchesSearch && matchesStatus ? "" : "none";
      });
    }

    searchInput.addEventListener("keyup", filterTable);
    statusFilter.addEventListener("change", filterTable);
  </script>
</body>
</html>
