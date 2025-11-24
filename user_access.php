<?php
include 'conn.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name     = $_POST['full_name'];
    $email    = $_POST['email'];
    $mobile   = $_POST['phone'];
    $role     = $_POST['role_name'];
    $password = $_POST['password'];
    $confirm  = $_POST['confirm_password'];

    if ($password !== $confirm) {
        $error_message = "❌ Password and Confirm Password do not match!";
    } else {
        // Hash the password before storing
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        $stmt = $conn->prepare("INSERT INTO users (name, email, mobile, password, role, created_at, updated_at) 
                                VALUES (?, ?, ?, ?, ?, NOW(), NOW())");
        $stmt->bind_param("sssss", $name, $email, $mobile, $hashedPassword, $role);

        if ($stmt->execute()) {
            $success_message = "✅ User access added successfully!";
        } else {
            $error_message = "❌ Error adding user access: " . $conn->error;
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
<title>Add User Access</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<link href="style.css" rel="stylesheet">
<style>
    body { background-color: #f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
    .card { border-radius: 12px; box-shadow: 0 0 15px rgba(0,0,0,0.1); background: linear-gradient(135deg, #4e89ae, #7da6c3); color: #fff; }
    .card-header { font-weight: bold; font-size: 1.2rem; background: rgba(255,255,255,0.1); border-bottom: 1px solid rgba(255,255,255,0.2); }
    .form-label { font-weight: 500; color: #fff; }
    .form-control, select { border-radius: 8px; background-color: rgba(255,255,255,0.9); border: 1px solid rgba(255,255,255,0.5); color: #333; }
    .form-control:focus, select:focus { background-color: #fff; border-color: #4e89ae; box-shadow: 0 0 0 0.2rem rgba(78, 137, 174, 0.25); }
    .btn-submit { background-color: #1c1c1c; color: #fff; width: 100%; border-radius: 8px; border: none; }
    .btn-submit:hover { background-color: #333; }
    .alert { border-radius: 8px; margin-bottom: 1rem; }
</style>
</head>
<body>

<div id="navbar-placeholder"></div>

<div class="container my-1">
    <div class="row justify-content-start">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-5">
            <a href="admin_dashboard.php" class="btn btn-dark">
                <i class="fas fa-arrow-left me-2"></i> Back
            </a>
        </div>
    </div>
</div>

<div class="container my-12">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card p-4">
                <div class="card-header mb-3 text-center">
                    <i class="bi bi-person-badge me-2"></i> Add User Access
                </div>

                <?php if (isset($success_message)): ?>
                    <div class="alert alert-success" role="alert">
                        <i class="bi bi-check-circle me-2"></i><?php echo $success_message; ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger" role="alert">
                        <i class="bi bi-exclamation-triangle me-2"></i><?php echo $error_message; ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="row g-3 mb-3">
                        <div class="col-12">
                            <label for="full_name" class="form-label">
                                <i class="bi bi-person-circle me-1"></i> Full Name
                            </label>
                            <input type="text" class="form-control" name="full_name" id="full_name" placeholder="Enter full name" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12">
                            <label for="email" class="form-label">
                                <i class="bi bi-envelope me-1"></i> Email
                            </label>
                            <input type="email" class="form-control" name="email" id="email" placeholder="Enter email" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12">
                            <label for="phone" class="form-label">
                                <i class="bi bi-telephone me-1"></i> Phone
                            </label>
                            <input type="text" class="form-control" name="phone" id="phone" placeholder="Enter phone number" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12">
                            <label for="password" class="form-label">
                                <i class="bi bi-key me-1"></i> Password
                            </label>
                            <input type="password" class="form-control" name="password" id="password" placeholder="Enter password" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12">
                            <label for="confirm_password" class="form-label">
                                <i class="bi bi-key-fill me-1"></i> Confirm Password
                            </label>
                            <input type="password" class="form-control" name="confirm_password" id="confirm_password" placeholder="Confirm password" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12">
                            <label for="role_name" class="form-label">Role Name</label>
                            <select class="form-select" name="role_name" id="role_name" required>
                                <option value="">Select Role</option>
                                <option value="Admin">Admin</option>
                                <option value="Manager">Manager</option>
                                <option value="employee">Employee</option>
                                <option value="Store Incharge">Store Incharge</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-submit">
                            <i class="bi bi-save me-2"></i> Save User Access
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="script.js"></script>

</body>
</html>