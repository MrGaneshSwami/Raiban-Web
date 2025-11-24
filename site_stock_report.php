<?php
include 'conn.php';

// Optional default values for filters
$startDate = isset($_GET['startDate']) ? $_GET['startDate'] : '';
$endDate = isset($_GET['endDate']) ? $_GET['endDate'] : '';

// Fetch stock with site details
$sql = "SELECT ss.id, ss.site_name, ss.material_code, ss.material_name, ss.unit, ss.qty,
               s.site_incharge, s.site_supervisor, s.contractor, s.circle, s.division, 
               s.sub_division, s.section, s.location, s.created_at
        FROM site_stock ss
        LEFT JOIN sites s ON ss.site_name = s.site_name
        ORDER BY ss.id DESC";

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
  <title>Site Stock Report - Raiban Electrical Solutions</title>

  <!-- Bootstrap -->
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
<div id="header-placeholder"></div>

<div class="container-fluid my-4">
  <!-- Header -->
  <div class="d-flex justify-content-between align-items-center mb-4 no-print">
    <a href="generate_report.html" class="btn btn-dark"><i class="bi bi-arrow-left"></i> Back</a>
    <h2 class="m-0">Site Stocks Report</h2>
   <div></div>
  </div>

  <!-- Filters Row -->
  <div class="row g-3 mb-3 align-items-end">
    <div class="col-md-2">
      <label for="startDate" class="form-label">📅Start Date</label>
      <div class="input-group">
        <input type="date" id="startDate" class="form-control" value="<?= $startDate ?>">
      </div>
    </div>
    <div class="col-md-2">
      <label for="endDate" class="form-label">📅End Date</label>
      <div class="input-group">
        <input type="date" id="endDate" class="form-control" value="<?= $endDate ?>">
      </div>
    </div>
    <div class="col-md-8">
      <label for="searchInput" class="form-label">🔍Search</label>
      <input type="text" id="searchInput" class="form-control" placeholder="🔍 Site, Material...">
    </div>
  </div>

  <!-- Stock Table -->
  <table class="table table-bordered table-striped text-center align-middle" id="stockTable">
    <thead class="table-dark">
      <tr>
        <th>Sr. No</th>
        <th>Date</th>
        <th>Site Name</th>
        <th>Incharge</th>
        <th>Supervisor</th>
        <th>Contractor</th>
        <th>Circle</th>
        <th>Division</th>
        <th>Subdivision</th>
        <th>Section</th>
        <th>Location</th>
        <th>Material Code</th>
        <th>Material Name</th>
        <th>Unit</th>
        <th>Quantity</th>
      </tr>
    </thead>
    <tbody>
      <?php 
      if($result->num_rows > 0):
        $sr = 1;
        while($row = $result->fetch_assoc()): ?>
          <tr>
            <td><?= $sr++ ?></td>
            <td><?= date('d-m-Y', strtotime($row['created_at'])) ?></td>
            <td><?= $row['site_name'] ?></td>
            <td><?= $row['site_incharge'] ?></td>
            <td><?= $row['site_supervisor'] ?></td>
            <td><?= $row['contractor'] ?></td>
            <td><?= $row['circle'] ?></td>
            <td><?= $row['division'] ?></td>
            <td><?= $row['sub_division'] ?></td>
            <td><?= $row['section'] ?></td>
            <td><?= $row['location'] ?></td>
            <td><?= $row['material_code'] ?></td>
            <td><?= $row['material_name'] ?></td>
            <td><?= $row['unit'] ?></td>
            <td><?= $row['qty'] ?></td>
          </tr>
      <?php endwhile; 
      else: ?>
        <tr><td colspan="15" class="text-center">No Stock Records Found</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<!-- Generate Report Button -->
<div class="text-center mt-4 no-print">
  <button class="btn btn-primary" onclick="printReport()">
    <i class="bi bi-printer me-2"></i>Generate Report
  </button>
</div>

<script src="script.js"></script>
<script>
const stockTable = document.getElementById("stockTable");
const searchInput = document.getElementById("searchInput");
const startDateInput = document.getElementById("startDate");
const endDateInput = document.getElementById("endDate");

// Live Search + Date Filter
function filterTable() {
    let searchValue = searchInput.value.toLowerCase();
    let startDate = startDateInput.value;
    let endDate = endDateInput.value;
    let rows = stockTable.querySelectorAll("tbody tr");
    let anyVisible = false;

    rows.forEach(row => {
        // Skip the 'no-result' row while filtering
        if(row.classList.contains('no-result')) return;

        let rowText = row.textContent.toLowerCase();
        let rowDate = row.cells[1].innerText.split("-").reverse().join("-");
        let show = true;

        // Check date filter
        if(startDate && new Date(rowDate) < new Date(startDate)) show = false;
        if(endDate && new Date(rowDate) > new Date(endDate)) show = false;

        // Check search filter
        if(searchValue && !rowText.includes(searchValue)) show = false;

        row.style.display = show ? "" : "none";
        if(show) anyVisible = true;
    });

    // Remove existing 'no-result' row
    let existingNoResult = stockTable.querySelector(".no-result");
    if(existingNoResult) existingNoResult.remove();

    // Add 'No records found' if nothing visible
    if(!anyVisible) {
        let colCount = stockTable.querySelectorAll("thead th").length;
        let tr = document.createElement("tr");
        tr.classList.add("no-result");
        tr.innerHTML = `<td colspan="${colCount}" class="text-center">No records found</td>`;
        stockTable.querySelector("tbody").appendChild(tr);
    }
}

// Event listeners
searchInput.addEventListener("keyup", filterTable);
startDateInput.addEventListener("change", filterTable);
endDateInput.addEventListener("change", filterTable);

// Print Report
function printReport() {
    let tableHtml = stockTable.outerHTML;
    let startDate = startDateInput.value;
    let endDate = endDateInput.value;
    let today = new Date().toLocaleDateString();

    let newWin = window.open("", "_blank");
    newWin.document.write(`
<html>
<head>
    <title>Site Stock Report</title>
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
    <h3>Site Stock Report</h3>
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
