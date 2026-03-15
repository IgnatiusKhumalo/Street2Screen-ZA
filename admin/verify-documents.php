<?php
$pageTitle='Document Verification';
require_once __DIR__.'/../includes/header.php';
Security::requireAdmin();

$db=new Database();

// Handle verification
if(is_post_request()){
    $action=get_post('action');
    $docId=get_post('doc_id');
    $reason=Security::sanitizeString(get_post('reason',''));
    
    if($action==='approve'){
        $db->query("UPDATE verification_documents SET verification_status='approved',verified_at=NOW() WHERE document_id=:id");
        $db->bind(':id',$docId);
        if($db->execute()){
            redirect_with_success(APP_URL.'/admin/verify-documents.php','Document approved');
        }
    }elseif($action==='reject'){
        $db->query("UPDATE verification_documents SET verification_status='rejected',rejection_reason=:reason,verified_at=NOW() WHERE document_id=:id");
        $db->bind(':id',$docId);
        $db->bind(':reason',$reason);
        if($db->execute()){
            redirect_with_success(APP_URL.'/admin/verify-documents.php','Document rejected');
        }
    }
}

// Get pending documents - ORDER BY user THEN type so both docs show together
$db->query("SELECT vd.*,u.full_name,u.email,u.user_type,u.citizenship,u.id_number,u.passport_number,u.country,u.business_age FROM verification_documents vd JOIN users u ON vd.user_id=u.user_id WHERE vd.verification_status='pending' ORDER BY vd.user_id, vd.document_type, vd.uploaded_at DESC");
$documents=$db->fetchAll();

$csrfToken=Security::generateCSRFToken();
?>

<div class="container my-5">
<h2 class="fw-bold mb-4"><i class="fas fa-id-card text-info"></i> Document Verification (<?php echo count($documents); ?>)</h2>

<?php if(empty($documents)): ?>
    <div class="alert alert-info text-center">
        <i class="fas fa-check-circle fa-3x mb-3"></i>
        <h5>No Pending Documents</h5>
        <p>All verification documents have been processed.</p>
    </div>
<?php else: ?>
    <div class="row">
        <?php foreach($documents as $doc): ?>
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">
                        <?php echo Security::clean($doc['full_name']); ?>
                        <span class="badge bg-light text-dark float-end"><?php echo ucfirst($doc['user_type']); ?></span>
                    </h6>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <?php 
                        // Use file_path instead of document_path
                        $filePath = APP_URL.'/'.$doc['file_path'];
                        $fileExtension = strtolower(pathinfo($doc['file_path'], PATHINFO_EXTENSION));
                        
                        // Check if it's an image or PDF
                        if(in_array($fileExtension, ['jpg','jpeg','png','gif','webp'])): 
                        ?>
                            <img src="<?php echo $filePath; ?>" class="img-fluid rounded" style="max-height:300px" alt="Document">
                        <?php elseif($fileExtension === 'pdf'): ?>
                            <!-- FIXED: Removed 'alert' class to prevent auto-dismiss -->
                            <div class="border border-secondary rounded p-3 bg-light">
                                <i class="fas fa-file-pdf fa-3x text-danger mb-2"></i>
                                <p class="mb-2"><strong>PDF Document</strong></p>
                                <a href="<?php echo $filePath; ?>" target="_blank" class="btn btn-sm btn-primary">
                                    <i class="fas fa-external-link-alt"></i> Open PDF
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="border border-warning rounded p-3 bg-light">
                                <i class="fas fa-file fa-3x mb-2 text-warning"></i>
                                <p class="mb-2"><strong>File: <?php echo strtoupper($fileExtension); ?></strong></p>
                                <a href="<?php echo $filePath; ?>" target="_blank" class="btn btn-sm btn-primary">
                                    <i class="fas fa-download"></i> Download File
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- FIXED: Proper document type names -->
                    <p class="mb-1"><strong>Type:</strong> 
                        <?php 
                        // Document type name mapping
                        $docTypeNames = [
                            'id_document' => 'ID Document',
                            'proof_of_address' => 'Proof of Address',
                            'cipc_document' => 'CIPC Registration Document',
                            'sars_vat_certificate' => 'SARS/VAT Certificate',
                            'bank_statement' => 'Business Bank Statement'
                        ];
                        echo $docTypeNames[$doc['document_type']] ?? ucfirst(str_replace('_',' ',$doc['document_type']));
                        ?>
                    </p>
                    
                    <p class="mb-1"><strong>Email:</strong> <?php echo Security::clean($doc['email']); ?></p>
                    <p class="mb-1"><strong>Uploaded:</strong> <?php echo time_ago($doc['uploaded_at']); ?></p>
                    
                    <!-- NEW PHASE 2: Show citizenship info -->
                    <?php if($doc['citizenship']): ?>
                    <hr class="my-2">
                    <p class="mb-1"><strong>Citizenship:</strong> 
                        <span class="badge bg-<?php echo $doc['citizenship']==='citizen'?'success':'info'; ?>">
                            <?php echo $doc['citizenship']==='citizen'?'SA Citizen':'Foreign National'; ?>
                        </span>
                    </p>
                    <?php endif; ?>
                    
                    <?php if($doc['citizenship']==='citizen' && $doc['id_number']): ?>
                    <p class="mb-1"><strong>SA ID:</strong> <?php echo Security::clean($doc['id_number']); ?></p>
                    <?php elseif($doc['citizenship']==='foreign'): ?>
                    <?php if($doc['passport_number']): ?><p class="mb-1"><strong>Passport:</strong> <?php echo Security::clean($doc['passport_number']); ?></p><?php endif; ?>
                    <?php if($doc['country']): ?><p class="mb-1"><strong>Country:</strong> <?php echo Security::clean($doc['country']); ?></p><?php endif; ?>
                    <?php endif; ?>
                    
                    <?php if($doc['business_age']): ?>
                    <p class="mb-1"><strong>Business Age:</strong> 
                        <span class="badge bg-secondary"><?php echo $doc['business_age']; ?> months</span>
                    </p>
                    <?php endif; ?>
                    
                    <div class="d-grid gap-2 mt-3">
                        <form method="POST" class="d-inline">
                            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                            <input type="hidden" name="doc_id" value="<?php echo $doc['document_id']; ?>">
                            <input type="hidden" name="action" value="approve">
                            <button type="submit" class="btn btn-success w-100">
                                <i class="fas fa-check"></i> Approve Document
                            </button>
                        </form>
                        <button class="btn btn-danger" onclick="showRejectModal(<?php echo $doc['document_id']; ?>)">
                            <i class="fas fa-times"></i> Reject Document
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Reject Document</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                    <input type="hidden" name="doc_id" id="rejectDocId">
                    <input type="hidden" name="action" value="reject">
                    
                    <div class="mb-3">
                        <label class="fw-bold">Reason for Rejection *</label>
                        <textarea class="form-control" name="reason" rows="3" required placeholder="e.g., Image unclear, wrong document type..."></textarea>
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
function showRejectModal(docId){
    document.getElementById('rejectDocId').value=docId;
    new bootstrap.Modal(document.getElementById('rejectModal')).show();
}
</script>

<?php require_once __DIR__.'/../includes/footer.php'; ?>
