<?php
date_default_timezone_set('Asia/Kolkata');
include 'conn.php';

// Get start and end date from GET parameters
$startDate = $_GET['start'] ?? null;
$endDate   = $_GET['end'] ?? null;

// Fetch returned items with details
$sql = "SELECT rs.id AS return_id, rs.store_incharge, rs.site_name,
               rs.site_incharge, rs.site_supervisor, rs.contractor,
               rs.circle, rs.division, rs.subdivision, rs.section_name,
               rs.location, rs.created_at,
               ri.id AS item_id, ri.material_code, ri.material_name, ri.unit, ri.quantity,
               ri.rate, ri.total
        FROM return_stock rs
        INNER JOIN return_items ri ON rs.id = ri.return_id";

if ($startDate && $endDate) {
    $sql .= " WHERE DATE(rs.created_at) BETWEEN '$startDate' AND '$endDate'";
}

$sql .= " ORDER BY rs.id DESC";

$result = $conn->query($sql);

if (!$result) {
    die('Database query failed: ' . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Returned Stock - Raiban Electrical Solutions</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<link href="style.css" rel="stylesheet">
<style>
@media print {
    .no-print { display: none; }
    body { margin: 10mm; }
    @page { size: A4 landscape; margin: 10mm; }
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #000; padding: 6px; font-size: 12px; }
    tfoot td { text-align: right !important; font-weight: bold; }
    td.date-cell { white-space: nowrap; }
}
</style>
</head>
<body>
<div id="navbar-placeholder"></div>
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
  <a href="store.html" class="btn btn-dark no-print">
    <i class="bi bi-arrow-left me-2"></i> Back
  </a>
  <h2 class="page-title text-center m-0"><i class="fas fa-undo"></i> Returned Stocks</h2>
  <div></div>
</div>

<div class="container-fluid my-4">
  <!-- Search -->
  <div class="row g-3 mb-3 align-items-end">
    <div class="col-md-12">
      <label for="searchInput" class="form-label">🔍 Search</label>
      <input type="text" id="searchInput" class="form-control" placeholder="Search Site, Material...">
    </div>
  </div>

  <!-- Returned Items Table -->
  <table class="table table-bordered table-striped text-center align-middle" id="returnTable">
    <thead class="table-dark">
      <tr>
        <th>Sr. No</th>
        <th>Date</th>
        <th>Return ID</th>
        <th>Store Incharge</th>
        <th>Site Name</th>
        <th>Site Incharge</th>
        <th>Site Supervisor</th>
        <th>Contractor</th>
        <th>Circle</th>
        <th>Division</th>
        <th>Subdivision</th>
        <th>Section</th>
        <th>Location</th>
        <th>Material Code</th>
        <th>Material Name</th>
        <th>Unit</th>
        <th>Qty</th>
        <th>Rate</th>
        <th>Total</th>
        <th class="no-print">Action</th>
      </tr>
    </thead>
    <tbody>
<?php 
if ($result && $result->num_rows > 0) {
    $sr = 1;
    while ($row = $result->fetch_assoc()) { ?>
      <tr>
        <td><?= $sr++ ?></td>
        <td class="date-cell"><?= date('d-m-Y', strtotime($row['created_at'])) ?></td>
        <td><?= $row['return_id'] ?></td>
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
        <td><?= $row['material_code'] ?></td>
        <td><?= $row['material_name'] ?></td>
        <td><?= $row['unit'] ?></td>
        <td><?= $row['quantity'] ?></td>
        <td><?= number_format($row['rate'], 2) ?></td>
        <td>₹<?= number_format($row['total'], 2) ?></td>
        <td class="no-print">
          <a href="edit_return.php?id=<?= $row['return_id'] ?>" class="btn btn-sm btn-primary">
            <i class="bi bi-pencil-square"></i> Edit
          </a>
        </td>
      </tr>
<?php } 
} ?>
    </tbody>
    <tfoot>
      <tr id="noMatchRow" style="display:none;">
        <td colspan="21" class="text-center">No matching records found</td>
      </tr>
    </tfoot>
  </table>
</div>

<!--Script-->
<script src="script.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// --- Search filter
document.getElementById("searchInput").addEventListener("keyup", function() {
    let value = this.value.toLowerCase();
    document.querySelectorAll("#returnTable tbody tr").forEach(row => {
        if(row.id === "noMatchRow") return;
        row.style.display = row.textContent.toLowerCase().includes(value) ? "" : "none";
    });
    updateNoMatchRow();
});

// --- No Match Row Show/Hide
function updateNoMatchRow() {
    let rows = document.querySelectorAll("#returnTable tbody tr");
    let visibleRows = 0;
    rows.forEach(row => {
        if(row.id !== "noMatchRow" && row.style.display !== "none") visibleRows++;
    });
    document.getElementById("noMatchRow").style.display = (visibleRows === 0) ? "" : "none";
}

updateNoMatchRow();
</script>
</body>
</html>

