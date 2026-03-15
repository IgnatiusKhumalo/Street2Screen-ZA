<?php
date_default_timezone_set('Africa/Johannesburg');

$pageTitle = 'Reset Password';
require_once __DIR__.'/../includes/header.php';

// FIX: Define APP_EMAIL if not defined
if(!defined('APP_EMAIL')) {
    define('APP_EMAIL', 'support@street2screen.co.za');
}

$errors = [];
$success = false;
$tokenValid = false;
$user = null;

$token = get_get('token');

if(empty($token)) {
    $errors[] = 'Invalid or missing reset token';
    $errors[] = 'Please request a new password reset';
} else {
    $db = new Database();
    
    // Check if token is valid
    $db->query("SELECT pr.*, u.full_name, u.email, u.user_id, u.temp_password, u.temp_password_expires
               FROM password_resets pr
               JOIN users u ON pr.email = u.email
               WHERE pr.reset_token = :token 
               AND pr.used = 0 
               AND pr.token_expiry > NOW()");
    $db->bind(':token', $token);
    $resetRequest = $db->fetch();
    
    if(!$resetRequest) {
        $errors[] = 'This reset link has expired or has already been used';
        $errors[] = 'Reset links expire after 1 hour';
        $errors[] = 'Please request a new password reset';
    } else {
        $tokenValid = true;
        $user = $resetRequest;
        
        // Handle form submission
        if(is_post_request()) {
            if(!Security::validateCSRFToken(get_post('csrf_token'))) {
                $errors[] = 'Invalid security token. Please try again.';
            } else {
                $tempPassword = get_post('temp_password');
                $newPassword = get_post('new_password');
                $confirmPassword = get_post('confirm_password');
                
                // Validation
                if(empty($tempPassword)) {
                    $errors[] = 'Temporary password is required';
                }
                if(empty($newPassword)) {
                    $errors[] = 'New password is required';
                }
                if(empty($confirmPassword)) {
                    $errors[] = 'Password confirmation is required';
                }
                
                // Verify temporary password
                if(!empty($tempPassword) && !empty($user['temp_password'])) {
                    if(!Security::verifyPassword($tempPassword, $user['temp_password'])) {
                        $errors[] = 'Incorrect temporary password';
                    }
                    
                    // Check if temp password expired
                    if(strtotime($user['temp_password_expires']) < time()) {
                        $errors[] = 'Temporary password has expired';
                        $errors[] = 'Please request a new password reset';
                    }
                } else {
                    $errors[] = 'Temporary password not found. Please request a new reset.';
                }
                
                // Password strength validation
                if(!empty($newPassword)) {
                    if(strlen($newPassword) < 8) {
                        $errors[] = 'Password must be at least 8 characters';
                    }
                    if(!preg_match('/[A-Z]/', $newPassword)) {
                        $errors[] = 'Password must contain at least one uppercase letter';
                    }
                    if(!preg_match('/[0-9]/', $newPassword)) {
                        $errors[] = 'Password must contain at least one number';
                    }
                    if(!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $newPassword)) {
                        $errors[] = 'Password must contain at least one special character';
                    }
                }
                
                // Passwords match
                if(!empty($newPassword) && !empty($confirmPassword)) {
                    if($newPassword !== $confirmPassword) {
                        $errors[] = 'Passwords do not match';
                    }
                }
                
                // If no errors, update password
                if(empty($errors)) {
                    $passwordHash = Security::hashPassword($newPassword);
                    
                    // Update password and clear temp password
                    $db->query("UPDATE users 
                               SET password_hash = :hash,
                                   temp_password = NULL,
                                   temp_password_expires = NULL,
                                   updated_at = NOW()
                               WHERE user_id = :uid");
                    $db->bind(':hash', $passwordHash);
                    $db->bind(':uid', $user['user_id']);
                    $db->execute();
                    
                    // Mark token as used
                    $db->query("UPDATE password_resets SET used = 1 WHERE reset_id = :rid");
                    $db->bind(':rid', $user['reset_id']);
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
                }
            }
        }
    }
}

$csrfToken = Security::generateCSRFToken();
?>

<!-- Password Strength CSS + Permanent Messages -->
<style>
.password-strength {
    height: 5px;
    margin-top: 5px;
    border-radius: 3px;
    transition: all 0.3s;
}
.strength-weak { background: #dc3545; width: 33%; }
.strength-medium { background: #ffc107; width: 66%; }
.strength-strong { background: #28a745; width: 100%; }

.requirement {
    font-size: 14px;
    padding: 5px 0;
}
.requirement.met {
    color: #28a745;
}
.requirement.unmet {
    color: #dc3545;
}
.requirement i {
    width: 20px;
}

/* Keep all messages permanent */
.permanent-message {
    animation: none !important;
    transition: none !important;
    display: block !important;
    visibility: visible !important;
    opacity: 1 !important;
}
</style>

<div class="container my-5">
<div class="row justify-content-center">
<div class="col-md-7">

<div class="card shadow">
    <div class="card-header <?php echo $success ? 'bg-success' : ($tokenValid ? 'bg-primary' : 'bg-danger'); ?> text-white text-center py-3">
        <h4><i class="fas fa-lock"></i> Reset Password</h4>
    </div>
    <div class="card-body p-4">
        
        <?php if($success): ?>
        <!-- SUCCESS -->
        <div class="alert alert-success text-center p-4 permanent-message">
            <i class="fas fa-check-circle fa-4x mb-3 d-block"></i>
            <h4>Password Changed Successfully!</h4>
            <p class="mb-0">Your password has been updated. You can now login with your new password.</p>
        </div>
        <div class="d-grid">
            <a href="<?php echo APP_URL; ?>/auth/login.php" class="btn btn-success btn-lg">
                <i class="fas fa-sign-in-alt"></i> Go to Login
            </a>
        </div>
        
        <?php elseif(!$tokenValid): ?>
        <!-- ERROR -->
        <div class="alert alert-danger p-4 permanent-message">
            <i class="fas fa-exclamation-triangle fa-4x mb-3 d-block text-center"></i>
            <h5 class="text-center">Invalid or Expired Reset Link</h5>
            <?php foreach($errors as $error): ?>
                <p class="mb-1 text-center"><?php echo Security::clean($error); ?></p>
            <?php endforeach; ?>
        </div>
        
        <div class="alert alert-info permanent-message">
            <h6><i class="fas fa-info-circle"></i> Why did this happen?</h6>
            <ul class="mb-0">
                <li>The link expired (1 hour time limit)</li>
                <li>The link was already used</li>
                <li>The link is invalid</li>
            </ul>
        </div>
        
        <div class="d-grid">
            <a href="<?php echo APP_URL; ?>/auth/forgot-password.php" class="btn btn-warning btn-lg">
                <i class="fas fa-redo"></i> Request New Reset Link
            </a>
        </div>
        
        <?php else: ?>
        <!-- FORM -->
        
        <?php if(!empty($errors)): ?>
        <div class="alert alert-danger permanent-message">
            <h6><i class="fas fa-exclamation-triangle"></i> Please fix these errors:</h6>
            <ul class="mb-0">
            <?php foreach($errors as $error): ?>
                <li><?php echo Security::clean($error); ?></li>
            <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
        
        <div class="alert alert-success permanent-message">
            <i class="fas fa-check-circle"></i>
            <p class="mb-0">Hi <strong><?php echo Security::clean($user['full_name']); ?></strong>, your reset link is valid. Use your temporary password to set a new one.</p>
        </div>
        
        <form method="POST" id="resetForm">
            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
            
            <!-- Temporary Password -->
            <div class="mb-3">
                <label class="fw-bold">Temporary Password *</label>
                <div style="position:relative">
                    <input type="password" 
                           class="form-control form-control-lg" 
                           id="tempPassword"
                           name="temp_password" 
                           placeholder="Enter temporary password from email"
                           required
                           autofocus>
                    <i class="fas fa-eye" 
                       onclick="togglePassword('tempPassword')" 
                       style="position:absolute;right:15px;top:50%;transform:translateY(-50%);cursor:pointer;font-size:18px"></i>
                </div>
                <small class="text-muted">Check your email for the 8-character temporary password</small>
            </div>
            
            <hr class="my-4">
            
            <!-- New Password -->
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
                       style="position:absolute;right:15px;top:50%;transform:translateY(-50%);cursor:pointer;font-size:18px"></i>
                </div>
                <div class="password-strength" id="strengthBar"></div>
                <div id="strengthText" class="small text-muted mt-1"></div>
            </div>
            
            <!-- Password Requirements -->
            <div class="card bg-light mb-3">
                <div class="card-body p-3">
                    <h6 class="mb-2">Password Requirements:</h6>
                    <div class="requirement unmet" id="req-length">
                        <i class="fas fa-times-circle"></i> At least 8 characters
                    </div>
                    <div class="requirement unmet" id="req-uppercase">
                        <i class="fas fa-times-circle"></i> One uppercase letter
                    </div>
                    <div class="requirement unmet" id="req-number">
                        <i class="fas fa-times-circle"></i> One number
                    </div>
                    <div class="requirement unmet" id="req-special">
                        <i class="fas fa-times-circle"></i> One special character (!@#$%^&*)
                    </div>
                </div>
            </div>
            
            <!-- Confirm Password -->
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
                       style="position:absolute;right:15px;top:50%;transform:translateY(-50%);cursor:pointer;font-size:18px"></i>
                </div>
                <div id="matchMessage" class="small mt-1"></div>
            </div>
            
            <div class="d-grid">
                <button type="submit" class="btn btn-primary btn-lg" id="submitBtn" disabled>
                    <i class="fas fa-check"></i> Reset Password
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

// Password validation
const newPasswordInput = document.getElementById('newPassword');
const confirmPasswordInput = document.getElementById('confirmPassword');
const strengthBar = document.getElementById('strengthBar');
const strengthText = document.getElementById('strengthText');
const submitBtn = document.getElementById('submitBtn');

const requirements = {
    length: /^.{8,}$/,
    uppercase: /[A-Z]/,
    number: /[0-9]/,
    special: /[!@#$%^&*(),.?":{}|<>]/
};

function checkRequirements() {
    const password = newPasswordInput.value;
    let metCount = 0;
    
    // Check each requirement
    for(let req in requirements) {
        const met = requirements[req].test(password);
        const element = document.getElementById('req-' + req);
        
        if(met) {
            element.classList.remove('unmet');
            element.classList.add('met');
            element.querySelector('i').className = 'fas fa-check-circle';
            metCount++;
        } else {
            element.classList.remove('met');
            element.classList.add('unmet');
            element.querySelector('i').className = 'fas fa-times-circle';
        }
    }
    
    // Update strength bar
    if(metCount === 0) {
        strengthBar.className = 'password-strength';
        strengthText.textContent = '';
    } else if(metCount <= 2) {
        strengthBar.className = 'password-strength strength-weak';
        strengthText.textContent = 'Weak password';
        strengthText.style.color = '#dc3545';
    } else if(metCount === 3) {
        strengthBar.className = 'password-strength strength-medium';
        strengthText.textContent = 'Medium password';
        strengthText.style.color = '#ffc107';
    } else {
        strengthBar.className = 'password-strength strength-strong';
        strengthText.textContent = 'Strong password';
        strengthText.style.color = '#28a745';
    }
    
    return metCount === 4;
}

function checkPasswordMatch() {
    const password = newPasswordInput.value;
    const confirm = confirmPasswordInput.value;
    const matchMsg = document.getElementById('matchMessage');
    
    if(confirm.length === 0) {
        matchMsg.textContent = '';
        return false;
    }
    
    if(password === confirm) {
        matchMsg.textContent = '✓ Passwords match';
        matchMsg.style.color = '#28a745';
        return true;
    } else {
        matchMsg.textContent = '✗ Passwords do not match';
        matchMsg.style.color = '#dc3545';
        return false;
    }
}

function validateForm() {
    const allRequirementsMet = checkRequirements();
    const passwordsMatch = checkPasswordMatch();
    const tempPassword = document.getElementById('tempPassword').value;
    
    if(allRequirementsMet && passwordsMatch && tempPassword.length > 0) {
        submitBtn.disabled = false;
        submitBtn.classList.remove('btn-secondary');
        submitBtn.classList.add('btn-primary');
    } else {
        submitBtn.disabled = true;
        submitBtn.classList.remove('btn-primary');
        submitBtn.classList.add('btn-secondary');
    }
}

newPasswordInput.addEventListener('input', validateForm);
confirmPasswordInput.addEventListener('input', validateForm);
document.getElementById('tempPassword').addEventListener('input', validateForm);

// Prevent any auto-dismissal of alerts
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.alert').forEach(function(alert) {
        alert.classList.add('permanent-message');
        const closeBtn = alert.querySelector('.btn-close');
        if(closeBtn) closeBtn.remove();
    });
});
</script>

<?php require_once __DIR__.'/../includes/footer.php'; ?>
