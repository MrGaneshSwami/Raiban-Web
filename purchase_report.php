<?php
include 'conn.php';

// Fetch purchases joined with items
$sql = "SELECT p.purchase_id, p.store_incharge, p.supplier_name, p.supplier_gst, 
               p.contact_no, p.payment_type, p.created_at,
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

<!-- Custom CSS -->
<link href="style.css" rel="stylesheet">
<style>

  @media print {
  table {
    width: 100%;
    table-layout: auto;
    border-collapse: collapse;
  }

  th, td {
    word-wrap: break-word;
    white-space: nowrap;
    padding: 6px;
    font-size: 12px; /* Reduce font size if needed */
  }

  body {
    margin: 10mm; /* Adjust margins so right side doesn’t cut */
  }

  @page {
    size: A4 landscape; /* Or portrait depending on table */
    margin: 10mm;
  }
}

tfoot td { font-weight: bold; }
tfoot td { text-align: right; }


  #purchaseTable { width: 100% !important; }
  #purchaseTable th, #purchaseTable td { white-space: nowrap; }
</style>
</head>
<body>

<div id="header-placeholder"></div>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
  <a href="generate_report.html" class="btn btn-dark">
    <i class="bi bi-arrow-left me-2"></i> Back
  </a>
  <h2 class="page-title text-center m-0"><i class="bi bi-cart4"></i> Purchased Stock Report</h2>
  <div></div>
</div>

<div class="container-fluid my-4">

  <!-- Filters Row -->
  <div class="row g-3 mb-3 align-items-end">
    <!-- Start Date -->
    <div class="col-md-2">
      <label for="startDate" class="form-label">📅Start Date</label>
      <div class="input-group">
        <input type="date" id="startDate" class="form-control">
      </div>
    </div>

    <!-- End Date -->
    <div class="col-md-2">
      <label for="endDate" class="form-label">📅End Date</label>
      <div class="input-group">
        <input type="date" id="endDate" class="form-control">
      </div>
    </div>

    <!-- Supplier Search -->
    <div class="col-md-8">
      <label for="searchInput" class="form-label">🔍Search Supplier</label>
      <input type="text" id="searchInput" class="form-control" placeholder="🔍 Search Supplier, Material...">
    </div>
  </div>

  <!-- Purchases Table -->
  <div class="table-responsive">
    <table class="table table-bordered table-striped text-center align-middle" id="purchaseTable">
      <thead class="table-dark">
        <tr>
          <th>Sr.No</th>
          <th>Date</th>
          <th>Store Incharge</th>
          <th>Supplier</th>
          <th>GST</th>
          <th>Contact</th>
          <th>Payment Type</th>
          <th>Material Name</th>
          <th>Code</th>
          <th>Qty</th>
          <th>Unit</th>
          <th>Location</th>
          <th>Rate</th>
          <th>GST</th>
          <th>Total</th>
        </tr>
      </thead>
      <tbody>
        <?php 
        if ($result && $result->num_rows > 0) {
          $srNo = 1;
          while($row = $result->fetch_assoc()) { ?>
            <tr>
              <td><?= $srNo++ ?></td>
              <td class="date-cell"><?= date('d-m-Y', strtotime($row['created_at'])) ?></td>
              <td><?= $row['store_incharge'] ?></td>
              <td><?= $row['supplier_name'] ?></td>
              <td><?= $row['supplier_gst'] ?></td>
              <td><?= $row['contact_no'] ?></td>
              <td><?= $row['payment_type'] ?></td>
              <td><?= $row['material_name'] ?></td>
              <td><?= $row['material_code'] ?></td>
              <td><?= $row['quantity'] ?></td>
              <td><?= $row['unit'] ?></td>
              <td><?= $row['location'] ?></td>
              <td>₹<?= $row['rate'] ?></td>
              <td>₹<?= $row['gst'] ?></td>
              <td>₹<?= $row['total'] ?></td>
            </tr>
        <?php } } else { ?>
            <tr><td colspan="15" class="text-center">No Purchases Found</td></tr>
        <?php } ?>
      </tbody>
     
      <tfoot>
  <tr class="table-secondary fw-bold">
    <!-- First 14 columns -->
    <td colspan="14" class="text-end">Grand Total:</td>
    <!-- 15th column -->
    <td id="grandTotal">₹0.00</td>
  </tr>
</tfoot>
    </table>
  </div>

  <!-- Generate Report Button -->
  <div class="text-center mt-4">
    <button class="btn btn-primary" onclick="printReport()"><i class="bi bi-printer me-2"></i>Generate Report</button>
  </div>

</div>

<script src="script.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Filter Function
const filterTable = () => {
  let searchText = document.getElementById("searchInput").value.toLowerCase();
  let startDate = document.getElementById("startDate").value;
  let endDate = document.getElementById("endDate").value;
  let rows = document.querySelectorAll("#purchaseTable tbody tr");
  let grandTotal = 0;

  rows.forEach(row => {
    let cells = row.querySelectorAll("td");
    if (cells.length < 15) return; // skip if no data row
    let rowText = Array.from(cells).map(cell => cell.textContent.toLowerCase()).join(" ");
    let date = cells[1].textContent.split(' ')[0]; // only date part if datetime
    let total = parseFloat(cells[14].textContent.replace('₹','')) || 0; // ✅ fixed index

    let showRow = true;

    // Global search across all cells
    if (searchText && !rowText.includes(searchText)) showRow = false;

    // Start and End Date filter
    if (startDate && new Date(date) < new Date(startDate)) showRow = false;
    if (endDate && new Date(date) > new Date(endDate)) showRow = false;

    row.style.display = showRow ? "" : "none";
    if (showRow) grandTotal += total;
  });

  document.getElementById("grandTotal").textContent = "₹" + grandTotal.toFixed(2);
}

// Event Listeners
document.getElementById("searchInput").addEventListener("keyup", filterTable);
document.getElementById("startDate").addEventListener("change", filterTable);
document.getElementById("endDate").addEventListener("change", filterTable);

// Initial calculation
filterTable();

// Print Report
function printReport() {
  let tableHtml = document.getElementById("purchaseTable").outerHTML;
  let startDate = document.getElementById("startDate").value;
  let endDate = document.getElementById("endDate").value;
  let today = new Date().toLocaleDateString();

  let newWin = window.open("", "_blank");
  newWin.document.write(`
<html>
  <head>
    <title>Purchase Report</title>
    <style>
      body { font-family: Arial, sans-serif; margin: 20px; }
      h1 { text-align: center; margin: 0; color: orange; }
      h2 { text-align: center; margin: 5px 0; font-size: 16px; color: black; }
      h3 { text-align: center; margin: 5px 0; font-size: 14px; }
      table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 12px; }
      th, td { border: 1px solid black; padding: 6px; text-align: center; }
      th { background-color: #f2f2f2; }
      thead { display: table-header-group; }
      tfoot { display: table-footer-group; }
      tfoot td { font-weight: bold; }
      tfoot td:not(#grandTotal) { text-align: right; }
      tfoot td#grandTotal { text-align: right; }
      footer { text-align: center; margin-top: 20px; font-size: 12px; }
    </style>
  </head>
  <body>
    <h1>Raiban Electrical Solutions</h1>
    <h2>Powering Homes & Hearts</h2>
    <h3>Purchased Stocks Report</h3>
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