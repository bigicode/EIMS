<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Issue.php';
require_once __DIR__ . '/../models/Device.php';
require_once __DIR__ . '/../includes/functions.php';

class IssueController {
    private $db;
    private $issue;
    private $device;
    
    public function __construct() {
        // Get database connection
        $database = new Database();
        $this->db = $database->getConnection();
        
        // Initialize issue object
        $this->issue = new Issue($this->db);
        
        // Initialize device object for history
        $this->device = new Device($this->db);
    }
    
    // Get all issues
    public function getIssues() {
        return $this->issue->read();
    }
    
    // Get issue by ID
    public function getIssueById($id) {
        $this->issue->id = $id;
        if($this->issue->read_single()) {
            return $this->issue;
        }
        return false;
    }
    
    // Create new issue
    public function createIssue($data) {
        // Set issue properties
        $this->issue->device_id = clean_input($data['device_id']);
        $this->issue->reported_by = clean_input($data['reported_by']);
        $this->issue->issue_title = clean_input($data['issue_title']);
        $this->issue->issue_description = clean_input($data['issue_description']);
        $this->issue->priority = clean_input($data['priority']);
        $this->issue->status = 'open';
        
        // Create issue
        if($this->issue->create()) {
            // Add device history
            $this->device->id = $this->issue->device_id;
            $this->device->add_history('issue_reported', "Issue reported: {$this->issue->issue_title}", $_SESSION['user_id']);
            
            return ["success" => true, "message" => "Issue reported successfully.", "id" => $this->issue->id];
        }
        
        return ["success" => false, "message" => "Failed to report issue."];
    }
    
    // Update issue
    public function updateIssue($data) {
        // Set issue properties
        $this->issue->id = clean_input($data['id']);
        $this->issue->issue_title = clean_input($data['issue_title']);
        $this->issue->issue_description = clean_input($data['issue_description']);
        $this->issue->priority = clean_input($data['priority']);
        $this->issue->status = clean_input($data['status']);
        $this->issue->resolution_notes = isset($data['resolution_notes']) ? clean_input($data['resolution_notes']) : null;
        
        // Get original issue data
        $original = new Issue($this->db);
        $original->id = $this->issue->id;
        $original->read_single();
        
        // Update issue
        if($this->issue->update()) {
            // Add device history if status changed
            if($original->status != $this->issue->status) {
                $this->device->id = $original->device_id;
                $this->device->add_history('issue_status_changed', "Issue status changed to {$this->issue->status}", $_SESSION['user_id']);
            }
            
            return ["success" => true, "message" => "Issue updated successfully."];
        }
        
        return ["success" => false, "message" => "Failed to update issue."];
    }
    
    // Delete issue
    public function deleteIssue($id) {
        $this->issue->id = $id;
        
        // Get issue details before deletion
        $this->issue->read_single();
        $device_id = $this->issue->device_id;
        
        // Delete issue
        if($this->issue->delete()) {
            // Add device history
            $this->device->id = $device_id;
            $this->device->add_history('issue_deleted', "Issue deleted", $_SESSION['user_id']);
            
            return ["success" => true, "message" => "Issue deleted successfully."];
        }
        
        return ["success" => false, "message" => "Failed to delete issue."];
    }
    
    // Get issues by device
    public function getIssuesByDevice($device_id) {
        $this->issue->device_id = $device_id;
        return $this->issue->get_issues_by_device();
    }
    
    // Get issues by user
    public function getIssuesByUser($user_id) {
        $this->issue->reported_by = $user_id;
        return $this->issue->get_issues_by_user();
    }
    
    // Get issues by status
    public function getIssuesByStatus($status) {
        $this->issue->status = $status;
        return $this->issue->get_issues_by_status();
    }
    
    // Count issues by status
    public function countIssuesByStatus() {
        return $this->issue->count_issues_by_status();
    }
    
    // Count issues by priority
    public function countIssuesByPriority() {
        return $this->issue->count_issues_by_priority();
    }
    
    // Count issues by device type
    public function countIssuesByDeviceType() {
        return $this->issue->count_issues_by_device_type();
    }
    
    // Get issues by priority
    public function getIssuesByPriority($priority) {
        return $this->issue->get_by_priority($priority);
    }
    
    // Get issues by reporter
    public function getIssuesByReporter($user_id) {
        return $this->issue->get_by_reporter($user_id);
    }
}
