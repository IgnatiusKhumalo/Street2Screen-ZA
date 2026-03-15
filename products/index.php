<?php
$pageTitle='Browse Products';
require_once __DIR__.'/../includes/header.php';

$db=new Database();

// Get filters from URL
$search=get_get('search','');
$category=get_get('category','');
$minPrice=get_get('min_price','');
$maxPrice=get_get('max_price','');
$condition=get_get('condition','');
$location=get_get('location','');
$sort=get_get('sort','latest');

// Build query
$query="SELECT p.*,u.full_name as seller_name,u.township as seller_township,c.category_name,(SELECT image_path FROM product_images WHERE product_id=p.product_id AND is_primary=1 LIMIT 1)as image FROM products p JOIN users u ON p.seller_id=u.user_id JOIN categories c ON p.category_id=c.category_id WHERE p.status='active' AND u.account_status='active'";

$conditions=[];

// Apply filters
if(!empty($search)){
    $query.=" AND (p.product_name LIKE :search OR p.description LIKE :search)";
    $conditions[':search']='%'.$search.'%';
}
if(!empty($category)){
    $query.=" AND p.category_id=:category";
    $conditions[':category']=$category;
}
if(!empty($minPrice)){
    $query.=" AND p.price>=:min_price";
    $conditions[':min_price']=$minPrice;
}
if(!empty($maxPrice)){
    $query.=" AND p.price<=:max_price";
    $conditions[':max_price']=$maxPrice;
}
if(!empty($condition)){
    $query.=" AND p.condition=:condition";
    $conditions[':condition']=$condition;
}
if(!empty($location)){
    $query.=" AND p.location LIKE :location";
    $conditions[':location']='%'.$location.'%';
}

// Apply sorting
switch($sort){
    case 'price_low':
        $query.=" ORDER BY p.price ASC";
        break;
    case 'price_high':
        $query.=" ORDER BY p.price DESC";
        break;
    case 'popular':
        $query.=" ORDER BY p.view_count DESC";
        break;
    default:
        $query.=" ORDER BY p.created_at DESC";
}

$db->query($query);
foreach($conditions as $k=>$v){
    $db->bind($k,$v);
}
$products=$db->fetchAll();

// Get categories for filter
$db->query("SELECT * FROM categories WHERE active=1 ORDER BY display_order");
$categories=$db->fetchAll();
?>

<div class="container my-5">
<div class="row">

