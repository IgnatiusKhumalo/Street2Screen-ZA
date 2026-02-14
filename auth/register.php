<?php
/**
 * ============================================
 * USER REGISTRATION PAGE
 * ============================================
 * Project: Street2Screen ZA
 * Purpose: Allow new users to create accounts
 * Author: Ignatius Mayibongwe Khumalo
 * Date: February 2026
 * ============================================
 * 
 * FEATURES:
 * - Email validation
 * - Password strength requirements
 * - CSRF protection
 * - XSS prevention
 * - Duplicate email check
 * - Secure password hashing (bcrypt)
 * - Flash messages for feedback
 * - Responsive design
 * 
 * ============================================
 */

// Set page title for header
$pageTitle = 'Register';

// Include common header (loads all required files)
require_once __DIR__ . '/../includes/header.php';

// Initialize error array
$errors = [];
$formData = [];

// ============================================
// PROCESS FORM SUBMISSION
// ============================================
if (is_post_request()) {
    
    // ----------------------------------------
    // STEP 1: CSRF Token Validation
    // ----------------------------------------
    // Protect against Cross-Site Request Forgery attacks
    if (!Security::validateCSRFToken(get_post('csrf_token'))) {
        redirect_with_error('/auth/register.php', 'Invalid security token. Please try again.');
    }
    
    // ----------------------------------------
    // STEP 2: Get and Sanitize Form Data
    // ----------------------------------------
    $formData = [
        'full_name' => Security::sanitizeString(get_post('full_name')),
        'email' => Security::sanitizeEmail(get_post('email')),
        'phone' => Security::sanitizePhone(get_post('phone')),
        'password' => get_post('password'),
        'password_confirm' => get_post('password_confirm'),
        'user_type' => get_post('user_type', 'buyer'), // Default to buyer
        'location' => Security::sanitizeString(get_post('location'))
    ];
    
    // ----------------------------------------
    // STEP 3: Validate All Fields
    // ----------------------------------------
    
    // Validate full name
    if (empty($formData['full_name'])) {
        $errors[] = 'Full name is required';
    } elseif (strlen($formData['full_name']) < 3) {
        $errors[] = 'Full name must be at least 3 characters';
    }
    
    // Validate email
    if (empty($formData['email'])) {
        $errors[] = 'Email is required';
    } elseif (!Security::validateEmail($formData['email'])) {
        $errors[] = 'Invalid email format';
    } else {
        // Check if email already exists in database
        $db = new Database();
        $db->query("SELECT user_id FROM users WHERE email = :email");
        $db->bind(':email', $formData['email']);
        $existingUser = $db->fetch();
        
        if ($existingUser) {
            $errors[] = 'Email already registered. Please login instead.';
        }
    }
    
    // Validate phone
    if (empty($formData['phone'])) {
        $errors[] = 'Phone number is required';
    } elseif (!Security::validatePhone($formData['phone'])) {
        $errors[] = 'Invalid phone number. Use format: 0821234567 or +27821234567';
    }
    
    // Validate location
    if (empty($formData['location'])) {
        $errors[] = 'Location is required';
    }
    
    // Validate password
    if (empty($formData['password'])) {
        $errors[] = 'Password is required';
    } else {
        $passwordCheck = Security::validatePassword($formData['password']);
        if (!$passwordCheck['valid']) {
            $errors = array_merge($errors, $passwordCheck['errors']);
        }
    }
    
    // Validate password confirmation
    if ($formData['password'] !== $formData['password_confirm']) {
        $errors[] = 'Passwords do not match';
    }
    
    // ----------------------------------------
    // STEP 4: Create User Account if No Errors
    // ----------------------------------------
    if (empty($errors)) {
        try {
            $db = new Database();
            
            // Hash password securely using bcrypt
            $passwordHash = Security::hashPassword($formData['password']);
            
            // Generate email verification token
            $verificationToken = Security::generateToken();
            
            // Insert user into database
            $db->query("
                INSERT INTO users (
                    full_name, 
                    email, 
                    phone, 
                    password_hash, 
                    user_type, 
                    location,
                    verification_token,
                    email_verified,
                    account_status,
                    created_at
                ) VALUES (
                    :full_name,
                    :email,
                    :phone,
                    :password_hash,
                    :user_type,
                    :location,
                    :verification_token,
                    0,
                    'active',
                    NOW()
                )
            ");
            
            // Bind all parameters safely
            $db->bind(':full_name', $formData['full_name']);
            $db->bind(':email', $formData['email']);
            $db->bind(':phone', $formData['phone']);
            $db->bind(':password_hash', $passwordHash);
            $db->bind(':user_type', $formData['user_type']);
            $db->bind(':location', $formData['location']);
            $db->bind(':verification_token', $verificationToken);
            
            // Execute the insert
            if ($db->execute()) {
                // Get the new user ID
                $newUserId = $db->lastInsertId();
                
                // TODO: Send verification email using EmailHelper
                // This would be implemented when email functionality is needed
                
                // Success! Redirect to login with success message
                redirect_with_success(
                    '/auth/login.php', 
                    'Registration successful! Please login to continue.'
                );
            } else {
                $errors[] = 'Registration failed. Please try again.';
            }
            
        } catch (Exception $e) {
            // Log error (in production, don't show to user)
            error_log("Registration error: " . $e->getMessage());
            $errors[] = 'An error occurred during registration. Please try again.';
        }
    }
}

// Generate fresh CSRF token for the form
$csrfToken = Security::generateCSRFToken();
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            
            <!-- Registration Card -->
            <div class="card shadow-lg border-0">
                <div class="card-body p-5">
                    
                    <!-- Header -->
                    <div class="text-center mb-4">
                        <h2 class="fw-bold" style="color: var(--primary-blue);">Create Account</h2>
                        <p class="text-muted">Join Street2Screen ZA Today</p>
                    </div>
                    
                    <!-- Display Errors if Any -->
                    <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <strong>Please fix the following errors:</strong>
                        <ul class="mb-0 mt-2">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo Security::clean($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Registration Form -->
                    <form method="POST" action="" id="registerForm">
                        
                        <!-- CSRF Token (Hidden Field for Security) -->
                        <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                        
                        <!-- Full Name -->
                        <div class="mb-3">
                            <label for="full_name" class="form-label">
                                <i class="fas fa-user"></i> Full Name *
                            </label>
                            <input type="text" 
                                   class="form-control" 
                                   id="full_name" 
                                   name="full_name" 
                                   value="<?php echo Security::clean($formData['full_name'] ?? ''); ?>"
                                   required>
                        </div>
                        
                        <!-- Email -->
                        <div class="mb-3">
                            <label for="email" class="form-label">
                                <i class="fas fa-envelope"></i> Email Address *
                            </label>
                            <input type="email" 
                                   class="form-control" 
                                   id="email" 
                                   name="email" 
                                   value="<?php echo Security::clean($formData['email'] ?? ''); ?>"
                                   required>
                        </div>
                        
                        <!-- Phone -->
                        <div class="mb-3">
                            <label for="phone" class="form-label">
                                <i class="fas fa-phone"></i> Phone Number *
                            </label>
                            <input type="tel" 
                                   class="form-control" 
                                   id="phone" 
                                   name="phone" 
                                   value="<?php echo Security::clean($formData['phone'] ?? ''); ?>"
                                   placeholder="0821234567"
                                   required>
                            <small class="text-muted">Format: 0821234567 or +27821234567</small>
                        </div>
                        
                        <!-- Location -->
                        <div class="mb-3">
                            <label for="location" class="form-label">
                                <i class="fas fa-map-marker-alt"></i> Location *
                            </label>
                            <input type="text" 
                                   class="form-control" 
                                   id="location" 
                                   name="location" 
                                   value="<?php echo Security::clean($formData['location'] ?? ''); ?>"
                                   placeholder="e.g., Soweto, Johannesburg"
                                   required>
                        </div>
                        
                        <!-- User Type -->
                        <div class="mb-3">
                            <label for="user_type" class="form-label">
                                <i class="fas fa-user-tag"></i> I want to *
                            </label>
                            <select class="form-select" id="user_type" name="user_type" required>
                                <option value="buyer" <?php echo ($formData['user_type'] ?? 'buyer') === 'buyer' ? 'selected' : ''; ?>>
                                    Buy Products
                                </option>
                                <option value="seller" <?php echo ($formData['user_type'] ?? '') === 'seller' ? 'selected' : ''; ?>>
                                    Sell Products
                                </option>
                                <option value="both" <?php echo ($formData['user_type'] ?? '') === 'both' ? 'selected' : ''; ?>>
                                    Both (Buy & Sell)
                                </option>
                            </select>
                        </div>
                        
                        <!-- Password -->
                        <div class="mb-3">
                            <label for="password" class="form-label">
                                <i class="fas fa-lock"></i> Password *
                            </label>
                            <input type="password" 
                                   class="form-control" 
                                   id="password" 
                                   name="password" 
                                   required>
                            <small class="text-muted">
                                Min 8 characters, 1 uppercase, 1 lowercase, 1 number, 1 special character
                            </small>
                        </div>
                        
                        <!-- Confirm Password -->
                        <div class="mb-4">
                            <label for="password_confirm" class="form-label">
                                <i class="fas fa-lock"></i> Confirm Password *
                            </label>
                            <input type="password" 
                                   class="form-control" 
                                   id="password_confirm" 
                                   name="password_confirm" 
                                   required>
                        </div>
                        
                        <!-- Submit Button -->
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary-custom btn-lg">
                                <i class="fas fa-user-plus"></i> Create Account
                            </button>
                        </div>
                        
                    </form>
                    
                    <!-- Login Link -->
                    <div class="text-center mt-4">
                        <p class="text-muted">
                            Already have an account? 
                            <a href="<?php echo APP_URL; ?>/auth/login.php" style="color: var(--primary-yellow); font-weight: 600;">
                                Login here
                            </a>
                        </p>
                    </div>
                    
                </div>
            </div>
            
        </div>
    </div>
</div>

<?php
// Include common footer
require_once __DIR__ . '/../includes/footer.php';
?>
