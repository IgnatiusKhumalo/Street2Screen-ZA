<?php
/**
 * ============================================
 * ADMIN - DISPUTES MANAGEMENT + APPEALS
 * ============================================
 * UPDATED: Added Appeals filter tab
 * NOTHING REMOVED - Only appeals functionality added
 * ============================================
 */

session_start();
require_once __DIR__.'/../config/database.php';
require_once __DIR__.'/../includes/Database.php';
require_once __DIR__.'/../includes/Security.php';
require_once __DIR__.'/../includes/functions.php';

Security::requireAdmin();

$db = new Database();
$pageTitle = 'Dispute Management';

// Get filter from URL (default: all)
$filter = get_get('filter', 'all');

// Build query based on filter
$whereClause = '';
switch($filter) {
    case 'open':
        $whereClause = "WHERE d.status = 'open'";
        break;
    case 'investigating':
        $whereClause = "WHERE d.status = 'investigating'";
        break;
    case 'resolved':
        $whereClause = "WHERE d.status = 'resolved'";
        break;
    case 'appeals':
        // NEW: Appeals filter - shows both pending and resolved appeals
        $whereClause = "WHERE (d.status = 'under_appeal' OR d.appeal_status IN ('pending', 'investigating', 'resolved'))";
        break;
    case 'pending_appeals':
        // Only appeals that need admin action
        $whereClause = "WHERE d.status = 'under_appeal' AND d.appeal_status = 'pending'";
        break;
    default:
        $whereClause = '';
}

