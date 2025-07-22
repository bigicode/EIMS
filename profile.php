<?php
require_once 'includes/functions.php';
require_once 'includes/auth.php';
require_once 'config/database.php';
require_once 'models/User.php';

// Require login for this page
require_login();

// Get user info from session
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$full_name = $_SESSION['full_name'];
$email = isset($_SESSION['email']) ? $_SESSION['email'] : '';
$department = $_SESSION['department'];
$role = $_SESSION['user_role'];

// Initialize database connection
$database = new Database();
$db = $database->getConnection();
$user = new User($db);
$user->id = $user_id;
$user->read_single();

// Handle form submissions
$profile_updated = false;
$password_updated = false;
$error_message = '';

// Profile update form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $user->email = $_POST['email'];
    $user->full_name = $_POST['full_name'];
    $user->department = $_POST['department'];

    try {
        if ($user->update()) {
            // Update session variables
            $_SESSION['full_name'] = $user->full_name;
            $_SESSION['email'] = $user->email;
            $_SESSION['department'] = $user->department;
            
            $profile_updated = true;
        }
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}

// Password change form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if ($new_password !== $confirm_password) {
        $error_message = "New password and confirmation don't match";
    } else {
        try {
            // Get current user data to verify password
            $check_user = new User($db);
            $check_user->id = $user_id;
            $check_user->read_single();
            
            // Verify current password before updating
            if (Security::verifyPassword($current_password, $check_user->password)) {
                $user->password = $new_password;
                if ($user->update_password()) {
                    $password_updated = true;
                }
            } else {
                $error_message = "Current password is incorrect";
            }
        } catch (Exception $e) {
            $error_message = $e->getMessage();
        }
    }
}

$page_title = 'Profile';
include 'includes/header.php';

// Generate random avatar color based on username
$colors = ['#4361ee', '#3a0ca3', '#7209b7', '#f72585', '#4cc9f0', '#4895ef', '#560bad', '#b5179e', '#ff006e', '#f15bb5'];
$color_index = hexdec(substr(md5($username), 0, 4)) % count($colors);
$avatar_color = $colors[$color_index];
$initials = strtoupper(substr($username, 0, 1));

// CSRF token for form security
$csrf_token = Security::generateCSRFToken();
?>

