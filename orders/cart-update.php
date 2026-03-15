<?php
session_start();
require_once __DIR__.'/../config/database.php';
require_once __DIR__.'/../includes/Database.php';
require_once __DIR__.'/../includes/Security.php';

Security::requireLogin();

if($_SERVER['REQUEST_METHOD']==='POST'){
    $cartId=$_POST['cart_id']??0;
    $quantity=$_POST['quantity']??1;
    
    if($cartId&&$quantity>0){
        $db=new Database();
        $db->query("UPDATE cart SET quantity=:qty WHERE cart_id=:id AND user_id=:uid");
        $db->bind(':qty',$quantity);
        $db->bind(':id',$cartId);
        $db->bind(':uid',Security::getUserId());
        $db->execute();
        
        echo json_encode(['success'=>true]);
    }else{
        echo json_encode(['success'=>false]);
    }
}
?>
