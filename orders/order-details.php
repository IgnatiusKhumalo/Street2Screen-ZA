<?php
$pageTitle='Order Details';
require_once __DIR__.'/../includes/header.php';
Security::requireLogin();

$orderId=get_get('id');
$db=new Database();
$userId=Security::getUserId();

// WORKAROUND: Use separate queries
$db->query("SELECT * FROM orders WHERE order_id=:id");
$db->bind(':id',$orderId);
$order=$db->fetch();

if(!$order){
    redirect_with_error(APP_URL.'/orders/my-orders.php','Order not found');
}

$isBuyer=$order['buyer_id']==$userId;
$isSeller=$order['seller_id']==$userId;

if(!$isBuyer && !$isSeller){
    redirect_with_error(APP_URL.'/orders/my-orders.php','Access denied');
}

// Get product, buyer, seller details
$db->query("SELECT product_name,description FROM products WHERE product_id=:pid");
$db->bind(':pid',$order['product_id']);
$product=$db->fetch();

$db->query("SELECT image_path FROM product_images WHERE product_id=:pid AND is_primary=1 LIMIT 1");
$db->bind(':pid',$order['product_id']);
$imageRow=$db->fetch();
$image=$imageRow?$imageRow['image_path']:null;

$db->query("SELECT full_name,email,phone FROM users WHERE user_id=:uid");
$db->bind(':uid',$order['buyer_id']);
$buyer=$db->fetch();

$db->query("SELECT full_name,email,phone FROM users WHERE user_id=:uid");
$db->bind(':uid',$order['seller_id']);
$seller=$db->fetch();

$payStatus = $order['payment_status'] ?: 'pending';
$delStatus = $order['delivery_status'] ?: 'pending';

// Delivery stages progress
$stages = ['pending', 'processing', 'shipped', 'in_transit', 'delivered'];
$currentStageIndex = array_search($delStatus, $stages);
if($currentStageIndex === false) $currentStageIndex = 0;
$progress = ($currentStageIndex / (count($stages) - 1)) * 100;
?>

<div class="container my-5">
<h2 class="fw-bold mb-4"><i class="fas fa-receipt"></i> <?php echo t('order'); ?> #<?php echo $order['order_id']; ?></h2>

<!-- DELIVERY TRACKER -->
<?php if($order['tracking_number']): ?>
<div class="card shadow-sm mb-3">
    <div class="card-header bg-info text-white">
        <h5 class="mb-0"><i class="fas fa-map-marker-alt"></i> <?php echo t('delivery_tracking'); ?>: <?php echo $order['tracking_number']; ?></h5>
    </div>
    <div class="card-body">
        <div class="progress mb-3" style="height:30px">
            <div class="progress-bar bg-success progress-bar-striped progress-bar-animated" 
                 style="width:<?php echo $progress; ?>%">
                <?php echo round($progress); ?>%
            </div>
        </div>
        
        <div class="row text-center">
            <?php 
            $stageIcons = ['clock', 'cog', 'shipping-fast', 'truck', 'check-circle'];
            $stageLabels = [
                t('pending'),
                t('processing'),
                t('shipped'),
                t('in_transit'),
                t('delivered')
            ];
            
            foreach($stages as $i => $stage): 
                $isComplete = $i <= $currentStageIndex;
                $isCurrent = $i == $currentStageIndex;
            ?>
            <div class="col">
                <div class="text-center">
                    <div class="mb-2">
                        <i class="fas fa-<?php echo $stageIcons[$i]; ?> fa-2x <?php 
                            echo $isComplete ? 'text-success' : 'text-muted'; 
                        ?>"></i>
                    </div>
                    <small class="fw-bold <?php echo $isCurrent ? 'text-primary' : ''; ?>">
                        <?php echo ucfirst($stageLabels[$i]); ?>
                    </small>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="row">