// Fetch disputes
$db->query("SELECT d.*, 
            o.order_id,
            o.buyer_id,
            o.seller_id,
            o.total_amount,
            p.product_name,
            buyer.full_name as buyer_name,
            seller.full_name as seller_name,
            reporter.full_name as reporter_name
            FROM disputes d
            JOIN orders o ON d.order_id = o.order_id
            JOIN products p ON o.product_id = p.product_id
            JOIN users buyer ON o.buyer_id = buyer.user_id
            JOIN users seller ON o.seller_id = seller.user_id
            JOIN users reporter ON d.reported_by = reporter.user_id
            $whereClause
            ORDER BY 
                CASE 
                    WHEN d.status = 'under_appeal' THEN 1
                    WHEN d.status = 'open' THEN 2
                    WHEN d.status = 'investigating' THEN 3
                    ELSE 4
                END,
                d.created_at DESC");
$disputes = $db->fetchAll();

// Calculate statistics
$stats = [
    'total' => 0,
    'open' => 0,
    'investigating' => 0,
    'resolved' => 0,
    'appeals' => 0,
    'pending_appeals' => 0
];

$db->query("SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'open' THEN 1 ELSE 0 END) as open,
            SUM(CASE WHEN status = 'investigating' THEN 1 ELSE 0 END) as investigating,
            SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved,
            SUM(CASE WHEN status = 'under_appeal' OR appeal_status != 'none' THEN 1 ELSE 0 END) as appeals,
            SUM(CASE WHEN status = 'under_appeal' AND appeal_status = 'pending' THEN 1 ELSE 0 END) as pending_appeals
            FROM disputes");
$statsRow = $db->fetch();
if ($statsRow) {
    $stats = $statsRow;
}

require_once __DIR__.'/../includes/header.php';
?>

<div class="container-fluid my-4">
    <h2 class="fw-bold mb-4">
        <i class="fas fa-gavel text-danger"></i> Dispute Management
    </h2>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-2">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="mb-0"><?php echo $stats['total']; ?></h3>
                    <small class="text-muted">Total Disputes</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card text-center border-danger">
                <div class="card-body">
                    <h3 class="mb-0 text-danger"><?php echo $stats['open']; ?></h3>
                    <small class="text-muted">Open</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card text-center border-warning">
                <div class="card-body">
                    <h3 class="mb-0 text-warning"><?php echo $stats['investigating']; ?></h3>
                    <small class="text-muted">Investigating</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card text-center border-success">
                <div class="card-body">
                    <h3 class="mb-0 text-success"><?php echo $stats['resolved']; ?></h3>
                    <small class="text-muted">Resolved</small>
                </div>
            </div>
        </div>
        <!-- NEW: Appeals Statistics -->
        <div class="col-md-2">
            <div class="card text-center border-info">
                <div class="card-body">
                    <h3 class="mb-0 text-info"><?php echo $stats['appeals']; ?></h3>
                    <small class="text-muted">All Appeals</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card text-center border-primary" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="card-body text-white">
                    <h3 class="mb-0"><?php echo $stats['pending_appeals']; ?></h3>
                    <small>⚠️ Pending Appeals</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Tabs -->
    <ul class="nav nav-tabs mb-3">
        <li class="nav-item">
            <a class="nav-link <?php echo $filter === 'all' ? 'active' : ''; ?>" 
               href="?filter=all">
                All Disputes (<?php echo $stats['total']; ?>)
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $filter === 'open' ? 'active' : ''; ?>" 
               href="?filter=open">
                Open (<?php echo $stats['open']; ?>)
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $filter === 'investigating' ? 'active' : ''; ?>" 
               href="?filter=investigating">
                Investigating (<?php echo $stats['investigating']; ?>)
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $filter === 'resolved' ? 'active' : ''; ?>" 
               href="?filter=resolved">
                Resolved (<?php echo $stats['resolved']; ?>)
            </a>
        </li>
        <!-- NEW: Appeals Tab -->
        <li class="nav-item">
            <a class="nav-link <?php echo $filter === 'appeals' ? 'active' : ''; ?> text-info fw-bold" 
               href="?filter=appeals">
                <i class="fas fa-redo"></i> All Appeals (<?php echo $stats['appeals']; ?>)
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $filter === 'pending_appeals' ? 'active' : ''; ?> bg-gradient-primary text-white fw-bold" 
               href="?filter=pending_appeals"
               style="<?php if ($stats['pending_appeals'] > 0): ?>animation: pulse 2s infinite;<?php endif; ?>">
                <i class="fas fa-exclamation-circle"></i> Pending Appeals (<?php echo $stats['pending_appeals']; ?>)
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
            <thead class="table-dark sticky-top">
                <tr>
                    <th>ID</th>
                    <th>Product</th>
                    <th>Buyer</th>
                    <th>Seller</th>
                    <th>Reason</th>
                    <th>Status</th>
                    <th>Stage</th>
                    <th>Appeal</th>
                    <th>Filed</th>
                    <th style="width: 200px;">Actions</th>
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
                
                $appealStatus = $d['appeal_status'] ?? 'none';
                $hasAppeal = ($appealStatus !== 'none');
                $needsAppealReview = ($d['status'] === 'under_appeal' && $appealStatus === 'pending');
                ?>
                <tr class="<?php echo $needsAppealReview ? 'table-warning' : ''; ?>">
                    <td>
                        <strong>#<?php echo $d['dispute_id']; ?></strong>
                        <?php if ($needsAppealReview): ?>
                        <br><span class="badge bg-danger">⚠️ URGENT</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="d-flex align-items-center">
                            <span><?php echo excerpt(Security::clean($d['product_name']), 30); ?></span>
                        </div>
                        <small class="text-muted">Order #<?php echo $d['order_id']; ?></small>
                    </td>
                    <td><?php echo Security::clean($d['buyer_name']); ?></td>
                    <td><?php echo Security::clean($d['seller_name']); ?></td>
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
                        <small><?php echo ucwords(str_replace('_', ' ', $d['stage'])); ?></small>
                    </td>
                    <!-- NEW: Appeal Status Column -->
                    <td>
                        <?php if ($hasAppeal): ?>
                            <?php if ($needsAppealReview): ?>
                            <span class="badge bg-danger">
                                <i class="fas fa-exclamation-circle"></i> REVIEW NOW
                            </span>
                            <?php elseif ($appealStatus === 'investigating'): ?>
                            <span class="badge bg-warning text-dark">
                                <i class="fas fa-search"></i> In Progress
                            </span>
                            <?php elseif ($appealStatus === 'resolved'): ?>
                            <span class="badge bg-success">
                                <i class="fas fa-check"></i> Resolved
                            </span>
                            <?php else: ?>
                            <span class="badge bg-info">
                                <?php echo ucfirst($appealStatus); ?>
                            </span>
                            <?php endif; ?>
                        <?php else: ?>
                            <span class="text-muted">-</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <small><?php echo time_ago($d['created_at']); ?></small>
                        <?php if ($d['appeal_date']): ?>
                        <br><small class="text-info">Appeal: <?php echo time_ago($d['appeal_date']); ?></small>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($needsAppealReview): ?>
                        <!-- ADMIN APPEAL REVIEW - Priority Action -->
                        <a href="<?php echo APP_URL; ?>/admin/appeal-review.php?id=<?php echo $d['dispute_id']; ?>" 
                           class="btn btn-danger btn-sm w-100 mb-1">
                            <i class="fas fa-gavel"></i> Review Appeal
                        </a>
                        <?php else: ?>
                        <!-- Regular View -->
                        <a href="<?php echo APP_URL; ?>/disputes/view.php?id=<?php echo $d['dispute_id']; ?>" 
                           class="btn btn-primary btn-sm w-100 mb-1">
                            <i class="fas fa-eye"></i> View Details
                        </a>
                        <?php endif; ?>
                        
                        <?php if ($hasAppeal && $appealStatus !== 'resolved'): ?>
                        <small class="text-info d-block">
                            <i class="fas fa-redo"></i> Has Appeal
                        </small>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php endif; ?>
</div>

<style>
@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.7; }
}

.sticky-top {
    position: sticky;
    top: 0;
    z-index: 10;
}

.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}
</style>

<?php require_once __DIR__.'/../includes/footer.php'; ?>
