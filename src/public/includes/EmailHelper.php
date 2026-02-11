<?php
/**
 * Email Helper Class using PHPMailer and Brevo SMTP
 * Street2Screen ZA - Professional Email System
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Load PHPMailer
require_once __DIR__ . '/../../../vendor/phpmailer/src/Exception.php';
require_once __DIR__ . '/../../../vendor/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../../../vendor/phpmailer/src/SMTP.php';

// Load SMTP configuration
require_once __DIR__ . '/../../config/smtp_config.php';

class EmailHelper {
    
    private $mail;
    
    public function __construct() {
        $this->mail = new PHPMailer(true);
        $this->configureSmtp();
    }
    
    /**
     * Configure SMTP settings
     */
    private function configureSmtp() {
        try {
            // Server settings
            if (EMAIL_DEBUG) {
                $this->mail->SMTPDebug = SMTP::DEBUG_SERVER;
            }
            
            $this->mail->isSMTP();
            $this->mail->Host = SMTP_HOST;
            $this->mail->SMTPAuth = true;
            $this->mail->Username = SMTP_USERNAME;
            $this->mail->Password = SMTP_PASSWORD;
            $this->mail->SMTPSecure = SMTP_ENCRYPTION;
            $this->mail->Port = SMTP_PORT;
            
            // Default from address
            $this->mail->setFrom(FROM_EMAIL, FROM_NAME);
            $this->mail->addReplyTo(REPLY_TO_EMAIL, REPLY_TO_NAME);
            
            // Encoding
            $this->mail->CharSet = 'UTF-8';
            
        } catch (Exception $e) {
            error_log("SMTP Configuration Error: " . $e->getMessage());
        }
    }
    
    /**
     * Send verification email
     */
    public function sendVerificationEmail($toEmail, $toName, $verificationToken) {
        try {
            $this->mail->clearAddresses();
            $this->mail->addAddress($toEmail, $toName);
            
            $this->mail->isHTML(true);
            $this->mail->Subject = 'Verify Your Street2Screen ZA Account';
            
            // Verification URL
            $verificationUrl = BASE_URL . "/verify.php?token=" . $verificationToken;
            
            // Email body
            $this->mail->Body = $this->getVerificationEmailTemplate($toName, $verificationUrl);
            $this->mail->AltBody = "Hi $toName, Please verify your account by clicking this link: $verificationUrl";
            
            $this->mail->send();
            return true;
            
        } catch (Exception $e) {
            error_log("Email sending failed: " . $this->mail->ErrorInfo);
            return false;
        }
    }
    
    /**
     * Send test email
     */
    public function sendTestEmail($toEmail, $toName) {
        try {
            $this->mail->clearAddresses();
            $this->mail->addAddress($toEmail, $toName);
            
            $this->mail->isHTML(true);
            $this->mail->Subject = 'Street2Screen ZA - Email System Test';
            
            $this->mail->Body = $this->getTestEmailTemplate($toName);
            $this->mail->AltBody = "Hi $toName, This is a test email from Street2Screen ZA. If you received this, your email system is working perfectly!";
            
            $this->mail->send();
            return true;
            
        } catch (Exception $e) {
            error_log("Test email failed: " . $this->mail->ErrorInfo);
            return false;
        }
    }
    
    /**
     * Verification email HTML template
     */
    private function getVerificationEmailTemplate($name, $url) {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #0B1F3A; color: white; padding: 20px; text-align: center; }
                .content { background: #f4f4f4; padding: 30px; }
                .button { 
                    display: inline-block; 
                    background: #FFC107; 
                    color: #0B1F3A; 
                    padding: 12px 30px; 
                    text-decoration: none; 
                    border-radius: 5px;
                    font-weight: bold;
                }
                .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Street2Screen ZA</h1>
                    <p>Bringing Kasi To Your Screen</p>
                </div>
                <div class='content'>
                    <h2>Verify Your Account</h2>
                    <p>Sawubona <strong>$name</strong>,</p>
                    <p>Welcome to Street2Screen ZA! We're excited to have you join our community of township entrepreneurs and buyers.</p>
                    <p>Please verify your email address by clicking the button below:</p>
                    <p style='text-align: center; margin: 30px 0;'>
                        <a href='$url' class='button'>Verify My Account</a>
                    </p>
                    <p>Or copy and paste this link into your browser:</p>
                    <p style='word-break: break-all; background: white; padding: 10px;'>$url</p>
                    <p><strong>This link will expire in 24 hours.</strong></p>
                    <p>If you didn't create an account with Street2Screen ZA, please ignore this email.</p>
                </div>
                <div class='footer'>
                    <p>&copy; 2026 Street2Screen ZA. All rights reserved.</p>
                    <p>Empowering the South African informal economy</p>
                    <p>" . COMPANY_ADDRESS . ", " . COMPANY_CITY . ", " . COMPANY_ZIP . "</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
    
    /**
     * Test email template
     */
    private function getTestEmailTemplate($name) {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #0B1F3A; color: white; padding: 20px; text-align: center; }
                .content { background: #f4f4f4; padding: 30px; }
                .success { background: #4CAF50; color: white; padding: 15px; border-radius: 5px; text-align: center; margin-bottom: 20px; }
                .info-box { background: white; padding: 15px; border-left: 4px solid #FFC107; margin: 15px 0; }
                .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
                ul { padding-left: 20px; }
                li { margin: 8px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🚀 Street2Screen ZA</h1>
                    <p>Email System Status</p>
                </div>
                <div class='content'>
                    <div class='success'>
                        <h2>✅ Email System Test Successful!</h2>
                    </div>
                    <p>Sawubona <strong>$name</strong>,</p>
                    <p>Congratulations! Your Street2Screen ZA email notification system is working perfectly.</p>
                    
                    <div class='info-box'>
                        <h3>📧 System Configuration:</h3>
                        <ul>
                            <li><strong>SMTP Provider:</strong> Brevo (smtp-relay.brevo.com)</li>
                            <li><strong>Port:</strong> 587 (TLS Encryption)</li>
                            <li><strong>From Address:</strong> " . FROM_EMAIL . "</li>
                            <li><strong>Company:</strong> " . COMPANY_NAME . "</li>
                            <li><strong>Location:</strong> " . COMPANY_CITY . ", " . COMPANY_COUNTRY . "</li>
                            <li><strong>Status:</strong> ✅ Fully Operational</li>
                        </ul>
                    </div>
                    
                    <h3>🎯 Your platform is now ready to send:</h3>
                    <ul>
                        <li>✅ Email verification messages</li>
                        <li>✅ Password reset requests</li>
                        <li>✅ Order confirmations</li>
                        <li>✅ Vendor notifications</li>
                        <li>✅ Account updates</li>
                    </ul>
                    
                    <div class='info-box'>
                        <p><strong>Daily Email Limit:</strong> 300 emails/day (Brevo Free Tier)</p>
                        <p><strong>Perfect for:</strong> Development, testing, and small-scale production</p>
                    </div>
                    
                    <p style='margin-top: 30px;'><strong>Next Steps:</strong></p>
                    <ol>
                        <li>Integrate email verification into user registration</li>
                        <li>Set up password reset functionality</li>
                        <li>Configure order notification emails</li>
                        <li>Test with real user workflows</li>
                    </ol>
                </div>
                <div class='footer'>
                    <p>&copy; 2026 Street2Screen ZA - Empowering Township Entrepreneurs</p>
                    <p>" . COMPANY_ADDRESS . ", " . COMPANY_CITY . ", " . COMPANY_ZIP . "</p>
                    <p>" . COMPANY_COUNTRY . "</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
}
?>