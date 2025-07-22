<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Device.php';
require_once __DIR__ . '/../models/Issue.php';
require_once __DIR__ . '/../models/Maintenance.php';
require_once __DIR__ . '/../models/Report.php';
require_once __DIR__ . '/../includes/functions.php';

class ReportController {
    private $db;
    private $report;
    private $device;
    private $issue;
    private $maintenance;
    
    public function __construct() {
        // Get database connection
        $database = new Database();
        $this->db = $database->getConnection();
        
        // Initialize objects
        $this->report = new Report($this->db);
        $this->device = new Device($this->db);
        $this->issue = new Issue($this->db);
        $this->maintenance = new Maintenance($this->db);
    }
    
    // Generate device inventory report
    public function generateInventoryReport($filters = []) {
        return $this->report->generate_inventory_report($filters);
    }
    
    // Generate device status report
    public function generateStatusReport() {
        $data = [];
        
        // Get device counts by status
        $status_result = $this->device->count_devices_by_status();
        $data['status_counts'] = [];
        while($row = $status_result->fetch(PDO::FETCH_ASSOC)) {
            $data['status_counts'][] = $row;
        }
        
        // Get device counts by type
        $type_result = $this->device->count_devices_by_type();
        $data['type_counts'] = [];
        while($row = $type_result->fetch(PDO::FETCH_ASSOC)) {
            $data['type_counts'][] = $row;
        }
        
        // Get device counts by department
        $dept_result = $this->device->count_devices_by_department();
        $data['department_counts'] = [];
        while($row = $dept_result->fetch(PDO::FETCH_ASSOC)) {
            $data['department_counts'][] = $row;
        }
        
        return $data;
    }
    
    // Generate maintenance report
    public function generateMaintenanceReport($filters = []) {
        return $this->report->generate_maintenance_report($filters);
    }
    
    // Generate issue report
    public function generateIssueReport($filters = []) {
        $data = [];
        
        // Get issue counts by status
        $status_result = $this->issue->count_issues_by_status();
        $data['status_counts'] = [];
        while($row = $status_result->fetch(PDO::FETCH_ASSOC)) {
            $data['status_counts'][] = $row;
        }
        
        // Get issue counts by priority
        $priority_result = $this->issue->count_issues_by_priority();
        $data['priority_counts'] = [];
        while($row = $priority_result->fetch(PDO::FETCH_ASSOC)) {
            $data['priority_counts'][] = $row;
        }
        
        // Get issue counts by device type
        $type_result = $this->issue->count_issues_by_device_type();
        $data['device_type_counts'] = [];
        while($row = $type_result->fetch(PDO::FETCH_ASSOC)) {
            $data['device_type_counts'][] = $row;
        }
        
        // Get detailed issue data with filters
        $data['issues'] = $this->report->generate_issue_report($filters);
        
        return $data;
    }
    
    // Generate warranty report
    public function generateWarrantyReport($days = 90) {
        $result = $this->device->get_devices_with_expiring_warranty($days);
        $data = [];
        
        while($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $data[] = $row;
        }
        
        return $data;
    }
    
    // Generate device history report
    public function generateDeviceHistoryReport($device_id) {
        $this->device->id = $device_id;
        $result = $this->device->get_history();
        $data = [];
        
        while($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $data[] = $row;
        }
        
        return $data;
    }
}