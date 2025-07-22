<?php
// Set relative path for includes
$base_path = '../../';
require_once $base_path . 'config/database.php';
require_once $base_path . 'models/Device.php';
require_once $base_path . 'models/User.php';
require_once $base_path . 'includes/functions.php';
require_once $base_path . 'includes/auth.php';

// Require login for this page
require_login();

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Initialize user object to get users for assignment
$user = new User($db);
$users = $user->read();

// Process form submission
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Initialize device controller
    require_once $base_path . 'controllers/DeviceController.php';
    $controller = new DeviceController();
    
    // Create device
    $result = $controller->createDevice($_POST);
    
    if($result['success']) {
        set_message($result['message'], "success");
        redirect("index.php");
    } else {
        set_message($result['message'], "danger");
    }
}

// Include header
$page_title = "Add New Device";
include $base_path . 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Add New Device</h1>
    <a href="index.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back to Devices</a>
</div>

<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card shadow">
            <div class="card-body">
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="device_name" class="form-label">Device Name</label>
                            <input type="text" class="form-control" id="device_name" name="device_name" required>
                        </div>
                        <div class="col-md-6">
                            <label for="device_type" class="form-label">Device Type</label>
                            <select class="form-select" id="device_type" name="device_type" required>
                                <option value="">Select Type</option>
                                <option value="Desktop">Desktop</option>
                                <option value="Laptop">Laptop</option>
                                <option value="Tablet">Tablet</option>
                                <option value="Printer">Printer</option>
                                <option value="Scanner">Scanner</option>
                                <option value="Server">Server</option>
                                <option value="Network">Network Equipment</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="serial_number" class="form-label">Serial Number</label>
                            <input type="text" class="form-control" id="serial_number" name="serial_number" required>
                        </div>
                        <div class="col-md-6">
                            <label for="model" class="form-label">Model</label>
                            <input type="text" class="form-control" id="model" name="model" required>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="manufacturer" class="form-label">Manufacturer</label>
                            <input type="text" class="form-control" id="manufacturer" name="manufacturer" required>
                        </div>
                        <div class="col-md-6">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="active">Active</option>
                                <option value="maintenance">Maintenance</option>
                                <option value="repair">Repair</option>
                                <option value="disposed">Disposed</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="purchase_date" class="form-label">Purchase Date</label>
                            <input type="date" class="form-control" id="purchase_date" name="purchase_date" required>
                        </div>
                        <div class="col-md-6">
                            <label for="warranty_end" class="form-label">Warranty End Date</label>
                            <input type="date" class="form-control" id="warranty_end" name="warranty_end" required>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="location" class="form-label">Location</label>
                            <input type="text" class="form-control" id="location" name="location" required>
                        </div>
                        <div class="col-md-6">
                            <label for="department" class="form-label">Department</label>
                            <select class="form-select" id="department" name="department" required>
                                <option value="">Select Department</option>
                                <option value="IT">IT</option>
                                <option value="HR">HR</option>
                                <option value="Finance">Finance</option>
                                <option value="Marketing">Marketing</option>
                                <option value="Operations">Operations</option>
                                <option value="Sales">Sales</option>
                                <option value="Research">Research</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="assigned_to" class="form-label">Assigned To (Optional)</label>
                        <select class="form-select" id="assigned_to" name="assigned_to">
                            <option value="">Not Assigned</option>
                            <?php while($user_row = $users->fetch(PDO::FETCH_ASSOC)): ?>
                                <option value="<?php echo $user_row['id']; ?>">
                                    <?php echo $user_row['full_name']; ?> (<?php echo $user_row['department']; ?>)
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="last_maintenance" class="form-label">Last Maintenance Date (Optional)</label>
                            <input type="date" class="form-control" id="last_maintenance" name="last_maintenance">
                        </div>
                        <div class="col-md-6">
                            <label for="next_maintenance" class="form-label">Next Maintenance Date (Optional)</label>
                            <input type="date" class="form-control" id="next_maintenance" name="next_maintenance">
                        </div>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Add Device</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include $base_path . 'includes/footer.php'; ?>