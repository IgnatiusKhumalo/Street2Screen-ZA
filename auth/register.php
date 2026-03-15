<?php
/**
 * ============================================
 * REGISTRATION PAGE - 5 USER TYPE CARDS
 * ============================================
 * PHASE 1 UPDATES:
 * - Citizenship (SA Citizen / Foreign National)
 * - SA ID Number validation (13 digits + checksum)
 * - Passport Number for foreign nationals
 * - Country selection (all countries grouped by continent)
 * - Business Age (0-11 months / 12+ months)
 * - Business Documents (CIPC, SARS, VAT, Bank) for 12+ months
 * - Updated ID Document requirements (certified copy, max 3 months)
 * - Updated Proof of Address options (SAPS affidavit, councillor letter)
 * - Refund Policy checkbox (compulsory)
 * - Marketing consent checkbox (optional)
 * ============================================
 */

$pageTitle = 'Register';
require_once __DIR__.'/../includes/header.php';

// If already logged in, redirect to dashboard
if (Security::isLoggedIn()) {
    redirect(APP_URL . '/user/dashboard.php');
}

$db = new Database();
$errors = [];
$success = '';

// FORM DATA RETENTION - Preserve all form values when errors occur
$formData = [
    'full_name' => htmlspecialchars(get_post('full_name', '')),
    'email' => htmlspecialchars(get_post('email', '')),
    'email_confirm' => htmlspecialchars(get_post('email_confirm', '')),
    'phone' => htmlspecialchars(str_replace('+27', '', get_post('phone', ''))),
    'address' => htmlspecialchars(get_post('address', '')),
    'township' => htmlspecialchars(get_post('township', '')),
    'city' => htmlspecialchars(get_post('city', '')),
    'province' => htmlspecialchars(get_post('province', '')),
    'postal_code' => htmlspecialchars(get_post('postal_code', '')),
    'citizenship' => htmlspecialchars(get_post('citizenship', '')),
    'id_number' => htmlspecialchars(get_post('id_number', '')),
    'passport_number' => htmlspecialchars(get_post('passport_number', '')),
    'country' => htmlspecialchars(get_post('country', '')),
    'business_age' => htmlspecialchars(get_post('business_age', '')),
    'cipc_number' => htmlspecialchars(get_post('cipc_number', '')),
    'sars_number' => htmlspecialchars(get_post('sars_number', '')),
    'vat_number' => htmlspecialchars(get_post('vat_number', '')),
    'user_type' => htmlspecialchars(get_post('user_type', ''))
];

