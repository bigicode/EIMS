<?php require_once 'functions.php'; ?>
<?php
// Notification logic for logged-in users
if (is_logged_in()) {
    require_once __DIR__ . '/../config/database.php';
    require_once __DIR__ . '/../models/Message.php';
    require_once __DIR__ . '/../models/Issue.php';
    $database = new Database();
    $db = $database->getConnection();
    $message_model = new Message($db);
    $user_id = $_SESSION['user_id'];
    // Mark all as read if requested (user messages)
    if (isset($_GET['mark_all_read']) && $_GET['mark_all_read'] == '1') {
        $unread_messages = $message_model->get_unread($user_id);
        while($msg = $unread_messages->fetch(PDO::FETCH_ASSOC)) {
            $message_model->mark_as_read($msg['id']);
        }
        header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
        exit;
    }
    $unread_count = $message_model->count_unread($user_id);
    $unread_messages = $message_model->get_unread($user_id);

    // Admin: Issue notifications
    if (is_admin()) {
        $issue_model = new Issue($db);
        $open_issue_count = $issue_model->count_open();
        $latest_open_issues = $issue_model->get_latest_open(5);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Electronic Integration Management System (EMIS)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?php echo isset($base_path) ? $base_path : ''; ?>assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        .navbar .nav-link.text-white {
            color: #e0e0e0 !important;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #001a4d;">
        <div class="container">
            <a class="navbar-brand" href="/shop/dashboard.php">EIMS</a>
            <!-- Hamburger for Offcanvas Sidebar (Mobile Only) -->
            <button class="navbar-toggler d-block d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar" aria-controls="mobileSidebar" aria-label="Toggle navigation">
                <i class="bi bi-list" style="font-size: 2rem; color: #fff;"></i>
            </button>
            <!-- Default Collapse (Desktop Only) -->
            <div class="collapse navbar-collapse d-none d-lg-flex" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <?php if(is_logged_in()): ?>
                        <li class="nav-item">
                            <a class="nav-link text-white fw-bold" href="<?php echo isset($base_path) ? $base_path : ''; ?>dashboard.php">Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white fw-bold" href="<?php echo isset($base_path) ? $base_path : ''; ?>views/devices/index.php">Devices</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white fw-bold" href="<?php echo isset($base_path) ? $base_path : ''; ?>views/issues/index.php">Issues</a>
                        </li>
                        <?php if(is_admin()): ?>
                            <li class="nav-item">
                                <a class="nav-link text-white fw-bold" href="<?php echo isset($base_path) ? $base_path : ''; ?>views/maintenance/index.php">Maintenance</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-white fw-bold" href="<?php echo isset($base_path) ? $base_path : ''; ?>views/reports/index.php">Reports</a>
                            </li>
                        <?php endif; ?>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav">
                    <?php if(is_logged_in()): ?>
                        <!-- Admin Issue Notification Icon/Text -->
                        <?php if(is_admin()): ?>
                        <li class="nav-item dropdown me-2">
                            <a class="nav-link position-relative text-white" href="#" id="adminIssueDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <span class="d-none d-lg-inline"><i class="bi bi-exclamation-triangle" style="font-size: 1.5rem;"></i></span>
                                <span class="d-inline d-lg-none">Issues</span>
                                <?php if(isset($open_issue_count) && $open_issue_count > 0): ?>
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                        <?php echo $open_issue_count; ?>
                                        <span class="visually-hidden">open issues</span>
                                    </span>
                                <?php endif; ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="adminIssueDropdown" style="min-width: 350px;">
                                <li class="dropdown-header">New Issues Reported</li>
                                <?php if(isset($open_issue_count) && $open_issue_count > 0): ?>
                                    <?php $shown = 0; ?>
                                    <?php while($issue = $latest_open_issues->fetch(PDO::FETCH_ASSOC)): ?>
                                        <?php if($shown++ >= 5) break; ?>
                                        <li class="dropdown-item small">
                                            <b>Device:</b> <?php echo htmlspecialchars($issue['device_name']); ?><br>
                                            <b>Title:</b> <?php echo htmlspecialchars($issue['issue_title']); ?><br>
                                            <b>By:</b> <?php echo htmlspecialchars($issue['reporter_name']); ?><br>
                                            <b>At:</b> <?php echo htmlspecialchars($issue['reported_at']); ?>
                                        </li>
                                    <?php endwhile; ?>
                                    <li><hr class="dropdown-divider"></li>
                                    <li class="dropdown-item text-center"><a href="<?php echo isset($base_path) ? $base_path : ''; ?>views/issues/index.php">View all issues</a></li>
                                <?php else: ?>
                                    <li class="dropdown-item small text-muted">No new issues</li>
                                <?php endif; ?>
                            </ul>
                        </li>
                        <?php endif; ?>
                        <!-- Notification Icon/Text -->
                        <li class="nav-item dropdown me-2">
                            <a class="nav-link position-relative text-white" href="#" id="notifDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <span class="d-none d-lg-inline"><i class="bi bi-bell" style="font-size: 1.5rem;"></i></span>
                                <span class="d-inline d-lg-none">Message</span>
                                <?php if(isset($unread_count) && $unread_count > 0): ?>
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                        <?php echo $unread_count; ?>
                                        <span class="visually-hidden">unread notifications</span>
                                    </span>
                                <?php endif; ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notifDropdown" style="min-width: 300px;">
                                <li class="dropdown-header d-flex justify-content-between align-items-center">
                                    Notifications
                                    <?php if(isset($unread_count) && $unread_count > 0): ?>
                                        <a href="?mark_all_read=1" class="btn btn-link btn-sm">Mark all as read</a>
                                    <?php endif; ?>
                                </li>
                                <?php if(isset($unread_count) && $unread_count > 0): ?>
                                    <?php $shown = 0; ?>
                                    <?php while($msg = $unread_messages->fetch(PDO::FETCH_ASSOC)): ?>
                                        <?php if($shown++ >= 5) break; ?>
                                        <li class="dropdown-item small">
                                            <b>From:</b> <?php echo htmlspecialchars($msg['sender_name']); ?><br>
                                            <b>Message:</b> <?php echo htmlspecialchars($msg['message']); ?>
                                        </li>
                                    <?php endwhile; ?>
                                    <li><hr class="dropdown-divider"></li>
                                    <li class="dropdown-item text-center"><a href="<?php echo isset($base_path) ? $base_path : ''; ?>views/dashboard/user.php">View all</a></li>
                                <?php else: ?>
                                    <li class="dropdown-item small text-muted">No new notifications</li>
                                <?php endif; ?>
                            </ul>
                        </li>
                        <!-- End Notification Icon/Text -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle text-white fw-bold" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <?php echo $_SESSION['username']; ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                <li><a class="dropdown-item" href="<?php echo isset($base_path) ? $base_path : ''; ?>profile.php">Profile</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="<?php echo isset($base_path) ? $base_path : ''; ?>logout.php">Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link text-white fw-bold" href="views/auth/login.php">Login</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
            <!-- Offcanvas Sidebar for Mobile (Right Side) -->
            <div class="offcanvas offcanvas-end d-lg-none" tabindex="-1" id="mobileSidebar" aria-labelledby="mobileSidebarLabel" style="width: 250px; background-color: #001a4d;">
                <div class="offcanvas-header">
                    <h5 class="offcanvas-title fw-bold" id="mobileSidebarLabel" style="color: #e0e0e0;">Menu</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                </div>
                <div class="offcanvas-body">
                    <ul class="navbar-nav">
                        <?php if(is_logged_in()): ?>
                            <li class="nav-item">
                                <a class="nav-link text-white fw-bold" href="<?php echo isset($base_path) ? $base_path : ''; ?>dashboard.php">Dashboard</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-white fw-bold" href="<?php echo isset($base_path) ? $base_path : ''; ?>views/devices/index.php">Devices</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-white fw-bold" href="<?php echo isset($base_path) ? $base_path : ''; ?>views/issues/index.php">Issues</a>
                            </li>
                            <?php if(is_admin()): ?>
                                <li class="nav-item">
                                    <a class="nav-link text-white fw-bold" href="<?php echo isset($base_path) ? $base_path : ''; ?>views/maintenance/index.php">Maintenance</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link text-white fw-bold" href="<?php echo isset($base_path) ? $base_path : ''; ?>views/reports/index.php">Reports</a>
                                </li>
                            <?php endif; ?>
                            
                            <?php if(isset($unread_count) && $unread_count > 0): ?>
                            <li class="nav-item">
                                <a class="nav-link text-white fw-bold d-flex justify-content-between align-items-center" href="<?php echo isset($base_path) ? $base_path : ''; ?>views/dashboard/user.php">
                                    Messages
                                    <span class="badge bg-danger rounded-pill ms-2"><?php echo $unread_count; ?></span>
                                </a>
                            </li>
                            <?php else: ?>
                            <li class="nav-item">
                                <a class="nav-link text-white fw-bold" href="<?php echo isset($base_path) ? $base_path : ''; ?>views/dashboard/user.php">Messages</a>
                            </li>
                            <?php endif; ?>
                            
                            <?php if(is_admin() && isset($open_issue_count) && $open_issue_count > 0): ?>
                            <li class="nav-item">
                                <a class="nav-link text-white fw-bold d-flex justify-content-between align-items-center" href="<?php echo isset($base_path) ? $base_path : ''; ?>views/issues/index.php">
                                    Open Issues
                                    <span class="badge bg-danger rounded-pill ms-2"><?php echo $open_issue_count; ?></span>
                                </a>
                            </li>
                            <?php endif; ?>
                            
                            <li><hr class="dropdown-divider bg-light"></li>
                            <li class="nav-item">
                                <a class="nav-link text-white fw-bold" href="<?php echo isset($base_path) ? $base_path : ''; ?>profile.php">Profile</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-white fw-bold" href="<?php echo isset($base_path) ? $base_path : ''; ?>logout.php">Logout</a>
                            </li>
                        <?php else: ?>
                            <li class="nav-item">
                                <a class="nav-link text-white fw-bold" href="views/auth/login.php">Login</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </nav>
    
    <div class="container mt-4">
        <?php display_message(); ?>