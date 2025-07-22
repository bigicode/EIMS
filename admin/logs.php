<?php
require_once '../includes/functions.php';
require_once '../includes/auth.php';
require_once '../config/database.php';
require_once '../models/Log.php';
require_once '../models/User.php';

require_login();
require_admin();

$database = new Database();
$db = $database->getConnection();
$log_model = new Log($db);

// Filters
$search = isset($_GET['search']) ? trim($_GET['search']) : null;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Export CSV
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    $stmt = $log_model->fetch(10000, 0, $search); // Export up to 10,000 logs
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="system_logs.csv"');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Date & Time', 'User', 'Action', 'Description', 'IP Address']);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $user = $row['full_name'] ? $row['full_name'] . ' (' . $row['username'] . ')' : $row['username'];
        fputcsv($output, [
            $row['timestamp'],
            $user,
            $row['action'],
            $row['description'],
            $row['ip_address']
        ]);
    }
    fclose($output);
    exit;
}

// Fetch logs for display
$stmt = $log_model->fetch($per_page, $offset, $search);
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Count total logs for pagination
$count_stmt = $db->prepare("SELECT COUNT(*) FROM system_logs");
$count_stmt->execute();
$total_records = $count_stmt->fetchColumn();
$total_pages = ceil($total_records / $per_page);

$page_title = 'System Logs';
include '../includes/header.php';
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3"><i class="bi bi-file-earmark-text me-2"></i>System Logs</h1>
        <a href="../profile.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Profile
        </a>
    </div>
    <div class="card shadow border-0 rounded-lg mb-4">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">System Activity Logs</h5>
            <form method="get" class="d-flex align-items-center mb-0">
                <a href="?export=csv" class="btn btn-sm btn-success">
                    <i class="bi bi-download"></i> Export CSV
                </a>
            </form>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Date & Time</th>
                            <th>User</th>
                            <th>Action</th>
                            <th>Description</th>
                            <th>IP Address</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($logs)): ?>
                            <tr>
                                <td colspan="5" class="text-center py-4">No logs found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($log['timestamp']); ?></td>
                                    <td><?php echo htmlspecialchars(($log['full_name'] ?? '') ? ($log['full_name'] . ' (' . ($log['username'] ?? '') . ')') : ($log['username'] ?? '')); ?></td>
                                    <td><span class="badge bg-secondary"><?php echo htmlspecialchars($log['action'] ?? ''); ?></span></td>
                                    <td><?php echo htmlspecialchars($log['description'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($log['ip_address'] ?? ''); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php if ($total_pages > 1): ?>
            <div class="card-footer">
                <nav aria-label="Log pagination">
                    <ul class="pagination justify-content-center mb-0">
                        <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $page - 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">
                                <i class="bi bi-chevron-left"></i>
                            </a>
                        </li>
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $page + 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">
                                <i class="bi bi-chevron-right"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php include '../includes/footer.php'; ?> 