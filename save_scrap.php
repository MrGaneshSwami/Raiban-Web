<?php
// scrap_save.php
include 'conn.php'; // should set $conn = new mysqli(...)

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: scrap.html");
    exit;
}

// fetch & sanitize
$scrap_type = isset($_POST['scrap_type']) ? trim($_POST['scrap_type']) : '';
$quantity   = isset($_POST['quantity']) ? trim($_POST['quantity']) : '';
$site_name  = isset($_POST['site_name']) ? trim($_POST['site_name']) : null;
$remark     = isset($_POST['remark']) ? trim($_POST['remark']) : null;

// basic validation
$errors = [];
if ($scrap_type === '') {
    $errors[] = "Please select a scrap type.";
}
if ($quantity === '' || !is_numeric($quantity) || floatval($quantity) <= 0) {
    $errors[] = "Please enter a valid scrap quantity greater than zero.";
}

if (!empty($errors)) {
    // redirect back with first error (simple approach)
    $err = urlencode($errors[0]);
    header("Location: scrap.html?error={$err}");
    exit;
}

$quantity_val = number_format((float)$quantity, 2, '.', '');

// prepared statement
$sql = "INSERT INTO scrap_records (scrap_type, quantity, site_name, remark) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    $err = urlencode("Database error: " . $conn->error);
    header("Location: scrap.html?error={$err}");
    exit;
}

// bind: s = string, d = double, s = string, s = string
$stmt->bind_param("sdss", $scrap_type, $quantity_val, $site_name, $remark);

$ok = $stmt->execute();
if ($ok) {
    header("Location: scrap.html?success=1");
} else {
    $err = urlencode("Failed to save: " . $stmt->error);
    header("Location: scrap.html?error={$err}");
}

$stmt->close();
$conn->close();
exit;
