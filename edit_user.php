<?php
include 'conn.php';

// Get user id from URL
if (!isset($_GET['id'])) {
    die("User ID not provided.");
}
$user_id = intval($_GET['id']);

// Fetch existing user
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    die("User not found.");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $role = $_POST['role'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $mobile = $_POST['mobile'];
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    $updatePassword = false;
    $hashedPassword = '';

    // Only update password if both fields are filled and match
    if (!empty($password) || !empty($confirm_password)) {
        if ($password !== $confirm_password) {
            $error_message = "Passwords do not match!";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $updatePassword = true;
        }
    }

    if (!isset($error_message)) {
        if ($updatePassword) {
            $stmt = $conn->prepare("UPDATE users SET role=?, name=?, email=?, mobile=?, password=? WHERE user_id=?");
            $stmt->bind_param("sssssi", $role, $name, $email, $mobile, $hashedPassword, $user_id);
        } else {
            $stmt = $conn->prepare("UPDATE users SET role=?, name=?, email=?, mobile=? WHERE user_id=?");
            $stmt->bind_param("ssssi", $role, $name, $email, $mobile, $user_id);
        }

        if ($stmt->execute()) {
            $success_message = "User updated successfully! Redirecting to users page...";
        } else {
            $error_message = "Error updating user. Please try again.";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit User</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<link href="style.css" rel="stylesheet">
<style>
body { background-color: #f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
.card { border-radius: 12px; box-shadow: 0 0 15px rgba(0,0,0,0.1); background: linear-gradient(135deg, #4e89ae, #7da6c3); color: #fff; }
.card-header { font-weight: bold; font-size: 1.2rem; background: rgba(255,255,255,0.1); border-bottom: 1px solid rgba(255,255,255,0.2); }
.form-label { font-weight: 500; color: #fff; }
.form-control, select { border-radius: 8px; background-color: rgba(255,255,255,0.9); color: #333; }
.btn-submit { background-color: #1c1c1c; color: #fff; width: 100%; border-radius: 8px; border: none; }
.btn-submit:hover { background-color: #333; }
</style>
</head>
<body>
<div id="navbar-placeholder"></div>

<div class="container my-3">
    <a href="view_users.php" class="btn btn-dark mb-3"><i class="bi bi-arrow-left"></i> Back to Users</a>
</div>

<div class="container my-12">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card p-4">
                <div class="card-header mb-3 text-center">
                    <i class="bi bi-person-badge me-2"></i> Edit User
                </div>
                
                <?php if (isset($success_message)): ?>
                    <div class="alert alert-success" id="successAlert">
                        <?php echo $success_message; ?>
                    </div>
                    <script>
                        setTimeout(function(){
                            window.location.href = "view_users.php?updated=1";
                        }, 3000);
                    </script>
                <?php endif; ?>
                
                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger"><?php echo $error_message; ?></div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="mb-3">
                        <label for="name" class="form-label"><i class="bi bi-person-circle me-1"></i> Full Name</label>
                        <input type="text" class="form-control" name="name" id="name" value="<?php echo htmlspecialchars($user['name']); ?>" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label"><i class="bi bi-envelope me-1"></i> Email</label>
                        <input type="email" class="form-control" name="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label for="mobile" class="form-label"><i class="bi bi-telephone me-1"></i> Mobile</label>
                        <input type="text" class="form-control" name="mobile" id="mobile" value="<?php echo htmlspecialchars($user['mobile']); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="role" class="form-label">Role</label>
                        <select class="form-select" name="role" id="role" required>
                            <option value="">Select Role</option>
                            <option value="admin" <?php if ($user['role']=="admin") echo "selected"; ?>>Admin</option>
                            <option value="manager" <?php if ($user['role']=="manager") echo "selected"; ?>>Manager</option>
                            <option value="employee" <?php if ($user['role']=="employee") echo "selected"; ?>>Employee</option>
                            <option value="Store Incharge" <?php if ($user['role']=="Store Incharge") echo "selected"; ?>>Store Incharge</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">New Password</label>
                        <input type="password" class="form-control" name="password" id="password" placeholder="Enter new password (optional)">
                    </div>

                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm Password</label>
                        <input type="password" class="form-control" name="confirm_password" id="confirm_password" placeholder="Confirm new password">
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-submit"><i class="bi bi-save me-2"></i> Update User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script src="script.js"></script>
</body>
</html>

