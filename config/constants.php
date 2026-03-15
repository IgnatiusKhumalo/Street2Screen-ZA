<?php
/**
 * ============================================
 * CONSTANTS CONFIGURATION
 * ============================================
 * Application-wide constants for Street2Screen ZA
 * ============================================
 */

// ============================================
// APPLICATION INFO
// ============================================
define('APP_NAME', 'Street2Screen ZA');
define('APP_SLOGAN', 'Bringing Kasi To Your Screen');
define('APP_VERSION', '1.0.0');
define('APP_YEAR', '2026');
define('APP_EMAIL', 'im.khumalo.the.coder@gmail.com');

// ============================================
// STUDENT / ACADEMIC INFO
// ============================================
define('STUDENT_NAME', 'Ignatius Mayibongwe Khumalo');
define('STUDENT_EMAIL', 'im.khumalo.the.coder@gmail.com');
define('INSTITUTION', 'Eduvos');
define('COURSE_CODE', 'ITECA3-12');
define('ACADEMIC_YEAR', '2026');

// ============================================
// ENVIRONMENT
// ============================================
$isLocal = (
    $_SERVER['HTTP_HOST'] === 'localhost' ||
    strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false
);
define('APP_ENV', $isLocal ? 'development' : 'production');
define('APP_DEBUG', APP_ENV === 'development');

// ============================================
// USER TYPES
// ============================================
define('USER_BUYER',     'buyer');
define('USER_SELLER',    'seller');
define('USER_BOTH',      'both');
define('USER_MODERATOR', 'moderator');
define('USER_ADMIN',     'admin');

// ============================================
// ACCOUNT STATUS
// ============================================
define('STATUS_ACTIVE',    'active');
define('STATUS_SUSPENDED', 'suspended');
define('STATUS_PENDING',   'pending');

// ============================================
// PRODUCT CONDITIONS
// ============================================
define('CONDITION_NEW',        'new');
define('CONDITION_LIKE_NEW',   'like_new');
define('CONDITION_GOOD',       'good');
define('CONDITION_FAIR',       'fair');

// ============================================
// ORDER / PAYMENT STATUS
// ============================================
define('PAYMENT_PENDING',   'pending');
define('PAYMENT_PAID',      'paid');
define('PAYMENT_FAILED',    'failed');
define('PAYMENT_REFUNDED',  'refunded');

define('DELIVERY_PENDING',    'pending');
define('DELIVERY_PROCESSING', 'processing');
define('DELIVERY_SHIPPED',    'shipped');
define('DELIVERY_DELIVERED',  'delivered');
define('DELIVERY_CANCELLED',  'cancelled');

// ============================================
// DISPUTE STATUS
// ============================================
define('DISPUTE_OPEN',         'open');
define('DISPUTE_INVESTIGATING', 'investigating');
define('DISPUTE_RESOLVED',     'resolved');
define('DISPUTE_CLOSED',       'closed');

// ============================================
// VERIFICATION STATUS
// ============================================
define('VERIFY_PENDING',  'pending');
define('VERIFY_APPROVED', 'approved');
define('VERIFY_REJECTED', 'rejected');

// ============================================
// PAGINATION
// ============================================
define('PRODUCTS_PER_PAGE', 12);
define('ORDERS_PER_PAGE',   10);
define('USERS_PER_PAGE',    20);
define('MESSAGES_PER_PAGE', 20);

// ============================================
// SOUTH AFRICAN PROVINCES
// ============================================
define('SA_PROVINCES', [
    'GP' => 'Gauteng',
    'WC' => 'Western Cape',
    'KZN' => 'KwaZulu-Natal',
    'EC' => 'Eastern Cape',
    'LP' => 'Limpopo',
    'MP' => 'Mpumalanga',
    'NW' => 'North West',
    'FS' => 'Free State',
    'NC' => 'Northern Cape'
]);

// ============================================
// SUPPORTED LANGUAGES
// ============================================
define('SUPPORTED_LANGUAGES', [
    'en'  => 'English',
    'af'  => 'Afrikaans',
    'zu'  => 'Zulu',
    'xh'  => 'Xhosa',
    'st'  => 'Sotho',
    'tn'  => 'Tswana',
    'ts'  => 'Tsonga',
    've'  => 'Venda',
    'ss'  => 'Swati',
    'nr'  => 'Ndebele',
    'nso' => 'Northern Sotho'
]);

// ============================================
// DELIVERY TIMEFRAMES (days)
// ============================================
define('DELIVERY_LOCAL',    2);  // Same township
define('DELIVERY_REGIONAL', 4);  // Same province
define('DELIVERY_NATIONAL', 7);  // Different province

// ============================================
// RATING LIMITS
// ============================================
define('MIN_RATING', 1);
define('MAX_RATING', 5);

// ============================================
// LOGS
// ============================================
define('LOG_DIR', __DIR__ . '/../logs/');
define('LOG_ERRORS', true);
?>
