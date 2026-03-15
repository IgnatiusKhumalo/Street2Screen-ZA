<?php
$pageTitle='Seller Dashboard';
require_once __DIR__.'/../includes/header.php';
Security::requireLogin();

if(!in_array(Security::getUserType(),['seller','both'])){
    redirect_with_error(APP_URL.'/index.php','Sellers only');
}

$db=new Database();
$userId=Security::getUserId();

// Get seller statistics
$db->query("SELECT COUNT(*) as total FROM products WHERE seller_id=:uid AND status='active'");
$db->bind(':uid',$userId);
$activeProducts=$db->fetch()['total'];

$db->query("SELECT COUNT(*) as total FROM orders WHERE seller_id=:uid");
$db->bind(':uid',$userId);
$totalSales=$db->fetch()['total'];

$db->query("SELECT SUM(total_amount) as revenue FROM orders WHERE seller_id=:uid AND payment_status='paid'");
$db->bind(':uid',$userId);
$totalRevenue=$db->fetch()['revenue']??0;

$db->query("SELECT AVG(rating) as avg FROM reviews WHERE seller_id=:uid");
$db->bind(':uid',$userId);
$avgRating=$db->fetch()['avg']??0;

// Get top products
$db->query("SELECT p.product_name,COUNT(o.order_id)as sales,SUM(o.total_amount)as revenue FROM products p LEFT JOIN orders o ON p.product_id=o.product_id WHERE p.seller_id=:uid GROUP BY p.product_id ORDER BY sales DESC LIMIT 5");
$db->bind(':uid',$userId);
$topProducts=$db->fetchAll();

// Recent sales
$db->query("SELECT o.*,p.product_name,u.full_name as buyer_name FROM orders o JOIN products p ON o.product_id=p.product_id JOIN users u ON o.buyer_id=u.user_id WHERE o.seller_id=:uid ORDER BY o.order_date DESC LIMIT 5");
$db->bind(':uid',$userId);
$recentSales=$db->fetchAll();
?>

<div class="container my-5">
<h2 class="fw-bold mb-4"><i class="fas fa-store text-success"></i> Seller Dashboard</h2>

<!-- Stats Cards -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card text-white bg-primary text-center">
            <div class="card-body">
                <h3><?php echo $activeProducts; ?></h3>
                <p class="mb-0">Active Products</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card text-white bg-success text-center">
            <div class="card-body">
                <h3><?php echo $totalSales; ?></h3>
                <p class="mb-0">Total Sales</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card text-white bg-info text-center">
            <div class="card-body">
                <h3><?php echo format_currency($totalRevenue); ?></h3>
                <p class="mb-0">Total Revenue</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card text-white bg-warning text-center">
            <div class="card-body">
                <h3><?php echo number_format($avgRating,1); ?> <i class="fas fa-star"></i></h3>
                <p class="mb-0">Average Rating</p>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-bolt"></i> Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <a href="<?php echo APP_URL; ?>/products/add.php" class="btn btn-success w-100 mb-2">
                            <i class="fas fa-plus"></i> Add Product
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="<?php echo APP_URL; ?>/orders/sales.php" class="btn btn-primary w-100 mb-2">
                            <i class="fas fa-chart-line"></i> View Sales
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="<?php echo APP_URL; ?>/messages/inbox.php" class="btn btn-info w-100 mb-2">
                            <i class="fas fa-envelope"></i> Messages
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="<?php echo APP_URL; ?>/reviews/view.php?seller_id=<?php echo $userId; ?>" class="btn btn-warning w-100 mb-2">
                            <i class="fas fa-star"></i> My Reviews
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Top Products -->
    <div class="col-md-6">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-trophy"></i> Top Products</h5>
            </div>
            <div class="card-body">
                <?php if(empty($topProducts)): ?>
                <p class="text-muted text-center">No sales yet</p>
                <?php else: ?>
                <?php foreach($topProducts as $p): ?>
                <div class="d-flex justify-content-between mb-2">
                    <span><?php echo excerpt(Security::clean($p['product_name']),30); ?></span>
                    <div>
                        <span class="badge bg-success"><?php echo $p['sales']?:0; ?> sales</span>
                        <span class="badge bg-info"><?php echo format_currency($p['revenue']?:0); ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Recent Sales -->
    <div class="col-md-6">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-clock"></i> Recent Sales</h5>
            </div>
            <div class="card-body">
                <?php if(empty($recentSales)): ?>
                <p class="text-muted text-center">No sales yet</p>
                <?php else: ?>
                <?php foreach($recentSales as $s): ?>
                <div class="mb-2 pb-2 border-bottom">
                    <strong><?php echo excerpt(Security::clean($s['product_name']),30); ?></strong><br>
                    <small class="text-muted">
                        <?php echo Security::clean($s['buyer_name']); ?> • 
                        <?php echo format_currency($s['total_amount']); ?> • 
                        <?php echo time_ago($s['order_date']); ?>
                    </small>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

</div>

<?php require_once __DIR__.'/../includes/footer.php'; ?>
