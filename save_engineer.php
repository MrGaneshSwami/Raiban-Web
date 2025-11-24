<?php
include 'conn.php'; // your DB connection file

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $employee_code= $_POST['employee_code'];
    $phone = $_POST['phone'];
    $alt_phone = $_POST['alt_phone'];
    $pan_no = $_POST['pan_no'];
    $aadhar_no = $_POST['aadhar_no'];
    $address = $_POST['address'];

    $sql = "INSERT INTO site_engineers (name, email,employee_code, phone, alt_phone, pan_no, aadhar_no, address)
            VALUES ('$name', '$email','$employee_code', '$phone', '$alt_phone', '$pan_no', '$aadhar_no', '$address')";

    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('Site Engineer added successfully'); window.location.href='site_engineer.html';</script>";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}
?>
