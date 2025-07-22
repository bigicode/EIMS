<?php
require_once __DIR__ . '/../includes/Security.php';

class User {
    // Database connection and table name
    private $conn;
    private $table_name = "users";

    // Object properties
    public $id;
    public $username;
    public $password;
    public $email;
    public $full_name;
    public $department;
    public $role;
    public $created_at;
    public $deleted_at;

    // Constructor with DB connection
    public function __construct($db) {
        $this->conn = $db;
    }

    // Create user
    public function create() {
        try {
            // Validate password strength
            if (!Security::validatePassword($this->password)) {
                throw new Exception("Password must be at least 8 characters long and contain uppercase, lowercase, number and special character");
            }

            // Check if username exists
            if ($this->username_exists()) {
                throw new Exception("Username already exists");
            }

            // Check if email exists
            if ($this->email_exists()) {
                throw new Exception("Email already exists");
            }

            // Sanitize inputs
            $this->sanitizeInputs();

            // Hash the password
            $this->password = Security::hashPassword($this->password);

            // Query to insert record
            $query = "INSERT INTO " . $this->table_name . "
                    SET username=:username, password=:password, email=:email, 
                        full_name=:full_name, department=:department, role=:role,
                        created_at=CURRENT_TIMESTAMP";

            // Prepare query
            $stmt = $this->conn->prepare($query);

            // Bind values
            $this->bindValues($stmt);

            // Execute query
            if($stmt->execute()) {
                $this->id = $this->conn->lastInsertId();
                return true;
            }

            return false;
        } catch (Exception $e) {
            throw $e;
        }
    }

    // Login user
    public function login($password) {
        try {
            // Sanitize username
            $this->username = Security::sanitizeOutput($this->username);

            // Query to check if username exists
            $query = "SELECT id, username, password, email, full_name, department, role 
                    FROM " . $this->table_name . " 
                    WHERE username = ?";

            // Prepare query statement
            $stmt = $this->conn->prepare($query);

            // Bind parameter
            $stmt->bindParam(1, $this->username);

            // Execute query
            $stmt->execute();

            // Check if user exists
            if($stmt->rowCount() > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Verify password
                if(Security::verifyPassword($password, $row['password'])) {
                    // Set properties
                    $this->id = $row['id'];
                    $this->email = $row['email'];
                    $this->full_name = $row['full_name'];
                    $this->department = $row['department'];
                    $this->role = $row['role'];
                    
                    return true;
                }
            }

            return false;
        } catch (Exception $e) {
            throw $e;
        }
    }

    // Read all users
    public function read() {
        try {
            $query = "SELECT id, username, email, full_name, department, role, created_at, deleted_at 
                    FROM " . $this->table_name . " 
                    WHERE deleted_at IS NULL
                    ORDER BY created_at DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt;
        } catch (Exception $e) {
            throw $e;
        }
    }

    // Read single user
    public function read_single() {
        try {
            $query = "SELECT id, username, email, full_name, department, role, created_at 
                    FROM " . $this->table_name . " 
                    WHERE id = ?";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $this->id);
            $stmt->execute();

            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if($row) {
                $this->username = $row['username'];
                $this->email = $row['email'];
                $this->full_name = $row['full_name'];
                $this->department = $row['department'];
                $this->role = $row['role'];
                $this->created_at = $row['created_at'];
                return true;
            }

            return false;
        } catch (Exception $e) {
            throw $e;
        }
    }

    // Update user
    public function update() {
        try {
            // Sanitize inputs
            $this->sanitizeInputs();

            $query = "UPDATE " . $this->table_name . "
                    SET email=:email, full_name=:full_name, department=:department
                    WHERE id=:id";

            $stmt = $this->conn->prepare($query);

            // Bind parameters
            $stmt->bindParam(":id", $this->id);
            $stmt->bindParam(":email", $this->email);
            $stmt->bindParam(":full_name", $this->full_name);
            $stmt->bindParam(":department", $this->department);

            return $stmt->execute();
        } catch (Exception $e) {
            throw $e;
        }
    }

    // Update password
    public function update_password() {
        try {
            // Validate password strength
            if (!Security::validatePassword($this->password)) {
                throw new Exception("Password must be at least 8 characters long and contain uppercase, lowercase, number and special character");
            }

            // Hash the password
            $this->password = Security::hashPassword($this->password);

            $query = "UPDATE " . $this->table_name . "
                    SET password=:password
                    WHERE id=:id";

            $stmt = $this->conn->prepare($query);

            $stmt->bindParam(":id", $this->id);
            $stmt->bindParam(":password", $this->password);

            return $stmt->execute();
        } catch (Exception $e) {
            throw $e;
        }
    }

    // Soft delete user
    public function delete() {
        try {
            $query = "UPDATE " . $this->table_name . " SET deleted_at = NOW() WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $this->id);
            return $stmt->execute();
        } catch (Exception $e) {
            throw $e;
        }
    }

    // Restore user (undo soft delete)
    public function restore() {
        try {
            $query = "UPDATE " . $this->table_name . " SET deleted_at = NULL WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $this->id);
            return $stmt->execute();
        } catch (Exception $e) {
            throw $e;
        }
    }

    // Check if username exists
    public function username_exists() {
        try {
            $query = "SELECT id FROM " . $this->table_name . " WHERE username = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $this->username);
            $stmt->execute();
            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            throw $e;
        }
    }

    // Check if email exists
    public function email_exists() {
        try {
            $query = "SELECT id FROM " . $this->table_name . " WHERE email = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $this->email);
            $stmt->execute();
            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            throw $e;
        }
    }

    // Get users by department
    public function get_users_by_department($department) {
        try {
            $query = "SELECT id, username, email, full_name, department, role, created_at 
                    FROM " . $this->table_name . " 
                    WHERE department = ?
                    ORDER BY full_name ASC";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $department);
            $stmt->execute();

            return $stmt;
        } catch (Exception $e) {
            throw $e;
        }
    }

    // Count all users
    public function count_all() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (int)$row['total'] : 0;
    }

    // Private helper methods
    private function sanitizeInputs() {
        $this->username = Security::sanitizeOutput($this->username);
        $this->email = Security::sanitizeOutput($this->email);
        $this->full_name = Security::sanitizeOutput($this->full_name);
        $this->department = Security::sanitizeOutput($this->department);
        $this->role = Security::sanitizeOutput($this->role);
    }

    private function bindValues($stmt) {
        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":password", $this->password);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":full_name", $this->full_name);
        $stmt->bindParam(":department", $this->department);
        $stmt->bindParam(":role", $this->role);
    }

    // Permanently delete a user
    public function permanent_delete() {
        try {
            $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $this->id);
            return $stmt->execute();
        } catch (Exception $e) {
            throw $e;
        }
    }

    // Auto cleanup - permanently delete users who were soft-deleted more than 7 days ago
    public function cleanup_old_deleted_users() {
        try {
            $query = "DELETE FROM " . $this->table_name . " 
                     WHERE deleted_at IS NOT NULL 
                     AND deleted_at < DATE_SUB(NOW(), INTERVAL 7 DAY)";
            $stmt = $this->conn->prepare($query);
            $result = $stmt->execute();
            
            // Return number of rows affected
            return $stmt->rowCount();
        } catch (Exception $e) {
            throw $e;
        }
    }
}
?>