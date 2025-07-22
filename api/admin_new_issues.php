<?php
require_once '../config/database.php';
require_once '../models/Issue.php';
header('Content-Type: application/json');

$database = new Database();
$db = $database->getConnection();
$issue_model = new Issue($db);

$stmt = $issue_model->get_latest_open(1);
$latest = $stmt->fetch(PDO::FETCH_ASSOC);

if ($latest) {
    echo json_encode([
        'id' => $latest['id'],
        'device_name' => $latest['device_name'],
        'issue_title' => $latest['issue_title'],
        'reporter_name' => $latest['reporter_name'],
        'reported_at' => $latest['reported_at'],
        'status' => $latest['status']
    ]);
} else {
    echo json_encode(null);
} 