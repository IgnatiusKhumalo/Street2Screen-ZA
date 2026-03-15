<?php
$pageTitle='Messages';
require_once __DIR__.'/../includes/header.php';
Security::requireLogin();

$db=new Database();
$userId=Security::getUserId();

// FIXED: Use separate parameter names for duplicate :uid
$db->query("SELECT 
    c.conversation_id,
    c.last_message_at,
    c.product_id,
    p.product_name,
    CASE WHEN c.buyer_id=:uid1 THEN seller.full_name ELSE buyer.full_name END as other_user_name,
    CASE WHEN c.buyer_id=:uid2 THEN c.seller_id ELSE c.buyer_id END as other_user_id,
    (SELECT message_text FROM messages WHERE conversation_id=c.conversation_id ORDER BY sent_at DESC LIMIT 1) as last_message,
    (SELECT COUNT(*) FROM messages WHERE conversation_id=c.conversation_id AND sender_id!=:uid3 AND read_status=0) as unread_count
FROM conversations c
JOIN users buyer ON c.buyer_id=buyer.user_id
JOIN users seller ON c.seller_id=seller.user_id
LEFT JOIN products p ON c.product_id=p.product_id
WHERE (c.buyer_id=:uid4 OR c.seller_id=:uid5)
AND c.status='active'
ORDER BY c.last_message_at DESC");

// Bind all occurrences
$db->bind(':uid1',$userId);
$db->bind(':uid2',$userId);
$db->bind(':uid3',$userId);
$db->bind(':uid4',$userId);
$db->bind(':uid5',$userId);

$conversations=$db->fetchAll();
?>

<div class="container my-5">
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold"><i class="fas fa-envelope text-primary"></i> Messages</h2>
    <a href="<?php echo APP_URL; ?>/messages/search.php" class="btn btn-outline-primary">
        <i class="fas fa-search"></i> Search Messages
    </a>
</div>

<?php if(empty($conversations)): ?>
<div class="alert alert-info text-center">
    <i class="fas fa-inbox fa-5x mb-4 text-muted"></i>
    <h4>No Messages Yet</h4>
    <p class="mb-4">Messages will appear here when buyers or sellers contact you.</p>
    <a href="<?php echo APP_URL; ?>/products/index.php" class="btn btn-primary">
        <i class="fas fa-shopping-bag"></i> Browse Products
    </a>
</div>
<?php else: ?>

<div class="card shadow">
    <div class="list-group list-group-flush">
        <?php foreach($conversations as $conv): ?>
        <a href="<?php echo APP_URL; ?>/messages/conversation.php?id=<?php echo $conv['conversation_id']; ?>"
           class="list-group-item list-group-item-action <?php echo $conv['unread_count']>0?'bg-light fw-bold':''; ?>">
            <div class="d-flex align-items-center">
                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-3 flex-shrink-0"
                     style="width:50px;height:50px;font-size:20px">
                    <?php echo strtoupper(substr($conv['other_user_name'],0,1)); ?>
                </div>

                <div class="flex-grow-1 overflow-hidden">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <h6 class="mb-0">
                            <?php echo Security::clean($conv['other_user_name']); ?>
                            <?php if($conv['unread_count']>0): ?>
                            <span class="badge bg-danger ms-2"><?php echo $conv['unread_count']; ?> new</span>
                            <?php endif; ?>
                        </h6>
                        <small class="text-muted flex-shrink-0 ms-2">
                            <?php echo $conv['last_message_at']?time_ago($conv['last_message_at']):''; ?>
                        </small>
                    </div>
                    <?php if($conv['product_name']): ?>
                    <small class="text-primary mb-1 d-block">
                        <i class="fas fa-box"></i> <?php echo excerpt(Security::clean($conv['product_name']),40); ?>
                    </small>
                    <?php endif; ?>
                    <p class="mb-0 text-muted text-truncate small">
                        <?php echo $conv['last_message']?excerpt(Security::clean($conv['last_message']),60):'No messages yet'; ?>
                    </p>
                </div>

                <i class="fas fa-chevron-right text-muted ms-2"></i>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
</div>

<?php endif; ?>
</div>

<style>
.list-group-item-action:hover{background-color:#f0f4ff!important;}
.list-group-item{transition:all 0.2s;border-left:3px solid transparent;}
.list-group-item:hover{border-left-color:#0B1F3A;}
</style>

<?php require_once __DIR__.'/../includes/footer.php'; ?>
