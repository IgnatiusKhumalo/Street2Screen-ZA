<?php
$pageTitle='Respond to Review';
require_once __DIR__.'/../includes/header.php';
Security::requireLogin();

$reviewId=get_get('id');
$db=new Database();
$userId=Security::getUserId();

// Get review - seller must own the reviewed product
$db->query("SELECT r.*,u.full_name as reviewer_name,p.product_name
FROM reviews r
JOIN orders o ON r.order_id=o.order_id
JOIN products p ON o.product_id=p.product_id
JOIN users u ON r.reviewer_id=u.user_id
WHERE r.review_id=:id AND r.seller_id=:seller");
$db->bind(':id',$reviewId);
$db->bind(':seller',$userId);
$review=$db->fetch();

if(!$review){
    redirect_with_error(APP_URL.'/user/seller-dashboard.php','Review not found');
}

if($review['seller_response']){
    redirect_with_error(APP_URL.'/reviews/view.php?seller_id='.$userId,'You have already responded to this review');
}

if(is_post_request()&&Security::validateCSRFToken(get_post('csrf_token'))){
    $response=Security::sanitizeString(get_post('seller_response'));

    if(!empty($response)){
        $db->query("UPDATE reviews SET seller_response=:response, response_date=NOW() WHERE review_id=:id");
        $db->bind(':response',$response);
        $db->bind(':id',$reviewId);
        $db->execute();

        redirect_with_success(APP_URL.'/reviews/view.php?seller_id='.$userId,'Response posted successfully!');
    }
}

$csrfToken=Security::generateCSRFToken();
?>

<div class="container my-5">
<div class="row justify-content-center">
<div class="col-md-6">

<h2 class="fw-bold mb-4"><i class="fas fa-reply text-primary"></i> Respond to Review</h2>

<!-- Original Review -->
<div class="card shadow-sm mb-4">
    <div class="card-header bg-light">
        <h5 class="mb-0">Original Review</h5>
    </div>
    <div class="card-body">
        <div class="d-flex justify-content-between mb-2">
            <strong><?php echo Security::clean($review['reviewer_name']); ?></strong>
            <div>
                <?php for($i=1;$i<=5;$i++): ?>
                <i class="fas fa-star <?php echo $i<=$review['rating']?'text-warning':'text-muted'; ?>"></i>
                <?php endfor; ?>
            </div>
        </div>
        <small class="text-muted d-block mb-2">
            <i class="fas fa-box"></i> <?php echo Security::clean($review['product_name']); ?> &nbsp;|&nbsp;
            <i class="fas fa-clock"></i> <?php echo time_ago($review['created_at']); ?>
        </small>
        <p class="mb-0"><?php echo nl2br(Security::clean($review['review_text'])); ?></p>
    </div>
</div>

<!-- Response Form -->
<div class="card shadow">
    <div class="card-body p-4">
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
            <div class="mb-4">
                <label class="fw-bold">Your Response *</label>
                <textarea class="form-control" name="seller_response" rows="5" required
                          placeholder="Write a professional, helpful response to this review..."></textarea>
                <small class="text-muted">Tip: Be polite and professional. Your response is visible to all buyers.</small>
            </div>
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-paper-plane"></i> Post Response
                </button>
                <a href="<?php echo APP_URL; ?>/reviews/view.php?seller_id=<?php echo $userId; ?>" class="btn btn-outline-secondary">
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
