<?php
/**
 * ============================================
 * SECURITY CLASS
 * ============================================
 * Project: Street2Screen ZA
 * Purpose: Provide security functions for the application
 * Author: Ignatius Mayibongwe Khumalo
 * Institution: Eduvos Private Institution
 * Course: ITECA3-12 Initial Project
 * Date: February 2026
 * ============================================
 * 
 * WHAT THIS CLASS DOES:
 * - Prevents Cross-Site Scripting (XSS) attacks
 * - Prevents Cross-Site Request Forgery (CSRF) attacks
 * - Sanitizes user input before database insertion
 * - Securely hashes and verifies passwords
 * - Validates email addresses and phone numbers
 * - Generates secure random tokens
 * 
 * SECURITY FEATURES:
 * - CSRF token generation and validation
 * - HTML entity encoding (XSS prevention)
 * - Input sanitization and validation
 * - Secure password hashing (bcrypt)
 * - Session security management
 * 
 * ============================================
 */

class Security {
    
    // ============================================
    // SECTION 1: CSRF PROTECTION
    // ============================================
    // Purpose: Prevent Cross-Site Request Forgery attacks
    // CSRF: Attacker tricks user into submitting malicious requests
    
    /**
     * Generate CSRF token and store in session
     * 
     * WHAT IT DOES:
     * 1. Starts session if not already started
     * 2. Generates a random cryptographic token
     * 3. Stores token in $_SESSION
     * 4. Returns token for use in forms
     * 
     * WHY WE NEED THIS:
     * CSRF tokens prevent attackers from submitting forms
     * on behalf of legitimate users
     * 
     * @return string CSRF token
     * 
     * USAGE IN HTML FORM:
     * <form method="POST">
     *     <input type="hidden" name="csrf_token" value="<?php echo Security::generateCSRFToken(); ?>">
     *     <!-- other form fields -->
     * </form>
     */
    public static function generateCSRFToken() {
        // Start session if not started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Generate random token using cryptographically secure method
        // bin2hex(random_bytes(32)) creates a 64-character hexadecimal string
        $token = bin2hex(random_bytes(32));
        
        // Store token in session for later validation
        $_SESSION['csrf_token'] = $token;
        
        // Also store timestamp to allow token expiration
        $_SESSION['csrf_token_time'] = time();
        
        return $token;
    }
    
    /**
     * Validate CSRF token from form submission
     * 
     * WHAT IT DOES:
     * 1. Checks if token exists in POST data
     * 2. Checks if token exists in session
     * 3. Compares them securely
     * 4. Checks if token has expired (1 hour limit)
     * 
     * @param string $token Token from form submission
     * @return bool True if valid, false otherwise
     * 
     * USAGE IN FORM HANDLER:
     * if (!Security::validateCSRFToken($_POST['csrf_token'])) {
     *     die('Invalid CSRF token - possible attack!');
     * }
     */
    public static function validateCSRFToken($token) {
        // Start session if not started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Check if token exists in session
        if (!isset($_SESSION['csrf_token']) || !isset($_SESSION['csrf_token_time'])) {
            return false;
        }
        
        // Check if token has expired (1 hour = 3600 seconds)
        $tokenAge = time() - $_SESSION['csrf_token_time'];
        if ($tokenAge > 3600) {
            // Token expired - generate new one
            unset($_SESSION['csrf_token']);
            unset($_SESSION['csrf_token_time']);
            return false;
        }
        
        // Compare tokens using hash_equals (timing-attack safe comparison)
        // Regular == comparison can leak information about the token
        $valid = hash_equals($_SESSION['csrf_token'], $token);
        
        // If valid, clear the token so it can't be reused
        if ($valid) {
            unset($_SESSION['csrf_token']);
            unset($_SESSION['csrf_token_time']);
        }
        
        return $valid;
    }
    
    // ============================================
    // SECTION 2: XSS PREVENTION
    // ============================================
    // Purpose: Prevent Cross-Site Scripting attacks
    // XSS: Attacker injects malicious JavaScript into web pages
    
