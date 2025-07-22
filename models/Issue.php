<?php
class Issue {
    // Database connection and table name
    private $conn;
    private $table_name = "issues";

    // Object properties
    public $id;
    public $device_id;
    public $reported_by;
    public $issue_title;
    public $issue_description;
    public $priority;
    public $status;
    public $reported_at;
    public $resolved_at;
    public $resolution_notes;
    public $device_name;
    public $serial_number;
    public $reported_by_name;
    public $reporter_name;
    public $reporter_department;

    // Constructor with DB connection
    public function __construct($db) {
        $this->conn = $db;
    }

    // Create issue
    public function create() {
        // Sanitize inputs
        $this->device_id = htmlspecialchars(strip_tags($this->device_id));
        $this->reported_by = htmlspecialchars(strip_tags($this->reported_by));
        $this->issue_title = htmlspecialchars(strip_tags($this->issue_title));
        $this->issue_description = htmlspecialchars(strip_tags($this->issue_description));
        $this->priority = htmlspecialchars(strip_tags($this->priority));
        $this->status = htmlspecialchars(strip_tags($this->status));

        // Query to insert record
        $query = "INSERT INTO " . $this->table_name . "
                SET device_id=:device_id, reported_by=:reported_by, issue_title=:issue_title,
                    issue_description=:issue_description, priority=:priority, status=:status";

        // Prepare query
        $stmt = $this->conn->prepare($query);

        // Bind values
        $stmt->bindParam(":device_id", $this->device_id);
        $stmt->bindParam(":reported_by", $this->reported_by);
        $stmt->bindParam(":issue_title", $this->issue_title);
        $stmt->bindParam(":issue_description", $this->issue_description);
        $stmt->bindParam(":priority", $this->priority);
        $stmt->bindParam(":status", $this->status);

        // Execute query
        if($stmt->execute()) {
            // Get the ID of the inserted record
            $this->id = $this->conn->lastInsertId();
            return true;
        }

        return false;
    }

    // Read all issues
    public function read() {
        try {
            $query = "SELECT issues.*, users.full_name AS reporter_name, devices.device_name
                      FROM " . $this->table_name . "
                      LEFT JOIN users ON issues.reported_by = users.id
                      LEFT JOIN devices ON issues.device_id = devices.id
                      ORDER BY issues.reported_at DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt;
        } catch (Exception $e) {
            throw $e;
        }
    }

    // Read single issue
    public function read_single() {
        // Query to read single record
        $query = "SELECT i.*, d.device_name, d.serial_number, u.full_name as reported_by_name
                FROM " . $this->table_name . " i
                LEFT JOIN devices d ON i.device_id = d.id
                LEFT JOIN users u ON i.reported_by = u.id
                WHERE i.id = ?";

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
            $this->reported_by = $row['reported_by'];
            $this->reported_by_name = $row['reported_by_name'];
            $this->issue_title = $row['issue_title'];
            $this->issue_description = $row['issue_description'];
            $this->priority = $row['priority'];
            $this->status = $row['status'];
            $this->reported_at = $row['reported_at'];
            $this->resolved_at = $row['resolved_at'];
            $this->resolution_notes = $row['resolution_notes'];
            return true;
        }

