<?php
/**
 * ============================================
 * SEND APOLOGY EMAIL - FIXED APP_URL
 * ============================================
 * Defines APP_URL constant BEFORE loading Email class
 * NOTHING REMOVED - Just adding missing constant
 * ============================================
 */

error_reporting(0);
ini_set('display_errors', 0);
ob_start();

// Start session first
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set JSON header
header('Content-Type: application/json; charset=UTF-8');

try {
    // Auth check
    if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_type'], ['moderator', 'admin'])) {
        ob_end_clean();
        die(json_encode(['success' => false, 'message' => 'Unauthorized access']));
    }
    
    if (!isset($_POST['dispute_id'])) {
        ob_end_clean();
        die(json_encode(['success' => false, 'message' => 'Missing dispute ID']));
    }
    
    $disputeId = (int)$_POST['dispute_id'];
    $userId = $_SESSION['user_id'];
    
    // Database config
    $isLocal = ($_SERVER['HTTP_HOST'] === 'localhost' || strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false);
    $dbHost = $isLocal ? 'localhost' : 'sql110.infinityfree.com';
    $dbName = $isLocal ? 'street2screen_db' : 'if0_41132529_street2screen';
    $dbUser = $isLocal ? 'root' : 'if0_41132529';
    $dbPass = $isLocal ? 'Street2Screen2026!' : 'NewPassword@2026';
    $appUrl = $isLocal ? 'http://localhost/street2screen' : 'https://street2screen.infinityfreeapp.com';
    
    // ✅ CRITICAL FIX: Define APP_URL constant BEFORE loading Email.php
    if (!defined('APP_URL')) {
        define('APP_URL', $appUrl);
    }
    
    // Load Email class (now APP_URL is defined!)
    require_once __DIR__ . '/../includes/Email.php';
    
    // Connect to database
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    // Get dispute details
    $stmt = $pdo->prepare("SELECT d.*, p.product_name, buyer.email as buyer_email, buyer.full_name as buyer_name
                           FROM disputes d
                           JOIN orders o ON d.order_id = o.order_id
                           JOIN products p ON o.product_id = p.product_id
                           JOIN users buyer ON o.buyer_id = buyer.user_id
                           WHERE d.dispute_id = ?");
    $stmt->execute([$disputeId]);
    $dispute = $stmt->fetch();
    
    if (!$dispute) {
        ob_end_clean();
        die(json_encode(['success' => false, 'message' => 'Dispute not found']));
    }
    
    $refundAmount = floatval($dispute['refund_amount'] ?? 0);
    if ($refundAmount <= 0) {
        ob_end_clean();
        die(json_encode(['success' => false, 'message' => 'No refund amount']));
    }
    
    $buyerEmail = trim($dispute['buyer_email']);
    $buyerName = htmlspecialchars($dispute['buyer_name'], ENT_QUOTES, 'UTF-8');
    $productName = htmlspecialchars($dispute['product_name'], ENT_QUOTES, 'UTF-8');
    
    // Create beautiful email body
    $emailBody = '
    <h2>🙏 We Sincerely Apologize</h2>
    <p>Dear <strong>' . $buyerName . '</strong>,</p>
    <p>We are truly sorry for the inconvenience you experienced with <strong>' . $productName . '</strong>.</p>
    <p>After careful review of your dispute, we have resolved it in your favour.</p>
    
    <div class="info-box" style="background: #e8f5e9; border-left-color: #4caf50;">
        <p style="margin-bottom: 10px;"><strong>✅ Refund Approved</strong></p>
        <p style="font-size: 28px; font-weight: 700; color: #2e7d32; margin: 15px 0;">
            R' . number_format($refundAmount, 2) . '
        </p>
    </div>
    
    <h3 style="color: #0B1F3A; margin-top: 30px;">📋 Next Steps to Get Your Refund:</h3>
    <ol style="line-height: 1.8;">
        <li><strong>Upload Your Bank Details</strong> - Login to your account and provide your banking information securely</li>
        <li><strong>Verification</strong> - Our team will verify your details within 1-2 business days</li>
        <li><strong>Payment via EFT</strong> - Your refund will be processed and transferred within 2-3 business days</li>
        <li><strong>Confirmation</strong> - You will receive proof of payment via email once the transfer is complete</li>
    </ol>
    
    <div class="btn-container">
        <a href="' . $appUrl . '/disputes/view.php?id=' . $disputeId . '" class="btn">📤 Upload Bank Details Now</a>
    </div>
    
    <div class="divider"></div>
    
    <h3 style="color: #0B1F3A;">🤝 Our Commitment to You</h3>
    <p>At Street2Screen ZA, we strive to provide a fair and secure marketplace for all our users. Your trust means everything to us.</p>
    <p>We appreciate your patience throughout this process and apologize again for any inconvenience caused.</p>
    <p>If you have any questions about your refund, please don\'t hesitate to contact us.</p>
    
    <div class="info-box">
        <p style="margin: 0;"><strong>📧 Need Help?</strong></p>
        <p style="margin: 5px 0 0 0;">Our support team is here to assist you. Reply to this email or contact us through your account.</p>
    </div>
    
    <p style="margin-top: 30px;">Thank you for being a valued member of the Street2Screen ZA community.</p>
    <p><strong>Warm regards,</strong><br>
    <strong>The Street2Screen Team</strong></p>
    ';
    
    // Save to database first
    $emailId = null;
    try {
        $stmt = $pdo->prepare("INSERT INTO email_notifications (dispute_id, recipient_email, recipient_name, subject, message, email_type, sent_status, created_at) VALUES (?, ?, ?, ?, ?, 'apology', 'pending', NOW())");
        $stmt->execute([$disputeId, $buyerEmail, $buyerName, 'Refund Approved', $emailBody]);
        $emailId = $pdo->lastInsertId();
    } catch (Exception $e) {
        error_log("Failed to save email notification: " . $e->getMessage());
    }
    
    // SEND EMAIL USING EMAIL CLASS
    $emailSent = false;
    
    try {
        $email = new Email();
        $emailSent = $email->send(
            $buyerEmail,
            '🙏 We\'re Sorry - Refund Approved for Your Dispute',
            $emailBody,
            $buyerName
        );
    } catch (Exception $e) {
        error_log("Email sending error: " . $e->getMessage());
    }
    
    // Update status
    if ($emailId) {
        try {
            $status = $emailSent ? 'sent' : 'failed';
            $stmt = $pdo->prepare("UPDATE email_notifications SET sent_status = ?, sent_at = " . ($emailSent ? "NOW()" : "NULL") . " WHERE email_id = ?");
            $stmt->execute([$status, $emailId]);
        } catch (Exception $e) {
            error_log("Failed to update email status: " . $e->getMessage());
        }
    }
    
    // Log
    try {
        $logDetails = $emailSent ? 
            "✅ Email SENT via PHPMailer/Brevo to: $buyerEmail (R" . number_format($refundAmount, 2) . ")" : 
            "⚠️ Email FAILED - saved for manual sending to: $buyerEmail (R" . number_format($refundAmount, 2) . ")";
        $stmt = $pdo->prepare("INSERT INTO dispute_logs (dispute_id, user_id, action, details, created_at) VALUES (?, ?, 'manual_email', ?, NOW())");
        $stmt->execute([$disputeId, $userId, $logDetails]);
    } catch (Exception $e) {
        error_log("Failed to log email attempt: " . $e->getMessage());
    }
    
    // Return response
    ob_end_clean();
    if ($emailSent) {
        die(json_encode([
            'success' => true, 
            'message' => '✅ Email sent successfully to ' . $buyerEmail
        ]));
    } else {
        die(json_encode([
            'success' => false, 
            'message' => '⚠️ Email saved. View at admin/view-pending-emails.php'
        ]));
    }
    
} catch (Exception $e) {
    ob_end_clean();
    error_log("Critical error in send-apology-email.php: " . $e->getMessage());
    die(json_encode([
        'success' => false, 
        'message' => 'Error: ' . $e->getMessage()
    ]));
}
?>
