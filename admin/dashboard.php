<?php
$pageTitle='Admin Dashboard';
require_once __DIR__.'/../includes/header.php';
Security::requireAdmin();

$db=new Database();

// Get statistics
$db->query("SELECT COUNT(*) as total FROM users");
$totalUsers=$db->fetch()['total'];

$db->query("SELECT COUNT(*) as total FROM products WHERE status='active'");
$totalProducts=$db->fetch()['total'];

$db->query("SELECT COUNT(*) as total FROM orders");
$totalOrders=$db->fetch()['total'];

$db->query("SELECT COUNT(*) as total FROM users WHERE user_type IN('seller','both','moderator','admin') AND account_status='suspended' AND suspension_reason LIKE '%approval%'");
$pendingApprovals=$db->fetch()['total'];

$db->query("SELECT COUNT(*) as total FROM verification_documents WHERE verification_status='pending'");
$pendingDocs=$db->fetch()['total'];

// FIXED: Count ALL active disputes (open, investigating, under_appeal)
$db->query("SELECT COUNT(*) as total FROM disputes WHERE status IN ('open', 'investigating', 'under_appeal')");
$openDisputes=$db->fetch()['total'];

// NEW: Count appeal-specific disputes
$db->query("SELECT COUNT(*) as total FROM disputes WHERE status='under_appeal' OR appeal_status='pending'");
$pendingAppeals=$db->fetch()['total'];

// Recent users
$db->query("SELECT user_id,full_name,email,user_type,created_at FROM users ORDER BY created_at DESC LIMIT 5");
$recentUsers=$db->fetchAll();

// Recent orders
$db->query("SELECT o.order_id,o.total_amount,o.order_date,p.product_name,u.full_name as buyer_name FROM orders o JOIN products p ON o.product_id=p.product_id JOIN users u ON o.buyer_id=u.user_id ORDER BY o.order_date DESC LIMIT 5");
$recentOrders=$db->fetchAll();
?>

<div class="container my-5">
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold"><i class="fas fa-tachometer-alt text-primary"></i> Admin Dashboard</h2>
    <div>
        <span class="badge bg-success">Administrator Access</span>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card text-white bg-primary h-100">
            <div class="card-body text-center">
                <i class="fas fa-users fa-3x mb-3"></i>
                <h2 class="fw-bold"><?php echo $totalUsers; ?></h2>
                <p class="mb-0">Total Users</p>
            </div>
            <div class="card-footer bg-transparent border-0">
                <a href="<?php echo APP_URL; ?>/admin/users.php" class="btn btn-light btn-sm w-100">View All</a>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card text-white bg-success h-100">
            <div class="card-body text-center">
                <i class="fas fa-box fa-3x mb-3"></i>
                <h2 class="fw-bold"><?php echo $totalProducts; ?></h2>
                <p class="mb-0">Active Products</p>
            </div>
            <div class="card-footer bg-transparent border-0">
                <a href="<?php echo APP_URL; ?>/admin/products.php" class="btn btn-light btn-sm w-100">View All</a>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card text-white bg-info h-100">
            <div class="card-body text-center">
                <i class="fas fa-shopping-cart fa-3x mb-3"></i>
                <h2 class="fw-bold"><?php echo $totalOrders; ?></h2>
                <p class="mb-0">Total Orders</p>
            </div>
            <div class="card-footer bg-transparent border-0">
                <a href="<?php echo APP_URL; ?>/admin/orders.php" class="btn btn-light btn-sm w-100">View All</a>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card text-white bg-warning h-100">
            <div class="card-body text-center">
                <i class="fas fa-user-check fa-3x mb-3"></i>
                <h2 class="fw-bold"><?php echo $pendingApprovals; ?></h2>
                <p class="mb-0">Pending Approvals</p>
            </div>
            <div class="card-footer bg-transparent border-0">
                <a href="<?php echo APP_URL; ?>/admin/pending-approvals.php" class="btn btn-light btn-sm w-100">Review Now</a>
            </div>
        </div>
    </div>
</div>

<!-- Action Cards -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card border-primary">
            <div class="card-body text-center">
                <i class="fas fa-id-card text-primary fa-3x mb-3"></i>
                <h5>Document Verification</h5>
                <p class="text-muted"><?php echo $pendingDocs; ?> pending documents</p>
                <a href="<?php echo APP_URL; ?>/admin/verify-documents.php" class="btn btn-primary">Verify Now</a>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card border-danger">
            <div class="card-body text-center">
                <i class="fas fa-gavel text-danger fa-3x mb-3"></i>
                <h5>Disputes</h5>
                <p class="text-muted"><?php echo $openDisputes; ?> active disputes</p>
                <a href="<?php echo APP_URL; ?>/admin/disputes.php" class="btn btn-danger">Manage Disputes</a>
            </div>
        </div>
    </div>
    
    <!-- NEW: Appeals Card -->
    <div class="col-md-3 mb-3">
        <div class="card border-warning">
            <div class="card-body text-center">
                <i class="fas fa-redo text-warning fa-3x mb-3"></i>
                <h5>Pending Appeals</h5>
                <p class="text-muted"><?php echo $pendingAppeals; ?> appeals need review</p>
                <a href="<?php echo APP_URL; ?>/admin/disputes.php?filter=pending_appeals" class="btn btn-warning">Review Appeals</a>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card border-success">
            <div class="card-body text-center">
                <i class="fas fa-chart-bar text-success fa-3x mb-3"></i>
                <h5>Reports</h5>
                <p class="text-muted">Platform analytics</p>
                <a href="<?php echo APP_URL; ?>/admin/reports.php" class="btn btn-success">View Reports</a>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity -->
<div class="row">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-user-plus"></i> Recent Users</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Name</th>
                                <th>Type</th>
                                <th>Registered</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($recentUsers as $u): ?>
                            <tr>
                                <td>
                                    <strong><?php echo Security::clean($u['full_name']); ?></strong><br>
                                    <small class="text-muted"><?php echo Security::clean($u['email']); ?></small>
                                </td>
                                <td><span class="badge bg-info"><?php echo ucfirst($u['user_type']); ?></span></td>
                                <td><?php echo time_ago($u['created_at']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-shopping-bag"></i> Recent Orders</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Order #</th>
                                <th>Product</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($recentOrders as $o): ?>
                            <tr>
                                <td><strong>#<?php echo $o['order_id']; ?></strong></td>
                                <td>
                                    <?php echo excerpt(Security::clean($o['product_name']), 30); ?><br>
                                    <small class="text-muted"><?php echo Security::clean($o['buyer_name']); ?></small>
                                </td>
                                <td class="text-success fw-bold"><?php echo format_currency($o['total_amount']); ?></td>
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
