<?php
/**
 * PayFast ITN (Instant Transaction Notification) Handler
 * Place at: payfast/notify.php
 * This URL is registered with PayFast as your notification URL
 */

// No session or header needed - this is a server-to-server callback
require_once __DIR__.'/../config/database.php';
require_once __DIR__.'/../includes/Database.php';
require_once __DIR__.'/../includes/Security.php';

// PayFast IPs (whitelist)
$validPayfastHosts=[
    'www.payfast.co.za',
    'sandbox.payfast.co.za',
    '197.97.145.144',
    '197.97.145.145',
    '197.97.145.146',
    '197.97.145.147'
];

// Log all ITN requests for debugging
$logFile=__DIR__.'/../logs/payfast_itn.log';
$logDir=dirname($logFile);
if(!is_dir($logDir)) mkdir($logDir,0755,true);
file_put_contents($logFile,date('Y-m-d H:i:s').' ITN Received: '.json_encode($_POST)."\n",FILE_APPEND);

// Verify request comes from PayFast
$sourceIp=$_SERVER['REMOTE_ADDR']??'';
$validSource=false;
foreach($validPayfastHosts as $host){
    if($sourceIp===gethostbyname($host)||$sourceIp===$host){
        $validSource=true;
        break;
    }
}

// Allow sandbox for development
if(APP_ENV==='development') $validSource=true;

if(!$validSource){
    file_put_contents($logFile,date('Y-m-d H:i:s').' INVALID SOURCE: '.$sourceIp."\n",FILE_APPEND);
    header('HTTP/1.0 403 Forbidden');
    exit;
}

// Get POST data
$pfData=$_POST;

// Verify payment status
if(($pfData['payment_status']??'')==='COMPLETE'){
    $orderId=$pfData['custom_str1']??0;
    $payfastPaymentId=$pfData['pf_payment_id']??'';
    $amount=$pfData['amount_gross']??0;

    $db=new Database();

    // Get order
    $db->query("SELECT * FROM orders WHERE order_id=:id");
    $db->bind(':id',$orderId);
    $order=$db->fetch();

    if($order&&$order['payment_status']==='pending'){
        // Verify amount matches
        if(abs($amount-$order['total_amount'])<0.01){

            // Update order payment status
            $db->query("UPDATE orders SET payment_status='paid', payment_date=NOW() WHERE order_id=:id");
            $db->bind(':id',$orderId);
            $db->execute();

            // Create transaction record
            $db->query("INSERT INTO transactions(order_id,payfast_payment_id,transaction_amount,platform_fee,seller_payout,transaction_date)
            VALUES(:oid,:pfid,:amount,0,0,NOW())");
            $db->bind(':oid',$orderId);
            $db->bind(':pfid',$payfastPaymentId);
            $db->bind(':amount',$amount);
            $db->execute();
            // Note: platform_fee & seller_payout auto-calculated by trigger

            file_put_contents($logFile,date('Y-m-d H:i:s').' Order #'.$orderId.' PAID via PayFast'."\n",FILE_APPEND);
        }else{
            file_put_contents($logFile,date('Y-m-d H:i:s').' AMOUNT MISMATCH Order #'.$orderId.' Expected:'.$order['total_amount'].' Got:'.$amount."\n",FILE_APPEND);
        }
    }
}

// PayFast requires 200 OK response
header('HTTP/1.0 200 OK');
exit;
?>
