<?php
$pageTitle='Order Success';
require_once __DIR__.'/../includes/header.php';
Security::requireLogin();
?>

<div class="container my-5">
<div class="row justify-content-center">
<div class="col-md-6">
    <div class="card shadow text-center">
        <div class="card-body p-5">
            <div class="mb-4">
                <i class="fas fa-check-circle text-success" style="font-size:100px"></i>
            </div>
            
            <h2 class="fw-bold text-success mb-3">Order Placed Successfully!</h2>
            
            <p class="lead mb-4">Thank you for your order. We've sent a confirmation email with your order details.</p>
            
            <div class="alert alert-info text-start">
                <strong><i class="fas fa-info-circle"></i> What's Next?</strong>
                <ul class="mb-0 mt-2">
                    <li>Track your order status in "My Orders"</li>
                    <li>Seller will prepare your items for shipment</li>
                    <li>You'll receive tracking information once shipped</li>
                </ul>
            </div>
            
            <div class="d-grid gap-2 mt-4">
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
