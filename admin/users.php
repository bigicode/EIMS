<?php
require_once '../includes/functions.php';
require_once '../includes/auth.php';
require_once '../config/database.php';
require_once '../models/User.php';

// Require login and admin role
require_login();
require_admin();

// Initialize database connection
$database = new Database();
$db = $database->getConnection();
$user = new User($db);

// Handle user actions
$action_message = '';
$action_status = '';

// Handle user deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $user->id = $_GET['delete'];
    if ($user->delete()) {
        $action_message = "User has been soft-deleted successfully.";
        $action_status = "success";
    } else {
        $action_message = "Failed to delete user.";
        $action_status = "danger";
    }
}

// Handle user restoration
if (isset($_GET['restore']) && is_numeric($_GET['restore'])) {
    $user->id = $_GET['restore'];
    if ($user->restore()) {
        $action_message = "User has been restored successfully.";
        $action_status = "success";
    } else {
        $action_message = "Failed to restore user.";
        $action_status = "danger";
    }
}

// Handle role change
if (isset($_POST['change_role']) && isset($_POST['user_id']) && isset($_POST['new_role'])) {
    try {
        $stmt = $db->prepare("UPDATE users SET role = ? WHERE id = ?");
        $stmt->execute([$_POST['new_role'], $_POST['user_id']]);
        $action_message = "User role has been updated successfully.";
        $action_status = "success";
    } catch (Exception $e) {
        $action_message = "Failed to update user role: " . $e->getMessage();
        $action_status = "danger";
    }
}

// Get all users including deleted ones
$stmt = $db->prepare("SELECT id, username, email, full_name, department, role, created_at, deleted_at FROM users ORDER BY deleted_at ASC, created_at DESC");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

$base_path = '../';
$page_title = 'User Management';
include '../includes/header.php';

// Get CSRF token for forms
$csrf_token = Security::generateCSRFToken();
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3"><i class="fas fa-users me-2"></i>User Management</h1>
        <a href="../profile.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Profile
        </a>
    </div>

    <?php if ($action_message): ?>
        <div class="alert alert-<?php echo $action_status; ?> alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($action_message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow border-0 rounded-lg">
        <div class="card-header bg-primary text-white">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">All Users</h5>
                <a href="user_create.php" class="btn btn-light btn-sm">
                    <i class="fas fa-plus me-1"></i> Add New User
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Username</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Department</th>
                            <th>Role</th>
                            <th>Created</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user_item): ?>
                            <tr class="<?php echo $user_item['deleted_at'] ? 'table-danger' : ''; ?>">
                                <td><?php echo htmlspecialchars($user_item['username']); ?></td>
                                <td><?php echo htmlspecialchars($user_item['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($user_item['email']); ?></td>
                                <td><?php echo htmlspecialchars($user_item['department']); ?></td>
                                <td>
                                    <?php if (!$user_item['deleted_at']): ?>
                                        <?php if ($user_item['role'] === 'admin'): ?>
                                            <span class="badge bg-primary">Admin</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">User</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-muted">N/A</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('Y-m-d', strtotime($user_item['created_at'])); ?></td>
                                <td>
                                    <?php if ($user_item['deleted_at']): ?>
                                        <span class="badge bg-danger">Deleted</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="user_edit.php?id=<?php echo $user_item['id']; ?>" class="btn btn-sm btn-warning" <?php echo $user_item['deleted_at'] ? 'disabled' : ''; ?> title="Edit User">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <?php if (!$user_item['deleted_at']): ?>
                                            <a href="?delete=<?php echo $user_item['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this user?');" title="Delete User">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        <?php else: ?>
                                            <a href="?restore=<?php echo $user_item['id']; ?>" class="btn btn-sm btn-success" onclick="return confirm('Are you sure you want to restore this user?');" title="Restore User">
                                                <i class="bi bi-arrow-counterclockwise"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($users)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-4">No users found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?> 