        return false;
    }

    // Update issue
    public function update() {
        // Sanitize inputs
        $this->id = htmlspecialchars(strip_tags($this->id));
        $this->issue_title = htmlspecialchars(strip_tags($this->issue_title));
        $this->issue_description = htmlspecialchars(strip_tags($this->issue_description));
        $this->priority = htmlspecialchars(strip_tags($this->priority));
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->resolution_notes = $this->resolution_notes ? htmlspecialchars(strip_tags($this->resolution_notes)) : null;

        // Set resolved_at if status is resolved or closed
        $resolved_at_sql = "";
        if($this->status == 'resolved' || $this->status == 'closed') {
            $resolved_at_sql = ", resolved_at = NOW()";
        }

        // Query to update record
        $query = "UPDATE " . $this->table_name . "
                SET issue_title=:issue_title, issue_description=:issue_description,
                    priority=:priority, status=:status, resolution_notes=:resolution_notes
                    {$resolved_at_sql}
                WHERE id=:id";

        // Prepare query statement
        $stmt = $this->conn->prepare($query);

        // Bind parameters
        $stmt->bindParam(":id", $this->id);
        $stmt->bindParam(":issue_title", $this->issue_title);
        $stmt->bindParam(":issue_description", $this->issue_description);
        $stmt->bindParam(":priority", $this->priority);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":resolution_notes", $this->resolution_notes);

        // Execute query
        if($stmt->execute()) {
            return true;
        }

        return false;
    }

    // Delete issue
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

    // Get issues by device
    public function get_issues_by_device() {
        // Sanitize input
        $this->device_id = htmlspecialchars(strip_tags($this->device_id));

        // Query to get issues by device
        $query = "SELECT i.*, d.device_name, d.serial_number, u.full_name as reported_by_name
                FROM " . $this->table_name . " i
                LEFT JOIN devices d ON i.device_id = d.id
                LEFT JOIN users u ON i.reported_by = u.id
                WHERE i.device_id = ?
                ORDER BY i.reported_at DESC";

        // Prepare query statement
        $stmt = $this->conn->prepare($query);

        // Bind parameter
        $stmt->bindParam(1, $this->device_id);

        // Execute query
        $stmt->execute();

        return $stmt;
    }

    // Get issues by user
    public function get_issues_by_user() {
        // Sanitize input
        $this->reported_by = htmlspecialchars(strip_tags($this->reported_by));

        // Query to get issues by user
        $query = "SELECT i.*, d.device_name, d.serial_number, u.full_name as reported_by_name
                FROM " . $this->table_name . " i
                LEFT JOIN devices d ON i.device_id = d.id
                LEFT JOIN users u ON i.reported_by = u.id
                WHERE i.reported_by = ?
                ORDER BY i.reported_at DESC";

        // Prepare query statement
        $stmt = $this->conn->prepare($query);

        // Bind parameter
        $stmt->bindParam(1, $this->reported_by);

        // Execute query
        $stmt->execute();

        return $stmt;
    }

    // Get issues by status
    public function get_issues_by_status() {
        // Sanitize input
        $this->status = htmlspecialchars(strip_tags($this->status));

        // Query to get issues by status
        $query = "SELECT i.*, d.device_name, d.serial_number, u.full_name as reported_by_name
                FROM " . $this->table_name . " i
                LEFT JOIN devices d ON i.device_id = d.id
                LEFT JOIN users u ON i.reported_by = u.id
                WHERE i.status = ?
                ORDER BY i.reported_at DESC";

        // Prepare query statement
        $stmt = $this->conn->prepare($query);

        // Bind parameter
        $stmt->bindParam(1, $this->status);

        // Execute query
        $stmt->execute();

        return $stmt;
    }

    // Count issues by status
    public function count_issues_by_status() {
        // Query to count issues by status
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

    // Count issues by priority
    public function count_issues_by_priority() {
        // Query to count issues by priority
        $query = "SELECT priority, COUNT(*) as count
                FROM " . $this->table_name . "
                GROUP BY priority
                ORDER BY count DESC";

        // Prepare query statement
        $stmt = $this->conn->prepare($query);

        // Execute query
        $stmt->execute();

        return $stmt;
    }

    // Count issues by device type
    public function count_issues_by_device_type() {
        // Query to count issues by device type
        $query = "SELECT d.device_type, COUNT(*) as count
                FROM " . $this->table_name . " i
                LEFT JOIN devices d ON i.device_id = d.id
                GROUP BY d.device_type
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

    public function count_by_priority($priority) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE priority = :priority";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':priority', $priority);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (int)$row['total'] : 0;
    }

    public function read_recent($limit = 5) {
        $query = "SELECT issues.*, devices.device_name 
                  FROM " . $this->table_name . " 
                  LEFT JOIN devices ON issues.device_id = devices.id 
                  ORDER BY issues.reported_at DESC 
                  LIMIT :limit";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt;
    }

    public function get_by_priority($priority) {
        $query = "SELECT issues.*, devices.device_name FROM " . $this->table_name . " LEFT JOIN devices ON issues.device_id = devices.id WHERE issues.priority = :priority ORDER BY issues.reported_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':priority', $priority);
        $stmt->execute();
        return $stmt;
    }

    public function get_by_device($device_id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE device_id = :device_id ORDER BY reported_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':device_id', $device_id);
        $stmt->execute();
        return $stmt;
    }

    public function get_by_reporter($user_id) {
        $query = "SELECT i.*, u.full_name as reporter_name, d.device_name FROM " . $this->table_name . " i LEFT JOIN users u ON i.reported_by = u.id LEFT JOIN devices d ON i.device_id = d.id WHERE i.reported_by = :user_id ORDER BY i.reported_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        return $stmt;
    }

    public function count_by_reporter_and_status($user_id, $status) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE reported_by = :user_id AND status = :status";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':status', $status);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (int)$row['total'] : 0;
    }

    // Get count of open issues (for admin notification)
    public function count_open() {
        $query = "SELECT COUNT(*) as open_count FROM {$this->table_name} WHERE status = 'open'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (int)$row['open_count'] : 0;
    }

    // Get latest open issues (for admin notification)
    public function get_latest_open($limit = 5) {
        $query = "SELECT i.*, u.full_name as reporter_name, d.device_name FROM {$this->table_name} i LEFT JOIN users u ON i.reported_by = u.id LEFT JOIN devices d ON i.device_id = d.id WHERE i.status = 'open' ORDER BY i.reported_at DESC LIMIT :limit";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt;
    }
}
?>