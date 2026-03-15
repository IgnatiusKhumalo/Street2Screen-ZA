<?php
/**
 * ============================================
 * EMAIL CLASS - PHPMAILER WITH BREVO SMTP
 * ============================================
 * Professional email templates with logo and branding
 * Sends emails using PHPMailer library via Brevo SMTP
 * FINAL VERSION: Gold bold header + Address added
 * ============================================
 */
// Load Brevo configuration
require_once __DIR__ . '/../config/brevo.php';
// Load PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
// Autoload PHPMailer classes
spl_autoload_register(function ($class) {
    $prefix = 'PHPMailer\\PHPMailer\\';
    $base_dir = __DIR__ . '/../vendor/phpmailer/phpmailer/src/';
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    
    if (file_exists($file)) {
        require $file;
    }
});
class Email {
    
    private $mailer;
    
    /**
     * Constructor - initialize PHPMailer with Brevo SMTP
     */
    public function __construct() {
        $this->mailer = new PHPMailer(true);
        
        try {
            // Server settings
            if (SMTP_DEBUG) {
                $this->mailer->SMTPDebug = SMTP::DEBUG_OFF; // Disable debug in production
            }
            
            $this->mailer->isSMTP();
            $this->mailer->Host       = SMTP_HOST;
            $this->mailer->SMTPAuth   = true;
            $this->mailer->Username   = SMTP_USERNAME;
            $this->mailer->Password   = SMTP_PASSWORD;
            $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $this->mailer->Port       = SMTP_PORT;
            
            // Default from address
            $this->mailer->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
            $this->mailer->addReplyTo(SMTP_REPLY_TO, SMTP_FROM_NAME);
            
            // Encoding
            $this->mailer->CharSet = 'UTF-8';
            $this->mailer->isHTML(true);
            
            // Embed logo for email (inline attachment)
            $logoPath = __DIR__ . '/../assets/images/logo.png';
            if (file_exists($logoPath)) {
                $this->mailer->addEmbeddedImage($logoPath, 'logo', 'logo.png');
            }
            
        } catch (Exception $e) {
            error_log("PHPMailer Initialization Error: " . $e->getMessage());
        }
    }
    
