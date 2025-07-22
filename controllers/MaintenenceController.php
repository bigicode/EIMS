<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Maintenance.php';
require_once __DIR__ . '/../models/Device.php';
require_once __DIR__ . '/../includes/functions.php';

class MaintenanceController {
    private $db;
    private $maintenance;
    private $device;
    
    public function __construct() {
        // Get database connection
        $database = new Database();
        $this->db = $database->getConnection();
        
        // Initialize maintenance object
        $this->maintenance = new Maintenance($this->db);
        
        // Initialize device object for updates and history
        $this->device = new Device($this->db);
    }
    
    // Get all maintenance records
    public function getAllMaintenance() {
        return $this->maintenance->read();
    }
    
    // Get maintenance by ID
    public function getMaintenanceById($id) {
        $this->maintenance->id = $id;
        if($this->maintenance->read_single()) {
            return $this->maintenance;
        }
        return false;
    }
    
    // Create new maintenance record
    public function createMaintenance($data) {
        // Set maintenance properties
        $this->maintenance->device_id = clean_input($data['device_id']);
        $this->maintenance->maintenance_type = clean_input($data['maintenance_type']);
        $this->maintenance->description = clean_input($data['description']);
        $this->maintenance->scheduled_date = clean_input($data['scheduled_date']);
        $this->maintenance->status = 'scheduled';
        
        // Create maintenance record
        if($this->maintenance->create()) {
            // Add device history
            $this->device->id = $this->maintenance->device_id;
            $this->device->add_history(
                'Maintenance Scheduled',
                "Scheduled {$this->maintenance->maintenance_type} maintenance for " . date('Y-m-d', strtotime($this->maintenance->scheduled_date)),
                $_SESSION['user_id']
            );
            
            // Update device's next maintenance date
            $this->device->next_maintenance = $this->maintenance->scheduled_date;
            $this->device->update_maintenance_date();
            
            return true;
        }
        return false;
    }
    
    // Update maintenance record
    public function updateMaintenance($data) {
        // Set maintenance properties
        $this->maintenance->id = clean_input($data['id']);
        $this->maintenance->device_id = clean_input($data['device_id']);
        $this->maintenance->maintenance_type = clean_input($data['maintenance_type']);
        $this->maintenance->description = clean_input($data['description']);
        $this->maintenance->scheduled_date = clean_input($data['scheduled_date']);
        $this->maintenance->status = clean_input($data['status']);
        
        // Set completion details if status is completed
        if($this->maintenance->status == 'completed') {
            $this->maintenance->performed_by = $_SESSION['user_id'];
            $this->maintenance->completion_date = date('Y-m-d');
            $this->maintenance->notes = isset($data['notes']) ? clean_input($data['notes']) : null;
            
            // Update device's last maintenance date
            $this->device->id = $this->maintenance->device_id;
            $this->device->last_maintenance = date('Y-m-d');
            $this->device->update_maintenance_date();
            
            // Add device history
            $this->device->add_history(
                'Maintenance Completed',
                "Completed {$this->maintenance->maintenance_type} maintenance",
                $_SESSION['user_id']
            );
            
            // If device was in maintenance status, set it back to active
            $this->device->read_single();
            if($this->device->status == 'maintenance') {
                $this->device->status = 'active';
                $this->device->update_status();
            }
        } 
        // If status changed to in_progress, update device status
        else if($this->maintenance->status == 'in_progress') {
            // Add device history
            $this->device->id = $this->maintenance->device_id;
            $this->device->add_history(
                'Maintenance Started',
                "Started {$this->maintenance->maintenance_type} maintenance",
                $_SESSION['user_id']
            );
            
            // Set device status to maintenance
            $this->device->status = 'maintenance';
            $this->device->update_status();
        }
        
        // Update maintenance record
        if($this->maintenance->update()) {
            return true;
        }
        return false;
    }
    
    // Delete maintenance record
    public function deleteMaintenance($id) {
        // Set maintenance ID
        $this->maintenance->id = $id;
        
        // Get maintenance details before deleting
        if($this->maintenance->read_single()) {
            $device_id = $this->maintenance->device_id;
            $maintenance_type = $this->maintenance->maintenance_type;
            
            // Delete maintenance record
            if($this->maintenance->delete()) {
                // Add device history
                $this->device->id = $device_id;
                $this->device->add_history(
                    'Maintenance Deleted',
                    "Deleted scheduled {$maintenance_type} maintenance",
                    $_SESSION['user_id']
                );
                
                return true;
            }
        }
        return false;
    }
    
