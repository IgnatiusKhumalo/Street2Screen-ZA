<?php
$pageTitle='Seller Reviews';
require_once __DIR__.'/../includes/header.php';

$sellerId=get_get('seller_id');
$db=new Database();

// Get seller info
$db->query("SELECT full_name,email FROM users WHERE user_id=:id AND user_type IN('seller','both')");
$db->bind(':id',$sellerId);
$seller=$db->fetch();

if(!$seller){
    redirect_with_error(APP_URL.'/products/index.php','Seller not found');
}

// Get review statistics
$db->query("SELECT AVG(rating)as avg_rating,COUNT(*)as total_reviews FROM reviews WHERE seller_id=:id");
$db->bind(':id',$sellerId);
$stats=$db->fetch();

$avgRating=$stats['avg_rating']??0;
$totalReviews=$stats['total_reviews']??0;

// Get rating breakdown
$ratingBreakdown=[];
for($i=5;$i>=1;$i--){
    $db->query("SELECT COUNT(*) as count FROM reviews WHERE seller_id=:id AND rating=:rating");
    $db->bind(':id',$sellerId);
    $db->bind(':rating',$i);
    $ratingBreakdown[$i]=$db->fetch()['count']??0;
}

// Get all reviews
$db->query("SELECT r.*,u.full_name as reviewer_name,p.product_name FROM reviews r JOIN users u ON r.reviewer_id=u.user_id JOIN orders o ON r.order_id=o.order_id JOIN products p ON o.product_id=p.product_id WHERE r.seller_id=:id ORDER BY r.created_at DESC");
$db->bind(':id',$sellerId);
$reviews=$db->fetchAll();
?>

<div class="container my-5">
<h2 class="fw-bold mb-4"><i class="fas fa-star text-warning"></i> Reviews for <?php echo Security::clean($seller['full_name']); ?></h2>

<div class="row mb-4">
<!-- Rating Summary -->
<div class="col-md-4">
    <div class="card shadow text-center">
        <div class="card-body p-4">
            <h1 class="display-1 text-warning mb-2"><?php echo number_format($avgRating,1); ?></h1>
            <div class="mb-2">
                <?php for($i=1;$i<=5;$i++): ?>
                <i class="fas fa-star <?php echo $i<=round($avgRating)?'text-warning':'text-muted'; ?>"></i>
                <?php endfor; ?>
            </div>
            <p class="text-muted mb-0"><?php echo $totalReviews; ?> Review<?php echo $totalReviews!=1?'s':''; ?></p>
        </div>
    </div>
</div>

<!-- Rating Breakdown -->
<div class="col-md-8">
    <div class="card shadow">
        <div class="card-body">
            <h5 class="mb-3">Rating Breakdown</h5>
            <?php foreach($ratingBreakdown as $rating=>$count): ?>
            <div class="mb-2">
                <div class="d-flex align-items-center">
                    <div style="width:80px">
                        <?php for($i=1;$i<=5;$i++): ?>
                        <i class="fas fa-star <?php echo $i<=$rating?'text-warning':'text-muted'; ?>" style="font-size:0.8rem"></i>
                        <?php endfor; ?>
                    </div>
                    <div class="flex-grow-1 mx-3">
                        <div class="progress" style="height:20px">
                            <div class="progress-bar bg-warning" 
                                 style="width:<?php echo $totalReviews>0?($count/$totalReviews)*100:0; ?>%">
                                <?php echo $count; ?>
                            </div>
                        </div>
                    </div>
                    <div style="width:60px" class="text-end">
                        <small class="text-muted"><?php echo $totalReviews>0?round(($count/$totalReviews)*100):0; ?>%</small>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
</div>

<!-- Reviews List -->
<?php if(empty($reviews)): ?>
<div class="alert alert-info text-center">
    <i class="fas fa-info-circle fa-3x mb-3"></i>
    <h5>No Reviews Yet</h5>
    <p class="mb-0">This seller hasn't received any reviews yet.</p>
</div>
<?php else: ?>

<h4 class="fw-bold mb-3">Customer Reviews</h4>

<?php foreach($reviews as $r): ?>
<div class="card mb-3 shadow-sm">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start mb-2">
            <div>
                <div class="d-flex align-items-center mb-1">
                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2" 
                         style="width:40px;height:40px">
                        <?php echo strtoupper(substr($r['reviewer_name'],0,1)); ?>
                    </div>
                    <div>
                        <h6 class="mb-0"><?php echo Security::clean($r['reviewer_name']); ?></h6>
                        <small class="text-muted">
                            <i class="fas fa-calendar"></i> <?php echo time_ago($r['created_at']); ?>
                        </small>
                    </div>
                </div>
            </div>
            <div class="text-end">
                <?php for($i=1;$i<=5;$i++): ?>
                <i class="fas fa-star <?php echo $i<=$r['rating']?'text-warning':'text-muted'; ?>"></i>
                <?php endfor; ?>
            </div>
        </div>
        
        <p class="small text-muted mb-2">
            <i class="fas fa-box"></i> Product: <?php echo Security::clean($r['product_name']); ?>
        </p>
        
        <p class="mb-0"><?php echo nl2br(Security::clean($r['review_text'])); ?></p>
        
        <?php if($r['seller_response']): ?>
        <div class="alert alert-light mt-3 mb-0">
            <strong><i class="fas fa-reply"></i> Seller Response:</strong><br>
            <?php echo nl2br(Security::clean($r['seller_response'])); ?>
            <br><small class="text-muted"><?php echo time_ago($r['response_date']); ?></small>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php endforeach; ?>

<?php endif; ?>

<div class="mt-4">
    <a href="<?php echo APP_URL; ?>/products/index.php" class="btn btn-primary">
        <i class="fas fa-arrow-left"></i> Back to Products
    </a>
</div>

</div>

<?php require_once __DIR__.'/../includes/footer.php'; ?>
