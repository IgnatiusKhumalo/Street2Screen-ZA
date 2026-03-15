<?php
$pageTitle='My Profile';
require_once __DIR__.'/../includes/header.php';
Security::requireLogin();

$db=new Database();
$userId=Security::getUserId();
$errors=[];
$success='';

// Get user data
$db->query("SELECT * FROM users WHERE user_id=:uid");
$db->bind(':uid',$userId);
$user=$db->fetch();

if(is_post_request()){
    if(!Security::validateCSRFToken(get_post('csrf_token'))){
        $errors[]='Invalid token';
    }else{
        $fullName=Security::sanitizeString(get_post('full_name'));
        $phone='+27'.Security::sanitizeString(get_post('phone'));
        $address=Security::sanitizeString(get_post('address'));
        $township=Security::sanitizeString(get_post('township'));
        $city=Security::sanitizeString(get_post('city'));
        $province=get_post('province');
        $postalCode=Security::sanitizeString(get_post('postal_code'));
        
        if(empty($fullName)) $errors[]='Full name required';
        if(!Security::validatePhone($phone)) $errors[]='Valid phone number required';
        
        if(empty($errors)){
            $db->query("UPDATE users SET full_name=:name,phone=:phone,address=:addr,township=:town,city=:city,province=:prov,postal_code=:postal WHERE user_id=:uid");
            $db->bind(':name',$fullName);
            $db->bind(':phone',$phone);
            $db->bind(':addr',$address);
            $db->bind(':town',$township);
            $db->bind(':city',$city);
            $db->bind(':prov',$province);
            $db->bind(':postal',$postalCode);
            $db->bind(':uid',$userId);
            
            if($db->execute()){
                $success='Profile updated successfully!';
                // Refresh user data
                $db->query("SELECT * FROM users WHERE user_id=:uid");
                $db->bind(':uid',$userId);
                $user=$db->fetch();
            }
        }
    }
}

$csrfToken=Security::generateCSRFToken();
?>

<div class="container my-5">
<div class="row justify-content-center">
<div class="col-md-8">

<h2 class="fw-bold mb-4"><i class="fas fa-user text-primary"></i> My Profile</h2>

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
            
            <div class="mb-3">
                <label class="fw-bold">Full Name *</label>
                <input type="text" class="form-control form-control-lg" name="full_name" value="<?php echo Security::clean($user['full_name']); ?>" required>
            </div>
            
            <div class="mb-3">
                <label class="fw-bold">Email Address</label>
                <input type="email" class="form-control form-control-lg" value="<?php echo Security::clean($user['email']); ?>" disabled>
                <small class="text-muted">Email cannot be changed</small>
            </div>
            
            <div class="mb-3">
                <label class="fw-bold">Phone Number *</label>
                <div class="input-group">
                    <span class="input-group-text">+27</span>
                    <input type="text" class="form-control form-control-lg" name="phone" value="<?php echo substr($user['phone'],3); ?>" maxlength="9" required>
                </div>
            </div>
            
            <div class="mb-3">
                <label class="fw-bold">Street Address</label>
                <input type="text" class="form-control form-control-lg" name="address" value="<?php echo Security::clean($user['address']); ?>">
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="fw-bold">Township/Suburb</label>
                    <input type="text" class="form-control form-control-lg" name="township" value="<?php echo Security::clean($user['township']); ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="fw-bold">City</label>
                    <input type="text" class="form-control form-control-lg" name="city" value="<?php echo Security::clean($user['city']); ?>">
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="fw-bold">Province</label>
                    <select class="form-select form-select-lg" name="province">
                        <option value="">Select</option>
                        <?php 
                        $provinces=['Gauteng','Western Cape','KwaZulu-Natal','Eastern Cape','Free State','Limpopo','Mpumalanga','Northern Cape','North West'];
                        foreach($provinces as $p):
                        ?>
                        <option value="<?php echo $p; ?>" <?php echo $user['province']===$p?'selected':''; ?>><?php echo $p; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="fw-bold">Postal Code</label>
                    <input type="text" class="form-control form-control-lg" name="postal_code" value="<?php echo Security::clean($user['postal_code']); ?>" maxlength="4">
                </div>
            </div>
            
            <div class="d-grid">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-save"></i> Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

</div>
</div>
</div>

<?php require_once __DIR__.'/../includes/footer.php'; ?>
