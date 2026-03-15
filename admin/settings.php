<?php
$pageTitle='Platform Settings';
require_once __DIR__.'/../includes/header.php';
Security::requireAdmin();

$db=new Database();
$success='';
$errors=[];

// Handle settings update
if(is_post_request()){
    if(!Security::validateCSRFToken(get_post('csrf_token'))){
        $errors[]='Invalid token';
    }else{
        $commission=get_post('commission');
        $codThreshold=get_post('cod_threshold');
        $featuredDuration=get_post('featured_duration');
        
        // Validate
        if($commission<0||$commission>100){
            $errors[]='Commission must be between 0 and 100';
        }
        if($codThreshold<0){
            $errors[]='COD threshold must be positive';
        }
        if($featuredDuration<1){
            $errors[]='Featured duration must be at least 1 day';
        }
        
        if(empty($errors)){
            $db->query("INSERT INTO platform_settings(setting_key,setting_value,updated_at)VALUES('commission',:commission,NOW())ON DUPLICATE KEY UPDATE setting_value=:commission,updated_at=NOW()");
            $db->bind(':commission',$commission);
            $db->execute();
            
            $db->query("INSERT INTO platform_settings(setting_key,setting_value,updated_at)VALUES('cod_threshold',:threshold,NOW())ON DUPLICATE KEY UPDATE setting_value=:threshold,updated_at=NOW()");
            $db->bind(':threshold',$codThreshold);
            $db->execute();
            
            $db->query("INSERT INTO platform_settings(setting_key,setting_value,updated_at)VALUES('featured_duration',:duration,NOW())ON DUPLICATE KEY UPDATE setting_value=:duration,updated_at=NOW()");
            $db->bind(':duration',$featuredDuration);
            $db->execute();
            
            $success='Settings updated successfully!';
        }
    }
}

// Get current settings
$db->query("SELECT setting_key,setting_value FROM platform_settings");
$settingsData=$db->fetchAll();
$settings=[];
foreach($settingsData as $s){
    $settings[$s['setting_key']]=$s['setting_value'];
}

$commission=$settings['commission']??PLATFORM_COMMISSION*100;
$codThreshold=$settings['cod_threshold']??COD_THRESHOLD;
$featuredDuration=$settings['featured_duration']??FEATURED_DURATION_DAYS;

$csrfToken=Security::generateCSRFToken();
?>

<div class="container my-5">
<h2 class="fw-bold mb-4"><i class="fas fa-cog text-primary"></i> Platform Settings</h2>

<div class="row justify-content-center">
<div class="col-md-8">

<?php if($success): ?>
<div class="alert alert-success alert-dismissible fade show">
    <i class="fas fa-check-circle"></i> <?php echo $success; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php if(!empty($errors)): ?>
<div class="alert alert-danger">
    <ul class="mb-0">
    <?php foreach($errors as $e): ?>
        <li><?php echo Security::clean($e); ?></li>
    <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<div class="card shadow">
    <div class="card-body p-4">
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
            
            <div class="mb-4">
                <label class="fw-bold">Platform Commission (%)</label>
                <input type="number" class="form-control form-control-lg" name="commission" value="<?php echo $commission; ?>" min="0" max="100" step="0.1" required>
                <small class="text-muted">Percentage charged on all sales</small>
            </div>
            
            <div class="mb-4">
                <label class="fw-bold">Cash on Delivery Threshold (R)</label>
                <input type="number" class="form-control form-control-lg" name="cod_threshold" value="<?php echo $codThreshold; ?>" min="0" step="1" required>
                <small class="text-muted">Orders below this amount use COD, above use PayFast</small>
            </div>
            
            <div class="mb-4">
                <label class="fw-bold">Featured Product Duration (Days)</label>
                <input type="number" class="form-control form-control-lg" name="featured_duration" value="<?php echo $featuredDuration; ?>" min="1" step="1" required>
                <small class="text-muted">How long featured products stay highlighted</small>
            </div>
            
            <div class="d-grid">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-save"></i> Save Settings
                </button>
            </div>
        </form>
    </div>
</div>

</div>
</div>

</div>

<?php require_once __DIR__.'/../includes/footer.php'; ?>