    // Get upcoming maintenance
    public function getUpcomingMaintenance() {
        return $this->maintenance->get_upcoming_maintenance();
    }
    
    // Get overdue maintenance
    public function getOverdueMaintenance() {
        return $this->maintenance->get_overdue_maintenance();
    }
    
    // Get maintenance by device
    public function getMaintenanceByDevice($device_id) {
        return $this->maintenance->get_by_device($device_id);
    }
    
    // Complete maintenance with detailed reporting
    public function completeMaintenance($data) {
        // First, verify the maintenance record exists
        $this->maintenance->id = $data['id'];
        if (!$this->maintenance->read_single()) {
            return false;
        }
        
        // Set basic maintenance properties
        $this->maintenance->maintenance_type = $data['maintenance_type'];
        $this->maintenance->description = $data['description'];
        $this->maintenance->scheduled_date = $data['scheduled_date'];
        $this->maintenance->status = 'completed';
        $this->maintenance->performed_by = $_SESSION['user_id'];
        $this->maintenance->completion_date = date('Y-m-d');
        
        // Set additional maintenance properties
        $this->maintenance->maintenance_cost = $data['maintenance_cost'];
        $this->maintenance->parts_replaced = $data['parts_replaced'];
        $this->maintenance->parts_details = $data['parts_details'];
        $this->maintenance->issues_found = $data['issues_found'];
        $this->maintenance->resolution = $data['resolution'];
        $this->maintenance->notes = $data['notes'];
        $this->maintenance->device_health_impact = $data['device_health_impact'];
        $this->maintenance->next_recommended_date = $data['next_recommended_date'];
        
        // Update maintenance record
        if (!$this->maintenance->update()) {
            return false;
        }
        
        // Update device record
        $this->device->id = $this->maintenance->device_id;
        if ($this->device->read_single()) {
            // Update device maintenance dates
            $this->device->last_maintenance = date('Y-m-d');
            $this->device->next_maintenance = $data['next_recommended_date'];
            
            // If device was in maintenance status, set it back to active
            if ($this->device->status == 'maintenance') {
                $this->device->status = 'active';
            }
            
            // Update device health metrics based on maintenance impact
            $health_score = 0;
            switch ($data['device_health_impact']) {
                case 'significant_improvement':
                    $health_score = 20;
                    break;
                case 'minor_improvement':
                    $health_score = 10;
                    break;
                case 'no_change':
                    $health_score = 0;
                    break;
                case 'deterioration':
                    $health_score = -10;
                    break;
            }
            
            // If the device already has a reliability_score, adjust it
            if (isset($this->device->reliability_score)) {
                $current_score = intval($this->device->reliability_score);
                $new_score = min(100, max(0, $current_score + $health_score));
                $this->device->reliability_score = $new_score;
            }
            
            // Add maintenance details to device recommendations if issues were found
            if (!empty($data['issues_found'])) {
                $recommendation = "Based on recent maintenance: ";
                
                if ($data['device_health_impact'] === 'deterioration') {
                    $recommendation .= "Consider replacement due to deteriorating condition.";
                    $this->device->recommendation = "Replace";
                } else if ($data['device_health_impact'] === 'no_change' && !empty($data['issues_found'])) {
                    $recommendation .= "Monitor device for recurring issues.";
                    $this->device->recommendation = "Monitor";
                } else {
                    $recommendation .= "Continue regular maintenance.";
                    $this->device->recommendation = "Keep";
                }
                
                // Add maintenance cost to device TCO calculation
                if (!empty($data['maintenance_cost'])) {
                    $cost = floatval($data['maintenance_cost']);
                    // Assuming cost_benefit is a TCO metric
                    if (isset($this->device->cost_benefit)) {
                        $this->device->cost_benefit = floatval($this->device->cost_benefit) + $cost;
                    }
                }
            }
            
            // Update the device
            if (!$this->device->update()) {
                return false;
            }
            
            // Add device history entry
            $history_description = "Completed {$this->maintenance->maintenance_type} maintenance";
            if (!empty($data['issues_found'])) {
                $history_description .= " - Issues found: {$data['issues_found']}";
            }
            $this->device->add_history('maintenance_completed', $history_description, $_SESSION['user_id']);
            
            return true;
        }
        
        return false;
    }
}
?>