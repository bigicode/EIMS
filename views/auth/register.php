<?php
// Set relative path for includes
$base_path = '../../';
require_once $base_path . 'config/database.php';
require_once $base_path . 'models/User.php';
require_once $base_path . 'includes/functions.php';
require_once $base_path . 'includes/auth.php';

// Only admin can access this page
require_admin();

// Process registration form
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get database connection
    $database = new Database();
    $db = $database->getConnection();
    
    // Initialize user object
    $user = new User($db);
    
    // Set properties
    $user->username = clean_input($_POST['username']);
    $user->email = clean_input($_POST['email']);
    $user->full_name = clean_input($_POST['full_name']);
    $user->department = clean_input($_POST['department']);
    $user->role = clean_input($_POST['role']);
    $password = clean_input($_POST['password']);
    $confirm_password = clean_input($_POST['confirm_password']);
    
    // Validate passwords match
    if($password !== $confirm_password) {
        set_message("Passwords do not match.", "danger");
    } 
    // Check if username already exists
    else if($user->username_exists()) {
        set_message("Username already exists.", "danger");
    }
    // Check if email already exists
    else if($user->email_exists()) {
        set_message("Email already exists.", "danger");
    }
    // Create user
    else if($user->create($password)) {
        set_message("User registered successfully.", "success");
        redirect($base_path . 'views/dashboard/admin.php');
    } else {
        set_message("Failed to register user.", "danger");
    }
}

// Include header
$page_title = "Register New User";
include $base_path . 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Register New User</h4>
            </div>
            <div class="card-body">
                <form action="" method="post">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="full_name" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="full_name" name="full_name" required>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="department" class="form-label">Department</label>
                            <select class="form-select" id="department" name="department" required>
                                <option value="">Select Department</option>
                                <option value="IT">IT</option>
                                <option value="HR">HR</option>
                                <option value="Finance">Finance</option>
                                <option value="Marketing">Marketing</option>
                                <option value="Operations">Operations</option>
                                <option value="Sales">Sales</option>
                                <option value="Research">Research</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="role" class="form-label">Role</label>
                            <select class="form-select" id="role" name="role" required>
                                <option value="user">User</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="col-md-6">
                            <label for="confirm_password" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between">
                        <a href="../dashboard/admin.php" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Register User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include $base_path . 'includes/footer.php'; ?>