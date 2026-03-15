<?php
$pageTitle='Home';
require_once __DIR__.'/includes/header.php';

$db=new Database();

// Get featured products
$db->query("SELECT p.*,u.full_name as seller_name,(SELECT image_path FROM product_images WHERE product_id=p.product_id AND is_primary=1 LIMIT 1)as image FROM products p JOIN users u ON p.seller_id=u.user_id WHERE p.status='active' AND p.featured=1 AND p.featured_until>NOW() ORDER BY RAND() LIMIT 4");
$featuredProducts=$db->fetchAll();

// Get latest products
$db->query("SELECT p.*,u.full_name as seller_name,(SELECT image_path FROM product_images WHERE product_id=p.product_id AND is_primary=1 LIMIT 1)as image FROM products p JOIN users u ON p.seller_id=u.user_id WHERE p.status='active' ORDER BY p.created_at DESC LIMIT 8");
$latestProducts=$db->fetchAll();

// Get categories
$db->query("SELECT * FROM categories WHERE active=1 ORDER BY display_order LIMIT 6");
$categories=$db->fetchAll();

// Get statistics
$db->query("SELECT COUNT(*) as total FROM users WHERE user_type IN('seller','both')");
$sellerCount=$db->fetch()['total'];

$db->query("SELECT COUNT(*) as total FROM products WHERE status='active'");
$productCount=$db->fetch()['total'];

$db->query("SELECT COUNT(*) as total FROM orders");
$orderCount=$db->fetch()['total'];
?>

<!-- Splash Screen (3 seconds) -->
<div id="splashScreen" style="position:fixed;top:0;left:0;width:100%;height:100vh;background:linear-gradient(135deg,#0B1F3A 0%,#1a3a5c 100%);z-index:9999;display:flex;align-items:center;justify-content:center">
    <div style="text-align:center;color:#fff">
        <img src="<?php echo APP_URL; ?>/assets/images/logo/Street2ScreenZA_Logo.png" alt="Logo" style="max-width:200px;margin-bottom:20px;animation:fadeIn 1s">
        <h1 style="font-size:3rem;margin-bottom:10px;animation:slideUp 1s">Street2Screen<span style="color:#FFC107">ZA</span></h1>
        <p style="font-size:1.5rem;color:#FFC107;animation:slideUp 1.5s">Bringing Kasi To Your Screen</p>
        <div class="spinner-border text-warning mt-4" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>
</div>

