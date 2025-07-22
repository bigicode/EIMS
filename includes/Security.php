<?php
class Security {
    public static function generateCSRFToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function validateCSRFToken($token) {
        if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
            throw new Exception('CSRF token validation failed');
        }
        return true;
    }

    public static function regenerateSession() {
        if (!empty($_SESSION['last_regeneration'])) {
            $interval = 60 * 30; // 30 minutes
            if (time() - $_SESSION['last_regeneration'] >= $interval) {
                session_regenerate_id(true);
                $_SESSION['last_regeneration'] = time();
            }
        } else {
            $_SESSION['last_regeneration'] = time();
        }
    }

    public static function sanitizeOutput($data) {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = self::sanitizeOutput($value);
            }
        } else {
            $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
        }
        return $data;
    }

    public static function validatePassword($password) {
        // Minimum 8 characters, at least one uppercase letter, one lowercase letter, one number and one special character
        $pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/';
        return preg_match($pattern, $password);
    }

    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => PASSWORD_HASH_COST]);
    }

    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
}
?> 