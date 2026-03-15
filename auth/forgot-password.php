<?php
// FIX: Set timezone to match database
date_default_timezone_set('Africa/Johannesburg');

// Check if we're in the right directory
$headerPath = __DIR__.'/../includes/header.php';
if(!file_exists($headerPath)) {
    die("ERROR: Cannot find header.php at: " . $headerPath . "<br>Current directory: " . __DIR__);
}

$pageTitle = 'Forgot Password';
require_once $headerPath;

$errors = [];
$success = false;
$tempPassword = '';

if(is_post_request()) {
    if(!Security::validateCSRFToken(get_post('csrf_token'))) {
        $errors[] = 'Invalid security token';
    } else {
        $email = Security::sanitizeEmail(get_post('email'));
        
        if(empty($email)) {
            $errors[] = 'Email is required';
        } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email format';
        } else {
            $db = new Database();
            
            // Check if user exists
            $db->query("SELECT user_id, full_name, email FROM users WHERE email=:email");
            $db->bind(':email', $email);
            $user = $db->fetch();
            
            if($user) {
                // Generate temporary password (8 characters: uppercase, lowercase, numbers, special)
                $tempPassword = substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ'), 0, 2) . 
                               substr(str_shuffle('abcdefghjkmnpqrstuvwxyz'), 0, 3) . 
                               substr(str_shuffle('23456789'), 0, 2) . 
                               substr(str_shuffle('!@#$%'), 0, 1);
                $tempPassword = str_shuffle($tempPassword);
                
                $tempPasswordHash = Security::hashPassword($tempPassword);
                
                // Generate reset token
                $token = bin2hex(random_bytes(32));
                
                // Save temp password and token to users table
                $db->query("UPDATE users 
                           SET temp_password = :temp_hash,
                               temp_password_expires = DATE_ADD(NOW(), INTERVAL 1 HOUR)
                           WHERE user_id = :uid");
                $db->bind(':temp_hash', $tempPasswordHash);
                $db->bind(':uid', $user['user_id']);
                $db->execute();
                
                // Also save token to password_resets table
                $db->query("INSERT INTO password_resets (email, reset_token, token_expiry, used) 
                           VALUES (:email, :token, DATE_ADD(NOW(), INTERVAL 1 HOUR), 0)");
                $db->bind(':email', $email);
                $db->bind(':token', $token);
                $db->execute();
                
                // Send email with temporary password AND reset link
                $resetLink = APP_URL . '/auth/reset-password.php?token=' . $token;
                
                $emailer = new Email();
                $subject = "🔐 Password Reset - Temporary Password - Street2Screen";
                $body = "
                <h2>Password Reset Request</h2>
                <p>Hi {$user['full_name']},</p>
                <p>You requested to reset your password. Here is your <strong>temporary password</strong>:</p>
                
                <div style='background:#fff3cd; padding:25px; border-left:4px solid #ffc107; margin:20px 0; text-align:center'>
                    <h3 style='color:#0B1F3A; margin-bottom:10px'>Temporary Password</h3>
                    <div style='background:white; padding:20px; border:2px dashed #ffc107; border-radius:5px; font-family:monospace; font-size:28px; letter-spacing:3px; color:#0B1F3A; font-weight:bold'>
                        {$tempPassword}
                    </div>
                    <p style='margin-top:10px; margin-bottom:0'><small>Copy this password exactly (case-sensitive)</small></p>
                </div>
                
                <div style='background:#d1ecf1; padding:20px; border-left:4px solid #0c5460; margin:20px 0'>
                    <h4>Next Steps:</h4>
                    <ol style='margin-bottom:0'>
                        <li>Click the button below to reset your password</li>
                        <li>Enter your <strong>temporary password</strong> (shown above)</li>
                        <li>Create your new password (must be strong)</li>
                        <li>Confirm your new password</li>
                        <li>Login with your new password</li>
                    </ol>
                </div>
                
                <div style='text-align:center; margin:30px 0'>
                    <a href='{$resetLink}' 
                       style='background:#0B1F3A; color:white; padding:15px 40px; text-decoration:none; border-radius:5px; display:inline-block; font-weight:bold; font-size:16px'>
                        Reset Password Now
                    </a>
                </div>
                
                <div style='background:#f8d7da; padding:15px; border-left:4px solid #dc3545; margin:20px 0'>
                    <p><strong>⏰ Important:</strong></p>
                    <ul>
                        <li>This temporary password expires in <strong>1 hour</strong></li>
                        <li>You must use it to set your new password</li>
                        <li>If you didn't request this, please contact us immediately</li>
                    </ul>
                </div>
                
                <p>Thank you,<br>Street2Screen Team</p>
                ";
                
                if($emailer->send($user['email'], $subject, $body, $user['full_name'])) {
                    $success = true;
                }
            } else {
                $success = true;
            }
        }
    }
}

$csrfToken = Security::generateCSRFToken();
?>

<div class="container my-5">
<div class="row justify-content-center">
<div class="col-md-6">

<div class="card shadow">
    <div class="card-header bg-warning text-dark text-center py-3">
        <h4><i class="fas fa-key"></i> Forgot Password</h4>
    </div>
    <div class="card-body p-4">
        
        <?php if($success): ?>
        
        <!-- GREEN BOX -->
        <div class="alert alert-success" role="alert">
            <i class="fas fa-check-circle fa-3x mb-3 d-block text-center"></i>
            <h5 class="text-center">Temporary Password Sent!</h5>
            <p class="text-center">If an account exists with that email, you'll receive a temporary password shortly.</p>
        </div>
        
        <!-- BLUE BOX -->
        <div class="alert alert-info" role="alert">
            <h6><i class="fas fa-info-circle"></i> What's in the email:</h6>
            <ul class="mb-0">
                <li><strong>Temporary Password</strong> (8 characters)</li>
                <li><strong>Reset Link</strong> (clickable button)</li>
                <li><strong>Instructions</strong> on how to proceed</li>
            </ul>
        </div>
        
        <!-- ORANGE BOX -->
        <div class="alert alert-warning" role="alert">
            <h6><i class="fas fa-exclamation-triangle"></i> Next Steps:</h6>
            <ol class="mb-0">
                <li>Check your email inbox (and spam folder)</li>
                <li>Copy the temporary password</li>
                <li>Click the "Reset Password Now" button</li>
                <li>Enter temporary password + new password</li>
                <li>Done! Login with new password</li>
            </ol>
        </div>
        
        <div class="d-grid mt-3">
            <a href="<?php echo APP_URL; ?>/auth/login.php" class="btn btn-primary btn-lg">
                <i class="fas fa-arrow-left"></i> Back to Login
            </a>
        </div>
        
        <?php else: ?>
        
        <?php if(!empty($errors)): ?>
        <div class="alert alert-danger" role="alert">
            <ul class="mb-0">
            <?php foreach($errors as $error): ?>
                <li><?php echo Security::clean($error); ?></li>
            <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
        
        <div class="alert alert-info" role="alert">
            <i class="fas fa-info-circle"></i>
            <p class="mb-0"><strong>Forgot your password?</strong><br>
            Enter your email address and we'll send you a <strong>temporary password</strong> to reset your account.</p>
        </div>
        
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
            
            <div class="mb-3">
                <label class="fw-bold">Email Address *</label>
                <input type="email" 
                       class="form-control form-control-lg" 
                       name="email" 
                       placeholder="your@email.com"
                       required 
                       autofocus>
            </div>
            
            <div class="d-grid mb-3">
                <button type="submit" class="btn btn-warning btn-lg">
                    <i class="fas fa-unlock-alt"></i> Reset Your Password
                </button>
            </div>
        </form>
        
        <div class="text-center">
            <p class="mb-0">Remember your password? 
                <a href="<?php echo APP_URL; ?>/auth/login.php" class="fw-bold">Login</a>
            </p>
        </div>
        
        <?php endif; ?>
    </div>
</div>

</div>
</div>
</div>

<?php 
$footerPath = __DIR__.'/../includes/footer.php';
if(file_exists($footerPath)) {
    require_once $footerPath;
}
?>
