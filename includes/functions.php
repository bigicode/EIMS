<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Clean input data
function clean_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Check if user is logged in
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Check if user is admin
function is_admin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin';
}

// Require admin role
function require_admin() {
    require_login();
    if (!is_admin()) {
        set_message("Access denied. Administrator privileges required.", "danger");
        redirect("../index.php");
        exit;
    }
}

// Redirect to a specific page
function redirect($url) {
    header("Location: $url");
    exit();
}

// Display flash messages
function display_message() {
    if(isset($_SESSION['message'])) {
        $message = $_SESSION['message'];
        $message_class = isset($_SESSION['message_class']) ? $_SESSION['message_class'] : 'info';
        
        echo "<div class='alert alert-{$message_class} alert-dismissible fade show' role='alert'>";
        echo $message;
        echo "<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>";
        echo "</div>";
        
        unset($_SESSION['message']);
        unset($_SESSION['message_class']);
    }
}

// Set flash message
function set_message($message, $class = 'info') {
    $_SESSION['message'] = $message;
    $_SESSION['message_class'] = $class;
}

// Format date
function format_date($date) {
    return date("F d, Y", strtotime($date));
}

// Calculate days between two dates
function days_between($date1, $date2) {
    $diff = strtotime($date2) - strtotime($date1);
    return abs(round($diff / 86400));
}

// Generate device status badge
function device_status_badge($status) {
    $badge_class = '';
    switch($status) {
        case 'active':
            $badge_class = 'success';
            break;
        case 'maintenance':
            $badge_class = 'warning';
            break;
        case 'repair':
            $badge_class = 'danger';
            break;
        case 'disposed':
            $badge_class = 'secondary';
            break;
        default:
            $badge_class = 'info';
    }
    
    return "<span class='badge bg-{$badge_class}'>{$status}</span>";
}

// Generate issue priority badge
function issue_priority_badge($priority) {
    $badge_class = '';
    switch($priority) {
        case 'low':
            $badge_class = 'info';
            break;
        case 'medium':
            $badge_class = 'warning';
            break;
        case 'high':
            $badge_class = 'danger';
            break;
        case 'critical':
            $badge_class = 'dark';
            break;
        default:
            $badge_class = 'secondary';
    }
    
    return "<span class='badge bg-{$badge_class}'>{$priority}</span>";
}

// Generate issue status badge
function issue_status_badge($status) {
    $badge_class = '';
    switch($status) {
        case 'open':
            $badge_class = 'danger';
            break;
        case 'in_progress':
            $badge_class = 'warning';
            break;
        case 'resolved':
            $badge_class = 'success';
            break;
        case 'closed':
            $badge_class = 'secondary';
            break;
        default:
            $badge_class = 'info';
    }
    
    return "<span class='badge bg-{$badge_class}'>{$status}</span>";
}

function log_action($user_id, $action, $description) {
    require_once __DIR__ . '/../models/Log.php';
    $db = (new Database())->getConnection();
    $log = new Log($db);
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? null;
    $log->add($user_id, $action, $description, $ip_address);
}
?>