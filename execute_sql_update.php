<?php
// Script to execute the SQL update for the maintenance table

// Include database connection
require_once 'config/database.php';

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Read SQL file
$sql_file = file_get_contents('updates/maintenance_table_update.sql');

try {
    // Execute the SQL
    $result = $db->exec($sql_file);
    echo "SQL executed successfully!<br>";
    echo "The maintenance table has been updated with the new columns.";
} catch (PDOException $e) {
    echo "Error executing SQL: " . $e->getMessage();
}
?> 