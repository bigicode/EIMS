<?php
class Maintenance {
    // Database connection and table name
    private $conn;
    private $table_name = "maintenance";

    // Object properties
    public $id;
    public $device_id;
    public $maintenance_type;
    public $description;
    public $scheduled_date;
    public $status;
    public $performed_by;
    public $completion_date;
    public $notes;
    public $created_at;
    public $device_name;
    public $serial_number;
    public $performed_by_name;
    public $maintenance_cost;
    public $parts_replaced;
    public $parts_details;
    public $issues_found;
    public $resolution;
    public $device_health_impact;
    public $next_recommended_date;

    // Constructor with DB connection
    public function __construct($db) {
        $this->conn = $db;
    }

    // Create maintenance record
    public function create() {
        // Sanitize inputs
        $this->device_id = htmlspecialchars(strip_tags($this->device_id));
        $this->maintenance_type = htmlspecialchars(strip_tags($this->maintenance_type));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->scheduled_date = htmlspecialchars(strip_tags($this->scheduled_date));
        $this->status = htmlspecialchars(strip_tags($this->status));

        // Query to insert record
        $query = "INSERT INTO " . $this->table_name . "
                SET device_id=:device_id, maintenance_type=:maintenance_type, description=:description,
                    scheduled_date=:scheduled_date, status=:status";

        // Prepare query
        $stmt = $this->conn->prepare($query);

        // Bind values
        $stmt->bindParam(":device_id", $this->device_id);
        $stmt->bindParam(":maintenance_type", $this->maintenance_type);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":scheduled_date", $this->scheduled_date);
        $stmt->bindParam(":status", $this->status);

        // Execute query
        if($stmt->execute()) {
            // Get the ID of the inserted record
            $this->id = $this->conn->lastInsertId();
            return true;
        }

