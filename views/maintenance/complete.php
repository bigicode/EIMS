<?php
$base_path = '../../';
require_once $base_path . 'config/database.php';
require_once $base_path . 'models/Maintenance.php';
require_once $base_path . 'models/Device.php';
require_once $base_path . 'controllers/MaintenenceController.php';
require_once $base_path . 'includes/functions.php';
require_once $base_path . 'includes/auth.php';

require_admin();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    set_message('Invalid maintenance ID.', 'danger');
    header('Location: index.php');
    exit;
}

$maintenance_id = intval($_GET['id']);
$database = new Database();
$db = $database->getConnection();
$maintenance = new Maintenance($db);
$maintenance->id = $maintenance_id;

// Get device information
$device = new Device($db);

if (!$maintenance->read_single()) {
    set_message('Maintenance record not found.', 'danger');
    header('Location: index.php');
    exit;
}

// Get device details
$device->id = $maintenance->device_id;
$device->read_single();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $controller = new MaintenanceController();
    
    $data = [
        'id' => $maintenance_id,
        'device_id' => $maintenance->device_id,
        'maintenance_type' => $maintenance->maintenance_type,
        'description' => $maintenance->description,
        'scheduled_date' => $maintenance->scheduled_date,
        'status' => 'completed',
        'notes' => clean_input($_POST['notes']),
        'maintenance_cost' => clean_input($_POST['maintenance_cost']),
        'parts_replaced' => isset($_POST['parts_replaced']) ? 1 : 0,
        'parts_details' => clean_input($_POST['parts_details']),
        'issues_found' => clean_input($_POST['issues_found']),
        'resolution' => clean_input($_POST['resolution']),
        'device_health_impact' => clean_input($_POST['device_health_impact']),
        'next_recommended_date' => clean_input($_POST['next_recommended_date'])
    ];
    
    if ($controller->completeMaintenance($data)) {
        set_message('Maintenance completed successfully and device records updated.', 'success');
        header('Location: view.php?id=' . $maintenance_id);
        exit;
    } else {
        set_message('Failed to complete maintenance record.', 'danger');
    }
}

$page_title = 'Complete Maintenance';
include $base_path . 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Complete Maintenance</h1>
    <a href="index.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back</a>
</div>

<!-- Device Info Card -->
<div class="card mb-4">
    <div class="card-header bg-info text-white">
        <h5 class="mb-0">Device Information</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <p><strong>Device:</strong> <?php echo htmlspecialchars($device->device_name); ?></p>
                <p><strong>Serial Number:</strong> <?php echo htmlspecialchars($device->serial_number); ?></p>
                <p><strong>Department:</strong> <?php echo htmlspecialchars($device->department); ?></p>
            </div>
            <div class="col-md-6">
                <p><strong>Status:</strong> <?php echo htmlspecialchars($device->status); ?></p>
                <p><strong>Last Maintenance:</strong> <?php echo $device->last_maintenance ? htmlspecialchars($device->last_maintenance) : 'None'; ?></p>
                <p><strong>Maintenance Type:</strong> <?php echo htmlspecialchars($maintenance->maintenance_type); ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Maintenance Completion Form -->
<div class="row">
    <div class="col-md-12">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Maintenance Completion Report</h5>
            </div>
            <div class="card-body">
                <form action="" method="post">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="completion_date" class="form-label">Completion Date</label>
                            <input type="date" class="form-control" id="completion_date" name="completion_date" value="<?php echo date('Y-m-d'); ?>" readonly>
                        </div>
                        <div class="col-md-6">
                            <label for="maintenance_cost" class="form-label">Maintenance Cost</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" class="form-control" id="maintenance_cost" name="maintenance_cost" placeholder="0.00" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="parts_replaced" name="parts_replaced">
                            <label class="form-check-label" for="parts_replaced">
                                Parts Replaced
                            </label>
                        </div>
                    </div>
                    
                    <div id="parts_details_section" class="mb-3" style="display: none;">
                        <label for="parts_details" class="form-label">Parts Details</label>
                        <textarea class="form-control" id="parts_details" name="parts_details" rows="2" placeholder="List parts that were replaced and their costs"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="issues_found" class="form-label">Issues Found</label>
                        <textarea class="form-control" id="issues_found" name="issues_found" rows="3" placeholder="Describe any issues discovered during maintenance"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="resolution" class="form-label">Resolution Actions</label>
                        <textarea class="form-control" id="resolution" name="resolution" rows="3" placeholder="Describe what actions were taken to resolve issues" required></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="notes" class="form-label">Additional Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Any additional notes or observations"></textarea>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="device_health_impact" class="form-label">Impact on Device Health</label>
                            <select class="form-select" id="device_health_impact" name="device_health_impact" required>
                                <option value="">Select Impact</option>
                                <option value="significant_improvement">Significant Improvement</option>
                                <option value="minor_improvement">Minor Improvement</option>
                                <option value="no_change" selected>No Change</option>
                                <option value="deterioration">Deterioration Detected</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="next_recommended_date" class="form-label">Next Recommended Maintenance</label>
                            <input type="date" class="form-control" id="next_recommended_date" name="next_recommended_date" 
                                   value="<?php echo date('Y-m-d', strtotime('+3 months')); ?>" required>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between">
                        <a href="view.php?id=<?php echo $maintenance_id; ?>" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-circle"></i> Mark as Completed
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Show/hide parts details section based on checkbox
    const partsReplacedCheckbox = document.getElementById('parts_replaced');
    const partsDetailsSection = document.getElementById('parts_details_section');
    
    partsReplacedCheckbox.addEventListener('change', function() {
        partsDetailsSection.style.display = this.checked ? 'block' : 'none';
    });
    
    // Set recommended date based on device type
    const deviceHealthImpact = document.getElementById('device_health_impact');
    const nextRecommendedDate = document.getElementById('next_recommended_date');
    
    deviceHealthImpact.addEventListener('change', function() {
        let additionalMonths = 3; // default
        
        switch(this.value) {
            case 'significant_improvement':
                additionalMonths = 6;
                break;
            case 'minor_improvement':
                additionalMonths = 4;
                break;
            case 'no_change':
                additionalMonths = 3;
                break;
            case 'deterioration':
                additionalMonths = 2;
                break;
        }
        
        const newDate = new Date();
        newDate.setMonth(newDate.getMonth() + additionalMonths);
        nextRecommendedDate.value = newDate.toISOString().split('T')[0];
    });
});
</script>

<?php include $base_path . 'includes/footer.php'; ?> 