<!-- Filters Sidebar -->
<div class="col-md-3">
    <div class="card shadow-sm sticky-top" style="top:20px">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-filter"></i> <?php echo t('filters'); ?></h5>
        </div>
        <div class="card-body">
            <form method="GET">
                <!-- Search -->
                <div class="mb-3">
                    <label class="fw-bold"><?php echo t('search'); ?></label>
                    <input type="text" class="form-control" name="search" value="<?php echo Security::clean($search); ?>" placeholder="<?php echo t('product_name'); ?>">
                </div>
                
                <!-- Category -->
                <div class="mb-3">
                    <label class="fw-bold"><?php echo t('category'); ?></label>
                    <select class="form-select" name="category">
                        <option value=""><?php echo t('all_categories'); ?></option>
                        <?php foreach($categories as $c): ?>
                        <option value="<?php echo $c['category_id']; ?>" <?php echo $category==$c['category_id']?'selected':''; ?>>
                            <?php echo Security::clean($c['category_name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- Price Range -->
                <div class="mb-3">
                    <label class="fw-bold"><?php echo t('price_range'); ?></label>
                    <div class="row">
                        <div class="col-6">
                            <input type="number" class="form-control" name="min_price" value="<?php echo Security::clean($minPrice); ?>" placeholder="<?php echo t('min'); ?>">
                        </div>
                        <div class="col-6">
                            <input type="number" class="form-control" name="max_price" value="<?php echo Security::clean($maxPrice); ?>" placeholder="<?php echo t('max'); ?>">
                        </div>
                    </div>
                </div>
                
                <!-- Condition -->
                <div class="mb-3">
                    <label class="fw-bold"><?php echo t('condition'); ?></label>
                    <select class="form-select" name="condition">
                        <option value=""><?php echo t('any'); ?></option>
                        <option value="new" <?php echo $condition==='new'?'selected':''; ?>><?php echo t('new'); ?></option>
                        <option value="like_new" <?php echo $condition==='like_new'?'selected':''; ?>><?php echo t('like_new'); ?></option>
                        <option value="good" <?php echo $condition==='good'?'selected':''; ?>><?php echo t('good'); ?></option>
                        <option value="fair" <?php echo $condition==='fair'?'selected':''; ?>><?php echo t('fair'); ?></option>
                    </select>
                </div>
                
                <!-- Location -->
                <div class="mb-3">
                    <label class="fw-bold"><?php echo t('location'); ?></label>
                    <input type="text" class="form-control" name="location" value="<?php echo Security::clean($location); ?>" placeholder="<?php echo t('township_city'); ?>">
                </div>
                
                <!-- Sort -->
                <div class="mb-3">
                    <label class="fw-bold"><?php echo t('sort_by'); ?></label>
                    <select class="form-select" name="sort">
                        <option value="latest" <?php echo $sort==='latest'?'selected':''; ?>><?php echo t('latest'); ?></option>
                        <option value="price_low" <?php echo $sort==='price_low'?'selected':''; ?>><?php echo t('price_low_high'); ?></option>
                        <option value="price_high" <?php echo $sort==='price_high'?'selected':''; ?>><?php echo t('price_high_low'); ?></option>
                        <option value="popular" <?php echo $sort==='popular'?'selected':''; ?>><?php echo t('most_popular'); ?></option>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-primary w-100 mb-2">
                    <i class="fas fa-search"></i> <?php echo t('apply_filters'); ?>
                </button>
                <a href="<?php echo APP_URL; ?>/products/index.php" class="btn btn-secondary w-100">
                    <i class="fas fa-undo"></i> <?php echo t('clear_all'); ?>
                </a>
            </form>
        </div>
    </div>
</div>

<!-- Products Grid -->
<div class="col-md-9">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="fw-bold mb-0">
            <i class="fas fa-shopping-bag text-primary"></i> 
            <?php echo count($products); ?> <?php echo t('products_found'); ?>
        </h3>
        <?php if(Security::isLoggedIn()&&in_array(Security::getUserType(),['seller','both'])): ?>
        <a href="<?php echo APP_URL; ?>/products/add.php" class="btn btn-success">
            <i class="fas fa-plus"></i> <?php echo t('list_product'); ?>
        </a>
        <?php endif; ?>
    </div>
    
    <?php if(empty($products)): ?>
    <div class="alert alert-info text-center">
        <i class="fas fa-info-circle fa-3x mb-3"></i>
        <h5><?php echo t('no_products_found'); ?></h5>
        <p><?php echo t('try_adjusting'); ?></p>
        <a href="<?php echo APP_URL; ?>/products/index.php" class="btn btn-primary"><?php echo t('view_all_products'); ?></a>
    </div>
    <?php else: ?>
    
    <div class="row">
        <?php foreach($products as $p): ?>
        <div class="col-md-4 col-sm-6 mb-4">
            <div class="card h-100 shadow-sm product-card">
                <!-- Featured Badge -->
                <?php if($p['featured']&&strtotime($p['featured_until'])>time()): ?>
                <div class="position-absolute top-0 end-0 m-2" style="z-index:1">
                    <span class="badge bg-warning text-dark">
                        <i class="fas fa-star"></i> <?php echo t('featured'); ?>
                    </span>
                </div>
                <?php endif; ?>
                
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
                    <h6 class="card-title"><?php echo excerpt(Security::clean($p['product_name']),40); ?></h6>
                    <p class="text-success fw-bold fs-5 mb-2"><?php echo format_currency($p['price']); ?></p>
                    <p class="small text-muted mb-1">
                        <i class="fas fa-map-marker-alt"></i> <?php echo Security::clean($p['location']); ?>
                    </p>
                    <p class="small text-muted mb-2">
                        <i class="fas fa-user"></i> <?php echo Security::clean($p['seller_name']); ?>
                    </p>
                    <div class="mb-2">
                        <span class="badge bg-secondary">
                            <?php echo ucfirst(str_replace('_',' ',$p['condition'])); ?>
                        </span>
                        <?php if($p['stock_quantity']>0): ?>
                        <span class="badge bg-success"><?php echo t('in_stock'); ?></span>
                        <?php else: ?>
                        <span class="badge bg-danger"><?php echo t('out_of_stock'); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="card-footer bg-white border-0">
                    <a href="<?php echo APP_URL; ?>/products/view.php?id=<?php echo $p['product_id']; ?>" 
                       class="btn btn-primary btn-sm w-100">
                        <i class="fas fa-eye"></i> <?php echo t('view_details'); ?>
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <?php endif; ?>
</div>

</div>
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
