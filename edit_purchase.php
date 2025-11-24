<?php
include 'conn.php';

// ✅ Get purchase_id
if (!isset($_GET['purchase_id'])) {
    die("Purchase ID missing!");
}
$purchaseId = intval($_GET['purchase_id']);

// ✅ Handle form submission
if (isset($_POST['update'])) {
    $purchase_date = $_POST['purchase_date'];
    $paymentType   = $_POST['paymentType'];
    $finalCost     = floatval($_POST['finalCost']);

    // Update purchase table
    $stmt = $conn->prepare("UPDATE purchases SET created_at=?, payment_type=?, final_cost=? WHERE purchase_id=?");
    $stmt->bind_param("ssdi", $purchase_date, $paymentType, $finalCost, $purchaseId);
    $stmt->execute();

    // Update items
    foreach ($_POST['itemsData'] as $itemId => $item) {
        $qty       = (float)$item['qty'];
        $rate      = (float)$item['rate'];
        $gstPercent= (float)$item['gstPercent'];
        $gstAmt    = (float)$item['gstAmount'];
        $total     = (float)$item['total'];

        $stmtItem = $conn->prepare("UPDATE purchase_items 
                                    SET quantity=?, rate=?, gst=?, total=? 
                                    WHERE item_id=? AND purchase_id=?");
        $stmtItem->bind_param("ddddii", $qty, $rate, $gstAmt, $total, $itemId, $purchaseId);
        $stmtItem->execute();

        // Update product stock & price
        $check = $conn->prepare("SELECT quantity FROM products WHERE product_code=?");
        $check->bind_param("s", $item['code']);
        $check->execute();
        $res = $check->get_result();
        if ($res->num_rows > 0) {
            $rowP = $res->fetch_assoc();
            $newQty = $rowP['quantity'] + $qty;
            $upd = $conn->prepare("UPDATE products SET quantity=?, price=? WHERE product_code=?");
            $upd->bind_param("ids", $newQty, $rate, $item['code']);
            $upd->execute();
        } else {
            $ins = $conn->prepare("INSERT INTO products (product_code, product_name, unit, quantity, price) VALUES (?,?,?,?,?)");
            $ins->bind_param("sssdi", $item['code'], $item['name'], $item['unit'], $qty, $rate);
            $ins->execute();
        }
        $check->close();
    }

    echo "<script>alert('Purchase updated successfully!');
    window.location='view_purchase.php';</script>";
}

// ✅ Fetch purchase details
$purchase = $conn->query("SELECT * FROM purchases WHERE purchase_id=$purchaseId")->fetch_assoc();
$supplierName = $purchase['supplier_name'];
$items = $conn->query("SELECT * FROM purchase_items WHERE purchase_id=$purchaseId");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Purchase</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="style.css"/>
</head>
<body class="bg-light">

<div id="navbar-placeholder"></div>
<div class="container py-4">
  <a href="view_purchase.php" class="btn btn-dark mb-3"><i class="bi bi-arrow-left me-2"></i>Back</a>
  <div class="card shadow">
    <div class="card-header bg-primary text-white">
      <h4>Edit Purchase - <?= $supplierName ?></h4>
    </div>
    <div class="card-body">
      <form method="POST" id="editForm">
        <input type="hidden" name="purchase_id" value="<?= $purchaseId ?>">

        <!-- Purchase Details -->
        <div class="row mb-3">
          <div class="col-md-4">
            <label class="form-label">Date</label>
            <input type="date" name="purchase_date" class="form-control" value="<?= date('Y-m-d', strtotime($purchase['created_at'])) ?>" required>
          </div>
          <div class="col-md-4">
            <label class="form-label">Payment Type</label>
            <select name="paymentType" class="form-select">
              <option <?= $purchase['payment_type']=="Paid"?"selected":"" ?>>Paid</option>
              <option <?= $purchase['payment_type']=="Unpaid"?"selected":"" ?>>Unpaid</option>
              <option <?= $purchase['payment_type']=="Other"?"selected":"" ?>>Other</option>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label">Final Cost</label>
            <input type="number" step="0.01" name="finalCost" id="finalCost" class="form-control" value="<?= $purchase['final_cost'] ?>" readonly>
          </div>
        </div>

        <!-- Items Table -->
        <table class="table table-bordered align-middle" id="itemsTable">
          <thead class="table-dark">
            <tr>
              <th>Material</th>
              <th>Code</th>
              <th>Qty</th>
              <th>Unit</th>
              <th>Location</th>
              <th>Rate</th>
              <th>GST %</th>
              <th>GST Amt</th>
              <th>Total</th>
            </tr>
          </thead>
          <tbody>
            <?php while($row = $items->fetch_assoc()) { ?>
              <tr>
                <input type="hidden" name="itemsData[<?= $row['item_id'] ?>][id]" value="<?= $row['item_id'] ?>">
                <td><input type="text" name="itemsData[<?= $row['item_id'] ?>][name]" value="<?= $row['material_name'] ?>" class="form-control" readonly></td>
                <td><input type="text" name="itemsData[<?= $row['item_id'] ?>][code]" value="<?= $row['material_code'] ?>" class="form-control" readonly></td>
                <td><input type="number" step="0.01" name="itemsData[<?= $row['item_id'] ?>][qty]" value="<?= $row['quantity'] ?>" class="form-control qty"></td>
                <td><input type="text" name="itemsData[<?= $row['item_id'] ?>][unit]" value="<?= $row['unit'] ?>" class="form-control" readonly></td>
                <td><input type="text" name="itemsData[<?= $row['item_id'] ?>][location]" value="<?= $row['location'] ?>" class="form-control"></td>
                <td><input type="number" step="0.01" name="itemsData[<?= $row['item_id'] ?>][rate]" value="<?= $row['rate'] ?>" class="form-control rate"></td>
                <td><input type="number" step="0.01" name="itemsData[<?= $row['item_id'] ?>][gstPercent]" value="<?= $row['rate']>0 ? round(($row['gst']/$row['rate']/$row['quantity']*100),2):0 ?>" class="form-control gstPercent"></td>
                <td><input type="number" step="0.01" name="itemsData[<?= $row['item_id'] ?>][gstAmount]" value="<?= $row['gst'] ?>" class="form-control gstAmount" readonly></td>
                <td><input type="number" step="0.01" name="itemsData[<?= $row['item_id'] ?>][total]" value="<?= $row['total'] ?>" class="form-control total" readonly></td>
              </tr>
            <?php } ?>
          </tbody>
        </table>

        <div class="text-center">
          <button type="submit" name="update" class="btn btn-primary">Update</button>
          <a href="view_purchase.php" class="btn btn-dark">Cancel</a>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="script.js"></script>
<script>
function recalc() {
  let finalCost = 0;
  document.querySelectorAll("#itemsTable tbody tr").forEach(row => {
    let qty = parseFloat(row.querySelector(".qty").value) || 0;
    let rate = parseFloat(row.querySelector(".rate").value) || 0;
    let gstPercent = parseFloat(row.querySelector(".gstPercent").value) || 0;

    let base = qty * rate;
    let gstAmt = (base * gstPercent / 100);
    let total = base + gstAmt;

    row.querySelector(".gstAmount").value = gstAmt.toFixed(2);
    row.querySelector(".total").value = total.toFixed(2);

    finalCost += total;
  });
  document.getElementById("finalCost").value = finalCost.toFixed(2);
}

// Add event listeners
document.querySelectorAll(".qty, .rate, .gstPercent").forEach(input => {
  input.addEventListener("input", recalc);
});
</script>

</body>
</html>
