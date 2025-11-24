<?php
include 'conn.php';

// Get return_id from URL
if (!isset($_GET['id'])) {
    die("Return Item ID missing!");
}
$itemId = intval($_GET['id']);

// Fetch return item details along with parent return stock info
$sql = "SELECT ri.*, rs.store_incharge, rs.site_name, rs.final_cost, rs.id AS return_id
        FROM return_items ri
        INNER JOIN return_stock rs ON ri.return_id = rs.id
        WHERE ri.id = $itemId";
$item = $conn->query($sql)->fetch_assoc();
$returnId = $item['return_id'];

// Handle form submission
if (isset($_POST['update'])) {
    $qty   = (float)$_POST['qty'];
    $rate  = (float)$_POST['rate'];
    $total = $qty * $rate;

    // Update return_items table
    $stmt = $conn->prepare("UPDATE return_items SET quantity=?, rate=?, total=? WHERE id=?");
    $stmt->bind_param("dddi", $qty, $rate, $total, $itemId);
    $stmt->execute();

    // Update return_stock final cost
    $finalCost = 0;
    $resItems = $conn->query("SELECT total FROM return_items WHERE return_id=$returnId");
    while ($row = $resItems->fetch_assoc()) {
        $finalCost += $row['total'];
    }
    $conn->query("UPDATE return_stock SET final_cost=$finalCost WHERE id=$returnId");

    echo "<script>alert('Return item updated successfully!'); window.location='view_return.php';</script>";
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Return Item #<?= $itemId ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="style.css"/>
</head>
<body class="bg-light">

<div id="navbar-placeholder"></div>
<div class="container py-4">
  <a href="view_return.php" class="btn btn-dark mb-3"><i class="bi bi-arrow-left me-2"></i>Back</a>
  <div class="card shadow">
    <div class="card-header bg-primary text-white">
      <h4>Edit Returned Stock - <?= $item['site_name'] ?></h4>
    </div>
    <div class="card-body">
      <form method="POST" id="editForm">
        <div class="mb-3">
          <label class="form-label">Store Incharge</label>
          <input type="text" class="form-control" value="<?= $item['store_incharge'] ?>" readonly>
        </div>
        <div class="mb-3">
          <label class="form-label">Site Name</label>
          <input type="text" class="form-control" value="<?= $item['site_name'] ?>" readonly>
        </div>
        <div class="mb-3">
          <label class="form-label">Final Cost</label>
          <input type="number" class="form-control" id="finalCost" value="<?= $item['final_cost'] ?>" readonly>
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
            <tr>
              <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
              <td><input type="text" class="form-control" value="<?= $item['material_name'] ?>" readonly></td>
              <td><input type="text" class="form-control" value="<?= $item['material_code'] ?>" readonly></td>
              <td><input type="number" step="1" name="qty" value="<?= $item['quantity'] ?>" class="form-control qty"></td>
              <td><input type="text" class="form-control" value="<?= $item['unit'] ?>" readonly></td>
              <td><input type="number" step="1" name="rate" value="<?= $item['rate'] ?>" class="form-control rate"></td>
              <td><input type="number" step="1" class="form-control total" value="<?= $item['total'] ?>" readonly></td>
            </tr>
          </tbody>
        </table>

        <div class="text-center mt-3">
          <button type="submit" name="update" class="btn btn-primary">Update Return</button>
          <a href="view_return.php" class="btn btn-dark">Cancel</a>
        </div>
      </form>
    </div>
  </div>
</div>

<!---Script--->
<script src="script.js"></script>
<script>
// Auto-calculate total & final cost on quantity or rate change
function recalc() {
  let qty = parseFloat(document.querySelector(".qty").value) || 0;
  let rate = parseFloat(document.querySelector(".rate").value) || 0;
  let total = qty * rate;
  document.querySelector(".total").value = total.toFixed(2);
  document.getElementById("finalCost").value = total.toFixed(2); // since single item
}

document.querySelector(".qty").addEventListener("input", recalc);
document.querySelector(".rate").addEventListener("input", recalc);
</script>

</body>
</html>
