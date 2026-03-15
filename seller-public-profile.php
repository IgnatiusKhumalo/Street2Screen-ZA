<?php
$pageTitle='Seller Profile';
require_once __DIR__.'/../includes/header.php';

$sellerId=get_get('id');
$db=new Database();

$db->query("SELECT u.user_id,u.full_name,u.township,u.city,u.province,u.created_at,
    (SELECT COUNT(*) FROM products WHERE seller_id=u.user_id AND status='active') as active_products,
    (SELECT COUNT(*) FROM orders WHERE seller_id=u.user_id AND payment_status='paid') as total_sales,
    (SELECT AVG(rating) FROM reviews WHERE seller_id=u.user_id) as avg_rating,
    (SELECT COUNT(*) FROM reviews WHERE seller_id=u.user_id) as review_count,
    (SELECT verification_status FROM verification_documents WHERE user_id=u.user_id LIMIT 1) as verified_status
FROM users u
WHERE u.user_id=:id AND u.user_type IN('seller','both') AND u.account_status='active'");
$db->bind(':id',$sellerId);
$seller=$db->fetch();

if(!$seller){
    redirect_with_error(APP_URL.'/products/index.php','Seller not found');
}

// Get seller's active products
$db->query("SELECT p.*,c.category_name,
    (SELECT image_path FROM product_images WHERE product_id=p.product_id AND is_primary=1 LIMIT 1) as image
FROM products p
JOIN categories c ON p.category_id=c.category_id
WHERE p.seller_id=:id AND p.status='active'
ORDER BY p.created_at DESC LIMIT 12");
$db->bind(':id',$sellerId);
$products=$db->fetchAll();

// Get recent reviews
$db->query("SELECT r.*,u.full_name as reviewer_name,p.product_name
FROM reviews r
JOIN users u ON r.reviewer_id=u.user_id
JOIN orders o ON r.order_id=o.order_id
JOIN products p ON o.product_id=p.product_id
WHERE r.seller_id=:id
ORDER BY r.created_at DESC LIMIT 3");
$db->bind(':id',$sellerId);
$recentReviews=$db->fetchAll();
?>

<div class="container my-5">

<!-- Seller Header -->
<div class="card shadow mb-4">
    <div class="card-body p-4">
        <div class="row align-items-center">
            <div class="col-md-2 text-center">
                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mx-auto"
                     style="width:100px;height:100px;font-size:40px">
                    <?php echo strtoupper(substr($seller['full_name'],0,1)); ?>
                </div>
            </div>
            <div class="col-md-6">
                <h3 class="fw-bold mb-1">
                    <?php echo Security::clean($seller['full_name']); ?>
                    <?php if($seller['verified_status']==='approved'): ?>
                    <i class="fas fa-check-circle text-primary ms-2" title="Verified Seller"></i>
                    <?php endif; ?>
                </h3>
                <p class="text-muted mb-2">
                    <i class="fas fa-map-marker-alt"></i>
                    <?php echo Security::clean($seller['township']??$seller['city']??'South Africa'); ?>
                </p>
                <p class="text-muted mb-0">
                    <i class="fas fa-calendar"></i> Member since <?php echo format_date($seller['created_at']); ?>
                </p>
            </div>
            <div class="col-md-4">
                <div class="row text-center">
                    <div class="col-4">
                        <h4 class="fw-bold text-primary"><?php echo $seller['active_products']; ?></h4>
                        <small class="text-muted">Products</small>
                    </div>
                    <div class="col-4">
                        <h4 class="fw-bold text-success"><?php echo $seller['total_sales']; ?></h4>
                        <small class="text-muted">Sales</small>
                    </div>
                    <div class="col-4">
                        <h4 class="fw-bold text-warning"><?php echo number_format($seller['avg_rating'],1); ?></h4>
                        <small class="text-muted">Rating</small>
                    </div>
                </div>
                <div class="d-grid mt-3 gap-2">
                    <?php if(Security::isLoggedIn()&&Security::getUserId()!=$sellerId): ?>
                    <a href="<?php echo APP_URL; ?>/messages/send.php?seller_id=<?php echo $sellerId; ?>"
                       class="btn btn-primary btn-sm">
                        <i class="fas fa-envelope"></i> Message Seller
                    </a>
                    <?php endif; ?>
                    <a href="<?php echo APP_URL; ?>/reviews/view.php?seller_id=<?php echo $sellerId; ?>"
                       class="btn btn-outline-warning btn-sm">
                        <i class="fas fa-star"></i> View All Reviews (<?php echo $seller['review_count']; ?>)
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Seller Products -->
<h4 class="fw-bold mb-3">Products by <?php echo Security::clean($seller['full_name']); ?></h4>
<div class="row mb-4">
    <?php foreach($products as $p): ?>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card h-100 shadow-sm product-card">
            <img src="<?php echo $p['image']?APP_URL.'/'.$p['image']:APP_URL.'/assets/images/placeholder.svg'; ?>"
                 class="card-img-top" style="height:180px;object-fit:cover">
            <div class="card-body">
                <h6><?php echo excerpt(Security::clean($p['product_name']),35); ?></h6>
                <p class="text-success fw-bold mb-0"><?php echo format_currency($p['price']); ?></p>
            </div>
            <div class="card-footer bg-white border-0">
                <a href="<?php echo APP_URL; ?>/products/view.php?id=<?php echo $p['product_id']; ?>"
                   class="btn btn-primary btn-sm w-100">View</a>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php if(empty($products)): ?>
    <div class="col-12"><div class="alert alert-info">No active products at this time.</div></div>
    <?php endif; ?>
</div>

<!-- Recent Reviews -->
<?php if(!empty($recentReviews)): ?>
<h4 class="fw-bold mb-3">Recent Reviews</h4>
<?php foreach($recentReviews as $r): ?>
<div class="card shadow-sm mb-3">
    <div class="card-body">
        <div class="d-flex justify-content-between mb-2">
            <strong><?php echo Security::clean($r['reviewer_name']); ?></strong>
            <div>
                <?php for($i=1;$i<=5;$i++): ?>
                <i class="fas fa-star <?php echo $i<=$r['rating']?'text-warning':'text-muted'; ?>"></i>
                <?php endfor; ?>
            </div>
        </div>
        <small class="text-muted d-block mb-2">
            <?php echo Security::clean($r['product_name']); ?> · <?php echo time_ago($r['created_at']); ?>
        </small>
        <p class="mb-0"><?php echo excerpt(nl2br(Security::clean($r['review_text'])),200); ?></p>
    </div>
</div>
<?php endforeach; ?>
<a href="<?php echo APP_URL; ?>/reviews/view.php?seller_id=<?php echo $sellerId; ?>"
   class="btn btn-outline-warning">View All Reviews</a>
<?php endif; ?>

</div>

<?php require_once __DIR__.'/../includes/footer.php'; ?>