if (is_post_request()) {
    if (!Security::validateCSRFToken(get_post('csrf_token'))) {
        $errors[] = 'Invalid security token. Please try again.';
    } else {
        // Get form data
        $userType = get_post('user_type');
        $fullName = Security::sanitizeString(get_post('full_name'));
        $email = Security::sanitizeEmail(get_post('email'));
        $emailConfirm = Security::sanitizeEmail(get_post('email_confirm'));
        $phone = '+27' . Security::sanitizeString(get_post('phone'));
        $password = get_post('password');
        $passwordConfirm = get_post('password_confirm');
        $address = Security::sanitizeString(get_post('address'));
        $township = Security::sanitizeString(get_post('township'));
        $city = Security::sanitizeString(get_post('city'));
        $province = get_post('province');
        $postalCode = Security::sanitizeString(get_post('postal_code'));
        
        // NEW PHASE 1 FIELDS
        $citizenship = get_post('citizenship'); // 'citizen' or 'foreign'
        $idNumber = Security::sanitizeString(get_post('id_number', ''));
        $passportNumber = Security::sanitizeString(get_post('passport_number', ''));
        $country = Security::sanitizeString(get_post('country', ''));
        $businessAge = get_post('business_age', ''); // '0-11' or '12+'
        $cipcNumber = Security::sanitizeString(get_post('cipc_number', ''));
        $sarsNumber = Security::sanitizeString(get_post('sars_number', ''));
        $vatNumber = Security::sanitizeString(get_post('vat_number', ''));
        $marketingConsent = get_post('marketing_consent') === '1' ? 1 : 0;
        
        // VALIDATION
        if (empty($userType) || !in_array($userType, ['buyer', 'seller', 'both', 'moderator', 'admin'])) {
            $errors[] = 'Please select an account type';
        }
        if (empty($fullName)) {
            $errors[] = 'Full name is required';
        }
        if (empty($email)) {
            $errors[] = 'Email is required';
        }
        if (!Security::validateEmail($email)) {
            $errors[] = 'Email must be valid and contain @';
        }
        if ($email !== $emailConfirm) {
            $errors[] = 'Email addresses do not match';
        }
        if (!Security::validatePhone($phone)) {
            $errors[] = 'Valid South African phone number required';
        }
        if (!Security::validatePassword($password)) {
            $errors[] = 'Password must be 8+ characters with uppercase, lowercase, number, and special character';
        }
        if ($password !== $passwordConfirm) {
            $errors[] = 'Passwords do not match';
        }
        
        // NEW: Citizenship validation (required for seller/both/moderator/admin)
        if (in_array($userType, ['seller', 'both', 'moderator', 'admin'])) {
            if (empty($citizenship) || !in_array($citizenship, ['citizen', 'foreign'])) {
                $errors[] = 'Citizenship status is required';
            }
            
            if ($citizenship === 'citizen') {
                // Validate SA ID Number
                if (empty($idNumber)) {
                    $errors[] = 'SA ID Number is required for South African citizens';
                } elseif (strlen($idNumber) !== 13 || !ctype_digit($idNumber)) {
                    $errors[] = 'SA ID Number must be exactly 13 digits';
                } else {
                    // Validate ID number format (YYMMDD)
                    $year = substr($idNumber, 0, 2);
                    $month = substr($idNumber, 2, 2);
                    $day = substr($idNumber, 4, 2);
                    
                    if ($month < 1 || $month > 12) {
                        $errors[] = 'Invalid SA ID Number: month must be 01-12';
                    }
                    if ($day < 1 || $day > 31) {
                        $errors[] = 'Invalid SA ID Number: day must be 01-31';
                    }
                    
                    // Validate checksum (Luhn algorithm)
                    $checkDigit = (int)substr($idNumber, 12, 1);
                    $sum = 0;
                    for ($i = 0; $i < 12; $i++) {
                        $digit = (int)$idNumber[$i];
                        if ($i % 2 === 1) {
                            $digit *= 2;
                            if ($digit > 9) $digit -= 9;
                        }
                        $sum += $digit;
                    }
                    $calculatedCheck = (10 - ($sum % 10)) % 10;
                    if ($checkDigit !== $calculatedCheck) {
                        $errors[] = 'Invalid SA ID Number: checksum validation failed';
                    }
                }
            } elseif ($citizenship === 'foreign') {
                // Validate passport number and country
                if (empty($passportNumber)) {
                    $errors[] = 'Valid passport number is required for foreign nationals';
                }
                if (empty($country)) {
                    $errors[] = 'Country is required for foreign nationals';
                }
            }
            
            // NEW: Business age validation (seller/both only)
            if (in_array($userType, ['seller', 'both'])) {
                if (empty($businessAge) || !in_array($businessAge, ['0-11', '12+'])) {
                    $errors[] = 'Business age is required';
                }
                
                // Validate business documents for 12+ months businesses
                if ($businessAge === '12+') {
                    if (empty($cipcNumber)) {
                        $errors[] = 'CIPC number is required for businesses 12+ months old';
                    }
                    if (empty($sarsNumber)) {
                        $errors[] = 'SARS number is required for businesses 12+ months old';
                    }
                    if (empty($vatNumber)) {
                        $errors[] = 'VAT number is required for businesses 12+ months old';
                    }
                }
            }
        }
        
        // Check if email already exists
        if (empty($errors)) {
            $db->query("SELECT user_id FROM users WHERE email = :email");
            $db->bind(':email', $email);
            if ($db->fetch()) {
                $errors[] = 'Email address is already registered';
            }
        }
        
        // Check if ID number already exists (for SA citizens)
        if (empty($errors) && $citizenship === 'citizen' && !empty($idNumber)) {
            $db->query("SELECT user_id FROM users WHERE id_number = :idnum");
            $db->bind(':idnum', $idNumber);
            if ($db->fetch()) {
                $errors[] = 'This ID number is already registered';
            }
        }
        
        // Handle document uploads for seller/both/moderator/admin
        $idDocumentPath = null;
        $proofOfAddressPath = null;
        $cipcDocumentPath = null;
        $sarsVatCertificatePath = null;
        $bankStatementPath = null;
        
        if (in_array($userType, ['seller', 'both', 'moderator', 'admin'])) {
            // ID Document upload (required)
            if (isset($_FILES['id_document']) && $_FILES['id_document']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../uploads/documents/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                $result = upload_file($_FILES['id_document'], $uploadDir, ALLOWED_DOCUMENT_TYPES, MAX_DOCUMENT_SIZE);
                if ($result['success']) {
                    $idDocumentPath = str_replace(__DIR__ . '/../', '', $result['path']);
                } else {
                    $errors[] = 'ID document upload failed: ' . $result['error'];
                }
            } else {
                $errors[] = 'ID document is required (must be certified copy, max 3 months old)';
            }
            
            // Proof of Address upload (required)
            if (isset($_FILES['proof_of_address']) && $_FILES['proof_of_address']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../uploads/documents/';
                $result = upload_file($_FILES['proof_of_address'], $uploadDir, ALLOWED_DOCUMENT_TYPES, MAX_DOCUMENT_SIZE);
                if ($result['success']) {
                    $proofOfAddressPath = str_replace(__DIR__ . '/../', '', $result['path']);
                } else {
                    $errors[] = 'Proof of address upload failed: ' . $result['error'];
                }
            } else {
                $errors[] = 'Proof of address is required (utility bill, bank statement, SAPS affidavit, or councillor letter - max 3 months old)';
            }
            
            // NEW: Business documents (only for 12+ months businesses)
            if (in_array($userType, ['seller', 'both']) && $businessAge === '12+') {
                // CIPC Document
                if (isset($_FILES['cipc_document']) && $_FILES['cipc_document']['error'] === UPLOAD_ERR_OK) {
                    $uploadDir = __DIR__ . '/../uploads/documents/';
                    $result = upload_file($_FILES['cipc_document'], $uploadDir, ALLOWED_DOCUMENT_TYPES, MAX_DOCUMENT_SIZE);
                    if ($result['success']) {
                        $cipcDocumentPath = str_replace(__DIR__ . '/../', '', $result['path']);
                    } else {
                        $errors[] = 'CIPC document upload failed: ' . $result['error'];
                    }
                } else {
                    $errors[] = 'CIPC registration document is required for businesses 12+ months old';
                }
                
                // SARS/VAT Certificate
                if (isset($_FILES['sars_vat_certificate']) && $_FILES['sars_vat_certificate']['error'] === UPLOAD_ERR_OK) {
                    $uploadDir = __DIR__ . '/../uploads/documents/';
                    $result = upload_file($_FILES['sars_vat_certificate'], $uploadDir, ALLOWED_DOCUMENT_TYPES, MAX_DOCUMENT_SIZE);
                    if ($result['success']) {
                        $sarsVatCertificatePath = str_replace(__DIR__ . '/../', '', $result['path']);
                    } else {
                        $errors[] = 'SARS/VAT certificate upload failed: ' . $result['error'];
                    }
                } else {
                    $errors[] = 'SARS/VAT certificate is required for businesses 12+ months old';
                }
                
                // Business Bank Statement
                if (isset($_FILES['bank_statement']) && $_FILES['bank_statement']['error'] === UPLOAD_ERR_OK) {
                    $uploadDir = __DIR__ . '/../uploads/documents/';
                    $result = upload_file($_FILES['bank_statement'], $uploadDir, ALLOWED_DOCUMENT_TYPES, MAX_DOCUMENT_SIZE);
                    if ($result['success']) {
                        $bankStatementPath = str_replace(__DIR__ . '/../', '', $result['path']);
                    } else {
                        $errors[] = 'Business bank statement upload failed: ' . $result['error'];
                    }
                } else {
                    $errors[] = 'Business bank statement is required for businesses 12+ months old';
                }
            }
        }
        
        // If no errors, create user
        if (empty($errors)) {
            $passwordHash = password_hash($password, PASSWORD_ALGO, ['cost' => PASSWORD_COST]);
            $verificationToken = bin2hex(random_bytes(32));
            
            // Determine account status
            // Buyers get instant access, others need approval
            if ($userType === 'buyer') {
                $accountStatus = 'active';
                $suspensionReason = null;
            } else {
                $accountStatus = 'suspended';
                $suspensionReason = ucfirst($userType) . ' access pending admin approval';
            }
            
            // Insert user with NEW PHASE 1 fields
            $db->query("INSERT INTO users (
                user_type, full_name, email, phone, password_hash,
                address, township, city, province, postal_code,
                citizenship, id_number, passport_number, country,
                business_age, cipc_number, sars_number, vat_number,
                marketing_consent,
                account_status, suspension_reason, email_verified,
                email_verification_token, created_at
            ) VALUES (
                :type, :name, :email, :phone, :hash,
                :addr, :town, :city, :prov, :postal,
                :citizenship, :idnum, :passport, :country,
                :bizage, :cipc, :sars, :vat,
                :marketing,
                :status, :reason, 0, :token, NOW()
            )");
            
            $db->bind(':type', $userType);
            $db->bind(':name', $fullName);
            $db->bind(':email', $email);
            $db->bind(':phone', $phone);
            $db->bind(':hash', $passwordHash);
            $db->bind(':addr', $address);
            $db->bind(':town', $township);
            $db->bind(':city', $city);
            $db->bind(':prov', $province);
            $db->bind(':postal', $postalCode);
            $db->bind(':citizenship', $citizenship);
            $db->bind(':idnum', $idNumber);
            $db->bind(':passport', $passportNumber);
            $db->bind(':country', $country);
            $db->bind(':bizage', $businessAge);
            $db->bind(':cipc', $cipcNumber);
            $db->bind(':sars', $sarsNumber);
            $db->bind(':vat', $vatNumber);
            $db->bind(':marketing', $marketingConsent);
            $db->bind(':status', $accountStatus);
            $db->bind(':reason', $suspensionReason);
            $db->bind(':token', $verificationToken);
            $db->execute();
            
            $userId = $db->lastInsertId();
            
            // If documents were uploaded, save verification records
            if ($idDocumentPath) {
                $db->query("INSERT INTO verification_documents (
                    user_id, document_type, file_path, verification_status, uploaded_at
                ) VALUES (
                    :uid, :dtype, :path, 'pending', NOW()
                )");
                $db->bind(':uid', $userId);
                $db->bind(':dtype', 'id_document');
                $db->bind(':path', $idDocumentPath);
                $db->execute();
            }
            
            if ($proofOfAddressPath) {
                $db->query("INSERT INTO verification_documents (
                    user_id, document_type, file_path, verification_status, uploaded_at
                ) VALUES (
                    :uid, :dtype, :path, 'pending', NOW()
                )");
                $db->bind(':uid', $userId);
                $db->bind(':dtype', 'proof_of_address');
                $db->bind(':path', $proofOfAddressPath);
                $db->execute();
            }
            
            // NEW: Save business documents (12+ months only)
            if ($cipcDocumentPath) {
                $db->query("INSERT INTO verification_documents (
                    user_id, document_type, file_path, verification_status, uploaded_at
                ) VALUES (
                    :uid, :dtype, :path, 'pending', NOW()
                )");
                $db->bind(':uid', $userId);
                $db->bind(':dtype', 'cipc_document');
                $db->bind(':path', $cipcDocumentPath);
                $db->execute();
            }
            
            if ($sarsVatCertificatePath) {
                $db->query("INSERT INTO verification_documents (
                    user_id, document_type, file_path, verification_status, uploaded_at
                ) VALUES (
                    :uid, :dtype, :path, 'pending', NOW()
                )");
                $db->bind(':uid', $userId);
                $db->bind(':dtype', 'sars_vat_certificate');
                $db->bind(':path', $sarsVatCertificatePath);
                $db->execute();
            }
            
            if ($bankStatementPath) {
                $db->query("INSERT INTO verification_documents (
                    user_id, document_type, file_path, verification_status, uploaded_at
                ) VALUES (
                    :uid, :dtype, :path, 'pending', NOW()
                )");
                $db->bind(':uid', $userId);
                $db->bind(':dtype', 'bank_statement');
                $db->bind(':path', $bankStatementPath);
                $db->execute();
            }
            
            // Send verification email using Brevo
            require_once __DIR__ . '/../includes/Email.php';
            $emailer = new Email();
            $verificationUrl = APP_URL . '/auth/verify-email.php?token=' . $verificationToken;
            
            $emailBody = "
            <h2>Welcome to Street2Screen ZA!</h2>
            <p>Hi $fullName,</p>
            <p>Thank you for registering as a <strong>" . ucfirst($userType) . "</strong>.</p>
            ";
            
            if ($userType === 'buyer') {
                $emailBody .= "<p>Your account is ready to use! Please verify your email:</p>";
            } else {
                $emailBody .= "<p>Your account is pending admin approval. Please verify your email first:</p>";
            }
            
            $emailBody .= "
            <p style='text-align:center;margin:30px 0'>
                <a href='$verificationUrl' 
                   style='background:#FFC107;color:#000;padding:15px 30px;text-decoration:none;border-radius:5px;font-weight:bold;display:inline-block'>
                    Verify Email Address
                </a>
            </p>
            <p>Or copy this link: <br><a href='$verificationUrl'>$verificationUrl</a></p>
            <p>If you didn't create this account, please ignore this email.</p>
            <p><strong>Street2Screen ZA Team</strong><br>
            Bringing Kasi To Your Screen 🇿🇦</p>
            ";
            
            $emailer->send($email, 'Verify Your Street2Screen ZA Account', $emailBody);
            
            $success = 'Registration successful! Please check your email (' . $email . ') to verify your account.';
        }
    }
}

