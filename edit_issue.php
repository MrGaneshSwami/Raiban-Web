<?php
include '../conn.php';

// Get issue_id from URL
if (!isset($_GET['issue_id'])) {
    die("Issue ID missing!");
}
$issueId = intval($_GET['issue_id']);

// ✅ Handle form submission
if (isset($_POST['update'])) {
    foreach ($_POST['itemsData'] as $itemId => $item) {
        $qty   = (float)$item['qty'];
        $rate  = (float)$item['rate'];
        $total = $qty * $rate;

        // Update stock_issue_items
        $stmt = $conn->prepare("UPDATE stock_issue_items SET qty=?, total=? WHERE item_id=? AND issue_id=?");
        $stmt->bind_param("ddii", $qty, $total, $itemId, $issueId);
        $stmt->execute();
    }

    // Update final cost
    $finalCost = 0;
    $resItems = $conn->query("SELECT total FROM stock_issue_items WHERE issue_id=$issueId");
    while ($row = $resItems->fetch_assoc()) {
        $finalCost += $row['total'];
    }
    $conn->query("UPDATE stock_issue SET final_cost=$finalCost WHERE issue_id=$issueId");

    echo "<script>alert('Issue updated successfully!'); window.location='view_issue.php';</script>";
}

// Fetch issue details
$issue = $conn->query("SELECT * FROM stock_issue WHERE issue_id=$issueId")->fetch_assoc();
$siteName = $issue['site_name'];
$items = $conn->query("SELECT * FROM stock_issue_items WHERE issue_id=$issueId");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Issue #<?= $issueId ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="style.css"/>
</head>
<body class="bg-light">

<div id="navbar-placeholder"></div>
<div class="container py-4">
  <a href="view_issue.php" class="btn btn-dark mb-3"><i class="bi bi-arrow-left me-2"></i>Back</a>
  <div class="card shadow">
    <div class="card-header bg-primary text-white">
      <h4>Edit Issued Stock - <?= $siteName ?></h4>
    </div>
    <div class="card-body">
      <form method="POST" id="editForm">
        <div class="mb-3">
          <label class="form-label">Store Incharge</label>
          <input type="text" class="form-control" value="<?= $issue['store_incharge'] ?>" readonly>
        </div>
        <div class="mb-3">
          <label class="form-label">Site Name</label>
          <input type="text" class="form-control" value="<?= $issue['site_name'] ?>" readonly>
        </div>
        <div class="mb-3">
          <label class="form-label">Final Cost</label>
          <input type="number" class="form-control" id="finalCost" value="<?= $issue['final_cost'] ?>" readonly>
        </div>

        <table class="table table-bordered" id="itemsTable">
          <thead class="table-dark">
            <tr>
              <th>Material</th>
              <th>Code</th>
              <th>Qty</th>
              <th>Unit</th>
              <th>Rate</th>
              <th>Total</th>
            </tr>
          </thead>
          <tbody>
            <?php while($row = $items->fetch_assoc()) { ?>
              <tr>
                <input type="hidden" name="itemsData[<?= $row['item_id'] ?>][id]" value="<?= $row['item_id'] ?>">
                <td><input type="text" name="itemsData[<?= $row['item_id'] ?>][name]" value="<?= $row['material_name'] ?>" class="form-control" readonly></td>
                <td><input type="text" name="itemsData[<?= $row['item_id'] ?>][code]" value="<?= $row['material_code'] ?>" class="form-control" readonly></td>
                <td><input type="number" step="1" name="itemsData[<?= $row['item_id'] ?>][qty]" value="<?= $row['qty'] ?>" class="form-control qty"></td>
                <td><input type="text" value="<?= $row['unit'] ?>" class="form-control" readonly></td>
                <td><input type="number" step="1" name="itemsData[<?= $row['item_id'] ?>][rate]" value="<?= $row['rate'] ?>" class="form-control rate" readonly></td>
                <td><input type="number" step="1" name="itemsData[<?= $row['item_id'] ?>][total]" value="<?= $row['total'] ?>" class="form-control total" readonly></td>
              </tr>
            <?php } ?>
          </tbody>
        </table>

        <div class="text-center mt-3">
          <button type="submit" name="update" class="btn btn-primary">Update Issue</button>
          <a href="view_issue.php" class="btn btn-dark">Cancel</a>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="script.js"></script>
<script>
// Auto-calculate total & final cost on quantity change
function recalc() {
  let finalCost = 0;
  document.querySelectorAll("#itemsTable tbody tr").forEach(row => {
    let qty = parseFloat(row.querySelector(".qty").value) || 0;
    let rate = parseFloat(row.querySelector(".rate").value) || 0;
    let total = qty * rate;
    row.querySelector(".total").value = total.toFixed(2);
    finalCost += total;
  });
  document.getElementById("finalCost").value = finalCost.toFixed(2);
}

document.querySelectorAll(".qty").forEach(input => {
  input.addEventListener("input", recalc);
});
</script>

</body>
</html>
