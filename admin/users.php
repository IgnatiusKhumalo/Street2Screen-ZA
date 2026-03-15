<?php
$pageTitle='All Users';
require_once __DIR__.'/../includes/header.php';
Security::requireAdmin();

$db=new Database();

// Get filter parameters
$filterType = get_get('type', '');
$filterStatus = get_get('status', '');
$filterVerified = get_get('verified', '');
$search = get_get('search', '');

// Build query
$query = "SELECT u.user_id, u.full_name, u.email, u.phone, u.user_type, u.citizenship, u.business_age,
          u.account_status, u.email_verified, u.created_at,
          (SELECT COUNT(*) FROM orders WHERE buyer_id = u.user_id) as order_count,
          (SELECT COUNT(*) FROM products WHERE seller_id = u.user_id) as product_count
          FROM users u WHERE 1=1";

$params = [];

if($filterType) {
    $query .= " AND u.user_type = :type";
    $params[':type'] = $filterType;
}

if($filterStatus) {
    $query .= " AND u.account_status = :status";
    $params[':status'] = $filterStatus;
}

if($filterVerified !== '') {
    $query .= " AND u.email_verified = :verified";
    $params[':verified'] = (int)$filterVerified;
}

if($search) {
    $query .= " AND (u.full_name LIKE :search OR u.email LIKE :search OR u.phone LIKE :search)";
    $params[':search'] = '%' . $search . '%';
}

$query .= " ORDER BY u.created_at DESC";

$db->query($query);
foreach($params as $key => $value) {
    $db->bind($key, $value);
}
$users = $db->fetchAll();

// Count by type
$db->query("SELECT user_type, COUNT(*) as count FROM users GROUP BY user_type");
$typeCounts = [];
foreach($db->fetchAll() as $row) {
    $typeCounts[$row['user_type']] = $row['count'];
}
?>

<div class="container-fluid my-4">

<!-- Header with filters -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="fw-bold">
                <i class="fas fa-users text-primary"></i> 
                All Users (<?php echo count($users); ?>)
            </h2>
            <div>
                <a href="<?php echo APP_URL; ?>/admin/dashboard.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </div>
        
        <!-- Type badges -->
        <div class="mb-3">
            <a href="?" class="badge <?php echo !$filterType ? 'bg-primary' : 'bg-secondary'; ?> text-decoration-none me-2 p-2">
                All (<?php echo array_sum($typeCounts); ?>)
            </a>
            <?php foreach(['buyer', 'seller', 'both', 'moderator', 'admin'] as $type): ?>
            <a href="?type=<?php echo $type; ?>" 
               class="badge <?php echo $filterType === $type ? 'bg-primary' : 'bg-secondary'; ?> text-decoration-none me-2 p-2">
                <?php echo ucfirst($type); ?> (<?php echo $typeCounts[$type] ?? 0; ?>)
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <input type="text" class="form-control" name="search" 
                       placeholder="Search name, email, phone..." 
                       value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div class="col-md-2">
                <select class="form-select" name="status">
                    <option value="">All Status</option>
                    <option value="active" <?php echo $filterStatus==='active'?'selected':''; ?>>Active</option>
                    <option value="suspended" <?php echo $filterStatus==='suspended'?'selected':''; ?>>Suspended</option>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select" name="verified">
                    <option value="">All Verified</option>
                    <option value="1" <?php echo $filterVerified==='1'?'selected':''; ?>>Verified</option>
                    <option value="0" <?php echo $filterVerified==='0'?'selected':''; ?>>Not Verified</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-filter"></i> Filter
                </button>
            </div>
            <div class="col-md-2">
                <a href="?" class="btn btn-outline-secondary w-100">
                    <i class="fas fa-redo"></i> Clear
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Users table -->
<div class="card shadow">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Type</th>
                        <th>Citizenship</th>
                        <th>Business</th>
                        <th>Status</th>
                        <th>Verified</th>
                        <th>Registered</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($users)): ?>
                    <tr>
                        <td colspan="11" class="text-center text-muted py-4">
                            <i class="fas fa-search fa-3x mb-3"></i>
                            <p>No users found matching your filters</p>
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach($users as $u): ?>
                    <tr>
                        <td><?php echo $u['user_id']; ?></td>
                        <td>
                            <strong><?php echo Security::clean($u['full_name']); ?></strong>
                            <br>
                            <small class="text-muted">
                                Orders: <?php echo $u['order_count']; ?> | 
                                Products: <?php echo $u['product_count']; ?>
                            </small>
                        </td>
                        <td><?php echo Security::clean($u['email']); ?></td>
                        <td><?php echo Security::clean($u['phone']); ?></td>
                        <td>
                            <span class="badge bg-<?php 
                                echo $u['user_type']==='admin'?'danger':
                                    ($u['user_type']==='moderator'?'warning':
                                    ($u['user_type']==='seller'?'info':
                                    ($u['user_type']==='both'?'purple':'primary')));
                            ?>">
                                <?php echo ucfirst($u['user_type']); ?>
                            </span>
                        </td>
                        <td>
                            <?php if($u['citizenship']): ?>
                                <span class="badge bg-<?php echo $u['citizenship']==='citizen'?'success':'info'; ?>">
                                    <?php echo $u['citizenship']==='citizen'?'SA':'Foreign'; ?>
                                </span>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if($u['business_age']): ?>
                                <span class="badge bg-secondary"><?php echo $u['business_age']; ?></span>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if($u['account_status']==='active'): ?>
                                <span class="badge bg-success">Active</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Suspended</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if($u['email_verified']): ?>
                                <i class="fas fa-check-circle text-success"></i>
                            <?php else: ?>
                                <i class="fas fa-times-circle text-danger"></i>
                            <?php endif; ?>
                        </td>
                        <td>
                            <small><?php echo format_date($u['created_at']); ?></small>
                        </td>
                        <td>
                            <a href="<?php echo APP_URL; ?>/admin/manage-user.php?id=<?php echo $u['user_id']; ?>" 
                               class="btn btn-sm btn-info" 
                               title="Manage User">
                                <i class="fas fa-cog"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</div>

<?php require_once __DIR__.'/../includes/footer.php'; ?>
