<?php
/**
 * This script is intended to be run as a cron job to automatically clean up 
 * users who have been in deleted state for more than 7 days.
 * 
 * Recommended cron schedule: Once daily
 * Example: 0 3 * * * php /path/to/cleanup_deleted_users.php
 */

// Set to true when running via command line, false when testing via browser
$is_cron = php_sapi_name() == 'cli';

// Path resolution
$base_path = dirname(__DIR__) . '/';

// Include required files
require_once $base_path . 'config/database.php';
require_once $base_path . 'models/User.php';

// Initialize the database connection
$database = new Database();
$db = $database->getConnection();

// Initialize user model
$user = new User($db);

// Run the cleanup process
$deleted_count = $user->cleanup_old_deleted_users();

// Output the results
$message = "Auto-cleanup completed: $deleted_count user(s) permanently deleted after 7 days.";

if ($is_cron) {
    // Log to system log if running as cron job
    error_log("EMIS User Cleanup: $message");
} else {
    // Output for browser if run manually
    echo "<p>$message</p>";
}

// Exit with success code
exit(0); 