<?php
$pageTitle='Search Messages';
require_once __DIR__.'/../includes/header.php';
Security::requireLogin();

$db=new Database();
$userId=Security::getUserId();
$searchQuery=get_get('q','');
$results=[];

if(!empty($searchQuery)){
    // FIX: Query uses correct columns:
    //   - messages.sent_at (not created_at)
    //   - messages.read_status (not is_read)
    //   - JOIN conversations to get other user info
    $db->query("SELECT
        m.message_id,
        m.message_text,
        m.sent_at,
        m.read_status,
        m.conversation_id,
        c.buyer_id,
        c.seller_id,
        p.product_name,
        CASE WHEN m.sender_id=:uid THEN 'sent' ELSE 'received' END as message_type,
        CASE WHEN c.buyer_id=:uid THEN seller.full_name ELSE buyer.full_name END as other_user_name
    FROM messages m
    JOIN conversations c ON m.conversation_id=c.conversation_id
    JOIN users buyer ON c.buyer_id=buyer.user_id
    JOIN users seller ON c.seller_id=seller.user_id
    LEFT JOIN products p ON c.product_id=p.product_id
    WHERE (c.buyer_id=:uid OR c.seller_id=:uid)
    AND m.message_text LIKE :search
    ORDER BY m.sent_at DESC
    LIMIT 50");

    $db->bind(':uid',$userId);
    $db->bind(':search','%'.$searchQuery.'%');
    $results=$db->fetchAll();
}
?>

<div class="container my-5">
<div class="row justify-content-center">
<div class="col-md-10">

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold"><i class="fas fa-search text-primary"></i> Search Messages</h2>
    <a href="<?php echo APP_URL; ?>/messages/inbox.php" class="btn btn-outline-primary">
        <i class="fas fa-arrow-left"></i> Back to Inbox
    </a>
</div>

<!-- Search Form -->
<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form method="GET">
            <div class="input-group input-group-lg">
                <span class="input-group-text bg-primary text-white">
                    <i class="fas fa-search"></i>
                </span>
                <input type="text"
                       class="form-control"
                       name="q"
                       value="<?php echo Security::clean($searchQuery); ?>"
                       placeholder="Search all your messages..."
                       autofocus>
                <button type="submit" class="btn btn-primary px-4">
                    Search
                </button>
            </div>
            <small class="text-muted mt-1 d-block">Search through all your sent and received messages</small>
        </form>
    </div>
</div>

<!-- Results -->
<?php if(!empty($searchQuery)): ?>
    <?php if(empty($results)): ?>
    <div class="alert alert-info text-center">
        <i class="fas fa-info-circle fa-3x mb-3"></i>
        <h5>No Messages Found</h5>
        <p class="mb-0">No messages containing "<strong><?php echo Security::clean($searchQuery); ?></strong>"</p>
    </div>
    <?php else: ?>

    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i>
        Found <strong><?php echo count($results); ?></strong> result(s) for
        "<strong><?php echo Security::clean($searchQuery); ?></strong>"
    </div>

    <div class="card shadow">
        <div class="list-group list-group-flush">
            <?php foreach($results as $msg): ?>
            <div class="list-group-item">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="flex-grow-1">
                        <!-- User & Type Badge -->
                        <div class="d-flex align-items-center mb-2">
                            <div class="rounded-circle bg-<?php echo $msg['message_type']==='sent'?'primary':'success'; ?> text-white d-flex align-items-center justify-content-center me-3"
                                 style="width:40px;height:40px;font-size:14px;flex-shrink:0">
                                <i class="fas fa-<?php echo $msg['message_type']==='sent'?'paper-plane':'inbox'; ?>"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">
                                    <span class="badge bg-<?php echo $msg['message_type']==='sent'?'primary':'success'; ?> me-2">
                                        <?php echo ucfirst($msg['message_type']); ?>
                                    </span>
                                    <?php echo Security::clean($msg['other_user_name']); ?>
                                </h6>
                                <small class="text-muted">
                                    <i class="fas fa-clock"></i>
                                    <?php echo format_datetime($msg['sent_at']); ?>
                                    <?php if($msg['product_name']): ?>
                                    &nbsp;|&nbsp;<i class="fas fa-box"></i>
                                    <?php echo Security::clean($msg['product_name']); ?>
                                    <?php endif; ?>
                                </small>
                            </div>
                        </div>

                        <!-- Message with highlighted search term -->
                        <div class="p-3 bg-light rounded">
                            <?php
                            $messageText=Security::clean($msg['message_text']);
                            $highlighted=preg_replace(
                                '/('.preg_quote(Security::clean($searchQuery),'/').')/iu',
                                '<mark class="bg-warning px-1 rounded">$1</mark>',
                                $messageText
                            );
                            echo nl2br($highlighted);
                            ?>
                        </div>
                    </div>

                    <!-- View Button -->
                    <div class="ms-3 flex-shrink-0">
                        <a href="<?php echo APP_URL; ?>/messages/conversation.php?id=<?php echo $msg['conversation_id']; ?>"
                           class="btn btn-sm btn-primary">
                            <i class="fas fa-comments"></i> Open
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <?php endif; ?>

<?php else: ?>
<div class="text-center text-muted py-5">
    <i class="fas fa-search fa-5x mb-4" style="opacity:0.2"></i>
    <h5>Enter a keyword to search messages</h5>
    <p>Search by product name, message content, or conversation topic</p>
</div>
<?php endif; ?>

</div>
</div>
</div>

<?php require_once __DIR__.'/../includes/footer.php'; ?>
