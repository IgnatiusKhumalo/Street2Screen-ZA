<?php
require_once __DIR__.'/../includes/header.php';
Security::requireLogin();

$sellerId=get_get('seller_id');
$productId=get_get('product_id','');
$db=new Database();
$userId=Security::getUserId();

if(empty($sellerId)){
    redirect_with_error(APP_URL.'/products/index.php','Invalid request');
}

// Prevent messaging yourself
if($sellerId==$userId){
    redirect_with_error(APP_URL.'/products/index.php','You cannot message yourself');
}

// FIXED: Check existing conversation (unique parameter names)
$db->query("SELECT conversation_id FROM conversations
WHERE buyer_id=:uid AND seller_id=:seller
AND (product_id=:pid OR (:pid2=0 AND product_id IS NULL))
LIMIT 1");
$db->bind(':uid',$userId);
$db->bind(':seller',$sellerId);
$db->bind(':pid',$productId?$productId:0);
$db->bind(':pid2',$productId?$productId:0);
$existing=$db->fetch();

if($existing){
    // Redirect to existing conversation
    redirect(APP_URL.'/messages/conversation.php?id='.$existing['conversation_id']);
}

// Create new conversation
$db->query("INSERT INTO conversations(buyer_id,seller_id,product_id,status,created_at,last_message_at)
VALUES(:uid,:seller,:pid,'active',NOW(),NOW())");
$db->bind(':uid',$userId);
$db->bind(':seller',$sellerId);
$db->bind(':pid',$productId?$productId:null);
$db->execute();

$conversationId=$db->lastInsertId();

// Get product name for initial message
$productName='';
if(!empty($productId)){
    $db->query("SELECT product_name FROM products WHERE product_id=:pid");
    $db->bind(':pid',$productId);
    $product=$db->fetch();
    $productName=$product?$product['product_name']:'';
}

// Build initial message text
$initialMessage=$productName
    ?"Hi! I'm interested in your product: \"{$productName}\". Is it still available?"
    :"Hi! I'd like to find out more about your products.";

// Insert first message
$db->query("INSERT INTO messages(conversation_id,sender_id,message_text,read_status,sent_at)
VALUES(:cid,:sender,:msg,0,NOW())");
$db->bind(':cid',$conversationId);
$db->bind(':sender',$userId);
$db->bind(':msg',$initialMessage);
$db->execute();

// Update conversation last_message_at
$db->query("UPDATE conversations SET last_message_at=NOW() WHERE conversation_id=:cid");
$db->bind(':cid',$conversationId);
$db->execute();

redirect(APP_URL.'/messages/conversation.php?id='.$conversationId);
?>
