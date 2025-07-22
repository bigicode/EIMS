<?php
// Set relative path for includes
$base_path = '../../';
require_once $base_path . 'config/database.php';
require_once $base_path . 'models/Device.php';
require_once $base_path . 'models/Issue.php';
require_once $base_path . 'models/Maintenance.php';
require_once $base_path . 'models/User.php';
require_once $base_path . 'models/Message.php';
require_once $base_path . 'includes/functions.php';
require_once $base_path . 'includes/auth.php';

// Only admin can access this page
require_admin();

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Initialize objects
$device = new Device($db);
$issue = new Issue($db);
$maintenance = new Maintenance($db);
$user = new User($db);
$message_model = new Message($db);

// Get counts
$total_devices = $device->count_all();
$active_devices = $device->count_by_status('active');
$maintenance_devices = $device->count_by_status('maintenance');
$repair_devices = $device->count_by_status('repair');

$open_issues = $issue->count_by_status('open');
$critical_issues = $issue->count_by_priority('critical');

$upcoming_maintenance = $maintenance->get_upcoming_maintenance();
$overdue_maintenance = $maintenance->get_overdue_maintenance();

$total_users = $user->count_all();

// Get recent issues
$recent_issues = $issue->read_recent(5);

// Get devices with expiring warranty (next 30 days)
$expiring_warranty = $device->get_expiring_warranty(30);

// Handle device acquisition form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_acquisition'])) {
    $new_device = new Device($db);
    $new_device->device_name = $_POST['device_name'];
    $new_device->warranty_end = $_POST['warranty_end'];
    // Set required fields for Device model
    $new_device->device_type = 'Unknown'; // or get from form if you want
    $new_device->serial_number = uniqid('SN'); // or get from form if you want
    $new_device->model = '';
    $new_device->status = 'active';
    $new_device->location = '';
    $new_device->department = '';
    $new_device->assigned_to = null;
    $new_device->last_maintenance = null;
    $new_device->next_maintenance = null;
    if ($new_device->create()) {
        set_message('Device acquisition recorded successfully.', 'success');
    } else {
        set_message('Failed to record acquisition.', 'danger');
    }
}
// Fetch all devices for Acquisition tab (only existing columns)
$stmt_acq = $db->prepare("SELECT device_name, purchase_date, warranty_end, status FROM devices ORDER BY purchase_date DESC");
$stmt_acq->execute();
$acquisitions = $stmt_acq->fetchAll(PDO::FETCH_ASSOC);

// Fetch all devices
$stmt_devices = $db->prepare("SELECT id, device_name, status FROM devices");
$stmt_devices->execute();
$all_devices = $stmt_devices->fetchAll(PDO::FETCH_ASSOC);

// Fetch all issues and join with device name
$stmt_issues = $db->prepare("SELECT i.issue_title, i.device_id FROM issues i");
$stmt_issues->execute();
$issues = $stmt_issues->fetchAll(PDO::FETCH_ASSOC);

// Define types
$issue_types = ['hardware', 'software', 'user_error'];
$device_analytics = [];
$issue_type_totals = array_fill_keys($issue_types, 0);

// Initialize all devices with zero counts
foreach ($all_devices as $dev) {
    $device_analytics[$dev['id']] = [
        'device_name' => $dev['device_name'],
        'status' => $dev['status'],
        'total_reports' => 0,
        'hardware' => 0,
        'software' => 0,
        'user_error' => 0
    ];
}
// Aggregate issues by device
foreach ($issues as $row) {
    $device_id = $row['device_id'];
    $title = strtolower($row['issue_title']);
    $type = null;
    if (strpos($title, 'hardware') !== false) {
        $type = 'hardware';
    } elseif (strpos($title, 'software') !== false) {
        $type = 'software';
    } elseif (strpos($title, 'user error') !== false || strpos($title, 'user') !== false) {
        $type = 'user_error';
    }
    if (isset($device_analytics[$device_id])) {
        $device_analytics[$device_id]['total_reports']++;
        if ($type) {
            $device_analytics[$device_id][$type]++;
            $issue_type_totals[$type]++;
        }
    }
}
$issue_analytics = array_values($device_analytics);

// Fetch all maintenance records (remove m.cost)
$stmt_maint = $db->prepare("SELECT m.device_id, m.maintenance_type, m.scheduled_date, m.status as maint_status, m.notes, m.performed_by, u.full_name as technician, m.description, m.completion_date FROM maintenance m LEFT JOIN users u ON m.performed_by = u.id");
$stmt_maint->execute();
$all_maintenance = $stmt_maint->fetchAll(PDO::FETCH_ASSOC);

// Map maintenance records by device_id (latest only)
$maint_by_device = [];
foreach ($all_maintenance as $row) {
    $dev_id = $row['device_id'];
    if (!isset($maint_by_device[$dev_id]) || ($row['scheduled_date'] > $maint_by_device[$dev_id]['scheduled_date'])) {
        $maint_by_device[$dev_id] = $row;
    }
}
// Build maintenance history for all devices
$maintenance_history = [];
foreach ($all_devices as $dev) {
    $dev_id = $dev['id'];
    $row = isset($maint_by_device[$dev_id]) ? $maint_by_device[$dev_id] : null;
    $maintenance_history[] = [
        'device_name' => $dev['device_name'],
        'status' => $dev['status'],
        'type' => $row['maintenance_type'] ?? 'No record',
        'date' => $row['scheduled_date'] ?? '',
        'technician' => $row['technician'] ?? '',
        'notes' => $row['notes'] ?? '',
    ];
}

