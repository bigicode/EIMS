<?php
require_once 'includes/functions.php';

// Redirect to dashboard if logged in, otherwise to login page
if(is_logged_in()) {
    redirect("dashboard.php");
} else {
    redirect("views/auth/login.php");
}
?>