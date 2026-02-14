<?php
/**
 * ============================================
 * COMMON FOOTER FILE
 * ============================================
 * Project: Street2Screen ZA
 * Purpose: Shared footer for all pages
 * Author: Ignatius Mayibongwe Khumalo
 * Date: February 2026
 * ============================================
 */
?>
    </main>
    <!-- Main Content Ends Here -->
    
    <!-- Footer -->
    <footer class="mt-5">
        <div class="container">
            <div class="row">
                <!-- About Section -->
                <div class="col-md-4 mb-4">
                    <h5>Street2Screen ZA</h5>
                    <p class="text-light">Bringing Kasi To Your Screen</p>
                    <p class="text-light small">
                        Empowering township entrepreneurs across South Africa with 
                        secure, accessible digital marketplace infrastructure.
                    </p>
                </div>
                
                <!-- Quick Links -->
                <div class="col-md-4 mb-4">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="<?php echo APP_URL; ?>/index.php">Home</a></li>
                        <li><a href="<?php echo APP_URL; ?>/products/index.php">Browse Products</a></li>
                        <li><a href="<?php echo APP_URL; ?>/about.php">About Us</a></li>
                        <li><a href="<?php echo APP_URL; ?>/contact.php">Contact</a></li>
                    </ul>
                </div>
                
                <!-- Contact Info -->
                <div class="col-md-4 mb-4">
                    <h5>Academic Project</h5>
                    <p class="text-light small mb-1">
                        <strong>Student:</strong> Ignatius Mayibongwe Khumalo
                    </p>
                    <p class="text-light small mb-1">
                        <strong>Institution:</strong> Eduvos Private Institution
                    </p>
                    <p class="text-light small mb-1">
                        <strong>Course:</strong> ITECA3-12 Initial Project
                    </p>
                    <p class="text-light small mb-0">
                        <strong>Year:</strong> 2026
                    </p>
                </div>
            </div>
            
            <hr style="border-color: rgba(255,255,255,0.2);">
            
            <!-- Copyright -->
            <div class="row">
                <div class="col-12 text-center">
                    <p class="mb-0 text-light">
                        &copy; <?php echo date('Y'); ?> Street2Screen ZA. 
                        All Rights Reserved. | 
                        <a href="<?php echo APP_URL; ?>/privacy.php">Privacy Policy</a> | 
                        <a href="<?php echo APP_URL; ?>/terms.php">Terms of Service</a>
                    </p>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Bootstrap 5 JavaScript Bundle (includes Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Our Custom JavaScript -->
    <script src="<?php echo APP_URL; ?>/assets/js/main.js"></script>
    
</body>
</html>
