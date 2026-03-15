<?php
$pageTitle='Admin Logs';
require_once __DIR__.'/../includes/header.php';
Security::requireAdmin();

$db=new Database();

// Filters
$actionType=get_get('action_type','');
$targetType=get_get('target_type','');

$query="SELECT l.*,u.full_name as admin_name FROM admin_logs l JOIN users u ON l.admin_id=u.user_id WHERE 1=1";
$params=[];

if(!empty($actionType)){
    $query.=" AND l.action_type=:action";
    $params[':action']=$actionType;
}
if(!empty($targetType)){
    $query.=" AND l.target_type=:target";
    $params[':target']=$targetType;
}

$query.=" ORDER BY l.timestamp DESC LIMIT 100";
$db->query($query);
foreach($params as $k=>$v) $db->bind($k,$v);
$logs=$db->fetchAll();

// Get distinct action types for filter
$db->query("SELECT DISTINCT action_type FROM admin_logs ORDER BY action_type");
$actionTypes=$db->fetchAll();
?>

<div class="container my-5">
<h2 class="fw-bold mb-4"><i class="fas fa-history text-primary"></i> Admin Audit Logs</h2>

<!-- Filters -->
<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <select class="form-select" name="action_type">
                    <option value="">All Actions</option>
                    <?php foreach($actionTypes as $a): ?>
                    <option value="<?php echo $a['action_type']; ?>" <?php echo $actionType===$a['action_type']?'selected':''; ?>>
                        <?php echo ucfirst(str_replace('_',' ',$a['action_type'])); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <select class="form-select" name="target_type">
                    <option value="">All Targets</option>
                    <option value="user" <?php echo $targetType==='user'?'selected':''; ?>>User</option>
                    <option value="product" <?php echo $targetType==='product'?'selected':''; ?>>Product</option>
                    <option value="dispute" <?php echo $targetType==='dispute'?'selected':''; ?>>Dispute</option>
                    <option value="order" <?php echo $targetType==='order'?'selected':''; ?>>Order</option>
                </select>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-filter"></i> Filter
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card shadow">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Time</th>
                        <th>Admin</th>
                        <th>Action</th>
                        <th>Target</th>
                        <th>Details</th>
                        <th>IP</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($logs as $log): ?>
                    <tr>
                        <td><small><?php echo time_ago($log['timestamp']); ?></small></td>
                        <td><?php echo Security::clean($log['admin_name']); ?></td>
                        <td>
                            <span class="badge bg-primary">
                                <?php echo ucfirst(str_replace('_',' ',$log['action_type'])); ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-secondary">
                                <?php echo ucfirst($log['target_type']); ?> #<?php echo $log['target_id']; ?>
                            </span>
                        </td>
                        <td>
                            <small class="text-muted">
                                <?php
                                if($log['action_details']){
                                    $details=json_decode($log['action_details'],true);
                                    echo is_array($details)?implode(', ',array_map(
                                        fn($k,$v)=>"$k: $v",
                                        array_keys($details),
                                        array_values($details)
                                    )):Security::clean($log['action_details']);
                                }
                                ?>
                            </small>
                        </td>
                        <td><small class="text-muted"><?php echo $log['ip_address']; ?></small></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if(empty($logs)): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">No logs found</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</div>

<?php require_once __DIR__.'/../includes/footer.php'; ?>
