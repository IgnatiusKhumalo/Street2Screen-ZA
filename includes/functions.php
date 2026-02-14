<?php
/**
 * ============================================
 * HELPER FUNCTIONS
 * ============================================
 * Project: Street2Screen ZA
 * Purpose: Common utility functions used throughout the application
 * Author: Ignatius Mayibongwe Khumalo
 * Institution: Eduvos Private Institution
 * Course: ITECA3-12 Initial Project
 * Date: February 2026
 * ============================================
 * 
 * WHAT THIS FILE CONTAINS:
 * - Redirect functions
 * - Flash message system
 * - File upload handlers
 * - Date formatting
 * - Price formatting (South African Rands)
 * - URL slug generation
 * - Pagination helpers
 * - Common utility functions
 * 
 * ============================================
 */

// ============================================
// SECTION 1: REDIRECT FUNCTIONS
// ============================================

/**
 * Redirect to another page
 * 
 * WHAT IT DOES:
 * Sends browser to a different page and stops script execution
 * 
 * @param string $location URL or path to redirect to
 * @return void
 * 
 * EXAMPLE:
 * redirect('/login.php');
 * redirect('https://www.example.com');
 */
function redirect($location) {
    // Check if location is a full URL or relative path
    if (!preg_match("/^https?:\/\//i", $location)) {
        // Relative path - prepend base URL
        $location = APP_URL . $location;
    }
    
    header("Location: $location");
    exit();
}

/**
 * Redirect with a success message
 * 
 * @param string $location Where to redirect
 * @param string $message Success message to display
 * @return void
 */
function redirect_with_success($location, $message) {
    set_flash_message('success', $message);
    redirect($location);
}

/**
 * Redirect with an error message
 * 
 * @param string $location Where to redirect
 * @param string $message Error message to display
 * @return void
 */
function redirect_with_error($location, $message) {
    set_flash_message('error', $message);
    redirect($location);
}

// ============================================
// SECTION 2: FLASH MESSAGE SYSTEM
// ============================================
// Flash messages are one-time messages shown after redirect
// They appear once, then disappear (like a camera flash!)

/**
 * Set a flash message
 * 
 * WHAT IT DOES:
 * Stores a message in session to display on next page load
 * Message is automatically deleted after being displayed once
 * 
 * @param string $type Message type (success, error, warning, info)
 * @param string $message The message text
 * @return void
 * 
 * EXAMPLE:
 * set_flash_message('success', 'Product added successfully!');
 * set_flash_message('error', 'Email already exists');
 */
function set_flash_message($type, $message) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Get and clear flash message
 * 
 * WHAT IT DOES:
 * Retrieves the flash message then deletes it from session
 * This ensures message only shows once
 * 
 * @return array|null ['type' => string, 'message' => string] or null
 * 
 * EXAMPLE IN HTML:
 * <?php
 * $flash = get_flash_message();
 * if ($flash):
 * ?>
 * <div class="alert alert-<?php echo $flash['type']; ?>">
 *     <?php echo $flash['message']; ?>
 * </div>
 * <?php endif; ?>
 */
function get_flash_message() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    
    return null;
}

// ============================================
// SECTION 3: FILE UPLOAD FUNCTIONS
// ============================================

/**
 * Upload an image file
 * 
 * WHAT IT DOES:
 * 1. Validates file is an image
 * 2. Checks file size limits
 * 3. Generates unique filename
 * 4. Moves file to uploads directory
 * 5. Returns new filename or error
 * 
 * @param array $file $_FILES array element
 * @param string $destination Folder to upload to (e.g., 'products', 'profiles')
 * @return array ['success' => bool, 'filename' => string, 'error' => string]
 * 
 * EXAMPLE:
 * $result = upload_image($_FILES['product_image'], 'products');
 * if ($result['success']) {
 *     echo "Uploaded: " . $result['filename'];
 * } else {
 *     echo "Error: " . $result['error'];
 * }
 */
function upload_image($file, $destination = 'products') {
    // Check if file was uploaded
    if (!isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
        return [
            'success' => false,
            'filename' => null,
            'error' => 'No file uploaded or upload error occurred'
        ];
    }
    
    // Get file extension
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    // Check if extension is allowed
    if (!in_array($extension, ALLOWED_IMAGE_TYPES)) {
        return [
            'success' => false,
            'filename' => null,
            'error' => 'Invalid file type. Allowed: ' . implode(', ', ALLOWED_IMAGE_TYPES)
        ];
    }
    
    // Check file size
    if ($file['size'] > MAX_IMAGE_SIZE) {
        $max_mb = MAX_IMAGE_SIZE / (1024 * 1024);
        return [
            'success' => false,
            'filename' => null,
            'error' => "File too large. Maximum size: {$max_mb}MB"
        ];
    }
    
    // Generate unique filename
    $unique_name = uniqid('img_', true) . '.' . $extension;
    
    // Determine upload path
    $upload_dir = dirname(__DIR__) . "/uploads/{$destination}/";
    
    // Create directory if it doesn't exist
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    $upload_path = $upload_dir . $unique_name;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
        return [
            'success' => true,
            'filename' => $unique_name,
            'error' => null
        ];
    } else {
        return [
            'success' => false,
            'filename' => null,
            'error' => 'Failed to move uploaded file'
        ];
    }
}

