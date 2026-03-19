<?php
/**
 * ============================================
 * ADMIN REPORTS - COMPREHENSIVE PLATFORM ANALYTICS
 * ============================================
 * Version: 5.0 - ABSOLUTELY FINAL - 100% Database Schema Matched
 * FIXED: Appeals are in disputes table, NOT separate dispute_appeals table
 * FIXED: orders table uses 'order_date' NOT 'created_at'
 * FIXED: products table uses 'featured' NOT 'is_featured'
 * FIXED: orders uses 'delivery_status' NOT 'order_status'
 * ============================================
 */

$pageTitle = 'Platform Reports & Analytics';
require_once __DIR__ . '/../includes/header.php';
Security::requireAdmin();

$db = new Database();

// ============================================
// USER STATISTICS
// ============================================

// Total users
$db->query("SELECT COUNT(*) as total FROM users");
$totalUsers = $db->fetch()['total'];

// New users this month
$db->query("SELECT COUNT(*) as total FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
$newUsersMonth = $db->fetch()['total'];

// New users this week
$db->query("SELECT COUNT(*) as total FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
$newUsersWeek = $db->fetch()['total'];

// User type breakdown
$db->query("SELECT COUNT(*) as total FROM users WHERE user_type = 'buyer'");
$buyerCount = $db->fetch()['total'];

$db->query("SELECT COUNT(*) as total FROM users WHERE user_type = 'seller'");
$sellerCount = $db->fetch()['total'];

$db->query("SELECT COUNT(*) as total FROM users WHERE user_type = 'both'");
$bothCount = $db->fetch()['total'];

$db->query("SELECT COUNT(*) as total FROM users WHERE user_type = 'moderator'");
$moderatorCount = $db->fetch()['total'];

$db->query("SELECT COUNT(*) as total FROM users WHERE user_type = 'admin'");
$adminCount = $db->fetch()['total'];

// Email verified users
$db->query("SELECT COUNT(*) as total FROM users WHERE email_verified = 1");
$verifiedUsers = $db->fetch()['total'];

// Active users
$db->query("SELECT COUNT(*) as total FROM users WHERE is_active = 1");
$activeUsers = $db->fetch()['total'];

// ============================================
// PRODUCT STATISTICS
// ============================================

// Total products
$db->query("SELECT COUNT(*) as total FROM products");
$totalProducts = $db->fetch()['total'];

// New products this month
$db->query("SELECT COUNT(*) as total FROM products WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
$newProductsMonth = $db->fetch()['total'];

// Active products
$db->query("SELECT COUNT(*) as total FROM products WHERE status = 'active'");
$activeProducts = $db->fetch()['total'];

// Featured products (using 'featured' column)
$db->query("SELECT COUNT(*) as total FROM products WHERE featured = 1");
$featuredProducts = $db->fetch()['total'];

// ============================================
// ORDER STATISTICS (USING order_date)
// ============================================

// Total orders
$db->query("SELECT COUNT(*) as total FROM orders");
$totalOrders = $db->fetch()['total'];

// Orders this month (using order_date)
$db->query("SELECT COUNT(*) as total FROM orders WHERE order_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
$ordersThisMonth = $db->fetch()['total'];

// Orders this week (using order_date)
$db->query("SELECT COUNT(*) as total FROM orders WHERE order_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
$ordersThisWeek = $db->fetch()['total'];

// Order status breakdown (using delivery_status)
$db->query("SELECT COUNT(*) as total FROM orders WHERE delivery_status = 'pending'");
$pendingOrders = $db->fetch()['total'];

$db->query("SELECT COUNT(*) as total FROM orders WHERE delivery_status = 'processing'");
$processingOrders = $db->fetch()['total'];

$db->query("SELECT COUNT(*) as total FROM orders WHERE delivery_status = 'shipped'");
$shippedOrders = $db->fetch()['total'];

$db->query("SELECT COUNT(*) as total FROM orders WHERE delivery_status = 'delivered'");
$deliveredOrders = $db->fetch()['total'];

$db->query("SELECT COUNT(*) as total FROM orders WHERE delivery_status = 'cancelled'");
$cancelledOrders = $db->fetch()['total'];

// ============================================
// REVENUE STATISTICS (USING order_date)
// ============================================

// Total revenue (all paid orders)
$db->query("SELECT SUM(total_amount) as revenue FROM orders WHERE payment_status = 'paid'");
$totalRevenue = $db->fetch()['revenue'] ?? 0;

// Revenue this month (using order_date)
$db->query("SELECT SUM(total_amount) as revenue FROM orders WHERE payment_status = 'paid' AND order_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
$revenueThisMonth = $db->fetch()['revenue'] ?? 0;

// Revenue this week (using order_date)
$db->query("SELECT SUM(total_amount) as revenue FROM orders WHERE payment_status = 'paid' AND order_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
$revenueThisWeek = $db->fetch()['revenue'] ?? 0;

// Average order value
$db->query("SELECT AVG(total_amount) as avg_order FROM orders WHERE payment_status = 'paid'");
$averageOrderValue = $db->fetch()['avg_order'] ?? 0;

// ============================================
// DISPUTE STATISTICS (Appeals are IN disputes table)
// ============================================

// Total disputes
$db->query("SELECT COUNT(*) as total FROM disputes");
$totalDisputes = $db->fetch()['total'];

// Open disputes
$db->query("SELECT COUNT(*) as total FROM disputes WHERE status = 'open'");
$openDisputes = $db->fetch()['total'];

// Resolved disputes
$db->query("SELECT COUNT(*) as total FROM disputes WHERE status = 'resolved'");
$resolvedDisputes = $db->fetch()['total'];

// Total refunded amount
$db->query("SELECT SUM(refund_amount) as total FROM disputes WHERE refund_amount > 0");
$totalRefunded = $db->fetch()['total'] ?? 0;

// Pending appeals (FIXED: using appeal_status from disputes table)
$db->query("SELECT COUNT(*) as total FROM disputes WHERE appeal_status = 'pending'");
$pendingAppeals = $db->fetch()['total'];

// Disputes under appeal
$db->query("SELECT COUNT(*) as total FROM disputes WHERE status = 'under_appeal'");
$disputesUnderAppeal = $db->fetch()['total'];

// ============================================
// TOP SELLERS
// ============================================
$db->query("
    SELECT 
        u.user_id,
        u.email,
        COUNT(DISTINCT o.order_id) as total_sales,
        SUM(o.total_amount) as total_revenue
    FROM users u
    JOIN orders o ON u.user_id = o.seller_id
    WHERE o.payment_status = 'paid'
    GROUP BY u.user_id, u.email
    ORDER BY total_revenue DESC
    LIMIT 10
");
$topSellers = $db->fetchAll();

// ============================================
// TOP BUYERS
// ============================================
$db->query("
    SELECT 
        u.user_id,
        u.email,
        COUNT(DISTINCT o.order_id) as total_orders,
        SUM(o.total_amount) as total_spent
    FROM users u
    JOIN orders o ON u.user_id = o.buyer_id
    WHERE o.payment_status = 'paid'
    GROUP BY u.user_id, u.email
    ORDER BY total_spent DESC
    LIMIT 10
");
$topBuyers = $db->fetchAll();

// ============================================
// RECENT REGISTRATIONS
// ============================================
$db->query("
    SELECT user_id, email, user_type, created_at 
    FROM users 
    ORDER BY created_at DESC 
    LIMIT 10
");
$recentUsers = $db->fetchAll();

// ============================================
// DAILY REVENUE LAST 7 DAYS (USING order_date)
// ============================================
$db->query("
    SELECT 
        DATE(order_date) as order_date,
        COUNT(*) as order_count,
        SUM(total_amount) as daily_revenue
    FROM orders
    WHERE payment_status = 'paid' 
    AND order_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    GROUP BY DATE(order_date)
    ORDER BY order_date DESC
");
$dailyRevenue = $db->fetchAll();

// ============================================
// MONTHLY REVENUE LAST 6 MONTHS (USING order_date)
// ============================================
$db->query("
    SELECT 
        DATE_FORMAT(order_date, '%Y-%m') as month,
        COUNT(*) as order_count,
        SUM(total_amount) as monthly_revenue
    FROM orders
    WHERE payment_status = 'paid'
    AND order_date >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(order_date, '%Y-%m')
    ORDER BY month DESC
");
$monthlyRevenue = $db->fetchAll();

?>

<div class="container-fluid my-4">
    <h1 class="fw-bold mb-4">
        <i class="fas fa-chart-line text-primary"></i> 
        Platform Reports & Analytics
    </h1>

    <!-- OVERVIEW CARDS -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card border-primary shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="fas fa-users fa-3x text-primary mb-2"></i>
                    <h2 class="fw-bold text-primary"><?php echo number_format($totalUsers); ?></h2>
                    <p class="text-muted mb-0">Total Users</p>
                    <small class="text-success">+<?php echo $newUsersMonth; ?> this month</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-success shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="fas fa-shopping-cart fa-3x text-success mb-2"></i>
                    <h2 class="fw-bold text-success"><?php echo number_format($totalOrders); ?></h2>
                    <p class="text-muted mb-0">Total Orders</p>
                    <small class="text-success">+<?php echo $ordersThisMonth; ?> this month</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-info shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="fas fa-box fa-3x text-info mb-2"></i>
                    <h2 class="fw-bold text-info"><?php echo number_format($totalProducts); ?></h2>
                    <p class="text-muted mb-0">Total Products</p>
                    <small class="text-success">+<?php echo $newProductsMonth; ?> this month</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-warning shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="fas fa-money-bill-wave fa-3x text-warning mb-2"></i>
                    <h2 class="fw-bold text-warning">R<?php echo number_format($totalRevenue, 2); ?></h2>
                    <p class="text-muted mb-0">Total Revenue</p>
                    <small class="text-success">R<?php echo number_format($revenueThisMonth, 2); ?> this month</small>
                </div>
            </div>
        </div>
    </div>

    <!-- USER STATISTICS -->
    <div class="row mb-4">
        <div class="col-md-6 mb-3">
            <div class="card shadow h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-users"></i> User Statistics</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tr>
                            <td>Total Users:</td>
                            <td class="text-end"><strong><?php echo number_format($totalUsers); ?></strong></td>
                        </tr>
                        <tr>
                            <td>New This Month:</td>
                            <td class="text-end"><span class="badge bg-success"><?php echo $newUsersMonth; ?></span></td>
                        </tr>
                        <tr>
                            <td>New This Week:</td>
                            <td class="text-end"><span class="badge bg-info"><?php echo $newUsersWeek; ?></span></td>
                        </tr>
                        <tr>
                            <td>Email Verified:</td>
                            <td class="text-end"><strong><?php echo $verifiedUsers; ?></strong> (<?php echo $totalUsers > 0 ? round(($verifiedUsers/$totalUsers)*100) : 0; ?>%)</td>
                        </tr>
                        <tr>
                            <td>Active Users:</td>
                            <td class="text-end"><strong><?php echo $activeUsers; ?></strong> (<?php echo $totalUsers > 0 ? round(($activeUsers/$totalUsers)*100) : 0; ?>%)</td>
                        </tr>
                    </table>
                    
                    <hr>
                    
                    <h6 class="fw-bold mb-3">User Type Breakdown:</h6>
                    <table class="table table-sm mb-0">
                        <tr>
                            <td>Buyers:</td>
                            <td class="text-end"><span class="badge bg-primary"><?php echo $buyerCount; ?></span></td>
                        </tr>
                        <tr>
                            <td>Sellers:</td>
                            <td class="text-end"><span class="badge bg-success"><?php echo $sellerCount; ?></span></td>
                        </tr>
                        <tr>
                            <td>Both (Buyer & Seller):</td>
                            <td class="text-end"><span class="badge bg-info"><?php echo $bothCount; ?></span></td>
                        </tr>
                        <tr>
                            <td>Moderators:</td>
                            <td class="text-end"><span class="badge bg-warning"><?php echo $moderatorCount; ?></span></td>
                        </tr>
                        <tr>
                            <td>Admins:</td>
                            <td class="text-end"><span class="badge bg-danger"><?php echo $adminCount; ?></span></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-3">
            <div class="card shadow h-100">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-shopping-cart"></i> Order Statistics</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tr>
                            <td>Total Orders:</td>
                            <td class="text-end"><strong><?php echo number_format($totalOrders); ?></strong></td>
                        </tr>
                        <tr>
                            <td>Orders This Month:</td>
                            <td class="text-end"><span class="badge bg-success"><?php echo $ordersThisMonth; ?></span></td>
                        </tr>
                        <tr>
                            <td>Orders This Week:</td>
                            <td class="text-end"><span class="badge bg-info"><?php echo $ordersThisWeek; ?></span></td>
                        </tr>
                        <tr>
                            <td>Average Order Value:</td>
                            <td class="text-end"><strong class="text-success">R<?php echo number_format($averageOrderValue, 2); ?></strong></td>
                        </tr>
                    </table>
                    
                    <hr>
                    
                    <h6 class="fw-bold mb-3">Delivery Status:</h6>
                    <table class="table table-sm mb-0">
                        <tr>
                            <td>Pending:</td>
                            <td class="text-end"><span class="badge bg-warning text-dark"><?php echo $pendingOrders; ?></span></td>
                        </tr>
                        <tr>
                            <td>Processing:</td>
                            <td class="text-end"><span class="badge bg-info"><?php echo $processingOrders; ?></span></td>
                        </tr>
                        <tr>
                            <td>Shipped:</td>
                            <td class="text-end"><span class="badge bg-primary"><?php echo $shippedOrders; ?></span></td>
                        </tr>
                        <tr>
                            <td>Delivered:</td>
                            <td class="text-end"><span class="badge bg-success"><?php echo $deliveredOrders; ?></span></td>
                        </tr>
                        <tr>
                            <td>Cancelled:</td>
                            <td class="text-end"><span class="badge bg-danger"><?php echo $cancelledOrders; ?></span></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- REVENUE AND PRODUCTS -->
    <div class="row mb-4">
        <div class="col-md-6 mb-3">
            <div class="card shadow h-100">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="fas fa-money-bill-wave"></i> Revenue Analysis</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tr>
                            <td>Total Revenue (All Time):</td>
                            <td class="text-end"><strong class="text-success">R<?php echo number_format($totalRevenue, 2); ?></strong></td>
                        </tr>
                        <tr>
                            <td>Revenue This Month:</td>
                            <td class="text-end"><strong class="text-success">R<?php echo number_format($revenueThisMonth, 2); ?></strong></td>
                        </tr>
                        <tr>
                            <td>Revenue This Week:</td>
                            <td class="text-end"><strong class="text-success">R<?php echo number_format($revenueThisWeek, 2); ?></strong></td>
                        </tr>
                        <tr>
                            <td>Average Order Value:</td>
                            <td class="text-end"><strong>R<?php echo number_format($averageOrderValue, 2); ?></strong></td>
                        </tr>
                        <tr>
                            <td colspan="2" class="text-muted pt-3">
                                <small><i class="fas fa-info-circle"></i> Revenue from paid orders only</small>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-3">
            <div class="card shadow h-100">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-box"></i> Product Statistics</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tr>
                            <td>Total Products:</td>
                            <td class="text-end"><strong><?php echo number_format($totalProducts); ?></strong></td>
                        </tr>
                        <tr>
                            <td>New This Month:</td>
                            <td class="text-end"><span class="badge bg-success"><?php echo $newProductsMonth; ?></span></td>
                        </tr>
                        <tr>
                            <td>Active Products:</td>
                            <td class="text-end"><strong><?php echo $activeProducts; ?></strong> (<?php echo $totalProducts > 0 ? round(($activeProducts/$totalProducts)*100) : 0; ?>%)</td>
                        </tr>
                        <tr>
                            <td>Featured Products:</td>
                            <td class="text-end"><span class="badge bg-warning text-dark"><?php echo $featuredProducts; ?></span></td>
                        </tr>
                        <tr>
                            <td colspan="2" class="text-muted pt-3">
                                <small><i class="fas fa-info-circle"></i> Inactive/sold products: <?php echo $totalProducts - $activeProducts; ?></small>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- DISPUTE STATISTICS -->
    <div class="row mb-4">
        <div class="col-md-12 mb-3">
            <div class="card shadow">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0"><i class="fas fa-exclamation-triangle"></i> Dispute & Refund Statistics</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <div class="text-center p-3 border rounded bg-light">
                                <h3 class="text-danger mb-2"><?php echo $totalDisputes; ?></h3>
                                <p class="mb-0 fw-bold">Total Disputes</p>
                                <small class="text-muted">All time</small>
                            </div>
                        </div>
                        <div class="col-md-2 mb-3">
                            <div class="text-center p-3 border rounded bg-light">
                                <h3 class="text-warning mb-2"><?php echo $openDisputes; ?></h3>
                                <p class="mb-0 fw-bold">Open Disputes</p>
                                <small class="text-muted">Needs attention</small>
                            </div>
                        </div>
                        <div class="col-md-2 mb-3">
                            <div class="text-center p-3 border rounded bg-light">
                                <h3 class="text-success mb-2"><?php echo $resolvedDisputes; ?></h3>
                                <p class="mb-0 fw-bold">Resolved</p>
                                <small class="text-muted">Completed</small>
                            </div>
                        </div>
                        <div class="col-md-2 mb-3">
                            <div class="text-center p-3 border rounded bg-light">
                                <h3 class="text-primary mb-2"><?php echo $disputesUnderAppeal; ?></h3>
                                <p class="mb-0 fw-bold">Under Appeal</p>
                                <small class="text-muted">Being reviewed</small>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="text-center p-3 border rounded bg-light">
                                <h3 class="text-info mb-2"><?php echo $pendingAppeals; ?></h3>
                                <p class="mb-0 fw-bold">Pending Appeals</p>
                                <small class="text-muted">Awaiting review</small>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3 text-center p-3 bg-light rounded">
                        <p class="mb-0">Total Amount Refunded: <strong class="text-danger fs-4">R<?php echo number_format($totalRefunded, 2); ?></strong></p>
                        <small class="text-muted">Across all resolved disputes</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- TOP SELLERS -->
    <div class="row mb-4">
        <div class="col-md-12 mb-3">
            <div class="card shadow">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-trophy"></i> Top 10 Sellers by Revenue</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th width="80">Rank</th>
                                    <th>Seller Email</th>
                                    <th width="150">Total Sales</th>
                                    <th width="200">Total Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(!empty($topSellers)): ?>
                                    <?php $rank = 1; foreach($topSellers as $seller): ?>
                                    <tr>
                                        <td class="text-center">
                                            <?php if($rank <= 3): ?>
                                                <span class="badge bg-warning text-dark fs-6"><?php echo $rank; ?></span>
                                            <?php else: ?>
                                                <strong><?php echo $rank; ?></strong>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($seller['email']); ?></td>
                                        <td><span class="badge bg-info"><?php echo $seller['total_sales']; ?> orders</span></td>
                                        <td class="fw-bold text-success">R<?php echo number_format($seller['total_revenue'], 2); ?></td>
                                    </tr>
                                    <?php $rank++; endforeach; ?>
                                <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">
                                        <i class="fas fa-info-circle"></i> No sales data available yet
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- TOP BUYERS -->
    <div class="row mb-4">
        <div class="col-md-12 mb-3">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-users"></i> Top 10 Buyers by Spending</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th width="80">Rank</th>
                                    <th>Buyer Email</th>
                                    <th width="150">Total Orders</th>
                                    <th width="200">Total Spent</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(!empty($topBuyers)): ?>
                                    <?php $rank = 1; foreach($topBuyers as $buyer): ?>
                                    <tr>
                                        <td class="text-center">
                                            <?php if($rank <= 3): ?>
                                                <span class="badge bg-warning text-dark fs-6"><?php echo $rank; ?></span>
                                            <?php else: ?>
                                                <strong><?php echo $rank; ?></strong>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($buyer['email']); ?></td>
                                        <td><span class="badge bg-info"><?php echo $buyer['total_orders']; ?> orders</span></td>
                                        <td class="fw-bold text-primary">R<?php echo number_format($buyer['total_spent'], 2); ?></td>
                                    </tr>
                                    <?php $rank++; endforeach; ?>
                                <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">
                                        <i class="fas fa-info-circle"></i> No buyer data available yet
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- DAILY REVENUE -->
    <div class="row mb-4">
        <div class="col-md-6 mb-3">
            <div class="card shadow h-100">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-calendar-day"></i> Daily Revenue (Last 7 Days)</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th width="120">Orders</th>
                                    <th width="180">Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(!empty($dailyRevenue)): ?>
                                    <?php foreach($dailyRevenue as $day): ?>
                                    <tr>
                                        <td><?php echo date('D, M j, Y', strtotime($day['order_date'])); ?></td>
                                        <td><span class="badge bg-info"><?php echo $day['order_count']; ?></span></td>
                                        <td class="fw-bold">R<?php echo number_format($day['daily_revenue'], 2); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-3">
                                        <i class="fas fa-info-circle"></i> No revenue in last 7 days
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-3">
            <div class="card shadow h-100">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="fas fa-calendar-alt"></i> Monthly Revenue (Last 6 Months)</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Month</th>
                                    <th width="120">Orders</th>
                                    <th width="180">Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(!empty($monthlyRevenue)): ?>
                                    <?php foreach($monthlyRevenue as $month): ?>
                                    <tr>
                                        <td><?php echo date('F Y', strtotime($month['month'] . '-01')); ?></td>
                                        <td><span class="badge bg-warning text-dark"><?php echo $month['order_count']; ?></span></td>
                                        <td class="fw-bold text-success">R<?php echo number_format($month['monthly_revenue'], 2); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-3">
                                        <i class="fas fa-info-circle"></i> No revenue in last 6 months
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- RECENT REGISTRATIONS -->
    <div class="row mb-4">
        <div class="col-md-12 mb-3">
            <div class="card shadow">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0"><i class="fas fa-history"></i> Recent User Registrations</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Email</th>
                                    <th width="150">User Type</th>
                                    <th width="250">Registered</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(!empty($recentUsers)): ?>
                                    <?php foreach($recentUsers as $user): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td>
                                            <?php
                                            $badgeClass = match($user['user_type']) {
                                                'buyer' => 'bg-primary',
                                                'seller' => 'bg-success',
                                                'both' => 'bg-info',
                                                'moderator' => 'bg-warning text-dark',
                                                'admin' => 'bg-danger',
                                                default => 'bg-secondary'
                                            };
                                            ?>
                                            <span class="badge <?php echo $badgeClass; ?>"><?php echo ucfirst($user['user_type']); ?></span>
                                        </td>
                                        <td><?php echo date('M j, Y g:i A', strtotime($user['created_at'])); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-3">
                                        <i class="fas fa-info-circle"></i> No user registrations yet
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- PLATFORM HEALTH SUMMARY -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0"><i class="fas fa-heartbeat"></i> Platform Health Summary</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <h6 class="fw-bold">User Engagement</h6>
                            <div class="progress mb-2" style="height: 25px;">
                                <?php $verifiedPercentage = $totalUsers > 0 ? ($verifiedUsers/$totalUsers)*100 : 0; ?>
                                <div class="progress-bar bg-success" style="width: <?php echo $verifiedPercentage; ?>%">
                                    <?php echo round($verifiedPercentage); ?>% Verified
                                </div>
                            </div>
                            <small class="text-muted"><?php echo $verifiedUsers; ?> of <?php echo $totalUsers; ?> users verified</small>
                        </div>
                        <div class="col-md-4 mb-3">
                            <h6 class="fw-bold">Order Fulfillment</h6>
                            <div class="progress mb-2" style="height: 25px;">
                                <?php $deliveredPercentage = $totalOrders > 0 ? ($deliveredOrders/$totalOrders)*100 : 0; ?>
                                <div class="progress-bar bg-info" style="width: <?php echo $deliveredPercentage; ?>%">
                                    <?php echo round($deliveredPercentage); ?>% Delivered
                                </div>
                            </div>
                            <small class="text-muted"><?php echo $deliveredOrders; ?> of <?php echo $totalOrders; ?> orders delivered</small>
                        </div>
                        <div class="col-md-4 mb-3">
                            <h6 class="fw-bold">Product Activity</h6>
                            <div class="progress mb-2" style="height: 25px;">
                                <?php $activePercentage = $totalProducts > 0 ? ($activeProducts/$totalProducts)*100 : 0; ?>
                                <div class="progress-bar bg-primary" style="width: <?php echo $activePercentage; ?>%">
                                    <?php echo round($activePercentage); ?>% Active
                                </div>
                            </div>
                            <small class="text-muted"><?php echo $activeProducts; ?> of <?php echo $totalProducts; ?> products active</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
