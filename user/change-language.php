<?php
/**
 * CHANGE LANGUAGE HANDLER
 * Handles language switching from navbar dropdown
 */
session_start();
require_once __DIR__.'/../includes/Language.php';

$lang = $_GET['lang'] ?? 'en';

if(Language::setLanguage($lang)) {
    // Language changed successfully
    $_SESSION['flash_success'] = 'Language changed to ' . Language::getLanguages()[$lang];
} else {
    // Invalid language code
    $_SESSION['flash_error'] = 'Invalid language selected';
}

// Redirect back to previous page or home
$redirect = $_SERVER['HTTP_REFERER'] ?? APP_URL . '/index.php';
header('Location: ' . $redirect);
exit;
?>
