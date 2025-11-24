<?php
include 'conn.php';
// view for the admoin 
// Fetch demand notes and items
$sql = "SELECT dn.demand_id, dn.store_incharge, dn.site_name, dn.site_incharge, dn.site_supervisor,
               dn.contractor, dn.circle, dn.division, dn.subdivision, dn.section_name, dn.location,
               dn.demand_date, dn.status,
               dni.material_code, dni.material_name, dni.unit, dni.qty,dni.remarks
        FROM demand_note dn
        LEFT JOIN demand_note_items dni ON dn.demand_id = dni.demand_id
        ORDER BY dn.demand_id DESC";

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
<title>Demand Notes - Raiban Electrical Solutions</title>

<!-- Bootstrap & Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="style.css"/>

<style>
.status-approved { color: #fff; background-color: green; padding: 2px 6px; border-radius: 4px; }
.status-rejected { color: #fff; background-color: red; padding: 2px 6px; border-radius: 4px; }
.status-pending { color: #fff; background-color: orange; padding: 2px 6px; border-radius: 4px; }

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

<!-- Header Bar with Print Button -->
<div id="header-placeholder"></div>

<div class="d-flex justify-content-between align-items-center mb-4 no-print">
  <button class="btn btn-dark" onclick="history.back()"><i class="bi bi-arrow-left"></i> Back</button>
  <h2 class="page-title text-center m-0"><i class="bi bi-file-earmark-text me-2"></i> Demand Notes List</h2>
  <button onclick="printReport()" class="btn btn-secondary"><i class="fas fa-print me-2"></i> Print</button>
</div>

<div class="container-fluid my-4">
  <!-- Search -->
  <div class="mb-3 no-print">
    <input type="text" id="searchInput" class="form-control" placeholder="🔍 Search Demand Notes...">
  </div>

  <!-- Table -->
  <table class="table table-bordered table-striped text-center align-middle" id="demandTable">
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
        <th>Sub-Division</th>
        <th>Section</th>
        <th>Location</th>
        <th>Material Code</th>
        <th>Material Name</th>
        <th>Unit</th>
        <th>Quantity</th>
        <th>Remark</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody>
    <?php 
    if($result->num_rows > 0):
      $sr = 1;
      while($row = $result->fetch_assoc()):
        // Determine status class
        $statusClass = '';
        if ($row['status'] === 'Approved') $statusClass = 'status-approved';
        elseif ($row['status'] === 'Rejected') $statusClass = 'status-rejected';
        else $statusClass = 'status-pending';
    ?>
      <tr>
        <td><?= $sr++ ?></td>
        <td><?= date('d-m-Y', strtotime($row['demand_date'])) ?></td>
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
        <td><?= $row['remarks'] ?></td>
        <td><span class="<?= $statusClass ?>"><?= $row['status'] ?></span></td>
      </tr>
    <?php endwhile; 
    else: ?>
      <tr><td colspan="17" class="text-center">No Demand Notes Found</td></tr>
    <?php endif; ?>
    </tbody>
  </table>
</div>

<!-- Bootstrap & JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="script.js"></script>
<script>
document.getElementById("searchInput").addEventListener("keyup", function () {
    let filter = this.value.toLowerCase();
    let rows = document.querySelectorAll("#demandTable tbody tr");
    let anyVisible = false;

    rows.forEach(row => {
        if (row.textContent.toLowerCase().includes(filter)) {
            row.style.display = "";
            anyVisible = true;
        } else {
            row.style.display = "none";
        }
    });

    // Remove existing "no results" row if any
    let noResultRow = document.querySelector("#demandTable tbody .no-result");
    if (noResultRow) noResultRow.remove();

    // If no rows visible, show "No search results found"
    if (!anyVisible) {
        let colCount = document.querySelectorAll("#demandTable thead th").length;
        let tbody = document.querySelector("#demandTable tbody");
        let tr = document.createElement("tr");
        tr.classList.add("no-result");
        tr.innerHTML = `<td colspan="${colCount}" class="text-center">No search results found</td>`;
        tbody.appendChild(tr);
    }
});

// Print Report Function - Only Company Name & Demand Notes List
function printReport() {
    let table = document.getElementById("demandTable");
    let rows = table.querySelectorAll("tbody tr");
    let filteredRows = "";

    rows.forEach(row => {
        let statusCell = row.cells[row.cells.length - 1]; // Last column = Status
        if (statusCell && statusCell.textContent.trim() === "Approved") {
            filteredRows += `<tr>${row.innerHTML}</tr>`;
        }
    });

    let today = new Date().toLocaleDateString();

    let newWin = window.open("", "_blank");
    newWin.document.write(`
<html>
<head>
  <title>Approved Demand Notes</title>
  <style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    h1, h2, h3 { text-align: center; margin: 5px 0; }
    h1 { color: orange; }
    h2 { font-size: 16px; color: black; }
    h3 { font-size: 14px; }
    table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 12px; }
    th, td { border: 1px solid black; padding: 6px; text-align: center; }
    th { background-color: #f2f2f2; }
    .status-approved { color: #fff; background-color: green; padding: 2px 6px; border-radius: 4px; }
    footer { text-align: center; margin-top: 20px; font-size: 12px; }
  </style>
</head>
<body>
  <h1>Raiban Electrical Solutions</h1>
  <h2>Powering Homes & Hearts</h2>
  <h3>Approved Demand Notes</h3>
  <h3>Report Date: ${today}</h3>
  <table>
    <thead>
      ${table.querySelector("thead").outerHTML}
    </thead>
    <tbody>
      ${filteredRows}
    </tbody>
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
