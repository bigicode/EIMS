<?php
// Set relative path for includes
$base_path = '../../';
require_once $base_path . 'config/config.php';
require_once $base_path . 'config/database.php';
require_once $base_path . 'models/User.php';
require_once $base_path . 'includes/functions.php';
require_once $base_path . 'includes/Security.php';

// Redirect if already logged in
if(is_logged_in()) {
    redirect($base_path . 'dashboard.php');
}

$error = '';

// Process login form
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Validate CSRF token
        Security::validateCSRFToken($_POST['csrf_token'] ?? '');
        
        // Get database connection
        $database = new Database();
        $db = $database->getConnection();
        
        // Initialize user object
        $user = new User($db);
        
        // Set properties
        $user->username = clean_input($_POST['username']);
        $password = $_POST['password'];
        
        // Attempt login
        if($user->login($password)) {
            // Set session variables
            $_SESSION['user_id'] = $user->id;
            $_SESSION['username'] = $user->username;
            $_SESSION['user_role'] = $user->role;
            $_SESSION['full_name'] = $user->full_name;
            $_SESSION['department'] = $user->department;
            
            // Regenerate session ID after successful login
            session_regenerate_id(true);
            
            // Redirect based on role
            set_message("Welcome back, " . Security::sanitizeOutput($user->full_name) . "!", "success");
            redirect($base_path . 'dashboard.php');
        } else {
            $error = "Invalid username or password.";
        }
    } catch (Exception $e) {
        $error = ENVIRONMENT === 'development' ? $e->getMessage() : "An error occurred. Please try again.";
    }
}

// Generate CSRF token
$csrf_token = Security::generateCSRFToken();

// Include header
$page_title = "Login";
include $base_path . 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Login to <?php echo Security::sanitizeOutput(APP_NAME); ?></h4>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo Security::sanitizeOutput($error); ?></div>
                <?php endif; ?>
                
                <form action="" method="post">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" required 
                               value="<?php echo isset($_POST['username']) ? Security::sanitizeOutput($_POST['username']) : ''; ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">Login</button>
                    </div>
                </form>
            </div>
            <div class="card-footer text-center">
                <p class="mb-0">Don't have an account? Contact your administrator.</p>
            </div>
        </div>
    </div>
</div>

<?php include $base_path . 'includes/footer.php'; ?>