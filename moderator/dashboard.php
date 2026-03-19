<?php
$pageTitle='Moderator Dashboard';
require_once __DIR__.'/../includes/header.php';
Security::requireModerator();

$db=new Database();

// FIXED: Count ALL active disputes that need moderator attention
$db->query("SELECT COUNT(*) as total FROM disputes WHERE status IN ('open', 'investigating')");
$openDisputes=$db->fetch()['total'];

$db->query("SELECT COUNT(*) as total FROM disputes WHERE status='investigating'");
$investigating=$db->fetch()['total'];

// NEW: Count resolved disputes (for reference)
$db->query("SELECT COUNT(*) as total FROM disputes WHERE status IN ('resolved', 'closed', 'appeal_resolved')");
$resolvedDisputes=$db->fetch()['total'];

// Get recent disputes - ALL statuses moderator can see
$db->query("SELECT d.*,
            o.order_id,
            p.product_name,
            buyer.email as buyer_name,
            seller.email as seller_name 
            FROM disputes d 
            JOIN orders o ON d.order_id=o.order_id 
            JOIN products p ON o.product_id=p.product_id 
            JOIN users buyer ON o.buyer_id=buyer.user_id 
            JOIN users seller ON o.seller_id=seller.user_id 
            ORDER BY d.created_at DESC 
            LIMIT 10");
$recentDisputes=$db->fetchAll();
?>

<div class="container my-5">
<h2 class="fw-bold mb-4"><i class="fas fa-shield-alt text-warning"></i> Moderator Dashboard</h2>

<!-- Stats Cards -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card text-white bg-danger text-center h-100">
            <div class="card-body">
                <i class="fas fa-exclamation-circle fa-3x mb-3"></i>
                <h2><?php echo $openDisputes; ?></h2>
                <p class="mb-0">Active Disputes</p>
            </div>
            <div class="card-footer bg-transparent border-0">
                <!-- FIXED: Changed from /admin/disputes.php to /moderator/disputes.php -->
                <a href="<?php echo APP_URL; ?>/moderator/disputes.php?filter=open" class="btn btn-light btn-sm w-100">View All</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-warning text-center h-100">
            <div class="card-body">
                <i class="fas fa-search fa-3x mb-3"></i>
                <h2><?php echo $investigating; ?></h2>
                <p class="mb-0">Under Investigation</p>
            </div>
            <div class="card-footer bg-transparent border-0">
                <!-- FIXED: Changed from /admin/disputes.php to /moderator/disputes.php -->
                <a href="<?php echo APP_URL; ?>/moderator/disputes.php?filter=investigating" class="btn btn-light btn-sm w-100">View All</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-success text-center h-100">
            <div class="card-body">
                <i class="fas fa-check-circle fa-3x mb-3"></i>
                <h2><?php echo $resolvedDisputes; ?></h2>
                <p class="mb-0">Resolved</p>
            </div>
            <div class="card-footer bg-transparent border-0">
                <!-- FIXED: Changed from /admin/disputes.php to /moderator/disputes.php -->
                <a href="<?php echo APP_URL; ?>/moderator/disputes.php?filter=resolved" class="btn btn-light btn-sm w-100">View All</a>
            </div>
        </div>
    </div>
</div>

<!-- Recent Disputes Table -->
<div class="card shadow">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="fas fa-list"></i> Recent Disputes</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Dispute #</th>
                        <th>Product</th>
                        <th>Buyer</th>
                        <th>Seller</th>
                        <th>Reason</th>
                        <th>Status</th>
                        <th>Filed</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($recentDisputes)): ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            <i class="fas fa-inbox fa-2x mb-2"></i>
                            <p class="mb-0">No disputes yet</p>
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach($recentDisputes as $d): ?>
                    <?php
                    // Determine status color
                    $statusColor = 'secondary';
                    if ($d['status'] === 'open') $statusColor = 'danger';
                    elseif ($d['status'] === 'investigating') $statusColor = 'warning';
                    elseif ($d['status'] === 'resolved') $statusColor = 'success';
                    elseif ($d['status'] === 'under_appeal') $statusColor = 'info';
                    elseif ($d['status'] === 'appeal_resolved') $statusColor = 'primary';
                    ?>
                    <tr>
                        <td><strong>#<?php echo $d['dispute_id']; ?></strong></td>
                        <td><?php echo excerpt(Security::clean($d['product_name']),30); ?></td>
                        <td><?php echo Security::clean($d['buyer_name']); ?></td>
                        <td><?php echo Security::clean($d['seller_name']); ?></td>
                        <td>
                            <span class="badge bg-secondary">
                                <?php echo ucfirst(str_replace('_',' ',$d['dispute_reason'])); ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-<?php echo $statusColor; ?>">
                                <?php echo ucfirst(str_replace('_', ' ', $d['status'])); ?>
                            </span>
                        </td>
                        <td><?php echo time_ago($d['created_at']); ?></td>
                        <td>
                            <!-- FIXED: Changed from /disputes/view.php to /moderator/resolve-dispute.php -->
                            <a href="<?php echo APP_URL; ?>/moderator/resolve-dispute.php?id=<?php echo $d['dispute_id']; ?>" 
                               class="btn btn-sm btn-primary">
                                <i class="fas fa-gavel"></i> Handle
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <?php if(!empty($recentDisputes)): ?>
    <div class="card-footer text-center">
        <!-- FIXED: Added link to view all disputes -->
        <a href="<?php echo APP_URL; ?>/moderator/disputes.php" class="btn btn-primary">
            <i class="fas fa-list"></i> View All Disputes
        </a>
    </div>
    <?php endif; ?>
</div>

</div>

<?php require_once __DIR__.'/../includes/footer.php'; ?>
