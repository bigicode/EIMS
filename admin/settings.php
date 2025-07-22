<?php
require_once '../includes/Security.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
require_once '../config/database.php';

// Require login and admin role
require_login();
require_admin();

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Define settings categories and their settings
$settings_categories = [
    'general' => [
        'title' => 'General Settings',
        'icon' => 'fas fa-cog',
        'settings' => [
            'site_name' => [
                'label' => 'Site Name',
                'type' => 'text',
                'description' => 'The name of the site displayed in the header and title'
            ],
            'company_name' => [
                'label' => 'Company Name',
                'type' => 'text',
                'description' => 'The company name used in reports and documents'
            ],
            'contact_email' => [
                'label' => 'Contact Email',
                'type' => 'email',
                'description' => 'The primary contact email for system notifications'
            ]
        ]
    ],
    'security' => [
        'title' => 'Security Settings',
        'icon' => 'fas fa-shield-alt',
        'settings' => [
            'password_expiry_days' => [
                'label' => 'Password Expiry (Days)',
                'type' => 'number',
                'description' => 'Number of days before passwords expire (0 = never)'
            ],
            'max_login_attempts' => [
                'label' => 'Max Login Attempts',
                'type' => 'number',
                'description' => 'Maximum number of failed login attempts before account lockout'
            ],
            'session_timeout' => [
                'label' => 'Session Timeout (Minutes)',
                'type' => 'number',
                'description' => 'Minutes of inactivity before session expires'
            ]
        ]
    ],
    'maintenance' => [
        'title' => 'Maintenance Settings',
        'icon' => 'fas fa-tools',
        'settings' => [
            'default_maintenance_interval' => [
                'label' => 'Default Maintenance Interval (Days)',
                'type' => 'number',
                'description' => 'Default number of days between scheduled maintenance'
            ],
            'maintenance_reminder_days' => [
                'label' => 'Maintenance Reminder (Days)',
                'type' => 'number',
                'description' => 'Days before scheduled maintenance to send reminder'
            ],
            'auto_schedule_maintenance' => [
                'label' => 'Auto Schedule Maintenance',
                'type' => 'checkbox',
                'description' => 'Automatically schedule next maintenance after completion'
            ]
        ]
    ],
    'notifications' => [
        'title' => 'Notification Settings',
        'icon' => 'fas fa-bell',
        'settings' => [
            'email_notifications' => [
                'label' => 'Email Notifications',
                'type' => 'checkbox',
                'description' => 'Send email notifications for important events'
            ],
            'maintenance_notifications' => [
                'label' => 'Maintenance Notifications',
                'type' => 'checkbox',
                'description' => 'Send notifications for upcoming and overdue maintenance'
            ],
            'issue_notifications' => [
                'label' => 'Issue Notifications',
                'type' => 'checkbox',
                'description' => 'Send notifications for new and updated issues'
            ]
        ]
    ]
];

// Check if settings table exists, create if not
try {
    $check_table = $db->query("SHOW TABLES LIKE 'settings'");
    if ($check_table->rowCount() == 0) {
        // Create settings table
        $db->exec("CREATE TABLE settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            setting_key VARCHAR(50) NOT NULL UNIQUE,
            setting_value TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )");
        
        // Insert default settings
        $default_settings = [
            'site_name' => 'Device Management System',
            'company_name' => 'Your Company',
            'contact_email' => 'admin@example.com',
            'password_expiry_days' => '90',
            'max_login_attempts' => '5',
            'session_timeout' => '30',
            'default_maintenance_interval' => '90',
            'maintenance_reminder_days' => '7',
            'auto_schedule_maintenance' => '1',
            'email_notifications' => '1',
            'maintenance_notifications' => '1',
            'issue_notifications' => '1'
        ];
        
        $insert_stmt = $db->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)");
        foreach ($default_settings as $key => $value) {
            $insert_stmt->execute([$key, $value]);
        }
    }
} catch (PDOException $e) {
    // Handle error
    set_message("Database error: " . $e->getMessage(), "danger");
}

