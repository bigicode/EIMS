<?php
class Report {
    // Database connection
    private $conn;

    // Constructor with DB connection
    public function __construct($db) {
        $this->conn = $db;
    }

    // Get device inventory report
    public function get_device_inventory() {
        // Query to get device inventory
        $query = "SELECT d.device_type, COUNT(*) as count,
                    SUM(CASE WHEN d.status = 'active' THEN 1 ELSE 0 END) as active,
                    SUM(CASE WHEN d.status = 'maintenance' THEN 1 ELSE 0 END) as maintenance,
                    SUM(CASE WHEN d.status = 'repair' THEN 1 ELSE 0 END) as repair,
                    SUM(CASE WHEN d.status = 'disposed' THEN 1 ELSE 0 END) as disposed
                FROM devices d
                GROUP BY d.device_type
                ORDER BY count DESC";

        // Prepare query statement
        $stmt = $this->conn->prepare($query);

        // Execute query
        $stmt->execute();

        return $stmt;
    }

    // Get device allocation by department
    public function get_device_allocation_by_department() {
        // Query to get device allocation by department
        $query = "SELECT d.department, COUNT(*) as count
                FROM devices d
                GROUP BY d.department
                ORDER BY count DESC";

        // Prepare query statement
        $stmt = $this->conn->prepare($query);

        // Execute query
        $stmt->execute();

        return $stmt;
    }

    // Get maintenance history report
    public function get_maintenance_history($start_date, $end_date) {
        // Sanitize inputs
        $start_date = htmlspecialchars(strip_tags($start_date));
        $end_date = htmlspecialchars(strip_tags($end_date));

        // Query to get maintenance history
        $query = "SELECT m.*, d.device_name, d.serial_number, d.device_type,
                    u.full_name as performed_by_name
                FROM maintenance m
                LEFT JOIN devices d ON m.device_id = d.id
                LEFT JOIN users u ON m.performed_by = u.id
                WHERE (m.scheduled_date BETWEEN ? AND ?)
                    OR (m.completion_date BETWEEN ? AND ?)
                ORDER BY m.scheduled_date ASC";

        // Prepare query statement
        $stmt = $this->conn->prepare($query);

        // Bind parameters
        $stmt->bindParam(1, $start_date);
        $stmt->bindParam(2, $end_date);
        $stmt->bindParam(3, $start_date);
        $stmt->bindParam(4, $end_date);

        // Execute query
        $stmt->execute();

        return $stmt;
    }

    // Get issue statistics report
    public function get_issue_statistics($start_date, $end_date) {
        // Sanitize inputs
        $start_date = htmlspecialchars(strip_tags($start_date));
        $end_date = htmlspecialchars(strip_tags($end_date));

        // Query to get issue statistics
        $query = "SELECT d.device_type,
                    COUNT(*) as total_issues,
                    SUM(CASE WHEN i.priority = 'low' THEN 1 ELSE 0 END) as low_priority,
                    SUM(CASE WHEN i.priority = 'medium' THEN 1 ELSE 0 END) as medium_priority,
                    SUM(CASE WHEN i.priority = 'high' THEN 1 ELSE 0 END) as high_priority,
                    SUM(CASE WHEN i.priority = 'critical' THEN 1 ELSE 0 END) as critical_priority,
                    SUM(CASE WHEN i.status = 'open' THEN 1 ELSE 0 END) as open_issues,
                    SUM(CASE WHEN i.status = 'in_progress' THEN 1 ELSE 0 END) as in_progress_issues,
                    SUM(CASE WHEN i.status = 'resolved' THEN 1 ELSE 0 END) as resolved_issues,
                    SUM(CASE WHEN i.status = 'closed' THEN 1 ELSE 0 END) as closed_issues,
                    AVG(CASE WHEN i.resolved_at IS NOT NULL THEN DATEDIFF(i.resolved_at, i.reported_at) ELSE NULL END) as avg_resolution_time
                FROM issues i
                LEFT JOIN devices d ON i.device_id = d.id
                WHERE i.reported_at BETWEEN ? AND ?
                GROUP BY d.device_type
                ORDER BY total_issues DESC";

        // Prepare query statement
        $stmt = $this->conn->prepare($query);

        // Bind parameters
        $stmt->bindParam(1, $start_date);
        $stmt->bindParam(2, $end_date);

        // Execute query
        $stmt->execute();

        return $stmt;
    }

    // Get warranty expiry report
    public function get_warranty_expiry_report($days = 90) {
        // Calculate date threshold
        $today = date('Y-m-d');
        $threshold_date = date('Y-m-d', strtotime("+{$days} days"));

        // Query to get warranty expiry report
        $query = "SELECT d.*, u.full_name as assigned_to_name,
                    DATEDIFF(d.warranty_end, CURDATE()) as days_until_expiry
                FROM devices d
                LEFT JOIN users u ON d.assigned_to = u.id
                WHERE d.warranty_end BETWEEN ? AND ?
                    AND d.status != 'disposed'
                ORDER BY d.warranty_end ASC";

        // Prepare query statement
        $stmt = $this->conn->prepare($query);

        // Bind parameters
        $stmt->bindParam(1, $today);
        $stmt->bindParam(2, $threshold_date);

        // Execute query
        $stmt->execute();

        return $stmt;
    }

    // Get device lifecycle report
    public function get_device_lifecycle_report() {
        // Query to get device lifecycle report
        $query = "SELECT d.*, u.full_name as assigned_to_name,
                    DATEDIFF(CURDATE(), d.purchase_date) as days_since_purchase,
                    DATEDIFF(d.warranty_end, CURDATE()) as days_until_warranty_expiry
                FROM devices d
                LEFT JOIN users u ON d.assigned_to = u.id
                ORDER BY days_since_purchase DESC";

        // Prepare query statement
        $stmt = $this->conn->prepare($query);

        // Execute query
        $stmt->execute();

        return $stmt;
    }

    public function generate_inventory_report($filters = []) {
        $query = "SELECT * FROM devices";
        // Add filter logic here if needed
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
}
?>