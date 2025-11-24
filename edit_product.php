<?php
include 'conn.php';

// Get product ID from URL
if (!isset($_GET['id'])) {
    die("Product ID missing!");
}
$productId = intval($_GET['id']);

// Handle form submission
if (isset($_POST['update'])) {
    $productCode = $conn->real_escape_string($_POST['product_code']);
    $productName = $conn->real_escape_string($_POST['product_name']); // optional to edit
    $unit        = $conn->real_escape_string($_POST['unit']);
    $price       = floatval($_POST['price']);
    $quantity    = floatval($_POST['quantity']);

    $stmt = $conn->prepare("UPDATE products SET product_code=?, product_name=?, unit=?, price=?, quantity=? WHERE product_id=?");
    $stmt->bind_param("sssddi", $productCode, $productName, $unit, $price, $quantity, $productId);

    if ($stmt->execute()) {
        echo "<script>alert('Product updated successfully!'); window.location='Manage_stock.php';</script>";
        exit;
    } else {
        $error = "Update failed: " . $conn->error;
    }
}

// Fetch product details
$product = $conn->query("SELECT * FROM products WHERE product_id=$productId")->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Product - <?= htmlspecialchars($product['product_name']) ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-4">
  <a href="Manage_stock.php" class="btn btn-dark mb-3"><i class="bi bi-arrow-left me-2"></i>Back</a>

  <div class="card shadow">
    <div class="card-header bg-primary text-white">
      <h4>Edit Product: <?= htmlspecialchars($product['product_name']) ?></h4>
    </div>
    <div class="card-body">
      <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
      <?php endif; ?>

      <form method="POST" id="editProductForm">
        <div class="mb-3">
          <label class="form-label">Product Code</label>
          <input type="text" name="product_code" class="form-control" value="<?= htmlspecialchars($product['product_code']) ?>" required>
        </div>

        <div class="mb-3">
          <label class="form-label">Product Name</label>
          <input type="text" name="product_name" class="form-control" value="<?= htmlspecialchars($product['product_name']) ?>" required>
        </div>

        <div class="mb-3">
          <label class="form-label">Unit</label>
          <input type="text" name="unit" class="form-control" value="<?= htmlspecialchars($product['unit']) ?>" required>
        </div>

        <div class="mb-3">
          <label class="form-label">Quantity</label>
          <input type="number" step="0.01" name="quantity" class="form-control" id="quantity" value="<?= $product['quantity'] ?>" required>
        </div>

        <div class="mb-3">
          <label class="form-label">Price (₹)</label>
          <input type="number" step="0.01" name="price" class="form-control" id="price" value="<?= $product['price'] ?>" required>
        </div>

        <div class="mb-3">
          <label class="form-label">Total Value (₹)</label>
          <input type="number" step="0.01" id="totalValue" class="form-control" value="<?= $product['quantity'] * $product['price'] ?>" readonly>
        </div>

        <div class="text-center">
          <button type="submit" name="update" class="btn btn-primary">Update Product</button>
          <a href="manage_stocks.php" class="btn btn-dark">Cancel</a>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
const quantityInput = document.getElementById('quantity');
const priceInput = document.getElementById('price');
const totalInput = document.getElementById('totalValue');

function recalcTotal() {
  const qty = parseFloat(quantityInput.value) || 0;
  const price = parseFloat(priceInput.value) || 0;
  totalInput.value = (qty * price).toFixed(2);
}

quantityInput.addEventListener('input', recalcTotal);
priceInput.addEventListener('input', recalcTotal);
</script>

</body>
</html>
