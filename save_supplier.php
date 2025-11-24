<?php
include 'conn.php'; // use your connection file

// Get data from form
$name       = $_POST['name'];
$email      = $_POST['email'];
$phone      = $_POST['phone'];
$alt_phone  = $_POST['alt_phone'];
$pan_no     = $_POST['pan_no'];
$aadhar_no  = $_POST['aadhar_no'];
$bank_name  = $_POST['bank_name'];
$bank_branch= $_POST['bank_branch'];
$gst_no     = $_POST['gst_no'];
$ifsc_code  = $_POST['ifsc_code'];
$address    = $_POST['address'];

// Insert into suppliers table
$sql = "INSERT INTO suppliers 
        (name, email, phone, alt_phone, pan_no, aadhar_no, bank_name, bank_branch, gst_no, ifsc_code, address) 
        VALUES 
        ('$name', '$email', '$phone', '$alt_phone', '$pan_no', '$aadhar_no', '$bank_name', '$bank_branch', '$gst_no', '$ifsc_code', '$address')";

if ($conn->query($sql) === TRUE) {
    echo "<script>
            alert('Supplier saved successfully!');
            window.location.href = 'supplier.html';
          </script>";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();
?>
