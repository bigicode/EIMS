<?php
// Environment configuration
define('ENVIRONMENT', 'development'); // Change to 'production' in production

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'emis');
define('DB_USER', 'root');
define('DB_PASS', '');

// Application configuration
define('APP_NAME', 'EIMS');
define('APP_URL', 'http://localhost/shop');
define('SESSION_LIFETIME', 3600); // 1 hour

// Security configuration
define('CSRF_TOKEN_SECRET', 'your-secret-key-here'); // Change this in production
define('PASSWORD_HASH_COST', 12);

// Error reporting
if (ENVIRONMENT === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}
?> 