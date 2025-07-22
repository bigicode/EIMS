<?php
// Set relative path for includes
$base_path = '../../';
require_once $base_path . 'config/database.php';
require_once $base_path . 'controllers/IssueController.php';
require_once $base_path . 'includes/functions.php';
require_once $base_path . 'includes/auth.php';

// Require login for this page
require_login();

// Check if ID is provided
if(!isset($_GET['id']) || empty($_GET['id'])) {
    set_message("No issue specified.", "warning");
    redirect("index.php");
}

$issue_id = clean_input($_GET['id']);

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Initialize issue controller
$controller = new IssueController();

// Get issue by ID
$issue = $controller->getIssueById($issue_id);

if(!$issue) {
    set_message("Issue not found.", "danger");
    redirect("index.php");
}

// Fetch device info
require_once $base_path . 'models/Device.php';
$device = new Device($db);
$device->id = $issue->device_id;
$device_found = $device->read_single();

// Fetch reporter info
require_once $base_path . 'models/User.php';
$reporter = new User($db);
$reporter->id = $issue->reported_by;
$reporter_found = $reporter->read_single();

// Check if user has permission to view this issue
if(!is_admin() && $issue->reported_by != $_SESSION['user_id'] && $issue->device_department != $_SESSION['department']) {
    set_message("You don't have permission to view this issue.", "danger");
    redirect("index.php");
}

// Process form submission for updating status or adding resolution
if($_SERVER['REQUEST_METHOD'] == 'POST' && is_admin()) {
    if(isset($_POST['update_status'])) {
        // Update issue status
        $result = $controller->updateIssueStatus($issue_id, $_POST['status'], $_POST['resolution_notes']);
        
        if($result['success']) {
            set_message($result['message'], "success");
            redirect("view.php?id=" . $issue_id);
        } else {
            set_message($result['message'], "danger");
        }
    }
}

// Include header
$page_title = "Issue Details - " . $issue->issue_title;
include $base_path . 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Issue Details: #<?php echo $issue->id; ?></h1>
    <div>
        <a href="index.php" class="btn btn-outline-blue me-2"><i class="bi bi-arrow-left"></i> Back to Issues</a>
        <?php if(is_admin() || $_SESSION['user_id'] == $issue->reported_by): ?>
            <a href="update.php?id=<?php echo $issue_id; ?>" class="btn btn-outline-blue"><i class="bi bi-pencil"></i> Edit Issue</a>
        <?php endif; ?>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0"><?php echo $issue->issue_title; ?></h5>
            </div>
            <div class="card-body">
                <div class="mb-4">
                    <h6 class="fw-bold">Description:</h6>
                    <p class="mb-0"><?php echo nl2br(htmlspecialchars($issue->issue_description)); ?></p>
                </div>
                
                <?php if($issue->resolution_notes): ?>
                    <div class="mb-0">
                        <h6 class="fw-bold">Resolution Notes:</h6>
                        <p class="mb-0"><?php echo nl2br(htmlspecialchars($issue->resolution_notes)); ?></p>
                    </div>
                <?php endif; ?>
            </div>
            <div class="card-footer bg-light">
                <div class="d-flex justify-content-between">
                    <span>Reported: <?php echo format_date($issue->reported_at); ?></span>
                    <?php if($issue->resolved_at): ?>
                        <span>Resolved: <?php echo format_date($issue->resolved_at); ?></span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <?php if(is_admin() && $issue->status != 'closed'): ?>
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Update Issue Status</h5>
                </div>
                <div class="card-body">
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?id=' . $issue_id; ?>" method="post">
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="open" <?php echo ($issue->status == 'open') ? 'selected' : ''; ?>>Open</option>
                                <option value="in_progress" <?php echo ($issue->status == 'in_progress') ? 'selected' : ''; ?>>In Progress</option>
                                <option value="resolved" <?php echo ($issue->status == 'resolved') ? 'selected' : ''; ?>>Resolved</option>
                                <option value="closed" <?php echo ($issue->status == 'closed') ? 'selected' : ''; ?>>Closed</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="resolution_notes" class="form-label">Resolution Notes</label>
                            <textarea class="form-control" id="resolution_notes" name="resolution_notes" rows="3"><?php echo $issue->resolution_notes; ?></textarea>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" name="update_status" class="btn btn-primary">Update Status</button>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">Issue Information</h5>
            </div>
            <div class="card-body">
                <p><strong>Status:</strong>
                    <?php 
                    $status_class = '';
                    switch($issue->status) {
                        case 'open':
                            $status_class = 'danger';
                            break;
                        case 'in_progress':
                            $status_class = 'warning';
                            break;
                        case 'resolved':
                            $status_class = 'success';
                            break;
                        case 'closed':
                            $status_class = 'secondary';
                            break;
                    }
                    ?>
                    <span class="badge bg-<?php echo $status_class; ?>"><?php echo $issue->status; ?></span>
                </p>
                <p><strong>Priority:</strong> <?php echo issue_priority_badge($issue->priority); ?></p>
                <p><strong>Reported By:</strong> <?php echo $reporter_found ? htmlspecialchars($reporter->full_name) : 'Unknown'; ?></p>
                <p><strong>Department:</strong> <?php echo $reporter_found ? htmlspecialchars($reporter->department) : 'Unknown'; ?></p>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="mb-0">Device Information</h5>
            </div>
            <div class="card-body">
                <p><strong>Device:</strong> <a href="../devices/view.php?id=<?php echo $issue->device_id; ?>"><?php echo $device_found ? htmlspecialchars($device->device_name) : 'Unknown'; ?></a></p>
                <p><strong>Type:</strong> <?php echo $device_found ? htmlspecialchars($device->device_type) : 'Unknown'; ?></p>
                <p><strong>Serial Number:</strong> <?php echo $device_found ? htmlspecialchars($device->serial_number) : 'Unknown'; ?></p>
                <p><strong>Status:</strong> <?php echo $device_found ? device_status_badge($device->status) : 'Unknown'; ?></p>
                <p><strong>Department:</strong> <?php echo $device_found ? htmlspecialchars($device->department) : 'Unknown'; ?></p>
            </div>
        </div>
    </div>
</div>

<?php include $base_path . 'includes/footer.php'; ?>