<style>
@keyframes fadeIn{from{opacity:0}to{opacity:1}}
@keyframes slideUp{from{transform:translateY(30px);opacity:0}to{transform:translateY(0);opacity:1}}
.hero-section{background:linear-gradient(135deg,rgba(11,31,58,0.95),rgba(26,58,92,0.95)),url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 600"><rect fill="%23FFC107" opacity="0.1" width="1200" height="600"/></svg>');padding:100px 0;color:#fff}
.category-card{transition:all 0.3s;border:2px solid transparent}
.category-card:hover{transform:translateY(-10px);border-color:#FFC107;box-shadow:0 10px 30px rgba(0,0,0,0.2)}
.product-card{transition:all 0.3s;border:none;box-shadow:0 2px 10px rgba(0,0,0,0.1)}
.product-card:hover{transform:translateY(-5px);box-shadow:0 10px 30px rgba(0,0,0,0.2)}
.stat-card{background:linear-gradient(135deg,#667eea,#764ba2);color:#fff;border-radius:15px;padding:30px}
</style>

<!-- Hero Section -->
<section class="hero-section text-center">
    <div class="container">
        <h1 class="display-3 fw-bold mb-3">Welcome to Street2Screen ZA</h1>
        <p class="lead fs-2 mb-4" style="color:#FFC107">Bringing Kasi To Your Screen</p>
        <p class="fs-5 mb-5">South Africa's Premier Township Marketplace - Empowering Entrepreneurs</p>
        <div class="d-flex gap-3 justify-content-center">
            <a href="<?php echo APP_URL; ?>/products/index.php" class="btn btn-warning btn-lg text-dark px-5">
                <i class="fas fa-shopping-bag"></i> Browse Products
            </a>
            <a href="<?php echo APP_URL; ?>/auth/register.php" class="btn btn-outline-light btn-lg px-5">
                <i class="fas fa-user-plus"></i> Start Selling
            </a>
        </div>
    </div>
</section>

<!-- Statistics -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row text-center">
            <div class="col-md-4 mb-3">
                <div class="stat-card">
                    <h2 class="display-4 fw-bold"><?php echo number_format($sellerCount); ?>+</h2>
                    <p class="fs-5 mb-0">Township Sellers</p>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="stat-card">
                    <h2 class="display-4 fw-bold"><?php echo number_format($productCount); ?>+</h2>
                    <p class="fs-5 mb-0">Products Listed</p>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="stat-card">
                    <h2 class="display-4 fw-bold"><?php echo number_format($orderCount); ?>+</h2>
                    <p class="fs-5 mb-0">Successful Orders</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Featured Products -->
<?php if(!empty($featuredProducts)): ?>
<section class="py-5">
    <div class="container">
        <h2 class="fw-bold text-center mb-4">
            <i class="fas fa-star text-warning"></i> Featured Products
        </h2>
        <div class="row">
            <?php foreach($featuredProducts as $p): ?>
            <div class="col-md-3 mb-4">
                <div class="card product-card h-100">
                    <div class="position-relative">
                        <img src="<?php echo $p['image']?APP_URL.'/'.$p['image']:APP_URL.'/assets/images/placeholder.svg'; ?>" 
                             class="card-img-top" style="height:200px;object-fit:cover">
                        <span class="position-absolute top-0 end-0 m-2 badge bg-warning text-dark">Featured</span>
                    </div>
                    <div class="card-body">
                        <h6 class="card-title"><?php echo excerpt(Security::clean($p['product_name']),40); ?></h6>
                        <p class="text-success fw-bold fs-5 mb-2"><?php echo format_currency($p['price']); ?></p>
                        <p class="small text-muted mb-2">
                            <i class="fas fa-map-marker-alt"></i> <?php echo Security::clean($p['location']); ?>
                        </p>
                        <a href="<?php echo APP_URL; ?>/products/view.php?id=<?php echo $p['product_id']; ?>" 
                           class="btn btn-primary btn-sm w-100">View Details</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Categories -->
<section class="py-5 bg-light">
    <div class="container">
        <h2 class="fw-bold text-center mb-4">
            <i class="fas fa-th-large text-primary"></i> Shop by Category
        </h2>
        <div class="row">
            <?php foreach($categories as $c): ?>
            <div class="col-md-2 col-6 mb-4">
                <a href="<?php echo APP_URL; ?>/products/category.php?id=<?php echo $c['category_id']; ?>" 
                   class="text-decoration-none">
                    <div class="card category-card text-center h-100">
                        <div class="card-body">
                            <i class="fas <?php echo $c['icon_class']; ?> fa-3x text-primary mb-3"></i>
                            <h6 class="fw-bold"><?php echo Security::clean($c['category_name']); ?></h6>
                        </div>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Latest Products -->
<section class="py-5">
    <div class="container">
        <h2 class="fw-bold text-center mb-4">
            <i class="fas fa-clock text-info"></i> Latest Products
        </h2>
        <div class="row">
            <?php foreach($latestProducts as $p): ?>
            <div class="col-md-3 col-6 mb-4">
                <div class="card product-card h-100">
                    <img src="<?php echo $p['image']?APP_URL.'/'.$p['image']:APP_URL.'/assets/images/placeholder.svg'; ?>" 
                         class="card-img-top" style="height:180px;object-fit:cover">
                    <div class="card-body">
                        <h6 class="card-title"><?php echo excerpt(Security::clean($p['product_name']),35); ?></h6>
                        <p class="text-success fw-bold mb-1"><?php echo format_currency($p['price']); ?></p>
                        <p class="small text-muted mb-2">
                            <i class="fas fa-user"></i> <?php echo Security::clean($p['seller_name']); ?>
                        </p>
                        <a href="<?php echo APP_URL; ?>/products/view.php?id=<?php echo $p['product_id']; ?>" 
                           class="btn btn-outline-primary btn-sm w-100">View</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-4">
            <a href="<?php echo APP_URL; ?>/products/index.php" class="btn btn-primary btn-lg">
                <i class="fas fa-arrow-right"></i> View All Products
            </a>
        </div>
    </div>
</section>

<script>
// Hide splash screen after 3 seconds
setTimeout(function(){
    document.getElementById('splashScreen').style.display='none';
},3000);
</script>

<?php require_once __DIR__.'/includes/footer.php'; ?>