        return false;
    }

    // Read all maintenance records
    public function read() {
        // Query to select all maintenance records
        $query = "SELECT m.*, d.device_name, d.serial_number, u.full_name as performed_by_name
                FROM " . $this->table_name . " m
                LEFT JOIN devices d ON m.device_id = d.id
                LEFT JOIN users u ON m.performed_by = u.id
                ORDER BY m.scheduled_date ASC";

        // Prepare query statement
        $stmt = $this->conn->prepare($query);

        // Execute query
        $stmt->execute();

        return $stmt;
    }

    // Read single maintenance record
    public function read_single() {
        // Query to read single record
        $query = "SELECT m.*, d.device_name, d.serial_number, u.full_name as performed_by_name
                FROM " . $this->table_name . " m
                LEFT JOIN devices d ON m.device_id = d.id
                LEFT JOIN users u ON m.performed_by = u.id
                WHERE m.id = ?";

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
            $this->device_id = $row['device_id'];
            $this->device_name = $row['device_name'];
            $this->serial_number = $row['serial_number'];
            $this->maintenance_type = $row['maintenance_type'];
            $this->description = $row['description'];
            $this->scheduled_date = $row['scheduled_date'];
            $this->status = $row['status'];
            $this->performed_by = $row['performed_by'];
            $this->performed_by_name = $row['performed_by_name'];
            $this->completion_date = $row['completion_date'];
            $this->notes = $row['notes'];
            $this->created_at = $row['created_at'];
            
            // Set new fields if they exist in the row
            $this->maintenance_cost = isset($row['maintenance_cost']) ? $row['maintenance_cost'] : null;
            $this->parts_replaced = isset($row['parts_replaced']) ? $row['parts_replaced'] : 0;
            $this->parts_details = isset($row['parts_details']) ? $row['parts_details'] : null;
            $this->issues_found = isset($row['issues_found']) ? $row['issues_found'] : null;
            $this->resolution = isset($row['resolution']) ? $row['resolution'] : null;
            $this->device_health_impact = isset($row['device_health_impact']) ? $row['device_health_impact'] : 'no_change';
            $this->next_recommended_date = isset($row['next_recommended_date']) ? $row['next_recommended_date'] : null;
            
            return true;
        }

        return false;
    }

    // Update maintenance record
    public function update() {
        // Sanitize inputs
        $this->id = htmlspecialchars(strip_tags($this->id));
        $this->maintenance_type = htmlspecialchars(strip_tags($this->maintenance_type));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->scheduled_date = htmlspecialchars(strip_tags($this->scheduled_date));
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->performed_by = $this->performed_by ? htmlspecialchars(strip_tags($this->performed_by)) : null;
        $this->completion_date = $this->completion_date ? htmlspecialchars(strip_tags($this->completion_date)) : null;
        $this->notes = $this->notes ? htmlspecialchars(strip_tags($this->notes)) : null;
        
        // Sanitize new fields
        $this->maintenance_cost = isset($this->maintenance_cost) ? htmlspecialchars(strip_tags($this->maintenance_cost)) : null;
        $this->parts_replaced = isset($this->parts_replaced) ? intval($this->parts_replaced) : 0;
        $this->parts_details = isset($this->parts_details) ? htmlspecialchars(strip_tags($this->parts_details)) : null;
        $this->issues_found = isset($this->issues_found) ? htmlspecialchars(strip_tags($this->issues_found)) : null;
        $this->resolution = isset($this->resolution) ? htmlspecialchars(strip_tags($this->resolution)) : null;
        $this->device_health_impact = isset($this->device_health_impact) ? htmlspecialchars(strip_tags($this->device_health_impact)) : 'no_change';
        $this->next_recommended_date = isset($this->next_recommended_date) ? htmlspecialchars(strip_tags($this->next_recommended_date)) : null;

        // Query to update record
        $query = "UPDATE " . $this->table_name . "
                SET maintenance_type=:maintenance_type, description=:description,
                    scheduled_date=:scheduled_date, status=:status, performed_by=:performed_by,
                    completion_date=:completion_date, notes=:notes,
                    maintenance_cost=:maintenance_cost, parts_replaced=:parts_replaced,
                    parts_details=:parts_details, issues_found=:issues_found,
                    resolution=:resolution, device_health_impact=:device_health_impact,
                    next_recommended_date=:next_recommended_date
                WHERE id=:id";

        // Prepare query statement
        $stmt = $this->conn->prepare($query);

        // Bind parameters
        $stmt->bindParam(":id", $this->id);
        $stmt->bindParam(":maintenance_type", $this->maintenance_type);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":scheduled_date", $this->scheduled_date);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":performed_by", $this->performed_by);
        $stmt->bindParam(":completion_date", $this->completion_date);
        $stmt->bindParam(":notes", $this->notes);
        $stmt->bindParam(":maintenance_cost", $this->maintenance_cost);
        $stmt->bindParam(":parts_replaced", $this->parts_replaced);
        $stmt->bindParam(":parts_details", $this->parts_details);
        $stmt->bindParam(":issues_found", $this->issues_found);
        $stmt->bindParam(":resolution", $this->resolution);
        $stmt->bindParam(":device_health_impact", $this->device_health_impact);
        $stmt->bindParam(":next_recommended_date", $this->next_recommended_date);

        // Execute query
        if($stmt->execute()) {
            return true;
        }

        return false;
    }

    // Delete maintenance record
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

    // Get maintenance records by device
    public function get_maintenance_by_device() {
        // Sanitize input
        $this->device_id = htmlspecialchars(strip_tags($this->device_id));

        // Query to get maintenance records by device
        $query = "SELECT m.*, d.device_name, d.serial_number, u.full_name as performed_by_name
                FROM " . $this->table_name . " m
                LEFT JOIN devices d ON m.device_id = d.id
                LEFT JOIN users u ON m.performed_by = u.id
                WHERE m.device_id = ?
                ORDER BY m.scheduled_date ASC";

        // Prepare query statement
        $stmt = $this->conn->prepare($query);

        // Bind parameter
        $stmt->bindParam(1, $this->device_id);

        // Execute query
        $stmt->execute();

        return $stmt;
    }

    // Get upcoming maintenance
    public function get_upcoming_maintenance($days = 30) {
        // Calculate date threshold
        $today = date('Y-m-d');
        $threshold_date = date('Y-m-d', strtotime("+{$days} days"));

        // Query to get upcoming maintenance
        $query = "SELECT m.*, d.device_name, d.serial_number, u.full_name as performed_by_name
                FROM " . $this->table_name . " m
                LEFT JOIN devices d ON m.device_id = d.id
                LEFT JOIN users u ON m.performed_by = u.id
                WHERE m.scheduled_date BETWEEN ? AND ?
                    AND m.status = 'scheduled'
                ORDER BY m.scheduled_date ASC";

        // Prepare query statement
        $stmt = $this->conn->prepare($query);

        // Bind parameters
        $stmt->bindParam(1, $today);
        $stmt->bindParam(2, $threshold_date);

        // Execute query
        $stmt->execute();

        return $stmt;
    }

    // Get overdue maintenance
    public function get_overdue_maintenance() {
        // Get current date
        $today = date('Y-m-d');

        // Query to get overdue maintenance
        $query = "SELECT m.*, d.device_name, d.serial_number, u.full_name as performed_by_name
                FROM " . $this->table_name . " m
                LEFT JOIN devices d ON m.device_id = d.id
                LEFT JOIN users u ON m.performed_by = u.id
                WHERE m.scheduled_date < ?
                    AND m.status = 'scheduled'
                ORDER BY m.scheduled_date ASC";

        // Prepare query statement
        $stmt = $this->conn->prepare($query);

        // Bind parameter
        $stmt->bindParam(1, $today);

        // Execute query
        $stmt->execute();

        return $stmt;
    }

    // Count maintenance by status
    public function count_maintenance_by_status() {
        // Query to count maintenance by status
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

    public function count_by_status($status) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE status = :status";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (int)$row['total'] : 0;
    }

    public function get_by_device($device_id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE device_id = :device_id ORDER BY scheduled_date DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':device_id', $device_id);
        $stmt->execute();
        return $stmt;
    }
}
?>