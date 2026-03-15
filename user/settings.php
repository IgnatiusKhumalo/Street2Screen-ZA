<?php
$pageTitle='Settings';
require_once __DIR__.'/../includes/header.php';
Security::requireLogin();

$success='';

// Handle theme change
if(is_post_request()&&get_post('action')==='theme'){
    $theme=get_post('theme');
    if(in_array($theme,['light','dark'])){
        $_SESSION['theme']=$theme;
        $success='Theme updated successfully!';
    }
}

// Handle language change
if(is_post_request()&&get_post('action')==='language'){
    $language=get_post('language');
    require_once __DIR__.'/../includes/Language.php';
    if(Language::setLanguage($language)){
        $success='Language updated successfully!';
    }
}

$currentTheme=$_SESSION['theme']??'light';
$currentLanguage=$_SESSION['language']??'en';

require_once __DIR__.'/../includes/Language.php';
$languages=Language::getLanguages();

$csrfToken=Security::generateCSRFToken();
?>

<div class="container my-5">
<div class="row justify-content-center">
<div class="col-md-8">

<h2 class="fw-bold mb-4"><i class="fas fa-cog text-primary"></i> Settings</h2>

<?php if($success): ?>
<div class="alert alert-success alert-dismissible fade show">
    <i class="fas fa-check-circle"></i> <?php echo $success; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Theme Settings -->
<div class="card shadow-sm mb-4">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="fas fa-palette"></i> Theme</h5>
    </div>
    <div class="card-body">
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
            <input type="hidden" name="action" value="theme">
            
            <p class="text-muted">Choose your preferred theme</p>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="card cursor-pointer <?php echo $currentTheme==='light'?'border-primary':''; ?>" style="cursor:pointer">
                        <div class="card-body text-center">
                            <input type="radio" name="theme" value="light" <?php echo $currentTheme==='light'?'checked':''; ?> onchange="this.form.submit()">
                            <i class="fas fa-sun fa-3x text-warning mt-2 mb-2"></i>
                            <h6>Light Mode</h6>
                            <small class="text-muted">Default theme</small>
                        </div>
                    </label>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="card cursor-pointer <?php echo $currentTheme==='dark'?'border-primary':''; ?>" style="cursor:pointer">
                        <div class="card-body text-center">
                            <input type="radio" name="theme" value="dark" <?php echo $currentTheme==='dark'?'checked':''; ?> onchange="this.form.submit()">
                            <i class="fas fa-moon fa-3x text-primary mt-2 mb-2"></i>
                            <h6>Dark Mode</h6>
                            <small class="text-muted">Easy on eyes</small>
                        </div>
                    </label>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Language Settings -->
<div class="card shadow-sm mb-4">
    <div class="card-header bg-success text-white">
        <h5 class="mb-0"><i class="fas fa-language"></i> Language</h5>
    </div>
    <div class="card-body">
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
            <input type="hidden" name="action" value="language">
            
            <p class="text-muted">Select your preferred language</p>
            
            <select class="form-select form-select-lg" name="language" onchange="this.form.submit()">
                <?php foreach($languages as $code=>$name): ?>
                <option value="<?php echo $code; ?>" <?php echo $currentLanguage===$code?'selected':''; ?>>
                    <?php echo $name; ?>
                </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>
</div>

<!-- Account Info -->
<div class="card shadow-sm">
    <div class="card-header bg-dark text-white">
        <h5 class="mb-0"><i class="fas fa-user-shield"></i> Account Information</h5>
    </div>
    <div class="card-body">
        <p class="mb-2"><strong>Account Type:</strong> <span class="badge bg-primary"><?php echo ucfirst(Security::getUserType()); ?></span></p>
        <p class="mb-2"><strong>Member Since:</strong> <?php 
            $db=new Database();
            $db->query("SELECT created_at FROM users WHERE user_id=:uid");
            $db->bind(':uid',Security::getUserId());
            echo format_date($db->fetch()['created_at']);
        ?></p>
        <hr>
        <a href="<?php echo APP_URL; ?>/auth/logout.php" class="btn btn-danger">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
</div>

</div>
</div>
</div>

<?php require_once __DIR__.'/../includes/footer.php'; ?>
