<?php
$pageTitle='Edit Product';
require_once __DIR__.'/../includes/header.php';
Security::requireLogin();

$productId=get_get('id');
$db=new Database();

// Get product
$db->query("SELECT * FROM products WHERE product_id=:id AND seller_id=:seller");
$db->bind(':id',$productId);
$db->bind(':seller',Security::getUserId());
$product=$db->fetch();

if(!$product){
    redirect_with_error(APP_URL.'/products/index.php','Product not found');
}

$errors=[];

if(is_post_request()){
    if(!Security::validateCSRFToken(get_post('csrf_token'))){
        $errors[]='Invalid token';
    }else{
        $data=[
            'product_name'=>Security::sanitizeString(get_post('product_name')),
            'category_id'=>get_post('category_id'),
            'description'=>Security::sanitizeString(get_post('description')),
            'price'=>get_post('price'),
            'stock_quantity'=>get_post('stock_quantity'),
            'condition'=>get_post('condition'),
            'location'=>Security::sanitizeString(get_post('location'))
        ];
        
        if(empty($errors)){
            $db->query("UPDATE products SET product_name=:name,category_id=:cat,description=:desc,price=:price,stock_quantity=:stock,location=:loc,`condition`=:cond,updated_at=NOW() WHERE product_id=:id");
            $db->bind(':name',$data['product_name']);
            $db->bind(':cat',$data['category_id']);
            $db->bind(':desc',$data['description']);
            $db->bind(':price',$data['price']);
            $db->bind(':stock',$data['stock_quantity']);
            $db->bind(':loc',$data['location']);
            $db->bind(':cond',$data['condition']);
            $db->bind(':id',$productId);
            $db->execute();
            
            redirect_with_success(APP_URL.'/products/view.php?id='.$productId,'Product updated!');
        }
    }
}

$db->query("SELECT * FROM categories WHERE active=1 ORDER BY display_order");
$categories=$db->fetchAll();
$csrfToken=Security::generateCSRFToken();
?>

<div class="container my-5">
<div class="row justify-content-center">
<div class="col-md-8">

<h2 class="fw-bold mb-4"><i class="fas fa-edit text-primary"></i> Edit Product</h2>

<div class="card shadow">
    <div class="card-body p-4">
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
            
            <div class="mb-3">
                <label class="fw-bold">Product Name *</label>
                <input type="text" class="form-control form-control-lg" name="product_name" value="<?php echo Security::clean($product['product_name']); ?>" required>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="fw-bold">Category *</label>
                    <select class="form-select form-select-lg" name="category_id" required>
                        <?php foreach($categories as $c): ?>
                        <option value="<?php echo $c['category_id']; ?>" <?php echo $product['category_id']==$c['category_id']?'selected':''; ?>>
                            <?php echo Security::clean($c['category_name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="fw-bold">Condition *</label>
                    <select class="form-select form-select-lg" name="condition" required>
                        <option value="new" <?php echo $product['condition']==='new'?'selected':''; ?>>New</option>
                        <option value="like_new" <?php echo $product['condition']==='like_new'?'selected':''; ?>>Like New</option>
                        <option value="good" <?php echo $product['condition']==='good'?'selected':''; ?>>Good</option>
                        <option value="fair" <?php echo $product['condition']==='fair'?'selected':''; ?>>Fair</option>
                    </select>
                </div>
            </div>
            
            <div class="mb-3">
                <label class="fw-bold">Description *</label>
                <textarea class="form-control" name="description" rows="4" required><?php echo Security::clean($product['description']); ?></textarea>
            </div>
            
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="fw-bold">Price (R) *</label>
                    <input type="number" class="form-control form-control-lg" name="price" step="0.01" value="<?php echo $product['price']; ?>" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="fw-bold">Stock *</label>
                    <input type="number" class="form-control form-control-lg" name="stock_quantity" value="<?php echo $product['stock_quantity']; ?>" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="fw-bold">Location *</label>
                    <input type="text" class="form-control form-control-lg" name="location" value="<?php echo Security::clean($product['location']); ?>" required>
                </div>
            </div>
            
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-save"></i> Save Changes
                </button>
                <a href="<?php echo APP_URL; ?>/products/view.php?id=<?php echo $productId; ?>" class="btn btn-secondary">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

</div>
</div>
</div>

<?php require_once __DIR__.'/../includes/footer.php'; ?>
