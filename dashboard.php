<?php
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Check if user is logged in
if(!is_logged_in()) {
    set_message("You must log in to access the dashboard.", "warning");
    redirect("views/auth/login.php");
}

// Redirect based on user role
if(is_admin()) {
    redirect("views/dashboard/admin.php");
} else {
    redirect("views/dashboard/user.php");
}
?>