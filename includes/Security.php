<?php
/**
 * ============================================
 * SECURITY CLASS
 * ============================================
 * CSRF protection, XSS prevention, password validation
 * UPDATED: Added output buffer handling for headers already sent error
 * ============================================
 */

class Security {
    
    /**
     * Generate CSRF token
     * @return string Token
     */
    public static function generateCSRFToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Validate CSRF token
     * @param string $token Token to validate
     * @return bool Valid or not
     */
    public static function validateCSRFToken($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Clean output to prevent XSS
     * @param string $data Data to clean
     * @return string Cleaned data
     */
    public static function clean($data) {
        return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Sanitize string input
     * @param string $data Input data
     * @return string Sanitized string
     */
    public static function sanitizeString($data) {
        return trim(strip_tags($data));
    }
    
    /**
     * Sanitize email
     * @param string $email Email address
     * @return string|false Sanitized email or false
     */
    public static function sanitizeEmail($email) {
        return filter_var(trim($email), FILTER_SANITIZE_EMAIL);
    }
    
    /**
     * Sanitize phone number (SA format)
     * @param string $phone Phone number
     * @return string Sanitized phone
     */
    public static function sanitizePhone($phone) {
        return preg_replace('/[^0-9+]/', '', $phone);
    }
    
    /**
     * Validate email format
     * @param string $email Email to validate
     * @return bool Valid or not
     */
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Validate phone number (SA format: +27xxxxxxxxx)
     * @param string $phone Phone to validate
     * @return bool Valid or not
     */
    public static function validatePhone($phone) {
        return preg_match('/^\+27[0-9]{9}$/', $phone);
    }
    
    /**
     * Validate password strength
     * Must have: 8+ chars, 1 uppercase, 1 lowercase, 1 number, 1 special char
     * @param string $password Password to validate
     * @return array ['valid' => bool, 'errors' => array]
     */
    public static function validatePassword($password) {
        $errors = [];
        
        if (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters';
        }
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Password must contain at least one uppercase letter';
        }
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'Password must contain at least one lowercase letter';
        }
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password must contain at least one number';
        }
        if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
            $errors[] = 'Password must contain at least one special character';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    /**
     * Hash password using bcrypt
     * @param string $password Plain password
     * @return string Hashed password
     */
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_ALGO, ['cost' => PASSWORD_COST]);
    }
    
    /**
     * Verify password against hash
     * @param string $password Plain password
     * @param string $hash Hashed password
     * @return bool Match or not
     */
    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
    
    /**
     * Generate random token
     * @param int $length Token length
     * @return string Random token
     */
    public static function generateToken($length = 32) {
        return bin2hex(random_bytes($length));
    }
    
    /**
     * Check if user is logged in
     * @return bool Logged in status
     */
    public static function isLoggedIn() {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }
    
    /**
     * Get current user ID
     * @return int|null User ID or null
     */
    public static function getUserId() {
        return $_SESSION['user_id'] ?? null;
    }
    
    /**
     * Get current user type
     * @return string|null User type or null
     */
    public static function getUserType() {
        return $_SESSION['user_type'] ?? null;
    }
    
    /**
     * Check if user has permission
     * @param array $allowedTypes Allowed user types
     * @return bool Has permission or not
     */
    public static function hasPermission($allowedTypes) {
        if (!self::isLoggedIn()) {
            return false;
        }
        $userType = self::getUserType();
        return in_array($userType, $allowedTypes);
    }
    
    /**
     * Require login - redirect if not logged in
     * UPDATED: Added output buffer clearing and JavaScript fallback
     * @param string $redirectUrl URL to redirect to
     */
    public static function requireLogin($redirectUrl = '/auth/login.php') {
        if (!self::isLoggedIn()) {
            // Clear any output buffers to prevent headers already sent error
            while (ob_get_level()) {
                ob_end_clean();
            }
            
            // Try PHP redirect first
            if (!headers_sent()) {
                header('Location: ' . APP_URL . $redirectUrl);
                exit;
            } else {
                // Fallback to JavaScript redirect if headers already sent
                echo '<script type="text/javascript">';
                echo 'window.location.href = "' . APP_URL . $redirectUrl . '";';
                echo '</script>';
                echo '<noscript>';
                echo '<meta http-equiv="refresh" content="0;url=' . APP_URL . $redirectUrl . '">';
                echo '</noscript>';
                exit;
            }
        }
    }
    
    /**
     * Require admin - redirect if not admin
     * UPDATED: Added output buffer clearing and JavaScript fallback
     */
    public static function requireAdmin() {
        self::requireLogin();
        if (self::getUserType() !== 'admin') {
            // Clear any output buffers
            while (ob_get_level()) {
                ob_end_clean();
            }
            
            if (!headers_sent()) {
                header('Location: ' . APP_URL . '/index.php');
                exit;
            } else {
                echo '<script type="text/javascript">';
                echo 'window.location.href = "' . APP_URL . '/index.php";';
                echo '</script>';
                echo '<noscript>';
                echo '<meta http-equiv="refresh" content="0;url=' . APP_URL . '/index.php">';
                echo '</noscript>';
                exit;
            }
        }
    }
    
    /**
     * Require moderator or admin
     * UPDATED: Added output buffer clearing and JavaScript fallback
     */
    public static function requireModerator() {
        self::requireLogin();
        if (!in_array(self::getUserType(), ['moderator', 'admin'])) {
            // Clear any output buffers
            while (ob_get_level()) {
                ob_end_clean();
            }
            
            if (!headers_sent()) {
                header('Location: ' . APP_URL . '/index.php');
                exit;
            } else {
                echo '<script type="text/javascript">';
                echo 'window.location.href = "' . APP_URL . '/index.php";';
                echo '</script>';
                echo '<noscript>';
                echo '<meta http-equiv="refresh" content="0;url=' . APP_URL . '/index.php">';
                echo '</noscript>';
                exit;
            }
        }
    }
}
?>
