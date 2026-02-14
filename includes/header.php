<?php
/**
 * ============================================
 * COMMON HEADER FILE
 * ============================================
 * Project: Street2Screen ZA
 * Purpose: Shared header for all pages with navigation
 * Author: Ignatius Mayibongwe Khumalo
 * Date: February 2026
 * ============================================
 * 
 * WHAT THIS FILE DOES:
 * - Starts session if not started
 * - Includes required files (config, database, security, functions)
 * - Displays navigation bar with logo
 * - Shows login/register OR user menu based on session
 * - Displays flash messages
 * 
 * USAGE:
 * Include at top of every page:
 * <?php require_once __DIR__ . '/includes/header.php'; ?>
 * 
 * ============================================
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include required files
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Security.php';
require_once __DIR__ . '/functions.php';

// Check if user is logged in
$isLoggedIn = Security::isLoggedIn();
$userId = Security::getUserId();
$userName = '';

// If logged in, get user's name from database
if ($isLoggedIn) {
    $db = new Database();
    $db->query("SELECT full_name FROM users WHERE user_id = :id");
    $db->bind(':id', $userId);
    $user = $db->fetch();
    $userName = $user ? $user['full_name'] : 'User';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?>Street2Screen ZA</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Our Custom CSS -->
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/main.css">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?php echo APP_URL; ?>/assets/images/logo/Street2ScreenZA_Logo.png">
</head>
<body>
    
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark" style="background: var(--primary-blue);">
        <div class="container">
            
            <!-- Logo & Brand -->
            <a class="navbar-brand d-flex align-items-center" href="<?php echo APP_URL; ?>/index.php">
                <img src="<?php echo APP_URL; ?>/assets/images/logo/Street2ScreenZA_Logo.png" 
                     alt="Street2Screen ZA" 
                     height="40" 
                     class="me-2">
                <span class="fw-bold">Street2Screen<span style="color: var(--primary-yellow);">ZA</span></span>
            </a>
            
            <!-- Mobile Toggle Button -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <!-- Navigation Links -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    
                    <!-- Home Link -->
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo APP_URL; ?>/index.php">
                            <i class="fas fa-home"></i> Home
                        </a>
                    </li>
                    
                    <!-- Products Link -->
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo APP_URL; ?>/products/index.php">
                            <i class="fas fa-shopping-bag"></i> Products
                        </a>
                    </li>
                    
                    <?php if ($isLoggedIn): ?>
                        <!-- Logged In Menu -->
                        
                        <!-- Sell Link -->
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo APP_URL; ?>/products/add.php">
                                <i class="fas fa-plus-circle"></i> Sell
                            </a>
                        </li>
                        
                        <!-- Messages Link -->
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo APP_URL; ?>/messages.php">
                                <i class="fas fa-envelope"></i> Messages
                            </a>
                        </li>
                        
                        <!-- User Dropdown -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user-circle"></i> <?php echo Security::clean($userName); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item" href="<?php echo APP_URL; ?>/profile.php">
                                        <i class="fas fa-user"></i> My Profile
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="<?php echo APP_URL; ?>/dashboard.php">
                                        <i class="fas fa-tachometer-alt"></i> Dashboard
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item" href="<?php echo APP_URL; ?>/auth/logout.php">
                                        <i class="fas fa-sign-out-alt"></i> Logout
                                    </a>
                                </li>
                            </ul>
                        </li>
                        
                    <?php else: ?>
                        <!-- Not Logged In Menu -->
                        
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo APP_URL; ?>/auth/login.php">
                                <i class="fas fa-sign-in-alt"></i> Login
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="btn btn-yellow-custom ms-2" href="<?php echo APP_URL; ?>/auth/register.php">
                                <i class="fas fa-user-plus"></i> Register
                            </a>
                        </li>
                        
                    <?php endif; ?>
                    
                </ul>
            </div>
        </div>
    </nav>
    
    <?php
    /**
     * Display flash message if exists
     * Flash messages are one-time notifications that disappear after being shown
     * They're stored in session and automatically cleared after display
     */
    $flash = get_flash_message();
    if ($flash):
    ?>
    <div class="container mt-3">
        <div class="flash-message flash-<?php echo $flash['type']; ?>">
            <?php echo Security::clean($flash['message']); ?>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Main Content Starts Here (pages will add their content) -->
    <main>
