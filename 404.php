<?php
http_response_code(404);
$pageTitle='404 - Page Not Found';
require_once __DIR__.'/includes/header.php';
?>

<div class="container my-5">
<div class="row justify-content-center">
<div class="col-md-6 text-center">
    <i class="fas fa-exclamation-triangle text-warning" style="font-size:120px"></i>
    <h1 class="display-1 fw-bold text-primary">404</h1>
    <h2 class="mb-3">Oops! Page Not Found</h2>
    <p class="lead text-muted mb-4">The page you're looking for doesn't exist or has been moved.</p>
    <div class="d-flex gap-2 justify-content-center">
        <a href="<?php echo APP_URL; ?>/index.php" class="btn btn-primary btn-lg">
            <i class="fas fa-home"></i> Go Home
        </a>
        <a href="<?php echo APP_URL; ?>/products/index.php" class="btn btn-outline-primary btn-lg">
            <i class="fas fa-shopping-bag"></i> Browse Products
        </a>
    </div>
</div>
</div>
</div>

<?php require_once __DIR__.'/includes/footer.php'; ?>
