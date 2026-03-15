<?php
/**
 * ============================================
 * HELPER FUNCTIONS
 * ============================================
 * Common utility functions used throughout the app
 * ============================================
 */

/**
 * Check if request is POST
 * @return bool
 */
function is_post_request() {
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

/**
 * Get POST data safely
 * @param string $key POST key
 * @param mixed $default Default value
 * @return mixed
 */
function get_post($key, $default = '') {
    return $_POST[$key] ?? $default;
}

/**
 * Get GET data safely
 * @param string $key GET key
 * @param mixed $default Default value
 * @return mixed
 */
function get_get($key, $default = '') {
    return $_GET[$key] ?? $default;
}

/**
 * Redirect to URL
 * @param string $url URL to redirect to
 */
function redirect($url) {
    if (!headers_sent()) {
        header('Location: ' . $url);
        exit;
    }
    echo '<script>window.location.href="' . $url . '";</script>';
    exit;
}

/**
 * Redirect with success message
 * @param string $url URL to redirect to
 * @param string $message Success message
 */
function redirect_with_success($url, $message) {
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = 'success';
    redirect($url);
}

/**
 * Redirect with error message
 * @param string $url URL to redirect to
 * @param string $message Error message
 */
function redirect_with_error($url, $message) {
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = 'danger';
    redirect($url);
}

/**
 * Get and clear flash message
 * @return array|null ['message' => string, 'type' => string]
 */
function get_flash_message() {
    if (isset($_SESSION['flash_message'])) {
        $message = [
            'message' => $_SESSION['flash_message'],
            'type' => $_SESSION['flash_type']
        ];
        unset($_SESSION['flash_message'], $_SESSION['flash_type']);
        return $message;
    }
    return null;
}

/**
 * Format currency (ZAR)
 * @param float $amount Amount to format
 * @return string Formatted currency
 */
function format_currency($amount) {
    return 'R' . number_format($amount, 2);
}

/**
 * Format date for display
 * @param string $date Date string
 * @param string $format Format string
 * @return string Formatted date
 */
function format_date($date, $format = 'M j, Y') {
    return date($format, strtotime($date));
}

/**
 * Format date and time
 * @param string $datetime Datetime string
 * @return string Formatted datetime
 */
function format_datetime($datetime) {
    return date('M j, Y \a\t g:i A', strtotime($datetime));
}

/**
 * Calculate time ago
 * @param string $datetime Datetime string
 * @return string Time ago (e.g., "2 hours ago")
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
        return date('M j, Y', $timestamp);
    }
}

/**
 * Generate excerpt from text
 * @param string $text Full text
 * @param int $length Max length
 * @return string Excerpt
 */
function excerpt($text, $length = 100) {
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length) . '...';
}

/**
 * Upload file securely
 * @param array $file $_FILES array element
 * @param string $uploadDir Upload directory
 * @param array $allowedTypes Allowed file types
 * @param int $maxSize Max file size in bytes
 * @return array ['success' => bool, 'path' => string, 'error' => string]
 */
function upload_file($file, $uploadDir, $allowedTypes, $maxSize) {
    // Check if file was uploaded
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'File upload failed'];
    }
    
    // Check file size
    if ($file['size'] > $maxSize) {
        $maxMB = $maxSize / (1024 * 1024);
        return ['success' => false, 'error' => 'File exceeds ' . $maxMB . 'MB limit'];
    }
    
    // Check file type
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedTypes)) {
        return ['success' => false, 'error' => 'File type not allowed'];
    }
    
    // Create upload directory if not exists
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Generate unique filename
    $filename = uniqid() . '_' . time() . '.' . $ext;
    $filepath = $uploadDir . '/' . $filename;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['success' => true, 'path' => $filepath, 'filename' => $filename];
    }
    
    return ['success' => false, 'error' => 'Failed to move uploaded file'];
}

/**
 * Delete file safely
 * @param string $filepath File path
 * @return bool Success status
 */
function delete_file($filepath) {
    if (file_exists($filepath)) {
        return unlink($filepath);
    }
    return false;
}

/**
 * Get user's IP address
 * @return string IP address
 */
function get_user_ip() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    return $_SERVER['REMOTE_ADDR'];
}

/**
 * Send email using Brevo SMTP
 * @param string $to Recipient email
 * @param string $subject Email subject
 * @param string $body Email body (HTML)
 * @return bool Success status
 */
function send_email($to, $subject, $body) {
    require_once __DIR__ . '/Email.php';
    $email = new Email();
    return $email->send($to, $subject, $body);
}

// NOTE: t() function is declared in Translate.php - do not declare here to avoid conflicts
?>
