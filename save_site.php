<?php
include 'conn.php'; // <-- Make sure this connects to your DB

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $site_name      = $conn->real_escape_string($_POST['site_name']);
    $site_incharge  = $conn->real_escape_string($_POST['site_incharge']);
    $site_supervisor= $conn->real_escape_string($_POST['site_supervisor']);
    $contractor     = $conn->real_escape_string($_POST['contractor']);
    $circle         = $conn->real_escape_string($_POST['circle']);
    $division       = $conn->real_escape_string($_POST['division']);
    $subdivision    = $conn->real_escape_string($_POST['sub_division']);
    $section        = $conn->real_escape_string($_POST['section']);
    $location       = $conn->real_escape_string($_POST['location']);

    $sql = "INSERT INTO sites 
            (site_name, site_incharge, site_supervisor, contractor, circle, division, sub_division, section, location)
            VALUES 
            ('$site_name', '$site_incharge', '$site_supervisor', '$contractor', '$circle', '$division', '$subdivision', '$section', '$location')";

    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('Site Information Saved Successfully'); window.location.href='site.html';</script>";
    } else {
        echo "Error: " . $conn->error;
    }
}
$conn->close();
?>
