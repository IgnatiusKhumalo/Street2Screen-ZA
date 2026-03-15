<?php
/**
 * ============================================
 * MARK EMAIL AS SENT
 * ============================================
 * When admin manually sends email via Gmail
 * ============================================
 */

require_once __DIR__.'/../includes/header.php';
Security::requireAdmin();

$emailId = get_get('id');

if ($emailId) {
    $db = new Database();
    
    try {
        $db->query("UPDATE email_notifications 
                    SET sent_status = 'sent', 
                        sent_at = NOW()
                    WHERE email_id = :eid");
        $db->bind(':eid', $emailId);
        $db->execute();
        
        redirect_with_success(APP_URL.'/admin/view-pending-emails.php', 'Email marked as sent!');
    } catch (Exception $e) {
        redirect_with_error(APP_URL.'/admin/view-pending-emails.php', 'Failed to update email status');
    }
} else {
    redirect_with_error(APP_URL.'/admin/view-pending-emails.php', 'Invalid email ID');
}
?>
