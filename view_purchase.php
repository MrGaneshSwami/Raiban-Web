<?php
include '../conn.php';

// Fetch purchases joined with items
$sql = "SELECT p.purchase_id, p.store_incharge, p.supplier_name, p.supplier_gst, 
               p.contact_no, p.payment_type, p.final_cost,p.created_at,
               i.material_name, i.material_code, i.quantity, i.unit, i.location, i.rate, i.gst, i.total
        FROM purchases p
        LEFT JOIN purchase_items i ON p.purchase_id = i.purchase_id
        ORDER BY p.purchase_id DESC";
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
  <title>Purchases - Raiban Electrical Solutions</title>

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
   <h2 class="text-center m-0"><i class="bi bi-cart4"></i>Purchased Stock</h2>
    <div>
      
    </div>
  </div>

  <div class="cointainer-fluid">
     <!-- Search & Filter -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
        <input type="text" id="searchInput" class="form-control search-bar" placeholder="🔍 Search by supplier, Material...">
        
        <select id="statusFilter" class="form-select w-auto">
            <option value="">All Status</option>
            <option value="Other">Other</option>
            <option value="Paid">Paid</option>
            <option value="Unpaid">Unpaid</option>
        </select>
    </div>

    <!-- Purchases Table -->

    <table class="table table-bordered table-striped text-center align-middle" id=purchaseTable>
      <thead class="table-dark">
        <tr>
          <th>Sr No</th>
          <th>Date</th>
          <th>Store Incharge</th>
          <th>Supplier</th>
          <th>GST</th>
          <th>Contact</th>
          <th>Final Cost</th>
          <th>Material Name</th>
          <th>Code</th>
          <th>Qty</th>
          <th>Unit</th>
          <th>Location</th>
          <th>Rate</th>
          <th>GST</th>
          <th>Total</th>
          <th>Payment Type</th>
          <th>Action</th>

        </tr>
      </thead>
      <tbody>
        <?php if ($result && $result->num_rows > 0) {
          $sr=1;
          while($row = $result->fetch_assoc()) { ?>
            <tr>
              <td><?= $sr++ ?></td>
              <th class="date-cell"><?= date('d-m-Y', strtotime($row['created_at'])) ?></th>
              <td><?= $row['store_incharge'] ?></td>
              <td><?= $row['supplier_name'] ?></td>
              <td><?= $row['supplier_gst'] ?></td>
              <td><?= $row['contact_no'] ?></td>
              <td>₹<?= $row['final_cost'] ?></td>
              <td><?= $row['material_name'] ?></td>
              <td><?= $row['material_code'] ?></td>
              <td><?= $row['quantity'] ?></td>
              <td><?= $row['unit'] ?></td>
              <td><?= $row['location'] ?></td>
              <td>₹<?= $row['rate'] ?></td>
              <td>₹<?= $row['gst'] ?></td>
              <td>₹<?= $row['total'] ?></td>
              <td>
                  <?php if ($row['payment_type'] === 'Paid') { ?>
                    <span class="badge bg-success">Paid</span>
                  <?php } elseif ($row['payment_type'] === 'Unpaid') { ?>
                    <span class="badge bg-danger">Unpaid</span>
                  <?php } else { ?>
                    <span class="badge bg-warning text-dark">Other</span>
                  <?php } ?>
                </td>

              <td>
                <a href="edit_purchase.php?purchase_id=<?= $row['purchase_id'] ?>" 
                   class="btn btn-sm btn-primary">
                  <i class="bi bi-pencil-square"></i> Edit
                </a>
              </td>
            </tr>
        <?php } } else { ?>
            <tr><td colspan="16" class="text-center">No Purchases Found</td></tr>
        <?php } ?>
      </tbody>
    </table>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="script.js"></script>
  <script>
    const searchInput = document.getElementById("searchInput");
    const statusFilter = document.getElementById("statusFilter");
    const rows = document.querySelectorAll("#purchaseTable tbody tr");

    function filterTable() {
      const search = searchInput.value.toLowerCase();
      const status = statusFilter.value;

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
