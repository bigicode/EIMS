<?php
// Set relative path for includes
$base_path = '../../';
require_once $base_path . 'config/database.php';
require_once $base_path . 'controllers/DeviceController.php';
require_once $base_path . 'models/User.php';
require_once $base_path . 'includes/functions.php';
require_once $base_path . 'includes/auth.php';

// Require login for this page
require_login();

// Check if ID is provided
if(!isset($_GET['id']) || empty($_GET['id'])) {
    set_message("No device specified.", "warning");
    redirect("index.php");
}

$device_id = clean_input($_GET['id']);

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Initialize device controller
$controller = new DeviceController();

// Get device by ID
$device = $controller->getDeviceById($device_id);

if(!$device) {
    set_message("Device not found.", "danger");
    redirect("index.php");
}

// Initialize user object to get users for assignment
$user = new User($db);
$users = $user->read();

// Process form submission
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Update device
    $result = $controller->updateDevice($_POST);
    
    if($result['success']) {
        set_message($result['message'], "success");
        redirect("view.php?id=" . $device_id);
    } else {
        set_message($result['message'], "danger");
    }
}

// Include header
$page_title = "Edit Device - " . $device->device_name;
include $base_path . 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Edit Device: <?php echo $device->device_name; ?></h1>
    <div>
        <a href="view.php?id=<?php echo $device_id; ?>" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back to Device</a>
    </div>
</div>

<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card shadow">
            <div class="card-body">
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?id=' . $device_id; ?>" method="post">
                    <input type="hidden" name="id" value="<?php echo $device_id; ?>">
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="device_name" class="form-label">Device Name</label>
                            <input type="text" class="form-control" id="device_name" name="device_name" value="<?php echo $device->device_name; ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="device_type" class="form-label">Device Type</label>
                            <select class="form-select" id="device_type" name="device_type" required>
                                <option value="">Select Type</option>
                                <option value="Desktop" <?php echo ($device->device_type == 'Desktop') ? 'selected' : ''; ?>>Desktop</option>
                                <option value="Laptop" <?php echo ($device->device_type == 'Laptop') ? 'selected' : ''; ?>>Laptop</option>
                                <option value="Tablet" <?php echo ($device->device_type == 'Tablet') ? 'selected' : ''; ?>>Tablet</option>
                                <option value="Printer" <?php echo ($device->device_type == 'Printer') ? 'selected' : ''; ?>>Printer</option>
                                <option value="Scanner" <?php echo ($device->device_type == 'Scanner') ? 'selected' : ''; ?>>Scanner</option>
                                <option value="Server" <?php echo ($device->device_type == 'Server') ? 'selected' : ''; ?>>Server</option>
                                <option value="Network" <?php echo ($device->device_type == 'Network') ? 'selected' : ''; ?>>Network Equipment</option>
                                <option value="Other" <?php echo ($device->device_type == 'Other') ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="serial_number" class="form-label">Serial Number</label>
                            <input type="text" class="form-control" id="serial_number" name="serial_number" value="<?php echo $device->serial_number; ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="model" class="form-label">Model</label>
                            <input type="text" class="form-control" id="model" name="model" value="<?php echo $device->model; ?>" required>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="manufacturer" class="form-label">Manufacturer</label>
                            <input type="text" class="form-control" id="manufacturer" name="manufacturer" value="<?php echo $device->manufacturer; ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="active" <?php echo ($device->status == 'active') ? 'selected' : ''; ?>>Active</option>
                                <option value="maintenance" <?php echo ($device->status == 'maintenance') ? 'selected' : ''; ?>>Maintenance</option>
                                <option value="repair" <?php echo ($device->status == 'repair') ? 'selected' : ''; ?>>Repair</option>
                                <option value="disposed" <?php echo ($device->status == 'disposed') ? 'selected' : ''; ?>>Disposed</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="purchase_date" class="form-label">Purchase Date</label>
                            <input type="date" class="form-control" id="purchase_date" name="purchase_date" value="<?php echo $device->purchase_date; ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="warranty_end" class="form-label">Warranty End Date</label>
                            <input type="date" class="form-control" id="warranty_end" name="warranty_end" value="<?php echo $device->warranty_end; ?>" required>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="location" class="form-label">Location</label>
                            <input type="text" class="form-control" id="location" name="location" value="<?php echo $device->location; ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="department" class="form-label">Department</label>
                            <select class="form-select" id="department" name="department" required>
                                <option value="">Select Department</option>
                                <option value="IT" <?php echo ($device->department == 'IT') ? 'selected' : ''; ?>>IT</option>
                                <option value="HR" <?php echo ($device->department == 'HR') ? 'selected' : ''; ?>>HR</option>
                                <option value="Finance" <?php echo ($device->department == 'Finance') ? 'selected' : ''; ?>>Finance</option>
                                <option value="Marketing" <?php echo ($device->department == 'Marketing') ? 'selected' : ''; ?>>Marketing</option>
                                <option value="Operations" <?php echo ($device->department == 'Operations') ? 'selected' : ''; ?>>Operations</option>
                                <option value="Sales" <?php echo ($device->department == 'Sales') ? 'selected' : ''; ?>>Sales</option>
                                <option value="Research" <?php echo ($device->department == 'Research') ? 'selected' : ''; ?>>Research</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="assigned_to" class="form-label">Assigned To (Optional)</label>
                        <select class="form-select" id="assigned_to" name="assigned_to">
                            <option value="">Not Assigned</option>
                            <?php while($user_row = $users->fetch(PDO::FETCH_ASSOC)): ?>
                                <option value="<?php echo $user_row['id']; ?>" <?php echo ($device->assigned_to == $user_row['id']) ? 'selected' : ''; ?>>
                                    <?php echo $user_row['full_name']; ?> (<?php echo $user_row['department']; ?>)
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="last_maintenance" class="form-label">Last Maintenance Date (Optional)</label>
                            <input type="date" class="form-control" id="last_maintenance" name="last_maintenance" value="<?php echo $device->last_maintenance; ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="next_maintenance" class="form-label">Next Maintenance Date (Optional)</label>
                            <input type="date" class="form-control" id="next_maintenance" name="next_maintenance" value="<?php echo $device->next_maintenance; ?>">
                        </div>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Update Device</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include $base_path . 'includes/footer.php'; ?>