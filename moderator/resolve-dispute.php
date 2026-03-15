<?php
/**
 * ============================================
 * MODERATOR - RESOLVE DISPUTE (FINAL FIX)
 * ============================================
 * FIXED: Column names match actual database structure
 * ============================================
 */

$pageTitle = 'Resolve Dispute';
require_once __DIR__.'/../includes/header.php';
Security::requireModerator();

$db = new Database();

// Get dispute ID
$disputeId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($disputeId === 0) {
    redirect_with_error('/moderator/dashboard.php', 'Invalid dispute');
}

// HANDLE DISPUTE STAGE UPDATE
if (is_post_request() && isset($_POST['update_stage'])) {
    $newStage = Security::sanitizeString($_POST['stage']);
    $adminNotes = Security::sanitizeString($_POST['admin_notes'] ?? '');
    
    $validStages = ['received', 'under_review', 'evidence_verification', 'resolution', 'closed'];
    
    if (in_array($newStage, $validStages)) {
        try {
            $db->query("UPDATE disputes SET 
                        stage = :stage,
                        admin_notes = :notes
                        WHERE dispute_id = :did");
            $db->bind(':stage', $newStage);
            $db->bind(':notes', $adminNotes);
            $db->bind(':did', $disputeId);
            $db->execute();
            
            // Log if table exists
            try {
                $db->query("INSERT INTO dispute_logs 
                            (dispute_id, user_id, action, details, created_at)
                            VALUES (:did, :uid, :action, :details, NOW())");
                $db->bind(':did', $disputeId);
                $db->bind(':uid', Security::getUserId());
                $db->bind(':action', 'stage_change');
                $db->bind(':details', "Stage changed to: $newStage");
                $db->execute();
            } catch (Exception $e) {}
            
            redirect_with_success("/moderator/resolve-dispute.php?id=$disputeId", 'Dispute stage updated');
        } catch (Exception $e) {
            redirect_with_error("/moderator/resolve-dispute.php?id=$disputeId", 'Failed to update stage');
        }
    }
}

// HANDLE DISPUTE RESOLUTION
if (is_post_request() && isset($_POST['resolve_dispute'])) {
    $favouredParty = Security::sanitizeString($_POST['favoured_party']);
    $refundAmount = floatval($_POST['refund_amount'] ?? 0);
    $resolutionNotes = Security::sanitizeString($_POST['resolution_notes']);
    
    try {
        $db->beginTransaction();
        
        $db->query("UPDATE disputes SET 
                    status = 'resolved',
                    stage = 'closed',
                    resolution_outcome = :outcome,
                    favoured_party = :party,
                    refund_amount = :refund,
                    resolution_notes = :notes,
                    resolved_by = :mod_id,
                    resolved_at = NOW()
                    WHERE dispute_id = :did");
        $db->bind(':outcome', $favouredParty);
        $db->bind(':party', $favouredParty);
        $db->bind(':refund', $refundAmount);
        $db->bind(':notes', $resolutionNotes);
        $db->bind(':mod_id', Security::getUserId());
        $db->bind(':did', $disputeId);
        $db->execute();
        
        // Log
        try {
            $db->query("INSERT INTO dispute_logs 
                        (dispute_id, user_id, action, details, created_at)
                        VALUES (:did, :uid, :action, :details, NOW())");
            $db->bind(':did', $disputeId);
            $db->bind(':uid', Security::getUserId());
            $db->bind(':action', 'resolved');
            $db->bind(':details', "Resolved in favour of: $favouredParty");
            $db->execute();
        } catch (Exception $e) {}
        
        $db->commit();
        redirect_with_success('/moderator/dashboard.php', 'Dispute resolved successfully');
        
    } catch (Exception $e) {
        $db->rollBack();
        redirect_with_error("/moderator/resolve-dispute.php?id=$disputeId", 'Failed to resolve');
    }
}

// HANDLE REJECTION
if (is_post_request() && isset($_POST['reject_dispute'])) {
    $rejectionReason = Security::sanitizeString($_POST['rejection_reason']);
    
    try {
        $db->query("UPDATE disputes SET 
                    status = 'closed',
                    stage = 'closed',
                    resolution_outcome = 'insufficient',
                    resolution_notes = :reason,
                    resolved_by = :mod_id,
                    resolved_at = NOW()
                    WHERE dispute_id = :did");
        $db->bind(':reason', $rejectionReason);
        $db->bind(':mod_id', Security::getUserId());
        $db->bind(':did', $disputeId);
        $db->execute();
        
        redirect_with_success('/moderator/dashboard.php', 'Dispute rejected');
    } catch (Exception $e) {
        redirect_with_error("/moderator/resolve-dispute.php?id=$disputeId", 'Failed to reject');
    }
}

// FETCH DISPUTE DETAILS (FIXED QUERY)
$db->query("SELECT d.*, 
            o.order_id,
            p.product_name,
            buyer.full_name as buyer_name,
            buyer.email as buyer_email,
            seller.full_name as seller_name,
            seller.email as seller_email,
            resolver.full_name as resolver_name
            FROM disputes d
            JOIN orders o ON d.order_id = o.order_id
            JOIN products p ON o.product_id = p.product_id
            JOIN users buyer ON o.buyer_id = buyer.user_id
            JOIN users seller ON o.seller_id = seller.user_id
            LEFT JOIN users resolver ON d.resolved_by = resolver.user_id
            WHERE d.dispute_id = :did");
$db->bind(':did', $disputeId);
$dispute = $db->fetch();

if (!$dispute) {
    redirect_with_error('/moderator/dashboard.php', 'Dispute not found');
}

// Fetch logs if table exists
$logs = [];
try {
    $db->query("SELECT dl.*, u.full_name as user_name 
                FROM dispute_logs dl
                JOIN users u ON dl.user_id = u.user_id
                WHERE dl.dispute_id = :did
                ORDER BY dl.created_at DESC");
    $db->bind(':did', $disputeId);
    $logs = $db->fetchAll();
} catch (Exception $e) {
    // Table doesn't exist yet
}
?>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col-12">
            <a href="<?php echo APP_URL; ?>/moderator/dashboard.php" class="btn btn-secondary mb-3">
                <i class="fas fa-arrow-left"></i> Back
            </a>
            <h2><i class="fas fa-gavel text-warning"></i> Resolve Dispute #<?php echo $dispute['dispute_id']; ?></h2>
        </div>
    </div>

    <div class="row">
        <!-- DISPUTE DETAILS -->
        <div class="col-lg-8">
            
            <div class="card mb-3">
                <div class="card-header bg-dark text-white">
                    <strong>📋 Dispute Information</strong>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Dispute ID:</strong> #<?php echo $dispute['dispute_id']; ?></p>
                            <p><strong>Order ID:</strong> #<?php echo $dispute['order_id']; ?></p>
                            <p><strong>Product:</strong> <?php echo Security::clean($dispute['product_name'] ?? 'N/A'); ?></p>
                            <p><strong>Reason:</strong> <span class="badge bg-info"><?php echo ucwords(str_replace('_', ' ', $dispute['dispute_reason'])); ?></span></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Status:</strong> 
                                <span class="badge bg-<?php echo $dispute['status'] === 'resolved' ? 'success' : 'warning'; ?>">
                                    <?php echo ucfirst($dispute['status']); ?>
                                </span>
                            </p>
                            <p><strong>Stage:</strong> 
                                <span class="badge bg-primary"><?php echo ucwords(str_replace('_', ' ', $dispute['stage'])); ?></span>
                            </p>
                            <p><strong>Created:</strong> <?php echo format_datetime($dispute['created_at']); ?></p>
                        </div>
                    </div>
                    <hr>
                    <p><strong>Buyer:</strong> <?php echo Security::clean($dispute['buyer_name']); ?> (<?php echo Security::clean($dispute['buyer_email']); ?>)</p>
                    <p><strong>Seller:</strong> <?php echo Security::clean($dispute['seller_name']); ?> (<?php echo Security::clean($dispute['seller_email']); ?>)</p>
                    <hr>
                    <p><strong>Description:</strong></p>
                    <div class="bg-light p-3 rounded">
                        <?php echo nl2br(Security::clean($dispute['description'])); ?>
                    </div>
                </div>
            </div>

            <?php if ($dispute['status'] === 'open' || $dispute['status'] === 'investigating'): ?>
            
            <!-- STAGE MANAGEMENT -->
            <div class="card mb-3">
                <div class="card-header bg-primary text-white">
                    <strong>📊 Update Stage</strong>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo Security::generateCSRFToken(); ?>">
                        
                        <div class="mb-3">
                            <label class="form-label">Stage:</label>
                            <select name="stage" class="form-select" required>
                                <option value="received" <?php echo $dispute['stage'] === 'received' ? 'selected' : ''; ?>>📥 Received</option>
                                <option value="under_review" <?php echo $dispute['stage'] === 'under_review' ? 'selected' : ''; ?>>🔍 Under Review</option>
                                <option value="evidence_verification" <?php echo $dispute['stage'] === 'evidence_verification' ? 'selected' : ''; ?>>✅ Verify Evidence</option>
                                <option value="resolution" <?php echo $dispute['stage'] === 'resolution' ? 'selected' : ''; ?>>⚖️ Resolution</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Internal Notes:</label>
                            <textarea name="admin_notes" class="form-control" rows="2"><?php echo Security::clean($dispute['admin_notes'] ?? ''); ?></textarea>
                        </div>
                        
                        <button type="submit" name="update_stage" class="btn btn-primary">
                            <i class="fas fa-arrow-right"></i> Update Stage
                        </button>
                    </form>
                </div>
            </div>

            <!-- RESOLVE -->
            <div class="card mb-3 border-success">
                <div class="card-header bg-success text-white">
                    <strong>✅ Resolve Dispute</strong>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo Security::generateCSRFToken(); ?>">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Favoured Party:</label>
                                <select name="favoured_party" class="form-select" required>
                                    <option value="">-- Select --</option>
                                    <option value="buyer_favour">🛒 Buyer</option>
                                    <option value="seller_favour">🏪 Seller</option>
                                    <option value="mutual">⚖️ Neutral</option>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Refund Amount:</label>
                                <div class="input-group">
                                    <span class="input-group-text">R</span>
                                    <input type="number" name="refund_amount" class="form-control" 
                                           min="0" step="0.01" value="0" placeholder="0.00">
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Resolution Notes:</label>
                            <textarea name="resolution_notes" class="form-control" rows="4" required></textarea>
                        </div>
                        
                        <button type="submit" name="resolve_dispute" class="btn btn-success btn-lg w-100">
                            <i class="fas fa-check-circle"></i> Resolve Dispute
                        </button>
                    </form>
                </div>
            </div>

            <!-- REJECT -->
            <div class="card border-danger">
                <div class="card-header bg-danger text-white">
                    <strong>❌ Reject Dispute</strong>
                </div>
                <div class="card-body">
                    <form method="POST" onsubmit="return confirm('Reject this dispute?');">
                        <input type="hidden" name="csrf_token" value="<?php echo Security::generateCSRFToken(); ?>">
                        <div class="mb-3">
                            <textarea name="rejection_reason" class="form-control" rows="3" required></textarea>
                        </div>
                        <button type="submit" name="reject_dispute" class="btn btn-danger w-100">
                            <i class="fas fa-times-circle"></i> Reject
                        </button>
                    </form>
                </div>
            </div>

            <?php else: ?>
            
            <!-- RESOLVED VIEW -->
            <div class="card border-success">
                <div class="card-header bg-success text-white">
                    <strong>✅ Resolved</strong>
                </div>
                <div class="card-body">
                    <p><strong>Outcome:</strong> <?php echo ucwords(str_replace('_', ' ', $dispute['resolution_outcome'] ?? 'N/A')); ?></p>
                    <?php if ($dispute['refund_amount'] > 0): ?>
                    <p><strong>Refund:</strong> R<?php echo number_format($dispute['refund_amount'], 2); ?></p>
                    <?php endif; ?>
                    <p><strong>Resolved By:</strong> <?php echo Security::clean($dispute['resolver_name'] ?? 'N/A'); ?></p>
                    <p><strong>Resolved At:</strong> <?php echo format_datetime($dispute['resolved_at']); ?></p>
                    <hr>
                    <p><strong>Notes:</strong></p>
                    <div class="bg-light p-3 rounded">
                        <?php echo nl2br(Security::clean($dispute['resolution_notes'])); ?>
                    </div>
                </div>
            </div>
            
            <?php endif; ?>
        </div>

        <!-- ACTIVITY LOG -->
        <div class="col-lg-4">
            <div class="card sticky-top" style="top: 20px;">
                <div class="card-header bg-info text-white">
                    <strong>📜 Activity Log</strong>
                </div>
                <div class="card-body" style="max-height: 600px; overflow-y: auto;">
                    <?php if (empty($logs)): ?>
                        <p class="text-muted text-center small">No logs yet</p>
                    <?php else: ?>
                        <?php foreach ($logs as $log): ?>
                        <div class="mb-3 pb-3 border-bottom">
                            <strong class="text-primary"><?php echo ucwords(str_replace('_', ' ', $log['action'])); ?></strong>
                            <br>
                            <small class="text-muted">By: <?php echo Security::clean($log['user_name']); ?></small>
                            <br>
                            <small class="text-muted"><?php echo time_ago($log['created_at']); ?></small>
                            <?php if ($log['details']): ?>
                            <p class="mb-0 mt-1 small bg-light p-2 rounded">
                                <?php echo Security::clean($log['details']); ?>
                            </p>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__.'/../includes/footer.php'; ?>
