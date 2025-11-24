<?php
date_default_timezone_set('Asia/Kolkata');
include 'conn.php';

// Get start and end date from GET parameters
$startDate = $_GET['start'] ?? null;
$endDate   = $_GET['end'] ?? null;

// Fetch scrap items with return_stock info
$sql = "SELECT rs.id AS return_id, rs.store_incharge, rs.site_name,
               rs.site_incharge, rs.site_supervisor, rs.contractor,
               rs.circle, rs.division, rs.subdivision, rs.section_name,
               rs.location, rs.created_at,
               s.scrap_type, s.scrap_qty, s.rate, s.total
        FROM return_stock rs
        INNER JOIN return_scrap s ON rs.id = s.return_id";

if ($startDate && $endDate) {
    $sql .= " WHERE DATE(rs.created_at) BETWEEN '$startDate' AND '$endDate'";
}

$sql .= " ORDER BY rs.id DESC";

$result = $conn->query($sql);

if (!$result) die('Database query failed: ' . $conn->error);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Scrap Report - Raiban Electrical Solutions</title>
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
}
</style>
</head>
<body>

<!-- Header Placeholder -->
<div id="header-placeholder"></div>

<div class="d-flex justify-content-between align-items-center mb-4">
  <a href="generate_report.html" class="btn btn-dark no-print">
    <i class="bi bi-arrow-left me-2"></i> Back
  </a>
  <h2 class="text-center m-0"><i class="bi bi-trash"></i> Scrap Report</h2>
  <div></div>
</div>

<div class="container-fluid my-4">
  <!-- Filters -->
  <div class="row g-3 mb-3 align-items-end">
    <div class="col-md-2">
      <label>📅Start Date</label>
      <input type="date" id="startDate" class="form-control" value="<?= $startDate ?>">
    </div>
    <div class="col-md-2">
      <label>📅End Date</label>
      <input type="date" id="endDate" class="form-control" value="<?= $endDate ?>">
    </div>
    <div class="col-md-8">
      <label>🔍Search</label>
      <input type="text" id="searchInput" class="form-control" placeholder="Search Site, Scrap Type...">
    </div>
  </div>

  <table class="table table-bordered table-striped text-center align-middle" id="scrapTable">
    <thead class="table-dark">
      <tr>
        <th>Sr. No</th>
        <th>Date</th>
        <th>Site Name</th>
        <th>Store Incharge</th>
        <th>Site Name</th>
        <th>Scrap Type</th>
        <th>Qty</th>
        <th>Rate</th>
        <th>Total</th>
      </tr>
    </thead>
    <tbody>
<?php
$sr = 1;
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) { ?>
      <tr>
        <td><?= $sr++ ?></td>
        <td><?= date('d-m-Y', strtotime($row['created_at'])) ?></td>
        <td><?= $row['site_name'] ?></td>
        <td><?= $row['store_incharge'] ?></td>
        <td><?= $row['site_name'] ?></td>
        <td><?= $row['scrap_type'] ?></td>
        <td><?= $row['scrap_qty'] ?></td>
        <td>₹<?= number_format($row['rate'],2) ?></td>
        <td>₹<?= number_format($row['total'],2) ?></td>
      </tr>
<?php } } ?>
      <tr id="noMatchRow" style="display:none;">
        <td colspan="9" class="text-center">No Scrap Records Found</td>
      </tr>
    </tbody>
    <tfoot>
      <tr class="table-secondary fw-bold" id="grandTotalRow">
        <td colspan="8" class="text-end">Grand Total:</td>
        <td id="grandTotal">₹0.00</td>
      </tr>
    </tfoot>
  </table>
</div>

<div class="text-center no-print mt-3">
  <button class="btn btn-primary" onclick="printReport()"><i class="bi bi-printer me-2"></i>Generate Report</button>
</div>

<!--Scripts-->
<script src="script.js"></script>
<script>
// Search filter
document.getElementById("searchInput").addEventListener("keyup", function() {
    let val = this.value.toLowerCase();
    let rows = document.querySelectorAll("#scrapTable tbody tr");
    let count = 0;
    rows.forEach(row => {
        if(row.id === "noMatchRow") return;
        if(row.textContent.toLowerCase().includes(val)) { row.style.display=""; count++; } 
        else { row.style.display="none"; }
    });
    document.getElementById("noMatchRow").style.display = count===0?"":"none";
    document.getElementById("grandTotalRow").style.display = count===0?"none":"";
    calculateGrandTotal();
});

// Date filter reload
document.getElementById("startDate").addEventListener("change", reloadWithDates);
document.getElementById("endDate").addEventListener("change", reloadWithDates);
function reloadWithDates(){
    let start=document.getElementById("startDate").value;
    let end=document.getElementById("endDate").value;
    if(start && end) window.location.href=`scrap_report.php?start=${start}&end=${end}`;
}

// Grand total
function calculateGrandTotal(){
    let total=0;
    document.querySelectorAll("#scrapTable tbody tr").forEach(row=>{
        if(row.style.display==="none"||row.id==="noMatchRow") return;
        let val=parseFloat(row.cells[8].innerText.replace(/[^0-9.-]+/g,""));
        if(!isNaN(val)) total+=val;
    });
    document.getElementById("grandTotal").innerText="₹"+total.toFixed(2);
}
calculateGrandTotal();

// Print
function printReport(){
    let tableHtml = document.getElementById("scrapTable").outerHTML;
    let start=document.getElementById("startDate").value;
    let end=document.getElementById("endDate").value;
    let today = new Date().toLocaleDateString();
    let newWin=window.open("","_blank");
    newWin.document.write(`<html><head><title>Scrap Report</title>
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
    <h1>Raiban Electrical Solutions</h1>
    <h2>Scrap Report</h2>
    <h3>Report Date: ${today}</h3>
    ${start&&end?`<h3>From: ${start} To: ${end}</h3>`:""}
    ${tableHtml}
     <footer>
      Office Address: P-4/6 OLD MIDC, Near GST Bhavan, Satara-415004 <br>
      Mob: 9420230909 | GST No: 27ATXPP3195R1Z9 <br>
      E-mail: raibanelectricalsoln@gmail.com
    </footer>
    </body></html>`);
    newWin.document.close(); newWin.print();
}
</script>
</body>
</html>

