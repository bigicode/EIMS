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

// Check if ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    set_message("Invalid user ID", "danger");
    header("Location: users.php");
    exit;
}

// Get user data
$user->id = $_GET['id'];
if (!$user->read_single()) {
    set_message("User not found", "danger");
    header("Location: users.php");
    exit;
}

// Handle form submission
$error_message = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Verify CSRF token
        Security::validateCSRFToken($_POST['csrf_token']);
        
        // Set user properties
        $user->email = $_POST['email'];
        $user->full_name = $_POST['full_name'];
        $user->department = $_POST['department'];
        
        // Update user
        if ($user->update()) {
            $success = true;
        }
        
        // Update role separately if changed
        if ($_POST['role'] !== $user->role) {
            $stmt = $db->prepare("UPDATE users SET role = ? WHERE id = ?");
            $stmt->execute([$_POST['role'], $user->id]);
            $user->role = $_POST['role']; // Update the role in the current object
        }
        
        // If password change is requested
        if (!empty($_POST['new_password'])) {
            $user->password = $_POST['new_password'];
            $user->update_password();
        }
        
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}

$page_title = 'Edit User';
include '../includes/header.php';

// Get CSRF token for form
$csrf_token = Security::generateCSRFToken();
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3"><i class="fas fa-user-edit me-2"></i>Edit User</h1>
        <a href="users.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Users
        </a>
    </div>
    
    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            User updated successfully!
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <?php if ($error_message): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($error_message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <div class="row">
        <!-- User Info Card -->
        <div class="col-md-4 mb-4">
            <div class="card shadow border-0 rounded-lg h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">User Information</h5>
                </div>
                <div class="card-body text-center">
                    <?php 
                    // Generate avatar
                    $colors = ['#4361ee', '#3a0ca3', '#7209b7', '#f72585', '#4cc9f0', '#4895ef', '#560bad', '#b5179e', '#ff006e', '#f15bb5'];
                    $color_index = hexdec(substr(md5($user->username), 0, 4)) % count($colors);
                    $avatar_color = $colors[$color_index];
                    $initials = strtoupper(substr($user->username, 0, 1));
                    ?>
                    <div class="avatar mx-auto mb-3" style="width: 100px; height: 100px; background-color: <?php echo $avatar_color; ?>; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <span style="color: white; font-size: 36px; font-weight: bold;"><?php echo $initials; ?></span>
                    </div>
                    <h4 class="card-title"><?php echo htmlspecialchars($user->full_name); ?></h4>
                    <p class="text-muted"><?php echo htmlspecialchars(ucfirst($user->role)); ?></p>
                    <hr>
                    <div class="text-start">
                        <p><i class="fas fa-user me-2"></i> <?php echo htmlspecialchars($user->username); ?></p>
                        <p><i class="fas fa-envelope me-2"></i> <?php echo htmlspecialchars($user->email); ?></p>
                        <p><i class="fas fa-building me-2"></i> <?php echo htmlspecialchars($user->department); ?></p>
                        <p><i class="fas fa-calendar me-2"></i> Created: <?php echo date('Y-m-d', strtotime($user->created_at)); ?></p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Edit Form -->
        <div class="col-md-8 mb-4">
            <div class="card shadow border-0 rounded-lg">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Edit User Details</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'] . '?id=' . $user->id); ?>">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" value="<?php echo htmlspecialchars($user->username); ?>" disabled>
                                <div class="form-text text-muted">Username cannot be changed</div>
                            </div>
                            <div class="col-md-6">
                                <label for="full_name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user->full_name); ?>" required>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user->email); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="department" class="form-label">Department <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="department" name="department" value="<?php echo htmlspecialchars($user->department); ?>" required>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
                                <select class="form-select" id="role" name="role" required>
                                    <option value="user" <?php echo $user->role === 'user' ? 'selected' : ''; ?>>User</option>
                                    <option value="admin" <?php echo $user->role === 'admin' ? 'selected' : ''; ?>>Administrator</option>
                                </select>
                            </div>
                        </div>
                        
                        <hr class="my-4">
                        <h5>Change Password (Optional)</h5>
                        <p class="text-muted mb-3">Leave blank to keep current password</p>
                        
                        <div class="mb-3">
                            <label for="new_password" class="form-label">New Password</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" oninput="checkPasswordStrength(this.value)">
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
                        
                        <div class="d-flex justify-content-end mt-4">
                            <a href="users.php" class="btn btn-secondary me-2">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
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