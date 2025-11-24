<?php
include 'conn.php'; // Your DB connection file

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect form data safely
    $name       = trim($_POST['name']);
    $email      = trim($_POST['email']);
    $employee_code   = trim($_POST['emp_code']);
    $phone      = trim($_POST['phone']);
    $alt_phone  = trim($_POST['alt_phone']);
    $pan_no     = strtoupper(trim($_POST['pan_no']));
    $aadhar_no  = trim($_POST['aadhar_no']);
    $address    = trim($_POST['address']);

    // Insert query with prepared statement
    $sql = "INSERT INTO site_incharge 
            (name, email, employee_code, phone, alt_phone, pan_no, aadhar_no, address) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssss", $name, $email, $employee_code, $phone, $alt_phone, $pan_no, $aadhar_no, $address);

    if ($stmt->execute()) {
       echo"<script>alert('✅ Site Incharge details saved successfully!'); window.location.href='site_incharge.html';</script>";
        exit;
    } else {
        echo "❌ Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
