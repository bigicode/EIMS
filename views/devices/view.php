<?php
// Set relative path for includes
$base_path = '../../';
require_once $base_path . 'config/database.php';
require_once $base_path . 'controllers/DeviceController.php';
require_once $base_path . 'models/Issue.php';
require_once $base_path . 'models/Maintenance.php';
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

// Get issues for this device
$issue = new Issue($db);
$issues = $issue->get_by_device($device_id);

// Get maintenance records for this device
$maintenance = new Maintenance($db);
$maintenance_records = $maintenance->get_by_device($device_id);

// Include header
$page_title = "Device Details - " . $device->device_name;
include $base_path . 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Device Details: <?php echo $device->device_name; ?></h1>
    <div>
        <a href="index.php" class="btn btn-secondary me-2"><i class="bi bi-arrow-left"></i> Back to Devices</a>
        <a href="edit.php?id=<?php echo $device_id; ?>" class="btn btn-warning"><i class="bi bi-pencil"></i> Edit Device</a>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="mb-0">Device Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Device Type:</strong> <?php echo $device->device_type; ?></p>
                        <p><strong>Serial Number:</strong> <?php echo $device->serial_number; ?></p>
                        <p><strong>Model:</strong> <?php echo $device->model; ?></p>
                        <p><strong>Manufacturer:</strong> <?php echo $device->manufacturer; ?></p>
                        <p><strong>Purchase Date:</strong> <?php echo format_date($device->purchase_date); ?></p>
                        <p><strong>Warranty End:</strong> <?php echo format_date($device->warranty_end); ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Status:</strong> <?php echo device_status_badge($device->status); ?></p>
                        <p><strong>Location:</strong> <?php echo $device->location; ?></p>
                        <p><strong>Department:</strong> <?php echo $device->department; ?></p>
                        <p><strong>Assigned To:</strong> <?php echo $device->assigned_to_name ?? 'Not Assigned'; ?></p>
                        <p><strong>Last Maintenance:</strong> <?php echo $device->last_maintenance ? format_date($device->last_maintenance) : 'Never'; ?></p>
                        <p><strong>Next Maintenance:</strong> <?php echo $device->next_maintenance ? format_date($device->next_maintenance) : 'Not scheduled'; ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="../issues/create.php?device_id=<?php echo $device_id; ?>" class="btn btn-outline-blue"><i class="bi bi-exclamation-triangle"></i> Report Issue</a>
                    <?php if(is_admin()): ?>
                        <a href="../maintenance/schedule.php?device_id=<?php echo $device_id; ?>" class="btn btn-outline-blue"><i class="bi bi-tools"></i> Schedule Maintenance</a>
                        <a href="../maintenance/history.php?device_id=<?php echo $device_id; ?>" class="btn btn-outline-blue"><i class="bi bi-clock-history"></i> View Maintenance History</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="mb-0">Warranty Status</h5>
            </div>
            <div class="card-body text-center">
                <?php 
                $today = date('Y-m-d');
                $days_left = days_between($today, $device->warranty_end);
                $expired = strtotime($device->warranty_end) < strtotime($today);
                
                if($expired) {
                    echo '<div class="alert alert-danger">Warranty Expired</div>';
                    echo '<p>Expired ' . $days_left . ' days ago</p>';
                } else {
                    if($days_left <= 30) {
                        echo '<div class="alert alert-warning">Warranty Ending Soon</div>';
                    } else {
                        echo '<div class="alert alert-success">Warranty Active</div>';
                    }
                    echo '<p>' . $days_left . ' days remaining</p>';
                }
                ?>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Recent Issues</h5>
                <a href="../issues/index.php?device_id=<?php echo $device_id; ?>" class="btn btn-sm btn-primary">View All</a>
            </div>
            <div class="card-body">
                <?php if($issues->rowCount() > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Issue</th>
                                    <th>Priority</th>
                                    <th>Status</th>
                                    <th>Reported</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($row = $issues->fetch(PDO::FETCH_ASSOC)): ?>
                                    <tr>
                                        <td><a href="../issues/view.php?id=<?php echo $row['id']; ?>"><?php echo $row['issue_title']; ?></a></td>
                                        <td><?php echo issue_priority_badge($row['priority']); ?></td>
                                        <td><?php echo $row['status']; ?></td>
                                        <td><?php echo format_date($row['reported_at']); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-center">No issues reported for this device</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Maintenance History</h5>
                <?php if(is_admin()): ?>
                    <a href="../maintenance/history.php?device_id=<?php echo $device_id; ?>" class="btn btn-sm btn-primary">View All</a>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php if($maintenance_records->rowCount() > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Health Impact</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($row = $maintenance_records->fetch(PDO::FETCH_ASSOC)): 
                                    // Set row class based on status
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
                                    
                                    // Format health impact
                                    $impact = isset($row['device_health_impact']) ? $row['device_health_impact'] : '';
                                    $impact_display = '';
                                    switch($impact) {
                                        case 'significant_improvement':
                                            $impact_display = '<span class="badge bg-success">Significant</span>';
                                            break;
                                        case 'minor_improvement':
                                            $impact_display = '<span class="badge bg-info">Minor</span>';
                                            break;
                                        case 'no_change':
                                            $impact_display = '<span class="badge bg-secondary">No Change</span>';
                                            break;
                                        case 'deterioration':
                                            $impact_display = '<span class="badge bg-danger">Deterioration</span>';
                                            break;
                                        default:
                                            $impact_display = '-';
                                    }
                                    
                                    // Determine which date to show
                                    $date_to_show = $row['status'] == 'completed' && !empty($row['completion_date']) 
                                        ? format_date($row['completion_date']) 
                                        : format_date($row['scheduled_date']);
                                    
                                    $date_label = $row['status'] == 'completed' ? 'Completed: ' : 'Scheduled: ';
                                ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['maintenance_type']); ?></td>
                                        <td><span class="badge bg-<?php echo $status_class; ?>"><?php echo ucfirst($row['status']); ?></span></td>
                                        <td><?php echo $date_label . $date_to_show; ?></td>
                                        <td><?php echo $impact_display; ?></td>
                                        <td>
                                            <a href="../maintenance/view.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-info">
                                                <i class="bi bi-eye"></i> Details
                                            </a>
                                            <?php if(is_admin() && ($row['status'] == 'scheduled' || $row['status'] == 'in_progress')): ?>
                                                <a href="../maintenance/complete.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-success">
                                                    <i class="bi bi-check-circle"></i> Complete
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-center">No maintenance records for this device</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include $base_path . 'includes/footer.php'; ?>