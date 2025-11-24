<?php
// view_sites.php
include 'conn.php'; // your DB connection file

// Fetch all sites
$sql = "SELECT id, site_name, site_incharge, site_supervisor, contractor, circle, division, sub_division, section, location 
        FROM sites ORDER BY id DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>View Sites - Raiban Electrical Solutions</title>

  <!-- Bootstrap & Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

  <!-- Common Stylesheet -->
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <!-- Navbar -->
  <div id="header-placeholder"></div>

  <div class="container-fluid my-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <a href="Forms.html" class="btn btn-dark">
        <i class="fas fa-arrow-left me-2"></i> Back
      </a>
      <h3 class="m-0"><i class="fas fa-building text-primary"></i> View Sites</h3>
    </div>

    <!-- Search Box -->
    <div class="mb-3">
      <input type="text" id="searchInput" class="form-control" placeholder="🔍 Search sites by name, incharge, contractor...">
    </div>

    <!-- Sites Table -->
    <div class="table-responsive">
      <table class="table table-bordered table-hover text-center align-middle w-100%" id="sitesTable">
       <thead class="table-dark">
          <tr>
            <th>Sr.No</th>
            <th>Site Name</th>
            <th>Incharge</th>
            <th>Supervisor</th>
            <th>Contractor</th>
            <th>Circle</th>
            <th>Division</th>
            <th>Sub Division</th>
            <th>Section</th>
            <th>Location</th>
          </tr>
        </thead>
        <tbody>
          <?php
          if ($result->num_rows > 0) {
              $i = 1;
              while ($row = $result->fetch_assoc()) {
                  echo "<tr>";
                  echo "<td>" . $i++ . "</td>";
                  echo "<td>" . htmlspecialchars($row['site_name']) . "</td>";
                  echo "<td>" . htmlspecialchars($row['site_incharge']) . "</td>";
                  echo "<td>" . htmlspecialchars($row['site_supervisor']) . "</td>";
                  echo "<td>" . htmlspecialchars($row['contractor']) . "</td>";
                  echo "<td>" . htmlspecialchars($row['circle']) . "</td>";
                  echo "<td>" . htmlspecialchars($row['division']) . "</td>";
                  echo "<td>" . htmlspecialchars($row['sub_division']) . "</td>";
                  echo "<td>" . htmlspecialchars($row['section']) . "</td>";
                  echo "<td>" . htmlspecialchars($row['location']) . "</td>";
                  echo "</tr>";
              }
          } else {
              echo "<tr><td colspan='10' class='text-muted'>No sites found</td></tr>";
          }
          ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Bootstrap & JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Load header
    document.addEventListener("DOMContentLoaded", function() {
      fetch("header.html")
        .then(response => response.text())
        .then(data => { document.getElementById("header-placeholder").innerHTML = data; });
    });

    // Client-side search filter
    document.getElementById("searchInput").addEventListener("keyup", function() {
      let filter = this.value.toLowerCase();
      let rows = document.querySelectorAll("#sitesTable tbody tr");
      rows.forEach(row => {
        let text = row.innerText.toLowerCase();
        row.style.display = text.includes(filter) ? "" : "none";
      });
    });
  </script>
</body>
</html>
