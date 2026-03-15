<?php
$pageTitle='My Sales';
require_once __DIR__.'/../includes/header.php';
Security::requireLogin();

if(!in_array(Security::getUserType(),['seller','both'])){
    redirect_with_error(APP_URL.'/index.php','Sellers only');
}

$db=new Database();
$userId=Security::getUserId();

$db->query("SELECT o.*,p.product_name,u.full_name as buyer_name,(SELECT image_path FROM product_images WHERE product_id=p.product_id AND is_primary=1 LIMIT 1)as image FROM orders o JOIN products p ON o.product_id=p.product_id JOIN users u ON o.buyer_id=u.user_id WHERE o.seller_id=:uid ORDER BY o.order_date DESC");
$db->bind(':uid',$userId);
$sales=$db->fetchAll();

$totalRevenue=0;
$paidOrders=0;

foreach($sales as $s){
    if($s['payment_status']==='paid'){
        $totalRevenue+=$s['total_amount'];
        $paidOrders++;
    }
}
?>

<div class="container my-5">
<div class="row mb-4">
    <div class="col-md-8">
        <h2 class="fw-bold"><i class="fas fa-chart-line text-success"></i> <?php echo t('my_sales'); ?></h2>
    </div>
    <div class="col-md-4">
        <div class="card bg-success text-white text-center">
            <div class="card-body">
                <h3 class="mb-0"><?php echo format_currency($totalRevenue); ?></h3>
                <p class="mb-0"><?php echo t('total_revenue'); ?> (<?php echo $paidOrders; ?> <?php echo t('paid_orders'); ?>)</p>
            </div>
        </div>
    </div>
</div>

<?php if(empty($sales)): ?>
<div class="alert alert-info text-center">
    <i class="fas fa-info-circle fa-3x mb-3"></i>
    <h5><?php echo t('no_sales_yet'); ?></h5>
    <p><?php echo t('list_more_products'); ?></p>
    <a href="<?php echo APP_URL; ?>/products/add.php" class="btn btn-success">
        <i class="fas fa-plus"></i> <?php echo t('list_product'); ?>
    </a>
</div>
<?php else: ?>

<div class="table-responsive">
    <table class="table table-hover">
        <thead class="table-dark">
            <tr>
                <th><?php echo t('product'); ?></th>
                <th><?php echo t('buyer'); ?></th>
                <th><?php echo t('quantity'); ?></th>
                <th><?php echo t('amount'); ?></th>
                <th><?php echo t('payment'); ?></th>
                <th><?php echo t('delivery_information'); ?></th>
                <th><?php echo t('date'); ?></th>
                <th style="width:200px"><?php echo t('actions'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($sales as $s): ?>
            <tr>
                <td>
                    <div class="d-flex align-items-center">
                        <img src="<?php echo $s['image']?APP_URL.'/'.$s['image']:APP_URL.'/assets/images/placeholder.svg'; ?>" 
                             style="width:50px;height:50px;object-fit:cover;border-radius:5px" class="me-2">
                        <span><?php echo Security::clean($s['product_name']); ?></span>
                    </div>
                </td>
                <td><?php echo Security::clean($s['buyer_name']); ?></td>
                <td><?php echo $s['quantity']; ?></td>
                <td class="fw-bold text-success"><?php echo format_currency($s['total_amount']); ?></td>
                <td>
                    <span class="badge bg-<?php echo $s['payment_status']==='paid'?'success':'warning'; ?>">
                        <?php echo ucfirst($s['payment_status']); ?>
                    </span>
                </td>
                <td>
                    <span class="badge bg-<?php 
                        echo $s['delivery_status']==='delivered'?'success':
                            ($s['delivery_status']==='shipped'?'info':'warning');
                    ?>">
                        <?php echo ucfirst($s['delivery_status']); ?>
                    </span>
                </td>
                <td><?php echo format_date($s['order_date']); ?></td>
                <td>
                    <!-- View Details Button -->
                    <a href="<?php echo APP_URL; ?>/orders/order-details.php?id=<?php echo $s['order_id']; ?>" 
                       class="btn btn-sm btn-primary mb-1">
                        <i class="fas fa-eye"></i> View
                    </a>
                    
                    <!-- Quick Actions -->
                    
                    <!-- UPDATED: Mark Payment Received for ALL payment methods when pending -->
                    <?php if($s['payment_status']==='pending' || $s['payment_status']==''): ?>
                    <button onclick="markPaid(<?php echo $s['order_id']; ?>)" 
                            class="btn btn-sm btn-success mb-1" title="Mark Payment Received">
                        <i class="fas fa-money-bill"></i>
                    </button>
                    <?php endif; ?>
                    
                    <!-- Ship Order button (if paid and not shipped) -->
                    <?php if($s['payment_status']==='paid' && ($s['delivery_status']==='pending' || $s['delivery_status']=='')): ?>
                    <a href="<?php echo APP_URL; ?>/orders/update-order-status.php?id=<?php echo $s['order_id']; ?>&action=ship_order" 
                       class="btn btn-sm btn-info mb-1" title="Ship Order">
                        <i class="fas fa-shipping-fast"></i>
                    </a>
                    <?php endif; ?>
                    
                    <!-- Mark Delivered button (if shipped) -->
                    <?php if($s['delivery_status']==='shipped'): ?>
                    <button onclick="markDelivered(<?php echo $s['order_id']; ?>)" 
                            class="btn btn-sm btn-primary mb-1" title="Mark as Delivered">
                        <i class="fas fa-check"></i>
                    </button>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php endif; ?>

</div>

<script>
function markPaid(orderId) {
    if(confirm('💰 Confirm you received payment for this order?')) {
        window.location.href = '<?php echo APP_URL; ?>/orders/update-order-status.php?id=' + orderId + '&action=mark_paid';
    }
}

function markDelivered(orderId) {
    if(confirm('📦 Confirm this order has been delivered?')) {
        window.location.href = '<?php echo APP_URL; ?>/orders/update-order-status.php?id=' + orderId + '&action=mark_delivered';
    }
}
</script>

<?php require_once __DIR__.'/../includes/footer.php'; ?>
