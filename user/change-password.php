<?php
$pageTitle = 'Change Password';
require_once __DIR__.'/../includes/header.php';
Security::requireLogin();

$db = new Database();
$userId = Security::getUserId();
$errors = [];
$success = false;

// Get user info
$db->query("SELECT email, full_name, temp_password, temp_password_expires FROM users WHERE user_id=:uid");
$db->bind(':uid', $userId);
$user = $db->fetch();

if(is_post_request()) {
    if(!Security::validateCSRFToken(get_post('csrf_token'))) {
        $errors[] = 'Invalid security token';
    } else {
        $currentPassword = get_post('current_password');
        $newPassword = get_post('new_password');
        $confirmPassword = get_post('confirm_password');
        
        if(empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $errors[] = 'All fields are required';
        } elseif(strlen($newPassword) < 8) {
            $errors[] = 'New password must be at least 8 characters';
        } elseif($newPassword !== $confirmPassword) {
            $errors[] = 'New passwords do not match';
        } else {
            // Check if current password is correct OR temp password is valid
            $db->query("SELECT password_hash, temp_password, temp_password_expires FROM users WHERE user_id=:uid");
            $db->bind(':uid', $userId);
            $userData = $db->fetch();
            
            $isCurrentPasswordValid = Security::verifyPassword($currentPassword, $userData['password_hash']);
            $isTempPasswordValid = false;
            
            // Check temp password if exists and not expired
            if(!empty($userData['temp_password']) && !empty($userData['temp_password_expires'])) {
                if(strtotime($userData['temp_password_expires']) > time()) {
                    $isTempPasswordValid = Security::verifyPassword($currentPassword, $userData['temp_password']);
                }
            }
            
            if($isCurrentPasswordValid || $isTempPasswordValid) {
                // Hash new password
                $newPasswordHash = Security::hashPassword($newPassword);
                
                // Update password and clear temp password
                $db->query("UPDATE users 
                           SET password_hash = :hash,
                               temp_password = NULL,
                               temp_password_expires = NULL,
                               updated_at = NOW()
                           WHERE user_id = :uid");
                $db->bind(':hash', $newPasswordHash);
                $db->bind(':uid', $userId);
                $db->execute();
                
                // Send confirmation email
                $emailer = new Email();
                $subject = "✅ Password Changed Successfully - Street2Screen";
                $body = "
                <h2>Password Changed Successfully</h2>
                <p>Hi {$user['full_name']},</p>
                <p>Your password has been successfully changed.</p>
                
                <div style='background:#d1ecf1; padding:15px; border-left:4px solid #0c5460; margin:20px 0'>
                    <p><strong>Password Change Details:</strong></p>
                    <ul>
                        <li>Date: " . date('F j, Y') . "</li>
                        <li>Time: " . date('g:i A') . "</li>
                        <li>Account: {$user['email']}</li>
                    </ul>
                </div>
                
                <div style='background:#d4edda; padding:15px; border-left:4px solid #28a745; margin:20px 0'>
                    <p><strong>✓ Your account is now secure</strong></p>
                    <p>You can login with your new password immediately.</p>
                </div>
                
                <div style='background:#fff3cd; padding:15px; border-left:4px solid #ffc107; margin:20px 0'>
                    <p><strong>🔒 Security Tip:</strong></p>
                    <p>If you did not make this change, please contact us immediately at " . APP_EMAIL . "</p>
                </div>
                
                <p>Thank you for keeping your account secure!</p>
                <p>Street2Screen Team</p>
                ";
                
                $emailer->send($user['email'], $subject, $body, $user['full_name']);
                
                $success = true;
            } else {
                $errors[] = 'Current password or temporary password is incorrect';
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
    <div class="card-header bg-primary text-white text-center py-3">
        <h4><i class="fas fa-lock"></i> Change Password</h4>
    </div>
    <div class="card-body p-4">
        
        <?php if($success): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle fa-3x mb-3 d-block text-center"></i>
            <h5 class="text-center">Password Changed Successfully!</h5>
            <p class="mb-0 text-center">Your password has been updated. You can now login with your new password.</p>
        </div>
        <div class="d-grid">
            <a href="<?php echo APP_URL; ?>/user/dashboard.php" class="btn btn-success btn-lg">
                <i class="fas fa-home"></i> Go to Dashboard
            </a>
        </div>
        
        <?php else: ?>
        
        <?php if(!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
            <?php foreach($errors as $error): ?>
                <li><?php echo Security::clean($error); ?></li>
            <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
        
        <?php if(!empty($user['temp_password']) && strtotime($user['temp_password_expires']) > time()): ?>
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i>
            <strong>Temporary Password Active</strong>
            <p class="mb-0 mt-2">You have a temporary password. Enter it below along with your new password.</p>
            <p class="mb-0"><small>Temporary password expires: <?php echo date('M j, Y g:i A', strtotime($user['temp_password_expires'])); ?></small></p>
        </div>
        <?php endif; ?>
        
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i>
            <p class="mb-0">Enter your <strong>current password</strong> (or <strong>temporary password</strong> if you received one) and choose a new password.</p>
        </div>
        
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
            
            <div class="mb-3">
                <label class="fw-bold">Current Password / Temporary Password *</label>
                <div style="position:relative">
                    <input type="password" 
                           class="form-control form-control-lg" 
                           id="currentPassword"
                           name="current_password" 
                           placeholder="Enter current or temporary password"
                           required
                           autofocus>
                    <i class="fas fa-eye" 
                       onclick="togglePassword('currentPassword')" 
                       style="position:absolute;right:10px;top:50%;transform:translateY(-50%);cursor:pointer"></i>
                </div>
                <small class="text-muted">If you received a temporary password via email, enter it here</small>
            </div>
            
            <div class="mb-3">
                <label class="fw-bold">New Password *</label>
                <div style="position:relative">
                    <input type="password" 
                           class="form-control form-control-lg" 
                           id="newPassword"
                           name="new_password" 
                           required
                           minlength="8">
                    <i class="fas fa-eye" 
                       onclick="togglePassword('newPassword')" 
                       style="position:absolute;right:10px;top:50%;transform:translateY(-50%);cursor:pointer"></i>
                </div>
                <small class="text-muted">Minimum 8 characters</small>
            </div>
            
            <div class="mb-3">
                <label class="fw-bold">Confirm New Password *</label>
                <div style="position:relative">
                    <input type="password" 
                           class="form-control form-control-lg" 
                           id="confirmPassword"
                           name="confirm_password" 
                           required
                           minlength="8">
                    <i class="fas fa-eye" 
                       onclick="togglePassword('confirmPassword')" 
                       style="position:absolute;right:10px;top:50%;transform:translateY(-50%);cursor:pointer"></i>
                </div>
            </div>
            
            <div class="d-grid mb-3">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-check"></i> Change Password
                </button>
            </div>
        </form>
        
        <?php endif; ?>
    </div>
</div>

</div>
</div>
</div>

<script>
function togglePassword(id) {
    const input = document.getElementById(id);
    const icon = input.nextElementSibling;
    
    if(input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}
</script>

<?php require_once __DIR__.'/../includes/footer.php'; ?>
