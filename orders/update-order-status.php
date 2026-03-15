<?php
require_once __DIR__.'/../includes/header.php';
Security::requireLogin();

$orderId = get_get('id');
$action = get_get('action');
$db = new Database();
$userId = Security::getUserId();

// Verify order exists and user has permission
$db->query("SELECT * FROM orders WHERE order_id=:id");
$db->bind(':id', $orderId);
$order = $db->fetch();

if(!$order) {
    redirect_with_error(APP_URL.'/orders/my-orders.php', 'Order not found');
}

$isBuyer = $order['buyer_id'] == $userId;
$isSeller = $order['seller_id'] == $userId;
$isAdmin = Security::getUserType() === 'admin';

if(!$isBuyer && !$isSeller && !$isAdmin) {
    redirect_with_error(APP_URL.'/index.php', 'Access denied');
}

// Function to generate automatic tracking number
function generateTrackingNumber() {
    return 'S2S-' . strtoupper(substr(md5(uniqid(rand(), true)), 0, 12));
}

// Function to calculate 10% commission
function calculateCommission($amount) {
    return round($amount * 0.10, 2);
}

// FIXED: Function to send commission notification - works with conversation-based messages
function sendCommissionNotification($db, $orderId, $buyerId, $sellerId, $totalAmount, $commission) {
    $sellerProfit = $totalAmount - $commission;
    
    // Check if conversation exists between buyer and seller
    $db->query("SELECT conversation_id FROM conversations 
                WHERE buyer_id=:buyer AND seller_id=:seller 
                ORDER BY created_at DESC LIMIT 1");
    $db->bind(':buyer', $buyerId);
    $db->bind(':seller', $sellerId);
    $conv = $db->fetch();
    
    $conversationId = 0; // System message (0 = no conversation)
    
    if($conv) {
        $conversationId = $conv['conversation_id'];
    } else {
        // Create a conversation for this notification
        $db->query("INSERT INTO conversations(buyer_id,seller_id,product_id,status,created_at,last_message_at) 
                    VALUES(:buyer,:seller,NULL,'active',NOW(),NOW())");
        $db->bind(':buyer', $buyerId);
        $db->bind(':seller', $sellerId);
        $db->execute();
        $conversationId = $db->lastInsertId();
    }
    
    // Build notification message
    $message = "🎉 PAYMENT CONFIRMED - Order #{$orderId}\n\n";
    $message .= "💰 Order Total: R" . number_format($totalAmount, 2) . "\n";
    $message .= "📊 Street2Screen Fee (10%): -R" . number_format($commission, 2) . "\n";
    $message .= "✅ YOUR PROFIT: R" . number_format($sellerProfit, 2) . "\n\n";
    $message .= "The buyer has confirmed payment. You can now ship the order!\n\n";
    $message .= "Thank you for selling with Street2Screen! 🚀";
    
    // Insert message (sender_id = 1 for system, or use 0)
    $db->query("INSERT INTO messages(conversation_id,sender_id,message_text,read_status,sent_at) 
                VALUES(:cid,1,:msg,0,NOW())");
    $db->bind(':cid', $conversationId);
    $db->bind(':msg', $message);
    $db->execute();
    
    // Update conversation last message time
    $db->query("UPDATE conversations SET last_message_at=NOW() WHERE conversation_id=:cid");
    $db->bind(':cid', $conversationId);
    $db->execute();
}

// Process actions
switch($action) {
    
    // SELLER: Mark payment as received (for COD/EFT)
    case 'mark_paid':
        if(!$isSeller && !$isAdmin) {
            redirect_with_error(APP_URL.'/orders/order-details.php?id='.$orderId, 'Only sellers can mark payment as received');
        }
        
        if($order['payment_status'] === 'paid') {
            redirect_with_error(APP_URL.'/orders/order-details.php?id='.$orderId, 'Payment already marked as received');
        }
        
        // Calculate 10% commission
        $commission = calculateCommission($order['total_amount']);
        $sellerProfit = $order['total_amount'] - $commission;
        
        // Update order with payment and commission
        $db->query("UPDATE orders SET 
                    payment_status='paid', 
                    payment_date=NOW(),
                    commission_amount=:commission,
                    seller_profit=:profit,
                    delivery_status='processing'
                    WHERE order_id=:id");
        $db->bind(':commission', $commission);
        $db->bind(':profit', $sellerProfit);
        $db->bind(':id', $orderId);
        
        if($db->execute()) {
            // Send commission notification to seller
            try {
                sendCommissionNotification($db, $orderId, $order['buyer_id'], $order['seller_id'], $order['total_amount'], $commission);
            } catch (Exception $e) {
                // Log error but don't fail the order update
                error_log("Notification failed: " . $e->getMessage());
            }
            
            redirect_with_success(APP_URL.'/orders/order-details.php?id='.$orderId, 
                'Payment confirmed! Commission: R'.number_format($commission, 2).', Your profit: R'.number_format($sellerProfit, 2));
        } else {
            redirect_with_error(APP_URL.'/orders/order-details.php?id='.$orderId, 'Failed to update payment status');
        }
        break;
    
    // SELLER: Ship order (AUTO-GENERATE tracking number)
    case 'ship_order':
        if(!$isSeller && !$isAdmin) {
            redirect_with_error(APP_URL.'/orders/order-details.php?id='.$orderId, 'Only sellers can ship orders');
        }
        
        if($order['delivery_status'] === 'shipped' || $order['delivery_status'] === 'delivered') {
            redirect_with_error(APP_URL.'/orders/order-details.php?id='.$orderId, 'Order already shipped');
        }
        
        // AUTO-GENERATE tracking number
        $trackingNumber = generateTrackingNumber();
        
        $db->query("UPDATE orders SET 
                    delivery_status='shipped', 
                    tracking_number=:track, 
                    shipped_date=NOW() 
                    WHERE order_id=:id");
        $db->bind(':track', $trackingNumber);
        $db->bind(':id', $orderId);
        
        if($db->execute()) {
            redirect_with_success(APP_URL.'/orders/order-details.php?id='.$orderId, 
                'Order shipped! Tracking: '.$trackingNumber);
        } else {
            redirect_with_error(APP_URL.'/orders/order-details.php?id='.$orderId, 'Failed to ship order');
        }
        break;
    
    // SELLER: Mark as in transit
    case 'mark_in_transit':
        if(!$isSeller && !$isAdmin) {
            redirect_with_error(APP_URL.'/orders/order-details.php?id='.$orderId, 'Only sellers can update delivery status');
        }
        
        $db->query("UPDATE orders SET delivery_status='in_transit' WHERE order_id=:id");
        $db->bind(':id', $orderId);
        
        if($db->execute()) {
            redirect_with_success(APP_URL.'/orders/order-details.php?id='.$orderId, 'Order marked as in transit!');
        } else {
            redirect_with_error(APP_URL.'/orders/order-details.php?id='.$orderId, 'Failed to update status');
        }
        break;
    
    // SELLER: Mark as delivered
    case 'mark_delivered':
        if(!$isSeller && !$isAdmin) {
            redirect_with_error(APP_URL.'/orders/order-details.php?id='.$orderId, 'Only sellers can mark as delivered');
        }
        
        if($order['delivery_status'] === 'delivered') {
            redirect_with_error(APP_URL.'/orders/order-details.php?id='.$orderId, 'Order already delivered');
        }
        
        $db->query("UPDATE orders SET delivery_status='delivered', delivery_date=NOW() WHERE order_id=:id");
        $db->bind(':id', $orderId);
        
        if($db->execute()) {
            redirect_with_success(APP_URL.'/orders/order-details.php?id='.$orderId, 'Order marked as delivered!');
        } else {
            redirect_with_error(APP_URL.'/orders/order-details.php?id='.$orderId, 'Failed to mark as delivered');
        }
        break;
    
    // BUYER: Confirm delivery received
    case 'confirm_delivery':
        if(!$isBuyer && !$isAdmin) {
            redirect_with_error(APP_URL.'/orders/order-details.php?id='.$orderId, 'Only buyers can confirm delivery');
        }
        
        if($order['delivery_status'] !== 'shipped' && $order['delivery_status'] !== 'in_transit') {
            redirect_with_error(APP_URL.'/orders/order-details.php?id='.$orderId, 'Order must be shipped first');
        }
        
        $db->query("UPDATE orders SET delivery_status='delivered', delivery_date=NOW() WHERE order_id=:id");
        $db->bind(':id', $orderId);
        
        if($db->execute()) {
            redirect_with_success(APP_URL.'/orders/order-details.php?id='.$orderId, 'Delivery confirmed! Thank you.');
        } else {
            redirect_with_error(APP_URL.'/orders/order-details.php?id='.$orderId, 'Failed to confirm delivery');
        }
        break;
    
    default:
        redirect_with_error(APP_URL.'/orders/order-details.php?id='.$orderId, 'Invalid action');
        break;
}
?>
