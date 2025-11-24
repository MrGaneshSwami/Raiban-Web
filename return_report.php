<?php
date_default_timezone_set('Asia/Kolkata');
include 'conn.php';

// Get start and end date from GET parameters
$startDate = $_GET['start'] ?? null;
$endDate   = $_GET['end'] ?? null;

// Fetch only normal returned items
$sql = "SELECT rs.id AS return_id, rs.store_incharge, rs.site_name, rs.created_at,
               ri.material_code, ri.material_name, ri.unit, ri.quantity, ri.rate, ri.total
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
  <title>Returned Stock Report - Raiban Electrical Solutions</title>

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
      tfoot td { text-align: right !important; font-weight: bold; }
      td.date-cell { white-space: nowrap; }
    }
  </style>
</head>
<body>
<div id="header-placeholder"></div>
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
  <a href="generate_report.html" class="btn btn-dark no-print">
    <i class="bi bi-arrow-left me-2"></i> Back
  </a>
  <h2 class="page-title text-center m-0"><i class="bi bi-box-seam"></i> Returned Stock Report</h2>
  <div></div>
</div>

<div class="container-fluid my-4">

  <!-- Filters Row -->
  <div class="row g-3 mb-3 align-items-end">
    <div class="col-md-2">
      <label for="startDate" class="form-label">📅Start Date</label>
      <input type="date" id="startDate" class="form-control" value="<?= $startDate ?>">
    </div>
    <div class="col-md-2">
      <label for="endDate" class="form-label">📅End Date</label>
      <input type="date" id="endDate" class="form-control" value="<?= $endDate ?>">
    </div>
    <div class="col-md-8">
      <label for="searchInput" class="form-label">🔍Search</label>
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
        <td class="date-cell"><?= date('d-m-Y', strtotime($row['created_at'])) ?></td>
        <td><?= $row['return_id'] ?></td>
        <td><?= $row['store_incharge'] ?></td>
        <td><?= $row['site_name'] ?></td>
        <td><?= $row['material_code'] ?></td>
        <td><?= $row['material_name'] ?></td>
        <td><?= $row['unit'] ?></td>
        <td><?= $row['quantity'] ?></td>
        <td>₹<?= number_format($row['rate'], 2) ?></td>
        <td>₹<?= number_format($row['total'], 2) ?></td>
      </tr>
<?php } 
} ?>
      <tr id="noMatchRow" class="text-danger text-center">
        <td colspan="11">No matching records found</td>
      </tr>
    </tbody>
    <tfoot>
      <tr class="table-secondary fw-bold" id="grandTotalRow">
        <td colspan="10" class="text-end">Grand Total:</td>
        <td id="grandTotal" class="text-center">₹0.00</td>
      </tr>
    </tfoot>
  </table>
</div>

<div class="text-center mt-4 no-print">
  <button class="btn btn-primary" onclick="printReport()">
    <i class="bi bi-printer me-2"></i>Generate Report
  </button>
</div>

<!-- Bootstrap JS & Custom Script -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="script.js"></script>
<script>
document.getElementById("searchInput").addEventListener("keyup", function() {
    let value = this.value.toLowerCase();
    document.querySelectorAll("#returnTable tbody tr").forEach(row => {
        if(row.id === "noMatchRow") return;
        row.style.display = row.textContent.toLowerCase().includes(value) ? "" : "none";
    });
    updateNoMatchRow();
});

// Date filter reload
document.getElementById("startDate").addEventListener("change", reloadWithDates);
document.getElementById("endDate").addEventListener("change", reloadWithDates);

function reloadWithDates(){
    let start = document.getElementById("startDate").value;
    let end = document.getElementById("endDate").value;
    if(start && end){
        window.location.href = `return_report.php?start=${start}&end=${end}`;
    }
}

// Calculate Grand Total
function calculateGrandTotal() {
    let rows = document.querySelectorAll("#returnTable tbody tr");
    let total = 0;
    rows.forEach(row => {
        if(row.style.display === "none" || row.id === "noMatchRow") return;
        let cell = row.cells[row.cells.length - 1];
        if (cell && !isNaN(parseFloat(cell.innerText.replace(/[^0-9.-]+/g, "")))) {
            total += parseFloat(cell.innerText.replace(/[^0-9.-]+/g, ""));
        }
    });
    document.getElementById("grandTotal").innerText = "₹" + total.toFixed(2);
}

// Show/Hide "No Match" and Grand Total
function updateNoMatchRow() {
    let rows = document.querySelectorAll("#returnTable tbody tr");
    let visibleRows = 0;
    rows.forEach(row => {
        if(row.id !== "noMatchRow" && row.style.display !== "none") visibleRows++;
    });

    document.getElementById("noMatchRow").style.display = (visibleRows === 0) ? "" : "none";
    document.getElementById("grandTotalRow").style.display = (visibleRows === 0) ? "none" : "";
}

calculateGrandTotal();
updateNoMatchRow();

// Print Report
function printReport() {
  let tableHtml = document.getElementById("returnTable").outerHTML;
  let startDate = document.getElementById("startDate").value;
  let endDate = document.getElementById("endDate").value;
  let today = new Date().toLocaleDateString();

  let newWin = window.open("", "_blank");
  newWin.document.write(`
<html>
  <head>
    <title>Returned Stock Report</title>
    <style>
      body { font-family: Arial, sans-serif; margin: 20px; }
      h1 { text-align: center; margin: 0; color: orange; }
      h2 { text-align: center; margin: 5px 0; font-size: 16px; color: black; }
      h3 { text-align: center; margin: 5px 0; font-size: 14px; }
      table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 12px; }
      th, td { border: 1px solid black; padding: 6px; text-align: center; }
      th { background-color: #f2f2f2; }
      tfoot td { font-weight: bold; text-align: right !important; }
      footer { text-align: center; margin-top: 20px; font-size: 12px; }
    </style>
  </head>
  <body>
    <h1>Raiban Electrical Solutions</h1>
    <h2>Powering Homes & Hearts</h2>
    <h3>Returned Items Report</h3>
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
