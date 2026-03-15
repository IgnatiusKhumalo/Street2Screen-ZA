<?php
/**
 * ============================================
 * HEADER - SHARED ACROSS ALL PAGES
 * ============================================
 */

require_once __DIR__.'/../config/database.php';
require_once __DIR__.'/../includes/Database.php';
require_once __DIR__.'/../includes/Security.php';
require_once __DIR__.'/../includes/functions.php';
require_once __DIR__.'/../includes/Language.php';
require_once __DIR__.'/../includes/Email.php';
require_once __DIR__.'/../includes/Translate.php';

// Load translations for current language
$currentLang = $_SESSION['language'] ?? 'en';
Translate::load($currentLang);

$currentTheme = $_SESSION['theme'] ?? 'light';

$unreadMessages = 0;
if (Security::isLoggedIn()) {
    try {
        $db = new Database();
        $db->query("SELECT COUNT(*) as unread 
                    FROM messages m
                    JOIN conversations c ON m.conversation_id = c.conversation_id
                    WHERE (c.buyer_id = :uid OR c.seller_id = :uid)
                    AND m.sender_id != :uid
                    AND m.read_status = 0");
        $db->bind(':uid', Security::getUserId());
        $result = $db->fetch();
        $unreadMessages = $result['unread'] ?? 0;
    } catch (Exception $e) {
        $unreadMessages = 0;
    }
}

$cartCount = 0;
if (Security::isLoggedIn()) {
    try {
        $db = new Database();
        $db->query("SELECT COALESCE(SUM(quantity), 0) as count FROM cart WHERE user_id = :uid");
        $db->bind(':uid', Security::getUserId());
        $result = $db->fetch();
        $cartCount = (int)($result['count'] ?? 0);
    } catch (Exception $e) {
        $cartCount = 0;
    }
}

$flashSuccess = $_SESSION['flash_success'] ?? '';
$flashError   = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

$pageTitle = $pageTitle ?? 'Street2Screen ZA';
?>
<!DOCTYPE html>
<html lang="<?php echo $currentLang; ?>" data-theme="<?php echo $currentTheme; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Street2Screen ZA - Bringing Kasi To Your Screen">
    <title><?php echo htmlspecialchars($pageTitle); ?> | Street2Screen ZA</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <link href="<?php echo APP_URL; ?>/assets/css/main.css" rel="stylesheet">
    <link href="<?php echo APP_URL; ?>/assets/css/themes.css" rel="stylesheet">
    <link href="<?php echo APP_URL; ?>/assets/css/responsive.css" rel="stylesheet">

    <script>
        const APP_URL = '<?php echo APP_URL; ?>';
        const COD_THRESHOLD = <?php echo COD_THRESHOLD; ?>;
        const IS_LOGGED_IN = <?php echo Security::isLoggedIn() ? 'true' : 'false'; ?>;
    </script>
</head>
<body class="<?php echo $currentTheme === 'dark' ? 'bg-dark text-light' : ''; ?>">

<nav class="navbar navbar-expand-lg navbar-dark sticky-top shadow" 
     style="background: linear-gradient(135deg, #0B1F3A 0%, #1a3a6b 100%);">
    <div class="container">

        <!-- LOGO BRAND -->
        <a class="navbar-brand d-flex align-items-center" href="<?php echo APP_URL; ?>/index.php">
            <img src="<?php echo APP_URL; ?>/assets/images/logo.png" 
                 alt="Street2Screen ZA Logo" 
                 style="height:50px; width:auto; max-width:200px; object-fit:contain;">
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarMain">

            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo APP_URL; ?>/index.php">
                        <i class="fas fa-home"></i> <?php echo t('home'); ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo APP_URL; ?>/products/index.php">
                        <i class="fas fa-shopping-bag"></i> <?php echo t('products'); ?>
                    </a>
                </li>
                <?php if (Security::isLoggedIn() && in_array(Security::getUserType(), ['seller', 'both'])): ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo APP_URL; ?>/products/add.php">
                        <i class="fas fa-plus-circle text-warning"></i> <?php echo t('sell'); ?>
                    </a>
                </li>
                <?php endif; ?>
            </ul>

            <ul class="navbar-nav ms-auto align-items-center">

                <!-- LANGUAGE SELECTOR (NEW) -->
                <li class="nav-item dropdown me-3">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                        <i class="fas fa-globe"></i> 
                        <?php 
                            $langs = Language::getLanguages();
                            $currentLang = Language::getCurrentLanguage();
                            echo $langs[$currentLang] ?? 'English';
                        ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <?php foreach($langs as $code => $name): ?>
                        <li>
                            <a class="dropdown-item <?php echo $currentLang === $code ? 'active' : ''; ?>" 
                               href="<?php echo APP_URL; ?>/user/change-language.php?lang=<?php echo $code; ?>">
                                <?php echo $name; ?>
                                <?php if($currentLang === $code): ?>
                                <i class="fas fa-check text-success float-end"></i>
                                <?php endif; ?>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </li>

                <?php if (Security::isLoggedIn()): ?>

                <li class="nav-item me-2">
                    <a class="nav-link position-relative" href="<?php echo APP_URL; ?>/messages/inbox.php">
                        <i class="fas fa-envelope"></i>
                        <?php if ($unreadMessages > 0): ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            <?php echo $unreadMessages; ?>
                        </span>
                        <?php endif; ?>
                    </a>
                </li>

                <li class="nav-item me-2">
                    <a class="nav-link position-relative" href="<?php echo APP_URL; ?>/orders/cart.php">
                        <i class="fas fa-shopping-cart"></i>
                        <?php if ($cartCount > 0): ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-warning text-dark">
                            <?php echo $cartCount; ?>
                        </span>
                        <?php endif; ?>
                    </a>
                </li>

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" data-bs-toggle="dropdown">
                        <div class="rounded-circle bg-warning text-dark d-flex align-items-center justify-content-center me-2"
                             style="width:32px;height:32px;font-weight:bold;font-size:14px">
                            <?php echo strtoupper(substr($_SESSION['user_name'] ?? 'U', 0, 1)); ?>
                        </div>
                        <span class="d-none d-lg-inline">
                            <?php echo htmlspecialchars(explode(' ', $_SESSION['user_name'] ?? 'Account')[0]); ?>
                        </span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow">
                        <li><h6 class="dropdown-header">
                            <span class="badge bg-primary"><?php echo ucfirst(Security::getUserType()); ?></span>
                        </h6></li>
                        <li><a class="dropdown-item" href="<?php echo APP_URL; ?>/user/dashboard.php">
                            <i class="fas fa-tachometer-alt text-primary"></i> <?php echo t('dashboard'); ?>
                        </a></li>
                        <li><a class="dropdown-item" href="<?php echo APP_URL; ?>/user/profile.php">
                            <i class="fas fa-user text-info"></i> <?php echo t('my_profile'); ?>
                        </a></li>
                        <?php if (in_array(Security::getUserType(), ['seller', 'both'])): ?>
                        <li><a class="dropdown-item" href="<?php echo APP_URL; ?>/user/seller-dashboard.php">
                            <i class="fas fa-store text-success"></i> <?php echo t('seller_dashboard'); ?>
                        </a></li>
                        <li><a class="dropdown-item" href="<?php echo APP_URL; ?>/orders/sales.php">
                            <i class="fas fa-chart-line text-warning"></i> <?php echo t('my_sales'); ?>
                        </a></li>
                        <?php endif; ?>
                        <li><a class="dropdown-item" href="<?php echo APP_URL; ?>/user/favorites.php">
                            <i class="fas fa-heart text-danger"></i> <?php echo t('favorites'); ?>
                        </a></li>
                        <li><a class="dropdown-item" href="<?php echo APP_URL; ?>/orders/my-orders.php">
                            <i class="fas fa-box text-secondary"></i> <?php echo t('my_orders'); ?>
                        </a></li>
                        <?php if (in_array(Security::getUserType(), ['admin'])): ?>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="<?php echo APP_URL; ?>/admin/dashboard.php">
                            <i class="fas fa-shield-alt"></i> Admin Panel
                        </a></li>
                        <?php endif; ?>
                        <?php if (in_array(Security::getUserType(), ['moderator'])): ?>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-warning" href="<?php echo APP_URL; ?>/moderator/dashboard.php">
                            <i class="fas fa-gavel"></i> Moderator Panel
                        </a></li>
                        <?php endif; ?>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?php echo APP_URL; ?>/user/settings.php">
                            <i class="fas fa-cog"></i> <?php echo t('settings'); ?>
                        </a></li>
                        <li><a class="dropdown-item text-danger" href="<?php echo APP_URL; ?>/auth/logout.php">
                            <i class="fas fa-sign-out-alt"></i> <?php echo t('logout'); ?>
                        </a></li>
                    </ul>
                </li>

                <?php else: ?>

                <li class="nav-item">
                    <a class="nav-link" href="<?php echo APP_URL; ?>/auth/login.php">
                        <i class="fas fa-sign-in-alt"></i> <?php echo t('login'); ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="btn btn-warning btn-sm ms-2 fw-bold" href="<?php echo APP_URL; ?>/auth/register.php">
                        <i class="fas fa-user-plus"></i> <?php echo t('register'); ?>
                    </a>
                </li>

                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<?php if ($flashSuccess): ?>
<div class="alert alert-success alert-dismissible fade show mb-0 rounded-0 text-center" role="alert">
    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($flashSuccess); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php if ($flashError): ?>
<div class="alert alert-danger alert-dismissible fade show mb-0 rounded-0 text-center" role="alert">
    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($flashError); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>
