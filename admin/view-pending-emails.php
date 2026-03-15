<?php
/**
 * ============================================
 * ADMIN - VIEW PENDING EMAILS
 * ============================================
 * Shows all emails that need to be sent manually
 * Admin can view content and copy to send via Gmail
 * ============================================
 */

$pageTitle = 'Pending Emails';
require_once __DIR__.'/../includes/header.php';
Security::requireAdmin();

$db = new Database();

// Get all pending/failed emails
$db->query("SELECT e.*, d.dispute_id, d.resolution_outcome
            FROM email_notifications e
            LEFT JOIN disputes d ON e.dispute_id = d.dispute_id
            WHERE e.sent_status IN ('pending', 'failed')
            ORDER BY e.created_at DESC");
$pendingEmails = $db->fetchAll();

// Get count
$pendingCount = count($pendingEmails);
?>

<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">
            <i class="fas fa-envelope"></i> Pending Emails
            <?php if ($pendingCount > 0): ?>
            <span class="badge bg-warning"><?php echo $pendingCount; ?></span>
            <?php endif; ?>
        </h2>
        <a href="<?php echo APP_URL; ?>/admin/disputes.php" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Back to Disputes
        </a>
    </div>

    <?php if ($pendingCount === 0): ?>
    <div class="alert alert-success">
        <h4 class="alert-heading">✅ All Caught Up!</h4>
        <p class="mb-0">There are no pending emails to send.</p>
    </div>
    <?php else: ?>
    
    <div class="alert alert-warning">
        <h5><i class="fas fa-info-circle"></i> About Pending Emails</h5>
        <p class="mb-0">
            These emails were saved but couldn't be sent automatically because PHP mail() is not configured in XAMPP.
            <strong>You can manually send these emails by copying the content below.</strong>
        </p>
    </div>

    <?php foreach ($pendingEmails as $email): ?>
    <div class="card shadow-sm mb-4 border-warning">
        <div class="card-header bg-warning text-dark">
            <h5 class="mb-0">
                📧 Email #<?php echo $email['email_id']; ?> - 
                <?php echo $email['email_type'] === 'apology' ? 'Apology for Refund' : ucfirst($email['email_type']); ?>
            </h5>
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <tr>
                    <th style="width: 150px;">Dispute ID:</th>
                    <td>
                        <a href="<?php echo APP_URL; ?>/disputes/view.php?id=<?php echo $email['dispute_id']; ?>" target="_blank">
                            #<?php echo $email['dispute_id']; ?>
                        </a>
                    </td>
                </tr>
                <tr>
                    <th>Recipient:</th>
                    <td>
                        <strong><?php echo Security::clean($email['recipient_name']); ?></strong>
                        <br>
                        <a href="mailto:<?php echo $email['recipient_email']; ?>">
                            <?php echo Security::clean($email['recipient_email']); ?>
                        </a>
                    </td>
                </tr>
                <tr>
                    <th>Subject:</th>
                    <td><strong><?php echo Security::clean($email['subject']); ?></strong></td>
                </tr>
                <tr>
                    <th>Status:</th>
                    <td>
                        <span class="badge bg-<?php echo $email['sent_status'] === 'failed' ? 'danger' : 'warning'; ?>">
                            <?php echo ucfirst($email['sent_status']); ?>
                        </span>
                    </td>
                </tr>
                <tr>
                    <th>Created:</th>
                    <td><?php echo format_datetime($email['created_at']); ?></td>
                </tr>
            </table>

            <hr>

            <h6>📨 How to Send This Email:</h6>
            <ol>
                <li>Click "Copy Email Content" button below</li>
                <li>Open your Gmail or email client</li>
                <li>Compose new email to: <strong><?php echo $email['recipient_email']; ?></strong></li>
                <li>Paste the content (Ctrl+V)</li>
                <li>Send!</li>
                <li>Then mark as sent below</li>
            </ol>

            <div class="mb-3">
                <button class="btn btn-primary" onclick="copyEmailContent<?php echo $email['email_id']; ?>()">
                    <i class="fas fa-copy"></i> Copy Email Content
                </button>
                <a href="<?php echo APP_URL; ?>/admin/mark-email-sent.php?id=<?php echo $email['email_id']; ?>" 
                   class="btn btn-success"
                   onclick="return confirm('Did you manually send this email?');">
                    <i class="fas fa-check"></i> Mark as Sent
                </a>
                <button class="btn btn-secondary" onclick="toggleEmailPreview<?php echo $email['email_id']; ?>()">
                    <i class="fas fa-eye"></i> Preview Email
                </button>
            </div>

            <!-- Hidden textarea for copying -->
            <textarea id="emailContent<?php echo $email['email_id']; ?>" style="position: absolute; left: -9999px;">
Subject: <?php echo $email['subject']; ?>

To: <?php echo $email['recipient_name']; ?> (<?php echo $email['recipient_email']; ?>)

<?php echo strip_tags($email['message']); ?>
            </textarea>

            <!-- Email preview (hidden by default) -->
            <div id="emailPreview<?php echo $email['email_id']; ?>" style="display: none;" class="mt-3">
                <div class="border p-3 bg-light">
                    <h6>Email Preview:</h6>
                    <iframe srcdoc="<?php echo htmlspecialchars($email['message']); ?>" 
                            style="width: 100%; height: 600px; border: 1px solid #ddd; background: white;">
                    </iframe>
                </div>
            </div>

            <script>
            function copyEmailContent<?php echo $email['email_id']; ?>() {
                const textarea = document.getElementById('emailContent<?php echo $email['email_id']; ?>');
                textarea.style.position = 'static';
                textarea.style.left = '0';
                textarea.select();
                document.execCommand('copy');
                textarea.style.position = 'absolute';
                textarea.style.left = '-9999px';
                alert('✅ Email content copied! Paste it in your email client.');
            }

            function toggleEmailPreview<?php echo $email['email_id']; ?>() {
                const preview = document.getElementById('emailPreview<?php echo $email['email_id']; ?>');
                preview.style.display = preview.style.display === 'none' ? 'block' : 'none';
            }
            </script>
        </div>
    </div>
    <?php endforeach; ?>

    <?php endif; ?>
</div>

<?php require_once __DIR__.'/../includes/footer.php'; ?>
