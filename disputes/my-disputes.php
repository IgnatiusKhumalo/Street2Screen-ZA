<?php
/**
 * ============================================
 * MY DISPUTES - BUYER'S DISPUTE LIST + APPEALS
 * ============================================
 * UPDATED: Added appeal status display
 * NOTHING REMOVED - Only appeal badges added
 * ============================================
 */

$pageTitle = 'My Disputes';
require_once __DIR__.'/../includes/header.php';
Security::requireLogin();

$db = new Database();
$userId = Security::getUserId();

// Fetch disputes where current user is the buyer
$db->query("SELECT d.*,
            o.order_id,
            o.total_amount,
            p.product_name,
            (SELECT image_path FROM product_images WHERE product_id = p.product_id AND is_primary = 1 LIMIT 1) as image
            FROM disputes d
            JOIN orders o ON d.order_id = o.order_id
            JOIN products p ON o.product_id = p.product_id
            WHERE o.buyer_id = :uid
            ORDER BY d.created_at DESC");
$db->bind(':uid', $userId);
$disputes = $db->fetchAll();
?>

<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold"><i class="fas fa-gavel text-danger"></i> My Disputes</h2>
        <span class="badge bg-primary fs-5"><?php echo count($disputes); ?> Total</span>
    </div>

    <?php if (empty($disputes)): ?>
    <div class="alert alert-info text-center py-5">
        <i class="fas fa-check-circle fa-5x mb-4 text-success"></i>
        <h4>No Disputes Filed</h4>
        <p class="mb-3">You haven't filed any disputes. We hope all your orders go smoothly!</p>
        <a href="<?php echo APP_URL; ?>/orders/my-orders.php" class="btn btn-primary">
            <i class="fas fa-box"></i> View My Orders
        </a>
    </div>
    <?php else: ?>

    <div class="row">
        <?php foreach ($disputes as $d): ?>
        <div class="col-12 mb-3">
            <div class="card shadow-sm hover-shadow">
                <div class="card-body">
                    <div class="row align-items-center">
                        <!-- Product Image -->
                        <div class="col-md-2 text-center">
                            <img src="<?php echo $d['image'] ? APP_URL.'/'.$d['image'] : APP_URL.'/assets/images/placeholder.svg'; ?>" 
                                 class="img-fluid rounded" 
                                 style="max-height: 100px; object-fit: cover;">
                        </div>

                        <!-- Dispute Info -->
                        <div class="col-md-5">
                            <h5 class="mb-2">
                                <i class="fas fa-box text-primary"></i> 
                                <?php echo Security::clean($d['product_name']); ?>
                            </h5>
                            <p class="mb-1">
                                <strong>Dispute ID:</strong> #<?php echo $d['dispute_id']; ?>
                                <span class="mx-2">|</span>
                                <strong>Order:</strong> #<?php echo $d['order_id']; ?>
                            </p>
                            <p class="mb-1">
                                <strong>Reason:</strong> 
                                <span class="badge bg-secondary">
                                    <?php echo ucfirst(str_replace('_', ' ', $d['dispute_reason'])); ?>
                                </span>
                            </p>
                            <p class="small text-muted mb-1">
                                <i class="fas fa-calendar"></i> Filed: <?php echo format_date($d['created_at']); ?>
                                <span class="mx-2">|</span>
                                <i class="fas fa-money-bill-wave"></i> Amount: R<?php echo number_format($d['total_amount'], 2); ?>
                            </p>
                            <p class="small mb-0">
                                <?php echo excerpt(Security::clean($d['description']), 80); ?>
                            </p>
                        </div>

                        <!-- Status & Stage -->
                        <div class="col-md-3 text-center">
                            <?php
                            $statusColors = [
                                'open' => 'warning',
                                'investigating' => 'info',
                                'resolved' => 'success',
                                'closed' => 'secondary',
                                'under_appeal' => 'info',
                                'appeal_resolved' => 'primary'
                            ];
                            $statusColor = $statusColors[$d['status']] ?? 'secondary';
                            
                            $stageIcons = [
                                'received' => '📥',
                                'under_review' => '🔍',
                                'evidence_verification' => '✅',
                                'resolution' => '⚖️',
                                'closed' => '🔒',
                                'appeal_review' => '🔄',
                                'appeal_closed' => '✅'
                            ];
                            $stageIcon = $stageIcons[$d['stage']] ?? '📄';
                            
                            // Check appeal status
                            $appealStatus = $d['appeal_status'] ?? 'none';
                            ?>
                            
                            <!-- Status Badge -->
                            <span class="badge bg-<?php echo $statusColor; ?> w-100 py-2 mb-2 fs-6">
                                <i class="fas fa-<?php 
                                    echo $d['status'] === 'resolved' ? 'check-circle' : 
                                        ($d['status'] === 'investigating' ? 'search' : 
                                        ($d['status'] === 'under_appeal' ? 'redo' : 'exclamation-triangle'));
                                ?>"></i>
                                <?php echo ucfirst(str_replace('_', ' ', $d['status'])); ?>
                            </span>

                            <!-- Stage Badge -->
                            <span class="badge bg-primary w-100 py-2 mb-2">
                                <?php echo $stageIcon; ?> <?php echo ucwords(str_replace('_', ' ', $d['stage'])); ?>
                            </span>

                            <!-- Appeal Status Badge (NEW!) -->
                            <?php if ($appealStatus !== 'none'): ?>
                            <span class="badge bg-info w-100 py-2 mb-2">
                                <i class="fas fa-redo"></i> Appeal: <?php echo ucfirst($appealStatus); ?>
                            </span>
                            <?php endif; ?>

                            <!-- Resolution Outcome -->
                            <?php if ($d['resolution_outcome']): ?>
                            <span class="badge bg-<?php echo $d['resolution_outcome'] === 'buyer_favour' ? 'success' : 'info'; ?> w-100 py-1 mb-2">
                                <?php echo ucfirst(str_replace('_', ' ', $d['resolution_outcome'])); ?>
                            </span>
                            <?php endif; ?>

                            <!-- Refund Amount -->
                            <?php if ($d['refund_amount'] > 0): ?>
                            <div class="mt-2">
                                <small class="text-muted d-block">Refund Amount</small>
                                <strong class="text-success">R<?php echo number_format($d['refund_amount'], 2); ?></strong>
                            </div>
                            <?php endif; ?>
                            
                            <!-- Appeal Refund Amount (if appeal resolved with refund) -->
                            <?php if (isset($d['appeal_refund_amount']) && $d['appeal_refund_amount'] > 0 && $appealStatus === 'resolved'): ?>
                            <div class="mt-2">
                                <small class="text-muted d-block">Final Refund (Appeal)</small>
                                <strong class="text-primary">R<?php echo number_format($d['appeal_refund_amount'], 2); ?></strong>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Actions -->
                        <div class="col-md-2 text-end">
                            <a href="<?php echo APP_URL; ?>/disputes/view.php?id=<?php echo $d['dispute_id']; ?>" 
                               class="btn btn-primary btn-sm w-100 mb-2">
                                <i class="fas fa-eye"></i> View Dispute
                            </a>
                            
                            <!-- Status Indicators -->
                            <?php if ($d['status'] === 'resolved' && $appealStatus === 'none'): ?>
                            <span class="badge bg-warning text-dark w-100 mb-2">
                                <i class="fas fa-redo"></i> Can Appeal
                            </span>
                            <?php elseif ($d['status'] === 'under_appeal'): ?>
                            <span class="badge bg-info w-100 mb-2">
                                <i class="fas fa-clock"></i> Appeal Pending
                            </span>
                            <?php elseif ($appealStatus === 'resolved'): ?>
                            <span class="badge bg-success w-100 mb-2">
                                <i class="fas fa-check-double"></i> Final Decision
                            </span>
                            <?php elseif ($d['status'] === 'open'): ?>
                            <span class="badge bg-warning text-dark w-100">
                                <i class="fas fa-clock"></i> Under Review
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <?php endif; ?>
</div>

<style>
.hover-shadow {
    transition: box-shadow 0.3s ease;
}
.hover-shadow:hover {
    box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15) !important;
}
</style>

<?php require_once __DIR__.'/../includes/footer.php'; ?>