/**
 * Delete an uploaded file
 * 
 * @param string $filename File to delete
 * @param string $folder Folder file is in (e.g., 'products')
 * @return bool True if deleted, false otherwise
 */
function delete_uploaded_file($filename, $folder = 'products') {
    $file_path = dirname(__DIR__) . "/uploads/{$folder}/{$filename}";
    
    if (file_exists($file_path)) {
        return unlink($file_path);
    }
    
    return false;
}

// ============================================
// SECTION 4: DATE & TIME FORMATTING
// ============================================

/**
 * Format date for South African users
 * 
 * WHAT IT DOES:
 * Converts database datetime to readable format
 * 
 * @param string $datetime MySQL datetime string
 * @param string $format Date format (default: 'd M Y')
 * @return string Formatted date
 * 
 * EXAMPLES:
 * format_date('2026-02-14 10:30:00') → "14 Feb 2026"
 * format_date('2026-02-14 10:30:00', 'd/m/Y H:i') → "14/02/2026 10:30"
 */
function format_date($datetime, $format = 'd M Y') {
    if (empty($datetime)) {
        return '';
    }
    
    $timestamp = strtotime($datetime);
    return date($format, $timestamp);
}

/**
 * Get time ago (e.g., "2 hours ago")
 * 
 * @param string $datetime MySQL datetime string
 * @return string Human-readable time difference
 * 
 * EXAMPLE:
 * time_ago('2026-02-14 08:00:00') → "2 hours ago"
 */
function time_ago($datetime) {
    $timestamp = strtotime($datetime);
    $diff = time() - $timestamp;
    
    if ($diff < 60) {
        return 'Just now';
    } elseif ($diff < 3600) {
        $mins = floor($diff / 60);
        return $mins . ' minute' . ($mins > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    } else {
        return format_date($datetime);
    }
}

// ============================================
// SECTION 5: PRICE FORMATTING
// ============================================

/**
 * Format price in South African Rands
 * 
 * WHAT IT DOES:
 * Converts number to formatted currency string
 * 
 * @param float $amount Price amount
 * @param bool $include_symbol Include R symbol (default: true)
 * @return string Formatted price
 * 
 * EXAMPLES:
 * format_price(1500) → "R 1,500.00"
 * format_price(1500.50, false) → "1,500.50"
 */
function format_price($amount, $include_symbol = true) {
    $formatted = number_format($amount, 2, '.', ',');
    
    if ($include_symbol) {
        return 'R ' . $formatted;
    }
    
    return $formatted;
}

/**
 * Calculate platform fee (5%)
 * 
 * @param float $amount Transaction amount
 * @return float Platform fee
 */
function calculate_platform_fee($amount) {
    return $amount * PLATFORM_FEE_PERCENTAGE;
}

/**
 * Calculate seller payout (amount - platform fee)
 * 
 * @param float $amount Transaction amount
 * @return float Amount seller receives
 */
function calculate_seller_payout($amount) {
    return $amount - calculate_platform_fee($amount);
}

// ============================================
// SECTION 6: URL & SLUG FUNCTIONS
// ============================================

/**
 * Generate URL-friendly slug from text
 * 
 * WHAT IT DOES:
 * Converts text to lowercase, removes special characters,
 * replaces spaces with hyphens
 * 
 * @param string $text Text to convert
 * @return string URL-safe slug
 * 
 * EXAMPLES:
 * generate_slug('Samsung Galaxy S23') → 'samsung-galaxy-s23'
 * generate_slug('Nike Air Max Sneakers!') → 'nike-air-max-sneakers'
 */
function generate_slug($text) {
    // Convert to lowercase
    $slug = strtolower($text);
    
    // Remove special characters
    $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
    
    // Replace spaces and multiple hyphens with single hyphen
    $slug = preg_replace('/[\s-]+/', '-', $slug);
    
    // Trim hyphens from ends
    $slug = trim($slug, '-');
    
    return $slug;
}

/**
 * Get current page URL
 * 
 * @return string Current page URL
 */
function current_url() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    return $protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}

// ============================================
// SECTION 7: PAGINATION HELPERS
// ============================================

