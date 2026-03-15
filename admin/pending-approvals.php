<?php
$pageTitle='Pending Approvals';
require_once __DIR__.'/../includes/header.php';
Security::requireAdmin();

$db=new Database();

// Handle approval/rejection
if(is_post_request()){
    $action=get_post('action');
    $userId=get_post('user_id');
    $reason=Security::sanitizeString(get_post('reason'));
    
    if($action==='approve'){
        $db->query("UPDATE users SET account_status='active',suspension_reason=NULL WHERE user_id=:id");
        $db->bind(':id',$userId);
        if($db->execute()){
            redirect_with_success(APP_URL.'/admin/pending-approvals.php','User approved successfully');
        }
    }elseif($action==='reject'){
        $db->query("UPDATE users SET suspension_reason=:reason WHERE user_id=:id");
        $db->bind(':id',$userId);
        $db->bind(':reason',$reason);
        if($db->execute()){
            redirect_with_success(APP_URL.'/admin/pending-approvals.php','User rejected');
        }
    }
}

// Get pending users
$db->query("SELECT user_id,full_name,email,phone,user_type,citizenship,id_number,passport_number,country,business_age,cipc_number,sars_number,vat_number,created_at FROM users WHERE user_type IN('seller','both','moderator','admin') AND account_status='suspended' AND suspension_reason LIKE '%approval%' ORDER BY created_at DESC");
$pending=$db->fetchAll();

$csrfToken=Security::generateCSRFToken();
?>

<div class="container my-5">
<h2 class="fw-bold mb-4"><i class="fas fa-user-check text-warning"></i> Pending Approvals (<?php echo count($pending); ?>)</h2>

<?php if(empty($pending)): ?>
    <div class="alert alert-info text-center">
        <i class="fas fa-check-circle fa-3x mb-3"></i>
        <h5>No Pending Approvals</h5>
        <p>All user registration requests have been processed.</p>
    </div>
<?php else: ?>
    <?php foreach($pending as $u): ?>
    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h5 class="mb-2">
                        <?php echo Security::clean($u['full_name']); ?>
                        <span class="badge bg-<?php echo $u['user_type']==='admin'?'danger':($u['user_type']==='moderator'?'warning':'info'); ?>">
                            <?php echo ucfirst($u['user_type']); ?>
                        </span>
                    </h5>
                    <p class="mb-1"><strong>Email:</strong> <?php echo Security::clean($u['email']); ?></p>
                    <p class="mb-1"><strong>Phone:</strong> <?php echo Security::clean($u['phone']); ?></p>
                    <p class="mb-0"><strong>Requested:</strong> <?php echo time_ago($u['created_at']); ?></p>
                    
                    <!-- NEW PHASE 2: Show citizenship info -->
                    <?php if($u['citizenship']): ?>
                    <hr class="my-2">
                    <p class="mb-1"><strong>Citizenship:</strong> 
                        <span class="badge bg-<?php echo $u['citizenship']==='citizen'?'success':'info'; ?>">
                            <?php echo $u['citizenship']==='citizen'?'SA Citizen':'Foreign National'; ?>
                        </span>
                    </p>
                    <?php if($u['citizenship']==='citizen' && $u['id_number']): ?>
                        <p class="mb-1"><strong>SA ID:</strong> <?php echo Security::clean($u['id_number']); ?></p>
                    <?php elseif($u['citizenship']==='foreign'): ?>
                        <p class="mb-1"><strong>Passport:</strong> <?php echo Security::clean($u['passport_number']); ?></p>
                        <p class="mb-1"><strong>Country:</strong> <?php echo Security::clean($u['country']); ?></p>
                    <?php endif; ?>
                    <?php endif; ?>
                    
                    <!-- NEW PHASE 2: Show business info -->
                    <?php if(in_array($u['user_type'],['seller','both']) && $u['business_age']): ?>
                    <hr class="my-2">
                    <p class="mb-1"><strong>Business Age:</strong> 
                        <span class="badge bg-secondary"><?php echo $u['business_age']; ?> months</span>
                    </p>
                    <?php if($u['business_age']==='12+' && ($u['cipc_number'] || $u['sars_number'] || $u['vat_number'])): ?>
                        <?php if($u['cipc_number']): ?><p class="mb-1 small"><strong>CIPC:</strong> <?php echo Security::clean($u['cipc_number']); ?></p><?php endif; ?>
                        <?php if($u['sars_number']): ?><p class="mb-1 small"><strong>SARS:</strong> <?php echo Security::clean($u['sars_number']); ?></p><?php endif; ?>
                        <?php if($u['vat_number']): ?><p class="mb-1 small"><strong>VAT:</strong> <?php echo Security::clean($u['vat_number']); ?></p><?php endif; ?>
                    <?php endif; ?>
                    <?php endif; ?>
                    
                    <!-- NEW: Show uploaded documents with proper names -->
                    <?php 
                    $db->query("SELECT document_type FROM verification_documents WHERE user_id=:uid AND verification_status='pending'");
                    $db->bind(':uid', $u['user_id']);
                    $userDocs = $db->fetchAll();
                    if(!empty($userDocs)):
                    ?>
                    <hr class="my-2">
                    <p class="mb-1"><strong>Documents Uploaded:</strong></p>
                    <div class="d-flex flex-wrap gap-1">
                        <?php 
                        $docTypeNames = [
                            'id_document' => 'ID Document',
                            'proof_of_address' => 'Proof of Address',
                            'cipc_document' => 'CIPC Document',
                            'sars_vat_certificate' => 'SARS/VAT Certificate',
                            'bank_statement' => 'Bank Statement'
                        ];
                        foreach($userDocs as $doc): 
                            $docName = $docTypeNames[$doc['document_type']] ?? ucfirst(str_replace('_', ' ', $doc['document_type']));
                        ?>
                            <span class="badge bg-secondary small"><?php echo $docName; ?></span>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="col-md-4 text-end">
                    <form method="POST" class="d-inline">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                        <input type="hidden" name="user_id" value="<?php echo $u['user_id']; ?>">
                        <input type="hidden" name="action" value="approve">
                        <button type="submit" class="btn btn-success mb-2 w-100">
                            <i class="fas fa-check"></i> Approve
                        </button>
                    </form>
                    <button class="btn btn-danger w-100" onclick="showRejectModal(<?php echo $u['user_id']; ?>,'<?php echo Security::clean($u['full_name']); ?>')">
                        <i class="fas fa-times"></i> Reject
                    </button>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
<?php endif; ?>

</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Reject User Application</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                    <input type="hidden" name="user_id" id="rejectUserId">
                    <input type="hidden" name="action" value="reject">
                    
                    <p>You are rejecting: <strong id="rejectUserName"></strong></p>
                    
                    <div class="mb-3">
                        <label class="fw-bold">Reason for Rejection *</label>
                        <textarea class="form-control" name="reason" rows="3" required placeholder="Explain why this application is being rejected..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Confirm Rejection</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showRejectModal(userId,userName){
    document.getElementById('rejectUserId').value=userId;
    document.getElementById('rejectUserName').textContent=userName;
    new bootstrap.Modal(document.getElementById('rejectModal')).show();
}
</script>

<?php require_once __DIR__.'/../includes/footer.php'; ?>
