<?php
// Set relative path for includes
$base_path = '../../';
require_once $base_path . 'config/database.php';
require_once $base_path . 'models/Device.php';
require_once $base_path . 'models/Issue.php';
require_once $base_path . 'models/Message.php';
require_once $base_path . 'includes/functions.php';
require_once $base_path . 'includes/auth.php';

// Require login for this page
require_login();

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Initialize objects
$device = new Device($db);
$issue = new Issue($db);
$message_model = new Message($db);

// Get user's department
$user_department = $_SESSION['department'];
$user_id = $_SESSION['user_id'];

// Get devices assigned to user
$assigned_devices = $device->get_by_assigned_user($user_id);
$assigned_count = $assigned_devices->rowCount();

// Get devices in user's department
$department_devices = $device->get_by_department($user_department);
$department_count = $department_devices->rowCount();

// Get issues reported by user
$user_issues = $issue->get_by_reporter($user_id);
$open_issues = $issue->count_by_reporter_and_status($user_id, 'open');

// Get unread messages
$unread_messages = $message_model->get_unread($user_id);

// Include header
$page_title = "User Dashboard";
include $base_path . 'includes/header.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['read_message_id'])) {
    $message_model->mark_as_read(intval($_POST['read_message_id']));
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit;
}
?>

<?php if($unread_messages->rowCount() > 0): ?>
<div class="alert alert-info alert-dismissible fade show" role="alert">
    <strong>Notifications:</strong>
    <ul class="mb-0">
        <?php while($msg = $unread_messages->fetch(PDO::FETCH_ASSOC)): ?>
            <li>
                <b>From:</b> <?php echo htmlspecialchars($msg['sender_name']); ?> <br>
                <b>Message:</b> <?php echo htmlspecialchars($msg['message']); ?> <br>
                <form method="post" action="" style="display:inline;">
                    <input type="hidden" name="read_message_id" value="<?php echo $msg['id']; ?>">
                    <button type="submit" class="btn btn-sm btn-success">Mark as read</button>
                </form>
            </li>
        <?php endwhile; ?>
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<h1 class="mb-4">Welcome, <?php echo $_SESSION['full_name']; ?></h1>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h5 class="card-title">Assigned Devices</h5>
                <h2 class="display-4"><?php echo $assigned_count; ?></h2>
                <p class="card-text"><i class="bi bi-laptop"></i> Devices assigned to you</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-info text-white">
            <div class="card-body">
                <h5 class="card-title">Department Devices</h5>
                <h2 class="display-4"><?php echo $department_count; ?></h2>
                <p class="card-text"><i class="bi bi-building"></i> In <?php echo $user_department; ?> department</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-warning text-dark">
            <div class="card-body">
                <h5 class="card-title">Open Issues</h5>
                <h2 class="display-4"><?php echo $open_issues; ?></h2>
                <p class="card-text"><i class="bi bi-exclamation-triangle"></i> Reported by you</p>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Your Devices</h5>
                <a href="../devices/index.php" class="btn btn-sm btn-primary">View All</a>
            </div>
            <div class="card-body">
                <?php if($assigned_count > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Device</th>
                                    <th>Type</th>
                                    <th>Serial Number</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($row = $assigned_devices->fetch(PDO::FETCH_ASSOC)): ?>
                                    <tr>
                                        <td><a href="../devices/view.php?id=<?php echo $row['id']; ?>"><?php echo $row['device_name']; ?></a></td>
                                        <td><?php echo $row['device_type']; ?></td>
                                        <td><?php echo $row['serial_number']; ?></td>
                                        <td><?php echo device_status_badge($row['status']); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> You don't have any devices assigned to you.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Your Reported Issues</h5>
                <a href="../issues/index.php" class="btn btn-sm btn-primary">View All</a>
            </div>
            <div class="card-body">
                <?php if($user_issues->rowCount() > 0): ?>
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
                                <?php while($row = $user_issues->fetch(PDO::FETCH_ASSOC)): ?>
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
                        <i class="bi bi-info-circle"></i> You haven't reported any issues yet.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="mb-0">Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div class="card h-100 shadow-sm hover-card">
                            <div class="card-body text-center">
                                <div class="icon-circle mb-3" style="background: rgba(65,105,225,0.1); width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                                    <i class="bi bi-exclamation-triangle fs-1" style="color: #4169e1;"></i>
                                </div>
                                <h5 class="card-title">Report an Issue</h5>
                                <p class="card-text text-muted">Submit a new problem report</p>
                                <a href="../issues/create.php" class="btn" style="background: #4169e1; color: #fff; border: none; font-weight: 500; padding: 0.75rem 1.25rem; border-radius: 0.5rem; box-shadow: 0 2px 8px rgba(65,105,225,0.15); width: 100%;">
                                    Report Issue
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card h-100 shadow-sm hover-card">
                            <div class="card-body text-center">
                                <div class="icon-circle mb-3" style="background: rgba(65,105,225,0.1); width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                                    <i class="bi bi-laptop fs-1" style="color: #4169e1;"></i>
                                </div>
                                <h5 class="card-title">Browse Devices</h5>
                                <p class="card-text text-muted">View and manage your devices</p>
                                <a href="../devices/index.php" class="btn" style="background: #4169e1; color: #fff; border: none; font-weight: 500; padding: 0.75rem 1.25rem; border-radius: 0.5rem; box-shadow: 0 2px 8px rgba(65,105,225,0.15); width: 100%;">
                                    View Devices
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card h-100 shadow-sm hover-card">
                            <div class="card-body text-center">
                                <div class="icon-circle mb-3" style="background: rgba(65,105,225,0.1); width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                                    <i class="bi bi-person-circle fs-1" style="color: #4169e1;"></i>
                                </div>
                                <h5 class="card-title">Update Profile</h5>
                                <p class="card-text text-muted">Manage your account settings</p>
                                <a href="<?php echo $base_path; ?>profile.php" class="btn" style="background: #4169e1; color: #fff; border: none; font-weight: 500; padding: 0.75rem 1.25rem; border-radius: 0.5rem; box-shadow: 0 2px 8px rgba(65,105,225,0.15); width: 100%;">
                                    Edit Profile
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <style>
                .hover-card {
                    transition: transform 0.3s ease, box-shadow 0.3s ease;
                }
                .hover-card:hover {
                    transform: translateY(-5px);
                    box-shadow: 0 10px 20px rgba(65,105,225,0.2) !important;
                }
                </style>
            </div>
        </div>
    </div>
</div>

<?php include $base_path . 'includes/footer.php'; ?>