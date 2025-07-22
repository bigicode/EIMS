<?php
require_once 'config/config.php';
require_once 'includes/Security.php';

$password = 'adam34';
$hash = Security::hashPassword($password);

// Print and flush output
print "Password: $password\n";
print "Hash: $hash\n";
flush();
?> 