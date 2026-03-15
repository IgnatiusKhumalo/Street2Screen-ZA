<?php
$pageTitle='My Dashboard';
require_once __DIR__.'/../includes/header.php';
Security::requireLogin();

$db=new Database();
$userId=Security::getUserId();
$userType=Security::getUserType();

// Get user stats
$db->query("SELECT COUNT(*) as total FROM orders WHERE buyer_id=:uid");
$db->bind(':uid',$userId);
$orderCount=$db->fetch()['total'];

// Get seller stats if applicable
$productCount=0;
$salesCount=0;
$totalRevenue=0;

if(in_array($userType,['seller','both'])){
    $db->query("SELECT COUNT(*) as total FROM products WHERE seller_id=:uid AND status='active'");
    $db->bind(':uid',$userId);
    $productCount=$db->fetch()['total'];
    
    $db->query("SELECT COUNT(*) as total FROM orders WHERE seller_id=:uid");
    $db->bind(':uid',$userId);
    $salesCount=$db->fetch()['total'];
    
    $db->query("SELECT SUM(total_amount) as revenue FROM orders WHERE seller_id=:uid AND payment_status='paid'");
    $db->bind(':uid',$userId);
    $totalRevenue=$db->fetch()['revenue']??0;
}

// Recent orders
$db->query("SELECT o.*,p.product_name,(SELECT image_path FROM product_images WHERE product_id=p.product_id AND is_primary=1 LIMIT 1)as image FROM orders o JOIN products p ON o.product_id=p.product_id WHERE o.buyer_id=:uid ORDER BY o.order_date DESC LIMIT 5");
$db->bind(':uid',$userId);
$recentOrders=$db->fetchAll();

// Get user info
$db->query("SELECT full_name,email,phone FROM users WHERE user_id=:uid");
$db->bind(':uid',$userId);
$userInfo=$db->fetch();
?>

<div class="container my-5">
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold"><i class="fas fa-tachometer-alt text-primary"></i> <?php echo t('dashboard'); ?></h2>
    <div>
        <span class="badge bg-primary fs-6"><?php echo ucfirst($userType); ?></span>
    </div>
</div>

<!-- Welcome Card - UPDATED COLORS FOR VISIBILITY -->
<div class="card shadow-sm mb-4" style="background: linear-gradient(135deg, #0B1F3A 0%, #1a3a5c 100%); border: none;">
    <div class="card-body p-4">
        <h4 class="mb-2 fw-bold" style="color: #ffffff;"><?php echo t('welcome_back', ['name' => Security::clean($userInfo['full_name'])]); ?> 👋</h4>
        <p class="mb-0" style="color: #e0e0e0;"><?php echo t('account_today'); ?></p>
    </div>
</div>

<!-- Stats Cards -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card text-white bg-primary h-100">
            <div class="card-body text-center">
                <i class="fas fa-shopping-cart fa-3x mb-3"></i>
                <h3><?php echo $orderCount; ?></h3>
                <p class="mb-0"><?php echo t('total_orders'); ?></p>
            </div>
            <div class="card-footer bg-transparent border-0">
                <a href="<?php echo APP_URL; ?>/orders/my-orders.php" class="btn btn-light btn-sm w-100"><?php echo t('view'); ?> <?php echo t('my_orders'); ?></a>
            </div>
        </div>
    </div>
    
    <?php if(in_array($userType,['seller','both'])): ?>
    <div class="col-md-3 mb-3">
        <div class="card text-white bg-success h-100">
            <div class="card-body text-center">
                <i class="fas fa-box fa-3x mb-3"></i>
                <h3><?php echo $productCount; ?></h3>
                <p class="mb-0"><?php echo t('my_products'); ?></p>
            </div>
            <div class="card-footer bg-transparent border-0">
                <a href="<?php echo APP_URL; ?>/products/add.php" class="btn btn-light btn-sm w-100"><?php echo t('add_to_cart'); ?></a>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card text-white bg-info h-100">
            <div class="card-body text-center">
                <i class="fas fa-chart-line fa-3x mb-3"></i>
                <h3><?php echo $salesCount; ?></h3>
                <p class="mb-0"><?php echo t('total_sales'); ?></p>
            </div>
            <div class="card-footer bg-transparent border-0">
                <a href="<?php echo APP_URL; ?>/orders/sales.php" class="btn btn-light btn-sm w-100"><?php echo t('view'); ?> <?php echo t('my_sales'); ?></a>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card text-white bg-warning h-100">
            <div class="card-body text-center">
                <i class="fas fa-money-bill-wave fa-3x mb-3"></i>
                <h3><?php echo format_currency($totalRevenue); ?></h3>
                <p class="mb-0"><?php echo t('revenue'); ?></p>
            </div>
            <div class="card-footer bg-transparent border-0">
                <a href="<?php echo APP_URL; ?>/user/seller-dashboard.php" class="btn btn-light btn-sm w-100">Analytics</a>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Quick Actions -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0"><i class="fas fa-bolt"></i> <?php echo t('quick_actions'); ?></h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-2">
                        <a href="<?php echo APP_URL; ?>/products/index.php" class="btn btn-outline-primary w-100">
                            <i class="fas fa-shopping-bag"></i> <?php echo t('browse_products_btn'); ?>
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="<?php echo APP_URL; ?>/orders/cart.php" class="btn btn-outline-success w-100">
                            <i class="fas fa-shopping-cart"></i> <?php echo t('my_cart'); ?>
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="<?php echo APP_URL; ?>/messages/inbox.php" class="btn btn-outline-info w-100">
                            <i class="fas fa-envelope"></i> <?php echo t('messages'); ?>
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="<?php echo APP_URL; ?>/user/profile.php" class="btn btn-outline-warning w-100">
                            <i class="fas fa-user"></i> <?php echo t('edit_profile'); ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Orders -->
<?php if(!empty($recentOrders)): ?>
<div class="card shadow-sm">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="fas fa-clock"></i> <?php echo t('recent_orders'); ?></h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th><?php echo t('product'); ?></th>
                        <th><?php echo t('amount'); ?></th>
                        <th><?php echo t('status'); ?></th>
                        <th><?php echo t('date'); ?></th>
                        <th><?php echo t('actions'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($recentOrders as $o): ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <img src="<?php echo $o['image']?APP_URL.'/'.$o['image']:APP_URL.'/assets/images/placeholder.svg'; ?>" 
                                     style="width:50px;height:50px;object-fit:cover;border-radius:5px" class="me-2">
                                <span><?php echo Security::clean($o['product_name']); ?></span>
                            </div>
                        </td>
                        <td class="fw-bold text-success"><?php echo format_currency($o['total_amount']); ?></td>
                        <td>
                            <span class="badge bg-<?php echo $o['delivery_status']==='delivered'?'success':'warning'; ?>">
                                <?php echo ucfirst($o['delivery_status']); ?>
                            </span>
                        </td>
                        <td><?php echo format_date($o['order_date']); ?></td>
                        <td>
                            <a href="<?php echo APP_URL; ?>/orders/order-details.php?id=<?php echo $o['order_id']; ?>" 
                               class="btn btn-sm btn-primary">
                                <i class="fas fa-eye"></i> <?php echo t('view'); ?>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

</div>

<?php require_once __DIR__.'/../includes/footer.php'; ?>
