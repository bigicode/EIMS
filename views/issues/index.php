<?php
// Set relative path for includes
$base_path = '../../';
require_once $base_path . 'config/database.php';
require_once $base_path . 'controllers/IssueController.php';
require_once $base_path . 'includes/functions.php';
require_once $base_path . 'includes/auth.php';

// Require login for this page
require_login();

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Initialize issue controller
$controller = new IssueController();

// Get issues based on filters
$device_id = isset($_GET['device_id']) ? clean_input($_GET['device_id']) : null;
$status = isset($_GET['status']) ? clean_input($_GET['status']) : null;
$priority = isset($_GET['priority']) ? clean_input($_GET['priority']) : null;

// Get issues based on filters
if($device_id) {
    $result = $controller->getIssuesByDevice($device_id);
    $filter_text = "for Device ID: $device_id";
} elseif($status) {
    $result = $controller->getIssuesByStatus($status);
    $filter_text = "with Status: $status";
} elseif($priority) {
    $result = $controller->getIssuesByPriority($priority);
    $filter_text = "with Priority: $priority";
} else {
    // Get all issues or only those reported by current user if not admin
    if(is_admin()) {
        $result = $controller->getIssues();
        $filter_text = "All Issues";
    } else {
        $result = $controller->getIssuesByReporter($_SESSION['user_id']);
        $filter_text = "Reported by You";
    }
}

// Include header
$page_title = "Issue Management";
include $base_path . 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Issue Management <?php echo isset($filter_text) ? "<small class='text-muted'>($filter_text)</small>" : ''; ?></h1>
    <a href="create.php" class="btn" style="background: #4169e1; color: #fff; border: none; font-weight: 500; padding: 0.75rem 1.25rem; border-radius: 0.5rem; box-shadow: 0 2px 8px rgba(65,105,225,0.15);"><i class="bi bi-plus-circle"></i> Report New Issue</a>
</div>

<div class="card mb-4">
    <div class="card-header bg-light">
        <h5 class="mb-0">Filter Issues</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3 mb-3">
                <label for="statusFilter" class="form-label">By Status</label>
                <select class="form-select" id="statusFilter" onchange="window.location.href='index.php?status='+this.value">
                    <option value="">All Statuses</option>
                    <option value="open" <?php echo ($status == 'open') ? 'selected' : ''; ?>>Open</option>
                    <option value="in_progress" <?php echo ($status == 'in_progress') ? 'selected' : ''; ?>>In Progress</option>
                    <option value="resolved" <?php echo ($status == 'resolved') ? 'selected' : ''; ?>>Resolved</option>
                    <option value="closed" <?php echo ($status == 'closed') ? 'selected' : ''; ?>>Closed</option>
                </select>
            </div>
            <div class="col-md-3 mb-3">
                <label for="priorityFilter" class="form-label">By Priority</label>
                <select class="form-select" id="priorityFilter" onchange="window.location.href='index.php?priority='+this.value">
                    <option value="">All Priorities</option>
                    <option value="low" <?php echo ($priority == 'low') ? 'selected' : ''; ?>>Low</option>
                    <option value="medium" <?php echo ($priority == 'medium') ? 'selected' : ''; ?>>Medium</option>
                    <option value="high" <?php echo ($priority == 'high') ? 'selected' : ''; ?>>High</option>
                    <option value="critical" <?php echo ($priority == 'critical') ? 'selected' : ''; ?>>Critical</option>
                </select>
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label">&nbsp;</label>
                <a href="index.php" class="btn btn-outline-blue d-block">Clear Filters</a>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Issue Title</th>
                        <th>Device</th>
                        <th>Reported By</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Reported Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($result->rowCount() > 0): ?>
                        <?php while($row = $result->fetch(PDO::FETCH_ASSOC)): ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo $row['issue_title']; ?></td>
                                <td><a href="../devices/view.php?id=<?php echo $row['device_id']; ?>"><?php echo $row['device_name'] ?? ''; ?></a></td>
                                <td><?php echo $row['reporter_name'] ?? ''; ?></td>
                                <td><?php echo issue_priority_badge($row['priority']); ?></td>
                                <td>
                                    <?php 
                                    $status_class = '';
                                    switch($row['status']) {
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
                                    <span class="badge bg-<?php echo $status_class; ?>"><?php echo $row['status']; ?></span>
                                </td>
                                <td><?php echo format_date($row['reported_at']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center">No issues found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include $base_path . 'includes/footer.php'; ?>