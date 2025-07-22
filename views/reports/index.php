<?php
// Set relative path for includes
$base_path = '../../';
require_once $base_path . 'config/database.php';
require_once $base_path . 'controllers/ReportController.php';
require_once $base_path . 'includes/functions.php';
require_once $base_path . 'includes/auth.php';

// Only admin can access this page
require_admin();

// Initialize the report controller
$database = new Database();
$db = $database->getConnection();
$reportController = new ReportController();

// Set page title and include header
$page_title = "Reports";
include $base_path . 'includes/header.php';

// Process form submission if a report is requested
$report_data = null;
$report_type = null;
$filters = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['report_type'])) {
    $report_type = $_POST['report_type'];
    
    // Set filters based on form inputs
    if (isset($_POST['start_date']) && !empty($_POST['start_date'])) {
        $filters['start_date'] = $_POST['start_date'];
    } else {
        $filters['start_date'] = date('Y-m-d', strtotime('-30 days'));
    }
    
    if (isset($_POST['end_date']) && !empty($_POST['end_date'])) {
        $filters['end_date'] = $_POST['end_date'];
    } else {
        $filters['end_date'] = date('Y-m-d');
    }
    
    if (isset($_POST['device_id']) && !empty($_POST['device_id'])) {
        $filters['device_id'] = $_POST['device_id'];
    }
    
    if (isset($_POST['warranty_days']) && !empty($_POST['warranty_days'])) {
        $filters['warranty_days'] = intval($_POST['warranty_days']);
    }
    
    // Generate the requested report
    switch ($report_type) {
        case 'inventory':
            $report_data = $reportController->generateInventoryReport($filters);
            break;
        case 'status':
            $report_data = $reportController->generateStatusReport();
            break;
        case 'maintenance':
            $report_data = $reportController->generateMaintenanceReport($filters);
            break;
        case 'issues':
            $report_data = $reportController->generateIssueReport($filters);
            break;
        case 'warranty':
            $days = isset($filters['warranty_days']) ? $filters['warranty_days'] : 90;
            $report_data = $reportController->generateWarrantyReport($days);
            break;
        case 'device_history':
            if (isset($filters['device_id'])) {
                $report_data = $reportController->generateDeviceHistoryReport($filters['device_id']);
            }
            break;
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Reports</h1>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Generate Report</h5>
            </div>
            <div class="card-body">
                <form method="post" action="">
                    <div class="mb-3">
                        <label for="report_type" class="form-label">Report Type</label>
                        <select class="form-select" id="report_type" name="report_type" required>
                            <option value="" selected disabled>Select a report type</option>
                            <option value="inventory">Device Inventory</option>
                            <option value="status">Device Status</option>
                            <option value="maintenance">Maintenance History</option>
                            <option value="issues">Issue Analysis</option>
                            <option value="warranty">Warranty Expiry</option>
                            <option value="device_history">Device History</option>
                        </select>
                    </div>
                    
                    <!-- Date range filters (for applicable reports) -->
                    <div class="mb-3 date-filter">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo date('Y-m-d', strtotime('-30 days')); ?>">
                    </div>
                    
                    <div class="mb-3 date-filter">
                        <label for="end_date" class="form-label">End Date</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    
                    <!-- Device ID filter (for device history report) -->
                    <div class="mb-3 device-filter" style="display: none;">
                        <label for="device_id" class="form-label">Device ID</label>
                        <input type="number" class="form-control" id="device_id" name="device_id">
                    </div>
                    
                    <!-- Warranty days filter -->
                    <div class="mb-3 warranty-filter" style="display: none;">
                        <label for="warranty_days" class="form-label">Days Until Expiry</label>
                        <input type="number" class="form-control" id="warranty_days" name="warranty_days" value="90">
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Generate Report</button>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">Report Options</h5>
            </div>
            <div class="card-body">
                <p><i class="bi bi-info-circle"></i> Select a report type and configure any filters before generating.</p>
                <p>Available reports:</p>
                <ul>
                    <li><strong>Device Inventory</strong> - Overview of all devices by type and status</li>
                    <li><strong>Device Status</strong> - Current status distribution of all devices</li>
                    <li><strong>Maintenance History</strong> - Record of maintenance activities</li>
                    <li><strong>Issue Analysis</strong> - Breakdown of issues by priority and status</li>
                    <li><strong>Warranty Expiry</strong> - Devices with warranties expiring soon</li>
                    <li><strong>Device History</strong> - Complete history for a specific device</li>
                </ul>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <?php if ($report_data !== null): ?>
            <div class="card">
                <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <?php 
                        switch ($report_type) {
                            case 'inventory':
                                echo 'Device Inventory Report';
                                break;
                            case 'status':
                                echo 'Device Status Report';
                                break;
                            case 'maintenance':
                                echo 'Maintenance History Report';
                                break;
                            case 'issues':
                                echo 'Issue Analysis Report';
                                break;
                            case 'warranty':
                                echo 'Warranty Expiry Report';
                                break;
                            case 'device_history':
                                echo 'Device History Report';
                                break;
                        }
                        ?>
                    </h5>
                    <button class="btn btn-sm btn-light" onclick="printReportSection()">
                        <i class="bi bi-printer"></i> Print
                    </button>
                </div>
                <div class="card-body" id="reportSection">
                    <?php if ($report_type === 'inventory'): ?>
                        <!-- Inventory Report -->
                        <h4>Device Inventory by Type</h4>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Device Type</th>
                                        <th>Total Count</th>
                                        <th>Active</th>
                                        <th>Maintenance</th>
                                        <th>Repair</th>
                                        <th>Disposed</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($report_data as $row): ?>
                                        <tr>
                                            <td><?php echo $row['device_type']; ?></td>
                                            <td><?php echo $row['count']; ?></td>
                                            <td><?php echo $row['active']; ?></td>
                                            <td><?php echo $row['maintenance']; ?></td>
                                            <td><?php echo $row['repair']; ?></td>
                                            <td><?php echo $row['disposed']; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                    <?php elseif ($report_type === 'status'): ?>
                        <!-- Status Report -->
                        <div class="row">
                            <div class="col-md-6">
                                <h4>Devices by Status</h4>
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Status</th>
                                                <th>Count</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($report_data['status_counts'] as $row): ?>
                                                <tr>
                                                    <td><?php echo device_status_badge($row['status']); ?></td>
                                                    <td><?php echo $row['count']; ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <h4>Devices by Type</h4>
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Device Type</th>
                                                <th>Count</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($report_data['type_counts'] as $row): ?>
                                                <tr>
                                                    <td><?php echo $row['device_type']; ?></td>
                                                    <td><?php echo $row['count']; ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <h4>Devices by Department</h4>
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Department</th>
                                                <th>Count</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($report_data['department_counts'] as $row): ?>
                                                <tr>
                                                    <td><?php echo $row['department']; ?></td>
                                                    <td><?php echo $row['count']; ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                    <?php elseif ($report_type === 'maintenance'): ?>
                        <!-- Maintenance Report -->
                        <h4>Maintenance History</h4>
                        <p>Period: <?php echo format_date($filters['start_date']); ?> to <?php echo format_date($filters['end_date']); ?></p>
                        
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Device</th>
                                        <th>Type</th>
                                        <th>Scheduled Date</th>
                                        <th>Completion Date</th>
                                        <th>Status</th>
                                        <th>Performed By</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($report_data as $row): ?>
                                        <tr>
                                            <td><?php echo $row['device_name']; ?> (<?php echo $row['serial_number']; ?>)</td>
                                            <td><?php echo $row['maintenance_type']; ?></td>
                                            <td><?php echo format_date($row['scheduled_date']); ?></td>
                                            <td><?php echo $row['completion_date'] ? format_date($row['completion_date']) : 'Not completed'; ?></td>
                                            <td><?php echo $row['status']; ?></td>
                                            <td><?php echo $row['performed_by_name'] ?: 'Not assigned'; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                    <?php elseif ($report_type === 'issues'): ?>
                        <!-- Issues Report -->
                        <div class="row">
                            <div class="col-md-4">
                                <h4>Issues by Status</h4>
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Status</th>
                                                <th>Count</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($report_data['status_counts'] as $row): ?>
                                                <tr>
                                                    <td><?php echo issue_status_badge($row['status']); ?></td>
                                                    <td><?php echo $row['count']; ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <h4>Issues by Priority</h4>
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Priority</th>
                                                <th>Count</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($report_data['priority_counts'] as $row): ?>
                                                <tr>
                                                    <td><?php echo issue_priority_badge($row['priority']); ?></td>
                                                    <td><?php echo $row['count']; ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <h4>Issues by Device Type</h4>
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Device Type</th>
                                                <th>Count</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($report_data['device_type_counts'] as $row): ?>
                                                <tr>
                                                    <td><?php echo $row['device_type']; ?></td>
                                                    <td><?php echo $row['count']; ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <h4>Detailed Issue List</h4>
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Device</th>
                                                <th>Issue Title</th>
                                                <th>Reported By</th>
                                                <th>Reported Date</th>
                                                <th>Status</th>
                                                <th>Priority</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($report_data['issues'] as $row): ?>
                                                <tr>
                                                    <td><?php echo $row['device_name']; ?></td>
                                                    <td><?php echo $row['issue_title']; ?></td>
                                                    <td><?php echo $row['reported_by_name']; ?></td>
                                                    <td><?php echo format_date($row['reported_at']); ?></td>
                                                    <td><?php echo issue_status_badge($row['status']); ?></td>
                                                    <td><?php echo issue_priority_badge($row['priority']); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                    <?php elseif ($report_type === 'warranty'): ?>
                        <!-- Warranty Report -->
                        <h4>Devices with Expiring Warranty</h4>
                        <p>Expiring within the next <?php echo isset($filters['warranty_days']) ? $filters['warranty_days'] : 90; ?> days</p>
                        
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Device Name</th>
                                        <th>Serial Number</th>
                                        <th>Type</th>
                                        <th>Purchase Date</th>
                                        <th>Warranty End</th>
                                        <th>Days Left</th>
                                        <th>Assigned To</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($report_data as $row): ?>
                                        <tr>
                                            <td><?php echo $row['device_name']; ?></td>
                                            <td><?php echo $row['serial_number']; ?></td>
                                            <td><?php echo $row['device_type']; ?></td>
                                            <td><?php echo format_date($row['purchase_date']); ?></td>
                                            <td><?php echo format_date($row['warranty_end']); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo ($row['days_until_expiry'] < 30) ? 'danger' : 'warning'; ?>">
                                                    <?php echo $row['days_until_expiry']; ?> days
                                                </span>
                                            </td>
                                            <td><?php echo $row['assigned_to_name'] ?: 'Unassigned'; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                    <?php elseif ($report_type === 'device_history'): ?>
                        <!-- Device History Report -->
                        <?php if (!empty($report_data)): ?>
                            <h4>History for Device: <?php echo $report_data[0]['device_name']; ?> (<?php echo $report_data[0]['serial_number']; ?>)</h4>
                            
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Action</th>
                                            <th>Description</th>
                                            <th>Performed By</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($report_data as $row): ?>
                                            <tr>
                                                <td><?php echo format_date($row['action_date']); ?></td>
                                                <td><?php echo $row['action']; ?></td>
                                                <td><?php echo $row['description']; ?></td>
                                                <td><?php echo $row['performed_by_name']; ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i> No history found for the specified device.
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="card">
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> Select a report type and click "Generate Report" to view data.
                    </div>
                    <div class="text-center py-5">
                        <i class="bi bi-file-earmark-bar-graph" style="font-size: 5rem; color: #ccc;"></i>
                        <h4 class="mt-3">Report Dashboard</h4>
                        <p class="text-muted">Generate various reports to analyze your device inventory, maintenance history, and issues.</p>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Show/hide filters based on report type
document.getElementById('report_type').addEventListener('change', function() {
    const reportType = this.value;
    const dateFilters = document.querySelectorAll('.date-filter');
    const deviceFilter = document.querySelector('.device-filter');
    const warrantyFilter = document.querySelector('.warranty-filter');
    
    // Reset display
    dateFilters.forEach(filter => filter.style.display = 'none');
    deviceFilter.style.display = 'none';
    warrantyFilter.style.display = 'none';
    
    // Show relevant filters based on report type
    switch (reportType) {
        case 'maintenance':
        case 'issues':
            dateFilters.forEach(filter => filter.style.display = 'block');
            break;
        case 'device_history':
            deviceFilter.style.display = 'block';
            break;
        case 'warranty':
            warrantyFilter.style.display = 'block';
            break;
    }
});

function printReportSection() {
    var printContents = document.getElementById('reportSection').innerHTML;
    var originalContents = document.body.innerHTML;
    document.body.innerHTML = printContents;
    window.print();
    document.body.innerHTML = originalContents;
    location.reload(); // reload to restore event handlers and scripts
}
</script>

<?php include $base_path . 'includes/footer.php'; ?>