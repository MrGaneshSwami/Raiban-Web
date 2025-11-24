<?php
include 'conn.php';

// Handle Delete
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $conn->query("DELETE FROM users WHERE user_id = $delete_id");
    header("Location: view_users.php"); // redirect to refresh page
    exit;
}

// Handle Update
if (isset($_POST['update_user'])) {
    $id = intval($_POST['user_id']);
    $role = $conn->real_escape_string($_POST['role']);
    $mobile = $conn->real_escape_string($_POST['mobile']);

    $conn->query("UPDATE users SET role='$role', mobile='$mobile' WHERE user_id=$id");
    header("Location: view_users.php");
    exit;
}

// Fetch all users
$users = $conn->query("SELECT * FROM users ORDER BY user_id ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>View Users - Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<link href="style.css" rel="stylesheet">

<style>
body { background-color: #f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
h2 { color: #070d3dff; margin-bottom: 20px; text-align: center; }
.table-container { background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 0 15px rgba(0,0,0,0.1); }
table th { background-color: #acabb8ff !important; color: white; }
table tr:nth-child(even) { background-color: #fdf0e5; }
.btn-warning { background: #ffc107; border: none; color: #000; }
.btn-warning:hover { background: #e0a800; }
.btn-danger { background: #dc3545; border: none; }
.btn-danger:hover { background: #c82333; }
#searchInput { margin-bottom: 15px; }
</style>
</head>
<body>

<div id="navbar-placeholder"></div>

<div class="container my-3">
    <div class="row mb-3">
        <div class="col-auto">
            <a href="admin_dashboard.php" class="btn btn-dark">
                <i class="bi bi-arrow-left me-2"></i> Back
            </a>
        </div>
    </div>

    <h2>Existing Users</h2>
    <input type="text" id="searchInput" class="form-control" placeholder="🔍 Search users...">

    <div class="table-container">
        <table class="table table-bordered table-striped text-center align-middle" id="usersTable">
            <thead>
                <tr>
                    <th>Sr.No</th>
                    <th>Date</th>
                    <th>Role</th>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Phone No.</th>
                    <th>Password</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                if($users->num_rows > 0):
                    $counter = 1;
                    while($row = $users->fetch_assoc()): ?>
                        <tr>
                            <td><?= $counter++ ?></td>
                            <td><?= date("d-m-Y", strtotime($row['created_at'])) ?></td>
                            <td><?= ucfirst($row['role']) ?></td>
                            <td><?= htmlspecialchars($row['name']) ?></td>
                            <td><?= htmlspecialchars($row['email']) ?></td>
                            <td><?= htmlspecialchars($row['mobile']) ?></td>
                            <td>******</td> <!-- Do NOT display hashed passwords -->
                            <td>
                                <a href="edit_user.php?id=<?= $row['user_id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                                <a href="view_users.php?delete_id=<?= $row['user_id'] ?>" 
                                   onclick="return confirm('Delete this user?')" 
                                   class="btn btn-sm btn-danger">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; 
                else: ?>
                    <tr><td colspan="8" class="text-center">No users found</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="script.js"></script>
<script>
// Live search filter
document.getElementById("searchInput").addEventListener("keyup", function() {
    let filter = this.value.toLowerCase();
    let rows = document.querySelectorAll("#usersTable tbody tr");
    let anyVisible = false;

    rows.forEach(row => {
        if (row.textContent.toLowerCase().includes(filter)) {
            row.style.display = "";
            anyVisible = true;
        } else {
            row.style.display = "none";
        }
    });

    let noResultRow = document.querySelector("#usersTable tbody .no-result");
    if (noResultRow) noResultRow.remove();

    if (!anyVisible) {
        let colCount = document.querySelectorAll("#usersTable thead th").length;
        let tbody = document.querySelector("#usersTable tbody");
        let tr = document.createElement("tr");
        tr.classList.add("no-result");
        tr.innerHTML = `<td colspan="${colCount}" class="text-center">No results found</td>`;
        tbody.appendChild(tr);
    }
});
</script>

</body>
</html>