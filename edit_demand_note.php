<?php
include '../conn.php';

// Get demand_id and material_id from URL
$demand_id   = isset($_GET['demand_id']) ? intval($_GET['demand_id']) : 0;
$material_id = isset($_GET['material_id']) ? intval($_GET['material_id']) : 0;

if (!$demand_id || !$material_id) {
    die("<div class='container mt-5'><div class='alert alert-danger'>Invalid request!</div></div>");
}

// Fetch demand note and specific material item
$sql = "SELECT dn.*, dni.material_code, dni.material_name, dni.unit, dni.qty, dni.remarks, dni.item_id AS material_item_id
        FROM demand_note dn
        JOIN demand_note_items dni ON dn.demand_id = dni.demand_id
        WHERE dn.demand_id=? AND dni.item_id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $demand_id, $material_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if (!$row) {
    die("<div class='container mt-5'><div class='alert alert-danger'>Material not found!</div></div>");
}

// Update record if submitted
if (isset($_POST['update'])) {
    $quantity = $_POST['quantity'];
    $remarks  = $_POST['remarks'];

    $update_sql = "UPDATE demand_note_items SET qty=?, remarks=? WHERE item_id=?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("isi", $quantity, $remarks, $material_id);

    if ($update_stmt->execute()) {
        echo "<script>
            alert('Material Updated Successfully');
            window.location='view_demand_note.php';
        </script>";
        exit;
    } else {
        echo "<script>alert('Update Failed');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Edit Material - Demand Note</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet"/>

<style>
    body {
        background: #f5f7fa;
    }
    .card {
        border-radius: 1rem;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }
    .card-header {
        background: linear-gradient(135deg, #0d6efd, #1e3a8a); /* deep blue gradient */
        color: #fff;
        border-radius: 1rem 1rem 0 0;
    }
    .form-label {
        font-weight: 600;
        color: #212529; /* black/dark gray */
    }
</style>

</head>
<body>

<div class="container my-5">

  <div class="d-flex justify-content-between align-items-center mb-4">
    <a href="view_demand_note.php" class="btn btn-dark">
      <i class="bi bi-arrow-left"></i> Back
    </a>
  </div>

  <div class="card">
    <div class="card-header">
      <h4 class="mb-0"><i class="bi bi-pencil-square me-2"></i> Edit Material - <?= htmlspecialchars($row['site_name']); ?></h4>
    </div>
    <div class="card-body">
      <form method="POST" class="needs-validation" novalidate>
        <!-- Demand Note Info -->
        <div class="row mb-3">
          <div class="col-md-6">
            <label class="form-label">Site Incharge</label>
            <input type="text" class="form-control" value="<?= htmlspecialchars($row['site_incharge']); ?>" readonly>
          </div>
          <div class="col-md-6">
            <label class="form-label">Store Incharge</label>
            <input type="text" class="form-control" value="<?= htmlspecialchars($row['store_incharge']); ?>" readonly>
          </div>
        </div>

        <!-- Material Info -->
        <div class="row mb-3">
          <div class="col-md-6">
            <label class="form-label">Material Code</label>
            <input type="text" class="form-control" value="<?= htmlspecialchars($row['material_code']); ?>" readonly>
          </div>
          <div class="col-md-6">
            <label class="form-label">Material Name</label>
            <input type="text" class="form-control" value="<?= htmlspecialchars($row['material_name']); ?>" readonly>
          </div>
        </div>

        <div class="row mb-3">
          <div class="col-md-6">
            <label class="form-label">Unit</label>
            <input type="text" class="form-control" value="<?= htmlspecialchars($row['unit']); ?>" readonly>
          </div>
          <div class="col-md-6">
            <label class="form-label">Quantity</label>
            <input type="number" name="quantity" class="form-control" value="<?= htmlspecialchars($row['qty']); ?>" required>
            <div class="invalid-feedback">Please enter a valid quantity.</div>
          </div>
        </div>

        <!-- Editable Remarks -->
        <div class="mb-3">
          <label class="form-label">Remarks</label>
          <textarea class="form-control" name="remarks" rows="3"><?= htmlspecialchars($row['remarks']); ?></textarea>
        </div>

        <div class="text-center">
       <button type="submit" name="update" class="btn btn-primary me-2">
    <i class="bi bi-check-circle me-1"></i> Update
     </button>
    <a href="view_demand_note.php" class="btn btn-dark">
    <i class="bi bi-x-circle me-1"></i> Cancel
     </a>
    </div>


      </form>
    </div>
  </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Bootstrap validation
(() => {
  'use strict'
  const forms = document.querySelectorAll('.needs-validation')
  Array.from(forms).forEach(form => {
    form.addEventListener('submit', event => {
      if (!form.checkValidity()) {
        event.preventDefault()
        event.stopPropagation()
      }
      form.classList.add('was-validated')
    }, false)
  })
})()
</script>
</body>
</html>
