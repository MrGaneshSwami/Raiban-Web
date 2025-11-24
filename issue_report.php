<?php
date_default_timezone_set('Asia/Kolkata');
include '../conn.php';

// Get start and end date from GET parameters
$startDate = $_GET['start'] ?? null;
$endDate = $_GET['end'] ?? null;

// Fetch stock issues joined with items
$sql = "SELECT s.issue_id, s.store_incharge, s.site_name, s.site_incharge, s.site_supervisor,
               s.contractor, s.circle, s.division, s.subdivision, s.section_name, s.location,
                s.issued_at,
               i.material_code, i.material_name, i.unit, i.qty, i.rate, i.total
        FROM stock_issue s
        LEFT JOIN stock_issue_items i ON s.issue_id = i.issue_id";

if($startDate && $endDate){
    $sql .= " WHERE DATE(s.issued_at) BETWEEN '$startDate' AND '$endDate'";
}

$sql .= " ORDER BY s.issue_id DESC";

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
  <title>Issued Stock Report - Raiban Electrical Solutions</title>

  <!-- Bootstrap & Icons -->
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
    }
  </style>
</head>
<body>

<!-- Header Bar -->
<div id="header-placeholder"></div>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
  <a href="generate_report.html" class="btn btn-dark">
    <i class="bi bi-arrow-left me-2"></i> Back
  </a>
  <h2 class="page-title text-center m-0"><i class="fas fa-box-open"></i> Issued Stock Report</h2>
  <div></div>
</div>

<div class="container-fluid my-4">

  <!-- Filters Row -->
  <div class="row g-3 mb-3 align-items-end">
    <!-- Start Date -->
    <div class="col-md-2">
      <label for="startDate" class="form-label">📅Start Date</label>
      <div class="input-group">
        <input type="date" id="startDate" class="form-control" value="<?= $startDate ?>">
      </div>
    </div>

    <!-- End Date -->
    <div class="col-md-2">
      <label for="endDate" class="form-label">📅End Date</label>
      <div class="input-group">
        <input type="date" id="endDate" class="form-control" value="<?= $endDate ?>">
      </div>
    </div>

    <!-- Search -->
    <div class="col-md-8">
      <label for="searchInput" class="form-label">🔍Search</label>
      <input type="text" id="searchInput" class="form-control" placeholder="🔍 Site, Material...">
    </div>
  </div>

  <!-- Issued Stocks Table -->
  <table class="table table-bordered table-striped text-center align-middle" id="issueTable">
    <thead class="table-dark">
      <tr>
        <th>Sr. No</th>
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
        <th>Material Code</th>
        <th>Material Name</th>
        <th>Unit</th>
        <th>Qty</th>
        <th>Rate</th>
        <th>Total</th>
      </tr>
    </thead>
    <tbody>
      <?php 
      if ($result && $result->num_rows > 0) {
        $sr = 1;
        while ($row = $result->fetch_assoc()) { ?>
          <tr>
            <td><?= $sr++ ?></td>
            <td><?= date('d-m-Y', strtotime($row['issued_at'])) ?></td>
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
            <td><?= $row['qty'] ?></td>
            <td>₹<?= $row['rate'] ?></td>
            <td>₹<?= $row['total'] ?></td>
          </tr>
      <?php } } else { ?>
          <tr><td colspan="19" class="text-center">No Issued Stocks Found</td></tr>
      <?php } ?>
    </tbody>
 <tfoot>
  <tr class="table-secondary fw-bold">
    <td colspan="17" class="text-end">Grand Total:</td>
    <td id="grandTotal" class="text-center">₹0.00</td>
  </tr>
</tfoot>

  </table>
</div>

<!-- Generate Report Button -->
<div class="text-center mt-4">
  <button class="btn btn-primary" onclick="printReport()">
    <i class="bi bi-printer me-2"></i>Generate Report
  </button>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="script.js"></script>
<script>
// Live search filter
document.getElementById("searchInput").addEventListener("keyup", function() {
  let value = this.value.toLowerCase();
  let rows = document.querySelectorAll("#issueTable tbody tr");
  rows.forEach(row => {
    row.style.display = row.textContent.toLowerCase().includes(value) ? "" : "none";
  });
});

// Date filter reload
document.getElementById("startDate").addEventListener("change", reloadWithDates);
document.getElementById("endDate").addEventListener("change", reloadWithDates);

function reloadWithDates(){
    let start = document.getElementById("startDate").value;
    let end = document.getElementById("endDate").value;
    if(start && end){
        window.location.href = `issue_report.php?start=${start}&end=${end}`;
    }
}

// Calculate Grand Total
function calculateGrandTotal() {
  let rows = document.querySelectorAll("#issueTable tbody tr");
  let total = 0;
  rows.forEach(row => {
    let lastCell = row.cells[row.cells.length - 1]; // last column = Total
    if (lastCell && !isNaN(parseFloat(lastCell.innerText.replace(/[^0-9.-]+/g, "")))) {
      total += parseFloat(lastCell.innerText.replace(/[^0-9.-]+/g, ""));
    }
  });
  document.getElementById("grandTotal").innerText = "₹" + total.toFixed(2);
}

// Call on page load
calculateGrandTotal();

// Print Report
function printReport() {
  let tableHtml = document.getElementById("issueTable").outerHTML;
  let startDate = document.getElementById("startDate").value;
  let endDate = document.getElementById("endDate").value;
  let today = new Date().toLocaleDateString();

  let newWin = window.open("", "_blank");
  newWin.document.write(`
<html>
  <head>
    <title>Issued Stock Report</title>
    <style>
      body { font-family: Arial, sans-serif; margin: 20px; }
      h1 { text-align: center; margin: 0; color: orange; }
      h2 { text-align: center; margin: 5px 0; font-size: 16px; color: black; }
      h3 { text-align: center; margin: 5px 0; font-size: 14px; }
      table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 12px; }
      th, td { border: 1px solid black; padding: 6px; text-align: center; }
      th { background-color: #f2f2f2; }
      tfoot td { font-weight: bold; }
      tfoot td:not(#grandTotal) { text-align: right; }
      tfoot td#grandTotal { text-align: right; }
      footer { text-align: center; margin-top: 20px; font-size: 12px; }
    </style>
  </head>
  <body>
    <h1>Raiban Electrical Solutions</h1>
    <h2>Powering Homes & Hearts</h2>
    <h3>Issued Stocks Report</h3>
    <h3>Report Date: ${today}</h3>
    ${startDate && endDate ? `<h3>From: ${startDate} To: ${endDate}</h3>` : ""}
    ${tableHtml}
    <footer>
      Office Address: P-4/6 OLD MIDC, Near GST Bhavan, Satara-415004 <br>
      Mob: 9420230909 | GST No: 27ATXPP3195R1Z9 <br>
      E-mail: raibanelectricalsoln@gmail.com
    </footer>
  </body>
</html>
  `);
  newWin.document.close();
  newWin.print();
}
</script>
</body>
</html>

