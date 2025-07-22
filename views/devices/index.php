<?php
// Set relative path for includes
$base_path = '../../';
require_once $base_path . 'config/database.php';
require_once $base_path . 'controllers/DeviceController.php';
require_once $base_path . 'includes/functions.php';
require_once $base_path . 'includes/auth.php';

// Require login for this page
require_login();

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Initialize device controller
$controller = new DeviceController();

// Get all devices
$result = $controller->getDevices();

// Include header
$page_title = "Device Management";
include $base_path . 'includes/header.php';
?>
<style>
@media print {
    #deviceListSection th.actions-col,
    #deviceListSection td.actions-col,
    #deviceListSection th:last-child,
    #deviceListSection td:last-child {
        display: none !important;
    }
}
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Device Management</h1>
    <div>
        <a href="create.php" class="btn" style="background: #4169e1; color: #fff; border: none; font-weight: 500; padding: 0.75rem 1.25rem; border-radius: 0.5rem; box-shadow: 0 2px 8px rgba(65,105,225,0.15);"><i class="bi bi-plus-circle"></i> Add New Device</a>
        <button class="btn btn-secondary ms-2" onclick="printDeviceListSection()"><i class="bi bi-printer"></i> Print Devices</button>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive" id="deviceListSection">
            <table class="table table-striped table-hover" id="devicesTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Device Name</th>
                        <th>Type</th>
                        <th>Serial Number</th>
                        <th>Department</th>
                        <th>Status</th>
                        <th>Assigned To</th>
                        <th>Recommendation</th>
                        <th>Reliability Score</th>
                        <th>Cost-Benefit Notes</th>
                        <th class="actions-col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($result->rowCount() > 0): ?>
                        <?php while($row = $result->fetch(PDO::FETCH_ASSOC)): ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo $row['device_name']; ?></td>
                                <td><?php echo $row['device_type']; ?></td>
                                <td><?php echo $row['serial_number']; ?></td>
                                <td><?php echo $row['department']; ?></td>
                                <td><?php echo device_status_badge($row['status']); ?></td>
                                <td><?php echo $row['assigned_to_name'] ?? 'Not Assigned'; ?></td>
                                <td><?php echo $row['recommendation'] ?? '-'; ?></td>
                                <td><?php echo $row['reliability_score'] ?? '-'; ?></td>
                                <td><?php echo $row['cost_benefit'] ?? '-'; ?></td>
                                <td class="actions-col">
                                    <a href="view.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-info"><i class="bi bi-eye"></i></a>
                                    <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></a>
                                    <?php if(is_admin()): ?>
                                        <a href="#" class="btn btn-sm btn-secondary" data-bs-toggle="modal" data-bs-target="#historyModal<?php echo $row['id']; ?>" title="Device History"><i class="bi bi-clock-history"></i></a>
                                        <a href="#" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?php echo $row['id']; ?>"><i class="bi bi-trash"></i></a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center">No devices found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
