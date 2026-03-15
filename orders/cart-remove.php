<?php
require_once __DIR__.'/../includes/header.php';
Security::requireLogin();

$cartId=get_get('id');

if($cartId){
    $db=new Database();
    $db->query("DELETE FROM cart WHERE cart_id=:id AND user_id=:uid");
    $db->bind(':id',$cartId);
    $db->bind(':uid',Security::getUserId());
    $db->execute();
    
    redirect_with_success(APP_URL.'/orders/cart.php','Item removed from cart');
}else{
    redirect(APP_URL.'/orders/cart.php');
}
?>
