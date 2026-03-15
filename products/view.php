<?php
$pageTitle='Product Details';
require_once __DIR__.'/../includes/header.php';

$productId=get_get('id');
if(empty($productId)){
    redirect(APP_URL.'/products/index.php');
}

$db=new Database();

// Include MyCourier class for shipping calculation
require_once __DIR__.'/../includes/MyCourier.php';

// Get product details
$db->query("SELECT p.*,u.user_id as seller_id,u.full_name as seller_name,u.email as seller_email,u.phone as seller_phone,u.township,u.city,u.province,c.category_name FROM products p JOIN users u ON p.seller_id=u.user_id JOIN categories c ON p.category_id=c.category_id WHERE p.product_id=:id");
$db->bind(':id',$productId);
$product=$db->fetch();

if(!$product){
    redirect_with_error(APP_URL.'/products/index.php','Product not found');
}

// Update view count
$db->query("UPDATE products SET view_count=view_count+1 WHERE product_id=:id");
$db->bind(':id',$productId);
$db->execute();

// Get product images
$db->query("SELECT * FROM product_images WHERE product_id=:id ORDER BY is_primary DESC,display_order ASC");
$db->bind(':id',$productId);
$images=$db->fetchAll();

// Check if favorited (NEW)
$isFavorited = false;
if(Security::isLoggedIn()) {
    $db->query("SELECT 1 FROM favorites WHERE user_id=:uid AND product_id=:pid");
    $db->bind(':uid', Security::getUserId());
    $db->bind(':pid', $productId);
    $isFavorited = $db->fetch() ? true : false;
}

// Calculate shipping
$shipping=MyCourier::calculateShipping($product['township'],$product['province'],'Johannesburg','Gauteng',1);

$isOwner=Security::isLoggedIn()&&Security::getUserId()==$product['seller_id'];
?>

<div class="container my-5">
<div class="row">

<!-- Product Images -->
<div class="col-md-6 mb-4">
    <?php if(empty($images)): ?>
    <img src="<?php echo APP_URL; ?>/assets/images/placeholder.svg" class="img-fluid rounded">
    <?php else: ?>
    <div id="productCarousel" class="carousel slide position-relative" data-bs-ride="carousel">
        <!-- FAVORITE BUTTON (NEW) - On carousel -->
        <?php if(Security::isLoggedIn() && !$isOwner): ?>
        <button class="btn btn-lg <?php echo $isFavorited?'btn-danger':'btn-outline-danger'; ?> position-absolute top-0 start-0 m-3"
                style="z-index:10"
                onclick="toggleFavorite(<?php echo $productId; ?>, <?php echo $isFavorited?'true':'false'; ?>)"
                id="fav-btn-<?php echo $productId; ?>"
                title="<?php echo $isFavorited?'Remove from favorites':'Add to favorites'; ?>">
            <i class="<?php echo $isFavorited?'fas':'far'; ?> fa-heart"></i>
        </button>
        <?php endif; ?>
        
        <div class="carousel-inner">
            <?php foreach($images as $i=>$img): ?>
            <div class="carousel-item <?php echo $i===0?'active':''; ?>">
                <img src="<?php echo APP_URL.'/'.$img['image_path']; ?>" class="d-block w-100 rounded" style="height:400px;object-fit:cover">
            </div>
            <?php endforeach; ?>
        </div>
        <?php if(count($images)>1): ?>
        <button class="carousel-control-prev" type="button" data-bs-target="#productCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon"></span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#productCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon"></span>
        </button>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<!-- Product Details -->