// Recurring Issues: aggregate by device and type
$recurring_issues = [];
$issue_counts = [];
foreach ($issues as $row) {
    $device_id = $row['device_id'];
    $title = strtolower($row['issue_title']);
    $type = null;
    if (strpos($title, 'hardware') !== false) {
        $type = 'Hardware';
    } elseif (strpos($title, 'software') !== false) {
        $type = 'Software';
    } elseif (strpos($title, 'user error') !== false || strpos($title, 'user') !== false) {
        $type = 'User Error';
    } else {
        $type = 'Other';
    }
    if (!isset($issue_counts[$device_id])) {
        $issue_counts[$device_id] = [];
    }
    if (!isset($issue_counts[$device_id][$type])) {
        $issue_counts[$device_id][$type] = 0;
    }
    $issue_counts[$device_id][$type]++;
}
foreach ($all_devices as $dev) {
    $dev_id = $dev['id'];
    $status = $dev['status'];
    if (isset($issue_counts[$dev_id]) && count($issue_counts[$dev_id]) > 0) {
        // Find most frequent type
        $max_type = array_keys($issue_counts[$dev_id], max($issue_counts[$dev_id]))[0];
        $max_count = $issue_counts[$dev_id][$max_type];
        $flag = $max_count >= 3;
        $recurring_issues[] = [
            'device_name' => $dev['device_name'],
            'status' => $status,
            'issue_type' => $max_type,
            'count' => $max_count,
            'flag' => $flag
        ];
    } else {
        $recurring_issues[] = [
            'device_name' => $dev['device_name'],
            'status' => $status,
            'issue_type' => 'No record',
            'count' => 0,
            'flag' => false
        ];
    }
}

// Recommendations: show all devices, demo logic for now
$recommendations = [];
foreach ($all_devices as $dev) {
    $recommendations[] = [
        'device_name' => $dev['device_name'],
        'status' => $dev['status'],
        'recommendation' => 'No record',
        'reliability_score' => '-',
        'cost_benefit' => '-',
    ];
}

// Status Monitoring: show all devices, demo logic for now
$status_monitoring = [];
foreach ($all_devices as $dev) {
    $status_monitoring[] = [
        'device_name' => $dev['device_name'],
        'status' => $dev['status'],
        'health' => '-',
        'alert' => '-',
    ];
}

// Disposal: show all devices, demo logic for now
$disposal_devices = [];
foreach ($all_devices as $dev) {
    $is_disposed = ($dev['status'] === 'disposed');
    $disposal_devices[] = [
        'device_name' => $dev['device_name'],
        'reason' => $is_disposed ? 'Disposed by admin' : '-',
        'disposal_date' => $is_disposed ? date('Y-m-d') : '-',
        'status' => $is_disposed ? 'Disposed' : $dev['status'],
    ];
}

// Get lifecycle analytics
$lifecycle_stages = ['Acquisition', 'Active Use', 'Maintenance Phase', 'End-of-Life'];
$lifecycle_counts = array_fill_keys($lifecycle_stages, 0);

// Health status counts
$health_counts = [
    'Good' => 0,   // 70-100
    'Fair' => 0,   // 40-69
    'Poor' => 0    // 0-39
];

// Skip detailed lifecycle analysis if no devices found
if (!empty($all_devices)) {
    // Process all devices to determine lifecycle stages
    foreach ($all_devices as $dev) {
        if (!empty($dev['id'])) {
            // Get device details
            $device->id = $dev['id'];
            if ($device->read_single()) {
                // Simplified logic - determine lifecycle based on purchase date and status
                $purchase_date = new DateTime($device->purchase_date ?? 'now');
                $today = new DateTime();
                $age_days = $purchase_date->diff($today)->days;
                
                // Assign lifecycle stage
                $stage = '';
                if ($age_days < 30) {
                    $stage = 'Acquisition';
                } elseif ($device->status == 'active' && empty($device->alert)) {
                    $stage = 'Active Use';
                } elseif ($device->status == 'maintenance' || $device->status == 'repair') {
                    $stage = 'Maintenance Phase';
                } else {
                    $stage = 'End-of-Life';
                }
                
                // Count by lifecycle stage
                if (isset($lifecycle_counts[$stage])) {
                    $lifecycle_counts[$stage]++;
                }
                
                // Use status as a proxy for health when score calculation is not available
                switch ($device->status) {
                    case 'active':
                        $health_counts['Good']++;
                        break;
                    case 'maintenance':
                        $health_counts['Fair']++;
                        break;
                    case 'repair':
                    case 'disposed':
                        $health_counts['Poor']++;
                        break;
                }
            }
        }
    }
}

// Include header
$page_title = "Admin Dashboard";
include $base_path . 'includes/header.php';

// --- User Registration Logic (inline) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register_user'])) {
    $user = new User($db);
    $user->username = clean_input($_POST['username']);
    $user->email = clean_input($_POST['email']);
    $user->full_name = clean_input($_POST['full_name']);
    $user->department = clean_input($_POST['department']);
    $user->role = clean_input($_POST['role']);
    $password = clean_input($_POST['password']);
    $confirm_password = clean_input($_POST['confirm_password']);

    if (empty($password)) {
        set_message('Password cannot be empty.', 'danger');
    } else if ($password !== $confirm_password) {
        set_message('Passwords do not match.', 'danger');
    } else if ($user->username_exists()) {
        set_message('Username already exists.', 'danger');
    } else if ($user->email_exists()) {
        set_message('Email already exists.', 'danger');
    } else {
        $user->password = $password;
        if ($user->create()) {
            set_message('User registered successfully.', 'success');
            // Optionally, you can refresh the page or update user count
        } else {
            set_message('Failed to register user.', 'danger');
        }
    }
}

// Handle send message form
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['send_message'])) {
    $msg = new Message($db);
    $msg->sender_id = $_SESSION['user_id'];
    $msg->receiver_id = intval($_POST['receiver_id']);
    $msg->message = trim($_POST['message_text']);
    if (!empty($msg->message) && $msg->send()) {
        set_message('Message sent successfully.', 'success');
    } else {
        set_message('Failed to send message.', 'danger');
    }
}

// Handle user deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_user_id'])) {
    $delete_user_id = intval($_POST['delete_user_id']);
    if ($delete_user_id == $_SESSION['user_id']) {
        set_message('You cannot delete your own account.', 'danger');
    } else {
        $user_to_delete = new User($db);
        $user_to_delete->id = $delete_user_id;
        $user_to_delete->read_single();
        if ($user_to_delete->role === 'admin') {
            set_message('Cannot delete another admin.', 'danger');
        } else {
            if ($user_to_delete->delete()) {
                set_message('User deleted successfully.', 'success');
            } else {
                set_message('Failed to delete user.', 'danger');
            }
        }
    }
}