    /**
     * Clean output to prevent XSS attacks
     * 
     * WHAT IT DOES:
     * Converts special HTML characters to HTML entities
     * This prevents JavaScript code from executing
     * 
     * EXAMPLE:
     * Input: <script>alert('XSS')</script>
     * Output: &lt;script&gt;alert('XSS')&lt;/script&gt;
     * (Shows as text, doesn't execute)
     * 
     * @param string $data Data to clean
     * @return string Cleaned data safe for HTML output
     * 
     * USAGE:
     * echo Security::clean($userInput);  // Safe
     * echo $userInput;  // DANGEROUS - can execute JavaScript!
     */
    public static function clean($data) {
        // htmlspecialchars converts:
        // < to &lt;
        // > to &gt;
        // & to &amp;
        // " to &quot;
        // ' to &#039;
        return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Clean array of data (for multiple form fields)
     * 
     * @param array $data Array of data to clean
     * @return array Cleaned array
     * 
     * USAGE:
     * $cleanData = Security::cleanArray($_POST);
     */
    public static function cleanArray($data) {
        $cleaned = [];
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $cleaned[$key] = self::cleanArray($value);
            } else {
                $cleaned[$key] = self::clean($value);
            }
        }
        return $cleaned;
    }
    
    // ============================================
    // SECTION 3: INPUT SANITIZATION
    // ============================================
    // Purpose: Clean and validate user input
    
    /**
     * Sanitize string input (remove tags, trim whitespace)
     * 
     * WHAT IT DOES:
     * 1. Removes HTML and PHP tags
     * 2. Trims leading/trailing whitespace
     * 3. Removes multiple spaces
     * 
     * @param string $input User input
     * @return string Sanitized string
     * 
     * USAGE:
     * $name = Security::sanitizeString($_POST['full_name']);
     */
    public static function sanitizeString($input) {
        // Remove HTML and PHP tags
        $cleaned = strip_tags($input);
        
        // Trim whitespace from start and end
        $cleaned = trim($cleaned);
        
        // Remove extra whitespace from middle
        $cleaned = preg_replace('/\s+/', ' ', $cleaned);
        
        return $cleaned;
    }
    
    /**
     * Sanitize email address
     * 
     * WHAT IT DOES:
     * Removes all characters except letters, numbers, and email symbols
     * 
     * @param string $email Email input
     * @return string Sanitized email
     * 
     * USAGE:
     * $email = Security::sanitizeEmail($_POST['email']);
     */
    public static function sanitizeEmail($email) {
        // filter_var with FILTER_SANITIZE_EMAIL removes invalid characters
        return filter_var($email, FILTER_SANITIZE_EMAIL);
    }
    
    /**
     * Sanitize phone number (South African format)
     * 
     * WHAT IT DOES:
     * Removes all characters except numbers, +, -, (, )
     * 
     * @param string $phone Phone input
     * @return string Sanitized phone
     * 
     * USAGE:
     * $phone = Security::sanitizePhone($_POST['phone']);
     */
    public static function sanitizePhone($phone) {
        // Keep only numbers and phone symbols
        return preg_replace('/[^0-9+\-\(\)\s]/', '', $phone);
    }
    
    // ============================================
    // SECTION 4: INPUT VALIDATION
    // ============================================
    // Purpose: Verify input meets requirements
    
    /**
     * Validate email address format
     * 
     * @param string $email Email to validate
     * @return bool True if valid, false otherwise
     * 
     * USAGE:
     * if (!Security::validateEmail($email)) {
     *     echo "Invalid email format!";
     * }
     */
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Validate password strength
     * 
     * REQUIREMENTS:
     * - At least 8 characters long
     * - Contains at least one uppercase letter
     * - Contains at least one lowercase letter
     * - Contains at least one number
     * - Contains at least one special character
     * 
     * @param string $password Password to validate
     * @return array ['valid' => bool, 'errors' => array]
     * 
     * USAGE:
     * $result = Security::validatePassword($_POST['password']);
     * if (!$result['valid']) {
     *     foreach ($result['errors'] as $error) {
     *         echo $error . "<br>";
     *     }
     * }
     */
    public static function validatePassword($password) {
        $errors = [];
        
        // Check minimum length
        if (strlen($password) < 8) {
            $errors[] = "Password must be at least 8 characters long";
        }
        
        // Check for uppercase letter
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = "Password must contain at least one uppercase letter";
        }
        
        // Check for lowercase letter
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = "Password must contain at least one lowercase letter";
        }
        
        // Check for number
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = "Password must contain at least one number";
        }
        
        // Check for special character
        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            $errors[] = "Password must contain at least one special character (!@#$%^&*)";
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    /**
     * Validate South African phone number
     * 
     * ACCEPTS:
     * - 0821234567 (10 digits starting with 0)
     * - +27821234567 (11 digits with country code)
     * 
     * @param string $phone Phone number
     * @return bool True if valid, false otherwise
     */
    public static function validatePhone($phone) {
        // Remove spaces, hyphens, brackets
        $cleaned = preg_replace('/[\s\-\(\)]/', '', $phone);
        
        // Check if starts with 0 and is 10 digits
        if (preg_match('/^0[0-9]{9}$/', $cleaned)) {
            return true;
        }
        
        // Check if starts with +27 and is 11 digits
        if (preg_match('/^\+27[0-9]{9}$/', $cleaned)) {
            return true;
        }
        
        return false;
    }
    
    // ============================================
    // SECTION 5: PASSWORD HASHING
    // ============================================
    // Purpose: Securely hash and verify passwords
    
    /**
     * Hash password using bcrypt
     * 
     * WHAT IT DOES:
     * Uses PHP's password_hash() with bcrypt algorithm
     * Automatically generates a salt
     * Cost factor of 12 (from config)
     * 
     * @param string $password Plain text password
     * @return string Hashed password
     * 
     * USAGE:
     * $hashedPassword = Security::hashPassword($_POST['password']);
     * // Store $hashedPassword in database, NOT the plain password!
     */
    public static function hashPassword($password) {
        // Use bcrypt with cost from config (default: 12)
        $options = ['cost' => defined('PASSWORD_COST') ? PASSWORD_COST : 12];
        
        return password_hash($password, PASSWORD_BCRYPT, $options);
    }
    
    /**
     * Verify password against hash
     * 
     * WHAT IT DOES:
     * Compares user-entered password with stored hash
     * Returns true if they match, false otherwise
     * 
     * @param string $password Plain text password from login form
     * @param string $hash Hashed password from database
     * @return bool True if password matches, false otherwise
     * 
     * USAGE (in login):
     * if (Security::verifyPassword($_POST['password'], $user['password_hash'])) {
     *     // Password correct - log user in
     * } else {
     *     // Password incorrect
     * }
     */
    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
    
    // ============================================
    // SECTION 6: RANDOM TOKEN GENERATION
    // ============================================
    // Purpose: Generate secure random tokens
    
    /**
     * Generate random token for email verification, password reset, etc.
     * 
     * WHAT IT DOES:
     * Creates a cryptographically secure random token
     * Uses random_bytes() for true randomness
     * 
     * @param int $length Length of token (default: 32 bytes = 64 hex characters)
     * @return string Random token
     * 
     * USAGE:
     * $verificationToken = Security::generateToken();
     * // Store in database, send to user via email
     */
    public static function generateToken($length = 32) {
        return bin2hex(random_bytes($length));
    }
    
    // ============================================
    // SECTION 7: SESSION SECURITY
    // ============================================
    // Purpose: Secure session management
    
    /**
     * Initialize secure session
     * 
     * WHAT IT DOES:
     * 1. Sets secure session parameters
     * 2. Prevents session hijacking
     * 3. Starts session safely
     * 
     * CALL THIS AT START OF EVERY PAGE:
     * Security::initSession();
     */
    public static function initSession() {
        // Don't start if session already active
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }
        
        // Set secure session parameters
        ini_set('session.cookie_httponly', 1);  // Prevent JavaScript access
        ini_set('session.use_only_cookies', 1); // Only use cookies, not URLs
        ini_set('session.cookie_secure', 0);    // Set to 1 for HTTPS only
        
        // Set session lifetime (from config, default 2 hours)
        $lifetime = defined('SESSION_LIFETIME') ? SESSION_LIFETIME : 7200;
        ini_set('session.gc_maxlifetime', $lifetime);
        
        // Start session
        session_start();
        
        // Regenerate session ID to prevent fixation attacks
        // Only do this occasionally to avoid performance issues
        if (!isset($_SESSION['last_regeneration'])) {
            $_SESSION['last_regeneration'] = time();
        } else if (time() - $_SESSION['last_regeneration'] > 600) {
            session_regenerate_id(true);
            $_SESSION['last_regeneration'] = time();
        }
    }
    
    /**
     * Check if user is logged in
     * 
     * @return bool True if logged in, false otherwise
     */
    public static function isLoggedIn() {
        self::initSession();
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }
    
    /**
     * Get current logged-in user ID
     * 
     * @return int|null User ID or null if not logged in
     */
    public static function getUserId() {
        self::initSession();
        return $_SESSION['user_id'] ?? null;
    }
    
    /**
     * Require user to be logged in (redirect to login if not)
     * 
     * USAGE:
     * Security::requireLogin();
     * // Code below only runs if user is logged in
     */
    public static function requireLogin() {
        if (!self::isLoggedIn()) {
            header('Location: /street2screen/login.php');
            exit();
        }
    }
    
    // ============================================
    // END OF SECURITY CLASS
    // ============================================
}

