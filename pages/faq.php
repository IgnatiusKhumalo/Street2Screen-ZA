<?php
$pageTitle='FAQ';
require_once __DIR__.'/../includes/header.php';
?>

<div class="container my-5">
<h1 class="fw-bold text-center mb-5">Frequently Asked Questions</h1>

<div class="row justify-content-center">
<div class="col-md-10">

<div class="accordion" id="faqAccordion">

<!-- General Questions -->
<h4 class="fw-bold mt-4 mb-3">General Questions</h4>

<div class="accordion-item">
    <h2 class="accordion-header">
        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
            What is Street2Screen ZA?
        </button>
    </h2>
    <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
        <div class="accordion-body">
            Street2Screen ZA is a South African township marketplace connecting buyers and sellers across the country. We focus on empowering township entrepreneurs with digital commerce tools.
        </div>
    </div>
</div>

<div class="accordion-item">
    <h2 class="accordion-header">
        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
            How do I create an account?
        </button>
    </h2>
    <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
        <div class="accordion-body">
            Click "Register" and choose your account type (Buyer, Seller, or Both). Fill in your details including proof of address. Sellers also need ID verification.
        </div>
    </div>
</div>

<!-- Buying Questions -->
<h4 class="fw-bold mt-4 mb-3">Buying & Orders</h4>

<div class="accordion-item">
    <h2 class="accordion-header">
        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
            What payment methods are accepted?
        </button>
    </h2>
    <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
        <div class="accordion-body">
            <strong>Orders under R300:</strong> Cash on Delivery (COD)<br>
            <strong>Orders R300 and above:</strong> PayFast online payment (credit card, EFT, etc.)
        </div>
    </div>
</div>

<div class="accordion-item">
    <h2 class="accordion-header">
        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
            How long does delivery take?
        </button>
    </h2>
    <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
        <div class="accordion-body">
            <strong>Local:</strong> 1-2 business days<br>
            <strong>Regional:</strong> 2-4 business days<br>
            <strong>National:</strong> 3-7 business days<br>
            Delivery times depend on your location and the seller's location.
        </div>
    </div>
</div>

<!-- Selling Questions -->
<h4 class="fw-bold mt-4 mb-3">Selling on Street2Screen</h4>

<div class="accordion-item">
    <h2 class="accordion-header">
        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">
            What are the seller fees?
        </button>
    </h2>
    <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
        <div class="accordion-body">
            We charge a <strong>10% commission</strong> on all successful sales. This covers platform maintenance, payment processing, and dispute resolution.
        </div>
    </div>
</div>

<div class="accordion-item">
    <h2 class="accordion-header">
        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq6">
            How do I list a product?
        </button>
    </h2>
    <div id="faq6" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
        <div class="accordion-body">
            1. Register as a Seller or Both<br>
            2. Get ID verified by admin<br>
            3. Click "Add Product"<br>
            4. Fill in details and upload photos (up to 5)<br>
            5. Set your price and stock quantity<br>
            6. Submit for listing
        </div>
    </div>
</div>

<!-- Disputes -->
<h4 class="fw-bold mt-4 mb-3">Disputes & Resolution</h4>

<div class="accordion-item">
    <h2 class="accordion-header">
        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq7">
            How do I file a dispute?
        </button>
    </h2>
    <div id="faq7" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
        <div class="accordion-body">
            Go to "My Orders", find the delivered order, and click "Dispute". Provide details and evidence. Our moderators will review within 48 hours.
        </div>
    </div>
</div>

<div class="accordion-item">
    <h2 class="accordion-header">
        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq8">
            What can I dispute?
        </button>
    </h2>
    <div id="faq8" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
        <div class="accordion-body">
            • Item not delivered<br>
            • Item not as described<br>
            • Item damaged or defective<br>
            • Seller unresponsive<br>
            • Other issues
        </div>
    </div>
</div>

<!-- Security -->
<h4 class="fw-bold mt-4 mb-3">Security & Privacy</h4>

<div class="accordion-item">
    <h2 class="accordion-header">
        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq9">
            Is my personal information safe?
        </button>
    </h2>
    <div id="faq9" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
        <div class="accordion-body">
            Yes! We use bank-level encryption and never share your personal information with third parties. All payments are processed securely through PayFast.
        </div>
    </div>
</div>

</div>

</div>
</div>

</div>

<?php require_once __DIR__.'/../includes/footer.php'; ?>
