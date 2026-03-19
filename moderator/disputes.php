<?php
/**
 * ============================================
 * MODERATOR - DISPUTES LIST
 * ============================================
 * Moderator view of all disputes they can handle
 * ============================================
 */

$pageTitle = 'Disputes';
require_once __DIR__.'/../includes/header.php';
Security::requireModerator();

$db = new Database();

// Get filter from URL (default: open)
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'open';

// Build query based on filter
$whereClause = '';
switch($filter) {
    case 'open':
        $whereClause = "WHERE d.status IN ('open', 'investigating')";
        break;
    case 'investigating':
        $whereClause = "WHERE d.status = 'investigating'";
        break;
    case 'resolved':
        $whereClause = "WHERE d.status IN ('resolved', 'closed', 'appeal_resolved')";
        break;
    case 'under_appeal':
        $whereClause = "WHERE d.status = 'under_appeal'";
        break;
    default:
        $whereClause = '';
}

// Fetch disputes
$db->query("SELECT d.*, 
            o.order_id,
            p.product_name,
            buyer.full_name as buyer_name,
            buyer.email as buyer_email,
            seller.full_name as seller_name,
            seller.email as seller_email
            FROM disputes d
            JOIN orders o ON d.order_id = o.order_id
            JOIN products p ON o.product_id = p.product_id
            JOIN users buyer ON o.buyer_id = buyer.user_id
            JOIN users seller ON o.seller_id = seller.user_id
            $whereClause
            ORDER BY 
                CASE 
                    WHEN d.status = 'open' THEN 1
                    WHEN d.status = 'investigating' THEN 2
                    WHEN d.status = 'under_appeal' THEN 3
                    ELSE 4
                END,
                d.created_at DESC");
$disputes = $db->fetchAll();

// Get statistics
$db->query("SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status IN ('open', 'investigating') THEN 1 ELSE 0 END) as active,
            SUM(CASE WHEN status = 'investigating' THEN 1 ELSE 0 END) as investigating,
            SUM(CASE WHEN status IN ('resolved', 'closed', 'appeal_resolved') THEN 1 ELSE 0 END) as resolved,
            SUM(CASE WHEN status = 'under_appeal' THEN 1 ELSE 0 END) as under_appeal
            FROM disputes");
$stats = $db->fetch();
?>

<div class="container-fluid my-4">
    <div class="row mb-4">
        <div class="col-12">
            <a href="<?php echo APP_URL; ?>/moderator/dashboard.php" class="btn btn-secondary mb-3">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
            <h2 class="fw-bold">
                <i class="fas fa-gavel text-warning"></i> Disputes Management
            </h2>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="mb-0"><?php echo $stats['total']; ?></h3>
                    <small class="text-muted">Total Disputes</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-danger">
                <div class="card-body">
                    <h3 class="mb-0 text-danger"><?php echo $stats['active']; ?></h3>
                    <small class="text-muted">Active Disputes</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-warning">
                <div class="card-body">
                    <h3 class="mb-0 text-warning"><?php echo $stats['investigating']; ?></h3>
                    <small class="text-muted">Investigating</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-success">
                <div class="card-body">
                    <h3 class="mb-0 text-success"><?php echo $stats['resolved']; ?></h3>
                    <small class="text-muted">Resolved</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Tabs -->
    <ul class="nav nav-tabs mb-3">
        <li class="nav-item">
            <a class="nav-link <?php echo $filter === 'all' ? 'active' : ''; ?>" 
               href="?filter=all">
                All (<?php echo $stats['total']; ?>)
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $filter === 'open' ? 'active' : ''; ?>" 
               href="?filter=open">
                <i class="fas fa-exclamation-circle text-danger"></i> Active (<?php echo $stats['active']; ?>)
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $filter === 'investigating' ? 'active' : ''; ?>" 
               href="?filter=investigating">
                <i class="fas fa-search text-warning"></i> Investigating (<?php echo $stats['investigating']; ?>)
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $filter === 'resolved' ? 'active' : ''; ?>" 
               href="?filter=resolved">
                <i class="fas fa-check-circle text-success"></i> Resolved (<?php echo $stats['resolved']; ?>)
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $filter === 'under_appeal' ? 'active' : ''; ?>" 
               href="?filter=under_appeal">
                <i class="fas fa-redo text-info"></i> Under Appeal (<?php echo $stats['under_appeal']; ?>)
            </a>
        </li>
    </ul>

    <?php if (empty($disputes)): ?>
    <div class="alert alert-info text-center">
        <i class="fas fa-info-circle fa-3x mb-3"></i>
        <h5>No disputes in this category</h5>
    </div>
    <?php else: ?>

    <!-- Disputes Table -->
    <div class="table-responsive">
        <table class="table table-hover table-striped">
            <thead class="table-dark">
                <tr>
                    <th>Dispute #</th>
                    <th>Product</th>
                    <th>Buyer</th>
                    <th>Seller</th>
                    <th>Reason</th>
                    <th>Status</th>
                    <th>Stage</th>
                    <th>Filed</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($disputes as $d): ?>
                <?php
                $statusColors = [
                    'open' => 'danger',
                    'investigating' => 'warning',
                    'resolved' => 'success',
                    'closed' => 'secondary',
                    'under_appeal' => 'info',
                    'appeal_resolved' => 'primary'
                ];
                $statusColor = $statusColors[$d['status']] ?? 'secondary';
                ?>
                <tr>
                    <td><strong>#<?php echo $d['dispute_id']; ?></strong></td>
                    <td>
                        <?php echo excerpt(Security::clean($d['product_name']), 30); ?>
                        <br>
                        <small class="text-muted">Order #<?php echo $d['order_id']; ?></small>
                    </td>
                    <td>
                        <?php echo Security::clean($d['buyer_name']); ?>
                        <br>
                        <small class="text-muted"><?php echo Security::clean($d['buyer_email']); ?></small>
                    </td>
                    <td>
                        <?php echo Security::clean($d['seller_name']); ?>
                        <br>
                        <small class="text-muted"><?php echo Security::clean($d['seller_email']); ?></small>
                    </td>
                    <td>
                        <span class="badge bg-secondary">
                            <?php echo ucfirst(str_replace('_', ' ', $d['dispute_reason'])); ?>
                        </span>
                    </td>
                    <td>
                        <span class="badge bg-<?php echo $statusColor; ?>">
                            <?php echo ucfirst(str_replace('_', ' ', $d['status'])); ?>
                        </span>
                    </td>
                    <td>
                        <small class="text-muted">
                            <?php echo ucwords(str_replace('_', ' ', $d['stage'])); ?>
                        </small>
                    </td>
                    <td>
                        <small><?php echo time_ago($d['created_at']); ?></small>
                    </td>
                    <td>
                        <a href="<?php echo APP_URL; ?>/moderator/resolve-dispute.php?id=<?php echo $d['dispute_id']; ?>" 
                           class="btn btn-primary btn-sm">
                            <i class="fas fa-gavel"></i> Handle
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php endif; ?>
</div>

<?php require_once __DIR__.'/../includes/footer.php'; ?>
