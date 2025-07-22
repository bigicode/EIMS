<?php
// Set relative path for includes
$base_path = '../../';
require_once $base_path . 'config/database.php';
require_once $base_path . 'controllers/IssueController.php';
require_once $base_path . 'models/Device.php';
require_once $base_path . 'includes/functions.php';
require_once $base_path . 'includes/auth.php';

// Require login for this page
require_login();

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Initialize device object to get devices
$device = new Device($db);

// If device_id is provided in URL, get that device
$selected_device_id = null;
if(isset($_GET['device_id']) && !empty($_GET['device_id'])) {
    $selected_device_id = clean_input($_GET['device_id']);
    $device->id = $selected_device_id;
    if(!$device->read_single()) {
        $selected_device_id = null;
    }
}

// Get devices based on user role
if(is_admin()) {
    // Admin can see all active devices
    $devices = $device->read_by_status('active');
} else {
    // Regular users can only see devices in their department or assigned to them
    $user_id = $_SESSION['user_id'];
    $user_department = $_SESSION['department'];
    $devices = $device->get_by_department_or_user($user_department, $user_id);
}

// Process form submission
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Initialize issue controller
    $controller = new IssueController();
    
    // Set reported_by to current user
    $_POST['reported_by'] = $_SESSION['user_id'];
    
    // Create issue
    $result = $controller->createIssue($_POST);
    
    if($result['success']) {
        set_message($result['message'], "success");
        redirect("index.php");
    } else {
        set_message($result['message'], "danger");
    }
}

// Include header
$page_title = "Report New Issue";
include $base_path . 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Report New Issue</h1>
    <a href="index.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back to Issues</a>
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
                                <option value="<?php echo $device_row['id']; ?>" <?php echo ($selected_device_id == $device_row['id']) ? 'selected' : ''; ?>>
                                    <?php echo $device_row['device_name']; ?> (<?php echo $device_row['serial_number']; ?>)
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="issue_title" class="form-label">Issue Title</label>
                        <input type="text" class="form-control" id="issue_title" name="issue_title" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="issue_description" class="form-label">Description</label>
                        <textarea class="form-control" id="issue_description" name="issue_description" rows="5" required></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="priority" class="form-label">Priority</label>
                        <select class="form-select" id="priority" name="priority" required>
                            <option value="low">Low</option>
                            <option value="medium" selected>Medium</option>
                            <option value="high">High</option>
                            <option value="critical">Critical</option>
                        </select>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Submit Issue</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include $base_path . 'includes/footer.php'; ?>