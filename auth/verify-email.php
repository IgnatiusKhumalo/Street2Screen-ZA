<?php
require_once __DIR__.'/../includes/header.php';

$token = get_get('token');
$message = '';
$type = 'danger';

if(!empty($token)){
    $db = new Database();
    
    // Use email_verification_token column (not verification_token)
    $db->query("SELECT user_id, email, full_name FROM users 
                WHERE email_verification_token = :token 
                AND email_verified = 0");
    $db->bind(':token', $token);
    $user = $db->fetch();
    
    if($user){
        // Update to mark as verified and clear the token
        $db->query("UPDATE users 
                   SET email_verified = 1,
                       email_verification_token = NULL,
                       verification_token = NULL
                   WHERE user_id = :id");
        $db->bind(':id', $user['user_id']);
        
        if($db->execute()){
            $message = 'Email verified successfully! You can now login.';
            $type = 'success';
            
            // Optional: Send welcome email
            try {
                $emailer = new Email();
                $subject = "Welcome to Street2Screen!";
                $body = "
                <h2>Welcome to Street2Screen, {$user['full_name']}!</h2>
                <p>Your email has been successfully verified.</p>
                <p>You can now enjoy all the features of our platform:</p>
                <ul>
                    <li>Browse products</li>
                    <li>Make purchases</li>
                    <li>Sell your own products</li>
                    <li>Connect with other sellers</li>
                </ul>
                <p>Thank you for joining us!</p>
                <p>Street2Screen Team</p>
                ";
                $emailer->send($user['email'], $subject, $body, $user['full_name']);
            } catch(Exception $e) {
                // Ignore email sending errors
            }
        } else {
            $message = 'Verification failed. Please try again.';
        }
    } else {
        $message = 'Invalid or expired verification link.';
    }
} else {
    $message = 'No verification token provided.';
}
?>

<div class="container my-5">
<div class="row justify-content-center">
<div class="col-md-6">
<div class="card shadow">
<div class="card-header bg-<?php echo $type==='success' ? 'success' : 'danger'; ?> text-white text-center py-4">
    <h4><i class="fas fa-<?php echo $type==='success' ? 'check-circle' : 'times-circle'; ?>"></i> Email Verification</h4>
</div>
<div class="card-body text-center p-5">
    <div class="alert alert-<?php echo $type; ?> p-4">
        <?php echo Security::clean($message); ?>
    </div>
    
    <?php if($type === 'success'): ?>
        <div class="d-grid gap-2 mt-4">
            <a href="<?php echo APP_URL; ?>/auth/login.php" class="btn btn-success btn-lg">
                <i class="fas fa-sign-in-alt"></i> Login Now
            </a>
        </div>
    <?php else: ?>
        <div class="d-grid gap-2 mt-4">
            <a href="<?php echo APP_URL; ?>/auth/register.php" class="btn btn-warning btn-lg">
                <i class="fas fa-user-plus"></i> Register Again
            </a>
            <a href="<?php echo APP_URL; ?>/" class="btn btn-secondary">
                <i class="fas fa-home"></i> Go Home
            </a>
        </div>
    <?php endif; ?>
</div>
</div>
</div>
</div>
</div>

<?php require_once __DIR__.'/../includes/footer.php'; ?>
