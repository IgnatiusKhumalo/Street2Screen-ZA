<?php
$pageTitle='Featured Products';
require_once __DIR__.'/../includes/header.php';
Security::requireAdmin();

$db=new Database();

// Handle feature/unfeature
if(is_post_request()&&Security::validateCSRFToken(get_post('csrf_token'))){
    $productId=get_post('product_id');
    $action=get_post('action');

    if($action==='feature'){
        $db->query("SELECT setting_value FROM platform_settings WHERE setting_key='featured_duration'");
        $setting=$db->fetch();
        $days=(int)($setting['setting_value']??90);

        $db->query("UPDATE products SET featured=1, featured_until=DATE_ADD(NOW(),INTERVAL :days DAY) WHERE product_id=:id");
        $db->bind(':days',$days);
        $db->bind(':id',$productId);
        $db->execute();

        // Log action
        $db->query("INSERT INTO admin_logs(admin_id,action_type,target_type,target_id,action_details,ip_address,timestamp)
        VALUES(:admin,'feature_product','product',:prod,:details,:ip,NOW())");
        $db->bind(':admin',Security::getUserId());
        $db->bind(':prod',$productId);
        $db->bind(':details',json_encode(['days'=>$days]));
        $db->bind(':ip',$_SERVER['REMOTE_ADDR']??'');
        $db->execute();

        redirect_with_success(APP_URL.'/admin/featured-products.php','Product featured for '.$days.' days!');

    }elseif($action==='unfeature'){
        $db->query("UPDATE products SET featured=0, featured_until=NULL WHERE product_id=:id");
        $db->bind(':id',$productId);
        $db->execute();
        redirect_with_success(APP_URL.'/admin/featured-products.php','Product removed from featured');
    }
}

// Get currently featured products
$db->query("SELECT p.*,u.full_name as seller_name,c.category_name,
    (SELECT image_path FROM product_images WHERE product_id=p.product_id AND is_primary=1 LIMIT 1) as image
FROM products p
JOIN users u ON p.seller_id=u.user_id
JOIN categories c ON p.category_id=c.category_id
WHERE p.featured=1 AND p.featured_until>NOW() AND p.status='active'
ORDER BY p.featured_until ASC");
$featuredProducts=$db->fetchAll();

// Get non-featured active products
$db->query("SELECT p.*,u.full_name as seller_name,c.category_name,
    (SELECT image_path FROM product_images WHERE product_id=p.product_id AND is_primary=1 LIMIT 1) as image
FROM products p
JOIN users u ON p.seller_id=u.user_id
JOIN categories c ON p.category_id=c.category_id
WHERE (p.featured=0 OR p.featured_until<=NOW()) AND p.status='active'
ORDER BY p.created_at DESC LIMIT 50");
$regularProducts=$db->fetchAll();

$csrfToken=Security::generateCSRFToken();
?>

<div class="container my-5">
<h2 class="fw-bold mb-4"><i class="fas fa-star text-warning"></i> Manage Featured Products</h2>

<!-- Currently Featured -->
<div class="card shadow mb-4">
    <div class="card-header bg-warning text-dark">
        <h5 class="mb-0"><i class="fas fa-star"></i> Currently Featured (<?php echo count($featuredProducts); ?>)</h5>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light"><tr><th>Product</th><th>Seller</th><th>Category</th><th>Price</th><th>Featured Until</th><th>Action</th></tr></thead>
            <tbody>
                <?php foreach($featuredProducts as $p): ?>
                <tr>
                    <td>
                        <div class="d-flex align-items-center">
                            <img src="<?php echo $p['image']?APP_URL.'/'.$p['image']:APP_URL.'/assets/images/placeholder.svg'; ?>"
                                 style="width:40px;height:40px;object-fit:cover;border-radius:5px" class="me-2">
                            <?php echo excerpt(Security::clean($p['product_name']),30); ?>
                        </div>
                    </td>
                    <td><?php echo Security::clean($p['seller_name']); ?></td>
                    <td><span class="badge bg-secondary"><?php echo Security::clean($p['category_name']); ?></span></td>
                    <td class="fw-bold text-success"><?php echo format_currency($p['price']); ?></td>
                    <td><small><?php echo format_date($p['featured_until']); ?></small></td>
                    <td>
                        <form method="POST" style="display:inline">
                            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                            <input type="hidden" name="product_id" value="<?php echo $p['product_id']; ?>">
                            <input type="hidden" name="action" value="unfeature">
                            <button class="btn btn-sm btn-danger" onclick="return confirm('Remove from featured?')">
                                <i class="fas fa-times"></i> Remove
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if(empty($featuredProducts)): ?>
                <tr><td colspan="6" class="text-center text-muted py-3">No featured products</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Regular Products (can be featured) -->
<div class="card shadow">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="fas fa-plus"></i> Add Featured Product</h5>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light"><tr><th>Product</th><th>Seller</th><th>Price</th><th>Views</th><th>Action</th></tr></thead>
            <tbody>
                <?php foreach($regularProducts as $p): ?>
                <tr>
                    <td><?php echo excerpt(Security::clean($p['product_name']),40); ?></td>
                    <td><?php echo Security::clean($p['seller_name']); ?></td>
                    <td><?php echo format_currency($p['price']); ?></td>
                    <td><?php echo $p['view_count']; ?></td>
                    <td>
                        <form method="POST" style="display:inline">
                            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                            <input type="hidden" name="product_id" value="<?php echo $p['product_id']; ?>">
                            <input type="hidden" name="action" value="feature">
                            <button class="btn btn-sm btn-warning text-dark">
                                <i class="fas fa-star"></i> Feature
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

</div>

<?php require_once __DIR__.'/../includes/footer.php'; ?>
