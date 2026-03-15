<?php
$pageTitle='Login';
require_once __DIR__.'/../includes/header.php';

$errors=[];

if(is_post_request()){
    if(!Security::validateCSRFToken(get_post('csrf_token'))){
        $errors[]='Invalid token';
    }else{
        $email=Security::sanitizeEmail(get_post('email'));
        $password=get_post('password');
        
        if(empty($email)||empty($password)){
            $errors[]='Email and password required';
        }else{
            $db=new Database();
            $db->query("SELECT user_id,full_name,password_hash,user_type,account_status,email_verified FROM users WHERE email=:email");
            $db->bind(':email',$email);
            $user=$db->fetch();
            
            if($user&&Security::verifyPassword($password,$user['password_hash'])){
                if($user['account_status']==='suspended'){
                    $errors[]='Account suspended';
                }elseif($user['email_verified']==0){
                    $errors[]='Please verify your email first';
                }else{
                    $_SESSION['user_id']=$user['user_id'];
                    $_SESSION['user_type']=$user['user_type'];
                    $_SESSION['user_name']=$user['full_name'];
                    redirect_with_success(APP_URL.'/index.php','Login successful!');
                }
            }else{
                $errors[]='Invalid email or password';
            }
        }
    }
}

$csrfToken=Security::generateCSRFToken();
?>

<div class="container my-5">
<div class="row justify-content-center">
<div class="col-md-5">

<div class="card shadow">
    <div class="card-header bg-primary text-white text-center py-3">
        <h4><i class="fas fa-sign-in-alt"></i> Login</h4>
    </div>
    <div class="card-body p-4">
        
        <?php if(!empty($errors)):?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach($errors as $e):?>
                    <li><?php echo Security::clean($e); ?></li>
                <?php endforeach;?>
            </ul>
        </div>
        <?php endif;?>
        
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
            
            <div class="mb-3">
                <label class="fw-bold">Email *</label>
                <input type="email" 
                       class="form-control form-control-lg" 
                       name="email" 
                       value="<?php echo Security::clean(get_post('email', '')); ?>"
                       required>
            </div>
            
            <div class="mb-3">
                <label class="fw-bold">Password *</label>
                <div style="position:relative">
                    <input type="password" 
                           class="form-control form-control-lg" 
                           id="loginPassword" 
                           name="password" 
                           required>
                    <i class="fas fa-eye" 
                       onclick="togglePassword('loginPassword')" 
                       style="position:absolute;right:10px;top:50%;transform:translateY(-50%);cursor:pointer"></i>
                </div>
            </div>
            
            <!-- ADDED: Forgot Password Link -->
            <div class="mb-3 text-end">
                <a href="<?php echo APP_URL; ?>/auth/forgot-password.php" class="text-primary">
                    <i class="fas fa-key"></i> Forgot Password?
                </a>
            </div>
            
            <div class="d-grid">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
            </div>
        </form>
        
        <div class="text-center mt-3">
            <p class="mb-0">Don't have an account? 
                <a href="<?php echo APP_URL; ?>/auth/register.php" class="fw-bold">Register</a>
            </p>
        </div>
    </div>
</div>

</div>
</div>
</div>

<script>
function togglePassword(id){
    const i=document.getElementById(id);
    const c=i.nextElementSibling;
    if(i.type==='password'){
        i.type='text';
        c.classList.remove('fa-eye');
        c.classList.add('fa-eye-slash');
    }else{
        i.type='password';
        c.classList.remove('fa-eye-slash');
        c.classList.add('fa-eye');
    }
}
</script>

<?php require_once __DIR__.'/../includes/footer.php';?>
