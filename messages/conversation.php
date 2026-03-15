<?php
$pageTitle='Conversation';
require_once __DIR__.'/../includes/header.php';
Security::requireLogin();

$conversationId=get_get('id');
$db=new Database();
$userId=Security::getUserId();

// FIXED: Use unique parameter names for each :uid occurrence
$db->query("SELECT c.*,
    buyer.full_name as buyer_name,
    seller.full_name as seller_name,
    p.product_name,
    p.product_id,
    CASE WHEN c.buyer_id=:uid1 THEN seller.full_name ELSE buyer.full_name END as other_user_name,
    CASE WHEN c.buyer_id=:uid2 THEN c.seller_id ELSE c.buyer_id END as other_user_id
FROM conversations c
JOIN users buyer ON c.buyer_id=buyer.user_id
JOIN users seller ON c.seller_id=seller.user_id
LEFT JOIN products p ON c.product_id=p.product_id
WHERE c.conversation_id=:cid
AND (c.buyer_id=:uid3 OR c.seller_id=:uid4)");

$db->bind(':cid',$conversationId);
$db->bind(':uid1',$userId);
$db->bind(':uid2',$userId);
$db->bind(':uid3',$userId);
$db->bind(':uid4',$userId);
$conversation=$db->fetch();

if(!$conversation){
    redirect_with_error(APP_URL.'/messages/inbox.php','Conversation not found');
}

// Get messages
$db->query("SELECT m.*,u.full_name as sender_name
FROM messages m
JOIN users u ON m.sender_id=u.user_id
WHERE m.conversation_id=:cid
ORDER BY m.sent_at ASC");
$db->bind(':cid',$conversationId);
$messages=$db->fetchAll();

// Mark messages as read (FIXED: unique parameters)
$db->query("UPDATE messages SET read_status=1, read_at=NOW()
WHERE conversation_id=:cid AND sender_id!=:uid AND read_status=0");
$db->bind(':cid',$conversationId);
$db->bind(':uid',$userId);
$db->execute();

// Handle send message
if(is_post_request()&&Security::validateCSRFToken(get_post('csrf_token'))){
    $messageText=Security::sanitizeString(get_post('message_text'));

    if(!empty($messageText)){
        // Insert message
        $db->query("INSERT INTO messages(conversation_id,sender_id,message_text,read_status,sent_at)
        VALUES(:cid,:sender,:msg,0,NOW())");
        $db->bind(':cid',$conversationId);
        $db->bind(':sender',$userId);
        $db->bind(':msg',$messageText);
        $db->execute();

        // Update conversation last_message_at
        $db->query("UPDATE conversations SET last_message_at=NOW() WHERE conversation_id=:cid");
        $db->bind(':cid',$conversationId);
        $db->execute();

        redirect(APP_URL.'/messages/conversation.php?id='.$conversationId);
    }
}

$csrfToken=Security::generateCSRFToken();
?>

<div class="container my-5">
<div class="row justify-content-center">
<div class="col-md-8">

<div class="card shadow">
    <!-- Header -->
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <div>
            <a href="<?php echo APP_URL; ?>/messages/inbox.php" class="text-white me-3">
                <i class="fas fa-arrow-left"></i>
            </a>
            <i class="fas fa-user-circle"></i>
            <strong><?php echo Security::clean($conversation['other_user_name']); ?></strong>
        </div>
        <?php if($conversation['product_name']): ?>
        <small class="text-white-50">
            <i class="fas fa-box"></i> <?php echo excerpt(Security::clean($conversation['product_name']),30); ?>
        </small>
        <?php endif; ?>
    </div>

    <!-- Messages Area -->
    <div class="card-body p-3" style="height:500px;overflow-y:auto" id="messageArea">
        <?php if(empty($messages)): ?>
        <div class="text-center text-muted py-5">
            <i class="fas fa-comments fa-3x mb-3"></i>
            <p>No messages yet. Start the conversation!</p>
        </div>
        <?php else: ?>
        <?php foreach($messages as $m): ?>
        <div class="mb-3 <?php echo $m['sender_id']==$userId?'text-end':'text-start'; ?>">
            <div class="d-inline-block" style="max-width:75%">
                <div class="p-3 rounded-3 <?php echo $m['sender_id']==$userId?'bg-primary text-white':'bg-light text-dark'; ?>">
                    <?php if($m['attachment_path']): ?>
                    <img src="<?php echo APP_URL.'/'.$m['attachment_path']; ?>"
                         class="img-fluid rounded mb-2" style="max-height:200px">
                    <?php endif; ?>
                    <p class="mb-1"><?php echo nl2br(Security::clean($m['message_text'])); ?></p>
                    <small class="<?php echo $m['sender_id']==$userId?'text-white-50':'text-muted'; ?>">
                        <i class="fas fa-clock"></i> <?php echo time_ago($m['sent_at']); ?>
                        <?php if($m['sender_id']==$userId&&$m['read_status']): ?>
                        <i class="fas fa-check-double ms-1" title="Read"></i>
                        <?php endif; ?>
                    </small>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Send Form -->
    <div class="card-footer bg-white border-top p-3">
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
            <div class="input-group">
                <textarea class="form-control" name="message_text" rows="2"
                          placeholder="Type your message..." required
                          style="resize:none"></textarea>
                <button type="submit" class="btn btn-primary px-4">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
        </form>
    </div>
</div>

</div>
</div>
</div>

<script>
// Auto scroll to bottom of messages
const messageArea=document.getElementById('messageArea');
if(messageArea) messageArea.scrollTop=messageArea.scrollHeight;
</script>

<?php require_once __DIR__.'/../includes/footer.php'; ?>
