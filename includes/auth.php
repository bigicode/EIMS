<?php
require_once 'functions.php';

// Check if user is logged in, if not redirect to login page
function require_login() {
    if(!is_logged_in()) {
        set_message("You must log in to access this page.", "warning");
        redirect("../auth/login.php");
    }
}
?>