<div class="col-md-6">
    <h2 class="fw-bold mb-3"><?php echo Security::clean($product['product_name']); ?></h2>
    <h3 class="text-success mb-3"><?php echo format_currency($product['price']); ?></h3>
    
    <div class="mb-3">
        <span class="badge bg-primary me-2"><?php echo Security::clean($product['category_name']); ?></span>
        <span class="badge bg-secondary me-2"><?php echo ucfirst(str_replace('_',' ',$product['condition'])); ?></span>
        <?php if($product['stock_quantity']>0): ?>
        <span class="badge bg-success"><?php echo t('in_stock_qty'); ?> (<?php echo $product['stock_quantity']; ?>)</span>
        <?php else: ?>
        <span class="badge bg-danger"><?php echo t('out_of_stock'); ?></span>
        <?php endif; ?>
        <?php if($product['featured']&&strtotime($product['featured_until'])>time()): ?>
        <span class="badge bg-warning text-dark"><i class="fas fa-star"></i> <?php echo t('featured'); ?></span>
        <?php endif; ?>
    </div>
    
    <!-- Description -->
    <div class="card mb-3">
        <div class="card-header bg-light">
            <h6 class="mb-0"><i class="fas fa-align-left"></i> <?php echo t('description'); ?></h6>
        </div>
        <div class="card-body">
            <p class="mb-0"><?php echo nl2br(Security::clean($product['description'])); ?></p>
        </div>
    </div>
    
    <!-- Seller Info -->
    <div class="card mb-3">
        <div class="card-header bg-light">
            <h6 class="mb-0"><i class="fas fa-user"></i> <?php echo t('seller_information'); ?></h6>
        </div>
        <div class="card-body">
            <p class="mb-1"><strong><?php echo t('name'); ?>:</strong> <?php echo Security::clean($product['seller_name']); ?></p>
            <p class="mb-1"><strong><?php echo t('location'); ?>:</strong> <?php echo Security::clean($product['location']); ?></p>
            <p class="mb-0"><strong><?php echo t('views'); ?>:</strong> <?php echo $product['view_count']; ?> <?php echo t('views'); ?></p>
        </div>
    </div>
    
    <!-- Shipping -->
    <div class="card mb-3">
        <div class="card-header bg-light">
            <h6 class="mb-0"><i class="fas fa-truck"></i> <?php echo t('shipping_information'); ?></h6>
        </div>
        <div class="card-body">
            <p class="mb-1"><strong><?php echo t('estimated_cost'); ?>:</strong> <?php echo format_currency($shipping['cost']); ?></p>
            <p class="mb-0"><strong><?php echo t('delivery_time'); ?>:</strong> <?php echo $shipping['estimate_days']; ?> <?php echo t('days'); ?> (<?php echo ucfirst($shipping['zone']); ?>)</p>
        </div>
    </div>
    
    <!-- Actions -->
    <div class="d-grid gap-2">
        <?php if($isOwner): ?>
        <a href="<?php echo APP_URL; ?>/products/edit.php?id=<?php echo $productId; ?>" class="btn btn-primary btn-lg">
            <i class="fas fa-edit"></i> <?php echo t('edit_product'); ?>
        </a>
        <?php elseif(Security::isLoggedIn()&&$product['stock_quantity']>0): ?>
        <a href="<?php echo APP_URL; ?>/orders/cart-add.php?product_id=<?php echo $productId; ?>" class="btn btn-success btn-lg">
            <i class="fas fa-shopping-cart"></i> <?php echo t('add_to_cart'); ?>
        </a>
        <a href="<?php echo APP_URL; ?>/messages/send.php?seller_id=<?php echo $product['seller_id']; ?>&product_id=<?php echo $productId; ?>" class="btn btn-outline-primary btn-lg">
            <i class="fas fa-envelope"></i> <?php echo t('message_seller'); ?>
        </a>
        <?php else: ?>
        <div class="alert alert-info mb-0">
            <a href="<?php echo APP_URL; ?>/auth/login.php"><?php echo t('login'); ?></a> <?php echo t('login_to_purchase'); ?>
        </div>
        <?php endif; ?>
    </div>
</div>

</div>
</div>

<!-- FAVORITE TOGGLE SCRIPT (NEW) -->
<script>
function toggleFavorite(productId, isCurrentlyFavorited) {
    const btn = document.getElementById('fav-btn-' + productId);
    const icon = btn.querySelector('i');
    
    // Show loading
    btn.disabled = true;
    icon.className = 'fas fa-spinner fa-spin';
    
    const action = isCurrentlyFavorited ? 'remove' : 'add';
    const formData = new FormData();
    formData.append('product_id', productId);
    formData.append('action', action);
    
    fetch('<?php echo APP_URL; ?>/ajax/toggle-favorite.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        btn.disabled = false;
        if(data.success) {
            if(action === 'add') {
                // Changed to favorited
                btn.className = 'btn btn-lg btn-danger position-absolute top-0 start-0 m-3';
                btn.title = 'Remove from favorites';
                icon.className = 'fas fa-heart';
                btn.onclick = function() { toggleFavorite(productId, true); };
            } else {
                // Removed from favorites
                btn.className = 'btn btn-lg btn-outline-danger position-absolute top-0 start-0 m-3';
                btn.title = 'Add to favorites';
                icon.className = 'far fa-heart';
                btn.onclick = function() { toggleFavorite(productId, false); };
            }
        } else {
            // Error - revert icon
            icon.className = isCurrentlyFavorited ? 'fas fa-heart' : 'far fa-heart';
            alert(data.message || 'Error occurred');
        }
    })
    .catch(error => {
        btn.disabled = false;
        icon.className = isCurrentlyFavorited ? 'fas fa-heart' : 'far fa-heart';
        console.error('Error:', error);
        alert('Network error occurred');
    });
}
</script>

<?php require_once __DIR__.'/../includes/footer.php'; ?>
