<?php
$pageTitle='My Favorites';
require_once __DIR__.'/../includes/header.php';
Security::requireLogin();

$db=new Database();
$userId=Security::getUserId();

// Handle add to favorites
if(is_post_request()&&get_post('action')==='add'){
    $productId=get_post('product_id');
    $db->query("INSERT IGNORE INTO favorites(user_id,product_id,added_at)VALUES(:uid,:pid,NOW())");
    $db->bind(':uid',$userId);
    $db->bind(':pid',$productId);
    $db->execute();
    redirect_with_success(APP_URL.'/user/favorites.php','Added to favorites');
}

// Handle remove from favorites
if(get_get('remove')){
    $productId=get_get('remove');
    $db->query("DELETE FROM favorites WHERE user_id=:uid AND product_id=:pid");
    $db->bind(':uid',$userId);
    $db->bind(':pid',$productId);
    $db->execute();
    redirect_with_success(APP_URL.'/user/favorites.php','Removed from favorites');
}

// Get favorites
$db->query("SELECT p.*,u.full_name as seller_name,(SELECT image_path FROM product_images WHERE product_id=p.product_id AND is_primary=1 LIMIT 1)as image,f.added_at FROM favorites f JOIN products p ON f.product_id=p.product_id JOIN users u ON p.seller_id=u.user_id WHERE f.user_id=:uid AND p.status='active' ORDER BY f.added_at DESC");
$db->bind(':uid',$userId);
$favorites=$db->fetchAll();
?>

<div class="container my-5">
<h2 class="fw-bold mb-4"><i class="fas fa-heart text-danger"></i> My Favorites (<?php echo count($favorites); ?>)</h2>

<?php if(empty($favorites)): ?>
<div class="alert alert-info text-center">
    <i class="fas fa-heart fa-5x mb-4 text-muted"></i>
    <h4>No Favorites Yet</h4>
    <p class="mb-4">Start adding products to your favorites to see them here!</p>
    <a href="<?php echo APP_URL; ?>/products/index.php" class="btn btn-primary btn-lg">
        <i class="fas fa-shopping-bag"></i> Browse Products
    </a>
</div>
<?php else: ?>

<div class="row">
    <?php foreach($favorites as $p): ?>
    <div class="col-md-3 col-sm-6 mb-4">
        <div class="card h-100 shadow-sm">
            <div class="position-relative">
                <img src="<?php echo $p['image']?APP_URL.'/'.$p['image']:APP_URL.'/assets/images/placeholder.svg'; ?>" 
                     class="card-img-top" style="height:200px;object-fit:cover">
                <button class="btn btn-danger btn-sm position-absolute top-0 end-0 m-2" 
                        onclick="if(confirm('Remove from favorites?'))location.href='?remove=<?php echo $p['product_id']; ?>'">
                    <i class="fas fa-heart-broken"></i>
                </button>
            </div>
            <div class="card-body">
                <h6 class="card-title"><?php echo excerpt(Security::clean($p['product_name']),40); ?></h6>
                <p class="text-success fw-bold fs-5 mb-2"><?php echo format_currency($p['price']); ?></p>
                <p class="small text-muted mb-1">
                    <i class="fas fa-user"></i> <?php echo Security::clean($p['seller_name']); ?>
                </p>
                <p class="small text-muted mb-2">
                    <i class="fas fa-map-marker-alt"></i> <?php echo Security::clean($p['location']); ?>
                </p>
                <small class="text-muted">Added <?php echo time_ago($p['added_at']); ?></small>
            </div>
            <div class="card-footer bg-white border-0">
                <a href="<?php echo APP_URL; ?>/products/view.php?id=<?php echo $p['product_id']; ?>" 
                   class="btn btn-primary btn-sm w-100">
                    <i class="fas fa-eye"></i> View Product
                </a>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php endif; ?>

</div>

<?php require_once __DIR__.'/../includes/footer.php'; ?>
