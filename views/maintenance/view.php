<?php
$base_path = '../../';
require_once $base_path . 'config/database.php';
require_once $base_path . 'models/Maintenance.php';
require_once $base_path . 'models/Device.php';
require_once $base_path . 'models/User.php';
require_once $base_path . 'includes/functions.php';
require_once $base_path . 'includes/auth.php';

require_login();

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
if (!$maintenance->read_single()) {
    set_message('Maintenance record not found.', 'danger');
    header('Location: index.php');
    exit;
}

// Get device information
$device = new Device($db);
$device->id = $maintenance->device_id;
$device->read_single();

// Get technician information if available
$technician_name = '';
if (!empty($maintenance->performed_by)) {
    $user = new User($db);
    $user->id = $maintenance->performed_by;
    if ($user->read_single()) {
        $technician_name = $user->full_name;
    }
}

$page_title = 'Maintenance Details';
include $base_path . 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Maintenance Details</h1>
    <a href="javascript:history.back()" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back</a>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="mb-0">Maintenance Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Maintenance ID:</strong> #<?php echo $maintenance->id; ?></p>
                        <p><strong>Type:</strong> <?php echo htmlspecialchars($maintenance->maintenance_type ?? 'N/A'); ?></p>
                        <p><strong>Status:</strong> 
                            <?php 
                            $status_class = '';
                            switch($maintenance->status) {
                                case 'scheduled': $status_class = 'primary'; break;
                                case 'in_progress': $status_class = 'warning'; break;
                                case 'completed': $status_class = 'success'; break;
                                case 'cancelled': $status_class = 'danger'; break;
                                default: $status_class = 'secondary';
                            }
                            ?>
                            <span class="badge bg-<?php echo $status_class; ?>"><?php echo ucfirst($maintenance->status ?? 'Unknown'); ?></span>
                        </p>
                        <p><strong>Scheduled Date:</strong> <?php echo format_date($maintenance->scheduled_date ?? ''); ?></p>
                        <p><strong>Completion Date:</strong> <?php echo !empty($maintenance->completion_date) ? format_date($maintenance->completion_date) : 'Not completed'; ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Device:</strong> <a href="../devices/view.php?id=<?php echo $maintenance->device_id; ?>"><?php echo htmlspecialchars($device->device_name ?? 'Unknown Device'); ?></a></p>
                        <p><strong>Technician:</strong> <?php echo !empty($technician_name) ? htmlspecialchars($technician_name) : 'Not assigned'; ?></p>
                        <p><strong>Cost:</strong> <?php echo !empty($maintenance->maintenance_cost) ? '$' . number_format($maintenance->maintenance_cost, 2) : 'Not specified'; ?></p>
                        <p><strong>Health Impact:</strong> 
                            <?php 
                            $impact = $maintenance->device_health_impact ?? '';
                            $impact_display = '';
                            switch($impact) {
                                case 'significant_improvement': $impact_display = '<span class="badge bg-success">Significant Improvement</span>'; break;
                                case 'minor_improvement': $impact_display = '<span class="badge bg-info">Minor Improvement</span>'; break;
                                case 'no_change': $impact_display = '<span class="badge bg-secondary">No Change</span>'; break;
                                case 'deterioration': $impact_display = '<span class="badge bg-danger">Deterioration</span>'; break;
                                default: $impact_display = 'Not assessed';
                            }
                            echo $impact_display;
                            ?>
                        </p>
                    </div>
                </div>
                
                <?php if (!empty($maintenance->description)): ?>
                <hr>
                <div class="row">
                    <div class="col-12">
                        <h6>Description:</h6>
                        <p><?php echo nl2br(htmlspecialchars($maintenance->description)); ?></p>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($maintenance->notes)): ?>
                <hr>
                <div class="row">
                    <div class="col-12">
                        <h6>Notes:</h6>
                        <p><?php echo nl2br(htmlspecialchars($maintenance->notes)); ?></p>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="mb-0">Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="../devices/view.php?id=<?php echo $maintenance->device_id; ?>" class="btn btn-outline-primary">
                        <i class="bi bi-laptop"></i> View Device
                    </a>
                    <?php if(is_admin() && ($maintenance->status == 'scheduled' || $maintenance->status == 'in_progress')): ?>
                        <a href="complete.php?id=<?php echo $maintenance->id; ?>" class="btn btn-success">
                            <i class="bi bi-check-circle"></i> Mark as Complete
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include $base_path . 'includes/footer.php'; ?> 