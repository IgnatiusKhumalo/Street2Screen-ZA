<?php
$pageTitle='Manage User';
require_once __DIR__.'/../includes/header.php';
Security::requireAdmin();

$userId=get_get('id');
$db=new Database();

$db->query("SELECT * FROM users WHERE user_id=:id");
$db->bind(':id',$userId);
$user=$db->fetch();

if(!$user) redirect_with_error(APP_URL.'/admin/users.php','User not found');

// Prevent admin from deleting themselves
if($userId == Security::getUserId()) {
    redirect_with_error(APP_URL.'/admin/users.php','You cannot delete your own account!');
}

$errors=[];

if(is_post_request()&&Security::validateCSRFToken(get_post('csrf_token'))){
    $action=get_post('action');

    if($action==='suspend'){
        $reason=Security::sanitizeString(get_post('suspension_reason'));
        if(empty($reason)){ $errors[]='Suspension reason required'; }
        else{
            $db->query("UPDATE users SET account_status='suspended',suspension_reason=:reason WHERE user_id=:id");
            $db->bind(':reason',$reason);
            $db->bind(':id',$userId);
            $db->execute();

            // Log action
            $db->query("INSERT INTO admin_logs(admin_id,action_type,target_type,target_id,action_details,ip_address,timestamp)
            VALUES(:admin,'suspend_user','user',:target,:details,:ip,NOW())");
            $db->bind(':admin',Security::getUserId());
            $db->bind(':target',$userId);
            $db->bind(':details',json_encode(['reason'=>$reason]));
            $db->bind(':ip',$_SERVER['REMOTE_ADDR']??'');
            $db->execute();

            redirect_with_success(APP_URL.'/admin/users.php','User suspended');
        }
    }elseif($action==='activate'){
        $db->query("UPDATE users SET account_status='active',suspension_reason=NULL WHERE user_id=:id");
        $db->bind(':id',$userId);
        $db->execute();

        $db->query("INSERT INTO admin_logs(admin_id,action_type,target_type,target_id,action_details,ip_address,timestamp)
        VALUES(:admin,'activate_user','user',:target,:details,:ip,NOW())");
        $db->bind(':admin',Security::getUserId());
        $db->bind(':target',$userId);
        $db->bind(':details',json_encode(['previous_status'=>$user['account_status']]));
        $db->bind(':ip',$_SERVER['REMOTE_ADDR']??'');
        $db->execute();

        redirect_with_success(APP_URL.'/admin/users.php','User activated');

    }elseif($action==='change_type'){
        $newType=get_post('user_type');
        $db->query("UPDATE users SET user_type=:type WHERE user_id=:id");
        $db->bind(':type',$newType);
        $db->bind(':id',$userId);
        $db->execute();

        $db->query("INSERT INTO admin_logs(admin_id,action_type,target_type,target_id,action_details,ip_address,timestamp)
        VALUES(:admin,'change_user_type','user',:target,:details,:ip,NOW())");
        $db->bind(':admin',Security::getUserId());
        $db->bind(':target',$userId);
        $db->bind(':details',json_encode(['from'=>$user['user_type'],'to'=>$newType]));
        $db->bind(':ip',$_SERVER['REMOTE_ADDR']??'');
        $db->execute();

        redirect_with_success(APP_URL.'/admin/users.php','User type updated');
        
    }elseif($action==='delete'){
        // NEW: DELETE USER ACTION
        // Log before deleting
        $db->query("INSERT INTO admin_logs(admin_id,action_type,target_type,target_id,action_details,ip_address,timestamp)
        VALUES(:admin,'delete_user','user',:target,:details,:ip,NOW())");
        $db->bind(':admin',Security::getUserId());
        $db->bind(':target',$userId);
        $db->bind(':details',json_encode([
            'user_email' => $user['email'],
            'user_name' => $user['full_name'],
            'user_type' => $user['user_type']
        ]));
        $db->bind(':ip',$_SERVER['REMOTE_ADDR']??'');
        $db->execute();
        
        // Delete user (CASCADE will delete related records)
        $db->query("DELETE FROM users WHERE user_id=:id");
        $db->bind(':id',$userId);
        $db->execute();

        redirect_with_success(APP_URL.'/admin/users.php','User permanently deleted');
    }
}

$csrfToken=Security::generateCSRFToken();

// Get user stats
$db->query("SELECT COUNT(*) as total FROM orders WHERE buyer_id=:id");
$db->bind(':id',$userId);
$orderCount=$db->fetch()['total'];

$db->query("SELECT COUNT(*) as total FROM products WHERE seller_id=:id");
$db->bind(':id',$userId);
$productCount=$db->fetch()['total'];
?>

<div class="container my-5">
<div class="row justify-content-center">
<div class="col-md-8">

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold"><i class="fas fa-user-cog"></i> Manage User</h2>
    <a href="<?php echo APP_URL; ?>/admin/users.php" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left"></i> Back
    </a>
</div>

<!-- User Info -->
<div class="card shadow-sm mb-4">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="fas fa-user"></i> User Information</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <p class="mb-1"><strong>Name:</strong> <?php echo Security::clean($user['full_name']); ?></p>
                <p class="mb-1"><strong>Email:</strong> <?php echo Security::clean($user['email']); ?></p>
                <p class="mb-1"><strong>Phone:</strong> <?php echo Security::clean($user['phone']??'N/A'); ?></p>
                <p class="mb-0"><strong>Joined:</strong> <?php echo format_date($user['created_at']); ?></p>
            </div>
            <div class="col-md-6">
                <p class="mb-1"><strong>Type:</strong> <span class="badge bg-primary"><?php echo ucfirst($user['user_type']); ?></span></p>
                <p class="mb-1"><strong>Status:</strong> <span class="badge bg-<?php echo $user['account_status']==='active'?'success':'danger'; ?>"><?php echo ucfirst($user['account_status']); ?></span></p>
                <p class="mb-1"><strong>Email Verified:</strong> <?php echo $user['email_verified']?'<span class="text-success">Yes</span>':'<span class="text-danger">No</span>'; ?></p>
                <p class="mb-0"><strong>Orders/Products:</strong> <?php echo $orderCount; ?> / <?php echo $productCount; ?></p>
            </div>
        </div>
        <?php if($user['suspension_reason']): ?>
        <div class="alert alert-warning mt-3 mb-0">
            <strong>Suspension Reason:</strong> <?php echo Security::clean($user['suspension_reason']); ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Actions -->
<div class="card shadow-sm mb-3">
    <div class="card-header bg-warning text-dark">
        <h5 class="mb-0"><i class="fas fa-tools"></i> Admin Actions</h5>
    </div>
    <div class="card-body">

        <!-- Suspend/Activate -->
        <?php if($user['account_status']==='active'): ?>
        <form method="POST" class="mb-3">
            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
            <input type="hidden" name="action" value="suspend">
            <label class="fw-bold">Suspend User</label>
            <div class="input-group">
                <input type="text" class="form-control" name="suspension_reason" placeholder="Reason for suspension..." required>
                <button class="btn btn-danger" onclick="return confirm('Suspend this user?')">
                    <i class="fas fa-ban"></i> Suspend
                </button>
            </div>
        </form>
        <?php else: ?>
        <form method="POST" class="mb-3">
            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
            <input type="hidden" name="action" value="activate">
            <button class="btn btn-success" onclick="return confirm('Activate this user?')">
                <i class="fas fa-check"></i> Activate Account
            </button>
        </form>
        <?php endif; ?>

        <!-- Change user type -->
        <form method="POST" class="mb-3">
            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
            <input type="hidden" name="action" value="change_type">
            <label class="fw-bold">Change User Type</label>
            <div class="input-group">
                <select class="form-select" name="user_type">
                    <?php foreach(['buyer','seller','both','moderator','admin'] as $type): ?>
                    <option value="<?php echo $type; ?>" <?php echo $user['user_type']===$type?'selected':''; ?>>
                        <?php echo ucfirst($type); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <button class="btn btn-primary" onclick="return confirm('Change user type?')">
                    <i class="fas fa-save"></i> Update
                </button>
            </div>
        </form>
        
        <!-- NEW: DELETE USER -->
        <hr class="my-4">
        <div class="alert alert-danger">
            <h6 class="alert-heading"><i class="fas fa-exclamation-triangle"></i> Danger Zone</h6>
            <p class="mb-0 small">Deleting this user will permanently remove all their data including orders, products, and favorites. This action cannot be undone!</p>
        </div>
        <form method="POST" onsubmit="return confirm('⚠️ WARNING: This will PERMANENTLY DELETE this user and ALL their data!\n\nUser: <?php echo Security::clean($user['full_name']); ?>\nEmail: <?php echo Security::clean($user['email']); ?>\n\nThis includes:\n- All their orders\n- All their products\n- All their favorites\n- All verification documents\n\nThis action CANNOT be undone!\n\nType DELETE to confirm:') && prompt('Type DELETE in capital letters to confirm:') === 'DELETE'">
            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
            <input type="hidden" name="action" value="delete">
            <button class="btn btn-danger btn-lg w-100">
                <i class="fas fa-trash-alt"></i> Permanently Delete User
            </button>
        </form>
    </div>
</div>

</div>
</div>
</div>

<?php require_once __DIR__.'/../includes/footer.php'; ?>
