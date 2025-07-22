<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Device.php';
require_once __DIR__ . '/../controllers/DeviceController.php';

// This script runs automated lifecycle management tasks
// It should be set up as a daily cron job

// Initialize controller
$deviceController = new DeviceController();

// 1. Update all health scores and recommendations
$healthUpdate = $deviceController->updateAllHealthScores();
echo "Updated health scores for {$healthUpdate['devices_updated']} devices.\n";

// 2. Schedule maintenance for devices that need it
$maintenanceScheduled = $deviceController->scheduleAllMaintenance();
echo "Scheduled maintenance for {$maintenanceScheduled['devices_scheduled']} devices.\n";

// 3. Generate alerts for devices nearing end of life
$devices = $deviceController->getDevices();
$alertCount = 0;

while ($row = $devices->fetch(PDO::FETCH_ASSOC)) {
    $device = $deviceController->getDeviceById($row['id']);
    
    if ($device) {
        $stage = $deviceController->getDeviceLifecycleStage($row['id']);
        
        if ($stage == "End-of-Life") {
            // Create alert
            $recommendation = $deviceController->getReplacementRecommendation($row['id']);
            
            // Here you could also send email notifications
            // For now, we just update the alert field
            $device->alert = "END-OF-LIFE: {$recommendation['reason']} Urgency: {$recommendation['urgency']}";
            $device->update();
            
            $alertCount++;
        }
    }
}

echo "Generated alerts for {$alertCount} devices at end of life.\n";
?> 