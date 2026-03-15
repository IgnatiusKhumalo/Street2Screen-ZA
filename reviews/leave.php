<?php
$pageTitle='Leave Review';
require_once __DIR__.'/../includes/header.php';
Security::requireLogin();

$orderId=get_get('order_id');
$db=new Database();
$userId=Security::getUserId();

// Get order
$db->query("SELECT o.*,p.product_name,u.full_name as seller_name FROM orders o JOIN products p ON o.product_id=p.product_id JOIN users u ON o.seller_id=u.user_id WHERE o.order_id=:id AND o.buyer_id=:uid AND o.delivery_status='delivered'");
$db->bind(':id',$orderId);
$db->bind(':uid',$userId);
$order=$db->fetch();

if(!$order){
    redirect_with_error(APP_URL.'/orders/my-orders.php','Order not found or not eligible for review');
}

// Check if review already exists
$db->query("SELECT review_id FROM reviews WHERE order_id=:id");
$db->bind(':id',$orderId);
if($db->fetch()){
    redirect_with_error(APP_URL.'/orders/my-orders.php','Review already submitted for this order');
}

$errors=[];

if(is_post_request()){
    if(!Security::validateCSRFToken(get_post('csrf_token'))){
        $errors[]='Invalid token';
    }else{
        $rating=get_post('rating');
        $reviewText=Security::sanitizeString(get_post('review_text'));
        
        if(empty($rating)||$rating<1||$rating>5){
            $errors[]='Please select a rating (1-5 stars)';
        }
        if(empty($reviewText)){
            $errors[]='Review text is required';
        }
        
        if(empty($errors)){
            $db->query("INSERT INTO reviews(order_id,reviewer_id,seller_id,rating,review_text,created_at)VALUES(:oid,:reviewer,:seller,:rating,:text,NOW())");
            $db->bind(':oid',$orderId);
            $db->bind(':reviewer',$userId);
            $db->bind(':seller',$order['seller_id']);
            $db->bind(':rating',$rating);
            $db->bind(':text',$reviewText);
            $db->execute();
            
            redirect_with_success(APP_URL.'/orders/my-orders.php','Review submitted successfully!');
        }
    }
}

$csrfToken=Security::generateCSRFToken();
?>

<div class="container my-5">
<div class="row justify-content-center">
<div class="col-md-6">

<h2 class="fw-bold mb-4"><i class="fas fa-star text-warning"></i> Leave Review</h2>

<div class="alert alert-info">
    <strong><i class="fas fa-box"></i> Order #<?php echo $order['order_id']; ?></strong><br>
    <strong>Product:</strong> <?php echo Security::clean($order['product_name']); ?><br>
    <strong>Seller:</strong> <?php echo Security::clean($order['seller_name']); ?>
</div>

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
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
            
            <div class="mb-4">
                <label class="fw-bold mb-2">Rating *</label>
                <div class="text-center mb-3">
                    <div id="starRating" style="font-size:3rem;cursor:pointer">
                        <i class="far fa-star" data-rating="1" style="color:#FFD700"></i>
                        <i class="far fa-star" data-rating="2" style="color:#FFD700"></i>
                        <i class="far fa-star" data-rating="3" style="color:#FFD700"></i>
                        <i class="far fa-star" data-rating="4" style="color:#FFD700"></i>
                        <i class="far fa-star" data-rating="5" style="color:#FFD700"></i>
                    </div>
                    <input type="hidden" name="rating" id="ratingValue" required>
                    <p class="text-muted mb-0" id="ratingText">Click to rate</p>
                </div>
            </div>
            
            <div class="mb-4">
                <label class="fw-bold">Your Review *</label>
                <textarea class="form-control" name="review_text" rows="5" required placeholder="Share your experience with this seller and product..."></textarea>
                <small class="text-muted">Help other buyers by describing the product quality, seller communication, and delivery experience.</small>
            </div>
            
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-warning btn-lg text-dark">
                    <i class="fas fa-star"></i> Submit Review
                </button>
                <a href="<?php echo APP_URL; ?>/orders/my-orders.php" class="btn btn-secondary">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

</div>
</div>
</div>

<script>
// Star rating functionality
const stars=document.querySelectorAll('#starRating i');
const ratingValue=document.getElementById('ratingValue');
const ratingText=document.getElementById('ratingText');

const ratingLabels=['','Poor','Fair','Good','Very Good','Excellent'];

stars.forEach((star,index)=>{
    star.addEventListener('click',function(){
        const rating=this.dataset.rating;
        ratingValue.value=rating;
        ratingText.textContent=ratingLabels[rating];
        updateStars(rating);
    });
    
    star.addEventListener('mouseenter',function(){
        const rating=this.dataset.rating;
        updateStars(rating);
    });
});

document.getElementById('starRating').addEventListener('mouseleave',function(){
    const rating=ratingValue.value||0;
    updateStars(rating);
});

function updateStars(rating){
    stars.forEach((s,i)=>{
        if(i<rating){
            s.classList.remove('far');
            s.classList.add('fas');
        }else{
            s.classList.remove('fas');
            s.classList.add('far');
        }
    });
}
</script>

<?php require_once __DIR__.'/../includes/footer.php'; ?>
