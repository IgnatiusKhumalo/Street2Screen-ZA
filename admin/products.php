<?php
$pageTitle='Manage Products';
require_once __DIR__.'/../includes/header.php';
Security::requireAdmin();

$db=new Database();

// Handle product deletion
if(get_get('delete')){
    $productId=get_get('delete');
    $db->query("UPDATE products SET status='deleted' WHERE product_id=:id");
    $db->bind(':id',$productId);
    if($db->execute()){
        redirect_with_success(APP_URL.'/admin/products.php','Product deleted successfully');
    }
}

// Get all products
$db->query("SELECT p.*,u.full_name as seller_name,c.category_name,(SELECT image_path FROM product_images WHERE product_id=p.product_id AND is_primary=1 LIMIT 1)as image FROM products p JOIN users u ON p.seller_id=u.user_id JOIN categories c ON p.category_id=c.category_id WHERE p.status!='deleted' ORDER BY p.created_at DESC");
$products=$db->fetchAll();
?>

<div class="container my-5">
<h2 class="fw-bold mb-4"><i class="fas fa-box text-success"></i> All Products (<?php echo count($products); ?>)</h2>

<div class="card shadow">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Image</th>
                        <th>Product</th>
                        <th>Seller</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Status</th>
                        <th>Views</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($products as $p): ?>
                    <tr>
                        <td><?php echo $p['product_id']; ?></td>
                        <td>
                            <img src="<?php echo $p['image']?APP_URL.'/'.$p['image']:APP_URL.'/assets/images/placeholder.svg'; ?>" 
                                 alt="Product" style="width:50px;height:50px;object-fit:cover;border-radius:5px">
                        </td>
                        <td>
                            <strong><?php echo excerpt(Security::clean($p['product_name']),30); ?></strong><br>
                            <small class="text-muted"><?php echo Security::clean($p['location']); ?></small>
                        </td>
                        <td><?php echo Security::clean($p['seller_name']); ?></td>
                        <td><span class="badge bg-info"><?php echo Security::clean($p['category_name']); ?></span></td>
                        <td class="fw-bold text-success"><?php echo format_currency($p['price']); ?></td>
                        <td>
                            <?php if($p['stock_quantity']>0): ?>
                                <span class="badge bg-success"><?php echo $p['stock_quantity']; ?></span>
                            <?php else: ?>
                                <span class="badge bg-danger">Out</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if($p['status']==='active'): ?>
                                <span class="badge bg-success">Active</span>
                            <?php else: ?>
                                <span class="badge bg-secondary"><?php echo ucfirst($p['status']); ?></span>
                            <?php endif; ?>
                            <?php if($p['featured']&&strtotime($p['featured_until'])>time()): ?>
                                <br><span class="badge bg-warning text-dark">Featured</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $p['view_count']; ?></td>
                        <td><?php echo format_date($p['created_at']); ?></td>
                        <td>
                            <a href="<?php echo APP_URL; ?>/products/view.php?id=<?php echo $p['product_id']; ?>" 
                               class="btn btn-sm btn-info" target="_blank" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="?delete=<?php echo $p['product_id']; ?>" 
                               class="btn btn-sm btn-danger" 
                               onclick="return confirm('Delete this product?')" 
                               title="Delete">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Product Statistics -->
<div class="row mt-4">
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h3 class="text-primary"><?php echo count(array_filter($products,fn($p)=>$p['status']==='active')); ?></h3>
                <p class="mb-0">Active Products</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h3 class="text-warning"><?php echo count(array_filter($products,fn($p)=>$p['featured']&&strtotime($p['featured_until'])>time())); ?></h3>
                <p class="mb-0">Featured</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h3 class="text-danger"><?php echo count(array_filter($products,fn($p)=>$p['stock_quantity']==0)); ?></h3>
                <p class="mb-0">Out of Stock</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h3 class="text-success"><?php echo format_currency(array_sum(array_column($products,'price'))); ?></h3>
                <p class="mb-0">Total Value</p>
            </div>
        </div>
    </div>
</div>

</div>

<?php require_once __DIR__.'/../includes/footer.php'; ?>
