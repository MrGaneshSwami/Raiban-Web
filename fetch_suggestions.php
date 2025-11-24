<?php

include 'conn.php';



$q = $_GET['q'] ?? '';
$field = $_GET['field'] ?? '';
$siteDetailsFields = [
    'site_supervisor', 'site_incharge', 'contractor', 'division', 'sub_division', 'location', 'section', 'circle'
];

// Site details by site name
if ($field === "site_details_by_name") {
    $stmt = $conn->prepare("SELECT site_supervisor, site_incharge, contractor, division, sub_division, location, section, circle FROM sites WHERE site_name = ? LIMIT 1");
    $stmt->bind_param("s", $q);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    if ($row) {
        // Map DB keys to frontend keys
        $response = [
            'success' => true,
            'siteSupervisor' => $row['site_supervisor'],
            'siteIncharge'   => $row['site_incharge'],
            'contractor'     => $row['contractor'],
            'division'       => $row['division'],
            'subDivision'    => $row['sub_division'],
            'location'       => $row['location'],
            'sectionName'    => $row['section'],
            'circle'         => $row['circle'],
        ];
        echo json_encode($response);
    } else {
        echo json_encode(['success' => false]);
    }
    exit();
}



// stock check

// Site stock by material code
if ($field === "site_stock_by_code") {
    $code = $_GET['q'];
    $site = $_GET['site'];
    $stmt = $conn->prepare("SELECT qty FROM site_stock WHERE site_name = ? AND material_code = ? LIMIT 1");
    $stmt->bind_param("ss", $site, $code);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    echo json_encode([ "stock" => $row['qty'] ?? 0 ]);
    exit;
}

// Site stock by material name
if ($field === "site_stock_by_name") {
    $name = $_GET['q'];
    $site = $_GET['site'];
    $stmt = $conn->prepare("SELECT qty FROM site_stock WHERE site_name = ? AND material_name = ? LIMIT 1");
    $stmt->bind_param("ss", $site, $name);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    echo json_encode([ "stock" => $row['qty'] ?? 0 ]);
    exit;
}


/**
 * -------------------------
 * Direct Data Fetch Handlers
 * -------------------------
 */

// Product rate by product code
if ($field === "product_rate_by_code") {
    $stmt = $conn->prepare("SELECT price FROM products WHERE product_code = ? LIMIT 1");
    $stmt->bind_param("s", $q);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    echo json_encode([
        'success' => $row ? true : false,
        'rate'    => $row ? floatval($row['price']) : 0
    ]);
    exit();
}

// Material unit by product name
if ($field === "material_unit_by_name") {
    $stmt = $conn->prepare("SELECT unit FROM products WHERE product_name = ? LIMIT 1");
    $stmt->bind_param("s", $q);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    echo $row ? htmlspecialchars($row['unit']) : "";
    exit();
}

// Material unit by product code
if ($field === "material_unit_by_code") {
    $stmt = $conn->prepare("SELECT unit FROM products WHERE product_code = ? LIMIT 1");
    $stmt->bind_param("s", $q);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    echo $row ? htmlspecialchars($row['unit']) : "";
    exit();
}

// Material name by code
if ($field === "material_name_by_code") {
    $stmt = $conn->prepare("SELECT product_name FROM products WHERE product_code = ? LIMIT 1");
    $stmt->bind_param("s", $q);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    echo $row ? htmlspecialchars($row['product_name']) : "<div class='list-group-item disabled'>No Material Name Found</div>";
    exit();
}

// Material code by name
if ($field === "material_code_by_name") {
    $stmt = $conn->prepare("SELECT product_code FROM products WHERE product_name = ? LIMIT 1");
    $stmt->bind_param("s", $q);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    echo $row ? htmlspecialchars($row['product_code']) : "<div class='list-group-item disabled'>No Material Code Found</div>";
    exit();
}

// Supplier GST by name
if ($field === "supplier_gst_by_name") {
    $stmt = $conn->prepare("SELECT gst_no FROM suppliers WHERE name = ? LIMIT 1");
    $stmt->bind_param("s", $q);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    echo $row ? htmlspecialchars($row['gst_no']) : "";
    exit();
}

// Supplier contact by name
if ($field === "supplier_contact_by_name") {
    $stmt = $conn->prepare("SELECT phone FROM suppliers WHERE name = ? LIMIT 1");
    $stmt->bind_param("s", $q);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    echo $row ? htmlspecialchars($row['phone']) : "";
    exit();
}

// Supplier name by GST
if ($field === "supplier_name_by_gst") {
    $stmt = $conn->prepare("SELECT name FROM suppliers WHERE gst_no = ? LIMIT 1");
    $stmt->bind_param("s", $q);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    echo $row ? htmlspecialchars($row['name']) : "";
    exit();
}

