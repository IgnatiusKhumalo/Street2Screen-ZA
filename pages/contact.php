<?php
$pageTitle='Contact Us';
require_once __DIR__.'/../includes/header.php';

$success='';
$errors=[];

if(is_post_request()){
    $name=Security::sanitizeString(get_post('name'));
    $email=Security::sanitizeEmail(get_post('email'));
    $subject=Security::sanitizeString(get_post('subject'));
    $message=Security::sanitizeString(get_post('message'));
    
    if(empty($name)) $errors[]='Name required';
    if(empty($email)||!Security::validateEmail($email)) $errors[]='Valid email required';
    if(empty($subject)) $errors[]='Subject required';
    if(empty($message)) $errors[]='Message required';
    
    if(empty($errors)){
        $emailer=new Email();
        $emailBody="<h3>New Contact Form Submission</h3>
        <p><strong>From:</strong> $name</p>
        <p><strong>Email:</strong> $email</p>
        <p><strong>Subject:</strong> $subject</p>
        <p><strong>Message:</strong></p>
        <p>$message</p>";
        
        if($emailer->send('im.khumalo.the.coder@gmail.com','Contact Form: '.$subject,$emailBody)){
            $success='Thank you! Your message has been sent.';
        }else{
            $errors[]='Failed to send message. Please try again.';
        }
    }
}

$csrfToken=Security::generateCSRFToken();
?>

<div class="container my-5">
<div class="row">

<div class="col-md-8">
<h2 class="fw-bold mb-4">Contact Us</h2>

<?php if($success): ?>
<div class="alert alert-success">
    <i class="fas fa-check-circle"></i> <?php echo $success; ?>
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
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="fw-bold">Your Name *</label>
                    <input type="text" class="form-control" name="name" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="fw-bold">Your Email *</label>
                    <input type="email" class="form-control" name="email" required>
                </div>
            </div>
            
            <div class="mb-3">
                <label class="fw-bold">Subject *</label>
                <input type="text" class="form-control" name="subject" required>
            </div>
            
            <div class="mb-3">
                <label class="fw-bold">Message *</label>
                <textarea class="form-control" name="message" rows="6" required></textarea>
            </div>
            
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="fas fa-paper-plane"></i> Send Message
            </button>
        </form>
    </div>
</div>
</div>

<div class="col-md-4">
<h4 class="fw-bold mb-4">Get In Touch</h4>

<div class="card shadow-sm mb-3">
    <div class="card-body">
        <h6 class="fw-bold"><i class="fas fa-envelope text-primary"></i> Email</h6>
        <p class="mb-0">im.khumalo.the.coder@gmail.com</p>
    </div>
</div>

<div class="card shadow-sm mb-3">
    <div class="card-body">
        <h6 class="fw-bold"><i class="fas fa-map-marker-alt text-danger"></i> Location</h6>
        <p class="mb-0">Johannesburg, Gauteng<br>South Africa</p>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <h6 class="fw-bold"><i class="fas fa-clock text-success"></i> Response Time</h6>
        <p class="mb-0">We typically respond within 24-48 hours</p>
    </div>
</div>

</div>

</div>
</div>

<?php require_once __DIR__.'/../includes/footer.php'; ?>
