<?php
require_once '../includes/Security.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
require_once '../config/database.php';

// Require login and admin role
require_login();
require_admin();

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Handle form submissions
$success_message = '';
$error_message = '';

// Handle backup creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_backup'])) {
    try {
        // Verify CSRF token
        Security::validateCSRFToken($_POST['csrf_token']);
        
        // Get database credentials from config
        require_once '../config/config.php';
        
        // Create backup directory if it doesn't exist
        $backup_dir = '../backups';
        if (!file_exists($backup_dir)) {
            mkdir($backup_dir, 0755, true);
        }
        
        // Create backup filename with timestamp
        $timestamp = date('Y-m-d_H-i-s');
        $backup_file = $backup_dir . '/backup_' . $timestamp . '.sql';
        
        // Build mysqldump command
        $command = sprintf(
            'mysqldump -h %s -u %s -p%s %s > %s',
            DB_HOST,
            DB_USER,
            DB_PASS,
            DB_NAME,
            $backup_file
        );
        
        // Execute command
        $output = [];
        $return_var = 0;
        exec($command, $output, $return_var);
        
        if ($return_var === 0) {
            $success_message = "Database backup created successfully: " . basename($backup_file);
        } else {
            $error_message = "Failed to create database backup. Error code: " . $return_var;
        }
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}

