<?php
require_once __DIR__.'/../includes/header.php';
Security::requireLogin();

$productId = get_get('id');
$db = new Database();
$userId = Security::getUserId();

// Verify ownership
$db->query("SELECT * FROM products WHERE product_id=:id AND seller_id=:seller");
$db->bind(':id', $productId);
$db->bind(':seller', $userId);
$product = $db->fetch();

if(!$product) {
    redirect_with_error(APP_URL.'/products/index.php', 'Product not found or you do not have permission');
}

// Check if product has orders
$db->query("SELECT COUNT(*) as order_count FROM orders WHERE product_id=:pid AND payment_status='paid'");
$db->bind(':pid', $productId);
$orderCheck = $db->fetch();

if($orderCheck['order_count'] > 0) {
    redirect_with_error(APP_URL.'/products/view.php?id='.$productId, 'Cannot delete product with existing paid orders. You can mark it as out of stock instead.');
}

// Soft delete (change status to deleted)
$db->query("UPDATE products SET status='deleted', updated_at=NOW() WHERE product_id=:id");
$db->bind(':id', $productId);

if($db->execute()) {
    redirect_with_success(APP_URL.'/products/index.php', 'Product deleted successfully');
} else {
    redirect_with_error(APP_URL.'/products/view.php?id='.$productId, 'Failed to delete product');
}
?>
