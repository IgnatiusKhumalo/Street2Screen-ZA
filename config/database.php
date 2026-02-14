<?php
/**
 * ============================================
 * DATABASE CONFIGURATION FILE
 * ============================================
 * Project: Street2Screen ZA
 * Purpose: Configure database connection settings
 * Author: Ignatius Mayibongwe Khumalo
 * Institution: Eduvos Private Institution
 * Course: ITECA3-12 Initial Project
 * Date: February 2026
 * ============================================
 * 
 * SECURITY NOTE:
 * This file contains sensitive database credentials.
 * - NEVER commit this to GitHub (protected by .gitignore)
 * - All credentials are from official setup guides
 * 
 * ============================================
 */

// ============================================
// SECTION 1: ENVIRONMENT DETECTION
// ============================================
// Purpose: Automatically detect if we're on local development or production server

// Check if we're on localhost (XAMPP) or production (InfinityFree)
$isLocalhost = ($_SERVER['SERVER_NAME'] === 'localhost' || $_SERVER['SERVER_ADDR'] === '127.0.0.1');

// ============================================
// SECTION 2: DATABASE CREDENTIALS
// ============================================

if ($isLocalhost) {
    // ----------------------------------------
    // LOCAL DEVELOPMENT (XAMPP)
    // ----------------------------------------
    // From XAMPP Setup Report (February 2026)
    
    define('DB_HOST', 'localhost');           
    define('DB_NAME', 'street2screen_db');    
    define('DB_USER', 'root');                
    define('DB_PASS', 'Street2Screen2026!');  // Your XAMPP MySQL password
    define('DB_CHARSET', 'utf8mb4');          
    
} else {
    // ----------------------------------------
    // PRODUCTION (InfinityFree)
    // ----------------------------------------
    // From InfinityFree Setup (February 11, 2026)
    // Account: if0_41132529
    
    define('DB_HOST', 'sql305.infinityfree.com');        
    define('DB_NAME', 'if0_41132529_street2screen');     
    define('DB_USER', 'if0_41132529');                   
    define('DB_PASS', 'lcjwkyOhjpCvbc');                 
    define('DB_CHARSET', 'utf8mb4');                     
}

// ============================================
// SECTION 3: PDO OPTIONS
// ============================================

define('DB_OPTIONS', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
]);

// ============================================
// SECTION 4: APPLICATION SETTINGS
// ============================================

define('APP_NAME', 'Street2Screen ZA');
define('APP_TAGLINE', 'Bringing Kasi To Your Screen');

if ($isLocalhost) {
    define('APP_URL', 'http://localhost/street2screen');
} else {
    define('APP_URL', 'http://street2screen.infinityfreeapp.com');
}

// ============================================
// SECTION 5: FILE UPLOAD SETTINGS
// ============================================
// Based on php.ini: upload_max_filesize = 10M

define('MAX_IMAGE_SIZE', 5 * 1024 * 1024);              // 5MB for images
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
define('MAX_DOCUMENT_SIZE', 10 * 1024 * 1024);          // 10MB for documents
define('ALLOWED_DOCUMENT_TYPES', ['jpg', 'jpeg', 'png', 'pdf']);

// ============================================
// SECTION 6: EMAIL CONFIGURATION (BREVO SMTP)
// ============================================
// From Brevo Setup (February 2026)
// Email: im.khumalo.the.coder@gmail.com

define('SMTP_HOST', 'smtp-relay.brevo.com');
define('SMTP_PORT', 587);                          
define('SMTP_USER', 'a2009c001@smtp-brevo.com');   
define('SMTP_PASS', 'qngw7HvEaT3S01L6');          
define('SMTP_FROM_EMAIL', 'noreply@street2screen.co.za');
define('SMTP_FROM_NAME', 'Street2Screen ZA');
define('SMTP_DAILY_LIMIT', 300);                   // Free tier limit

// ============================================
// SECTION 7: SECURITY SETTINGS
// ============================================

define('SESSION_LIFETIME', 7200);                  // 2 hours
define('PASSWORD_ALGO', PASSWORD_BCRYPT);
define('PASSWORD_COST', 12);

// ============================================
// SECTION 8: PAYMENT GATEWAY (PayFast)
// ============================================

if ($isLocalhost) {
    define('PAYFAST_MODE', 'sandbox');
    define('PAYFAST_MERCHANT_ID', '10000100');
    define('PAYFAST_MERCHANT_KEY', '46f0cd694581a');
    define('PAYFAST_URL', 'https://sandbox.payfast.co.za/eng/process');
} else {
    define('PAYFAST_MODE', 'live');
    define('PAYFAST_MERCHANT_ID', 'REGISTER_FOR_LIVE_ACCOUNT');
    define('PAYFAST_MERCHANT_KEY', 'UPDATE_AFTER_REGISTRATION');
    define('PAYFAST_URL', 'https://www.payfast.co.za/eng/process');
}

define('PLATFORM_FEE_PERCENTAGE', 0.05);           // 5% platform fee

// ============================================
// SECTION 9: TIMEZONE SETTING
// ============================================

date_default_timezone_set('Africa/Johannesburg');

// ============================================
// SECTION 10: ERROR REPORTING
// ============================================

if ($isLocalhost) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('log_errors', 1);
    ini_set('error_log', 'C:/xampp/php/logs/php_error_log');
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
}

// ============================================
// END OF CONFIGURATION
// ============================================
?>