// Handle SQL execution
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['execute_sql'])) {
    try {
        // Verify CSRF token
        Security::validateCSRFToken($_POST['csrf_token']);
        
        // Get SQL query
        $sql = $_POST['sql_query'];
        
        // Execute query
        if (!empty($sql)) {
            // For safety, only allow SELECT queries
            $sql_upper = strtoupper(trim($sql));
            if (strpos($sql_upper, 'SELECT') === 0) {
                $stmt = $db->query($sql);
                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Store results in session for display
                $_SESSION['sql_results'] = $results;
                $_SESSION['sql_query'] = $sql;
                
                $success_message = "Query executed successfully. " . count($results) . " rows returned.";
            } else {
                $error_message = "For safety, only SELECT queries are allowed through this interface.";
            }
        } else {
            $error_message = "SQL query cannot be empty.";
        }
    } catch (PDOException $e) {
        $error_message = "SQL Error: " . $e->getMessage();
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}

// Handle SQL file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_sql'])) {
    try {
        // Verify CSRF token
        Security::validateCSRFToken($_POST['csrf_token']);
        
        // Check if file was uploaded
        if (isset($_FILES['sql_file']) && $_FILES['sql_file']['error'] === UPLOAD_ERR_OK) {
            $tmp_name = $_FILES['sql_file']['tmp_name'];
            $name = $_FILES['sql_file']['name'];
            
            // Check file extension
            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            if ($ext !== 'sql') {
                throw new Exception("Only SQL files are allowed.");
            }
            
            // Read file content
            $sql_content = file_get_contents($tmp_name);
            
            // Execute SQL
            $db->exec($sql_content);
            
            $success_message = "SQL file executed successfully.";
        } else {
            $error_message = "Error uploading file. Code: " . $_FILES['sql_file']['error'];
        }
    } catch (PDOException $e) {
        $error_message = "SQL Error: " . $e->getMessage();
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}

// Get database statistics
$db_stats = [];

// Get table list and sizes
try {
    $tables_query = $db->query("SHOW TABLES");
    $tables = $tables_query->fetchAll(PDO::FETCH_COLUMN);
    
    $db_stats['table_count'] = count($tables);
    
    // Get table sizes
    $size_query = $db->query("
        SELECT 
            table_name AS 'table',
            round(((data_length + index_length) / 1024 / 1024), 2) AS 'size_mb'
        FROM information_schema.TABLES
        WHERE table_schema = '" . DB_NAME . "'
        ORDER BY (data_length + index_length) DESC
    ");
    
    $table_sizes = $size_query->fetchAll(PDO::FETCH_ASSOC);
    $db_stats['tables'] = $table_sizes;
    
    // Calculate total size
    $total_size = 0;
    foreach ($table_sizes as $table) {
        $total_size += $table['size_mb'];
    }
    $db_stats['total_size_mb'] = round($total_size, 2);
    
    // Get row counts for important tables
    $important_tables = ['users', 'devices', 'maintenance', 'issues'];
    $row_counts = [];
    
    foreach ($important_tables as $table) {
        try {
            $count_query = $db->query("SELECT COUNT(*) FROM $table");
            $row_counts[$table] = $count_query->fetchColumn();
        } catch (PDOException $e) {
            $row_counts[$table] = 'N/A';
        }
    }
    
    $db_stats['row_counts'] = $row_counts;
    
} catch (PDOException $e) {
    $error_message = "Error getting database statistics: " . $e->getMessage();
}

// Get available backups
$backups = [];
$backup_dir = '../backups';
if (file_exists($backup_dir)) {
    $backup_files = glob($backup_dir . '/backup_*.sql');
    foreach ($backup_files as $file) {
        $backups[] = [
            'filename' => basename($file),
            'size' => round(filesize($file) / 1024 / 1024, 2), // Size in MB
            'date' => date('Y-m-d H:i:s', filemtime($file))
        ];
    }
    
    // Sort backups by date (newest first)
    usort($backups, function($a, $b) {
        return strtotime($b['date']) - strtotime($a['date']);
    });
}

$page_title = 'Database Tools';
include '../includes/header.php';

// Get CSRF token for forms
$csrf_token = Security::generateCSRFToken();
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3"><i class="fas fa-database me-2"></i>Database Tools</h1>
        <a href="../profile.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Profile
        </a>
    </div>
    
    <?php if ($success_message): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($success_message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <?php if ($error_message): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($error_message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <div class="row">
        <!-- Database Statistics -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow border-0 rounded-lg h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Database Statistics</h5>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <h6>Overview</h6>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <tbody>
                                    <tr>
                                        <th>Database Name:</th>
                                        <td><?php echo htmlspecialchars(DB_NAME); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Total Tables:</th>
                                        <td><?php echo isset($db_stats['table_count']) ? $db_stats['table_count'] : 'N/A'; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Total Size:</th>
                                        <td><?php echo isset($db_stats['total_size_mb']) ? $db_stats['total_size_mb'] . ' MB' : 'N/A'; ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <h6>Row Counts</h6>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <tbody>
                                    <?php if (isset($db_stats['row_counts'])): ?>
                                        <?php foreach ($db_stats['row_counts'] as $table => $count): ?>
                                            <tr>
                                                <th><?php echo ucfirst(htmlspecialchars($table)); ?>:</th>
                                                <td><?php echo htmlspecialchars($count); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <div>
                        <h6>Largest Tables</h6>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Table</th>
                                        <th>Size (MB)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (isset($db_stats['tables'])): ?>
                                        <?php $counter = 0; foreach ($db_stats['tables'] as $table): ?>
                                            <?php if ($counter < 5): // Show only top 5 largest tables ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($table['table']); ?></td>
                                                    <td><?php echo htmlspecialchars($table['size_mb']); ?></td>
                                                </tr>
                                            <?php endif; ?>
                                            <?php $counter++; ?>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Database Tools -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow border-0 rounded-lg">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-tools me-2"></i>Database Management</h5>
                </div>
                <div class="card-body">
                    <ul class="nav nav-tabs mb-4" id="databaseTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="backup-tab" data-bs-toggle="tab" data-bs-target="#backup" type="button" role="tab" aria-controls="backup" aria-selected="true">
                                <i class="fas fa-download me-1"></i> Backup
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="query-tab" data-bs-toggle="tab" data-bs-target="#query" type="button" role="tab" aria-controls="query" aria-selected="false">
                                <i class="fas fa-code me-1"></i> SQL Query
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="upload-tab" data-bs-toggle="tab" data-bs-target="#upload" type="button" role="tab" aria-controls="upload" aria-selected="false">
                                <i class="fas fa-upload me-1"></i> SQL Upload
                            </button>
                        </li>
                    </ul>
                    
                    <div class="tab-content" id="databaseTabsContent">
                        <!-- Backup Tab -->
                        <div class="tab-pane fade show active" id="backup" role="tabpanel" aria-labelledby="backup-tab">
                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <h5 class="card-title">Create Backup</h5>
                                            <p class="card-text">Create a backup of the entire database.</p>
                                            <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                                                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                                <button type="submit" name="create_backup" class="btn btn-primary">
                                                    <i class="fas fa-download me-1"></i> Create Backup
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6 mb-4">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <h5 class="card-title">Optimize Database</h5>
                                            <p class="card-text">Optimize tables to improve performance.</p>
                                            <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                                                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                                <button type="submit" name="optimize_db" class="btn btn-secondary">
                                                    <i class="fas fa-bolt me-1"></i> Optimize Tables
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <h5 class="mt-4">Available Backups</h5>
                            <div class="table-responsive">
                                <table class="table table-hover table-striped">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Filename</th>
                                            <th>Date</th>
                                            <th>Size</th>
                                            <th class="text-end">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($backups)): ?>
                                            <tr>
                                                <td colspan="4" class="text-center py-3">No backups available.</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($backups as $backup): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($backup['filename']); ?></td>
                                                    <td><?php echo htmlspecialchars($backup['date']); ?></td>
                                                    <td><?php echo htmlspecialchars($backup['size']); ?> MB</td>
                                                    <td class="text-end">
                                                        <a href="../backups/<?php echo htmlspecialchars($backup['filename']); ?>" class="btn btn-sm btn-outline-primary" download>
                                                            <i class="fas fa-download"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <!-- SQL Query Tab -->
                        <div class="tab-pane fade" id="query" role="tabpanel" aria-labelledby="query-tab">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Execute SQL Query</h5>
                                    <p class="card-text text-muted">For safety, only SELECT queries are allowed.</p>
                                    <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                        <div class="mb-3">
                                            <textarea class="form-control font-monospace" name="sql_query" rows="5" placeholder="Enter SQL query here..."><?php echo isset($_SESSION['sql_query']) ? htmlspecialchars($_SESSION['sql_query']) : ''; ?></textarea>
                                        </div>
                                        <div class="d-flex justify-content-end">
                                            <button type="submit" name="execute_sql" class="btn btn-primary">
                                                <i class="fas fa-play me-1"></i> Execute Query
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            
                            <?php if (isset($_SESSION['sql_results'])): ?>
                                <div class="mt-4">
                                    <h5>Query Results</h5>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-hover table-striped">
                                            <thead class="table-light">
                                                <?php if (!empty($_SESSION['sql_results'])): ?>
                                                    <tr>
                                                        <?php foreach ($_SESSION['sql_results'][0] as $column => $value): ?>
                                                            <th><?php echo htmlspecialchars($column); ?></th>
                                                        <?php endforeach; ?>
                                                    </tr>
                                                <?php endif; ?>
                                            </thead>
                                            <tbody>
                                                <?php if (empty($_SESSION['sql_results'])): ?>
                                                    <tr>
                                                        <td colspan="100%" class="text-center py-3">No results found.</td>
                                                    </tr>
                                                <?php else: ?>
                                                    <?php foreach ($_SESSION['sql_results'] as $row): ?>
                                                        <tr>
                                                            <?php foreach ($row as $value): ?>
                                                                <td><?php echo htmlspecialchars($value); ?></td>
                                                            <?php endforeach; ?>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- SQL Upload Tab -->
                        <div class="tab-pane fade" id="upload" role="tabpanel" aria-labelledby="upload-tab">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Upload SQL File</h5>
                                    <p class="card-text">Upload and execute an SQL file.</p>
                                    <div class="alert alert-warning">
                                        <i class="fas fa-exclamation-triangle me-2"></i> Warning: This will execute all SQL commands in the file. Make sure you trust the source of the file.
                                    </div>
                                    <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" enctype="multipart/form-data">
                                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                        <div class="mb-3">
                                            <label for="sql_file" class="form-label">SQL File</label>
                                            <input type="file" class="form-control" id="sql_file" name="sql_file" accept=".sql" required>
                                        </div>
                                        <div class="d-flex justify-content-end">
                                            <button type="submit" name="upload_sql" class="btn btn-primary">
                                                <i class="fas fa-upload me-1"></i> Upload & Execute
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?> 