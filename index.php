<?php
/**
 * ============================================
 * HOMEPAGE
 * ============================================
 * Main landing page showing categories and featured products
 * ============================================
 */

$pageTitle = 'Home';
require_once __DIR__ . '/includes/header.php';

// Get categories from database
$db = new Database();
$db->query("SELECT * FROM categories WHERE status = 'active' ORDER BY name ASC");
$categories = $db->fetchAll();

// Get product count
$db->query("SELECT COUNT(*) as total FROM products WHERE status = 'active'");
$productStats = $db->fetch();
$productCount = $productStats['total'];

// Get user count
$db->query("SELECT COUNT(*) as total FROM users WHERE account_status = 'active'");
$userStats = $db->fetch();
$userCount = $userStats['total'];
?>

<!-- Hero Section -->
<div style="background: linear-gradient(135deg, var(--primary-blue) 0%, #1a3a5c 100%); color: white; padding: 80px 0;">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="display-4 fw-bold mb-3">Bringing Kasi To Your Screen</h1>
                <p class="lead mb-4">
                    Connect with township entrepreneurs across South Africa. 
                    Buy and sell with confidence on our secure platform.
                </p>
                <?php if (!$isLoggedIn): ?>
                <div>
                    <a href="<?php echo APP_URL; ?>/auth/register.php" class="btn btn-yellow-custom btn-lg me-2">
                        <i class="fas fa-user-plus"></i> Get Started
                    </a>
                    <a href="<?php echo APP_URL; ?>/products/index.php" class="btn btn-outline-light btn-lg">
                        <i class="fas fa-shopping-bag"></i> Browse Products
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Stats Section -->
<div class="container my-5">
    <div class="row text-center">
        <div class="col-md-4 mb-4">
            <div class="p-4">
                <i class="fas fa-users fa-3x mb-3" style="color: var(--primary-blue);"></i>
                <h3 class="fw-bold"><?php echo number_format($userCount); ?>+</h3>
                <p class="text-muted">Active Users</p>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="p-4">
                <i class="fas fa-box fa-3x mb-3" style="color: var(--primary-yellow);"></i>
                <h3 class="fw-bold"><?php echo number_format($productCount); ?>+</h3>
                <p class="text-muted">Products Listed</p>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="p-4">
                <i class="fas fa-shield-alt fa-3x mb-3" style="color: var(--success-green);"></i>
                <h3 class="fw-bold">100%</h3>
                <p class="text-muted">Secure Platform</p>
            </div>
        </div>
    </div>
</div>

<!-- Categories Section -->
<div class="container my-5">
    <h2 class="text-center fw-bold mb-4" style="color: var(--primary-blue);">Shop by Category</h2>
    <div class="row">
        <?php foreach ($categories as $category): ?>
        <div class="col-md-4 col-lg-3 mb-4">
            <a href="<?php echo APP_URL; ?>/products/index.php?category=<?php echo $category['category_id']; ?>" 
               style="text-decoration: none; color: inherit;">
                <div class="card h-100 product-card">
                    <div class="card-body text-center">
                        <i class="fas fa-tag fa-3x mb-3" style="color: var(--primary-yellow);"></i>
                        <h5><?php echo Security::clean($category['name']); ?></h5>
                        <p class="text-muted small mb-0"><?php echo Security::clean($category['description']); ?></p>
                    </div>
                </div>
            </a>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Features Section -->
<div class="py-5" style="background: var(--light-grey);">
    <div class="container">
        <h2 class="text-center fw-bold mb-5" style="color: var(--primary-blue);">Why Choose Street2Screen ZA?</h2>
        <div class="row">
            <div class="col-md-3 text-center mb-4">
                <i class="fas fa-check-circle fa-3x mb-3" style="color: var(--success-green);"></i>
                <h5>Verified Sellers</h5>
                <p class="text-muted">All sellers verified for your safety</p>
            </div>
            <div class="col-md-3 text-center mb-4">
                <i class="fas fa-globe fa-3x mb-3" style="color: var(--primary-blue);"></i>
                <h5>11 SA Languages</h5>
                <p class="text-muted">Platform in all official languages</p>
            </div>
            <div class="col-md-3 text-center mb-4">
                <i class="fas fa-lock fa-3x mb-3" style="color: var(--primary-yellow);"></i>
                <h5>Secure Payments</h5>
                <p class="text-muted">PayFast integration for safety</p>
            </div>
            <div class="col-md-3 text-center mb-4">
                <i class="fas fa-headset fa-3x mb-3" style="color: var(--danger-red);"></i>
                <h5>24/7 Support</h5>
                <p class="text-muted">Help when you need it</p>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