// Load current settings
$settings = [];
try {
    $stmt = $db->query("SELECT setting_key, setting_value FROM settings");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
} catch (PDOException $e) {
    set_message("Error loading settings: " . $e->getMessage(), "danger");
}

// Handle form submission
$success = false;
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_settings'])) {
    try {
        // Verify CSRF token
        Security::validateCSRFToken($_POST['csrf_token']);
        
        // Update settings
        $update_stmt = $db->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
        
        foreach ($settings_categories as $category => $category_data) {
            foreach ($category_data['settings'] as $key => $setting) {
                $value = isset($_POST[$key]) ? $_POST[$key] : '0';
                
                // For checkboxes, set value to 1 if checked, 0 if not
                if ($setting['type'] === 'checkbox') {
                    $value = isset($_POST[$key]) ? '1' : '0';
                }
                
                $update_stmt->execute([$value, $key]);
                $settings[$key] = $value; // Update local settings array
            }
        }
        
        $success = true;
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}

$page_title = 'System Settings';
include '../includes/header.php';

// Get CSRF token for form
$csrf_token = Security::generateCSRFToken();
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3"><i class="fas fa-cogs me-2"></i>System Settings</h1>
        <a href="../profile.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Profile
        </a>
    </div>
    
    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            Settings updated successfully!
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <?php if ($error_message): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($error_message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <div class="card shadow border-0 rounded-lg">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Manage System Settings</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                
                <ul class="nav nav-tabs mb-4" id="settingsTabs" role="tablist">
                    <?php $first = true; foreach ($settings_categories as $category_id => $category): ?>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link <?php echo $first ? 'active' : ''; ?>" 
                                    id="<?php echo $category_id; ?>-tab" 
                                    data-bs-toggle="tab" 
                                    data-bs-target="#<?php echo $category_id; ?>" 
                                    type="button" 
                                    role="tab" 
                                    aria-controls="<?php echo $category_id; ?>" 
                                    aria-selected="<?php echo $first ? 'true' : 'false'; ?>">
                                <i class="<?php echo $category['icon']; ?> me-1"></i> <?php echo $category['title']; ?>
                            </button>
                        </li>
                    <?php $first = false; endforeach; ?>
                </ul>
                
                <div class="tab-content" id="settingsTabContent">
                    <?php $first = true; foreach ($settings_categories as $category_id => $category): ?>
                        <div class="tab-pane fade <?php echo $first ? 'show active' : ''; ?>" 
                             id="<?php echo $category_id; ?>" 
                             role="tabpanel" 
                             aria-labelledby="<?php echo $category_id; ?>-tab">
                            
                            <div class="row">
                                <?php foreach ($category['settings'] as $key => $setting): ?>
                                    <div class="col-md-6 mb-3">
                                        <div class="card h-100">
                                            <div class="card-body">
                                                <?php if ($setting['type'] === 'checkbox'): ?>
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" id="<?php echo $key; ?>" name="<?php echo $key; ?>" <?php echo isset($settings[$key]) && $settings[$key] == '1' ? 'checked' : ''; ?>>
                                                        <label class="form-check-label" for="<?php echo $key; ?>"><?php echo $setting['label']; ?></label>
                                                    </div>
                                                <?php else: ?>
                                                    <label for="<?php echo $key; ?>" class="form-label"><?php echo $setting['label']; ?></label>
                                                    <input type="<?php echo $setting['type']; ?>" class="form-control" id="<?php echo $key; ?>" name="<?php echo $key; ?>" value="<?php echo isset($settings[$key]) ? htmlspecialchars($settings[$key]) : ''; ?>">
                                                <?php endif; ?>
                                                <small class="form-text text-muted"><?php echo $setting['description']; ?></small>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php $first = false; endforeach; ?>
                </div>
                
                <div class="d-flex justify-content-end mt-4">
                    <button type="reset" class="btn btn-secondary me-2">Reset</button>
                    <button type="submit" name="save_settings" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Save Settings
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?> 