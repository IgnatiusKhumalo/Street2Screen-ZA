<?php
$pageTitle='Shopping Cart';
require_once __DIR__.'/../includes/header.php';
Security::requireLogin();

$db=new Database();
$userId=Security::getUserId();

// Get cart items
$db->query("SELECT c.*,p.product_name,p.price,p.stock_quantity,p.location,u.full_name as seller_name,(SELECT image_path FROM product_images WHERE product_id=p.product_id AND is_primary=1 LIMIT 1)as image FROM cart c JOIN products p ON c.product_id=p.product_id JOIN users u ON p.seller_id=u.user_id WHERE c.user_id=:uid");
$db->bind(':uid',$userId);
$cartItems=$db->fetchAll();

$subtotal=0;
foreach($cartItems as $item){
    $subtotal+=$item['price']*$item['quantity'];
}

$shippingEstimate=50; // Base estimate
$total=$subtotal+$shippingEstimate;
?>

<div class="container my-5">
<h2 class="fw-bold mb-4"><i class="fas fa-shopping-cart text-primary"></i> Shopping Cart</h2>

<?php if(empty($cartItems)): ?>
<div class="alert alert-info text-center">
    <i class="fas fa-shopping-cart fa-5x mb-4 text-muted"></i>
    <h4>Your Cart is Empty</h4>
    <p class="mb-4">Start shopping and add items to your cart!</p>
    <a href="<?php echo APP_URL; ?>/products/index.php" class="btn btn-primary btn-lg">
        <i class="fas fa-shopping-bag"></i> Browse Products
    </a>
</div>
<?php else: ?>

<div class="row">
<!-- Cart Items -->
<div class="col-md-8">
    <?php foreach($cartItems as $item): ?>
    <div class="card mb-3 shadow-sm">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-2">
                    <img src="<?php echo $item['image']?APP_URL.'/'.$item['image']:APP_URL.'/assets/images/placeholder.svg'; ?>" 
                         class="img-fluid rounded" style="height:80px;object-fit:cover">
                </div>
                <div class="col-md-4">
                    <h6 class="mb-1"><?php echo Security::clean($item['product_name']); ?></h6>
                    <p class="small text-muted mb-1">
                        <i class="fas fa-user"></i> <?php echo Security::clean($item['seller_name']); ?>
                    </p>
                    <p class="small text-muted mb-0">
                        <i class="fas fa-map-marker-alt"></i> <?php echo Security::clean($item['location']); ?>
                    </p>
                </div>
                <div class="col-md-2 text-center">
                    <p class="fw-bold mb-0"><?php echo format_currency($item['price']); ?></p>
                    <small class="text-muted">per item</small>
                </div>
                <div class="col-md-2">
                    <label class="small">Quantity</label>
                    <input type="number" 
                           class="form-control" 
                           value="<?php echo $item['quantity']; ?>" 
                           min="1" 
                           max="<?php echo $item['stock_quantity']; ?>" 
                           onchange="updateCart(<?php echo $item['cart_id']; ?>,this.value)">
                    <small class="text-muted"><?php echo $item['stock_quantity']; ?> available</small>
                </div>
                <div class="col-md-2 text-end">
                    <p class="fw-bold text-success fs-5 mb-2">
                        <?php echo format_currency($item['price']*$item['quantity']); ?>
                    </p>
                    <a href="<?php echo APP_URL; ?>/orders/cart-remove.php?id=<?php echo $item['cart_id']; ?>" 
                       class="btn btn-sm btn-danger" 
                       onclick="return confirm('Remove this item?')">
                        <i class="fas fa-trash"></i> Remove
                    </a>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    
    <a href="<?php echo APP_URL; ?>/products/index.php" class="btn btn-outline-primary">
        <i class="fas fa-arrow-left"></i> Continue Shopping
    </a>
</div>

<!-- Order Summary -->
<div class="col-md-4">
    <div class="card shadow sticky-top" style="top:20px">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-receipt"></i> Order Summary</h5>
        </div>
        <div class="card-body">
            <div class="d-flex justify-content-between mb-2">
                <span>Subtotal:</span>
                <strong><?php echo format_currency($subtotal); ?></strong>
            </div>
            <div class="d-flex justify-content-between mb-3">
                <span>Shipping:</span>
                <strong class="text-muted">Calculated at checkout</strong>
            </div>
            <hr>
            <div class="d-flex justify-content-between mb-3">
                <strong class="fs-5">Total:</strong>
                <strong class="text-success fs-4"><?php echo format_currency($subtotal); ?></strong>
            </div>
            
            <div class="d-grid">
                <a href="<?php echo APP_URL; ?>/orders/checkout.php" class="btn btn-success btn-lg">
                    <i class="fas fa-credit-card"></i> Proceed to Checkout
                </a>
            </div>
            
            <div class="mt-3">
                <small class="text-muted">
                    <i class="fas fa-shield-alt"></i> Secure checkout powered by PayFast
                </small>
            </div>
        </div>
    </div>
</div>

</div>

<?php endif; ?>

</div>

<script>
function updateCart(cartId,quantity){
    if(quantity<1) return;
    fetch('<?php echo APP_URL; ?>/orders/cart-update.php',{
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:'cart_id='+cartId+'&quantity='+quantity
    }).then(()=>location.reload());
}
</script>

<?php require_once __DIR__.'/../includes/footer.php'; ?>
