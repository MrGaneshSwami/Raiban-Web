<?php
include 'conn.php'; // DB connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $storeIncharge = $conn->real_escape_string($_POST['storeIncharge']);
    $supplierName  = $conn->real_escape_string($_POST['supplierName']);
    $supplierGst   = $conn->real_escape_string($_POST['supplierGst']);
    $contactNo     = $conn->real_escape_string($_POST['contactNo']);
    $paymentType   = $conn->real_escape_string($_POST['paymentType']);
    $finalCost     = floatval($_POST['finalCost']);
    $itemsData     = json_decode($_POST['itemsData'], true);

    // ✅ Check if updating existing purchase
    if (!empty($_POST['purchase_id'])) {
        $purchaseId = intval($_POST['purchase_id']);

        // Update purchase details
        $updatePurchase = $conn->prepare("UPDATE purchases 
            SET store_incharge=?, supplier_name=?, supplier_gst=?, contact_no=?, payment_type=?, final_cost=? 
            WHERE purchase_id=?");
        $updatePurchase->bind_param("ssssssi", $storeIncharge, $supplierName, $supplierGst, $contactNo, $paymentType, $finalCost, $purchaseId);
        $updatePurchase->execute();

        foreach ($itemsData as $item) {
            $itemId = isset($item['id']) ? intval($item['id']) : 0;

            if ($itemId > 0) {
                // 🔄 Update purchase_items
                $stmt = $conn->prepare("UPDATE purchase_items 
                    SET material_name=?, material_code=?, quantity=?, unit=?, location=?, rate=?, gst=?, total=? 
                    WHERE item_id=? AND purchase_id=?");
                $stmt->bind_param("sssdsddddi",
                    $item['name'],
                    $item['code'],
                    $item['qty'],
                    $item['unit'],
                    $item['location'],
                    $item['rate'],
                    $item['gstAmount'],
                    $item['total'],
                    $itemId,
                    $purchaseId
                );
                $stmt->execute();
            } else {
                // ➕ Insert new purchase_items
                $stmt = $conn->prepare("INSERT INTO purchase_items 
                    (purchase_id, material_name, material_code, quantity, unit, location, rate, gst, total) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("issdssddd",
                    $purchaseId,
                    $item['name'],
                    $item['code'],
                    $item['qty'],
                    $item['unit'],
                    $item['location'],
                    $item['rate'],
                    $item['gstAmount'],
                    $item['total']
                );
                $stmt->execute();
            }

            // ✅ Update Products table stock & price
            updateProduct($conn, $item);
        }

        echo "<script>
                alert('Purchase updated successfully!');
                window.location.href='purchase.html';
              </script>";

    } else {
        // ➕ Insert new purchase
        $sql = "INSERT INTO purchases (store_incharge, supplier_name, supplier_gst, contact_no, payment_type, final_cost) 
                VALUES ('$storeIncharge', '$supplierName', '$supplierGst', '$contactNo', '$paymentType', '$finalCost')";

        if ($conn->query($sql) === TRUE) {
            $purchaseId = $conn->insert_id;

            if (!empty($itemsData)) {
                $stmt = $conn->prepare("INSERT INTO purchase_items 
                    (purchase_id, material_name, material_code, quantity, unit, location, rate, gst, total) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                
                foreach ($itemsData as $item) {
                    $stmt->bind_param("issdsssdd",
                        $purchaseId,
                        $item['name'],
                        $item['code'],
                        $item['qty'],
                        $item['unit'],
                        $item['location'],
                        $item['rate'],
                        $item['gstAmount'],
                        $item['total']
                    );
                    $stmt->execute();

                    // ✅ Update Products table stock & price
                    updateProduct($conn, $item);
                }
                $stmt->close();
            }

            echo "<script>
                    alert('Purchase saved successfully!');
                    window.location.href='purchase.html';
                  </script>";
        } else {
            echo "Error: " . $conn->error;
        }
    }
}

// ✅ Function to update products table
function updateProduct($conn, $item) {
    $product_code = $item['code'];
    $product_name = $item['name'];
    $unit         = $item['unit'];
    $qty          = (int)$item['qty'];
    $price        = (float)$item['rate'];

    // Check if product exists
    $check = $conn->prepare("SELECT quantity FROM products WHERE product_code = ?");
    $check->bind_param("s", $product_code);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        // Update stock & price
        $row = $result->fetch_assoc();
        $new_qty = $row['quantity'] + $qty;

        $update = $conn->prepare("UPDATE products SET quantity=?, price=? WHERE product_code=?");
        $update->bind_param("ids", $new_qty, $price, $product_code);
        $update->execute();
        $update->close();
    } else {
        // Insert new product
        $insert = $conn->prepare("INSERT INTO products (product_code, product_name, unit, quantity, price) 
                                  VALUES (?, ?, ?, ?, ?)");
        $insert->bind_param("sssid", $product_code, $product_name, $unit, $qty, $price);
        $insert->execute();
        $insert->close();
    }
    $check->close();
}
?>
