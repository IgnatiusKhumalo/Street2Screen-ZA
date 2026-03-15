<?php
http_response_code(403);
$pageTitle='403 - Access Denied';
require_once __DIR__.'/includes/header.php';
?>

<div class="container my-5">
<div class="row justify-content-center">
<div class="col-md-6 text-center">
    <i class="fas fa-lock text-danger" style="font-size:120px"></i>
    <h1 class="display-1 fw-bold text-danger">403</h1>
    <h2 class="mb-3">Access Denied</h2>
    <p class="lead text-muted mb-4">You don't have permission to access this resource.</p>
    <a href="<?php echo APP_URL; ?>/index.php" class="btn btn-primary btn-lg">
        <i class="fas fa-home"></i> Go Home
    </a>
</div>
</div>
</div>

<?php require_once __DIR__.'/includes/footer.php'; ?>