<!-- Order Details -->
<div class="col-md-8">
    <div class="card shadow-sm mb-3">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-box"></i> <?php echo t('order_information'); ?></h5>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-4">
                    <img src="<?php echo $image?APP_URL.'/'.$image:APP_URL.'/assets/images/placeholder.svg'; ?>" 
                         class="img-fluid rounded">
                </div>
                <div class="col-md-8">
                    <h5><?php echo Security::clean($product['product_name']); ?></h5>
                    <p class="text-muted"><?php echo excerpt(Security::clean($product['description']),150); ?></p>
                    <div class="row mt-3">
                        <div class="col-6">
                            <strong><?php echo t('quantity'); ?>:</strong> <?php echo $order['quantity']; ?>
                        </div>
                        <div class="col-6">
                            <strong><?php echo t('unit_price'); ?>:</strong> <?php echo format_currency($order['unit_price']); ?>
                        </div>
                        <div class="col-6 mt-2">
                            <strong><?php echo t('total'); ?>:</strong> <span class="text-success fw-bold"><?php echo format_currency($order['total_amount']); ?></span>
                        </div>
                        <div class="col-6 mt-2">
                            <strong><?php echo t('payment'); ?>:</strong> <?php echo ucfirst($order['payment_method']); ?>
                        </div>
                        
                        <!-- Commission Info (Seller only) -->
                        <?php if($isSeller && $order['commission_amount'] > 0): ?>
                        <div class="col-12 mt-3">
                            <div class="alert alert-info">
                                <strong><i class="fas fa-info-circle"></i> <?php echo t('commission_breakdown'); ?>:</strong><br>
                                <?php echo t('order_total'); ?>: <?php echo format_currency($order['total_amount']); ?><br>
                                <?php echo t('platform_fee'); ?> (10%): <span class="text-danger">-<?php echo format_currency($order['commission_amount']); ?></span><br>
                                <strong><?php echo t('your_profit'); ?>: <span class="text-success"><?php echo format_currency($order['seller_profit']); ?></span></strong>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card shadow-sm mb-3">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0"><i class="fas fa-truck"></i> <?php echo t('delivery_information'); ?></h5>
        </div>
        <div class="card-body">
            <p class="mb-2"><strong><?php echo t('address'); ?>:</strong></p>
            <p><?php echo nl2br(Security::clean($order['delivery_address'])); ?></p>
            <?php if($order['tracking_number']): ?>
            <p class="mb-0"><strong><?php echo t('tracking_number'); ?>:</strong> 
                <code class="bg-light p-2 rounded"><?php echo Security::clean($order['tracking_number']); ?></code>
            </p>
            <?php endif; ?>
        </div>
    </div>
    
    <?php if($isBuyer): ?>
    <div class="card shadow-sm">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0"><i class="fas fa-user"></i> <?php echo t('seller_information'); ?></h5>
        </div>
        <div class="card-body">
            <p class="mb-1"><strong><?php echo t('name'); ?>:</strong> <?php echo Security::clean($seller['full_name']); ?></p>
            <p class="mb-1"><strong><?php echo t('email'); ?>:</strong> <?php echo Security::clean($seller['email']); ?></p>
            <p class="mb-0"><strong><?php echo t('phone'); ?>:</strong> <?php echo Security::clean($seller['phone']); ?></p>
        </div>
    </div>
    <?php endif; ?>
    
    <?php if($isSeller): ?>
    <div class="card shadow-sm">
        <div class="card-header bg-warning text-dark">
            <h5 class="mb-0"><i class="fas fa-user"></i> <?php echo t('buyer_information'); ?></h5>
        </div>
        <div class="card-body">
            <p class="mb-1"><strong><?php echo t('name'); ?>:</strong> <?php echo Security::clean($buyer['full_name']); ?></p>
            <p class="mb-1"><strong><?php echo t('email'); ?>:</strong> <?php echo Security::clean($buyer['email']); ?></p>
            <p class="mb-0"><strong><?php echo t('phone'); ?>:</strong> <?php echo Security::clean($buyer['phone']); ?></p>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Status & Actions -->
