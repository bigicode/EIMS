<?php
class Log {
    private $conn;
    public $id;
    public $timestamp;
    public $user_id;
    public $action;
    public $description;
    public $ip_address;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Add a new log entry
    public function add($user_id, $action, $description, $ip_address = null) {
        $query = "INSERT INTO system_logs (user_id, action, description, ip_address) VALUES (:user_id, :action, :description, :ip_address)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':action', $action);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':ip_address', $ip_address);
        return $stmt->execute();
    }

    // Fetch logs (optionally with limit, offset, and search)
    public function fetch($limit = 100, $offset = 0, $search = null) {
        $sql = "SELECT l.*, u.username, u.full_name FROM system_logs l LEFT JOIN users u ON l.user_id = u.id";
        $bindSearch = false;
        if ($search) {
            $sql .= " WHERE l.action LIKE :search OR l.description LIKE :search";
            $bindSearch = true;
        }
        $sql .= " ORDER BY l.timestamp DESC LIMIT " . (int)$offset . ", " . (int)$limit;

        $stmt = $this->conn->prepare($sql);
        if ($bindSearch) {
            $searchTerm = "%$search%";
            $stmt->bindValue(':search', $searchTerm);
        }
        $stmt->execute();
        return $stmt;
    }
} 