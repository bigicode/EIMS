<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Device.php';
require_once __DIR__ . '/../controllers/DeviceController.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

// Only admin can access this page
require_admin();

// Initialize device controller
$deviceController = new DeviceController();

// Update all health scores and recommendations
$healthUpdate = $deviceController->updateAllHealthScores();

// Schedule maintenance for devices that need it
$maintenanceScheduled = $deviceController->scheduleAllMaintenance();

// Set success message
set_message("Lifecycle data updated for {$healthUpdate['devices_updated']} devices. Maintenance scheduled for {$maintenanceScheduled['devices_scheduled']} devices.", 'success');

// Redirect back to admin dashboard
header('Location: ../views/dashboard/admin.php');
exit(); 