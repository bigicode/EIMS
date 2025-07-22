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

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Initialize maintenance object
$maintenance = new Maintenance($db);

// Get all maintenance records
$result = $maintenance->read();

// Get upcoming maintenance
$upcoming = $maintenance->get_upcoming_maintenance();

// Get overdue maintenance
$overdue = $maintenance->get_overdue_maintenance();

// Handle delete action
if (isset($_GET['delete_id']) && is_numeric($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $maintenance_to_delete = new Maintenance($db);
    $maintenance_to_delete->id = $delete_id;
    if ($maintenance_to_delete->delete()) {
        set_message('Maintenance record deleted successfully.', 'success');
    } else {
        set_message('Failed to delete maintenance record.', 'danger');
    }
    header('Location: index.php');
    exit;
}

// Include header
$page_title = "Maintenance Management";
include $base_path . 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Maintenance Management</h1>
    <a href="schedule.php" class="btn mb-3" style="background-color: #28a745; color: #fff; border: none; font-weight: 500; padding: 0.5rem 1rem; border-radius: 0.25rem; box-shadow: 0 2px 8px rgba(40,167,69,0.15);"><i class="bi bi-plus-circle"></i> Schedule Maintenance</a>
</div>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h5 class="card-title">Total Scheduled</h5>
                <h2 class="display-4"><?php echo $maintenance->count_by_status('scheduled'); ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-warning text-dark">
            <div class="card-body">
                <h5 class="card-title">Upcoming (Next 7 Days)</h5>
                <h2 class="display-4"><?php echo $upcoming->rowCount(); ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-danger text-white">
            <div class="card-body">
                <h5 class="card-title">Overdue</h5>
                <h2 class="display-4"><?php echo $overdue->rowCount(); ?></h2>
            </div>
        </div>
    </div>
</div>

<ul class="nav nav-tabs mb-4" id="maintenanceTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link btn btn-outline-blue active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all" type="button" role="tab" aria-controls="all" aria-selected="true">All Maintenance</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link btn btn-outline-blue" id="upcoming-tab" data-bs-toggle="tab" data-bs-target="#upcoming" type="button" role="tab" aria-controls="upcoming" aria-selected="false">Upcoming</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link btn btn-outline-blue" id="overdue-tab" data-bs-toggle="tab" data-bs-target="#overdue" type="button" role="tab" aria-controls="overdue" aria-selected="false">Overdue</button>
    </li>
</ul>

<div class="tab-content" id="maintenanceTabsContent">
    <div class="tab-pane fade show active" id="all" role="tabpanel" aria-labelledby="all-tab">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Device</th>
                        <th>Type</th>
                        <th>Scheduled Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($result->rowCount() > 0): ?>
                        <?php while($row = $result->fetch(PDO::FETCH_ASSOC)): ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo $row['device_name']; ?></td>
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
                                <td>
                                    <a href="view.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-info"><i class="bi bi-eye"></i></a>
                                    <a href="update.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></a>
                                    <?php if ($row['status'] != 'completed' && $row['status'] != 'cancelled'): ?>
                                        <a href="complete.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-success"><i class="bi bi-check-circle"></i> Complete</a>
                                    <?php endif; ?>
                                    <a href="#" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?php echo $row['id']; ?>"><i class="bi bi-trash"></i></a>
                                    <!-- Delete Modal -->
                                    <div class="modal fade" id="deleteModal<?php echo $row['id']; ?>" tabindex="-1" aria-labelledby="deleteModalLabel<?php echo $row['id']; ?>" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="deleteModalLabel<?php echo $row['id']; ?>">Confirm Delete</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    Are you sure you want to delete this maintenance record? This action cannot be undone.
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <a href="index.php?delete_id=<?php echo $row['id']; ?>" class="btn btn-danger">Delete</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">No maintenance records found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <div class="tab-pane fade" id="upcoming" role="tabpanel" aria-labelledby="upcoming-tab">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Device</th>
                        <th>Type</th>
                        <th>Scheduled Date</th>
                        <th>Days Left</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($upcoming->rowCount() > 0): ?>
                        <?php while($row = $upcoming->fetch(PDO::FETCH_ASSOC)): ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo $row['device_name']; ?></td>
                                <td><?php echo $row['maintenance_type']; ?></td>
                                <td><?php echo format_date($row['scheduled_date']); ?></td>
                                <td>
                                    <?php 
                                    $days_left = days_between(date('Y-m-d'), $row['scheduled_date']);
                                    echo "<span class='badge bg-warning'>{$days_left} days</span>";
                                    ?>
                                </td>
                                <td>
                                    <a href="view.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-info"><i class="bi bi-eye"></i></a>
                                    <a href="update.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">No upcoming maintenance records</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <div class="tab-pane fade" id="overdue" role="tabpanel" aria-labelledby="overdue-tab">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Device</th>
                        <th>Type</th>
                        <th>Scheduled Date</th>
                        <th>Days Overdue</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($overdue->rowCount() > 0): ?>
                        <?php while($row = $overdue->fetch(PDO::FETCH_ASSOC)): ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo $row['device_name']; ?></td>
                                <td><?php echo $row['maintenance_type']; ?></td>
                                <td><?php echo format_date($row['scheduled_date']); ?></td>
                                <td>
                                    <?php 
                                    $days_overdue = days_between(date('Y-m-d'), $row['scheduled_date']);
                                    echo "<span class='badge bg-danger'>{$days_overdue} days</span>";
                                    ?>
                                </td>
                                <td>
                                    <a href="view.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-info"><i class="bi bi-eye"></i></a>
                                    <a href="update.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">No overdue maintenance records</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include $base_path . 'includes/footer.php'; ?>