<?php
class Message {
    private $conn;
    private $table_name = "messages";

    public $id;
    public $sender_id;
    public $receiver_id;
    public $message;
    public $is_read;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Send a message
    public function send() {
        $query = "INSERT INTO {$this->table_name} (sender_id, receiver_id, message, is_read, created_at) VALUES (:sender_id, :receiver_id, :message, 0, NOW())";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":sender_id", $this->sender_id);
        $stmt->bindParam(":receiver_id", $this->receiver_id);
        $stmt->bindParam(":message", $this->message);
        return $stmt->execute();
    }

    // Get inbox messages for a user
    public function get_inbox($user_id) {
        $query = "SELECT m.*, u.full_name as sender_name FROM {$this->table_name} m JOIN users u ON m.sender_id = u.id WHERE m.receiver_id = :user_id ORDER BY m.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
        return $stmt;
    }

    // Get unread messages for a user
    public function get_unread($user_id) {
        $query = "SELECT m.*, u.full_name as sender_name FROM {$this->table_name} m JOIN users u ON m.sender_id = u.id WHERE m.receiver_id = :user_id AND m.is_read = 0 ORDER BY m.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
        return $stmt;
    }

    // Mark a message as read
    public function mark_as_read($message_id) {
        $query = "UPDATE {$this->table_name} SET is_read = 1 WHERE id = :message_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":message_id", $message_id);
        return $stmt->execute();
    }

    // Get count of unread messages for a user
    public function count_unread($user_id) {
        $query = "SELECT COUNT(*) as unread_count FROM {$this->table_name} WHERE receiver_id = :user_id AND is_read = 0";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (int)$row['unread_count'] : 0;
    }
}