// Supplier contact by GST
if ($field === "supplier_contact_by_gst") {
    $stmt = $conn->prepare("SELECT phone FROM suppliers WHERE gst_no = ? LIMIT 1");
    $stmt->bind_param("s", $q);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    echo $row ? htmlspecialchars($row['phone']) : "";
    exit();
}

/**
 * -------------------------
 * Autocomplete Suggestions
 * -------------------------
 */
$map = [
    "site_supervisor" => ["table" => "site_engineers", "column" => "name"],
    "contractor"      => ["table" => "contractors",    "column" => "name"],
    "site_incharge"   => ["table" => "site_incharge",  "column" => "name"],
    "site_name"       => ["table" => "sites",          "column" => "site_name"],
    "material_code"   => ["table" => "products",       "column" => "product_code"],
    "material_name"   => ["table" => "products",       "column" => "product_name"],
    "store_incharge"  => ["table" => "users",          "column" => "name", "role_column" => "role", "role_value" => "store incharge"],
    "supplier_name"   => ["table" => "suppliers",      "column" => "name"],
    "supplier_gst"    => ["table" => "suppliers",      "column" => "gst_no"],
];

if (isset($map[$field])) {
    $table  = $map[$field]['table'];
    $column = $map[$field]['column'];

    if ($field === "store_incharge") {
        $role_column = $map[$field]['role_column'];
        $role_value  = $map[$field]['role_value'];
        $stmt = $conn->prepare("SELECT DISTINCT $column FROM $table WHERE $column LIKE ? AND $role_column = ? LIMIT 10");
        $search = "%".$q."%";
        $stmt->bind_param("ss", $search, $role_value);
    } else {
        $stmt = $conn->prepare("SELECT DISTINCT $column FROM $table WHERE $column LIKE ? LIMIT 10");
        $search = "%".$q."%";
        $stmt->bind_param("s", $search);
    }

    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows > 0) {
        while ($row = $res->fetch_assoc()) {
            echo "<div class='list-group-item list-group-item-action' data-value='" . htmlspecialchars($row[$column]) . "'>" . htmlspecialchars($row[$column]) . "</div>";
        }
    } else {
        echo "<div class='list-group-item disabled'>No Suggestions Found</div>";
    }
    exit();

}
// Issued rate by product code (for return form)
if ($field === "issued_rate_by_code") {
    // Legacy fallback (not site-specific). Prefer issued_rate_by_site_and_code
    $code = $_GET['q'] ?? '';
    $stmt = $conn->prepare("SELECT i.rate
        FROM stock_issue_items i
        WHERE i.material_code = ?
        ORDER BY i.id DESC
        LIMIT 1");
    $stmt->bind_param("s", $code);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    echo json_encode([
        'success' => $row ? true : false,
        'rate'    => $row ? floatval($row['rate']) : 0
    ]);
    exit();
}

ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: text/html; charset=UTF-8');



//echo "<div class='list-group-item disabled'>Invalid Field</div>";

//site rate

if ($field === "issued_rate_by_name") {
    // Legacy fallback (not site-specific). Prefer issued_rate_by_site_and_name
    $name = $_GET['q'] ?? '';
    $stmt = $conn->prepare("SELECT i.rate
        FROM stock_issue_items i
        WHERE i.material_name = ?
        ORDER BY i.id DESC
        LIMIT 1");
    $stmt->bind_param("s", $name);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    echo json_encode([
        'success' => $row ? true : false,
        'rate'    => $row ? floatval($row['rate']) : 0
    ]);
    exit();
}

// Site-specific latest issue rate by code
if ($field === "issued_rate_by_site_and_code") {
    $code = $_GET['q'] ?? '';
    $site = $_GET['site'] ?? '';
    $stmt = $conn->prepare("SELECT i.rate
        FROM stock_issue_items i
        JOIN stock_issue s ON s.issue_id = i.issue_id
        WHERE s.site_name = ? AND i.material_code = ?
        ORDER BY s.issue_id DESC
        LIMIT 1");
    $stmt->bind_param("ss", $site, $code);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    echo json_encode([
        'success' => $row ? true : false,
        'rate'    => $row ? floatval($row['rate']) : 0
    ]);
    exit();
}

// Site-specific latest issue rate by name
if ($field === "issued_rate_by_site_and_name") {
    $name = $_GET['q'] ?? '';
    $site = $_GET['site'] ?? '';
    $stmt = $conn->prepare("SELECT i.rate
        FROM stock_issue_items i
        JOIN stock_issue s ON s.issue_id = i.issue_id
        WHERE s.site_name = ? AND i.material_name = ?
        ORDER BY s.issue_id DESC
        LIMIT 1");
    $stmt->bind_param("ss", $site, $name);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    echo json_encode([
        'success' => $row ? true : false,
        'rate'    => $row ? floatval($row['rate']) : 0
    ]);
    exit();
}

