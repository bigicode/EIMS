<?php
$base_path = '../../';
require_once $base_path . 'config/database.php';
require_once $base_path . 'models/Maintenance.php';
require_once $base_path . 'includes/functions.php';
require_once $base_path . 'includes/auth.php';

require_admin();

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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $maintenance->maintenance_type = clean_input($_POST['maintenance_type']);
    $maintenance->description = clean_input($_POST['description']);
    $maintenance->scheduled_date = clean_input($_POST['scheduled_date']);
    $maintenance->status = clean_input($_POST['status']);
    $maintenance->performed_by = clean_input($_POST['performed_by']);
    $maintenance->completion_date = clean_input($_POST['completion_date']);
    $maintenance->notes = clean_input($_POST['notes']);
    if ($maintenance->update()) {
        set_message('Maintenance record updated successfully.', 'success');
        header('Location: view.php?id=' . $maintenance->id);
        exit;
    } else {
        set_message('Failed to update maintenance record.', 'danger');
    }
}
$page_title = 'Update Maintenance';
include $base_path . 'includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Update Maintenance</h1>
    <a href="index.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back</a>
</div>
<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card shadow">
            <div class="card-body">
                <form action="" method="post">
                    <div class="mb-3">
                        <label for="maintenance_type" class="form-label">Maintenance Type</label>
                        <input type="text" class="form-control" id="maintenance_type" name="maintenance_type" value="<?php echo htmlspecialchars($maintenance->maintenance_type ?? ''); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3" required><?php echo htmlspecialchars($maintenance->description ?? ''); ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="scheduled_date" class="form-label">Scheduled Date</label>
                        <input type="date" class="form-control" id="scheduled_date" name="scheduled_date" value="<?php echo htmlspecialchars($maintenance->scheduled_date ?? ''); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="scheduled" <?php if($maintenance->status=='scheduled') echo 'selected'; ?>>Scheduled</option>
                            <option value="in_progress" <?php if($maintenance->status=='in_progress') echo 'selected'; ?>>In Progress</option>
                            <option value="completed" <?php if($maintenance->status=='completed') echo 'selected'; ?>>Completed</option>
                            <option value="cancelled" <?php if($maintenance->status=='cancelled') echo 'selected'; ?>>Cancelled</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="performed_by" class="form-label">Performed By</label>
                        <input type="text" class="form-control" id="performed_by" name="performed_by" value="<?php echo htmlspecialchars($maintenance->performed_by ?? ''); ?>">
                    </div>
                    <div class="mb-3">
                        <label for="completion_date" class="form-label">Completion Date</label>
                        <input type="date" class="form-control" id="completion_date" name="completion_date" value="<?php echo htmlspecialchars($maintenance->completion_date ?? ''); ?>">
                    </div>
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="2"><?php echo htmlspecialchars($maintenance->notes ?? ''); ?></textarea>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Update Maintenance</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php include $base_path . 'includes/footer.php'; ?> 