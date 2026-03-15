<?php
require_once __DIR__.'/../includes/header.php';
Security::requireLogin();

$orderId = get_get('id');
$db = new Database();
$userId = Security::getUserId();

// Verify this is buyer's order
$db->query("SELECT * FROM orders WHERE order_id=:id AND buyer_id=:uid");
$db->bind(':id', $orderId);
$db->bind(':uid', $userId);
$order = $db->fetch();

if(!$order) {
    redirect_with_error(APP_URL.'/orders/my-orders.php', 'Order not found');
}

// Check if order can be cancelled
if($order['payment_status'] === 'paid') {
    redirect_with_error(APP_URL.'/orders/order-details.php?id='.$orderId, 'Paid orders cannot be cancelled. Please contact support for refunds.');
}

if($order['delivery_status'] === 'shipped' || $order['delivery_status'] === 'delivered') {
    redirect_with_error(APP_URL.'/orders/order-details.php?id='.$orderId, 'Order has already been shipped and cannot be cancelled.');
}

// FIXED: Cancel order - removed updated_at column
$db->query("UPDATE orders SET payment_status='cancelled', delivery_status='cancelled' WHERE order_id=:id");
$db->bind(':id', $orderId);

if($db->execute()) {
    // Restore product stock
    $db->query("UPDATE products SET stock_quantity=stock_quantity+:qty WHERE product_id=:pid");
    $db->bind(':qty', $order['quantity']);
    $db->bind(':pid', $order['product_id']);
    $db->execute();
    
    redirect_with_success(APP_URL.'/orders/my-orders.php', 'Order cancelled successfully');
} else {
    redirect_with_error(APP_URL.'/orders/order-details.php?id='.$orderId, 'Failed to cancel order');
}
?>
