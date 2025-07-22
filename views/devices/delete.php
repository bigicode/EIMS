<?php
$base_path = '../../';
require_once $base_path . 'config/database.php';
require_once $base_path . 'models/Device.php';
require_once $base_path . 'includes/functions.php';
require_once $base_path . 'includes/auth.php';

require_admin();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    set_message('Invalid device ID.', 'danger');
    header('Location: index.php');
    exit;
}

$device_id = intval($_GET['id']);
$database = new Database();
$db = $database->getConnection();
$device = new Device($db);
$device->id = $device_id;

if ($device->delete()) {
    set_message('Device deleted successfully.', 'success');
} else {
    set_message('Failed to delete device.', 'danger');
}
header('Location: index.php');
exit; 