<?php
include 'conn.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $storeIncharge   = $conn->real_escape_string($_POST['storeIncharge']);
    $siteName        = $conn->real_escape_string($_POST['siteName']);
    $siteIncharge    = $conn->real_escape_string($_POST['siteIncharge']);
    $siteSupervisor  = $conn->real_escape_string($_POST['siteSupervisor']);
    $contractor      = $conn->real_escape_string($_POST['contractor']);
    $circle          = $conn->real_escape_string($_POST['circle']);
    $division        = $conn->real_escape_string($_POST['division']);
    $subDivision     = $conn->real_escape_string($_POST['subDivision']);
    $sectionName     = $conn->real_escape_string($_POST['sectionName']);
    $location        = $conn->real_escape_string($_POST['location']);
    $itemsData       = json_decode($_POST['itemsData'], true);

  
    // Insert into demand_note
    $stmt = $conn->prepare("INSERT INTO demand_note 
        (store_incharge, site_name, site_incharge, site_supervisor, contractor, circle, division, subdivision, section_name, location) 
        VALUES (?,?,?,?,?,?,?,?,?,?)");
    $stmt->bind_param("ssssssssss", $storeIncharge, $siteName, $siteIncharge, $siteSupervisor, $contractor, $circle, $division, $subDivision, $sectionName, $location);

    if ($stmt->execute()) {
        $demand_id = $stmt->insert_id;
        $stmt->close();

        // Insert items
        $stmtItem = $conn->prepare("INSERT INTO demand_note_items (demand_id, material_code, material_name, unit, qty, remarks) VALUES (?,?,?,?,?,?)");

        foreach ($itemsData as $it) {
            $code = $it['materialCode'];
            $name = $it['materialName'];
            $unit = $it['materialUnit'];
            $qty  = intval($it['quantity']);
            $remarks = $it['remarks'];
          

            $stmtItem->bind_param("isssis", $demand_id, $code, $name, $unit, $qty, $remarks);
            $stmtItem->execute();
        }
        $stmtItem->close();

         echo "<script>alert('Demand Saved successfully'); window.location.href='demand_note.html';</script>";
    } else {
        echo "error";
    }
}

$conn->close();
?>