/**
 * ============================================
 * USAGE EXAMPLES
 * ============================================
 * 
 * EXAMPLE 1: Secure Form with CSRF Protection
 * --------------------------------------------
 * <form method="POST">
 *     <input type="hidden" name="csrf_token" value="<?php echo Security::generateCSRFToken(); ?>">
 *     <input type="email" name="email">
 *     <button type="submit">Submit</button>
 * </form>
 * 
 * // In handler:
 * if (!Security::validateCSRFToken($_POST['csrf_token'])) {
 *     die('Invalid request');
 * }
 * 
 * EXAMPLE 2: User Registration
 * ----------------------------
 * $email = Security::sanitizeEmail($_POST['email']);
 * if (!Security::validateEmail($email)) {
 *     die('Invalid email');
 * }
 * 
 * $passwordCheck = Security::validatePassword($_POST['password']);
 * if (!$passwordCheck['valid']) {
 *     foreach ($passwordCheck['errors'] as $error) {
 *         echo $error . "<br>";
 *     }
 *     die();
 * }
 * 
 * $hashedPassword = Security::hashPassword($_POST['password']);
 * // Store $hashedPassword in database
 * 
 * EXAMPLE 3: User Login
 * ---------------------
 * $user = // ... fetch from database by email
 * 
 * if (Security::verifyPassword($_POST['password'], $user['password_hash'])) {
 *     $_SESSION['user_id'] = $user['user_id'];
 *     header('Location: dashboard.php');
 * } else {
 *     echo "Invalid password";
 * }
 * 
 * EXAMPLE 4: Display User Input Safely
 * -------------------------------------
 * echo Security::clean($user['full_name']);  // Safe from XSS
 * 
 * ============================================
 */
?>
