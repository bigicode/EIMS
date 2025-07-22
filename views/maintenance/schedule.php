<?php
// Set relative path for includes
$base_path = '../../';
require_once $base_path . 'config/database.php';
require_once $base_path . 'models/Maintenance.php';
require_once $base_path . 'models/Device.php';
require_once $base_path . 'includes/functions.php';
require_once $base_path . 'includes/auth.php';

// Only admin can access this page
require_admin();

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Initialize device object
$device = new Device($db);

// Get all active devices
$devices = $device->read_by_status('active');

// Process form submission
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Initialize maintenance object
    $maintenance = new Maintenance($db);
    
    // Set maintenance properties
    $maintenance->device_id = clean_input($_POST['device_id']);
    $maintenance->maintenance_type = clean_input($_POST['maintenance_type']);
    $maintenance->description = clean_input($_POST['description']);
    $maintenance->scheduled_date = clean_input($_POST['scheduled_date']);
    $maintenance->status = 'scheduled';
    
    // Create maintenance record
    if($maintenance->create()) {
        // Update device's next maintenance date
        $device->id = $maintenance->device_id;
        $device->next_maintenance = $maintenance->scheduled_date;
        $device->update_maintenance_date();
        
        set_message("Maintenance scheduled successfully.", "success");
        redirect("index.php");
    } else {
        set_message("Failed to schedule maintenance.", "danger");
    }
}

// Include header
$page_title = "Schedule Maintenance";
include $base_path . 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Schedule Maintenance</h1>
    <a href="index.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back to Maintenance</a>
</div>

<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card shadow">
            <div class="card-body">
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <div class="mb-3">
                        <label for="device_id" class="form-label">Device</label>
                        <select class="form-select" id="device_id" name="device_id" required>
                            <option value="">Select Device</option>
                            <?php while($device_row = $devices->fetch(PDO::FETCH_ASSOC)): ?>
                                <option value="<?php echo $device_row['id']; ?>">
                                    <?php echo $device_row['device_name']; ?> (<?php echo $device_row['serial_number']; ?>)
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="maintenance_type" class="form-label">Maintenance Type</label>
                        <select class="form-select" id="maintenance_type" name="maintenance_type" required>
                            <option value="">Select Type</option>
                            <option value="Routine Check">Routine Check</option>
                            <option value="Software Update">Software Update</option>
                            <option value="Hardware Inspection">Hardware Inspection</option>
                            <option value="Calibration">Calibration</option>
                            <option value="Cleaning">Cleaning</option>
                            <option value="Parts Replacement">Parts Replacement</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="scheduled_date" class="form-label">Scheduled Date</label>
                        <input type="date" class="form-control" id="scheduled_date" name="scheduled_date" required min="<?php echo date('Y-m-d'); ?>">
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn" style="background-color: #000080; color: white;">Schedule Maintenance</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include $base_path . 'includes/footer.php'; ?>