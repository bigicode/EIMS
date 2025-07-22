<?php
require_once '../includes/functions.php';
require_once '../includes/auth.php';
require_once '../config/database.php';
require_once '../models/User.php';

// Require login and admin role
require_login();
require_admin();

// Initialize database connection
$database = new Database();
$db = $database->getConnection();
$user = new User($db);

// Handle form submission
$error_message = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Verify CSRF token
        Security::validateCSRFToken($_POST['csrf_token']);
        
        // Set user properties
        $user->username = $_POST['username'];
        $user->password = $_POST['password'];
        $user->email = $_POST['email'];
        $user->full_name = $_POST['full_name'];
        $user->department = $_POST['department'];
        $user->role = $_POST['role'];
        
        // Create user
        if ($user->create()) {
            $success = true;
        }
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}

$page_title = 'Create User';
include '../includes/header.php';

// Get CSRF token for form
$csrf_token = Security::generateCSRFToken();
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3"><i class="fas fa-user-plus me-2"></i>Create New User</h1>
        <a href="users.php" class="btn btn-outline-blue">
            <i class="fas fa-arrow-left me-1"></i> Back to Users
        </a>
    </div>
    
    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            User created successfully!
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <div class="text-center mb-4">
            <a href="users.php" class="btn btn-outline-blue">Return to User List</a>
            <a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="btn btn-outline-blue ms-2">Create Another User</a>
        </div>
    <?php else: ?>
        <?php if ($error_message): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($error_message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <div class="card shadow border-0 rounded-lg">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">User Information</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        <div class="col-md-6">
                            <label for="full_name" class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="full_name" name="full_name" required>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="col-md-6">
                            <label for="department" class="form-label">Department <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="department" name="department" required>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="password" name="password" required oninput="checkPasswordStrength(this.value)">
                            <div class="mt-2">
                                <div class="password-strength-meter">
                                    <div class="progress" style="height: 5px;">
                                        <div id="password-strength-bar" class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <small id="password-strength-text" class="form-text mt-1">Password strength: Not provided</small>
                                </div>
                            </div>
                            <div class="form-text mt-2">
                                Password must be at least 8 characters long and contain:
                                <ul class="mb-0 ps-3">
                                    <li>Uppercase letter (A-Z)</li>
                                    <li>Lowercase letter (a-z)</li>
                                    <li>Number (0-9)</li>
                                    <li>Special character (@$!%*?&)</li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
                            <select class="form-select" id="role" name="role" required>
                                <option value="user">User</option>
                                <option value="admin">Administrator</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-end mt-4">
                        <a href="users.php" class="btn btn-outline-blue me-2">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Create User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Password strength checking script -->
<script>
function checkPasswordStrength(password) {
    // Initialize variables
    let strength = 0;
    const progressBar = document.getElementById('password-strength-bar');
    const strengthText = document.getElementById('password-strength-text');
    
    if (password.length === 0) {
        progressBar.style.width = '0%';
        progressBar.className = 'progress-bar';
        strengthText.textContent = 'Password strength: Not provided';
        return;
    }
    
    // Check for length (at least 8 characters)
    if (password.length >= 8) {
        strength += 1;
    }
    
    // Check for uppercase letters
    if (password.match(/[A-Z]/)) {
        strength += 1;
    }
    
    // Check for lowercase letters
    if (password.match(/[a-z]/)) {
        strength += 1;
    }
    
    // Check for numbers
    if (password.match(/[0-9]/)) {
        strength += 1;
    }
    
    // Check for special characters
    if (password.match(/[^A-Za-z0-9]/)) {
        strength += 1;
    }
    
    // Update the strength meter
    switch (strength) {
        case 0:
        case 1:
            progressBar.style.width = '20%';
            progressBar.className = 'progress-bar bg-danger';
            strengthText.textContent = 'Password strength: Too weak';
            break;
        case 2:
            progressBar.style.width = '40%';
            progressBar.className = 'progress-bar bg-warning';
            strengthText.textContent = 'Password strength: Weak';
            break;
        case 3:
            progressBar.style.width = '60%';
            progressBar.className = 'progress-bar bg-info';
            strengthText.textContent = 'Password strength: Fair';
            break;
        case 4:
            progressBar.style.width = '80%';
            progressBar.className = 'progress-bar bg-primary';
            strengthText.textContent = 'Password strength: Good';
            break;
        case 5:
            progressBar.style.width = '100%';
            progressBar.className = 'progress-bar bg-success';
            strengthText.textContent = 'Password strength: Strong';
            break;
    }
}
</script>

<?php include '../includes/footer.php'; ?> 