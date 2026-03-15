<?php
/**
 * ============================================
 * HOMEPAGE - WITH SPLASH SCREEN CHECK
 * ============================================
 * First-time visitors see splash screen
 * Returning visitors go straight to homepage
 * ============================================
 */

// Start session to track if user has seen splash
session_start();

// Check if user has already seen splash screen
if (!isset($_SESSION['splash_seen'])) {
    // First visit - redirect to splash screen
    $_SESSION['splash_seen'] = true;
    header('Location: splash.php');
    exit;
}

// If they've seen splash, continue to homepage
$pageTitle = 'Home';
require_once 'includes/header.php';
?>

<!-- BEAUTIFUL HERO SECTION -->
<section class="position-relative overflow-hidden" style="min-height: 100vh; background: linear-gradient(135deg, #0B1F3A 0%, #1a3a6b 25%, #2a5a9b 50%, #1a3a6b 75%, #0B1F3A 100%); background-size: 400% 400%; animation: gradientShift 15s ease infinite;">
    
    <style>
        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        .floating-particles {
            position: absolute;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 1;
        }
        
        .particle {
            position: absolute;
            background: rgba(255, 193, 7, 0.15);
            border-radius: 50%;
            animation: floatUp 25s infinite ease-in-out;
        }
        
        .particle:nth-child(1) { width: 60px; height: 60px; left: 10%; animation-delay: 0s; }
        .particle:nth-child(2) { width: 40px; height: 40px; left: 25%; animation-delay: 3s; }
        .particle:nth-child(3) { width: 80px; height: 80px; left: 40%; animation-delay: 6s; }
        .particle:nth-child(4) { width: 50px; height: 50px; left: 55%; animation-delay: 9s; }
        .particle:nth-child(5) { width: 70px; height: 70px; left: 70%; animation-delay: 12s; }
        .particle:nth-child(6) { width: 90px; height: 90px; left: 85%; animation-delay: 15s; }
        
        @keyframes floatUp {
            0%, 100% {
                transform: translateY(100vh) scale(0);
                opacity: 0;
            }
            50% {
                opacity: 0.6;
            }
            100% {
                transform: translateY(-20vh) scale(1);
            }
        }
        
        .hero-content {
            position: relative;
            z-index: 10;
            animation: fadeInUp 1.5s ease-out;
        }
        
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(50px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .hero-logo {
            animation: zoomIn 1.2s ease-out;
            filter: drop-shadow(0 10px 30px rgba(0, 0, 0, 0.5));
        }
        
        @keyframes zoomIn {
            from { opacity: 0; transform: scale(0.5); }
            to { opacity: 1; transform: scale(1); }
        }
        
        .hero-slogan {
            font-size: 2rem;
            color: #FFD54F;
            font-weight: 600;
            animation: slideInRight 2s ease-out;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        }
        
        @keyframes slideInRight {
            from { opacity: 0; transform: translateX(100px); }
            to { opacity: 1; transform: translateX(0); }
        }
        
        .hero-description {
            font-size: 1.3rem;
            color: #ffffff;
            animation: fadeIn 2.5s ease-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        .btn-hero {
            padding: 15px 40px;
            font-size: 1.2rem;
            font-weight: 600;
            border-radius: 50px;
            transition: all 0.3s ease;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
        }
        
        .btn-hero:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.4);
        }
        
        .btn-browse {
            background: linear-gradient(135deg, #FFC107 0%, #FFD54F 100%);
            color: #0B1F3A;
            border: none;
        }
        
        .btn-sell {
            background: transparent;
            color: #FFC107;
            border: 3px solid #FFC107;
        }
        
        .btn-sell:hover {
            background: #FFC107;
            color: #0B1F3A;
        }
        
        @media (max-width: 768px) {
            .hero-slogan { font-size: 1.3rem; }
            .hero-description { font-size: 1rem; }
            .btn-hero { padding: 12px 30px; font-size: 1rem; }
        }
    </style>
    
    <!-- Floating Particles -->
    <div class="floating-particles">
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
    </div>
    
    <!-- Hero Content -->
    <div class="container hero-content d-flex flex-column justify-content-center align-items-center text-center" style="min-height: 100vh; padding: 60px 20px;">
        
        <!-- Logo -->
        <div class="mb-4 hero-logo">
            <img src="<?php echo APP_URL; ?>/assets/images/logo.png" alt="Street2Screen ZA" style="max-width: 500px; width: 90%;">
        </div>
        
        <!-- Slogan -->
        <h1 class="hero-slogan mb-3">BRINGING KASI TO YOUR SCREEN</h1>
        
        <p class="hero-description mb-5 px-3" style="max-width: 700px;">
            South Africa's Premier Township Marketplace - Empowering Entrepreneurs Across Mzansi 🇿🇦
        </p>
        
        <!-- CTA Buttons -->
        <div class="d-flex gap-3 flex-wrap justify-content-center" style="animation: fadeIn 3s ease-out;">
            <a href="<?php echo APP_URL; ?>/products/index.php" class="btn btn-browse btn-hero">
                <i class="fas fa-shopping-bag me-2"></i> <?php echo t('browse_products'); ?>
            </a>
            <a href="<?php echo APP_URL; ?>/auth/register.php" class="btn btn-sell btn-hero">
                <i class="fas fa-plus-circle me-2"></i> <?php echo t('start_selling'); ?>
            </a>
        </div>
        
    </div>
</section>

<!-- FEATURES SECTION -->
<section class="py-5">
    <div class="container">
        <h2 class="text-center mb-5 fw-bold"><?php echo t('why_us'); ?></h2>
        <div class="row g-4 text-center">
            <div class="col-md-4">
                <div class="p-4">
                    <i class="fas fa-shield-alt fa-4x text-primary mb-3"></i>
                    <h4><?php echo t('secure_safe'); ?></h4>
                    <p class="text-muted"><?php echo t('secure_desc'); ?></p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-4">
                    <i class="fas fa-truck fa-4x text-success mb-3"></i>
                    <h4><?php echo t('nationwide_delivery'); ?></h4>
                    <p class="text-muted"><?php echo t('delivery_desc'); ?></p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-4">
                    <i class="fas fa-handshake fa-4x text-warning mb-3"></i>
                    <h4><?php echo t('support_local'); ?></h4>
                    <p class="text-muted"><?php echo t('support_desc'); ?></p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- HOW IT WORKS -->
<section class="py-5 bg-light">
    <div class="container">
        <h2 class="text-center mb-5 fw-bold"><?php echo t('how_it_works'); ?></h2>
        <div class="row g-4">
            <div class="col-md-3 text-center">
                <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center mb-3" 
                     style="width: 80px; height: 80px; font-size: 2rem; font-weight: bold;">1</div>
                <h5><?php echo t('step_register'); ?></h5>
                <p class="text-muted"><?php echo t('step_register_desc'); ?></p>
            </div>
            <div class="col-md-3 text-center">
                <div class="rounded-circle bg-success text-white d-inline-flex align-items-center justify-content-center mb-3" 
                     style="width: 80px; height: 80px; font-size: 2rem; font-weight: bold;">2</div>
                <h5><?php echo t('step_browse'); ?></h5>
                <p class="text-muted"><?php echo t('step_browse_desc'); ?></p>
            </div>
            <div class="col-md-3 text-center">
                <div class="rounded-circle bg-warning text-white d-inline-flex align-items-center justify-content-center mb-3" 
                     style="width: 80px; height: 80px; font-size: 2rem; font-weight: bold;">3</div>
                <h5><?php echo t('step_order'); ?></h5>
                <p class="text-muted"><?php echo t('step_order_desc'); ?></p>
            </div>
            <div class="col-md-3 text-center">
                <div class="rounded-circle bg-info text-white d-inline-flex align-items-center justify-content-center mb-3" 
                     style="width: 80px; height: 80px; font-size: 2rem; font-weight: bold;">4</div>
                <h5><?php echo t('step_delivered'); ?></h5>
                <p class="text-muted"><?php echo t('step_delivered_desc'); ?></p>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