$csrfToken = Security::generateCSRFToken();
?>

<div class="container my-5">
    <div class="text-center mb-5">
        <h1 class="fw-bold mb-3">Create Your Account</h1>
        <p class="lead text-muted">Choose your account type to get started</p>
    </div>

    <?php if ($success): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle"></i> <?php echo Security::clean($success); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    
    <?php if (!empty($errors)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong><i class="fas fa-exclamation-triangle"></i> Please fix the following errors:</strong>
        <ul class="mb-0 mt-2">
        <?php foreach ($errors as $error): ?>
            <li><?php echo Security::clean($error); ?></li>
        <?php endforeach; ?>
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <!-- USER TYPE CARDS -->
    <div class="row mb-5">
        <!-- BUYER -->
        <div class="col-md-4 mb-3">
            <div class="card h-100 shadow hover-scale" style="cursor:pointer;transition:all 0.3s;border:2px solid transparent" onclick="selectUserType('buyer', this)">
                <div class="card-body text-center p-4">
                    <div class="mb-3">
                        <i class="fas fa-shopping-bag fa-4x text-primary"></i>
                    </div>
                    <h4 class="fw-bold">Buyer</h4>
                    <p class="text-muted">Browse and purchase products from township sellers</p>
                    <ul class="list-unstyled small text-start">
                        <li class="mb-2"><i class="fas fa-check text-success"></i> Instant account activation</li>
                        <li class="mb-2"><i class="fas fa-check text-success"></i> Browse all products</li>
                        <li class="mb-2"><i class="fas fa-check text-success"></i> Message sellers</li>
                        <li><i class="fas fa-check text-success"></i> COD or online payment</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- SELLER -->
        <div class="col-md-4 mb-3">
            <div class="card h-100 shadow hover-scale" style="cursor:pointer;transition:all 0.3s;border:2px solid transparent" onclick="selectUserType('seller', this)">
                <div class="card-body text-center p-4">
                    <div class="mb-3">
                        <i class="fas fa-store fa-4x text-success"></i>
                    </div>
                    <h4 class="fw-bold">Seller</h4>
                    <p class="text-muted">Sell your products to buyers across South Africa</p>
                    <ul class="list-unstyled small text-start">
                        <li class="mb-2"><i class="fas fa-clock text-warning"></i> Requires admin approval</li>
                        <li class="mb-2"><i class="fas fa-id-card text-info"></i> ID & address verification</li>
                        <li class="mb-2"><i class="fas fa-briefcase text-info"></i> Business documents (12+ months)</li>
                        <li><i class="fas fa-percent text-success"></i> 10% commission</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- BOTH -->
        <div class="col-md-4 mb-3">
            <div class="card h-100 shadow hover-scale" style="cursor:pointer;transition:all 0.3s;border:2px solid transparent" onclick="selectUserType('both', this)">
                <div class="card-body text-center p-4">
                    <div class="mb-3">
                        <i class="fas fa-exchange-alt fa-4x text-info"></i>
                    </div>
                    <h4 class="fw-bold">Both</h4>
                    <p class="text-muted">Buy AND sell on the platform</p>
                    <ul class="list-unstyled small text-start">
                        <li class="mb-2"><i class="fas fa-clock text-warning"></i> Requires admin approval</li>
                        <li class="mb-2"><i class="fas fa-shopping-cart text-success"></i> Full buyer access</li>
                        <li class="mb-2"><i class="fas fa-store text-success"></i> Full seller access</li>
                        <li><i class="fas fa-star text-warning"></i> Best of both worlds</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- MODERATOR -->
        <div class="col-md-6 mb-3">
            <div class="card h-100 shadow hover-scale" style="cursor:pointer;transition:all 0.3s;border:2px solid transparent" onclick="selectUserType('moderator', this)">
                <div class="card-body text-center p-4">
                    <div class="mb-3">
                        <i class="fas fa-gavel fa-4x text-warning"></i>
                    </div>
                    <h4 class="fw-bold">Moderator</h4>
                    <p class="text-muted">Help resolve disputes between buyers and sellers</p>
                    <ul class="list-unstyled small text-start">
                        <li class="mb-2"><i class="fas fa-clock text-danger"></i> Strict approval process</li>
                        <li class="mb-2"><i class="fas fa-balance-scale text-info"></i> Dispute resolution powers</li>
                        <li><i class="fas fa-shield-alt text-success"></i> Platform moderation</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- ADMIN -->
        <div class="col-md-6 mb-3">
            <div class="card h-100 shadow hover-scale" style="cursor:pointer;transition:all 0.3s;border:2px solid transparent" onclick="selectUserType('admin', this)">
                <div class="card-body text-center p-4">
                    <div class="mb-3">
                        <i class="fas fa-shield-alt fa-4x text-danger"></i>
                    </div>
                    <h4 class="fw-bold">Admin</h4>
                    <p class="text-muted">Full platform management and control</p>
                    <ul class="list-unstyled small text-start">
                        <li class="mb-2"><i class="fas fa-lock text-danger"></i> Highest approval level</li>
                        <li class="mb-2"><i class="fas fa-users-cog text-info"></i> User management</li>
                        <li><i class="fas fa-cog text-success"></i> Platform settings</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- REGISTRATION FORM (Hidden until user type selected) -->
    <div id="registrationForm" style="display:none">
        <div class="card shadow-lg">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">
                    <i class="fas fa-user-plus"></i> Register as <span id="selectedTypeDisplay"></span>
                </h4>
            </div>
            <div class="card-body p-4">
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                    <input type="hidden" name="user_type" id="userTypeInput">

                    <!-- PERSONAL INFORMATION -->
                    <h5 class="fw-bold mb-3"><i class="fas fa-user"></i> Personal Information</h5>

                    <div class="mb-3">
                        <label class="fw-bold">Full Name *</label>
                        <input type="text" class="form-control form-control-lg" name="full_name" 
                               value="<?php echo $formData['full_name']; ?>"
                               placeholder="John Doe" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">Email Address *</label>
                            <input type="email" class="form-control form-control-lg" name="email" id="email"
                                   value="<?php echo $formData['email']; ?>"
                                   placeholder="john@example.com" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">Confirm Email *</label>
                            <input type="email" class="form-control form-control-lg" name="email_confirm" id="emailConfirm"
                                   value="<?php echo $formData['email_confirm']; ?>"
                                   placeholder="john@example.com" required>
                            <small id="emailMatchStatus" class="text-muted"></small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="fw-bold">Phone Number *</label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text">+27</span>
                            <input type="tel" class="form-control" name="phone" 
                                   value="<?php echo $formData['phone']; ?>"
                                   placeholder="812345678" pattern="[0-9]{9}" maxlength="9" required>
                        </div>
                        <small class="text-muted">Enter 9 digits (without leading 0)</small>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">Password *</label>
                            <input type="password" class="form-control form-control-lg" name="password" id="password"
                                   minlength="8" required>
                            <small class="text-muted">
                                <span id="req-length"><i class="fas fa-circle"></i> 8+ characters</span><br>
                                <span id="req-upper"><i class="fas fa-circle"></i> Uppercase</span><br>
                                <span id="req-number"><i class="fas fa-circle"></i> Number</span><br>
                                <span id="req-special"><i class="fas fa-circle"></i> Special character</span>
                            </small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">Confirm Password *</label>
                            <input type="password" class="form-control form-control-lg" name="password_confirm" id="passwordConfirm"
                                   minlength="8" required>
                            <small id="passwordMatchStatus" class="text-muted"></small>
                        </div>
                    </div>

                    <!-- ADDRESS INFORMATION -->
                    <h5 class="fw-bold mb-3 mt-4"><i class="fas fa-map-marker-alt"></i> Address Information</h5>

                    <div class="mb-3">
                        <label class="fw-bold">Street Address *</label>
                        <input type="text" class="form-control form-control-lg" name="address" 
                               value="<?php echo $formData['address']; ?>"
                               placeholder="123 Main Street" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">Township (e.g., Soweto, Alexandra)</label>
                            <input type="text" class="form-control form-control-lg" name="township" 
                                   value="<?php echo $formData['township']; ?>"
                                   placeholder="Soweto">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">City/Town *</label>
                            <input type="text" class="form-control form-control-lg" name="city" 
                                   value="<?php echo $formData['city']; ?>"
                                   placeholder="Johannesburg" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">Province</label>
                            <select class="form-select form-select-lg" name="province">
                                <option value="">Select Province</option>
                                <option value="Gauteng" <?php echo $formData['province']==='Gauteng'?'selected':''; ?>>Gauteng</option>
                                <option value="Western Cape" <?php echo $formData['province']==='Western Cape'?'selected':''; ?>>Western Cape</option>
                                <option value="KwaZulu-Natal" <?php echo $formData['province']==='KwaZulu-Natal'?'selected':''; ?>>KwaZulu-Natal</option>
                                <option value="Eastern Cape" <?php echo $formData['province']==='Eastern Cape'?'selected':''; ?>>Eastern Cape</option>
                                <option value="Free State" <?php echo $formData['province']==='Free State'?'selected':''; ?>>Free State</option>
                                <option value="Limpopo" <?php echo $formData['province']==='Limpopo'?'selected':''; ?>>Limpopo</option>
                                <option value="Mpumalanga" <?php echo $formData['province']==='Mpumalanga'?'selected':''; ?>>Mpumalanga</option>
                                <option value="Northern Cape" <?php echo $formData['province']==='Northern Cape'?'selected':''; ?>>Northern Cape</option>
                                <option value="North West" <?php echo $formData['province']==='North West'?'selected':''; ?>>North West</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">Postal Code</label>
                            <input type="text" class="form-control form-control-lg" name="postal_code" 
                                   value="<?php echo $formData['postal_code']; ?>"
                                   placeholder="1685" maxlength="4" pattern="[0-9]{4}">
                        </div>
                    </div>

                    <!-- NEW PHASE 1: CITIZENSHIP INFORMATION (sellers/both/moderator/admin only) -->
                    <div id="citizenshipSection" style="display: none;">
                        <h5 class="fw-bold mb-3 mt-4"><i class="fas fa-passport"></i> Citizenship Information</h5>
                        
                        <div class="alert alert-warning">
                            <i class="fas fa-info-circle"></i> <strong>Required:</strong> Citizenship verification is mandatory for seller, both, moderator, and admin accounts.
                        </div>

                        <div class="mb-3">
                            <label class="fw-bold">Citizenship Status *</label>
                            <select class="form-select form-select-lg" name="citizenship" id="citizenshipSelect" onchange="toggleCitizenshipFields()">
                                <option value="">Select Citizenship Status</option>
                                <option value="citizen" <?php echo $formData['citizenship']==='citizen'?'selected':''; ?>>South African Citizen</option>
                                <option value="foreign" <?php echo $formData['citizenship']==='foreign'?'selected':''; ?>>Foreign National</option>
                            </select>
                        </div>

                        <!-- SA CITIZEN FIELDS -->
                        <div id="saCitizenFields" style="display: <?php echo $formData['citizenship']==='citizen'?'block':'none'; ?>;">
                            <div class="mb-3">
                                <label class="fw-bold">SA ID Number *</label>
                                <input type="text" class="form-control form-control-lg" name="id_number" id="idNumberInput"
                                       value="<?php echo $formData['id_number']; ?>"
                                       placeholder="0001010000000" maxlength="13" pattern="[0-9]{13}">
                                <small class="text-muted">Must be exactly 13 digits (YYMMDD format)</small>
                            </div>
                        </div>

                        <!-- FOREIGN NATIONAL FIELDS -->
                        <div id="foreignNationalFields" style="display: <?php echo $formData['citizenship']==='foreign'?'block':'none'; ?>;">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="fw-bold">Country *</label>
                                    <select class="form-select form-select-lg" name="country" id="countrySelect">
                                        <option value="">Select Country</option>
                                        <optgroup label="Africa">
                                            <option value="Zimbabwe" <?php echo $formData['country']==='Zimbabwe'?'selected':''; ?>>Zimbabwe</option>
                                            <option value="Mozambique" <?php echo $formData['country']==='Mozambique'?'selected':''; ?>>Mozambique</option>
                                            <option value="Lesotho" <?php echo $formData['country']==='Lesotho'?'selected':''; ?>>Lesotho</option>
                                            <option value="Botswana" <?php echo $formData['country']==='Botswana'?'selected':''; ?>>Botswana</option>
                                            <option value="Namibia" <?php echo $formData['country']==='Namibia'?'selected':''; ?>>Namibia</option>
                                            <option value="Swaziland" <?php echo $formData['country']==='Swaziland'?'selected':''; ?>>Swaziland/Eswatini</option>
                                            <option value="Nigeria" <?php echo $formData['country']==='Nigeria'?'selected':''; ?>>Nigeria</option>
                                            <option value="Ghana" <?php echo $formData['country']==='Ghana'?'selected':''; ?>>Ghana</option>
                                            <option value="Kenya" <?php echo $formData['country']==='Kenya'?'selected':''; ?>>Kenya</option>
                                            <option value="Ethiopia" <?php echo $formData['country']==='Ethiopia'?'selected':''; ?>>Ethiopia</option>
                                            <option value="Other-Africa" <?php echo $formData['country']==='Other-Africa'?'selected':''; ?>>Other African Country</option>
                                        </optgroup>
                                        <optgroup label="Asia">
                                            <option value="India" <?php echo $formData['country']==='India'?'selected':''; ?>>India</option>
                                            <option value="China" <?php echo $formData['country']==='China'?'selected':''; ?>>China</option>
                                            <option value="Pakistan" <?php echo $formData['country']==='Pakistan'?'selected':''; ?>>Pakistan</option>
                                            <option value="Bangladesh" <?php echo $formData['country']==='Bangladesh'?'selected':''; ?>>Bangladesh</option>
                                            <option value="Other-Asia" <?php echo $formData['country']==='Other-Asia'?'selected':''; ?>>Other Asian Country</option>
                                        </optgroup>
                                        <optgroup label="Europe">
                                            <option value="United Kingdom" <?php echo $formData['country']==='United Kingdom'?'selected':''; ?>>United Kingdom</option>
                                            <option value="Germany" <?php echo $formData['country']==='Germany'?'selected':''; ?>>Germany</option>
                                            <option value="France" <?php echo $formData['country']==='France'?'selected':''; ?>>France</option>
                                            <option value="Portugal" <?php echo $formData['country']==='Portugal'?'selected':''; ?>>Portugal</option>
                                            <option value="Other-Europe" <?php echo $formData['country']==='Other-Europe'?'selected':''; ?>>Other European Country</option>
                                        </optgroup>
                                        <optgroup label="Americas">
                                            <option value="United States" <?php echo $formData['country']==='United States'?'selected':''; ?>>United States</option>
                                            <option value="Canada" <?php echo $formData['country']==='Canada'?'selected':''; ?>>Canada</option>
                                            <option value="Brazil" <?php echo $formData['country']==='Brazil'?'selected':''; ?>>Brazil</option>
                                            <option value="Other-Americas" <?php echo $formData['country']==='Other-Americas'?'selected':''; ?>>Other American Country</option>
                                        </optgroup>
                                        <optgroup label="Oceania">
                                            <option value="Australia" <?php echo $formData['country']==='Australia'?'selected':''; ?>>Australia</option>
                                            <option value="New Zealand" <?php echo $formData['country']==='New Zealand'?'selected':''; ?>>New Zealand</option>
                                            <option value="Other-Oceania" <?php echo $formData['country']==='Other-Oceania'?'selected':''; ?>>Other Oceania Country</option>
                                        </optgroup>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="fw-bold">Valid Passport Number *</label>
                                    <input type="text" class="form-control form-control-lg" name="passport_number" id="passportNumberInput"
                                           value="<?php echo $formData['passport_number']; ?>"
                                           placeholder="A12345678">
                                    <small class="text-muted">Enter your valid passport number</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- NEW PHASE 1: BUSINESS INFORMATION (seller/both only) -->
                    <div id="businessSection" style="display: none;">
                        <h5 class="fw-bold mb-3 mt-4"><i class="fas fa-briefcase"></i> Business Information</h5>
                        
                        <div class="mb-3">
                            <label class="fw-bold">How old is your business? *</label>
                            <select class="form-select form-select-lg" name="business_age" id="businessAgeSelect" onchange="toggleBusinessDocuments()">
                                <option value="">Select Business Age</option>
                                <option value="0-11" <?php echo $formData['business_age']==='0-11'?'selected':''; ?>>0-11 months (New Business)</option>
                                <option value="12+" <?php echo $formData['business_age']==='12+'?'selected':''; ?>>12+ months (Established Business)</option>
                            </select>
                            <small class="text-muted">Businesses 12+ months require additional documentation</small>
                        </div>

                        <!-- ESTABLISHED BUSINESS FIELDS (12+ months) -->
                        <div id="establishedBusinessFields" style="display: <?php echo $formData['business_age']==='12+'?'block':'none'; ?>;">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> <strong>Established Business:</strong> Please provide your business registration details and documents.
                            </div>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="fw-bold">CIPC Number *</label>
                                    <input type="text" class="form-control form-control-lg" name="cipc_number" id="cipcNumberInput"
                                           value="<?php echo $formData['cipc_number']; ?>"
                                           placeholder="2023/123456/07">
                                    <small class="text-muted">Company registration number</small>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="fw-bold">SARS Number *</label>
                                    <input type="text" class="form-control form-control-lg" name="sars_number" id="sarsNumberInput"
                                           value="<?php echo $formData['sars_number']; ?>"
                                           placeholder="9123456789">
                                    <small class="text-muted">Tax reference number</small>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="fw-bold">VAT Number *</label>
                                    <input type="text" class="form-control form-control-lg" name="vat_number" id="vatNumberInput"
                                           value="<?php echo $formData['vat_number']; ?>"
                                           placeholder="4123456789">
                                    <small class="text-muted">VAT registration number</small>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="fw-bold">CIPC Registration Document * (PDF)</label>
                                <input type="file" class="form-control form-control-lg" name="cipc_document" id="cipcDocumentInput"
                                       accept=".pdf,.jpg,.jpeg,.png">
                                <small class="text-muted">Upload your CIPC registration certificate</small>
                            </div>

                            <div class="mb-3">
                                <label class="fw-bold">SARS/VAT Certificate * (PDF)</label>
                                <input type="file" class="form-control form-control-lg" name="sars_vat_certificate" id="sarsVatCertificateInput"
                                       accept=".pdf,.jpg,.jpeg,.png">
                                <small class="text-muted">Upload your SARS/VAT registration certificate</small>
                            </div>

                            <div class="mb-3">
                                <label class="fw-bold">Business Bank Statement * (PDF, max 3 months old)</label>
                                <input type="file" class="form-control form-control-lg" name="bank_statement" id="bankStatementInput"
                                       accept=".pdf,.jpg,.jpeg,.png">
                                <small class="text-muted">Upload recent business bank statement showing company name</small>
                            </div>
                        </div>
                    </div>

                    <!-- DOCUMENT UPLOADS (shown only for seller/both/moderator/admin) -->
                    <div id="documentUploads" style="display: none;">
                        <h5 class="fw-bold mb-3 mt-4"><i class="fas fa-file-upload"></i> Required Documents</h5>
                        
                        <div class="alert alert-warning">
                            <strong><i class="fas fa-info-circle"></i> Verification Required</strong>
                            <p class="mb-0">Please upload clear, readable copies of your documents. Accepted formats: JPG, PNG, PDF (max 50MB each)</p>
                        </div>

                        <div class="mb-3">
                            <label class="fw-bold">ID Document * (SA ID, Passport, or SA Driver's License)</label>
                            <input type="file" class="form-control form-control-lg" name="id_document" 
                                   accept=".jpg,.jpeg,.png,.pdf" id="idDocumentInput">
                            <small class="text-danger"><strong>IMPORTANT:</strong> Must be a <strong>CERTIFIED COPY</strong> no older than <strong>3 months</strong>. Must be clear and readable. Failure to comply will result in application rejection.</small>
                        </div>

                        <div class="mb-3">
                            <label class="fw-bold">Proof of Address *</label>
                            <input type="file" class="form-control form-control-lg" name="proof_of_address" 
                                   accept=".jpg,.jpeg,.png,.pdf" id="proofOfAddressInput">
                            <small class="text-muted">Accepted: Utility bill, Bank statement, SAPS Affidavit, or Official letter from Ward Councillor (max <strong>3 months old</strong>). Must show your name and address clearly.</small>
                        </div>
                    </div>

                    <!-- NEW PHASE 1: COMPULSORY CHECKBOXES -->
                    <div class="mb-3 mt-4">
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="agreeTerms" required>
                            <label class="form-check-label" for="agreeTerms">
                                I agree to the <a href="<?php echo APP_URL; ?>/pages/terms.php" target="_blank">Terms of Service</a> *
                            </label>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="agreePrivacy" required>
                            <label class="form-check-label" for="agreePrivacy">
                                I agree to the <a href="<?php echo APP_URL; ?>/pages/privacy.php" target="_blank">Privacy Policy</a> *
                            </label>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="agreeRefund" required>
                            <label class="form-check-label" for="agreeRefund">
                                I agree to the <a href="<?php echo APP_URL; ?>/pages/refund-policy.php" target="_blank">Refund and Cancellation Policy</a> *
                            </label>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="marketing_consent" value="1" id="agreeMarketing">
                            <label class="form-check-label" for="agreeMarketing">
                                I agree to receive marketing emails and special deals from Street2Screen ZA (Optional)
                            </label>
                        </div>
                    </div>

                    <!-- SUBMIT -->
                    <div class="d-grid gap-2 mt-4">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-user-plus"></i> Create Account
                        </button>
                        <a href="<?php echo APP_URL; ?>/auth/login.php" class="btn btn-outline-secondary">
                            Already have an account? Login
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// FORM RETENTION - Show/hide sections on page load based on preserved values
window.addEventListener('DOMContentLoaded', function() {
    // Show citizenship fields if value exists
    <?php if($formData['citizenship']): ?>
    toggleCitizenshipFields();
    <?php endif; ?>
    
    // Show business fields if value exists
    <?php if($formData['business_age']): ?>
    toggleBusinessDocuments();
    <?php endif; ?>
    
    // Pre-select user type card if value exists
    <?php if($formData['user_type']): ?>
    const userType = '<?php echo $formData['user_type']; ?>';
    const cards = document.querySelectorAll('.card');
    cards.forEach(card => {
        if(card.onclick && card.onclick.toString().includes(userType)) {
            card.style.border = '2px solid #0B1F3A';
            document.getElementById('selectedTypeDisplay').textContent = userType.charAt(0).toUpperCase() + userType.slice(1);
            document.getElementById('registrationForm').style.display = 'block';
            
            // Show/hide sections based on user type
            const docUploads = document.getElementById('documentUploads');
            const citizenshipSection = document.getElementById('citizenshipSection');
            const businessSection = document.getElementById('businessSection');
            
            if (userType === 'seller' || userType === 'both' || userType === 'moderator' || userType === 'admin') {
                docUploads.style.display = 'block';
                citizenshipSection.style.display = 'block';
                
                if (userType === 'seller' || userType === 'both') {
                    businessSection.style.display = 'block';
                }
            }
        }
    });
    <?php endif; ?>
});

// User type selection
function selectUserType(type, card) {
    // Remove selection from all cards
    document.querySelectorAll('.card').forEach(c => {
        c.style.border = '2px solid transparent';
    });
    
    // Highlight selected card
    card.style.border = '2px solid #0B1F3A';
    
    // Set hidden input
    document.getElementById('userTypeInput').value = type;
    document.getElementById('selectedTypeDisplay').textContent = type.charAt(0).toUpperCase() + type.slice(1);
    
    // Show/hide sections based on user type
    const docUploads = document.getElementById('documentUploads');
    const idDoc = document.getElementById('idDocumentInput');
    const proofDoc = document.getElementById('proofOfAddressInput');
    const citizenshipSection = document.getElementById('citizenshipSection');
    const businessSection = document.getElementById('businessSection');
    
    if (type === 'seller' || type === 'both' || type === 'moderator' || type === 'admin') {
        docUploads.style.display = 'block';
        idDoc.required = true;
        proofDoc.required = true;
        citizenshipSection.style.display = 'block';
        document.getElementById('citizenshipSelect').required = true;
        
        // Show business section for seller/both only
        if (type === 'seller' || type === 'both') {
            businessSection.style.display = 'block';
            document.getElementById('businessAgeSelect').required = true;
        } else {
            businessSection.style.display = 'none';
            document.getElementById('businessAgeSelect').required = false;
        }
    } else {
        docUploads.style.display = 'none';
        idDoc.required = false;
        proofDoc.required = false;
        citizenshipSection.style.display = 'none';
        document.getElementById('citizenshipSelect').required = false;
        businessSection.style.display = 'none';
        document.getElementById('businessAgeSelect').required = false;
    }
    
    // Show form
    document.getElementById('registrationForm').style.display = 'block';
    
    // Scroll to form
    document.getElementById('registrationForm').scrollIntoView({ behavior: 'smooth', block: 'start' });
}

// Toggle citizenship fields
function toggleCitizenshipFields() {
    const citizenship = document.getElementById('citizenshipSelect').value;
    const saCitizenFields = document.getElementById('saCitizenFields');
    const foreignNationalFields = document.getElementById('foreignNationalFields');
    const idNumberInput = document.getElementById('idNumberInput');
    const passportNumberInput = document.getElementById('passportNumberInput');
    const countrySelect = document.getElementById('countrySelect');
    
    if (citizenship === 'citizen') {
        saCitizenFields.style.display = 'block';
        foreignNationalFields.style.display = 'none';
        idNumberInput.required = true;
        passportNumberInput.required = false;
        countrySelect.required = false;
    } else if (citizenship === 'foreign') {
        saCitizenFields.style.display = 'none';
        foreignNationalFields.style.display = 'block';
        idNumberInput.required = false;
        passportNumberInput.required = true;
        countrySelect.required = true;
    } else {
        saCitizenFields.style.display = 'none';
        foreignNationalFields.style.display = 'none';
        idNumberInput.required = false;
        passportNumberInput.required = false;
        countrySelect.required = false;
    }
}

// Toggle business documents
function toggleBusinessDocuments() {
    const businessAge = document.getElementById('businessAgeSelect').value;
    const establishedBusinessFields = document.getElementById('establishedBusinessFields');
    const cipcNumberInput = document.getElementById('cipcNumberInput');
    const sarsNumberInput = document.getElementById('sarsNumberInput');
    const vatNumberInput = document.getElementById('vatNumberInput');
    const cipcDocumentInput = document.getElementById('cipcDocumentInput');
    const sarsVatCertificateInput = document.getElementById('sarsVatCertificateInput');
    const bankStatementInput = document.getElementById('bankStatementInput');
    
    if (businessAge === '12+') {
        establishedBusinessFields.style.display = 'block';
        cipcNumberInput.required = true;
        sarsNumberInput.required = true;
        vatNumberInput.required = true;
        cipcDocumentInput.required = true;
        sarsVatCertificateInput.required = true;
        bankStatementInput.required = true;
    } else {
        establishedBusinessFields.style.display = 'none';
        cipcNumberInput.required = false;
        sarsNumberInput.required = false;
        vatNumberInput.required = false;
        cipcDocumentInput.required = false;
        sarsVatCertificateInput.required = false;
        bankStatementInput.required = false;
    }
}

// Email match validation
document.getElementById('emailConfirm').addEventListener('input', function() {
    const email = document.getElementById('email').value;
    const emailConfirm = this.value;
    const status = document.getElementById('emailMatchStatus');
    
    if (emailConfirm === '') {
        status.textContent = '';
        status.className = 'text-muted';
    } else if (email === emailConfirm) {
        status.textContent = '✓ Emails match';
        status.className = 'text-success';
    } else {
        status.textContent = '✗ Emails do not match';
        status.className = 'text-danger';
    }
});

// Password match validation
document.getElementById('passwordConfirm').addEventListener('input', function() {
    const password = document.getElementById('password').value;
    const passwordConfirm = this.value;
    const status = document.getElementById('passwordMatchStatus');
    
    if (passwordConfirm === '') {
        status.textContent = '';
        status.className = 'text-muted';
    } else if (password === passwordConfirm) {
        status.textContent = '✓ Passwords match';
        status.className = 'text-success';
    } else {
        status.textContent = '✗ Passwords do not match';
        status.className = 'text-danger';
    }
});

// Password strength checker
document.getElementById('password').addEventListener('input', function() {
    const val = this.value;
    check('req-length', val.length >= 8);
    check('req-upper', /[A-Z]/.test(val));
    check('req-number', /[0-9]/.test(val));
    check('req-special', /[^A-Za-z0-9]/.test(val));
});

function check(id, pass) {
    const el = document.getElementById(id);
    el.className = pass ? 'text-success' : 'text-muted';
    el.querySelector('i').className = pass ? 'fas fa-check-circle' : 'fas fa-circle';
}

// Hover effects for cards
document.querySelectorAll('.hover-scale').forEach(card => {
    card.addEventListener('mouseenter', function() {
        if (this.style.border === '2px solid transparent') {
            this.style.border = '2px solid #ccc';
        }
    });
    card.addEventListener('mouseleave', function() {
        if (this.style.border === '2px solid rgb(204, 204, 204)') {
            this.style.border = '2px solid transparent';
        }
    });
});
</script>

<style>
.hover-scale:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2) !important;
}
</style>

<?php require_once __DIR__.'/../includes/footer.php'; ?>
