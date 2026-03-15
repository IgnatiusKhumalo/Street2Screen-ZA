<?php
$pageTitle='List Product';
require_once __DIR__.'/../includes/header.php';
Security::requireLogin();

if(!in_array(Security::getUserType(),['seller','both'])){
    redirect_with_error(APP_URL.'/index.php','Only sellers can list products');
}

$db=new Database();
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
            'stock_quantity'=>get_post('stock_quantity',1),
            'condition'=>get_post('condition'),
            'location'=>Security::sanitizeString(get_post('location'))
        ];
        
        if(empty($data['product_name'])) $errors[]='Product name required';
        if(empty($data['category_id'])) $errors[]='Category required';
        if(empty($data['description'])) $errors[]='Description required';
        if($data['price']<=0) $errors[]='Valid price required';
        if(empty($data['location'])) $errors[]='Location required';
        
        if(empty($errors)){
            $db->query("INSERT INTO products(seller_id,category_id,product_name,description,price,stock_quantity,location,`condition`,status,created_at)VALUES(:seller,:cat,:name,:desc,:price,:stock,:loc,:cond,'active',NOW())");
            $db->bind(':seller',Security::getUserId());
            $db->bind(':cat',$data['category_id']);
            $db->bind(':name',$data['product_name']);
            $db->bind(':desc',$data['description']);
            $db->bind(':price',$data['price']);
            $db->bind(':stock',$data['stock_quantity']);
            $db->bind(':loc',$data['location']);
            $db->bind(':cond',$data['condition']);
            
            if($db->execute()){
                $productId=$db->lastInsertId();
                
                // Handle image uploads
                $uploadDir=__DIR__.'/../uploads/products/';
                if(!is_dir($uploadDir)) mkdir($uploadDir,0755,true);
                
                for($i=1;$i<=5;$i++){
                    if(isset($_FILES['image'.$i])&&$_FILES['image'.$i]['error']===UPLOAD_ERR_OK){
                        $result=upload_file($_FILES['image'.$i],$uploadDir,ALLOWED_IMAGE_TYPES,MAX_IMAGE_SIZE);
                        if($result['success']){
                            $db->query("INSERT INTO product_images(product_id,image_path,is_primary,display_order,uploaded_at)VALUES(:pid,:path,:primary,:order,NOW())");
                            $db->bind(':pid',$productId);
                            $db->bind(':path',str_replace(__DIR__.'/../','',$result['path']));
                            $db->bind(':primary',$i===1?1:0);
                            $db->bind(':order',$i);
                            $db->execute();
                        }
                    }
                }
                
                redirect_with_success(APP_URL.'/products/view.php?id='.$productId,'Product listed successfully!');
            }
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

<h2 class="fw-bold mb-4"><i class="fas fa-plus-circle text-success"></i> List New Product</h2>

<?php if(!empty($errors)): ?>
<div class="alert alert-danger">
    <ul class="mb-0">
    <?php foreach($errors as $e): ?>
        <li><?php echo Security::clean($e); ?></li>
    <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<div class="card shadow">
    <div class="card-body p-4">
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
            
            <div class="mb-3">
                <label class="fw-bold">Product Name *</label>
                <input type="text" class="form-control form-control-lg" name="product_name" required>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="fw-bold">Category *</label>
                    <select class="form-select form-select-lg" name="category_id" required>
                        <option value="">Select category</option>
                        <?php foreach($categories as $c): ?>
                        <option value="<?php echo $c['category_id']; ?>">
                            <?php echo Security::clean($c['category_name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="fw-bold">Condition *</label>
                    <select class="form-select form-select-lg" name="condition" required>
                        <option value="new">New</option>
                        <option value="like_new">Like New</option>
                        <option value="good">Good</option>
                        <option value="fair">Fair</option>
                    </select>
                </div>
            </div>
            
            <div class="mb-3">
                <label class="fw-bold">Description *</label>
                <textarea class="form-control" name="description" rows="4" required placeholder="Describe your product in detail..."></textarea>
            </div>
            
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="fw-bold">Price (R) *</label>
                    <input type="number" class="form-control form-control-lg" name="price" step="0.01" min="0" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="fw-bold">Stock Quantity *</label>
                    <input type="number" class="form-control form-control-lg" name="stock_quantity" value="1" min="1" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="fw-bold">Location *</label>
                    <input type="text" class="form-control form-control-lg" name="location" placeholder="Township/City" required>
                </div>
            </div>
            
            <div class="mb-4">
                <label class="fw-bold">Product Images (Max 5)</label>
                <p class="small text-muted">First image will be the main product image. Max 5MB each.</p>
                <?php for($i=1;$i<=5;$i++): ?>
                <div class="mb-2">
                    <input type="file" class="form-control" name="image<?php echo $i; ?>" accept="image/*" <?php echo $i===1?'required':''; ?>>
                </div>
                <?php endfor; ?>
            </div>
            
            <div class="d-grid">
                <button type="submit" class="btn btn-success btn-lg">
                    <i class="fas fa-check"></i> List Product
                </button>
            </div>
        </form>
    </div>
</div>

</div>
</div>
</div>

<?php require_once __DIR__.'/../includes/footer.php'; ?>