// Render all modals after the table for best compatibility
if($result->rowCount() > 0):
    $result->execute(); // Reset pointer
    while($row = $result->fetch(PDO::FETCH_ASSOC)):
        if(is_admin()): ?>
        <!-- History Modal -->
        <div class="modal fade" id="historyModal<?php echo $row['id']; ?>" tabindex="-1" aria-labelledby="historyModalLabel<?php echo $row['id']; ?>" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <form method="post" action="history.php?id=<?php echo $row['id']; ?>">
                        <input type="hidden" name="device_name" value="<?php echo htmlspecialchars($row['device_name']); ?>">
                        <input type="hidden" name="device_type" value="<?php echo htmlspecialchars($row['device_type']); ?>">
                        <input type="hidden" name="serial_number" value="<?php echo htmlspecialchars($row['serial_number']); ?>">
                        <input type="hidden" name="model" value="<?php echo htmlspecialchars($row['model']); ?>">
                        <input type="hidden" name="manufacturer" value="<?php echo htmlspecialchars($row['manufacturer']); ?>">
                        <input type="hidden" name="location" value="<?php echo htmlspecialchars($row['location']); ?>">
                        <input type="hidden" name="department" value="<?php echo htmlspecialchars($row['department']); ?>">
                        <div class="modal-header">
                            <h5 class="modal-title" id="historyModalLabel<?php echo $row['id']; ?>">Device Lifecycle Details: <?php echo htmlspecialchars($row['device_name']); ?></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label class="form-label">Purchase Date</label>
                                    <input type="date" name="purchase_date" class="form-control" value="<?php echo $row['purchase_date'] ?? ''; ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Warranty End</label>
                                    <input type="date" name="warranty_end" class="form-control" value="<?php echo $row['warranty_end'] ?? ''; ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Status</label>
                                    <select name="status" class="form-select">
                                        <option value="active" <?php if($row['status']==='active') echo 'selected'; ?>>Active</option>
                                        <option value="maintenance" <?php if($row['status']==='maintenance') echo 'selected'; ?>>Maintenance</option>
                                        <option value="repair" <?php if($row['status']==='repair') echo 'selected'; ?>>Repair</option>
                                        <option value="disposed" <?php if($row['status']==='disposed') echo 'selected'; ?>>Disposed</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Last Maintenance</label>
                                    <input type="date" name="last_maintenance" class="form-control" value="<?php echo $row['last_maintenance'] ?? ''; ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Next Maintenance</label>
                                    <input type="date" name="next_maintenance" class="form-control" value="<?php echo $row['next_maintenance'] ?? ''; ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Recommendation</label>
                                    <input type="text" name="recommendation" class="form-control" value="<?php echo $row['recommendation'] ?? ''; ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Reliability Score</label>
                                    <input type="number" name="reliability_score" class="form-control" value="<?php echo $row['reliability_score'] ?? ''; ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Cost-Benefit Notes</label>
                                    <input type="text" name="cost_benefit" class="form-control" value="<?php echo $row['cost_benefit'] ?? ''; ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Health Score</label>
                                    <input type="number" name="health" class="form-control" value="<?php echo $row['health'] ?? ''; ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Alert</label>
                                    <input type="text" name="alert" class="form-control" value="<?php echo $row['alert'] ?? ''; ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Disposal Reason</label>
                                    <input type="text" name="disposal_reason" class="form-control" value="<?php echo $row['disposal_reason'] ?? ''; ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Disposal Date</label>
                                    <input type="date" name="disposal_date" class="form-control" value="<?php echo $row['disposal_date'] ?? ''; ?>">
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">Issues / Recurring Issues</label>
                                    <a href="../issues/index.php?device_id=<?php echo $row['id']; ?>" class="btn btn-outline-primary btn-sm ms-2">View Issues</a>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-success">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!-- Delete Modal -->
        <div class="modal fade" id="deleteModal<?php echo $row['id']; ?>" tabindex="-1" aria-labelledby="deleteModalLabel<?php echo $row['id']; ?>" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteModalLabel<?php echo $row['id']; ?>">Confirm Delete</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        Are you sure you want to delete the device &quot;<?php echo $row['device_name']; ?>&quot;? This action cannot be undone.
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <a href="delete.php?id=<?php echo $row['id']; ?>" class="btn btn-danger">Delete</a>
                    </div>
                </div>
            </div>
        </div>
<?php endif; endwhile; endif; ?>

<script>
function printDeviceListSection() {
    var printContents = document.getElementById('deviceListSection').innerHTML;
    var originalContents = document.body.innerHTML;
    document.body.innerHTML = printContents;
    window.print();
    document.body.innerHTML = originalContents;
    location.reload();
}
</script>

<!-- Ensure Bootstrap JS is included for modals -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<?php include $base_path . 'includes/footer.php'; ?>