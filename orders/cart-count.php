<?php
/**
 * Cart Count API - Returns cart item count for navbar badge
 * Place at: orders/cart-count.php
 */
session_start();
require_once __DIR__.'/../config/database.php';
require_once __DIR__.'/../includes/Database.php';
require_once __DIR__.'/../includes/Security.php';

header('Content-Type: application/json');

if(!Security::isLoggedIn()){
    echo json_encode(['count'=>0]);
    exit;
}

$db=new Database();
$db->query("SELECT COALESCE(SUM(quantity),0) as count FROM cart WHERE user_id=:uid");
$db->bind(':uid',Security::getUserId());
$result=$db->fetch();

echo json_encode(['count'=>(int)($result['count']??0)]);
?>
