<?php
class Device {
    // Database connection and table name
    private $conn;
    private $table_name = "devices";

    // Object properties
    public $id;
    public $device_name;
    public $device_type;
    public $serial_number;
    public $model;
    public $manufacturer;
    public $purchase_date;
    public $warranty_end;
    public $status;
    public $location;
    public $department;
    public $assigned_to;
    public $last_maintenance;
    public $next_maintenance;
    public $created_at;
    public $assigned_to_name;
    public $recommendation;
    public $reliability_score;
    public $cost_benefit;
    public $health;
    public $alert;
    public $disposal_reason;
    public $disposal_date;

    // Constructor with DB connection
    public function __construct($db) {
        $this->conn = $db;
    }

    // Create device
    public function create() {
        // Sanitize inputs
        $this->device_name = htmlspecialchars(strip_tags($this->device_name));
        $this->device_type = htmlspecialchars(strip_tags($this->device_type));
        $this->serial_number = htmlspecialchars(strip_tags($this->serial_number));
        $this->model = htmlspecialchars(strip_tags($this->model));
        $this->manufacturer = htmlspecialchars(strip_tags($this->manufacturer));
        $this->purchase_date = htmlspecialchars(strip_tags($this->purchase_date));
        $this->warranty_end = htmlspecialchars(strip_tags($this->warranty_end));
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->location = htmlspecialchars(strip_tags($this->location));
        $this->department = htmlspecialchars(strip_tags($this->department));
        $this->assigned_to = $this->assigned_to ? htmlspecialchars(strip_tags($this->assigned_to)) : null;
        $this->last_maintenance = $this->last_maintenance ? htmlspecialchars(strip_tags($this->last_maintenance)) : null;
        $this->next_maintenance = $this->next_maintenance ? htmlspecialchars(strip_tags($this->next_maintenance)) : null;

        // Query to insert record
        $query = "INSERT INTO " . $this->table_name . "
                SET device_name=:device_name, device_type=:device_type, serial_number=:serial_number,
                    model=:model, manufacturer=:manufacturer, purchase_date=:purchase_date,
                    warranty_end=:warranty_end, status=:status, location=:location,
                    department=:department, assigned_to=:assigned_to, last_maintenance=:last_maintenance,
                    next_maintenance=:next_maintenance";

        // Prepare query
        $stmt = $this->conn->prepare($query);

        // Bind values
        $stmt->bindParam(":device_name", $this->device_name);
        $stmt->bindParam(":device_type", $this->device_type);
        $stmt->bindParam(":serial_number", $this->serial_number);
        $stmt->bindParam(":model", $this->model);
        $stmt->bindParam(":manufacturer", $this->manufacturer);
        $stmt->bindParam(":purchase_date", $this->purchase_date);
        $stmt->bindParam(":warranty_end", $this->warranty_end);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":location", $this->location);
        $stmt->bindParam(":department", $this->department);
        $stmt->bindParam(":assigned_to", $this->assigned_to);
        $stmt->bindParam(":last_maintenance", $this->last_maintenance);
        $stmt->bindParam(":next_maintenance", $this->next_maintenance);

        // Execute query
        if($stmt->execute()) {
            // Get the ID of the inserted record
            $this->id = $this->conn->lastInsertId();
            return true;
        }

