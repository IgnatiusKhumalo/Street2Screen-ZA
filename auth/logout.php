<?php
/**
 * ============================================
 * USER LOGOUT
 * ============================================
 * Securely destroys session and logs user out
 * ============================================
 */

session_start();

// Unset all session variables
$_SESSION = array();

// Destroy session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-3600, '/');
}

// Destroy session
session_destroy();

// Redirect to homepage with message
require_once __DIR__ . '/../includes/functions.php';
redirect_with_success('/index.php', 'You have been logged out successfully.');
?>