// Handle user restore
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['restore_user_id'])) {
    $restore_user_id = intval($_POST['restore_user_id']);
    $user_to_restore = new User($db);
    $user_to_restore->id = $restore_user_id;
    if ($user_to_restore->restore()) {
        set_message('User restored successfully.', 'success');
    } else {
        set_message('Failed to restore user.', 'danger');
    }
}

// Handle bulk delete
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['bulk_delete_ids'])) {
    $ids = array_map('intval', $_POST['bulk_delete_ids']);
    $deleted = 0;
    foreach ($ids as $id) {
        if ($id == $_SESSION['user_id']) continue;
        $user_to_delete = new User($db);
        $user_to_delete->id = $id;
        $user_to_delete->read_single();
        if ($user_to_delete->role !== 'admin' && $user_to_delete->delete()) {
            $deleted++;
        }
    }
    set_message("$deleted user(s) deleted.", $deleted ? 'success' : 'danger');
}

// Fetch all users for management table
$all_users = $user->read();
// Fetch deleted users for restore table
$stmt_deleted = $db->prepare("SELECT id, username, full_name, email, department, role, deleted_at FROM users WHERE deleted_at IS NOT NULL ORDER BY deleted_at DESC");
$stmt_deleted->execute();
$deleted_users = $stmt_deleted;

// Auto-cleanup users deleted more than 7 days ago
$cleanup_user = new User($db);
$cleaned_count = $cleanup_user->cleanup_old_deleted_users();
if ($cleaned_count > 0) {
    set_message("Auto-cleanup: $cleaned_count user(s) permanently deleted after 7 days.", 'info');
}

// Handle permanent user deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['permanent_delete_id'])) {
    $permanent_delete_id = intval($_POST['permanent_delete_id']);
    if ($permanent_delete_id == $_SESSION['user_id']) {
        set_message('You cannot permanently delete your own account.', 'danger');
    } else {
        $user_to_delete = new User($db);
        $user_to_delete->id = $permanent_delete_id;
        if ($user_to_delete->permanent_delete()) {
            set_message('User permanently deleted.', 'success');
        } else {
            set_message('Failed to delete user.', 'danger');
        }
    }
}
?>

