<?php
include 'conn.php'; // your DB connection file

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Get Site Name
    $siteName = $_POST['siteName'] ?? '';

    // Get items data JSON
    $scrapData = $_POST['scrapData'] ?? '[]';
    $items = json_decode($scrapData, true);

    if (!$siteName || empty($items)) {
        echo "No data to save.";
        exit;
    }

    // Prepare insert statement
    $stmt = $conn->prepare("INSERT INTO recycle_stock (site_name, material_code, material_name, unit, quantity, price, remark) VALUES (?, ?, ?, ?, ?, ?, ?)");

    foreach ($items as $item) {
        $code = $item['code'] ?? '';
        $name = $item['name'] ?? '';
        $unit = $item['unit'] ?? '';
        $qty = $item['qty'] ?? 0;
        $price = $item['price'] ?? 0;
        $remark = $item['remark'] ?? '';

        if (!$code || !$name || !$unit || $qty <= 0 || $price <= 0) continue;

        $stmt->bind_param("ssssdds", $siteName, $code, $name, $unit, $qty, $price, $remark);
        $stmt->execute();
    }

    $stmt->close();

    // Redirect back with success message
    header("Location: recycle.html?msg=success");
    exit;
}
?>
