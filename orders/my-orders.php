<?php
$pageTitle='My Orders';
require_once __DIR__.'/../includes/header.php';
Security::requireLogin();

$db=new Database();
$userId=Security::getUserId();

$db->query("SELECT o.*,p.product_name,u.full_name as seller_name,(SELECT image_path FROM product_images WHERE product_id=p.product_id AND is_primary=1 LIMIT 1)as image FROM orders o JOIN products p ON o.product_id=p.product_id JOIN users u ON o.seller_id=u.user_id WHERE o.buyer_id=:uid ORDER BY o.order_date DESC");
$db->bind(':uid',$userId);
$orders=$db->fetchAll();
?>

<div class="container my-5">
<h2 class="fw-bold mb-4"><i class="fas fa-box text-primary"></i> My Orders (<?php echo count($orders); ?>)</h2>

<?php if(empty($orders)): ?>
<div class="alert alert-info text-center">
    <i class="fas fa-box-open fa-5x mb-4 text-muted"></i>
    <h4>No Orders Yet</h4>
    <p class="mb-4">You haven't placed any orders yet. Start shopping!</p>
    <a href="<?php echo APP_URL; ?>/products/index.php" class="btn btn-primary btn-lg">
        <i class="fas fa-shopping-bag"></i> Browse Products
    </a>
</div>
<?php else: ?>

<?php foreach($orders as $o): ?>
<div class="card mb-3 shadow-sm">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-2">
                <img src="<?php echo $o['image']?APP_URL.'/'.$o['image']:APP_URL.'/assets/images/placeholder.svg'; ?>" 
                     class="img-fluid rounded">
            </div>
            <div class="col-md-4">
                <h6 class="mb-2"><?php echo Security::clean($o['product_name']); ?></h6>
                <p class="small text-muted mb-1">
                    <i class="fas fa-user"></i> Seller: <?php echo Security::clean($o['seller_name']); ?>
                </p>
                <p class="small text-muted mb-1">
                    <i class="fas fa-box"></i> Quantity: <?php echo $o['quantity']; ?>
                </p>
                <p class="small text-muted mb-0">
                    <i class="fas fa-calendar"></i> <?php echo format_datetime($o['order_date']); ?>
                </p>
            </div>
            <div class="col-md-2 text-center">
                <p class="fw-bold text-success fs-5 mb-0">
                    <?php echo format_currency($o['total_amount']); ?>
                </p>
                <small class="text-muted"><?php echo ucfirst($o['payment_method']); ?></small>
            </div>
            <div class="col-md-2 text-center">
                <span class="badge bg-<?php 
                    echo $o['payment_status']==='paid'?'success':
                        ($o['payment_status']==='pending'?'warning':'danger');
                ?> mb-2 w-100">
                    <?php echo ucfirst($o['payment_status']); ?>
                </span>
                <span class="badge bg-<?php 
                    echo $o['delivery_status']==='delivered'?'success':
                        ($o['delivery_status']==='shipped'?'info':'warning');
                ?> w-100">
                    <?php echo ucfirst($o['delivery_status']); ?>
                </span>
            </div>
            <div class="col-md-2 text-end">
                <a href="<?php echo APP_URL; ?>/orders/order-details.php?id=<?php echo $o['order_id']; ?>" 
                   class="btn btn-primary btn-sm w-100 mb-2">
                    <i class="fas fa-eye"></i> View Details
                </a>
                
                <?php if($o['payment_status']==='pending' && $o['delivery_status']==='pending'): ?>
                <!-- NEW: Cancel Order button for unpaid/unshipped orders -->
                <button onclick="cancelOrder(<?php echo $o['order_id']; ?>)" class="btn btn-warning btn-sm w-100 mb-2">
                    <i class="fas fa-times-circle"></i> Cancel Order
                </button>
                <?php endif; ?>
                
                <?php if($o['delivery_status']==='delivered'): ?>
                <a href="<?php echo APP_URL; ?>/disputes/file.php?order_id=<?php echo $o['order_id']; ?>" 
                   class="btn btn-outline-danger btn-sm w-100">
                    <i class="fas fa-exclamation-triangle"></i> Dispute
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php endforeach; ?>

<?php endif; ?>

</div>

<script>
function cancelOrder(orderId) {
    if(confirm('⚠️ Are you sure you want to CANCEL this order?\n\nThis action cannot be undone.')) {
        window.location.href = '<?php echo APP_URL; ?>/orders/order-cancel.php?id=' + orderId;
    }
}
</script>

<?php require_once __DIR__.'/../includes/footer.php'; ?>
