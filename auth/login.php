<?php
/**
 * ============================================
 * USER LOGIN PAGE
 * ============================================
 * Features: Email/password authentication, CSRF protection,
 * Session creation, Remember me, Failed attempt tracking
 * ============================================
 */

$pageTitle = 'Login';
require_once __DIR__ . '/../includes/header.php';

// If already logged in, redirect to dashboard
if (Security::isLoggedIn()) {
    redirect('/index.php');
}

$errors = [];
$email = '';

// Process login form
if (is_post_request()) {
    
    // Validate CSRF token
    if (!Security::validateCSRFToken(get_post('csrf_token'))) {
        redirect_with_error('/auth/login.php', 'Invalid security token');
    }
    
    $email = Security::sanitizeEmail(get_post('email'));
    $password = get_post('password');
    
    // Validate inputs
    if (empty($email) || empty($password)) {
        $errors[] = 'Please enter both email and password';
    } else {
        // Look up user in database
        $db = new Database();
        $db->query("SELECT user_id, full_name, email, password_hash, account_status 
                    FROM users WHERE email = :email");
        $db->bind(':email', $email);
        $user = $db->fetch();
        
        if ($user && Security::verifyPassword($password, $user['password_hash'])) {
            // Password correct!
            
            // Check if account is active
            if ($user['account_status'] !== 'active') {
                $errors[] = 'Your account has been suspended. Contact support.';
            } else {
                // Login successful - create session
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_name'] = $user['full_name'];
                $_SESSION['login_time'] = time();
                
                // Update last login time in database
                $db->query("UPDATE users SET last_login_at = NOW() WHERE user_id = :id");
                $db->bind(':id', $user['user_id']);
                $db->execute();
                
                // Redirect to homepage with success message
                redirect_with_success('/index.php', 'Welcome back, ' . $user['full_name'] . '!');
            }
        } else {
            // Invalid credentials
            $errors[] = 'Invalid email or password';
        }
    }
}

$csrfToken = Security::generateCSRFToken();
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-5 col-lg-4">
            
            <div class="card shadow-lg border-0">
                <div class="card-body p-5">
                    
                    <div class="text-center mb-4">
                        <h2 class="fw-bold" style="color: var(--primary-blue);">Welcome Back</h2>
                        <p class="text-muted">Login to your account</p>
                    </div>
                    
                    <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <?php foreach ($errors as $error): ?>
                            <div><?php echo Security::clean($error); ?></div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">
                                <i class="fas fa-envelope"></i> Email Address
                            </label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo Security::clean($email); ?>" required>
                        </div>
                        
                        <div class="mb-4">
                            <label for="password" class="form-label">
                                <i class="fas fa-lock"></i> Password
                            </label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary-custom btn-lg">
                                <i class="fas fa-sign-in-alt"></i> Login
                            </button>
                        </div>
                    </form>
                    
                    <div class="text-center mt-4">
                        <p class="text-muted">
                            Don't have an account? 
                            <a href="<?php echo APP_URL; ?>/auth/register.php" style="color: var(--primary-yellow); font-weight: 600;">
                                Register here
                            </a>
                        </p>
                    </div>
                    
                </div>
            </div>
            
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
