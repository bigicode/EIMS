<?php
require_once 'includes/functions.php';

// Destroy the session
session_destroy();

// Set logout message
set_message("You have been logged out successfully.", "info");

// Redirect to login page
redirect("views/auth/login.php");
?>