    /**
     * Send email using Brevo SMTP
     * 
     * @param string $to Recipient email
     * @param string $subject Email subject
     * @param string $body Email body (HTML)
     * @param string $toName Recipient name (optional)
     * @return bool Success status
     */
    public function send($to, $subject, $body, $toName = '') {
        try {
            // Clear previous recipients
            $this->mailer->clearAddresses();
            
            // Validate email
            if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
                error_log("Invalid email address: $to");
                return false;
            }
            
            // Set recipient
            $this->mailer->addAddress($to, $toName);
            
            // Set subject and body
            $this->mailer->Subject = $subject;
            $this->mailer->Body    = $this->wrapInTemplate($body);
            $this->mailer->AltBody = strip_tags($body);
            
            // Send email
            $this->mailer->send();
            error_log("Email sent successfully to: $to");
            return true;
            
        } catch (Exception $e) {
            error_log("Email sending failed to $to: " . $this->mailer->ErrorInfo);
            return false;
        }
    }
    
    /**
     * Wrap email content in beautiful professional HTML template
     * Design inspired by Amazon, Gumtree, and modern e-commerce standards
     */
    private function wrapInTemplate($content) {
        // Get logo URL for email display
        $logoUrl = APP_URL . '/assets/images/logo.png';
        
        return '
<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="x-apple-disable-message-reformatting">
    <title>Street2Screen ZA</title>
    <!--[if mso]>
    <noscript>
        <xml>
            <o:OfficeDocumentSettings>
                <o:PixelsPerInch>96</o:PixelsPerInch>
            </o:OfficeDocumentSettings>
        </xml>
    </noscript>
    <![endif]-->
    <style>
        /* Reset styles for email clients */
        body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        img { -ms-interpolation-mode: bicubic; border: 0; height: auto; line-height: 100%; outline: none; text-decoration: none; }
        
        /* Main styles */
        body {
            margin: 0 !important;
            padding: 0 !important;
            background-color: #f5f5f5;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            font-size: 16px;
            line-height: 1.6;
            color: #333333;
        }
        
        /* Container */
        .email-wrapper {
            width: 100%;
            background-color: #f5f5f5;
            padding: 20px 0;
        }
        
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        /* UPDATED: Header with GOLD BOLD text */
        .email-header {
            background: linear-gradient(135deg, #0B1F3A 0%, #1a3a6b 100%);
            padding: 30px 40px;
            text-align: center;
            border-bottom: 4px solid #FFC107;
        }
        
        .email-header img {
            max-width: 200px;
            height: auto;
            display: block;
            margin: 0 auto 15px;
        }
        
        .email-header h1 {
            margin: 0;
            padding: 0;
            font-size: 28px;
            font-weight: 900;
            color: #FFC107;
            letter-spacing: 1px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        
        .email-header h1 .za {
            color: #FFD54F;
        }
        
        .email-header .tagline {
            margin: 10px 0 0 0;
            font-size: 15px;
            color: #FFD54F;
            font-weight: 700;
            letter-spacing: 0.5px;
        }
        
        /* Body content */
        .email-body {
            padding: 40px 40px 30px;
            background-color: #ffffff;
        }
        
        .email-body h2 {
            margin: 0 0 20px 0;
            font-size: 22px;
            font-weight: 600;
            color: #0B1F3A;
        }
        
        .email-body p {
            margin: 0 0 16px 0;
            color: #555555;
            line-height: 1.7;
        }
        
        .email-body ul, .email-body ol {
            margin: 0 0 16px 20px;
            padding: 0;
        }
        
        .email-body li {
            margin-bottom: 8px;
            color: #555555;
        }
        
        /* Call-to-action button */
        .btn-container {
            text-align: center;
            margin: 30px 0;
        }
        
        .btn {
            display: inline-block;
            padding: 14px 40px;
            background-color: #FFC107;
            color: #0B1F3A !important;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 700;
            font-size: 16px;
            box-shadow: 0 4px 12px rgba(255, 193, 7, 0.3);
            transition: all 0.3s ease;
        }
        
        .btn:hover {
            background-color: #FFD54F;
            box-shadow: 0 6px 16px rgba(255, 193, 7, 0.4);
        }
        
        /* Info box */
        .info-box {
            background-color: #f8f9fa;
            border-left: 4px solid #FFC107;
            padding: 16px 20px;
            margin: 20px 0;
            border-radius: 4px;
        }
        
        .info-box p {
            margin: 0;
            font-size: 14px;
            color: #666666;
        }
        
        /* Divider */
        .divider {
            height: 1px;
            background-color: #e0e0e0;
            margin: 30px 0;
        }
        
        /* UPDATED: Footer with address */
        .email-footer {
            background-color: #f8f9fa;
            padding: 30px 40px;
            text-align: center;
            border-top: 1px solid #e0e0e0;
        }
        
        .email-footer .brand {
            font-size: 18px;
            font-weight: 700;
            color: #0B1F3A;
            margin-bottom: 8px;
        }
        
        .email-footer .description {
            font-size: 13px;
            color: #777777;
            margin-bottom: 15px;
        }
        
        .email-footer .address {
            font-size: 13px;
            color: #555555;
            line-height: 1.6;
            margin: 15px 0;
            font-style: normal;
        }
        
        .footer-links {
            margin: 20px 0;
        }
        
        .footer-links a {
            color: #0B1F3A;
            text-decoration: none;
            font-size: 13px;
            margin: 0 12px;
            font-weight: 500;
        }
        
        .footer-links a:hover {
            color: #FFC107;
            text-decoration: underline;
        }
        
        .social-icons {
            margin: 20px 0;
        }
        
        .social-icons a {
            display: inline-block;
            width: 36px;
            height: 36px;
            line-height: 36px;
            background-color: #0B1F3A;
            color: #ffffff;
            border-radius: 50%;
            margin: 0 6px;
            text-decoration: none;
            font-size: 16px;
        }
        
        .social-icons a:hover {
            background-color: #FFC107;
            color: #0B1F3A;
        }
        
        .copyright {
            font-size: 12px;
            color: #999999;
            margin-top: 20px;
        }
        
        /* Responsive */
        @media only screen and (max-width: 600px) {
            .email-header,
            .email-body,
            .email-footer {
                padding: 20px !important;
            }
            
            .email-header h1 {
                font-size: 24px;
            }
            
            .btn {
                padding: 12px 30px;
                font-size: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <table role="presentation" class="email-container" width="100%" cellspacing="0" cellpadding="0" border="0">
            <!-- Header - UPDATED: Gold bold text -->
            <tr>
                <td class="email-header">
                    <img src="cid:logo" alt="Street2Screen ZA Logo" style="max-width: 180px; height: auto;">
                    <h1>Street2Screen<span class="za">ZA</span></h1>
                    <p class="tagline">🇿🇦 Bringing Kasi To Your Screen</p>
                </td>
            </tr>
            
            <!-- Body Content -->
            <tr>
                <td class="email-body">
                    ' . $content . '
                </td>
            </tr>
            
            <!-- Footer - UPDATED: Added address -->
            <tr>
                <td class="email-footer">
                    <div class="brand">Street2Screen ZA</div>
                    <div class="description">South Africa\'s Premier Township Marketplace</div>
                    
                    <div class="address">
                        📍 44 Alsatian Road, Glen Austin<br>
                        Midrand, 1685<br>
                        South Africa
                    </div>
                    
                    <div class="divider" style="margin: 20px auto; width: 80%; height: 1px; background: #e0e0e0;"></div>
                    
                    <div class="footer-links">
                        <a href="' . APP_URL . '">Home</a>
                        <a href="' . APP_URL . '/products">Shop</a>
                        <a href="' . APP_URL . '/pages/about.php">About Us</a>
                        <a href="' . APP_URL . '/pages/contact.php">Contact</a>
                        <a href="' . APP_URL . '/pages/privacy.php">Privacy</a>
                    </div>
                    
                    <div class="social-icons">
                        <a href="#" title="Facebook">f</a>
                        <a href="#" title="Twitter">𝕏</a>
                        <a href="#" title="Instagram">📷</a>
                    </div>
                    
                    <div class="copyright">
                        © ' . date('Y') . ' Street2Screen ZA. All rights reserved.<br>
                        Empowering township entrepreneurs across South Africa.
                    </div>
                    
                    <div style="margin-top: 20px; font-size: 11px; color: #aaa;">
                        <p style="margin: 5px 0;">You received this email because you registered on Street2Screen ZA.</p>
                        <p style="margin: 5px 0;">If you did not request this, please ignore this email.</p>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
        ';
    }
    
    /**
     * Send verification email with professional template
     */
    public function sendVerificationEmail($to, $name, $token) {
        $verificationUrl = APP_URL . '/auth/verify-email.php?token=' . $token;
        
        $body = '
        <h2>Verify Your Email Address</h2>
        <p>Hi <strong>' . htmlspecialchars($name) . '</strong>,</p>
        <p>Welcome to Street2Screen ZA! We\'re excited to have you join our community of township entrepreneurs and shoppers.</p>
        <p>To complete your registration and start using your account, please verify your email address by clicking the button below:</p>
        
        <div class="btn-container">
            <a href="' . $verificationUrl . '" class="btn">Verify Email Address</a>
        </div>
        
        <div class="info-box">
            <p><strong>⏰ This link expires in 24 hours</strong></p>
            <p>For security reasons, this verification link will only work once and expires after 24 hours.</p>
        </div>
        
        <p>If the button above doesn\'t work, copy and paste this link into your browser:</p>
        <p style="word-break: break-all; font-size: 13px; color: #0B1F3A; background: #f8f9fa; padding: 12px; border-radius: 4px; font-family: monospace;">
            ' . $verificationUrl . '
        </p>
        
        <div class="divider"></div>
        
        <p style="font-size: 13px; color: #777;">
            <strong>Didn\'t create an account?</strong><br>
            If you did not register for a Street2Screen ZA account, please ignore this email. Your email address will not be used.
        </p>
        ';
        
        return $this->send($to, '🇿🇦 Verify Your Email - Street2Screen ZA', $body, $name);
    }
    
    /**
     * Send password reset email with professional template
     */
    public function sendPasswordResetEmail($to, $name, $token) {
        $resetUrl = APP_URL . '/auth/reset-password.php?token=' . $token;
        
        $body = '
        <h2>Reset Your Password</h2>
        <p>Hi <strong>' . htmlspecialchars($name) . '</strong>,</p>
        <p>We received a request to reset the password for your Street2Screen ZA account.</p>
        <p>Click the button below to create a new password:</p>
        
        <div class="btn-container">
            <a href="' . $resetUrl . '" class="btn">Reset Password</a>
        </div>
        
        <div class="info-box">
            <p><strong>⏰ This link expires in 1 hour</strong></p>
            <p>For your security, this password reset link will only work once and expires after 1 hour.</p>
        </div>
        
        <p>If the button above doesn\'t work, copy and paste this link into your browser:</p>
        <p style="word-break: break-all; font-size: 13px; color: #0B1F3A; background: #f8f9fa; padding: 12px; border-radius: 4px; font-family: monospace;">
            ' . $resetUrl . '
        </p>
        
        <div class="divider"></div>
        
        <p style="font-size: 13px; color: #777;">
            <strong>Didn\'t request this?</strong><br>
            If you did not request a password reset, please ignore this email. Your password will remain unchanged and your account is secure.
        </p>
        ';
        
        return $this->send($to, '🔒 Reset Your Password - Street2Screen ZA', $body, $name);
    }
    
    /**
     * Send order confirmation email with professional template
     */
    public function sendOrderConfirmation($to, $name, $orderId, $totalAmount) {
        $orderUrl = APP_URL . '/orders/order-details.php?id=' . $orderId;
        
        $body = '
        <h2>Order Confirmation</h2>
        <p>Hi <strong>' . htmlspecialchars($name) . '</strong>,</p>
        <p>Thank you for your order! We\'re processing it now and will notify you when it ships.</p>
        
        <div class="info-box" style="background: #f0f8ff; border-left-color: #0B1F3A;">
            <p style="margin-bottom: 8px;"><strong>Order Details:</strong></p>
            <p style="margin: 4px 0;"><strong>Order Number:</strong> #' . $orderId . '</p>
            <p style="margin: 4px 0;"><strong>Total Amount:</strong> ' . format_currency($totalAmount) . '</p>
            <p style="margin: 4px 0;"><strong>Order Date:</strong> ' . date('F j, Y') . '</p>
        </div>
        
        <div class="btn-container">
            <a href="' . $orderUrl . '" class="btn">View Order Details</a>
        </div>
        
        <p><strong>What happens next?</strong></p>
        <ol>
            <li>Our team will verify your order</li>
            <li>The seller will prepare your items</li>
            <li>You\'ll receive a shipping notification</li>
            <li>Track your delivery in your account</li>
        </ol>
        
        <div class="divider"></div>
        
        <p style="font-size: 13px; color: #777;">
            <strong>Need help?</strong> Contact our support team at any time. We\'re here to help!
        </p>
        ';
        
        return $this->send($to, '🛍️ Order Confirmation #' . $orderId . ' - Street2Screen ZA', $body, $name);
    }
}
?>