/**
 * Calculate pagination values
 * 
 * WHAT IT DOES:
 * Given total items and items per page, calculates:
 * - Total pages
 * - Current page offset
 * - Start and end item numbers
 * 
 * @param int $total_items Total number of items
 * @param int $per_page Items per page
 * @param int $current_page Current page number
 * @return array Pagination data
 * 
 * EXAMPLE:
 * $pagination = paginate(100, 20, 2);
 * // Returns:
 * // [
 * //   'total_items' => 100,
 * //   'per_page' => 20,
 * //   'total_pages' => 5,
 * //   'current_page' => 2,
 * //   'offset' => 20,
 * //   'start_item' => 21,
 * //   'end_item' => 40
 * // ]
 */
function paginate($total_items, $per_page = 20, $current_page = 1) {
    // Ensure current page is valid
    $current_page = max(1, (int)$current_page);
    
    // Calculate total pages
    $total_pages = ceil($total_items / $per_page);
    
    // Ensure current page doesn't exceed total pages
    $current_page = min($current_page, $total_pages);
    
    // Calculate offset for SQL query
    $offset = ($current_page - 1) * $per_page;
    
    // Calculate start and end item numbers
    $start_item = $offset + 1;
    $end_item = min($offset + $per_page, $total_items);
    
    return [
        'total_items' => $total_items,
        'per_page' => $per_page,
        'total_pages' => $total_pages,
        'current_page' => $current_page,
        'offset' => $offset,
        'start_item' => $start_item,
        'end_item' => $end_item,
        'has_previous' => $current_page > 1,
        'has_next' => $current_page < $total_pages
    ];
}

// ============================================
// SECTION 8: VALIDATION HELPERS
// ============================================

/**
 * Check if request is POST
 * 
 * @return bool True if POST request
 */
function is_post_request() {
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

/**
 * Check if request is GET
 * 
 * @return bool True if GET request
 */
function is_get_request() {
    return $_SERVER['REQUEST_METHOD'] === 'GET';
}

/**
 * Check if value exists in POST data
 * 
 * @param string $key POST key to check
 * @return bool True if exists and not empty
 */
function has_post($key) {
    return isset($_POST[$key]) && !empty(trim($_POST[$key]));
}

/**
 * Get POST value safely
 * 
 * @param string $key POST key
 * @param mixed $default Default value if not set
 * @return mixed POST value or default
 */
function get_post($key, $default = '') {
    return isset($_POST[$key]) ? trim($_POST[$key]) : $default;
}

/**
 * Get GET value safely
 * 
 * @param string $key GET key
 * @param mixed $default Default value if not set
 * @return mixed GET value or default
 */
function get_query($key, $default = '') {
    return isset($_GET[$key]) ? trim($_GET[$key]) : $default;
}

// ============================================
// SECTION 9: ARRAY HELPERS
// ============================================

/**
 * Check if array key exists and has value
 * 
 * @param string $key Array key
 * @param array $array Array to check
 * @return bool True if key exists and has value
 */
function array_has($key, $array) {
    return isset($array[$key]) && !empty($array[$key]);
}

/**
 * Get array value safely
 * 
 * @param string $key Array key
 * @param array $array Array to get from
 * @param mixed $default Default value
 * @return mixed Array value or default
 */
function array_get($key, $array, $default = null) {
    return isset($array[$key]) ? $array[$key] : $default;
}

// ============================================
// SECTION 10: DEBUG HELPERS
// ============================================

/**
 * Debug dump variable (for development only!)
 * 
 * WHAT IT DOES:
 * Pretty-prints variable in readable format
 * Only works in development environment
 * 
 * @param mixed $var Variable to dump
 * @param bool $die Stop execution after dump
 * @return void
 */
function dd($var, $die = true) {
    // Only show in development
    if (DB_HOST !== 'localhost') {
        return;
    }
    
    echo '<pre>';
    var_dump($var);
    echo '</pre>';
    
    if ($die) {
        die();
    }
}

// ============================================
// END OF HELPER FUNCTIONS
// ============================================

/*
 * USAGE EXAMPLES:
 * ===============
 * 
 * // Redirect with message
 * redirect_with_success('/products', 'Product added successfully!');
 * 
 * // Upload image
 * $result = upload_image($_FILES['photo'], 'products');
 * if ($result['success']) {
 *     // Save $result['filename'] to database
 * }
 * 
 * // Format price
 * echo format_price(1500.50); // "R 1,500.50"
 * 
 * // Generate slug
 * $slug = generate_slug($product_name);
 * 
 * // Pagination
 * $page_data = paginate($total_products, 20, $_GET['page'] ?? 1);
 * // Use $page_data['offset'] in SQL LIMIT query
 * 
 * // Flash messages in HTML
 * <?php $flash = get_flash_message(); ?>
 * <?php if ($flash): ?>
 *     <div class="alert alert-<?= $flash['type'] ?>">
 *         <?= $flash['message'] ?>
 *     </div>
 * <?php endif; ?>
 */
?>
