<?php
require_once __DIR__.'/../includes/header.php';
Security::requireLogin();

$productId=get_get('product_id');
$quantity=get_get('quantity',1);

if(!empty($productId)){
    $db=new Database();
    $userId=Security::getUserId();
    
    // Check if already in cart
    $db->query("SELECT cart_id,quantity FROM cart WHERE user_id=:uid AND product_id=:pid");
    $db->bind(':uid',$userId);
    $db->bind(':pid',$productId);
    $existing=$db->fetch();
    
    if($existing){
        // Update quantity
        $db->query("UPDATE cart SET quantity=quantity+:qty WHERE cart_id=:cid");
        $db->bind(':qty',$quantity);
        $db->bind(':cid',$existing['cart_id']);
        $db->execute();
    }else{
        // Add new
        $db->query("INSERT INTO cart(user_id,product_id,quantity,added_at)VALUES(:uid,:pid,:qty,NOW())");
        $db->bind(':uid',$userId);
        $db->bind(':pid',$productId);
        $db->bind(':qty',$quantity);
        $db->execute();
    }
    
    redirect_with_success(APP_URL.'/orders/cart.php','Added to cart!');
}else{
    redirect_with_error(APP_URL.'/products/index.php','Invalid product');
}
?>
