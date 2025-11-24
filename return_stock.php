<?php
include 'conn.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Helper: fetch last issue rate, fallback to product price
    $getLastIssueRate = function($conn, $productCode, $siteName) {
        // Try latest from stock_issue_items joined to stock_issue filtered by site
        $stmt = $conn->prepare("SELECT i.rate
            FROM stock_issue_items i
            JOIN stock_issue s ON s.issue_id = i.issue_id
            WHERE s.site_name = ? AND i.material_code = ?
            ORDER BY s.issue_id DESC
            LIMIT 1");
        $stmt->bind_param("ss", $siteName, $productCode);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        $stmt->close();
        if ($row && isset($row['rate'])) {
            return (float)$row['rate'];
        }
        // Fallback to products.price
        $stmt = $conn->prepare("SELECT price FROM products WHERE product_code = ? LIMIT 1");
        $stmt->bind_param("s", $productCode);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        $stmt->close();
        return $row && isset($row['price']) ? (float)$row['price'] : 0.0;
    };
    // General details
    $storeIncharge  = $_POST['storeIncharge'] ?? '';
    $siteName       = $_POST['siteName'] ?? '';
    $siteIncharge   = $_POST['siteIncharge'] ?? '';
    $siteSupervisor = $_POST['siteSupervisor'] ?? '';
    $contractor     = $_POST['contractor'] ?? '';
    $circle         = $_POST['circle'] ?? '';
    $division       = $_POST['division'] ?? '';
    $subDivision    = $_POST['subDivision'] ?? '';
    $sectionName    = $_POST['sectionName'] ?? '';
    $location       = $_POST['location'] ?? '';

    $itemsData = $_POST['itemsData'] ?? '[]';
    $scrapData = $_POST['scrapData'] ?? '[]';

    $items = json_decode($itemsData, true);
    $scraps = json_decode($scrapData, true);

    $conn->begin_transaction();

    try {
        // Insert into return_stock
        $stmt = $conn->prepare("INSERT INTO return_stock 
            (store_incharge, site_name, site_incharge, site_supervisor, contractor, circle, division, subdivision, section_name, location)
            VALUES (?,?,?,?,?,?,?,?,?,?)");
        $stmt->bind_param("ssssssssss", $storeIncharge, $siteName, $siteIncharge, $siteSupervisor, $contractor, $circle, $division, $subDivision, $sectionName, $location);
        $stmt->execute();
        $returnId = $stmt->insert_id;
        $stmt->close();

        // --- Save normal return items ---
        if (!empty($items)) {
            // Insert return item prepared statement
            $insertReturnItem = $conn->prepare("INSERT INTO return_items 
                (return_id, material_code, material_name, unit, quantity, consumption, rate, total, remark)
                VALUES (?,?,?,?,?,?,?,?,?)");

            // Site stock select and update prepared statements
            $selectSiteStock = $conn->prepare("SELECT qty FROM site_stock WHERE site_name = ? AND material_code = ? LIMIT 1 FOR UPDATE");
            $updateSiteStock = $conn->prepare("UPDATE site_stock SET qty = qty - (?) WHERE site_name = ? AND material_code = ?");

            // Product check, update, insert prepared statements
            $selectProduct = $conn->prepare("SELECT product_code FROM products WHERE product_code = ? LIMIT 1");
            $updateProduct = $conn->prepare("UPDATE products SET quantity = quantity + ? WHERE product_code = ?");
            $insertProduct = $conn->prepare("INSERT INTO products (product_code, product_name, unit, quantity, price) VALUES (?,?,?,?,?)");

            foreach ($items as $item) {
                $code   = $item['code'];
                $name   = $item['name'];
                $unit   = $item['unit'];
                $qty    = (int)($item['qty']);
                $consumption = (float)($item['consumedQty'] ?? 0);
                $rate   = isset($item['rate']) ? (float)$item['rate'] : 0.0;
                if ($rate <= 0) {
                    $rate = $getLastIssueRate($conn, $code, $siteName);
                }
                $total  = $qty * $rate;
                $remark = $item['remark'];

                // Insert into return_items
                $insertReturnItem->bind_param("isssiddds", $returnId, $code, $name, $unit, $qty, $consumption, $rate, $total, $remark);
                if (!$insertReturnItem->execute()) {
                    throw new Exception("Failed to insert return item for material: " . $name);
                }

                // Get current site stock
                $selectSiteStock->bind_param("ss", $siteName, $code);
                if (!$selectSiteStock->execute()) {
                    throw new Exception("Failed to fetch site stock for material: " . $name);
                }
                $res = $selectSiteStock->get_result();
                $row = $res->fetch_assoc();
                $currentStock = isset($row['qty']) ? (int)$row['qty'] : 0;

                if ($currentStock < ($qty + $consumption)) {
                    throw new Exception("Return + Consumed exceeds site stock for $name. Available: $currentStock, Requested: " . ($qty + $consumption));
                }

                // Deduct from site stock by (return + consumption)
                // Deduct from site stock (return + consumed)
$deduct = $qty + $consumption;
$updateSiteStock->bind_param("dss", $deduct, $siteName, $code);
if (!$updateSiteStock->execute()) {
    throw new Exception("Failed to update site stock for material: " . $name);
}

// Increase main product stock by returned qty only
$selectProduct->bind_param("s", $code);
$selectProduct->execute();
$productExists = $selectProduct->get_result()->num_rows > 0;

if ($productExists) {
    $updateProduct->bind_param("ds", $qty, $code); // use d for decimal quantities
    if (!$updateProduct->execute()) {
        throw new Exception("Failed to update main product stock for material: " . $name);
    }
} else {
    $insertProduct->bind_param("sssid", $code, $name, $unit, $qty, $rate);
    if (!$insertProduct->execute()) {
        throw new Exception("Failed to insert product into main stock for material: " . $name);
    }
}

            }

            $insertReturnItem->close();
            $selectSiteStock->close();
            $updateSiteStock->close();
            $selectProduct->close();
            $updateProduct->close();
            $insertProduct->close();
        }

        // --- Save scrap and recycle items into separate tables ---
        if (!empty($scraps)) {
            $insertScrap = $conn->prepare("INSERT INTO return_scrap (return_id, scrap_type, scrap_qty, rate, total, remark) VALUES (?,?,?,?,?,?)");
            $insertRecycle = $conn->prepare("INSERT INTO return_recycle (return_id, recycle_code, recycle_name, recycle_unit, recycle_qty, rate, total, remark) VALUES (?,?,?,?,?,?,?,?)");

            foreach ($scraps as $s) {
                $scrapType = $s['scrapType'] ?? '';
                $scrapQty  = isset($s['scrapQty']) ? (float)$s['scrapQty'] : 0;
                $reCode    = $s['recycleCode'] ?? '';
                $reName    = $s['recycleName'] ?? '';
                $reUnit    = $s['recycleUnit'] ?? '';
                $reQty     = isset($s['recycleQty']) ? (float)$s['recycleQty'] : 0;
                $rate      = isset($s['rate']) ? (float)$s['rate'] : 0;
                $total     = isset($s['total']) ? (float)$s['total'] : 0;
                $remark    = $s['remark'] ?? '';

                if ($scrapQty > 0) {
                    $insertScrap->bind_param("isddds", $returnId, $scrapType, $scrapQty, $rate, $total, $remark);
                    if (!$insertScrap->execute()) {
                        throw new Exception("Failed to insert scrap item.");
                    }
                }
                if ($reQty > 0) {
                    // Types: i s s s d d d s
                    $insertRecycle->bind_param("isssddds", $returnId, $reCode, $reName, $reUnit, $reQty, $rate, $total, $remark);
                    if (!$insertRecycle->execute()) {
                        throw new Exception("Failed to insert recycle item.");
                    }
                }
            }

            $insertScrap->close();
            $insertRecycle->close();
        }

        $conn->commit();

         echo "
    <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: 'Items were returned successfully!',
                showConfirmButton: false,
                timer: 2000
            }).then(() => {
                window.location.replace('return.html');
            });
        });
    </script>";


    } catch (Exception $e) {
        $conn->rollback();
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

    }
}
?>

