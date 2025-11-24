<?php
include 'conn.php';

// Set the response type to HTML
header('Content-Type: text/html; charset=utf-8');

// Start a database transaction
$conn->begin_transaction();

try {
    // Check if the request method is POST
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        throw new Exception("Invalid request method.");
    }

    // General details from POST data
    $storeIncharge  = $conn->real_escape_string($_POST['storeIncharge']);
    $siteName       = $conn->real_escape_string($_POST['siteName']);
    $siteIncharge   = $conn->real_escape_string($_POST['siteIncharge']);
    $siteSupervisor = $conn->real_escape_string($_POST['siteSupervisor']);
    $contractor     = $conn->real_escape_string($_POST['contractor']);
    $circle         = $conn->real_escape_string($_POST['circle']);
    $division       = $conn->real_escape_string($_POST['division']);
    $subDivision    = $conn->real_escape_string($_POST['subDivision']);
    $sectionName    = $conn->real_escape_string($_POST['sectionName']);
    $location       = $conn->real_escape_string($_POST['location']);
    $itemsData      = json_decode($_POST['itemsData'], true);

    // Validate that itemsData is an array
    if (!is_array($itemsData)) {
        throw new Exception("Items data is not valid.");
    }

    // --- 1. Calculate final cost ---
    $final_cost = 0;
    foreach ($itemsData as $it) {
        $final_cost += floatval($it['total']);
    }

    // --- 2. Insert into main stock_issue table ---
    $stmt = $conn->prepare("INSERT INTO stock_issue 
        (store_incharge, site_name, site_incharge, site_supervisor, contractor, circle, division, subdivision, section_name, location, final_cost) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssssssd", $storeIncharge, $siteName, $siteIncharge, $siteSupervisor, $contractor, $circle, $division, $subDivision, $sectionName, $location, $final_cost);

    if (!$stmt->execute()) {
        throw new Exception("Failed to create stock issue record.");
    }

    $issue_id = $stmt->insert_id;
    $stmt->close();

    // --- 3. Process each item ---
    // Prepare statements outside the loop for better performance
    $stmtItem = $conn->prepare("INSERT INTO stock_issue_items (issue_id, material_code, material_name, unit, qty, rate, total) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $checkSiteStock = $conn->prepare("SELECT qty FROM site_stock WHERE site_name = ? AND material_code = ?");
    $updateSiteStock = $conn->prepare("UPDATE site_stock SET qty = ? WHERE site_name = ? AND material_code = ?");
    $insertSiteStock = $conn->prepare("INSERT INTO site_stock (site_name, material_code, material_name, unit, qty) VALUES (?, ?, ?, ?, ?)");
    $updateProductStock = $conn->prepare("UPDATE products SET quantity = quantity - ? WHERE product_code = ?");
    $checkProductStock = $conn->prepare("SELECT quantity FROM products WHERE product_code = ?");

    foreach ($itemsData as $it) {
        $code  = $it['code'];
        $name  = $it['name'];
        $unit  = $it['unit'];
        $qty   = intval($it['qty']);
        $rate  = floatval($it['rate']);
        $total = floatval($it['total']);

        // Check for available stock before proceeding
        $checkProductStock->bind_param("s", $code);
        if (!$checkProductStock->execute()) {
            throw new Exception("Failed to check product stock for item: " . $name);
        }
        $productResult = $checkProductStock->get_result();
        if ($productResult->num_rows === 0) {
            throw new Exception("Product '{$name}' ({$code}) not found in the main stock.");
        }
        $productRow = $productResult->fetch_assoc();
        $availableQty = intval($productRow['quantity']);

        if ($availableQty < $qty) {
            throw new Exception("Not enough stock for '{$name}'. Available: {$availableQty}, Requested: {$qty}.");
        }

        // Insert into stock_issue_items
        $stmtItem->bind_param("isssidd", $issue_id, $code, $name, $unit, $qty, $rate, $total);
        if (!$stmtItem->execute()) {
            throw new Exception("Failed to add item: " . $name);
        }

        // Update site stock (add or update)
        $checkSiteStock->bind_param("ss", $siteName, $code);
        $checkSiteStock->execute();
        $result = $checkSiteStock->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $newQty = $row['qty'] + $qty;
            $updateSiteStock->bind_param("iss", $newQty, $siteName, $code);
            if (!$updateSiteStock->execute()) {
                throw new Exception("Failed to update site stock for item: " . $name);
            }
        } else {
            $insertSiteStock->bind_param("ssssi", $siteName, $code, $name, $unit, $qty);
            if (!$insertSiteStock->execute()) {
                throw new Exception("Failed to insert into site stock for item: " . $name);
            }
        }

        // Debit issued quantity from the main products table
        $updateProductStock->bind_param("is", $qty, $code);
        if (!$updateProductStock->execute()) {
            throw new Exception("Failed to update main product stock for item: " . $name);
        }
    }

    // Close all prepared statements
    $stmtItem->close();
    $checkSiteStock->close();
    $updateSiteStock->close();
    $insertSiteStock->close();
    $updateProductStock->close();
    $checkProductStock->close();

    // If all queries were successful, commit the transaction
    $conn->commit();

    // --- ✅ Success Message ---
    // If execution reaches here, it means the transaction was successful.
    echo "
    <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: 'Items were issued successfully!',
                showConfirmButton: false,
                timer: 2000
            }).then(() => {
                window.location.replace('issue.html');
            });
        });
    </script>";

} catch (Exception $e) {
    // If any error occurred, roll back the entire transaction
    $conn->rollback();

    $errorMessage = json_encode($e->getMessage());
    // --- ❌ Error Message ---
    // You can log the detailed error for your own records: error_log($e->getMessage());
    echo "
    <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'error',
                title: 'Transaction Failed',
                text: {$errorMessage},
                showConfirmButton: true
            }).then(() => {
                window.location.replace('issue.html');
            });
        });
    </script>";
}

$conn->close();
?>