        return false;
    }

    // Read all devices
    public function read() {
        // Query to select all devices
        $query = "SELECT d.*, u.full_name as assigned_to_name
                FROM " . $this->table_name . " d
                LEFT JOIN users u ON d.assigned_to = u.id
                ORDER BY d.created_at DESC";

        // Prepare query statement
        $stmt = $this->conn->prepare($query);

        // Execute query
        $stmt->execute();

        return $stmt;
    }

    // Read single device
    public function read_single() {
        // Query to read single record
        $query = "SELECT d.*, u.full_name as assigned_to_name
                FROM " . $this->table_name . " d
                LEFT JOIN users u ON d.assigned_to = u.id
                WHERE d.id = ?";

        // Prepare query statement
        $stmt = $this->conn->prepare($query);

        // Bind ID
        $stmt->bindParam(1, $this->id);

        // Execute query
        $stmt->execute();

        // Get retrieved row
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        // Set properties
        if($row) {
            $this->device_name = $row['device_name'];
            $this->device_type = $row['device_type'];
            $this->serial_number = $row['serial_number'];
            $this->model = $row['model'];
            $this->manufacturer = $row['manufacturer'];
            $this->purchase_date = $row['purchase_date'];
            $this->warranty_end = $row['warranty_end'];
            $this->status = $row['status'];
            $this->location = $row['location'];
            $this->department = $row['department'];
            $this->assigned_to = $row['assigned_to'];
            $this->assigned_to_name = $row['assigned_to_name'];
            $this->last_maintenance = $row['last_maintenance'];
            $this->next_maintenance = $row['next_maintenance'];
            $this->created_at = $row['created_at'];
            return true;
        }

        return false;
    }

    // Update device
    public function update() {
        // Sanitize inputs
        $this->id = htmlspecialchars(strip_tags($this->id));
        $this->device_name = htmlspecialchars(strip_tags($this->device_name));
        $this->device_type = htmlspecialchars(strip_tags($this->device_type));
        $this->serial_number = htmlspecialchars(strip_tags($this->serial_number));
        $this->model = htmlspecialchars(strip_tags($this->model));
        $this->manufacturer = htmlspecialchars(strip_tags($this->manufacturer));
        $this->purchase_date = htmlspecialchars(strip_tags($this->purchase_date));
        $this->warranty_end = htmlspecialchars(strip_tags($this->warranty_end));
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->location = htmlspecialchars(strip_tags($this->location));
        $this->department = htmlspecialchars(strip_tags($this->department));
        $this->assigned_to = $this->assigned_to ? htmlspecialchars(strip_tags($this->assigned_to)) : null;
        $this->last_maintenance = $this->last_maintenance ? htmlspecialchars(strip_tags($this->last_maintenance)) : null;
        $this->next_maintenance = $this->next_maintenance ? htmlspecialchars(strip_tags($this->next_maintenance)) : null;
        $this->recommendation = htmlspecialchars(strip_tags($this->recommendation));
        $this->reliability_score = htmlspecialchars(strip_tags($this->reliability_score));
        $this->cost_benefit = htmlspecialchars(strip_tags($this->cost_benefit));
        $this->health = htmlspecialchars(strip_tags($this->health));
        $this->alert = htmlspecialchars(strip_tags($this->alert));
        $this->disposal_reason = htmlspecialchars(strip_tags($this->disposal_reason));
        $this->disposal_date = htmlspecialchars(strip_tags($this->disposal_date));

        // Query to update record
        $query = "UPDATE " . $this->table_name . "
                SET device_name=:device_name, device_type=:device_type, serial_number=:serial_number,
                    model=:model, manufacturer=:manufacturer, purchase_date=:purchase_date,
                    warranty_end=:warranty_end, status=:status, location=:location,
                    department=:department, assigned_to=:assigned_to, last_maintenance=:last_maintenance,
                    next_maintenance=:next_maintenance, recommendation=:recommendation, reliability_score=:reliability_score, cost_benefit=:cost_benefit, health=:health, alert=:alert, disposal_reason=:disposal_reason, disposal_date=:disposal_date
                WHERE id=:id";

        // Prepare query statement
        $stmt = $this->conn->prepare($query);

        // Bind parameters
        $stmt->bindParam(":id", $this->id);
        $stmt->bindParam(":device_name", $this->device_name);
        $stmt->bindParam(":device_type", $this->device_type);
        $stmt->bindParam(":serial_number", $this->serial_number);
        $stmt->bindParam(":model", $this->model);
        $stmt->bindParam(":manufacturer", $this->manufacturer);
        $stmt->bindParam(":purchase_date", $this->purchase_date);
        $stmt->bindParam(":warranty_end", $this->warranty_end);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":location", $this->location);
        $stmt->bindParam(":department", $this->department);
        $stmt->bindParam(":assigned_to", $this->assigned_to);
        $stmt->bindParam(":last_maintenance", $this->last_maintenance);
        $stmt->bindParam(":next_maintenance", $this->next_maintenance);
        $stmt->bindParam(":recommendation", $this->recommendation);
        $stmt->bindParam(":reliability_score", $this->reliability_score);
        $stmt->bindParam(":cost_benefit", $this->cost_benefit);
        $stmt->bindParam(":health", $this->health);
        $stmt->bindParam(":alert", $this->alert);
        $stmt->bindParam(":disposal_reason", $this->disposal_reason);
        $stmt->bindParam(":disposal_date", $this->disposal_date);

        // Execute query
        if($stmt->execute()) {
            return true;
        }

        return false;
    }

    // Delete device
    public function delete() {
        // Sanitize input
        $this->id = htmlspecialchars(strip_tags($this->id));

        // Query to delete record
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";

        // Prepare query statement
        $stmt = $this->conn->prepare($query);

        // Bind parameter
        $stmt->bindParam(1, $this->id);

        // Execute query
        if($stmt->execute()) {
            return true;
        }

        return false;
    }

    // Check if serial number exists
    public function serial_number_exists() {
        // Sanitize input
        $this->serial_number = htmlspecialchars(strip_tags($this->serial_number));

        // Query to check if serial number exists
        $query = "SELECT id FROM " . $this->table_name . " WHERE serial_number = ?";

        // Prepare query statement
        $stmt = $this->conn->prepare($query);

        // Bind parameter
        $stmt->bindParam(1, $this->serial_number);

        // Execute query
        $stmt->execute();

        // Return true if serial number exists
        return $stmt->rowCount() > 0;
    }

    // Get devices by department
    public function get_devices_by_department() {
        // Sanitize input
        $this->department = htmlspecialchars(strip_tags($this->department));

        // Query to get devices by department
        $query = "SELECT d.*, u.full_name as assigned_to_name
                FROM " . $this->table_name . " d
                LEFT JOIN users u ON d.assigned_to = u.id
                WHERE d.department = ?
                ORDER BY d.created_at DESC";

        // Prepare query statement
        $stmt = $this->conn->prepare($query);

        // Bind parameter
        $stmt->bindParam(1, $this->department);

        // Execute query
        $stmt->execute();

        return $stmt;
    }

    // Get devices by user
    public function get_devices_by_user() {
        // Sanitize input
        $this->assigned_to = htmlspecialchars(strip_tags($this->assigned_to));

        // Query to get devices by user
        $query = "SELECT d.*, u.full_name as assigned_to_name
                FROM " . $this->table_name . " d
                LEFT JOIN users u ON d.assigned_to = u.id
                WHERE d.assigned_to = ?
                ORDER BY d.created_at DESC";

        // Prepare query statement
        $stmt = $this->conn->prepare($query);

        // Bind parameter
        $stmt->bindParam(1, $this->assigned_to);

        // Execute query
        $stmt->execute();

        return $stmt;
    }

    // Get devices by status
    public function get_devices_by_status() {
        // Sanitize input
        $this->status = htmlspecialchars(strip_tags($this->status));

        // Query to get devices by status
        $query = "SELECT d.*, u.full_name as assigned_to_name
                FROM " . $this->table_name . " d
                LEFT JOIN users u ON d.assigned_to = u.id
                WHERE d.status = ?
                ORDER BY d.created_at DESC";

        // Prepare query statement
        $stmt = $this->conn->prepare($query);

        // Bind parameter
        $stmt->bindParam(1, $this->status);

        // Execute query
        $stmt->execute();

        return $stmt;
    }

    // Get devices that need maintenance
    public function get_devices_needing_maintenance() {
        // Get current date
        $today = date('Y-m-d');

        // Query to get devices needing maintenance
        $query = "SELECT d.*, u.full_name as assigned_to_name
                FROM " . $this->table_name . " d
                LEFT JOIN users u ON d.assigned_to = u.id
                WHERE d.next_maintenance <= ? AND d.status != 'disposed'
                ORDER BY d.next_maintenance ASC";

        // Prepare query statement
        $stmt = $this->conn->prepare($query);

        // Bind parameter
        $stmt->bindParam(1, $today);

        // Execute query
        $stmt->execute();

        return $stmt;
    }

    // Get devices with expiring warranty
    public function get_devices_with_expiring_warranty($days = 30) {
        // Calculate date threshold
        $threshold_date = date('Y-m-d', strtotime("+{$days} days"));
        $today = date('Y-m-d');

        // Query to get devices with expiring warranty
        $query = "SELECT d.*, u.full_name as assigned_to_name,
                    DATEDIFF(d.warranty_end, ?) as days_until_expiry
                FROM " . $this->table_name . " d
                LEFT JOIN users u ON d.assigned_to = u.id
                WHERE d.warranty_end BETWEEN ? AND ?
                    AND d.status != 'disposed'
                ORDER BY d.warranty_end ASC";

        // Prepare query statement
        $stmt = $this->conn->prepare($query);

        // Bind parameters
        $stmt->bindParam(1, $today);
        $stmt->bindParam(2, $today);
        $stmt->bindParam(3, $threshold_date);

        // Execute query
        $stmt->execute();

        return $stmt;
    }

    // Count devices by type
    public function count_devices_by_type() {
        // Query to count devices by type
        $query = "SELECT device_type, COUNT(*) as count
                FROM " . $this->table_name . "
                GROUP BY device_type
                ORDER BY count DESC";

        // Prepare query statement
        $stmt = $this->conn->prepare($query);

        // Execute query
        $stmt->execute();

        return $stmt;
    }

    // Count devices by status
    public function count_devices_by_status() {
        // Query to count devices by status
        $query = "SELECT status, COUNT(*) as count
                FROM " . $this->table_name . "
                GROUP BY status
                ORDER BY count DESC";

        // Prepare query statement
        $stmt = $this->conn->prepare($query);

        // Execute query
        $stmt->execute();

        return $stmt;
    }

    // Count devices by department
    public function count_devices_by_department() {
        // Query to count devices by department
        $query = "SELECT department, COUNT(*) as count
                FROM " . $this->table_name . "
                GROUP BY department
                ORDER BY count DESC";

        // Prepare query statement
        $stmt = $this->conn->prepare($query);

        // Execute query
        $stmt->execute();

        return $stmt;
    }

    // Add device history
    public function add_history($action, $description, $performed_by) {
        // Sanitize inputs
        $this->id = htmlspecialchars(strip_tags($this->id));
        $action = htmlspecialchars(strip_tags($action));
        $description = htmlspecialchars(strip_tags($description));
        $performed_by = htmlspecialchars(strip_tags($performed_by));

        // Query to insert history record
        $query = "INSERT INTO device_history
                SET device_id=:device_id, action=:action, description=:description, performed_by=:performed_by";

        // Prepare query
        $stmt = $this->conn->prepare($query);

        // Bind values
        $stmt->bindParam(":device_id", $this->id);
        $stmt->bindParam(":action", $action);
        $stmt->bindParam(":description", $description);
        $stmt->bindParam(":performed_by", $performed_by);

        // Execute query
        if($stmt->execute()) {
            return true;
        }

        return false;
    }

    // Get device history
    public function get_history() {
        // Sanitize input
        $this->id = htmlspecialchars(strip_tags($this->id));

        // Query to get device history
        $query = "SELECT h.*, u.full_name as performed_by_name
                FROM device_history h
                LEFT JOIN users u ON h.performed_by = u.id
                WHERE h.device_id = ?
                ORDER BY h.performed_at DESC";

        // Prepare query statement
        $stmt = $this->conn->prepare($query);

        // Bind parameter
        $stmt->bindParam(1, $this->id);

        // Execute query
        $stmt->execute();

        return $stmt;
    }

    public function count_all() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (int)$row['total'] : 0;
    }

    public function count_by_status($status) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE status = :status";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (int)$row['total'] : 0;
    }

    public function get_expiring_warranty($days = 30) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE warranty_end BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL :days DAY)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':days', (int)$days, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt;
    }

    public function read_by_status($status) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE status = :status";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->execute();
        return $stmt;
    }

    public function get_by_assigned_user($user_id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE assigned_to = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        return $stmt;
    }

    public function get_by_department($department) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE department = :department";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':department', $department);
        $stmt->execute();
        return $stmt;
    }

    public function get_by_department_or_user($department, $user_id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE department = :department OR assigned_to = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':department', $department);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        return $stmt;
    }

    // Update only the next_maintenance date for a device
    public function update_maintenance_date() {
        $query = "UPDATE " . $this->table_name . " SET next_maintenance = :next_maintenance WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':next_maintenance', $this->next_maintenance);
        $stmt->bindParam(':id', $this->id);
        return $stmt->execute();
    }

    // Calculate device health score based on age, issues, and maintenance
    public function calculate_health_score() {
        // Fetch device age
        $purchase_date = new DateTime($this->purchase_date);
        $today = new DateTime();
        $age = $purchase_date->diff($today)->y;
        
        // Get maintenance records
        $query = "SELECT COUNT(*) FROM maintenance WHERE device_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        $maintenance_count = $stmt->fetchColumn();
        
        // Get issue records
        $query = "SELECT COUNT(*) FROM issues WHERE device_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        $issue_count = $stmt->fetchColumn();
        
        // Calculate base health score (100 - age*5)
        $health_score = 100 - ($age * 5);
        
        // Deduct for issues
        $health_score -= ($issue_count * 3);
        
        // Add for regular maintenance
        $health_score += ($maintenance_count * 2);
        
        // Warranty factor
        $warranty_end = new DateTime($this->warranty_end);
        if ($today <= $warranty_end) {
            $health_score += 10;
        } else {
            $health_score -= 10;
        }
        
        // Cap the score between 0 and 100
        $health_score = max(0, min(100, $health_score));
        
        return $health_score;
    }

    // Determine lifecycle stage
    public function determine_lifecycle_stage() {
        $purchase_date = new DateTime($this->purchase_date);
        $today = new DateTime();
        $age_days = $purchase_date->diff($today)->days;
        
        // Get expected lifespan for device type (in days)
        $lifespan = $this->get_expected_lifespan($this->device_type);
        
        // Calculate health score
        $health_score = $this->calculate_health_score();
        
        if ($age_days < 30) {
            return "Acquisition";
        } elseif ($health_score > 70) {
            return "Active Use";
        } elseif ($health_score > 40) {
            return "Maintenance Phase";
        } else {
            return "End-of-Life";
        }
    }

    // Get expected lifespan based on device type
    private function get_expected_lifespan($device_type) {
        $lifespans = [
            'desktop' => 1825, // 5 years
            'laptop' => 1460,  // 4 years
            'tablet' => 1095,  // 3 years
            'printer' => 2190, // 6 years
            'server' => 2555,  // 7 years
            'network' => 2190  // 6 years
        ];
        
        return isset($lifespans[strtolower($device_type)]) ? 
            $lifespans[strtolower($device_type)] : 1460; // Default 4 years
    }

    // Calculate total cost of ownership
    public function calculate_tco() {
        // Get initial purchase cost
        $purchase_cost = floatval($this->purchase_cost ?? 0);
        
        // Get maintenance costs
        // Note: This assumes you have a 'cost' column in the maintenance table
        // If not, you'll need to modify this query accordingly
        $query = "SELECT COUNT(*) FROM maintenance WHERE device_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        $maintenance_count = $stmt->fetchColumn();
        
        // Estimate maintenance cost if actual cost isn't available
        $maintenance_cost = $maintenance_count * 100; // Assume $100 per maintenance
        
        // Calculate TCO
        $tco = $purchase_cost + $maintenance_cost;
        
        return $tco;
    }

    // Determine if maintenance is needed
    public function needs_maintenance() {
        // Check if next_maintenance date is set and is in the past or today
        if (!empty($this->next_maintenance)) {
            $next_maint = new DateTime($this->next_maintenance);
            $today = new DateTime();
            return $next_maint <= $today;
        }
        
        // If no next_maintenance is set, check based on last_maintenance
        if (!empty($this->last_maintenance)) {
            $last_maint = new DateTime($this->last_maintenance);
            $today = new DateTime();
            $diff = $last_maint->diff($today)->days;
            
            // Determine maintenance interval based on device type
            $interval = $this->get_maintenance_interval($this->device_type);
            
            return $diff >= $interval;
        }
        
        // If no maintenance dates are set, check if device is older than 6 months
        $purchase_date = new DateTime($this->purchase_date);
        $today = new DateTime();
        $age_days = $purchase_date->diff($today)->days;
        
        return $age_days > 180; // 6 months
    }

    // Get maintenance interval based on device type (in days)
    private function get_maintenance_interval($device_type) {
        $intervals = [
            'desktop' => 180, // 6 months
            'laptop' => 180,  // 6 months
            'tablet' => 365,  // 1 year
            'printer' => 90,  // 3 months
            'server' => 90,   // 3 months
            'network' => 180  // 6 months
        ];
        
        return isset($intervals[strtolower($device_type)]) ? 
            $intervals[strtolower($device_type)] : 180; // Default 6 months
    }

    // Schedule next maintenance based on device type
    public function schedule_next_maintenance() {
        $interval = $this->get_maintenance_interval($this->device_type);
        
        $base_date = !empty($this->last_maintenance) ? 
            new DateTime($this->last_maintenance) : 
            new DateTime();
        
        $next_date = clone $base_date;
        $next_date->add(new DateInterval('P' . $interval . 'D'));
        
        $this->next_maintenance = $next_date->format('Y-m-d');
        return $this->update();
    }

    // Generate replacement recommendation
    public function get_replacement_recommendation() {
        $health_score = $this->calculate_health_score();
        $lifecycle_stage = $this->determine_lifecycle_stage();
        $tco = $this->calculate_tco();
        
        // Get current value (estimate based on depreciation)
        $purchase_date = new DateTime($this->purchase_date);
        $today = new DateTime();
        $age_years = $purchase_date->diff($today)->y;
        
        $initial_value = floatval($this->purchase_cost ?? 0);
        $current_value = $initial_value * pow(0.7, $age_years); // 30% depreciation per year
        
        // Get cost-benefit ratio
        $cost_benefit = $current_value > 0 ? $tco / $current_value : 999;
        
        if ($health_score < 30 || $lifecycle_stage == "End-of-Life" || $cost_benefit > 2) {
            return [
                'recommendation' => 'Replace',
                'reason' => ($health_score < 30 ? 'Low health score. ' : '') . 
                            ($lifecycle_stage == "End-of-Life" ? 'End of lifecycle. ' : '') .
                            ($cost_benefit > 2 ? 'High maintenance cost ratio.' : ''),
                'urgency' => $health_score < 20 ? 'High' : 'Medium',
                'cost_benefit' => $cost_benefit
            ];
        } else if ($health_score < 60 || $cost_benefit > 1.5) {
            return [
                'recommendation' => 'Monitor',
                'reason' => 'Moderate health concerns or increasing maintenance costs',
                'urgency' => 'Low',
                'cost_benefit' => $cost_benefit
            ];
        } else {
            return [
                'recommendation' => 'Keep',
                'reason' => 'Device is functioning well with good cost efficiency',
                'urgency' => 'None',
                'cost_benefit' => $cost_benefit
            ];
        }
    }

    // Update device status
    public function update_status() {
        // Sanitize inputs
        $this->id = htmlspecialchars(strip_tags($this->id));
        $this->status = htmlspecialchars(strip_tags($this->status));

        // Query to update status
        $query = "UPDATE " . $this->table_name . "
                SET status = :status
                WHERE id = :id";

        // Prepare statement
        $stmt = $this->conn->prepare($query);

        // Bind values
        $stmt->bindParam(":id", $this->id);
        $stmt->bindParam(":status", $this->status);

        return $stmt->execute();
    }
}
?>