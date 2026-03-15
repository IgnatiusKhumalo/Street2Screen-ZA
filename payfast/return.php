<?php
/**
 * PayFast Return URL - User lands here after successful payment
 * Place at: payfast/return.php
 */
$pageTitle='Payment Successful';
require_once __DIR__.'/../includes/header.php';
Security::requireLogin();

$orderId=get_get('order_id');
$db=new Database();
$userId=Security::getUserId();

// Verify this order belongs to current user
$db->query("SELECT o.*,p.product_name FROM orders o JOIN products p ON o.product_id=p.product_id WHERE o.order_id=:id AND o.buyer_id=:uid");
$db->bind(':id',$orderId);
$db->bind(':uid',$userId);
$order=$db->fetch();
?>

<div class="container my-5">
<div class="row justify-content-center">
<div class="col-md-6 text-center">
    <div class="card shadow">
        <div class="card-body p-5">
            <div class="mb-4">
                <i class="fas fa-check-circle text-success" style="font-size:100px"></i>
            </div>
            <h2 class="fw-bold text-success mb-3">Payment Successful!</h2>
            <?php if($order): ?>
            <div class="alert alert-info text-start mb-4">
                <p class="mb-1"><strong>Order #:</strong> <?php echo $order['order_id']; ?></p>
                <p class="mb-1"><strong>Product:</strong> <?php echo Security::clean($order['product_name']); ?></p>
                <p class="mb-0"><strong>Amount:</strong> <?php echo format_currency($order['total_amount']); ?></p>
            </div>
            <?php endif; ?>
            <p class="text-muted mb-4">Your payment has been processed successfully. The seller has been notified and will prepare your order.</p>
            <div class="d-grid gap-2">
                <a href="<?php echo APP_URL; ?>/orders/my-orders.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-box"></i> View My Orders
                </a>
                <a href="<?php echo APP_URL; ?>/products/index.php" class="btn btn-outline-secondary">
                    <i class="fas fa-shopping-bag"></i> Continue Shopping
                </a>
            </div>
        </div>
    </div>
</div>
</div>
</div>

<?php require_once __DIR__.'/../includes/footer.php'; ?>
