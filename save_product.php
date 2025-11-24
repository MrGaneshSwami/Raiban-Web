<?php
include 'conn.php'; // Include the database connection file

// Get data from the form
$product_code = $_POST['product_code'];
$product_name = $_POST['product_name'];
$unit         = $_POST['unit'];
$quantity     = $_POST['quantity'];
$price        = $_POST['price'];

// Insert into the products table
$sql = "INSERT INTO products 
        (product_code, product_name, unit, quantity, price) 
        VALUES 
        ('$product_code', '$product_name', '$unit', '$quantity', '$price')";

if ($conn->query($sql) === TRUE) {
    echo "<script>
            alert('Product saved successfully!');
            window.location.href = 'product.html';
          </script>";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();
?>
