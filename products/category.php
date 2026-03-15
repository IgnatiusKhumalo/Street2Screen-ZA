<?php
$pageTitle='Category';
require_once __DIR__.'/../includes/header.php';

$categoryId=get_get('id');
$db=new Database();

$db->query("SELECT * FROM categories WHERE category_id=:id");
$db->bind(':id',$categoryId);
$category=$db->fetch();

if(!$category){
    redirect(APP_URL.'/products/index.php');
}

$db->query("SELECT p.*,u.full_name as seller_name,(SELECT image_path FROM product_images WHERE product_id=p.product_id AND is_primary=1 LIMIT 1)as image FROM products p JOIN users u ON p.seller_id=u.user_id WHERE p.category_id=:cid AND p.status='active' ORDER BY p.created_at DESC");
$db->bind(':cid',$categoryId);
$products=$db->fetchAll();
?>

<div class="container my-5">
<div class="d-flex align-items-center mb-4">
    <i class="fas <?php echo $category['icon_class']; ?> fa-3x text-primary me-3"></i>
    <div>
        <h2 class="fw-bold mb-0"><?php echo Security::clean($category['category_name']); ?></h2>
        <p class="text-muted mb-0"><?php echo Security::clean($category['description']); ?></p>
    </div>
</div>

<p class="mb-4"><?php echo count($products); ?> <?php echo t('products_found'); ?></p>

<?php if(empty($products)): ?>
<div class="alert alert-info text-center">
    <i class="fas fa-info-circle fa-3x mb-3"></i>
    <h5><?php echo t('no_products_found'); ?></h5>
    <a href="<?php echo APP_URL; ?>/products/index.php" class="btn btn-primary"><?php echo t('browse_products_btn'); ?></a>
</div>
<?php else: ?>

<div class="row">
    <?php foreach($products as $p): ?>
    <div class="col-md-3 col-sm-6 mb-4">
        <div class="card h-100 shadow-sm product-card">
            <!-- Product Image with Favorite Button -->
            <div class="position-relative">
                <img src="<?php echo $p['image']?APP_URL.'/'.$p['image']:APP_URL.'/assets/images/placeholder.svg'; ?>" 
                     class="card-img-top" style="height:200px;object-fit:cover">
                
                <!-- FAVORITE BUTTON (NEW) -->
                <?php if(Security::isLoggedIn()): ?>
                    <?php
                        $db2 = new Database();
                        $db2->query("SELECT 1 FROM favorites WHERE user_id=:uid AND product_id=:pid");
                        $db2->bind(':uid', Security::getUserId());
                        $db2->bind(':pid', $p['product_id']);
                        $isFav = $db2->fetch();
                    ?>
                    <button class="btn btn-sm <?php echo $isFav?'btn-danger':'btn-outline-danger'; ?> position-absolute top-0 start-0 m-2"
                            style="z-index:2"
                            onclick="toggleFavorite(<?php echo $p['product_id']; ?>, <?php echo $isFav?'true':'false'; ?>)"
                            id="fav-btn-<?php echo $p['product_id']; ?>"
                            title="<?php echo $isFav?'Remove from favorites':'Add to favorites'; ?>">
                        <i class="<?php echo $isFav?'fas':'far'; ?> fa-heart"></i>
                    </button>
                <?php endif; ?>
            </div>
            
            <div class="card-body">
                <h6 class="card-title"><?php echo excerpt(Security::clean($p['product_name']),35); ?></h6>
                <p class="text-success fw-bold mb-2"><?php echo format_currency($p['price']); ?></p>
                <p class="small text-muted mb-0">
                    <i class="fas fa-map-marker-alt"></i> <?php echo Security::clean($p['location']); ?>
                </p>
            </div>
            <div class="card-footer bg-white border-0">
                <a href="<?php echo APP_URL; ?>/products/view.php?id=<?php echo $p['product_id']; ?>" 
                   class="btn btn-primary btn-sm w-100"><?php echo t('view_details'); ?></a>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php endif; ?>

</div>

<style>
.product-card{
    transition:all 0.3s ease;
}
.product-card:hover{
    transform:translateY(-5px);
    box-shadow:0 10px 20px rgba(0,0,0,0.15)!important;
}
</style>

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
                btn.className = 'btn btn-sm btn-danger position-absolute top-0 start-0 m-2';
                btn.title = 'Remove from favorites';
                icon.className = 'fas fa-heart';
                btn.onclick = function() { toggleFavorite(productId, true); };
            } else {
                // Removed from favorites
                btn.className = 'btn btn-sm btn-outline-danger position-absolute top-0 start-0 m-2';
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
