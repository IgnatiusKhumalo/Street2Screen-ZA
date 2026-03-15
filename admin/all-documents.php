<?php
$pageTitle='All Documents';
require_once __DIR__.'/../includes/header.php';
Security::requireAdmin();

$db=new Database();

// Get ALL documents with user info
$db->query("SELECT vd.*,u.full_name,u.email,u.user_type,u.citizenship,u.business_age FROM verification_documents vd JOIN users u ON vd.user_id=u.user_id ORDER BY vd.uploaded_at DESC LIMIT 100");
$allDocuments=$db->fetchAll();

// Count by status
$pending = count(array_filter($allDocuments, fn($d) => $d['verification_status'] === 'pending'));
$approved = count(array_filter($allDocuments, fn($d) => $d['verification_status'] === 'approved'));
$rejected = count(array_filter($allDocuments, fn($d) => $d['verification_status'] === 'rejected'));

// Count by document type
$docTypeCounts = [];
foreach($allDocuments as $doc) {
    $docTypeCounts[$doc['document_type']] = ($docTypeCounts[$doc['document_type']] ?? 0) + 1;
}

$docTypeNames = [
    'id_document' => 'ID Document',
    'proof_of_address' => 'Proof of Address',
    'cipc_document' => 'CIPC Registration Document',
    'sars_vat_certificate' => 'SARS/VAT Certificate',
    'bank_statement' => 'Business Bank Statement'
];
?>

<div class="container my-5">
<h2 class="fw-bold mb-4"><i class="fas fa-folder-open text-primary"></i> All Documents (<?php echo count($allDocuments); ?>)</h2>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-white bg-warning text-center">
            <div class="card-body">
                <h3><?php echo $pending; ?></h3>
                <p class="mb-0">Pending</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-success text-center">
            <div class="card-body">
                <h3><?php echo $approved; ?></h3>
                <p class="mb-0">Approved</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-danger text-center">
            <div class="card-body">
                <h3><?php echo $rejected; ?></h3>
                <p class="mb-0">Rejected</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-info text-center">
            <div class="card-body">
                <h3><?php echo count($allDocuments); ?></h3>
                <p class="mb-0">Total</p>
            </div>
        </div>
    </div>
</div>

<!-- Document Type Breakdown -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-chart-pie"></i> Documents by Type</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php foreach($docTypeCounts as $type => $count): 
                        $typeName = $docTypeNames[$type] ?? ucfirst(str_replace('_', ' ', $type));
                    ?>
                    <div class="col-md-4 mb-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h4 class="text-primary"><?php echo $count; ?></h4>
                                <p class="mb-0"><?php echo $typeName; ?></p>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- All Documents Table -->
<div class="card shadow">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Document Type</th>
                        <th>User Type</th>
                        <th>Citizenship</th>
                        <th>Status</th>
                        <th>Uploaded</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($allDocuments as $doc): 
                        $typeName = $docTypeNames[$doc['document_type']] ?? ucfirst(str_replace('_', ' ', $doc['document_type']));
                        $statusColor = $doc['verification_status']==='approved'?'success':($doc['verification_status']==='rejected'?'danger':'warning');
                    ?>
                    <tr>
                        <td><strong>#<?php echo $doc['document_id']; ?></strong></td>
                        <td>
                            <strong><?php echo Security::clean($doc['full_name']); ?></strong><br>
                            <small class="text-muted"><?php echo Security::clean($doc['email']); ?></small>
                        </td>
                        <td>
                            <span class="badge bg-secondary"><?php echo $typeName; ?></span>
                        </td>
                        <td>
                            <span class="badge bg-primary"><?php echo ucfirst($doc['user_type']); ?></span>
                        </td>
                        <td>
                            <?php if($doc['citizenship']): ?>
                                <span class="badge bg-<?php echo $doc['citizenship']==='citizen'?'success':'info'; ?> small">
                                    <?php echo $doc['citizenship']==='citizen'?'SA':'Foreign'; ?>
                                </span>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge bg-<?php echo $statusColor; ?>">
                                <?php echo ucfirst($doc['verification_status']); ?>
                            </span>
                        </td>
                        <td><?php echo time_ago($doc['uploaded_at']); ?></td>
                        <td>
                            <a href="<?php echo APP_URL.'/'.$doc['file_path']; ?>" target="_blank" class="btn btn-sm btn-primary" title="View Document">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="<?php echo APP_URL; ?>/admin/manage-user.php?id=<?php echo $doc['user_id']; ?>" class="btn btn-sm btn-info" title="View User">
                                <i class="fas fa-user"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if(empty($allDocuments)): ?>
<div class="alert alert-info text-center mt-4">
    <i class="fas fa-info-circle fa-3x mb-3"></i>
    <h5>No Documents</h5>
    <p>No documents have been uploaded yet.</p>
</div>
<?php endif; ?>

<!-- Quick Actions -->
<div class="row mt-4">
    <div class="col-md-12 text-center">
        <a href="<?php echo APP_URL; ?>/admin/verify-documents.php" class="btn btn-warning">
            <i class="fas fa-clock"></i> Pending Verification (<?php echo $pending; ?>)
        </a>
        <a href="<?php echo APP_URL; ?>/admin/pending-approvals.php" class="btn btn-info">
            <i class="fas fa-user-check"></i> Pending Approvals
        </a>
        <a href="<?php echo APP_URL; ?>/admin/dashboard.php" class="btn btn-secondary">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>
    </div>
</div>

</div>

<?php require_once __DIR__.'/../includes/footer.php'; ?>
