<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Device.php';
require_once __DIR__ . '/../includes/functions.php';

class DeviceController {
    private $db;
    private $device;
    
    public function __construct() {
        // Get database connection
        $database = new Database();
        $this->db = $database->getConnection();
        
        // Initialize device object
        $this->device = new Device($this->db);
    }
    
    // Get all devices
    public function getDevices() {
        return $this->device->read();
    }
    
    // Get device by ID
    public function getDeviceById($id) {
        $this->device->id = $id;
        if($this->device->read_single()) {
            return $this->device;
        }
        return false;
    }
    
    // Create new device
    public function createDevice($data) {
        // Set device properties
        $this->device->device_name = clean_input($data['device_name']);
        $this->device->device_type = clean_input($data['device_type']);
        $this->device->serial_number = clean_input($data['serial_number']);
        $this->device->model = clean_input($data['model']);
        $this->device->manufacturer = clean_input($data['manufacturer']);
        $this->device->purchase_date = clean_input($data['purchase_date']);
        $this->device->warranty_end = clean_input($data['warranty_end']);
        $this->device->status = clean_input($data['status']);
        $this->device->location = clean_input($data['location']);
        $this->device->department = clean_input($data['department']);
        $this->device->assigned_to = !empty($data['assigned_to']) ? clean_input($data['assigned_to']) : null;
        $this->device->last_maintenance = !empty($data['last_maintenance']) ? clean_input($data['last_maintenance']) : null;
        $this->device->next_maintenance = !empty($data['next_maintenance']) ? clean_input($data['next_maintenance']) : null;
        $this->device->recommendation = isset($data['recommendation']) ? clean_input($data['recommendation']) : null;
        $this->device->reliability_score = isset($data['reliability_score']) ? clean_input($data['reliability_score']) : null;
        $this->device->cost_benefit = isset($data['cost_benefit']) ? clean_input($data['cost_benefit']) : null;
        $this->device->health = isset($data['health']) ? clean_input($data['health']) : null;
        $this->device->alert = isset($data['alert']) ? clean_input($data['alert']) : null;
        $this->device->disposal_reason = isset($data['disposal_reason']) ? clean_input($data['disposal_reason']) : null;
        $this->device->disposal_date = isset($data['disposal_date']) ? clean_input($data['disposal_date']) : null;
        
        // Check if serial number already exists
        if($this->device->serial_number_exists()) {
            return ["success" => false, "message" => "Serial number already exists."];
        }
        
        // Create device
        if($this->device->create()) {
            // Add device history
            $this->device->add_history('created', 'Device created', $_SESSION['user_id']);
            return ["success" => true, "message" => "Device created successfully.", "id" => $this->device->id];
        }
        
        return ["success" => false, "message" => "Failed to create device."];
    }
    
    // Update device
    public function updateDevice($data) {
        // Set device properties
        $this->device->id = clean_input($data['id']);
        $this->device->device_name = clean_input($data['device_name']);
        $this->device->device_type = clean_input($data['device_type']);
        $this->device->serial_number = clean_input($data['serial_number']);
        $this->device->model = clean_input($data['model']);
        $this->device->manufacturer = clean_input($data['manufacturer']);
        $this->device->purchase_date = clean_input($data['purchase_date']);
        $this->device->warranty_end = clean_input($data['warranty_end']);
        $this->device->status = clean_input($data['status']);
        $this->device->location = clean_input($data['location']);
        $this->device->department = clean_input($data['department']);
        $this->device->assigned_to = !empty($data['assigned_to']) ? clean_input($data['assigned_to']) : null;
        $this->device->last_maintenance = !empty($data['last_maintenance']) ? clean_input($data['last_maintenance']) : null;
        $this->device->next_maintenance = !empty($data['next_maintenance']) ? clean_input($data['next_maintenance']) : null;
        $this->device->recommendation = isset($data['recommendation']) ? clean_input($data['recommendation']) : null;
        $this->device->reliability_score = isset($data['reliability_score']) ? clean_input($data['reliability_score']) : null;
        $this->device->cost_benefit = isset($data['cost_benefit']) ? clean_input($data['cost_benefit']) : null;
        $this->device->health = isset($data['health']) ? clean_input($data['health']) : null;
        $this->device->alert = isset($data['alert']) ? clean_input($data['alert']) : null;
        $this->device->disposal_reason = isset($data['disposal_reason']) ? clean_input($data['disposal_reason']) : null;
        $this->device->disposal_date = isset($data['disposal_date']) ? clean_input($data['disposal_date']) : null;
        
        // Get original device data for history
        $original = new Device($this->db);
        $original->id = $this->device->id;
        $original->read_single();
        
        // Update device
        if($this->device->update()) {
            // Add device history
            $this->device->add_history('updated', 'Device information updated', $_SESSION['user_id']);
            return ["success" => true, "message" => "Device updated successfully."];
        }
        
        return ["success" => false, "message" => "Failed to update device."];
    }
    
