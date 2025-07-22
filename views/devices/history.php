<?php
require_once '../../controllers/DeviceController.php';
require_once '../../includes/functions.php';
// session_start(); // Already started in main app

if (!is_admin()) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $data = $_POST;
    $data['id'] = $id;
    $controller = new DeviceController();
    $result = $controller->updateDevice($data);
    if ($result['success']) {
        set_message('Device lifecycle details updated successfully.', 'success');
    } else {
        set_message('Failed to update device: ' . $result['message'], 'danger');
    }
    header('Location: index.php');
    exit;
} else {
    header('Location: index.php');
    exit;
} 