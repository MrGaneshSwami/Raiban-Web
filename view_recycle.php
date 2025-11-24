<?php
date_default_timezone_set('Asia/Kolkata');
include 'conn.php';

$startDate = $_GET['start'] ?? null;
$endDate   = $_GET['end'] ?? null;

$sql = "SELECT rs.id AS return_id, rs.store_incharge, rs.site_name,
               rs.site_incharge, rs.site_supervisor, rs.contractor,
               rs.circle, rs.division, rs.subdivision, rs.section_name,
               rs.location, rs.created_at,
               r.recycle_code, r.recycle_name, r.recycle_unit,
               r.recycle_qty, r.rate, r.total
        FROM return_stock rs
        INNER JOIN return_recycle r ON rs.id = r.return_id";

if ($startDate && $endDate) $sql .= " WHERE DATE(rs.created_at) BETWEEN '$startDate' AND '$endDate'";
$sql .= " ORDER BY rs.id DESC";

$result = $conn->query($sql);
if (!$result) die('Database query failed: '.$conn->error);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Recycle Report - Raiban Electrical Solutions</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<link href="style.css" rel="stylesheet">
<style>
@media print {
  .no-print { display:none; } 
  body{margin:10mm;} 
  @page{size:A4 landscape; margin:10mm;} 
  table{width:100%;border-collapse:collapse;} 
  th,td{border:1px solid #000;padding:6px;font-size:12px;} 
  tfoot td{font-weight:bold;text-align:right;}
}
</style>
</head>
<body>
    <!-- Header Placeholder -->
    <div id="navbar-placeholder"></div>
    
<div class="d-flex justify-content-between align-items-center mb-4">
  <a href="store.html" class="btn btn-dark no-print"><i class="bi bi-arrow-left me-2"></i>Back</a>
  <h2 class="text-center m-0"><i class="bi bi-recycle"></i> Recycle Stock</h2>
  <div></div>
</div>

<div class="container-fluid my-4">
<div class="row g-3 mb-3 align-items-end">
  <div class="col-md-12"><label>🔍Search</label>
  <input type="text" id="searchInput" class="form-control" placeholder="Search Site, Recycle Material..."></div>
</div>

<table class="table table-bordered table-striped text-center align-middle" id="recycleTable">
<thead class="table-dark">
<tr>
<th>Sr. No</th>
<th>Date</th>
<th>Return ID</th>
<th>Store Incharge</th>
<th>Site Name</th>
<th>Material Code</th>
<th>Material Name</th>
<th>Material Unit</th>
<th>Material Qty</th>
<th>Rate</th>
<th>Total</th>
<th class="no-print">Action</th>
</tr>
</thead>
<tbody>
<?php 
$sr=1; 
if($result && $result->num_rows>0){
while($row=$result->fetch_assoc()){ ?>
<tr>
<td><?= $sr++ ?></td>
<td><?= date('d-m-Y',strtotime($row['created_at'])) ?></td>
<td><?= $row['return_id'] ?></td>
<td><?= $row['store_incharge'] ?></td>
<td><?= $row['site_name'] ?></td>
<td><?= $row['recycle_code'] ?></td>
<td><?= $row['recycle_name'] ?></td>
<td><?= $row['recycle_unit'] ?></td>
<td><?= $row['recycle_qty'] ?></td>
<td>₹<?= number_format($row['rate'],2) ?></td>
<td>₹<?= number_format($row['total'],2) ?></td>
<td class="no-print">
  <a href="edit_recycle.php?id=<?= $row['return_id'] ?>" class="btn btn-sm btn-primary">
    <i class="bi bi-pencil-square"></i> Edit
  </a>
</td>
</tr>
<?php }} ?>
<tr id="noMatchRow" style="display:none;">
  <td colspan="12" class="text-center">No Recycle Records Found</td>
</tr>
</tbody>
</table>
</div>

<!--Scripts-->
<script src="script.js"></script>
<script>
document.getElementById("searchInput").addEventListener("keyup",function(){
  let val=this.value.toLowerCase();
  let rows=document.querySelectorAll("#recycleTable tbody tr");
  let count=0;
  rows.forEach(r=>{
    if(r.id==="noMatchRow") return;
    if(r.textContent.toLowerCase().includes(val)){
      r.style.display="";
      count++;
    } else {
      r.style.display="none";
    }
  });
  document.getElementById("noMatchRow").style.display=count===0?"":"none";
});
</script>

</body>
</html>