<div class="col-md-4">
    <div class="card shadow" style="position:sticky; top:80px; z-index:10;">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0"><i class="fas fa-clipboard-check"></i> <?php echo t('status'); ?></h5>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <strong><?php echo t('payment'); ?>:</strong><br>
                <span class="badge bg-<?php 
                    echo $payStatus==='paid'?'success':($payStatus==='pending'?'warning':'danger');
                ?> w-100 py-2">
                    <?php echo ucfirst($payStatus); ?>
                </span>
            </div>
            <div class="mb-3">
                <strong><?php echo t('delivery_information'); ?>:</strong><br>
                <span class="badge bg-<?php 
                    echo $delStatus==='delivered'?'success':
                        ($delStatus==='in_transit'?'primary':
                        ($delStatus==='shipped'?'info':
                        ($delStatus==='processing'?'warning':'secondary')));
                ?> w-100 py-2">
                    <?php echo ucfirst(str_replace('_', ' ', $delStatus)); ?>
                </span>
            </div>
            <hr>
            <div class="mb-2">
                <strong><?php echo t('order_date'); ?>:</strong><br>
                <small><?php echo format_datetime($order['order_date']); ?></small>
            </div>
            <?php if($order['payment_date']): ?>
            <div class="mb-2">
                <strong><?php echo t('payment_date'); ?>:</strong><br>
                <small><?php echo format_datetime($order['payment_date']); ?></small>
            </div>
            <?php endif; ?>
            <?php if($order['shipped_date']): ?>
            <div class="mb-2">
                <strong><?php echo t('shipped'); ?>:</strong><br>
                <small><?php echo format_datetime($order['shipped_date']); ?></small>
            </div>
            <?php endif; ?>
            <?php if($order['delivery_date']): ?>
            <div class="mb-2">
                <strong><?php echo t('delivered'); ?>:</strong><br>
                <small><?php echo format_datetime($order['delivery_date']); ?></small>
            </div>
            <?php endif; ?>
            
            <hr>
            
            <!-- BUYER ACTIONS -->
            <?php if($isBuyer): ?>
                <?php if($payStatus==='pending' && ($delStatus==='pending' || $delStatus=='')): ?>
                <button onclick="cancelOrder()" class="btn btn-warning w-100 mb-2">
                    <i class="fas fa-times-circle"></i> <?php echo t('cancel_order'); ?>
                </button>
                <?php endif; ?>
                
                <?php if($delStatus==='shipped' || $delStatus==='in_transit'): ?>
                <button onclick="confirmDelivery()" class="btn btn-success w-100 mb-2">
                    <i class="fas fa-check-circle"></i> <?php echo t('confirm_received'); ?>
                </button>
                <?php endif; ?>
            <?php endif; ?>
            
            <!-- SELLER ACTIONS -->
            <?php if($isSeller): ?>
                <?php if($payStatus==='pending' || $payStatus==''): ?>
                <button onclick="markPaid()" class="btn btn-success w-100 mb-2">
                    <i class="fas fa-money-bill"></i> <?php echo t('confirm_payment'); ?>
                </button>
                <?php endif; ?>
                
                <?php if($payStatus==='paid' && ($delStatus==='processing' || $delStatus==='pending' || $delStatus=='')): ?>
                <button onclick="shipOrder()" class="btn btn-info w-100 mb-2">
                    <i class="fas fa-shipping-fast"></i> <?php echo t('ship_order'); ?> (Auto-Track)
                </button>
                <?php endif; ?>
                
                <?php if($delStatus==='shipped'): ?>
                <button onclick="markInTransit()" class="btn btn-primary w-100 mb-2">
                    <i class="fas fa-truck"></i> <?php echo t('mark_in_transit'); ?>
                </button>
                <?php endif; ?>
                
                <?php if($delStatus==='in_transit' || $delStatus==='shipped'): ?>
                <button onclick="markDelivered()" class="btn btn-success w-100 mb-2">
                    <i class="fas fa-check"></i> <?php echo t('mark_delivered'); ?>
                </button>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

</div>
</div>

<script>
function cancelOrder() {
    if(confirm('⚠️ Cancel this order?')) {
        window.location.href = '<?php echo APP_URL; ?>/orders/order-cancel.php?id=<?php echo $orderId; ?>';
    }
}

function confirmDelivery() {
    if(confirm('✅ Confirm you received this order?')) {
        window.location.href = '<?php echo APP_URL; ?>/orders/update-order-status.php?id=<?php echo $orderId; ?>&action=confirm_delivery';
    }
}

function markPaid() {
    if(confirm('💰 Confirm payment received?\n\n10% commission will be deducted automatically.')) {
        window.location.href = '<?php echo APP_URL; ?>/orders/update-order-status.php?id=<?php echo $orderId; ?>&action=mark_paid';
    }
}

function shipOrder() {
    if(confirm('📦 Ship this order?\n\nTracking number will be generated automatically.')) {
        window.location.href = '<?php echo APP_URL; ?>/orders/update-order-status.php?id=<?php echo $orderId; ?>&action=ship_order';
    }
}

function markInTransit() {
    if(confirm('🚚 Mark order as in transit?')) {
        window.location.href = '<?php echo APP_URL; ?>/orders/update-order-status.php?id=<?php echo $orderId; ?>&action=mark_in_transit';
    }
}

function markDelivered() {
    if(confirm('📦 Confirm order delivered?')) {
        window.location.href = '<?php echo APP_URL; ?>/orders/update-order-status.php?id=<?php echo $orderId; ?>&action=mark_delivered';
    }
}
</script>

<?php require_once __DIR__.'/../includes/footer.php'; ?>
