<?php
/**
 * ============================================
 * FILE A DISPUTE - BUYER FILES NEW DISPUTE
 * ============================================
 * Allows buyers to file disputes for delivered orders
 * ============================================
 */

$pageTitle = 'File a Dispute';
require_once __DIR__.'/../includes/header.php';
Security::requireLogin();

$orderId = get_get('order_id');
$db = new Database();
$userId = Security::getUserId();

// Verify order exists and belongs to buyer
$db->query("SELECT o.*, p.product_name,
            (SELECT image_path FROM product_images WHERE product_id = p.product_id AND is_primary = 1 LIMIT 1) as image
            FROM orders o
            JOIN products p ON o.product_id = p.product_id
            WHERE o.order_id = :oid AND o.buyer_id = :uid");
$db->bind(':oid', $orderId);
$db->bind(':uid', $userId);
$order = $db->fetch();

if (!$order) {
    redirect_with_error(APP_URL.'/orders/my-orders.php', 'Order not found or access denied');
}

// Check if dispute already exists for this order
$db->query("SELECT dispute_id FROM disputes WHERE order_id = :oid");
$db->bind(':oid', $orderId);
$existingDispute = $db->fetch();

if ($existingDispute) {
    redirect_with_error(APP_URL.'/disputes/view.php?id='.$existingDispute['dispute_id'], 
        'A dispute already exists for this order');
}

// Handle form submission
if (is_post_request()) {
    $reason = Security::sanitizeString($_POST['dispute_reason']);
    $description = Security::sanitizeString($_POST['description']);
    
    // Handle evidence uploads
    $evidencePaths = [];
    
    if (!empty($_FILES['evidence']['name'][0])) {
        $uploadDir = __DIR__.'/../uploads/disputes/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
        
        foreach ($_FILES['evidence']['name'] as $key => $filename) {
            if ($_FILES['evidence']['error'][$key] === UPLOAD_ERR_OK) {
                $fileType = $_FILES['evidence']['type'][$key];
                
                if (in_array($fileType, $allowedTypes)) {
                    $ext = pathinfo($filename, PATHINFO_EXTENSION);
                    $newFilename = 'dispute_' . $orderId . '_' . time() . '_' . $key . '.' . $ext;
                    $targetPath = $uploadDir . $newFilename;
                    
                    if (move_uploaded_file($_FILES['evidence']['tmp_name'][$key], $targetPath)) {
                        $evidencePaths[] = 'uploads/disputes/' . $newFilename;
                    }
                }
            }
        }
    }
    
    // Insert dispute
    $db->query("INSERT INTO disputes 
                (order_id, reported_by, dispute_reason, description, evidence_paths, status, stage, created_at)
                VALUES (:oid, :uid, :reason, :desc, :evidence, 'open', 'received', NOW())");
    $db->bind(':oid', $orderId);
    $db->bind(':uid', $userId);
    $db->bind(':reason', $reason);
    $db->bind(':desc', $description);
    $db->bind(':evidence', json_encode($evidencePaths));
    
    if ($db->execute()) {
        $disputeId = $db->lastInsertId();
        
        // Log the dispute filing
        try {
            $db->query("INSERT INTO dispute_logs 
                        (dispute_id, user_id, action, details, created_at)
                        VALUES (:did, :uid, 'filed', 'Dispute filed by buyer', NOW())");
            $db->bind(':did', $disputeId);
            $db->bind(':uid', $userId);
            $db->execute();
        } catch (Exception $e) {}
        
        redirect_with_success(APP_URL.'/disputes/view.php?id='.$disputeId, 
            'Dispute filed successfully! A moderator will review within 48 hours.');
    } else {
        $error = 'Failed to file dispute. Please try again.';
    }
}
?>

<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold"><i class="fas fa-gavel text-danger"></i> File a Dispute</h2>
        <a href="<?php echo APP_URL; ?>/orders/order-details.php?id=<?php echo $orderId; ?>" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Back to Order
        </a>
    </div>

    <?php if (isset($error)): ?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
    </div>
    <?php endif; ?>

    <div class="row">
        <!-- Left: Order Info -->
        <div class="col-md-4">
            <div class="card shadow-sm sticky-top" style="top: 20px;">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-box"></i> Order Details</h5>
                </div>
                <div class="card-body">
                    <img src="<?php echo $order['image'] ? APP_URL.'/'.$order['image'] : APP_URL.'/assets/images/placeholder.svg'; ?>" 
                         class="img-fluid rounded mb-3">
                    
                    <h6><?php echo Security::clean($order['product_name']); ?></h6>
                    
                    <hr>
                    
                    <p class="mb-1"><strong>Order ID:</strong> #<?php echo $order['order_id']; ?></p>
                    <p class="mb-1"><strong>Amount:</strong> <?php echo format_currency($order['total_amount']); ?></p>
                    <p class="mb-1"><strong>Quantity:</strong> <?php echo $order['quantity']; ?></p>
                    <p class="mb-1"><strong>Order Date:</strong> <?php echo format_date($order['order_date']); ?></p>
                    
                    <?php if ($order['delivery_date']): ?>
                    <p class="mb-0"><strong>Delivered:</strong> <?php echo format_date($order['delivery_date']); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Right: Dispute Form -->
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0"><i class="fas fa-exclamation-triangle"></i> Dispute Information</h5>
                </div>
                <div class="card-body">
                    
                    <div class="alert alert-warning">
                        <strong><i class="fas fa-info-circle"></i> Before filing a dispute:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Try contacting the seller first to resolve the issue</li>
                            <li>Provide clear evidence (photos, screenshots, etc.)</li>
                            <li>Be honest and accurate in your description</li>
                            <li>False claims may result in account suspension</li>
                        </ul>
                    </div>

                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?php echo Security::generateCSRFToken(); ?>">
                        
                        <div class="mb-3">
                            <label class="form-label">Dispute Reason <span class="text-danger">*</span></label>
                            <select name="dispute_reason" class="form-select" required>
                                <option value="">-- Select Reason --</option>
                                <option value="non_delivery">Item Not Delivered</option>
                                <option value="damaged_product">Product Damaged/Defective</option>
                                <option value="not_as_described">Not As Described</option>
                                <option value="missing_items">Missing Items</option>
                                <option value="wrong_item">Wrong Item Received</option>
                                <option value="quality_issues">Quality Issues</option>
                                <option value="seller_unresponsive">Seller Not Responding</option>
                                <option value="other">Other Issue</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Detailed Description <span class="text-danger">*</span></label>
                            <textarea name="description" class="form-control" rows="6" required 
                                      placeholder="Explain what went wrong. Be specific and include dates, communication attempts, etc."></textarea>
                            <small class="text-muted">Minimum 50 characters. Be clear and provide as much detail as possible.</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Evidence (Photos/Screenshots)</label>
                            <input type="file" name="evidence[]" class="form-control" multiple accept="image/*">
                            <small class="text-muted">
                                Upload photos showing the issue (damaged product, wrong item, etc.). Max 5 images. JPG, PNG only.
                            </small>
                        </div>
                        
                        <div class="alert alert-info">
                            <strong><i class="fas fa-clock"></i> What happens next?</strong>
                            <ol class="mb-0 mt-2">
                                <li>Your dispute is submitted to our moderation team</li>
                                <li>A moderator reviews evidence within 24-48 hours</li>
                                <li>Seller is notified and may respond</li>
                                <li>Decision is made based on evidence</li>
                                <li>You'll be notified of the outcome</li>
                            </ol>
                        </div>
                        
                        <div class="form-check mb-3">
                            <input type="checkbox" class="form-check-input" id="confirm" required>
                            <label class="form-check-label" for="confirm">
                                I confirm that the information provided is accurate and I have attempted to resolve this issue with the seller.
                            </label>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-danger btn-lg">
                                <i class="fas fa-paper-plane"></i> Submit Dispute
                            </button>
                            <a href="<?php echo APP_URL; ?>/orders/order-details.php?id=<?php echo $orderId; ?>" 
                               class="btn btn-outline-secondary">
                                Cancel
                            </a>
                        </div>
                    </form>
                    
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__.'/../includes/footer.php'; ?>
