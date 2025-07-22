<?php
require_once '../config/database.php';
require_once '../models/User.php';

class UserController {
    private $db;
    private $user;

    public function __construct() {
        // Initialize database connection
        $database = new Database();
        $this->db = $database->getConnection();
        
        // Initialize user model
        $this->user = new User($this->db);
    }

    // Handle user registration
    public function register() {
        // Check if form is submitted
        if($_SERVER["REQUEST_METHOD"] == "POST") {
            // Set user properties
            $this->user->username = $_POST['username'];
            $this->user->password = $_POST['password'];
            $this->user->email = $_POST['email'];
            $this->user->full_name = $_POST['full_name'];
            $this->user->department = $_POST['department'];
            $this->user->role = isset($_POST['role']) ? $_POST['role'] : 'user';

            // Validate input
            if(empty($this->user->username) || empty($this->user->password) || empty($this->user->email) || 
               empty($this->user->full_name) || empty($this->user->department)) {
                set_message("Please fill all required fields.", "danger");
                return false;
            }

            // Check if username already exists
            if($this->user->username_exists()) {
                set_message("Username already exists. Please choose another.", "danger");
                return false;
            }

            // Check if email already exists
            if($this->user->email_exists()) {
                set_message("Email already exists. Please use another email.", "danger");
                return false;
            }

            // Create user
            if($this->user->create()) {
                set_message("User registered successfully.", "success");
                return true;
            } else {
                set_message("Unable to register user.", "danger");
                return false;
            }
        }
        return false;
    }

    // Handle user login
    public function login() {
        // Check if form is submitted
        if($_SERVER["REQUEST_METHOD"] == "POST") {
            // Set user properties
            $this->user->username = $_POST['username'];
            $password = $_POST['password'];

            // Validate input
            if(empty($this->user->username) || empty($password)) {
                set_message("Please enter both username and password.", "danger");
                return false;
            }

            // Attempt to login
            if($this->user->login($password)) {
                // Set session variables
                $_SESSION['user_id'] = $this->user->id;
                $_SESSION['username'] = $this->user->username;
                $_SESSION['full_name'] = $this->user->full_name;
                $_SESSION['email'] = $this->user->email;
                $_SESSION['department'] = $this->user->department;
                $_SESSION['user_role'] = $this->user->role;

                set_message("Welcome back, {$this->user->full_name}!", "success");
                return true;
            } else {
                set_message("Invalid username or password.", "danger");
                return false;
            }
        }
        return false;
    }

    // Get all users
    public function getAllUsers() {
        return $this->user->read();
    }

    // Get user by ID
    public function getUserById($id) {
        $this->user->id = $id;
        if($this->user->read_single()) {
            return $this->user;
        }
        return null;
    }

    // Update user
    public function updateUser() {
        // Check if form is submitted
        if($_SERVER["REQUEST_METHOD"] == "POST") {
            // Set user properties
            $this->user->id = $_POST['id'];
            $this->user->email = $_POST['email'];
            $this->user->full_name = $_POST['full_name'];
            $this->user->department = $_POST['department'];

            // Validate input
            if(empty($this->user->email) || empty($this->user->full_name) || empty($this->user->department)) {
                set_message("Please fill all required fields.", "danger");
                return false;
            }

            // Update user
            if($this->user->update()) {
                set_message("User updated successfully.", "success");
                return true;
            } else {
                set_message("Unable to update user.", "danger");
                return false;
            }
        }
        return false;
    }

    // Update user password
    public function updatePassword() {
        // Check if form is submitted
        if($_SERVER["REQUEST_METHOD"] == "POST") {
            // Set user properties
            $this->user->id = $_POST['id'];
            $current_password = $_POST['current_password'];
            $new_password = $_POST['new_password'];
            $confirm_password = $_POST['confirm_password'];

            // Validate input
            if(empty($current_password) || empty($new_password) || empty($confirm_password)) {
                set_message("Please fill all password fields.", "danger");
                return false;
            }

            // Check if new password matches confirmation
            if($new_password !== $confirm_password) {
                set_message("New password and confirmation do not match.", "danger");
                return false;
            }

            // Verify current password
            if(!$this->user->verify_password($current_password)) {
                set_message("Current password is incorrect.", "danger");
                return false;
            }

            // Update password
            if($this->user->update_password($new_password)) {
                set_message("Password updated successfully.", "success");
                return true;
            } else {
                set_message("Unable to update password.", "danger");
                return false;
            }
        }
        return false;
    }

    // Delete user
    public function deleteUser($id) {
        $this->user->id = $id;
        if($this->user->delete()) {
            set_message("User deleted successfully.", "success");
            return true;
        } else {
            set_message("Unable to delete user.", "danger");
            return false;
        }
    }
}
?>