<?php
/**
 * AJAX Handler for Toggle Favorite
 */
session_start();

// Try to find config file (multiple possible locations)
$configPaths = [
    __DIR__.'/../config/config.php',
    __DIR__.'/../config/database.php',
    __DIR__.'/../includes/config.php',
];

$configFound = false;
foreach($configPaths as $path) {
    if(file_exists($path)) {
        require_once $path;
        $configFound = true;
        break;
    }
}

// If no config found, define APP_URL manually
if(!$configFound && !defined('APP_URL')) {
    define('APP_URL', 'http://localhost/street2screen');
}

require_once __DIR__.'/../includes/Database.php';
require_once __DIR__.'/../includes/Security.php';

header('Content-Type: application/json');

// Enable error logging for debugging
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__.'/../logs/ajax-errors.log');

// Check if logged in
if(!Security::isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit;
}

$productId = (int)($_POST['product_id'] ?? 0);
$action = $_POST['action'] ?? ''; // 'add' or 'remove'

if($productId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid product']);
    exit;
}

$db = new Database();
$userId = Security::getUserId();

try {
    if($action === 'add') {
        // Add to favorites
        $db->query("INSERT IGNORE INTO favorites (user_id, product_id, added_at) 
                    VALUES (:uid, :pid, NOW())");
        $db->bind(':uid', $userId);
        $db->bind(':pid', $productId);
        $db->execute();
        
        echo json_encode(['success' => true, 'message' => 'Added to favorites']);
        
    } elseif($action === 'remove') {
        // Remove from favorites
        $db->query("DELETE FROM favorites WHERE user_id = :uid AND product_id = :pid");
        $db->bind(':uid', $userId);
        $db->bind(':pid', $productId);
        $db->execute();
        
        echo json_encode(['success' => true, 'message' => 'Removed from favorites']);
        
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch(Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
