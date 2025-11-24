<?php
include 'conn.php';

// Optional default values for filters
$startDate = isset($_GET['startDate']) ? $_GET['startDate'] : '';
$endDate = isset($_GET['endDate']) ? $_GET['endDate'] : '';

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
    @media print {
      .no-print { display: none; }
      body { margin: 10mm; }
      @page { size: A4 landscape; margin: 10mm; }
      table { width: 100%; border-collapse: collapse; }
      th, td { border: 1px solid #000; padding: 6px; font-size: 12px; }
      .grand-total { text-align: right; font-weight: bold; margin-top: 10px; font-size: 14px; }
    }
    tfoot td { font-weight: bold; }
    tfoot td:first-child { text-align: right; }
    tfoot td:last-child { text-align: right; }
  </style>
</head>
<body class="manage-stocks-page">

<!-- Header Bar -->
<div id="header-placeholder"></div>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
  <a href="generate_report.html" class="btn btn-dark">
    <i class="bi bi-arrow-left me-2"></i> Back
  </a>
  <h2 class="page-title text-center m-0"><i class="bi bi-box-seam me-2"></i> Manage Stocks Report</h2>
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
      <input type="text" id="searchInput" class="form-control" placeholder="🔍 Product Name, Code...">
    </div>
  </div>

  <!-- Stocks Table -->
  <table class="table table-bordered table-striped text-center align-middle" id="stockTable">
    <thead class="table-dark">
      <tr>
        <th>Sr. No</th>
        <th>Date</th> 
        <th>Product Code</th>
        <th>Product Name</th>
        <th>Unit</th>
        <th>Quantity</th>
        <th>Price</th>
        <th>Total Value</th>
      </tr>
    </thead>
    <tbody>
      <?php 
      if ($result->num_rows > 0): 
        $sr_no = 1;
        while($row = $result->fetch_assoc()): ?>
          <tr>
            <td><?= $sr_no++; ?></td>
            <td><?= isset($row['created_at']) ? date("d-m-Y", strtotime($row['created_at'])) : '' ?></td>
            <td><?= $row['product_code'] ?></td>
            <td><?= $row['product_name'] ?></td>
            <td><?= $row['unit'] ?></td>
            <td><?= $row['quantity'] ?></td>
            <td>₹<?= number_format($row['price'], 2) ?></td>
            <td>₹<?= number_format($row['quantity'] * $row['price'], 2) ?></td>
          </tr>
        <?php endwhile; ?>
      <?php else: ?>
        <tr><td colspan="8" class="text-center">No products found</td></tr>
      <?php endif; ?>
    </tbody>
    <tfoot>
      <tr class="table-secondary fw-bold">
        <td colspan="7" style="text-align:right;">Grand Total:</td>
        <td id="grandTotal" style="text-align:center;">₹0.00</td>
      </tr>
    </tfoot>
  </table>

  <!-- Generate Report Button -->
  <div class="text-center mt-4 no-print">
    <button class="btn btn-primary" onclick="printReport()">
      <i class="bi bi-printer me-2"></i>Generate Report
    </button>
  </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="script.js"></script>
<script>
// Calculate Grand Total
function calculateGrandTotal() {
  let rows = document.querySelectorAll("#stockTable tbody tr");
  let total = 0;
  let anyVisible = false;
  rows.forEach(row => {
    if (row.style.display !== "none") {
      let lastCell = row.cells[row.cells.length - 1];
      if (lastCell && !isNaN(parseFloat(lastCell.innerText.replace(/[^0-9.-]+/g, "")))) {
        total += parseFloat(lastCell.innerText.replace(/[^0-9.-]+/g, ""));
      }
      anyVisible = true;
    }
  });

  document.getElementById("grandTotal").innerText = "₹" + total.toFixed(2);

  // Show "No products found" if nothing visible
  let tbody = document.querySelector("#stockTable tbody");
  let noResRow = document.getElementById("noResultsRow");
  if (!anyVisible) {
    if (!noResRow) {
      let tr = document.createElement("tr");
      tr.id = "noResultsRow";
      tr.innerHTML = `<td colspan="8" class="text-center">No products found</td>`;
      tbody.appendChild(tr);
    }
  } else {
    if (noResRow) noResRow.remove();
  }
}

// Live search filter
document.getElementById("searchInput").addEventListener("keyup", function() {
  let value = this.value.toLowerCase();
  let rows = document.querySelectorAll("#stockTable tbody tr");
  rows.forEach(row => {
    row.style.display = row.textContent.toLowerCase().includes(value) ? "" : "none";
  });
  calculateGrandTotal();
});

// Date filter
document.getElementById("startDate").addEventListener("change", filterByDate);
document.getElementById("endDate").addEventListener("change", filterByDate);

function filterByDate() {
  let startDate = document.getElementById("startDate").value;
  let endDate = document.getElementById("endDate").value;
  let rows = document.querySelectorAll("#stockTable tbody tr");

  rows.forEach(row => {
    let dateCell = row.cells[1].innerText; // Date column
    if (dateCell) {
      let rowDate = new Date(dateCell.split("-").reverse().join("-"));
      let show = true;

      if (startDate && rowDate < new Date(startDate)) show = false;
      if (endDate && rowDate > new Date(endDate)) show = false;

      row.style.display = show ? "" : "none";
    }
  });
  calculateGrandTotal();
}

// Call on page load
calculateGrandTotal();

// Print Report
function printReport() {
  let startDateVal = document.getElementById("startDate").value;
  let endDateVal = document.getElementById("endDate").value;

  let rows = document.querySelectorAll("#stockTable tbody tr");
  let grandTotal = 0;
  let tableRowsHtml = "";

  rows.forEach((row) => {
    if (row.style.display !== "none") {
      let cells = Array.from(row.cells).map((cell) => `<td>${cell.innerText}</td>`).join("");
      tableRowsHtml += `<tr>${cells}</tr>`;
      grandTotal += parseFloat(row.cells[7].innerText.replace(/[^0-9.-]+/g, "")) || 0;
    }
  });

  let today = new Date().toLocaleDateString();

  let newWin = window.open("", "_blank");
  newWin.document.write(`
<html>
<head>
  <title>Manage Stocks Report</title>
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
  <h3>Manage Stocks Report</h3>
  <h3>Report Date: ${today}</h3>
  ${startDateVal && endDateVal ? `<h3>From: ${startDateVal} To: ${endDateVal}</h3>` : ""}

  <table>
    <thead>
      <tr>
        <th>Sr. No</th>
        <th>Date</th>
        <th>Product Code</th>
        <th>Product Name</th>
        <th>Unit</th>
        <th>Quantity</th>
        <th>Price</th>
        <th>Total Value</th>
      </tr>
    </thead>
    <tbody>
      ${tableRowsHtml}
    </tbody>
    <tfoot>
      <tr>
        <td colspan="7" style="text-align:right; font-weight:bold;">Grand Total:</td>
        <td style="text-align:center; font-weight:bold;">₹${grandTotal.toFixed(2)}</td>
      </tr>
    </tfoot>
  </table>

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