    // Delete device
    public function deleteDevice($id) {
        $this->device->id = $id;
        
        // Delete device
        if($this->device->delete()) {
            return ["success" => true, "message" => "Device deleted successfully."];
        }
        
        return ["success" => false, "message" => "Failed to delete device."];
    }
    
    // Get devices by department
    public function getDevicesByDepartment($department) {
        $this->device->department = $department;
        return $this->device->get_devices_by_department();
    }
    
    // Get devices by user
    public function getDevicesByUser($user_id) {
        $this->device->assigned_to = $user_id;
        return $this->device->get_devices_by_user();
    }
    
    // Get devices by status
    public function getDevicesByStatus($status) {
        $this->device->status = $status;
        return $this->device->get_devices_by_status();
    }
    
    // Get devices needing maintenance
    public function getDevicesNeedingMaintenance() {
        return $this->device->get_devices_needing_maintenance();
    }
    
    // Get devices with expiring warranty
    public function getDevicesWithExpiringWarranty($days = 30) {
        return $this->device->get_devices_with_expiring_warranty($days);
    }
    
    // Get device history
    public function getDeviceHistory($id) {
        $this->device->id = $id;
        return $this->device->get_history();
    }
    
    // Count devices by type
    public function countDevicesByType() {
        return $this->device->count_devices_by_type();
    }
    
    // Count devices by status
    public function countDevicesByStatus() {
        return $this->device->count_devices_by_status();
    }
    
    // Count devices by department
    public function countDevicesByDepartment() {
        return $this->device->count_devices_by_department();
    }
    
    // Get device health score
    public function getDeviceHealthScore($id) {
        $this->device->id = $id;
        if($this->device->read_single()) {
            return $this->device->calculate_health_score();
        }
        return false;
    }

    // Get device lifecycle stage
    public function getDeviceLifecycleStage($id) {
        $this->device->id = $id;
        if($this->device->read_single()) {
            return $this->device->determine_lifecycle_stage();
        }
        return false;
    }

    // Get total cost of ownership
    public function getDeviceTCO($id) {
        $this->device->id = $id;
        if($this->device->read_single()) {
            return $this->device->calculate_tco();
        }
        return false;
    }

    // Get maintenance recommendation
    public function getMaintenanceRecommendation($id) {
        $this->device->id = $id;
        if($this->device->read_single()) {
            return [
                'needs_maintenance' => $this->device->needs_maintenance(),
                'next_date' => $this->device->next_maintenance
            ];
        }
        return false;
    }

    // Get replacement recommendation
    public function getReplacementRecommendation($id) {
        $this->device->id = $id;
        if($this->device->read_single()) {
            return $this->device->get_replacement_recommendation();
        }
        return false;
    }

    // Auto-schedule maintenance for all devices
    public function scheduleAllMaintenance() {
        $devices = $this->getDevices();
        $count = 0;
        
        while ($row = $devices->fetch(PDO::FETCH_ASSOC)) {
            $this->device->id = $row['id'];
            $this->device->read_single();
            
            if ($this->device->needs_maintenance() && empty($this->device->next_maintenance)) {
                if ($this->device->schedule_next_maintenance()) {
                    $count++;
                }
            }
        }
        
        return ['success' => true, 'devices_scheduled' => $count];
    }

    // Update all device health scores
    public function updateAllHealthScores() {
        $devices = $this->getDevices();
        $updated = 0;
        
        while ($row = $devices->fetch(PDO::FETCH_ASSOC)) {
            $this->device->id = $row['id'];
            $this->device->read_single();
            
            // Calculate and update health score
            $health_score = $this->device->calculate_health_score();
            $this->device->health = $health_score;
            
            // Generate recommendation and cost-benefit
            $recommendation = $this->device->get_replacement_recommendation();
            $this->device->recommendation = $recommendation['recommendation'];
            $this->device->reliability_score = $health_score;
            $this->device->cost_benefit = $recommendation['cost_benefit'];
            
            // Set alert if health is low
            if ($health_score < 50) {
                $this->device->alert = "Low health score: $health_score";
            } else {
                $this->device->alert = null;
            }
            
            if($this->device->update()) {
                $updated++;
            }
        }
        
        return ['success' => true, 'devices_updated' => $updated];
    }
}