<h1 class="mb-4">Admin Dashboard</h1>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="card dashboard-card bg-primary text-white">
            <div class="card-body">
                <h5 class="card-title">Total Devices</h5>
                <h2 class="display-4"><?php echo $total_devices; ?></h2>
                <p class="card-text"><i class="bi bi-laptop"></i> Managed devices</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card dashboard-card bg-success text-white">
            <div class="card-body">
                <h5 class="card-title">Active Devices</h5>
                <h2 class="display-4"><?php echo $active_devices; ?></h2>
                <p class="card-text"><i class="bi bi-check-circle"></i> In operation</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card dashboard-card bg-warning text-dark">
            <div class="card-body">
                <h5 class="card-title">Open Issues</h5>
                <h2 class="display-4"><?php echo $open_issues; ?></h2>
                <p class="card-text"><i class="bi bi-exclamation-triangle"></i> Require attention</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card dashboard-card bg-danger text-white">
            <div class="card-body">
                <h5 class="card-title">Overdue Maintenance</h5>
                <h2 class="display-4"><?php echo $overdue_maintenance->rowCount(); ?></h2>
                <p class="card-text"><i class="bi bi-clock-history"></i> Past scheduled date</p>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Recent Issues</h5>
                <a href="../issues/index.php" class="btn btn-sm btn-primary">View All</a>
            </div>
            <div class="card-body">
                <?php if($recent_issues->rowCount() > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Device</th>
                                    <th>Issue</th>
                                    <th>Priority</th>
                                    <th>Status</th>
                                    <th>Reported</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($row = $recent_issues->fetch(PDO::FETCH_ASSOC)): ?>
                                    <tr>
                                        <td><?php echo $row['device_name']; ?></td>
                                        <td><a href="../issues/view.php?id=<?php echo $row['id']; ?>"><?php echo $row['issue_title']; ?></a></td>
                                        <td><?php echo issue_priority_badge($row['priority']); ?></td>
                                        <td><?php echo issue_status_badge($row['status']); ?></td>
                                        <td><?php echo format_date($row['reported_at']); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> No recent issues found.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Upcoming Maintenance</h5>
                <a href="../maintenance/index.php" class="btn btn-sm btn-primary">View All</a>
            </div>
            <div class="card-body">
                <?php if($upcoming_maintenance->rowCount() > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Device</th>
                                    <th>Type</th>
                                    <th>Scheduled Date</th>
                                    <th>Days Left</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($row = $upcoming_maintenance->fetch(PDO::FETCH_ASSOC)): ?>
                                    <tr>
                                        <td><?php echo $row['device_name']; ?></td>
                                        <td><?php echo $row['maintenance_type']; ?></td>
                                        <td><?php echo format_date($row['scheduled_date']); ?></td>
                                        <td>
                                            <?php 
                                            $days_left = days_between(date('Y-m-d'), $row['scheduled_date']);
                                            echo "<span class='badge bg-warning'>{$days_left} days</span>";
                                            ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> No upcoming maintenance scheduled.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Expiring Warranties</h5>
                <a href="../reports/index.php" class="btn btn-sm btn-primary">View Reports</a>
            </div>
            <div class="card-body">
                <?php if($expiring_warranty->rowCount() > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Device</th>
                                    <th>Serial Number</th>
                                    <th>Warranty Ends</th>
                                    <th>Days Left</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($row = $expiring_warranty->fetch(PDO::FETCH_ASSOC)): ?>
                                    <tr>
                                        <td><a href="../devices/view.php?id=<?php echo $row['id']; ?>"><?php echo $row['device_name']; ?></a></td>
                                        <td><?php echo $row['serial_number']; ?></td>
                                        <td><?php echo format_date($row['warranty_end']); ?></td>
                                        <td>
                                            <?php 
                                            $days_left = days_between(date('Y-m-d'), $row['warranty_end']);
                                            echo "<span class='badge bg-danger'>{$days_left} days</span>";
                                            ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> No warranties expiring in the next 30 days.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header bg-light">
                <h5 class="mb-0">System Overview</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="bi bi-people fs-1 text-primary"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h5 class="mb-0"><?php echo $total_users; ?></h5>
                                <p class="text-muted mb-0">Total Users</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="bi bi-exclamation-diamond fs-1 text-danger"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h5 class="mb-0"><?php echo $critical_issues; ?></h5>
                                <p class="text-muted mb-0">Critical Issues</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="bi bi-tools fs-1 text-warning"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h5 class="mb-0"><?php echo $maintenance_devices; ?></h5>
                                <p class="text-muted mb-0">In Maintenance</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="bi bi-wrench fs-1 text-info"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h5 class="mb-0"><?php echo $repair_devices; ?></h5>
                                <p class="text-muted mb-0">In Repair</p>
                            </div>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="d-grid gap-2">
                    <a href="../reports/generate.php" class="btn" style="background: #4169e1; color: #fff; border: none; font-weight: 500; width: 100%; padding: 0.75rem 1.25rem; border-radius: 0.5rem; box-shadow: 0 2px 8px rgba(65,105,225,0.15);"><i class="bi bi-file-earmark-bar-graph"></i> Generate Reports</a>
                    <a href="../devices/create.php" class="btn" style="background: #28a745; color: #fff; border: none; font-weight: 500; width: 100%; padding: 0.75rem 1.25rem; border-radius: 0.5rem; box-shadow: 0 2px 8px rgba(40,167,69,0.15);"><i class="bi bi-plus-circle"></i> Add New Device</a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h5 class="mb-0">User Management</h5>
            </div>
            <div class="card-body">
                <?php display_message(); ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="selectAll"></th>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Full Name</th>
                                <th>Email</th>
                                <th>Department</th>
                                <th>Role</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($u = $all_users->fetch(PDO::FETCH_ASSOC)): ?>
                                <tr>
                                    <td><?php if($u['role'] !== 'admin' && $u['id'] != $_SESSION['user_id']): ?><input type="checkbox" name="bulk_delete_ids[]" value="<?php echo $u['id']; ?>"><?php endif; ?></td>
                                    <td><?php echo $u['id']; ?></td>
                                    <td><?php echo $u['username']; ?></td>
                                    <td><?php echo $u['full_name']; ?></td>
                                    <td><?php echo $u['email']; ?></td>
                                    <td><?php echo $u['department']; ?></td>
                                    <td><?php echo ucfirst($u['role']); ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#sendMessageModal" data-userid="<?php echo $u['id']; ?>" data-username="<?php echo htmlspecialchars($u['full_name']); ?>">Send Message</button>
                                        <?php if($u['role'] !== 'admin' && $u['id'] != $_SESSION['user_id']): ?>
                                            <form method="post" action="" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                                <input type="hidden" name="delete_user_id" value="<?php echo $u['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <button type="submit" class="btn mb-3" style="background: #dc3545; color: #fff; border: none; font-weight: 500; padding: 0.5rem 1rem; border-radius: 0.25rem;" onclick="return confirm('Are you sure you want to delete selected users?');">Bulk Delete</button>
            </div>
        </div>
    </div>
</div>

<!-- Button to show deleted users -->
<div class="mt-4 mb-2">
    <button class="btn" style="background:rgb(16, 112, 255); color:rgb(255, 255, 255); border: none; font-weight: 500; padding: 0.5rem 1rem; border-radius: 0.25rem;" type="button" data-bs-toggle="collapse" data-bs-target="#deletedUsersSection" aria-expanded="false" aria-controls="deletedUsersSection">
        <i class="bi bi-person-x"></i> Show Deleted Users
    </button>
    <small class="text-muted ms-2">Deleted users are automatically purged after 7 days</small>
</div>

<!-- Deleted Users Table (Collapsible) -->
<div class="collapse" id="deletedUsersSection">
    <div class="card">
        <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Deleted Users</h5>
            <span class="badge bg-light text-dark">Auto-purge after 7 days</span>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Department</th>
                        <th>Deleted At</th>
                            <th>Days Left</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                        <?php 
                        $now = new DateTime();
                        $count = 0;
                        while($du = $deleted_users->fetch(PDO::FETCH_ASSOC)): 
                            $count++;
                            $deleted_date = new DateTime($du['deleted_at']);
                            $days_ago = $deleted_date->diff($now)->days;
                            $days_left = 7 - $days_ago;
                        ?>
                            <tr>
                                <td><?php echo htmlspecialchars($du['username']); ?></td>
                                <td><?php echo htmlspecialchars($du['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($du['email']); ?></td>
                                <td><?php echo htmlspecialchars($du['department']); ?></td>
                                <td><?php echo htmlspecialchars($du['deleted_at']); ?></td>
                                <td>
                                    <?php if($days_left <= 0): ?>
                                        <span class="badge bg-danger">Auto-delete pending</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning"><?php echo $days_left; ?> day<?php echo $days_left != 1 ? 's' : ''; ?></span>
                                    <?php endif; ?>
                                </td>
                            <td>
                                    <div class="btn-group btn-group-sm">
                                        <form method="post" action="" class="me-1">
                                    <input type="hidden" name="restore_user_id" value="<?php echo $du['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-success">Restore</button>
                                </form>
                                        <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#permanentDeleteModal<?php echo $du['id']; ?>">
                                            Delete Permanently
                                        </button>
                                    </div>
                            </td>
                        </tr>
                            
                            <!-- Permanent Delete Confirmation Modal -->
                            <div class="modal fade" id="permanentDeleteModal<?php echo $du['id']; ?>" tabindex="-1" aria-labelledby="permanentDeleteModalLabel<?php echo $du['id']; ?>" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header bg-danger text-white">
                                            <h5 class="modal-title" id="permanentDeleteModalLabel<?php echo $du['id']; ?>">Confirm Permanent Deletion</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p>Are you sure you want to permanently delete user <strong><?php echo htmlspecialchars($du['username']); ?> (<?php echo htmlspecialchars($du['full_name']); ?>)</strong>?</p>
                                            <p class="text-danger"><strong>Warning:</strong> This action cannot be undone!</p>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-outline-blue" data-bs-dismiss="modal">Cancel</button>
                                            <form method="post" action="">
                                                <input type="hidden" name="permanent_delete_id" value="<?php echo $du['id']; ?>">
                                                <button type="submit" class="btn btn-danger">Permanently Delete</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                    <?php endwhile; ?>
                        
                        <?php if($count === 0): ?>
                            <tr>
                                <td colspan="7" class="text-center">No deleted users found</td>
                            </tr>
                        <?php endif; ?>
                </tbody>
            </table>
            </div>
        </div>
    </div>
</div>

<div class="row justify-content-center mb-4">
    <div class="col-md-8">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Register New User</h4>
            </div>
            <div class="card-body">
                <form action="" method="post" id="registerUserForm">
                    <input type="hidden" name="register_user" value="1">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="full_name" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="full_name" name="full_name" required>
                    </div>
                    <div class="row mb-3">
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
                        <div class="col-md-6">
                            <label for="role" class="form-label">Role</label>
                            <select class="form-select" id="role" name="role" required>
                                <option value="user">User</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                            <small class="form-text text-muted">Password must be at least 8 characters long and contain uppercase, lowercase, number, and special character.</small>
                        </div>
                        <div class="col-md-6">
                            <label for="confirm_password" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>
                    </div>
                    <div id="passwordHelp" class="text-danger mb-2" style="display:none;"></div>
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">Register User</button>
                    </div>
                </form>
                <script>
                document.getElementById('registerUserForm').addEventListener('submit', function(e) {
                    var password = document.getElementById('password').value;
                    var confirmPassword = document.getElementById('confirm_password').value;
                    var help = document.getElementById('passwordHelp');
                    var pattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
                    if (!pattern.test(password)) {
                        help.style.display = 'block';
                        help.textContent = 'Password must be at least 8 characters long and contain uppercase, lowercase, number, and special character.';
                        e.preventDefault();
                        return false;
                    } else if (password !== confirmPassword) {
                        help.style.display = 'block';
                        help.textContent = 'Passwords do not match.';
                        e.preventDefault();
                        return false;
                    } else {
                        help.style.display = 'none';
                    }
                });
                </script>
            </div>
        </div>
    </div>
</div>

<!-- Send Message Modal -->
<div class="modal fade" id="sendMessageModal" tabindex="-1" aria-labelledby="sendMessageModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post" action="">
        <div class="modal-header">
          <h5 class="modal-title" id="sendMessageModalLabel">Send Message</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="receiver_id" id="modalReceiverId">
          <div class="mb-3">
            <label for="modalReceiverName" class="form-label">To</label>
            <input type="text" class="form-control" id="modalReceiverName" disabled>
          </div>
          <div class="mb-3">
            <label for="message_text" class="form-label">Message</label>
            <textarea class="form-control" name="message_text" id="message_text" rows="4" required></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-blue" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" name="send_message" class="btn btn-primary">Send</button>
        </div>
      </form>
    </div>
  </div>
</div>
<script>
// Bootstrap 5 modal: fill in user info when opening
var sendMessageModal = document.getElementById('sendMessageModal');
sendMessageModal.addEventListener('show.bs.modal', function (event) {
  var button = event.relatedTarget;
  var userId = button.getAttribute('data-userid');
  var userName = button.getAttribute('data-username');
  document.getElementById('modalReceiverId').value = userId;
  document.getElementById('modalReceiverName').value = userName;
});

document.getElementById('selectAll').addEventListener('change', function() {
    var checkboxes = document.querySelectorAll('input[name="bulk_delete_ids[]"]');
    for (var checkbox of checkboxes) {
        checkbox.checked = this.checked;
    }
});
</script>

<!-- Toast for new issue notification -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1100">
  <div id="newIssueToast" class="toast align-items-center text-bg-warning border-0" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex">
      <div class="toast-body" id="newIssueToastBody">
        <!-- Content will be set by JS -->
      </div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
  </div>
</div>
<script>
function showNewIssueToast(issue) {
  var toastBody = document.getElementById('newIssueToastBody');
  toastBody.innerHTML = '<b>New Issue Reported!</b><br>' +
    '<b>Device:</b> ' + issue.device_name + '<br>' +
    '<b>Title:</b> ' + issue.issue_title + '<br>' +
    '<b>By:</b> ' + issue.reporter_name + '<br>' +
    '<b>At:</b> ' + issue.reported_at;
  var toast = new bootstrap.Toast(document.getElementById('newIssueToast'));
  toast.show();
}

let lastIssueId = localStorage.getItem('lastShownIssueId');
function pollNewIssues() {
  fetch('../../api/admin_new_issues.php')
    .then(response => response.json())
    .then(issue => {
      if (issue && issue.id && issue.id !== lastIssueId) {
        showNewIssueToast(issue);
        localStorage.setItem('lastShownIssueId', issue.id);
        lastIssueId = issue.id;
      }
    });
}
if (typeof bootstrap !== 'undefined') {
  setInterval(pollNewIssues, 10000); // every 10 seconds
  pollNewIssues(); // initial check
}
</script>

<div class="card mt-4">
    <div class="card-header bg-dark text-white">
        <h4 class="mb-0">Device Lifecycle Management</h4>
    </div>
    <div class="card-body">
        <ul class="nav nav-tabs mb-3" id="lifecycleTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="acquisition-tab" data-bs-toggle="tab" data-bs-target="#acquisition" type="button" role="tab">Acquisition</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="issue-analytics-tab" data-bs-toggle="tab" data-bs-target="#issue-analytics" type="button" role="tab">Issue Analytics</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="maintenance-tab" data-bs-toggle="tab" data-bs-target="#maintenance" type="button" role="tab">Maintenance History</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="recurring-tab" data-bs-toggle="tab" data-bs-target="#recurring" type="button" role="tab">Recurring Issues</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="recommendations-tab" data-bs-toggle="tab" data-bs-target="#recommendations" type="button" role="tab">Recommendations</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="status-tab" data-bs-toggle="tab" data-bs-target="#status" type="button" role="tab">Status Monitoring</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="disposal-tab" data-bs-toggle="tab" data-bs-target="#disposal" type="button" role="tab">Disposal</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="lifecycle-tab" data-bs-toggle="tab" data-bs-target="#lifecycle" type="button" role="tab">Lifecycle Management</button>
            </li>
        </ul>
        <div class="tab-content" id="lifecycleTabsContent">
            <div class="tab-pane fade show active" id="acquisition" role="tabpanel">
                <h5>Available Devices</h5>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Device Name</th>
                                <th>Purchase Date</th>
                                <th>Warranty End</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($acquisitions)): ?>
                                <?php foreach ($acquisitions as $acq): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($acq['device_name']); ?></td>
                                        <td><?php echo htmlspecialchars($acq['purchase_date']); ?></td>
                                        <td><?php echo htmlspecialchars($acq['warranty_end']); ?></td>
                                        <td><?php echo htmlspecialchars($acq['status']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="4" class="text-center">No devices found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="tab-pane fade" id="issue-analytics" role="tabpanel">
                <h5>Issue Reporting Analytics</h5>
                <div class="table-responsive mb-4">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Device Name</th>
                                <th>Total Reports</th>
                                <th>Hardware Issues</th>
                                <th>Software Issues</th>
                                <th>User Error</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($issue_analytics as $row): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['device_name']); ?></td>
                                    <td><?php echo $row['total_reports']; ?></td>
                                    <td><?php echo $row['hardware']; ?></td>
                                    <td><?php echo $row['software']; ?></td>
                                    <td><?php echo $row['user_error']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div>
                    <canvas id="issueTypeChart" height="100"></canvas>
                </div>
                <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
                <script>
                var ctx = document.getElementById('issueTypeChart').getContext('2d');
                var issueTypeChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: <?php echo json_encode(array_map('ucfirst', $issue_types)); ?>,
                        datasets: [{
                            label: 'Total Issues by Type',
                            data: <?php echo json_encode(array_values($issue_type_totals)); ?>,
                            backgroundColor: ['#dc3545', '#0d6efd', '#ffc107'],
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: { display: false },
                            title: { display: true, text: 'Issues by Type' }
                        }
                    }
                });
                </script>
            </div>
            <div class="tab-pane fade" id="maintenance" role="tabpanel">
                <h5>Maintenance History Management</h5>
                
                <!-- Summary statistics for maintenance -->
                <div class="row mb-4 mb-md-5">
<div class="col-md-3">
    <div class="card bg-primary text-white h-100">
        <div class="card-body">
            <h5 class="card-title">Total Maintenance Records</h5>
            <h2 class="display-4"><?php 
                $stmt = $db->prepare("SELECT COUNT(*) FROM maintenance");
                $stmt->execute();
                echo $stmt->fetchColumn();
            ?></h2>
        </div>
    </div>
</div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white h-100">
                            <div class="card-body">
                                <h5 class="card-title">Completed</h5>
                                <h2 class="display-4"><?php 
                                    $stmt = $db->prepare("SELECT COUNT(*) FROM maintenance WHERE status = 'completed'");
                                    $stmt->execute();
                                    echo $stmt->fetchColumn();
                                ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-dark h-100">
                            <div class="card-body">
                                <h5 class="card-title">Scheduled</h5>
                                <h2 class="display-4"><?php 
                                    $stmt = $db->prepare("SELECT COUNT(*) FROM maintenance WHERE status = 'scheduled'");
                                    $stmt->execute();
                                    echo $stmt->fetchColumn();
                                ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white h-100">
                            <div class="card-body">
                                <h5 class="card-title">Average Cost</h5>
                                <h2 class="display-4">$<?php 
                                    $stmt = $db->prepare("SELECT AVG(maintenance_cost) FROM maintenance WHERE maintenance_cost IS NOT NULL");
                                    $stmt->execute();
                                    echo number_format($stmt->fetchColumn(), 2);
                                ?></h2>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Enhanced maintenance history table -->
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="maintenanceHistoryTable">
                        <thead>
                            <tr>
                                <th>Device</th>
                                <th>Status</th>
                                <th>Type</th>
                                <th>Scheduled Date</th>
                                <th>Completion Date</th>
                                <th>Cost</th>
                                <th>Health Impact</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        // Fetch all maintenance records with more details
                        $stmt_maint = $db->prepare("
                            SELECT 
                                m.id, m.device_id, m.maintenance_type, m.scheduled_date, 
                                m.status as maint_status, m.completion_date, 
                                m.maintenance_cost, m.device_health_impact,
                                m.issues_found, m.resolution, m.performed_by, 
                                d.device_name, d.status as device_status,
                                u.full_name as technician 
                            FROM maintenance m 
                            LEFT JOIN devices d ON m.device_id = d.id 
                            LEFT JOIN users u ON m.performed_by = u.id
                            ORDER BY m.scheduled_date DESC
                        ");
                        $stmt_maint->execute();
                        $all_maintenance = $stmt_maint->fetchAll(PDO::FETCH_ASSOC);
                        
                        foreach ($all_maintenance as $row):
                            // Set row class based on maintenance status
                            $rowClass = '';
                            switch ($row['maint_status']) {
                                case 'completed':
                                    $rowClass = 'table-success';
                                    break;
                                case 'scheduled':
                                    $rowClass = 'table-warning';
                                    break;
                                case 'in_progress':
                                    $rowClass = 'table-primary';
                                    break;
                                case 'cancelled':
                                    $rowClass = 'table-secondary';
                                    break;
                            }
                            
                            // Format the health impact for display
                            $healthImpact = '';
                            switch ($row['device_health_impact']) {
                                case 'significant_improvement':
                                    $healthImpact = '<span class="badge bg-success">Significant Improvement</span>';
                                    break;
                                case 'minor_improvement':
                                    $healthImpact = '<span class="badge bg-info">Minor Improvement</span>';
                                    break;
                                case 'no_change':
                                    $healthImpact = '<span class="badge bg-secondary">No Change</span>';
                                    break;
                                case 'deterioration':
                                    $healthImpact = '<span class="badge bg-danger">Deterioration</span>';
                                    break;
                                default:
                                    $healthImpact = '-';
                            }
                        ?>
                        <tr class="<?php echo $rowClass; ?>">
                                    <td><?php echo htmlspecialchars($row['device_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['maint_status']); ?></td>
                            <td><?php echo htmlspecialchars($row['maintenance_type']); ?></td>
                            <td><?php echo htmlspecialchars($row['scheduled_date']); ?></td>
                            <td><?php echo $row['completion_date'] ? htmlspecialchars($row['completion_date']) : '-'; ?></td>
                            <td><?php echo $row['maintenance_cost'] ? '$'.htmlspecialchars(number_format($row['maintenance_cost'], 2)) : '-'; ?></td>
                            <td><?php echo $healthImpact; ?></td>
                            <td>
                                <a href="../maintenance/view.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-info">
                                    <i class="bi bi-eye"></i> Details
                                </a>
                            </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Maintenance analytics chart -->
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">Maintenance Status Distribution</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="maintenanceStatusChart" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">Maintenance Health Impact</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="maintenanceHealthImpactChart" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade" id="recurring" role="tabpanel">
                <h5>Recurring Issue Analysis</h5>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Device Name</th>
                                <th>Status</th>
                                <th>Issue Type</th>
                                <th>Occurrences</th>
                                <th>Repetitive Problem</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recurring_issues as $row): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['device_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['status']); ?></td>
                                    <td><?php echo htmlspecialchars($row['issue_type']); ?></td>
                                    <td><?php echo $row['count']; ?></td>
                                    <td>
                                        <?php if ($row['flag']): ?>
                                            <span class="badge bg-danger">Yes</span>
                                        <?php else: ?>
                                            <span class="badge bg-success">No</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="alert alert-info mt-3">
                    <i class="bi bi-info-circle"></i> Devices flagged as repetitive have frequent issues and may require preventive action or replacement.
                </div>
            </div>
            <div class="tab-pane fade" id="recommendations" role="tabpanel">
                <h5>Intelligent Recommendations</h5>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Device Name</th>
                                <th>Status</th>
                                <th>Recommendation</th>
                                <th>Reliability Score</th>
                                <th>Cost-Benefit Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recommendations as $row): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['device_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['status']); ?></td>
                                    <td><?php echo htmlspecialchars($row['recommendation']); ?></td>
                                    <td><?php echo htmlspecialchars($row['reliability_score']); ?></td>
                                    <td><?php echo htmlspecialchars($row['cost_benefit']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="alert alert-info mt-3">
                    <i class="bi bi-lightbulb"></i> Recommendations are based on device performance, issue frequency, and cost analysis.
                </div>
            </div>
            <div class="tab-pane fade" id="status" role="tabpanel">
                <h5>Device Status Monitoring</h5>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Device Name</th>
                                <th>Status</th>
                                <th>Health Score</th>
                                <th>Alert</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($status_monitoring as $row): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['device_name']); ?></td>
                                    <td>
                                        <?php
                                        $badge = 'success';
                                        if ($row['status'] === 'Needs Attention') $badge = 'warning';
                                        if ($row['status'] === 'Critical') $badge = 'danger';
                                        ?>
                                        <span class="badge bg-<?php echo $badge; ?>"><?php echo htmlspecialchars($row['status']); ?></span>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['health']); ?></td>
                                    <td><?php echo htmlspecialchars($row['alert']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="alert alert-info mt-3">
                    <i class="bi bi-activity"></i> Status and health scores are updated in real time based on device metrics and alerts.
                </div>
            </div>
            <div class="tab-pane fade" id="disposal" role="tabpanel">
                <h5>Device Disposal</h5>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Device Name</th>
                                <th>Reason for Disposal</th>
                                <th>Disposal Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($disposal_devices as $row): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['device_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['reason']); ?></td>
                                    <td><?php echo htmlspecialchars($row['disposal_date']); ?></td>
                                    <td>
                                        <?php
                                        $badge = $row['status'] === 'Disposed' ? 'success' : 'secondary';
                                        ?>
                                        <span class="badge bg-<?php echo $badge; ?>"><?php echo htmlspecialchars($row['status']); ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="alert alert-info mt-3">
                    <i class="bi bi-trash"></i> This is demo data. Disposal records will be managed and updated here.
                </div>
            </div>
            <div class="tab-pane fade" id="lifecycle" role="tabpanel">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title">Device Lifecycle Distribution</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="lifecycleChart" width="100%" height="50"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title">Device Health Distribution</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="healthChart" width="100%" height="50"></canvas>
                            </div>
        </div>
    </div>
                </div>
                
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title">Lifecycle Management Overview</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="lifecycleTable">
                                <thead>
                                    <tr>
                                        <th>Device</th>
                                        <th>Current Stage</th>
                                        <th>Health Score</th>
                                        <th>Age</th>
                                        <th>Next Action</th>
                                        <th>Recommendation</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($all_devices as $dev): 
                                        $device->id = $dev['id'];
                                        if ($device->read_single()):
                                            $health = $device->calculate_health_score();
                                            $stage = $device->determine_lifecycle_stage();
                                            $recommendation = $device->get_replacement_recommendation();
                                            
                                            // Calculate age
                                            $purchase = new DateTime($device->purchase_date);
                                            $today = new DateTime();
                                            $age = $purchase->diff($today)->format('%y years, %m months');
                                            
                                            // Determine next action
                                            if ($device->needs_maintenance()) {
                                                $nextAction = "Schedule maintenance";
                                            } elseif ($recommendation['recommendation'] == 'Replace') {
                                                $nextAction = "Plan replacement";
                                            } else {
                                                $nextAction = "Regular monitoring";
                                            }
                                            
                                            // Set row color based on health
                                            $rowClass = '';
                                            if ($health < 40) $rowClass = 'table-danger';
                                            elseif ($health < 70) $rowClass = 'table-warning';
                                            elseif ($health >= 70) $rowClass = 'table-success';
                                    ?>
                                    <tr class="<?php echo $rowClass; ?>">
                                        <td><?php echo $device->device_name; ?></td>
                                        <td><?php echo $stage; ?></td>
                                        <td><?php echo $health; ?></td>
                                        <td><?php echo $age; ?></td>
                                        <td><?php echo $nextAction; ?></td>
                                        <td>
                                            <strong><?php echo $recommendation['recommendation']; ?></strong><br>
                                            <small><?php echo $recommendation['reason']; ?></small>
                                        </td>
                                    </tr>
                                    <?php 
                                        endif;
                                    endforeach; 
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="mb-4 mt-3">
    <a href="../controllers/lifecycle_update.php" class="btn" style="background: #4169e1; color: #fff; border: none; font-weight: 500; padding: 0.75rem 1.25rem; border-radius: 0.5rem; box-shadow: 0 2px 8px rgba(65,105,225,0.15);">
        <i class="bi bi-arrow-repeat"></i> Update Lifecycle Data
    </a>
    <span class="ms-2 text-muted">Click to recalculate health scores and recommendations for all devices</span>
</div>

<?php include $base_path . 'includes/footer.php'; ?>

<script>
// Lifecycle Distribution Chart
var lifecycleCtx = document.getElementById('lifecycleChart');
var lifecycleData = {
    labels: <?php echo json_encode(array_keys($lifecycle_counts)); ?>,
    datasets: [{
        data: <?php echo json_encode(array_values($lifecycle_counts)); ?>,
        backgroundColor: ['#4e73df', '#1cc88a', '#f6c23e', '#e74a3b']
    }]
};

var lifecycleChart = new Chart(lifecycleCtx, {
    type: 'pie',
    data: lifecycleData,
    options: {
        responsive: true,
        maintainAspectRatio: false,
        tooltips: {
            callbacks: {
                label: function(tooltipItem, data) {
                    var dataset = data.datasets[tooltipItem.datasetIndex];
                    var currentValue = dataset.data[tooltipItem.index];
                    var total = dataset.data.reduce(function(previousValue, currentValue) {
                        return previousValue + currentValue;
                    });
                    var percentage = Math.round((currentValue/total) * 100);
                    return data.labels[tooltipItem.index] + ': ' + currentValue + ' (' + percentage + '%)';
                }
            }
        }
    }
});

// Health Distribution Chart
var healthCtx = document.getElementById('healthChart');
var healthData = {
    labels: <?php echo json_encode(array_keys($health_counts)); ?>,
    datasets: [{
        data: <?php echo json_encode(array_values($health_counts)); ?>,
        backgroundColor: ['#1cc88a', '#f6c23e', '#e74a3b']
    }]
};

var healthChart = new Chart(healthCtx, {
    type: 'pie',
    data: healthData,
    options: {
        responsive: true,
        maintainAspectRatio: false,
        tooltips: {
            callbacks: {
                label: function(tooltipItem, data) {
                    var dataset = data.datasets[tooltipItem.datasetIndex];
                    var currentValue = dataset.data[tooltipItem.index];
                    var total = dataset.data.reduce(function(previousValue, currentValue) {
                        return previousValue + currentValue;
                    });
                    var percentage = Math.round((currentValue/total) * 100);
                    return data.labels[tooltipItem.index] + ': ' + currentValue + ' (' + percentage + '%)';
                }
            }
        }
    }
});

// Add this script before the closing body tag
// Add maintenance charts JavaScript
var maintenanceStatusCtx = document.getElementById('maintenanceStatusChart');
if (maintenanceStatusCtx) {
    // Count maintenance records by status
    <?php
    $stmt_status = $db->prepare("
        SELECT status, COUNT(*) as count FROM maintenance 
        GROUP BY status
    ");
    $stmt_status->execute();
    $status_data = $stmt_status->fetchAll(PDO::FETCH_ASSOC);
    
    $status_labels = [];
    $status_counts = [];
    $status_colors = [];
    
    foreach ($status_data as $row) {
        $status_labels[] = ucfirst($row['status']);
        $status_counts[] = $row['count'];
        
        // Set color based on status
        switch($row['status']) {
            case 'completed': 
                $status_colors[] = '#28a745'; // green
                break;
            case 'scheduled': 
                $status_colors[] = '#ffc107'; // yellow
                break;
            case 'in_progress': 
                $status_colors[] = '#0d6efd'; // blue
                break;
            case 'cancelled': 
                $status_colors[] = '#6c757d'; // gray
                break;
            default: 
                $status_colors[] = '#6c757d';
        }
    }
    ?>
    
    new Chart(maintenanceStatusCtx, {
        type: 'pie',
        data: {
            labels: <?php echo json_encode($status_labels); ?>,
            datasets: [{
                data: <?php echo json_encode($status_counts); ?>,
                backgroundColor: <?php echo json_encode($status_colors); ?>
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
}

var healthImpactCtx = document.getElementById('maintenanceHealthImpactChart');
if (healthImpactCtx) {
    // Count maintenance records by health impact
    <?php
    $stmt_impact = $db->prepare("
        SELECT device_health_impact, COUNT(*) as count FROM maintenance 
        WHERE device_health_impact IS NOT NULL 
        GROUP BY device_health_impact
    ");
    $stmt_impact->execute();
    $impact_data = $stmt_impact->fetchAll(PDO::FETCH_ASSOC);
    
    $impact_labels = [];
    $impact_counts = [];
    $impact_colors = [];
    
    foreach ($impact_data as $row) {
        switch($row['device_health_impact']) {
            case 'significant_improvement': 
                $impact_labels[] = 'Significant Improvement';
                $impact_colors[] = '#28a745'; // green
                break;
            case 'minor_improvement': 
                $impact_labels[] = 'Minor Improvement';
                $impact_colors[] = '#17a2b8'; // cyan
                break;
            case 'no_change': 
                $impact_labels[] = 'No Change';
                $impact_colors[] = '#6c757d'; // gray
                break;
            case 'deterioration': 
                $impact_labels[] = 'Deterioration';
                $impact_colors[] = '#dc3545'; // red
                break;
            default:
                $impact_labels[] = ucfirst($row['device_health_impact']);
                $impact_colors[] = '#6c757d';
        }
        $impact_counts[] = $row['count'];
    }
    ?>
    
    new Chart(healthImpactCtx, {
        type: 'pie',
        data: {
            labels: <?php echo json_encode($impact_labels); ?>,
            datasets: [{
                data: <?php echo json_encode($impact_counts); ?>,
                backgroundColor: <?php echo json_encode($impact_colors); ?>
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        boxWidth: 12,
                        font: {
                            size: 11
                        }
                    }
                }
            },
            layout: {
                padding: {
                    left: 10,
                    right: 10,
                    top: 0,
                    bottom: 10
                }
            }
        }
    });
}
</script>