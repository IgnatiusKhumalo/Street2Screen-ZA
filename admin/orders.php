<?php
$pageTitle='All Orders';
require_once __DIR__.'/../includes/header.php';
Security::requireAdmin();

$db=new Database();

// Get all orders
$db->query("SELECT o.*,p.product_name,buyer.full_name as buyer_name,buyer.email as buyer_email,seller.full_name as seller_name,seller.email as seller_email FROM orders o JOIN products p ON o.product_id=p.product_id JOIN users buyer ON o.buyer_id=buyer.user_id JOIN users seller ON o.seller_id=seller.user_id ORDER BY o.order_date DESC LIMIT 100");
$orders=$db->fetchAll();

// Calculate statistics
$totalRevenue=0;
$paidOrders=0;
$pendingOrders=0;
$deliveredOrders=0;

foreach($orders as $o){
    if($o['payment_status']==='paid'){
        $totalRevenue+=$o['total_amount'];
        $paidOrders++;
    }
    if($o['payment_status']==='pending'){
        $pendingOrders++;
    }
    if($o['delivery_status']==='delivered'){
        $deliveredOrders++;
    }
}
?>

<div class="container my-5">
<h2 class="fw-bold mb-4"><i class="fas fa-shopping-cart text-info"></i> All Orders (<?php echo count($orders); ?>)</h2>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-white bg-success text-center">
            <div class="card-body">
                <h3><?php echo format_currency($totalRevenue); ?></h3>
                <p class="mb-0">Total Revenue</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-primary text-center">
            <div class="card-body">
                <h3><?php echo $paidOrders; ?></h3>
                <p class="mb-0">Paid Orders</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-warning text-center">
            <div class="card-body">
                <h3><?php echo $pendingOrders; ?></h3>
                <p class="mb-0">Pending Payment</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-info text-center">
            <div class="card-body">
                <h3><?php echo $deliveredOrders; ?></h3>
                <p class="mb-0">Delivered</p>
            </div>
        </div>
    </div>
</div>

<!-- Orders Table -->
<div class="card shadow">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Order #</th>
                        <th>Product</th>
                        <th>Buyer</th>
                        <th>Seller</th>
                        <th>Qty</th>
                        <th>Amount</th>
                        <th>Payment</th>
                        <th>Delivery</th>
                        <th>Method</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($orders as $o): ?>
                    <tr>
                        <td><strong>#<?php echo $o['order_id']; ?></strong></td>
                        <td>
                            <?php echo excerpt(Security::clean($o['product_name']),30); ?>
                        </td>
                        <td>
                            <strong><?php echo Security::clean($o['buyer_name']); ?></strong><br>
                            <small class="text-muted"><?php echo Security::clean($o['buyer_email']); ?></small>
                        </td>
                        <td>
                            <strong><?php echo Security::clean($o['seller_name']); ?></strong><br>
                            <small class="text-muted"><?php echo Security::clean($o['seller_email']); ?></small>
                        </td>
                        <td><?php echo $o['quantity']; ?></td>
                        <td class="fw-bold text-success"><?php echo format_currency($o['total_amount']); ?></td>
                        <td>
                            <span class="badge bg-<?php 
                                echo $o['payment_status']==='paid'?'success':
                                    ($o['payment_status']==='pending'?'warning':'danger');
                            ?>">
                                <?php echo ucfirst($o['payment_status']); ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-<?php 
                                echo $o['delivery_status']==='delivered'?'success':
                                    ($o['delivery_status']==='shipped'?'info':
                                    ($o['delivery_status']==='pending'?'warning':'secondary'));
                            ?>">
                                <?php echo ucfirst($o['delivery_status']); ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-secondary">
                                <?php echo strtoupper($o['payment_method']); ?>
                            </span>
                        </td>
                        <td><?php echo format_date($o['order_date']); ?></td>
                        <td>
                            <a href="<?php echo APP_URL; ?>/orders/order-details.php?id=<?php echo $o['order_id']; ?>" 
                               class="btn btn-sm btn-primary" title="View Details">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if(empty($orders)): ?>
<div class="alert alert-info text-center mt-4">
    <i class="fas fa-info-circle fa-3x mb-3"></i>
    <h5>No Orders Yet</h5>
    <p>Orders will appear here once customers start purchasing.</p>
</div>
<?php endif; ?>

</div>

<?php require_once __DIR__.'/../includes/footer.php'; ?>
