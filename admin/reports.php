<?php
$pageTitle='Platform Reports';
require_once __DIR__.'/../includes/header.php';
Security::requireAdmin();

$db=new Database();

// User statistics
$db->query("SELECT COUNT(*) as total FROM users WHERE created_at>=DATE_SUB(NOW(),INTERVAL 30 DAY)");
$newUsersMonth=$db->fetch()['total'];

$db->query("SELECT COUNT(*) as total FROM users WHERE user_type='buyer'");
$buyerCount=$db->fetch()['total'];

$db->query("SELECT COUNT(*) as total FROM users WHERE user_type IN('seller','both')");
$sellerCount=$db->fetch()['total'];

// Order statistics
$db->query("SELECT COUNT(*) as total FROM orders WHERE order_date>=DATE_SUB(NOW(),INTERVAL 30 DAY)");
$ordersThisMonth=$db->fetch()['total'];

$db->query("SELECT SUM(total_amount) as revenue FROM orders WHERE payment_status='paid' AND order_date>=DATE_SUB(NOW(),INTERVAL 30 DAY)");
$revenueThisMonth=$db->fetch()['revenue']??0;

$db->query("SELECT COUNT(*) as total FROM products WHERE created_at>=DATE_SUB(NOW(),INTERVAL 30 DAY)");
$newProductsMonth=$db->fetch()['total'];

// Category breakdown
$db->query("SELECT c.category_name,COUNT(p.product_id) as product_count FROM categories c LEFT JOIN products p ON c.category_id=p.category_id WHERE c.active=1 GROUP BY c.category_id ORDER BY product_count DESC");
$categoryStats=$db->fetchAll();

// Top sellers
$db->query("SELECT u.full_name,u.email,COUNT(o.order_id) as total_sales,SUM(o.total_amount) as total_revenue FROM users u JOIN orders o ON u.user_id=o.seller_id WHERE o.payment_status='paid' GROUP BY u.user_id ORDER BY total_revenue DESC LIMIT 10");
$topSellers=$db->fetchAll();

// Recent activity
$db->query("SELECT u.full_name,u.user_type,u.created_at FROM users ORDER BY u.created_at DESC LIMIT 10");
$recentUsers=$db->fetchAll();
?>

<div class="container my-5">
<h2 class="fw-bold mb-4"><i class="fas fa-chart-bar text-success"></i> Platform Reports</h2>

<!-- Monthly Statistics -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-white bg-primary text-center">
            <div class="card-body">
                <h3><?php echo $newUsersMonth; ?></h3>
                <p class="mb-0">New Users (30d)</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-success text-center">
            <div class="card-body">
                <h3><?php echo $ordersThisMonth; ?></h3>
                <p class="mb-0">Orders (30d)</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-info text-center">
            <div class="card-body">
                <h3><?php echo format_currency($revenueThisMonth); ?></h3>
                <p class="mb-0">Revenue (30d)</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-warning text-center">
            <div class="card-body">
                <h3><?php echo $newProductsMonth; ?></h3>
                <p class="mb-0">New Products (30d)</p>
            </div>
        </div>
    </div>
</div>

<!-- User Breakdown -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-users"></i> User Distribution</h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-3">
                    <span class="fw-bold">Buyers:</span>
                    <span class="badge bg-primary"><?php echo $buyerCount; ?> users</span>
                </div>
                <div class="progress mb-3" style="height:25px">
                    <div class="progress-bar bg-primary" style="width:<?php echo ($buyerCount/($buyerCount+$sellerCount))*100; ?>%">
                        <?php echo round(($buyerCount/($buyerCount+$sellerCount))*100); ?>%
                    </div>
                </div>
                
                <div class="d-flex justify-content-between mb-3">
                    <span class="fw-bold">Sellers:</span>
                    <span class="badge bg-success"><?php echo $sellerCount; ?> users</span>
                </div>
                <div class="progress" style="height:25px">
                    <div class="progress-bar bg-success" style="width:<?php echo ($sellerCount/($buyerCount+$sellerCount))*100; ?>%">
                        <?php echo round(($sellerCount/($buyerCount+$sellerCount))*100); ?>%
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card shadow">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="fas fa-box"></i> Products by Category</h5>
            </div>
            <div class="card-body">
                <?php foreach($categoryStats as $cat): ?>
                <div class="d-flex justify-content-between mb-2">
                    <span><?php echo Security::clean($cat['category_name']); ?></span>
                    <span class="badge bg-info"><?php echo $cat['product_count']; ?> products</span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- Top Sellers -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card shadow">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-trophy"></i> Top 10 Sellers by Revenue</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Seller Name</th>
                                <th>Email</th>
                                <th>Total Sales</th>
                                <th>Total Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $rank=1; foreach($topSellers as $seller): ?>
                            <tr>
                                <td><strong><?php echo $rank++; ?></strong></td>
                                <td><?php echo Security::clean($seller['full_name']); ?></td>
                                <td><?php echo Security::clean($seller['email']); ?></td>
                                <td><span class="badge bg-info"><?php echo $seller['total_sales']; ?> orders</span></td>
                                <td class="fw-bold text-success"><?php echo format_currency($seller['total_revenue']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity -->
<div class="row">
    <div class="col-md-12">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-history"></i> Recent User Registrations</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Name</th>
                                <th>User Type</th>
                                <th>Registered</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($recentUsers as $user): ?>
                            <tr>
                                <td><?php echo Security::clean($user['full_name']); ?></td>
                                <td><span class="badge bg-primary"><?php echo ucfirst($user['user_type']); ?></span></td>
                                <td><?php echo time_ago($user['created_at']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

</div>

<?php require_once __DIR__.'/../includes/footer.php'; ?>