<div class="container py-4">
    <?php if ($profile_updated): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            Profile updated successfully!
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if ($password_updated): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            Password changed successfully!
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
        <!-- Profile Overview Card -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow border-0 rounded-lg h-100">
                <div class="card-body text-center">
                    <div class="avatar mx-auto mb-3" style="width: 100px; height: 100px; background-color: <?php echo $avatar_color; ?>; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <span style="color: white; font-size: 36px; font-weight: bold;"><?php echo $initials; ?></span>
                    </div>
                    <h3 class="card-title"><?php echo htmlspecialchars($full_name); ?></h3>
                    <p class="text-muted"><?php echo htmlspecialchars(ucfirst($role)); ?></p>
                    <hr>
                    <div class="text-start">
                        <p><i class="fas fa-user me-2"></i> <?php echo htmlspecialchars($username); ?></p>
                        <p><i class="fas fa-envelope me-2"></i> <?php echo htmlspecialchars($email); ?></p>
                        <p><i class="fas fa-building me-2"></i> <?php echo htmlspecialchars($department); ?></p>
                        <p><i class="fas fa-id-badge me-2"></i> User ID: <?php echo htmlspecialchars($user_id); ?></p>
                    </div>
                    <div class="mt-3">
                        <button class="btn btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#editProfileForm" aria-expanded="false">
                            <i class="fas fa-edit me-1"></i> Edit Profile
                        </button>
                        <button class="btn btn-secondary ms-2" type="button" data-bs-toggle="collapse" data-bs-target="#changePasswordForm" aria-expanded="false">
                            <i class="fas fa-key me-1"></i> Change Password
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Forms Column -->
        <div class="col-lg-8 mb-4">
            <!-- Edit Profile Form -->
            <div class="collapse mb-4" id="editProfileForm">
                <div class="card shadow border-0 rounded-lg">
            <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-user-edit me-2"></i>Edit Profile Information</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                            
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" value="<?php echo htmlspecialchars($username); ?>" disabled>
                                <div class="form-text text-muted">Username cannot be changed</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="full_name" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="full_name" name="full_name" value="<?php echo htmlspecialchars($full_name); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="department" class="form-label">Department</label>
                                <input type="text" class="form-control" id="department" name="department" value="<?php echo htmlspecialchars($department); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="role" class="form-label">Role</label>
                                <input type="text" class="form-control" id="role" value="<?php echo htmlspecialchars(ucfirst($role)); ?>" disabled>
                                <div class="form-text text-muted">Role can only be changed by administrators</div>
                            </div>
                            
                            <div class="d-flex justify-content-end">
                                <button type="button" class="btn btn-secondary me-2" data-bs-toggle="collapse" data-bs-target="#editProfileForm">Cancel</button>
                                <button type="submit" name="update_profile" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> Save Changes
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Change Password Form -->
            <div class="collapse mb-4" id="changePasswordForm">
                <div class="card shadow border-0 rounded-lg">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-key me-2"></i>Change Password</h5>
            </div>
            <div class="card-body">
                        <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                            
                            <div class="mb-3">
                                <label for="current_password" class="form-label">Current Password</label>
                                <input type="password" class="form-control" id="current_password" name="current_password" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="new_password" class="form-label">New Password</label>
                                <input type="password" class="form-control" id="new_password" name="new_password" required oninput="checkPasswordStrength(this.value)">
                                <div class="mt-2">
                                    <div class="password-strength-meter">
                                        <div class="progress" style="height: 5px;">
                                            <div id="password-strength-bar" class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                        <small id="password-strength-text" class="form-text mt-1">Password strength: Too weak</small>
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
                            
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirm New Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                            
                            <div class="d-flex justify-content-end">
                                <button type="button" class="btn btn-secondary me-2" data-bs-toggle="collapse" data-bs-target="#changePasswordForm">Cancel</button>
                                <button type="submit" name="change_password" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> Change Password
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Admin Section (visible only to admins) -->
            <?php if ($role === 'admin'): ?>
            <div class="card shadow border-0 rounded-lg">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0"><i class="fas fa-shield-alt me-2"></i>Admin Tools</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="card border-0 bg-light h-100">
                                <div class="card-body">
                                    <h5 class="card-title"><i class="fas fa-users me-2"></i>User Management</h5>
                                    <p class="card-text">Manage user accounts, roles, and permissions</p>
                                    <a href="admin/users.php" class="btn btn-sm btn-primary">Go to User Management</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="card border-0 bg-light h-100">
                                <div class="card-body">
                                    <h5 class="card-title"><i class="fas fa-cogs me-2"></i>System Settings</h5>
                                    <p class="card-text">Configure system settings and preferences</p>
                                    <a href="admin/settings.php" class="btn btn-sm btn-primary">Go to System Settings</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="card border-0 bg-light h-100">
                                <div class="card-body">
                                    <h5 class="card-title"><i class="fas fa-database me-2"></i>Database Tools</h5>
                                    <p class="card-text">Manage database operations and maintenance</p>
                                    <a href="admin/database.php" class="btn btn-sm btn-primary">Go to Database Tools</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="card border-0 bg-light h-100">
                                <div class="card-body">
                                    <h5 class="card-title"><i class="fas fa-file-alt me-2"></i>System Logs</h5>
                                    <p class="card-text">View system logs and activity history</p>
                                    <a href="admin/logs.php" class="btn btn-sm btn-primary">View Logs</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Add Font Awesome for icons -->
<script defer src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/js/all.min.js"></script>

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

// Confirm password validation
document.getElementById('confirm_password')?.addEventListener('input', function() {
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = this.value;
    
    if (newPassword === confirmPassword) {
        this.classList.remove('is-invalid');
        this.classList.add('is-valid');
    } else {
        this.classList.remove('is-valid');
        this.classList.add('is-invalid');
    }
});
</script>

<?php include 'includes/footer.php'; ?> 