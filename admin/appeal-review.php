<?php
/**
 * ============================================
 * ADMIN - APPEAL REVIEW (3-PARTY INVESTIGATION)
 * ============================================
 * NEW FILE: Admin reviews appeals from buyer, seller, moderator
 * Shows all evidence and makes FINAL decision
 * ============================================
 */

$pageTitle = 'Appeal Review';
require_once __DIR__.'/../includes/header.php';
Security::requireAdmin();

$disputeId = get_get('id');
$db = new Database();
$userId = Security::getUserId();

// HANDLE APPEAL RESOLUTION (Admin only)
if (is_post_request() && isset($_POST['resolve_appeal'])) {
    $finalOutcome = Security::sanitizeString($_POST['final_outcome']);
    $finalRefund = floatval($_POST['final_refund'] ?? 0);
    $appealDecision = Security::sanitizeString($_POST['appeal_decision']);
    
    try {
        $db->beginTransaction();
        
        $db->query("UPDATE disputes SET 
                    status = 'appeal_resolved',
                    stage = 'appeal_closed',
                    appeal_status = 'resolved',
                    appeal_final_outcome = :outcome,
                    appeal_refund_amount = :refund,
                    appeal_decision = :decision,
                    appeal_resolved_by = :uid,
                    appeal_resolved_at = NOW()
                    WHERE dispute_id = :did");
        $db->bind(':outcome', $finalOutcome);
        $db->bind(':refund', $finalRefund);
        $db->bind(':decision', $appealDecision);
        $db->bind(':uid', $userId);
        $db->bind(':did', $disputeId);
        $db->execute();
        
        // Log
        try {
            $db->query("INSERT INTO dispute_logs 
                        (dispute_id, user_id, action, details, created_at)
                        VALUES (:did, :uid, 'appeal_resolved', :details, NOW())");
            $db->bind(':did', $disputeId);
            $db->bind(':uid', $userId);
            $db->bind(':details', "Appeal resolved: $finalOutcome");
            $db->execute();
        } catch (Exception $e) {}
        
        $db->commit();
        redirect_with_success(APP_URL.'/admin/disputes.php?filter=appeals', 
            'Appeal resolved successfully! This is the final decision.');
            
    } catch (Exception $e) {
        $db->rollBack();
        redirect_with_error(APP_URL."/admin/appeal-review.php?id=$disputeId", 'Failed to resolve appeal');
    }
}

// HANDLE APPEAL REJECTION
if (is_post_request() && isset($_POST['reject_appeal'])) {
    $rejectionReason = Security::sanitizeString($_POST['rejection_reason']);
    
    try {
        // Reject appeal - revert to original moderator decision
        $db->query("UPDATE disputes SET 
                    status = 'appeal_resolved',
                    stage = 'appeal_closed',
                    appeal_status = 'resolved',
                    appeal_decision = :reason,
                    appeal_resolved_by = :uid,
                    appeal_resolved_at = NOW()
                    WHERE dispute_id = :did");
        $db->bind(':reason', $rejectionReason);
        $db->bind(':uid', $userId);
        $db->bind(':did', $disputeId);
        $db->execute();
        
        redirect_with_success(APP_URL.'/admin/disputes.php?filter=appeals', 
            'Appeal rejected. Original decision stands.');
            
    } catch (Exception $e) {
        redirect_with_error(APP_URL."/admin/appeal-review.php?id=$disputeId", 'Failed to reject appeal');
    }
}

// FETCH COMPLETE DISPUTE DATA
$db->query("SELECT d.*, 
            o.order_id,
            o.buyer_id,
            o.seller_id,
            o.total_amount,
            o.order_date,
            p.product_name,
            p.description as product_description,
            (SELECT image_path FROM product_images WHERE product_id = p.product_id AND is_primary = 1 LIMIT 1) as product_image,
            buyer.full_name as buyer_name,
            buyer.email as buyer_email,
            buyer.phone as buyer_phone,
            seller.full_name as seller_name,
            seller.email as seller_email,
            seller.phone as seller_phone,
            moderator.full_name as moderator_name,
            moderator.email as moderator_email
            FROM disputes d
            JOIN orders o ON d.order_id = o.order_id
            JOIN products p ON o.product_id = p.product_id
            JOIN users buyer ON o.buyer_id = buyer.user_id
            JOIN users seller ON o.seller_id = seller.user_id
            LEFT JOIN users moderator ON d.resolved_by = moderator.user_id
            WHERE d.dispute_id = :id");
$db->bind(':id', $disputeId);
$dispute = $db->fetch();

if (!$dispute) {
    redirect_with_error(APP_URL.'/admin/disputes.php', 'Dispute not found');
}

// Check if appeal exists
if (empty($dispute['appeal_reason'])) {
    redirect_with_error(APP_URL.'/admin/disputes.php', 'No appeal filed for this dispute');
}

// Decode evidence
$originalEvidence = [];
if (!empty($dispute['evidence_paths'])) {
    $decoded = json_decode($dispute['evidence_paths'], true);
    if (is_array($decoded)) $originalEvidence = $decoded;
}

$appealEvidence = [];
if (!empty($dispute['appeal_evidence_paths'])) {
    $decoded = json_decode($dispute['appeal_evidence_paths'], true);
    if (is_array($decoded)) $appealEvidence = $decoded;
}

$outcomeLabels = [
    'buyer_favour' => 'Buyer Wins',
    'seller_favour' => 'Seller Wins',
    'mutual' => 'Mutual/Compromise',
    'insufficient' => 'Insufficient Evidence'
];
?>

<div class="container-fluid my-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">
            <i class="fas fa-balance-scale text-primary"></i> Appeal Review - Dispute #<?php echo $dispute['dispute_id']; ?>
        </h2>
        <a href="<?php echo APP_URL; ?>/admin/disputes.php?filter=pending_appeals" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Back to Appeals
        </a>
    </div>

    <div class="alert alert-warning">
        <strong><i class="fas fa-exclamation-triangle"></i> Admin Final Decision Required</strong>
        <p class="mb-0">
            Review all evidence from the buyer, seller, and moderator. Your decision will be <strong>FINAL</strong> and cannot be appealed.
        </p>
    </div>

    <div class="row">
        <!-- LEFT: ALL EVIDENCE -->
        <div class="col-lg-8">
            
            <!-- 1. ORIGINAL DISPUTE (BUYER'S CLAIM) -->
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-user"></i> 1. BUYER'S ORIGINAL DISPUTE
                    </h5>
                </div>
                <div class="card-body">
                    <p><strong>Buyer:</strong> <?php echo Security::clean($dispute['buyer_name']); ?></p>
                    <p><strong>Filed:</strong> <?php echo format_datetime($dispute['created_at']); ?></p>
                    <p><strong>Reason:</strong> 
                        <span class="badge bg-secondary fs-6">
                            <?php echo ucfirst(str_replace('_', ' ', $dispute['dispute_reason'])); ?>
                        </span>
                    </p>
                    <hr>
                    <p><strong>Buyer's Description:</strong></p>
                    <div class="bg-light p-3 rounded">
                        <?php echo nl2br(Security::clean($dispute['description'])); ?>
                    </div>
                    
                    <?php if (!empty($originalEvidence)): ?>
                    <hr>
                    <p><strong>Original Evidence (<?php echo count($originalEvidence); ?> photos):</strong></p>
                    <div class="row">
                        <?php foreach ($originalEvidence as $path): ?>
                        <div class="col-md-3 mb-2">
                            <a href="<?php echo APP_URL.'/'.$path; ?>" target="_blank">
                                <img src="<?php echo APP_URL.'/'.$path; ?>" class="img-fluid rounded shadow-sm">
                            </a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- 2. MODERATOR'S DECISION -->
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-gavel"></i> 2. MODERATOR'S ORIGINAL DECISION
                    </h5>
                </div>
                <div class="card-body">
                    <p><strong>Moderator:</strong> <?php echo Security::clean($dispute['moderator_name'] ?? 'System'); ?></p>
                    <p><strong>Decided:</strong> <?php echo format_datetime($dispute['resolved_at']); ?></p>
                    
                    <div class="alert alert-success">
                        <strong>Outcome:</strong> 
                        <?php echo $outcomeLabels[$dispute['resolution_outcome']] ?? ucfirst(str_replace('_', ' ', $dispute['resolution_outcome'])); ?>
                    </div>
                    
                    <?php if ($dispute['favoured_party']): ?>
                    <p><strong>Favoured:</strong> 
                        <span class="badge bg-primary fs-6">
                            <?php echo ucfirst(str_replace('_', ' ', $dispute['favoured_party'])); ?>
                        </span>
                    </p>
                    <?php endif; ?>
                    
                    <?php if ($dispute['refund_amount'] > 0): ?>
                    <p><strong>Refund Approved:</strong> 
                        <span class="text-success fs-5">R<?php echo number_format($dispute['refund_amount'], 2); ?></span>
                    </p>
                    <?php endif; ?>
                    
                    <hr>
                    <p><strong>Moderator's Resolution Notes:</strong></p>
                    <div class="bg-light p-3 rounded">
                        <?php echo nl2br(Security::clean($dispute['resolution_notes'])); ?>
                    </div>
                    
                    <?php if (!empty($dispute['admin_notes'])): ?>
                    <hr>
                    <p><strong>Internal Admin Notes:</strong></p>
                    <div class="bg-warning bg-opacity-10 p-2 rounded small">
                        <?php echo nl2br(Security::clean($dispute['admin_notes'])); ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- 3. BUYER'S APPEAL -->
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="fas fa-redo"></i> 3. BUYER'S APPEAL
                    </h5>
                </div>
                <div class="card-body">
                    <p><strong>Appeal Filed:</strong> <?php echo format_datetime($dispute['appeal_date']); ?></p>
                    <p><strong>Why Buyer is Appealing:</strong></p>
                    <div class="bg-light p-3 rounded border-start border-warning border-5">
                        <?php echo nl2br(Security::clean($dispute['appeal_reason'])); ?>
                    </div>
                    
                    <?php if (!empty($appealEvidence)): ?>
                    <hr>
                    <p><strong>New Evidence (<?php echo count($appealEvidence); ?> photos):</strong></p>
                    <div class="row">
                        <?php foreach ($appealEvidence as $path): ?>
                        <div class="col-md-3 mb-2">
                            <a href="<?php echo APP_URL.'/'.$path; ?>" target="_blank">
                                <img src="<?php echo APP_URL.'/'.$path; ?>" class="img-fluid rounded shadow-sm">
                            </a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- 4. SELLER INFORMATION (for context) -->
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-store"></i> 4. SELLER INFORMATION
                    </h5>
                </div>
                <div class="card-body">
                    <p><strong>Seller:</strong> <?php echo Security::clean($dispute['seller_name']); ?></p>
                    <p><strong>Email:</strong> <?php echo Security::clean($dispute['seller_email']); ?></p>
                    <p><strong>Phone:</strong> <?php echo Security::clean($dispute['seller_phone']); ?></p>
                    
                    <div class="alert alert-info">
                        <strong><i class="fas fa-info-circle"></i> Note:</strong> 
                        You may want to contact the seller to get their side of the story before making a final decision.
                    </div>
                </div>
            </div>

        </div>

        <!-- RIGHT: ADMIN DECISION PANEL -->
        <div class="col-lg-4">
            <div class="card shadow sticky-top" style="top: 20px;">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-balance-scale"></i> ADMIN FINAL DECISION
                    </h5>
                </div>
                <div class="card-body">
                    
                    <!-- Product & Order Summary -->
                    <div class="mb-3 p-3 bg-light rounded">
                        <h6 class="fw-bold"><?php echo Security::clean($dispute['product_name']); ?></h6>
                        <p class="mb-1"><strong>Order:</strong> #<?php echo $dispute['order_id']; ?></p>
                        <p class="mb-0"><strong>Amount:</strong> R<?php echo number_format($dispute['total_amount'], 2); ?></p>
                    </div>

                    <hr>

                    <!-- Summary -->
                    <div class="mb-3">
                        <h6 class="fw-bold">Quick Summary:</h6>
                        <ul class="small mb-0">
                            <li>Moderator decided: <strong><?php echo $outcomeLabels[$dispute['resolution_outcome']] ?? 'N/A'; ?></strong></li>
                            <li>Original refund: <strong>R<?php echo number_format($dispute['refund_amount'], 2); ?></strong></li>
                            <li>Buyer appeals because: <em><?php echo excerpt(Security::clean($dispute['appeal_reason']), 60); ?></em></li>
                        </ul>
                    </div>

                    <hr>

                    <!-- APPROVE APPEAL FORM -->
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo Security::generateCSRFToken(); ?>">
                        
                        <h6 class="fw-bold text-success mb-3">
                            <i class="fas fa-check-circle"></i> Approve Appeal & Make New Decision
                        </h6>
                        
                        <div class="mb-3">
                            <label class="form-label">Final Outcome:</label>
                            <select name="final_outcome" class="form-select" required>
                                <option value="">-- Select --</option>
                                <option value="buyer_favour">🛒 Buyer Wins (Overturn)</option>
                                <option value="seller_favour">🏪 Seller Wins (Uphold)</option>
                                <option value="mutual">⚖️ Compromise</option>
                                <option value="insufficient">❌ Insufficient Evidence</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Final Refund Amount:</label>
                            <div class="input-group">
                                <span class="input-group-text">R</span>
                                <input type="number" name="final_refund" class="form-control" 
                                       min="0" max="<?php echo $dispute['total_amount']; ?>" 
                                       step="0.01" value="0" placeholder="0.00">
                            </div>
                            <small class="text-muted">
                                Max: R<?php echo number_format($dispute['total_amount'], 2); ?>
                            </small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Final Decision Notes (visible to all):</label>
                            <textarea name="appeal_decision" class="form-control" rows="6" required 
                                      placeholder="Explain your final decision clearly. This will be visible to the buyer, seller, and moderator."></textarea>
                        </div>
                        
                        <div class="alert alert-danger">
                            <strong><i class="fas fa-exclamation-triangle"></i> WARNING:</strong>
                            This decision is FINAL and cannot be appealed or changed.
                        </div>
                        
                        <button type="submit" name="resolve_appeal" class="btn btn-success btn-lg w-100 mb-2"
                                onclick="return confirm('⚠️ Are you sure? This decision is FINAL and cannot be changed!');">
                            <i class="fas fa-gavel"></i> Make Final Decision
                        </button>
                    </form>

                    <hr>

                    <!-- REJECT APPEAL FORM -->
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo Security::generateCSRFToken(); ?>">
                        
                        <h6 class="fw-bold text-danger mb-3">
                            <i class="fas fa-times-circle"></i> Reject Appeal (Keep Original Decision)
                        </h6>
                        
                        <div class="mb-3">
                            <label class="form-label">Rejection Reason:</label>
                            <textarea name="rejection_reason" class="form-control" rows="4" required 
                                      placeholder="Explain why the appeal is rejected and the original decision stands."></textarea>
                        </div>
                        
                        <button type="submit" name="reject_appeal" class="btn btn-outline-danger w-100"
                                onclick="return confirm('Reject appeal and keep the moderator\'s decision?');">
                            <i class="fas fa-ban"></i> Reject Appeal
                        </button>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__.'/../includes/footer.php'; ?>
