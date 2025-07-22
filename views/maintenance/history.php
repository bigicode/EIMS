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

// Check if device ID is provided
if(!isset($_GET['device_id']) || empty($_GET['device_id'])) {
    set_message("No device specified.", "warning");
    redirect("../devices/index.php");
}

$device_id = clean_input($_GET['device_id']);

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Initialize device object
$device = new Device($db);
$device->id = $device_id;

// Check if device exists
if(!$device->read_single()) {
    set_message("Device not found.", "danger");
    redirect("../devices/index.php");
}

// Initialize maintenance object
$maintenance = new Maintenance($db);

// Get maintenance history for this device
$result = $maintenance->get_by_device($device_id);

// Include header
$page_title = "Maintenance History - " . $device->device_name;
include $base_path . 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Maintenance History: <?php echo $device->device_name; ?></h1>
    <div>
        <a href="../devices/view.php?id=<?php echo $device_id; ?>" class="btn btn-secondary me-2"><i class="bi bi-arrow-left"></i> Back to Device</a>
        <a href="schedule.php" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Schedule Maintenance</a>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header bg-light">
        <h5 class="mb-0">Device Information</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <p><strong>Serial Number:</strong> <?php echo $device->serial_number; ?></p>
                <p><strong>Model:</strong> <?php echo $device->model; ?></p>
                <p><strong>Manufacturer:</strong> <?php echo $device->manufacturer; ?></p>
            </div>
            <div class="col-md-6">
                <p><strong>Status:</strong> <?php echo device_status_badge($device->status); ?></p>
                <p><strong>Last Maintenance:</strong> <?php echo $device->last_maintenance ? format_date($device->last_maintenance) : 'Never'; ?></p>
                <p><strong>Next Maintenance:</strong> <?php echo $device->next_maintenance ? format_date($device->next_maintenance) : 'Not scheduled'; ?></p>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header bg-light">
        <h5 class="mb-0">Maintenance Records</h5>
    </div>
    <div class="card-body">
        <?php if($result->rowCount() > 0): ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Type</th>
                            <th>Scheduled Date</th>
                            <th>Status</th>
                            <th>Completed Date</th>
                            <th>Performed By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $result->fetch(PDO::FETCH_ASSOC)): ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo $row['maintenance_type']; ?></td>
                                <td><?php echo format_date($row['scheduled_date']); ?></td>
                                <td>
                                    <?php 
                                    $status_class = '';
                                    switch($row['status']) {
                                        case 'scheduled':
                                            $status_class = 'primary';
                                            break;
                                        case 'in_progress':
                                            $status_class = 'warning';
                                            break;
                                        case 'completed':
                                            $status_class = 'success';
                                            break;
                                        case 'cancelled':
                                            $status_class = 'danger';
                                            break;
                                    }
                                    ?>
                                    <span class="badge bg-<?php echo $status_class; ?>"><?php echo $row['status']; ?></span>
                                </td>
                                <td><?php echo $row['completion_date'] ? format_date($row['completion_date']) : '-'; ?></td>
                                <td><?php echo $row['performed_by_name'] ? $row['performed_by_name'] : '-'; ?></td>
                                <td>
                                    <a href="view.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-info"><i class="bi bi-eye"></i></a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> No maintenance records found for this device.
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include $base_path . 'includes/footer.php'; ?>