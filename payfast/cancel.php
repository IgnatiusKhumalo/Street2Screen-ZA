<?php
/**
 * PayFast Cancel URL - User lands here after cancelling payment
 * Place at: payfast/cancel.php
 */
$pageTitle='Payment Cancelled';
require_once __DIR__.'/../includes/header.php';
Security::requireLogin();

$orderId=get_get('order_id');
$db=new Database();

// Mark order as failed
if($orderId){
    $db->query("UPDATE orders SET payment_status='failed' WHERE order_id=:id AND buyer_id=:uid AND payment_status='pending'");
    $db->bind(':id',$orderId);
    $db->bind(':uid',Security::getUserId());
    $db->execute();
}
?>

<div class="container my-5">
<div class="row justify-content-center">
<div class="col-md-6 text-center">
    <div class="card shadow">
        <div class="card-body p-5">
            <i class="fas fa-times-circle text-danger mb-4" style="font-size:100px"></i>
            <h2 class="fw-bold text-danger mb-3">Payment Cancelled</h2>
            <p class="text-muted mb-4">Your payment was cancelled. No charges were made. You can try again or choose a different payment method.</p>
            <div class="d-grid gap-2">
                <a href="<?php echo APP_URL; ?>/orders/checkout.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-redo"></i> Try Again
                </a>
                <a href="<?php echo APP_URL; ?>/orders/cart.php" class="btn btn-outline-secondary">
                    <i class="fas fa-shopping-cart"></i> Back to Cart
                </a>
            </div>
        </div>
    </div>
</div>
</div>
</div>

<?php require_once __DIR__.'/../includes/footer.php'; ?>
