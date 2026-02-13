-- ============================================================================
-- STREET2SCREEN ZA - DATABASE SCHEMA
-- ============================================================================
-- Project: Street2Screen ZA C2C E-Commerce Platform
-- Student: Ignatius Mayibongwe Khumalo
-- Institution: Eduvos Private Institution
-- Course: ITECA3-12 Initial Project
-- Date: February 2026
-- Database: street2screen_db (local) / if0_41132529_street2screen (production)
-- ============================================================================

-- Drop database if exists (CAREFUL IN PRODUCTION!)
-- DROP DATABASE IF EXISTS street2screen_db;

-- Create database
CREATE DATABASE IF NOT EXISTS street2screen_db
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE street2screen_db;

-- ============================================================================
-- TABLE 1: USERS
-- Purpose: All platform users (buyers, sellers, moderators, admins)
-- ============================================================================

CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    user_type ENUM('buyer', 'seller', 'moderator', 'admin') NOT NULL DEFAULT 'buyer',
    phone VARCHAR(15) NULL,
    address TEXT NULL,
    township VARCHAR(100) NULL COMMENT 'e.g., Soweto, Alexandra, Katlehong',
    city VARCHAR(100) NULL DEFAULT 'Johannesburg',
    province VARCHAR(50) NULL DEFAULT 'Gauteng',
    postal_code VARCHAR(10) NULL,
    profile_picture VARCHAR(255) NULL,
    email_verified BOOLEAN DEFAULT 0,
    verification_token VARCHAR(64) NULL,
    token_expiry DATETIME NULL,
    remember_token VARCHAR(255) NULL,
    remember_expiry DATETIME NULL,
    account_status ENUM('active', 'suspended', 'deleted') NOT NULL DEFAULT 'active',
    suspension_reason TEXT NULL,
    suspension_until DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    
    INDEX idx_user_type (user_type),
    INDEX idx_account_status (account_status),
    INDEX idx_email_verified (email_verified)
) ENGINE=InnoDB COMMENT='All platform users with RBAC support';

-- ============================================================================
-- TABLE 2: CATEGORIES
-- Purpose: Fixed product categories (5 categories)
-- ============================================================================

CREATE TABLE categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(50) NOT NULL UNIQUE,
    description TEXT NULL,
    icon_class VARCHAR(50) NULL COMMENT 'Font Awesome icon class',
    display_order INT DEFAULT 0,
    active BOOLEAN DEFAULT 1,
    
    INDEX idx_active (active)
) ENGINE=InnoDB COMMENT='Fixed product categories';

-- Insert fixed categories
INSERT INTO categories (category_id, category_name, description, icon_class, display_order) VALUES
(1, 'Clothing & Fashion', 'Traditional and modern clothing, accessories, shoes', 'fa-tshirt', 1),
(2, 'Electronics & Accessories', 'Phones, laptops, chargers, headphones', 'fa-laptop', 2),
(3, 'Home & Kitchen', 'Furniture, appliances, kitchenware, decor', 'fa-home', 3),
(4, 'Food & Drinks', 'Traditional foods, snacks, beverages', 'fa-utensils', 4),
(5, 'Handmade & Crafts', 'Beadwork, art, handmade goods, crafts', 'fa-palette', 5);

-- ============================================================================
-- TABLE 3: PRODUCTS
-- Purpose: Product listings created by sellers
-- ============================================================================

CREATE TABLE products (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    seller_id INT NOT NULL,
    category_id INT NOT NULL,
    product_name VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    stock_quantity INT NOT NULL DEFAULT 1,
    location VARCHAR(100) NOT NULL COMMENT 'Township/City where product is located',
    `condition` ENUM('new', 'like_new', 'good', 'fair') NOT NULL,
    status ENUM('active', 'sold', 'suspended', 'deleted') NOT NULL DEFAULT 'active',
    view_count INT DEFAULT 0,
    featured BOOLEAN DEFAULT 0,
    featured_until DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (seller_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(category_id),
    
    INDEX idx_seller (seller_id),
    INDEX idx_category (category_id),
    INDEX idx_status (status),
    INDEX idx_price (price),
    INDEX idx_featured (featured, featured_until),
    FULLTEXT INDEX idx_search (product_name, description)
) ENGINE=InnoDB COMMENT='Product listings';

-- ============================================================================
-- TABLE 4: PRODUCT_IMAGES
-- Purpose: Product photos (3-5 images per product)
-- ============================================================================

CREATE TABLE product_images (
    image_id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL COMMENT '800x800px detail image',
    thumbnail_path VARCHAR(255) NULL COMMENT '300x300px thumbnail',
    is_primary BOOLEAN DEFAULT 0,
    display_order INT DEFAULT 0,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE,
    
    INDEX idx_product (product_id),
    INDEX idx_primary (product_id, is_primary),
    INDEX idx_order (display_order)
) ENGINE=InnoDB COMMENT='Product images (3-5 per product)';

-- ============================================================================
-- TABLE 5: ORDERS
-- Purpose: All purchase transactions
-- ============================================================================

CREATE TABLE orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    buyer_id INT NOT NULL,
    seller_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    unit_price DECIMAL(10,2) NOT NULL COMMENT 'Price at time of purchase',
    total_amount DECIMAL(10,2) NOT NULL COMMENT 'unit_price * quantity',
    payment_method ENUM('payfast', 'cod', 'eft', 'manual') NOT NULL DEFAULT 'payfast',
    payment_status ENUM('pending', 'paid', 'failed', 'refunded') NOT NULL DEFAULT 'pending',
    delivery_address TEXT NOT NULL,
    delivery_method ENUM('collection', 'courier', 'pudo') DEFAULT 'collection',
    delivery_status ENUM('pending', 'shipped', 'delivered') DEFAULT 'pending',
    tracking_number VARCHAR(100) NULL,
    buyer_notes TEXT NULL,
    seller_notes TEXT NULL,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    payment_date TIMESTAMP NULL,
    shipped_date TIMESTAMP NULL,
    delivery_date TIMESTAMP NULL,
    
    FOREIGN KEY (buyer_id) REFERENCES users(user_id),
    FOREIGN KEY (seller_id) REFERENCES users(user_id),
    FOREIGN KEY (product_id) REFERENCES products(product_id),
    
    INDEX idx_buyer (buyer_id),
    INDEX idx_seller (seller_id),
    INDEX idx_product (product_id),
    INDEX idx_payment_status (payment_status),
    INDEX idx_delivery_status (delivery_status),
    INDEX idx_order_date (order_date)
) ENGINE=InnoDB COMMENT='Purchase transactions';

-- ============================================================================
-- TABLE 6: TRANSACTIONS
-- Purpose: Financial records with platform fee calculations
-- ============================================================================

CREATE TABLE transactions (
    transaction_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL UNIQUE,
    payfast_payment_id VARCHAR(100) NULL COMMENT 'PayFast transaction ID',
    transaction_amount DECIMAL(10,2) NOT NULL,
    platform_fee DECIMAL(10,2) NOT NULL COMMENT '5% of transaction_amount',
    seller_payout DECIMAL(10,2) NOT NULL COMMENT 'transaction_amount - platform_fee',
    transaction_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    payout_status ENUM('pending', 'processed', 'failed') DEFAULT 'pending',
    payout_date TIMESTAMP NULL,
    payout_reference VARCHAR(100) NULL,
    
    FOREIGN KEY (order_id) REFERENCES orders(order_id),
    
    INDEX idx_payfast (payfast_payment_id),
    INDEX idx_payout_status (payout_status),
    INDEX idx_transaction_date (transaction_date)
) ENGINE=InnoDB COMMENT='Financial transactions with platform fees';

-- ============================================================================
-- TABLE 7: REVIEWS
-- Purpose: Buyer ratings and feedback for sellers
-- ============================================================================

CREATE TABLE reviews (
    review_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL UNIQUE COMMENT 'Ensures verified purchase',
    reviewer_id INT NOT NULL COMMENT 'Buyer who wrote review',
    seller_id INT NOT NULL COMMENT 'Seller being reviewed',
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    review_text TEXT NOT NULL,
    seller_response TEXT NULL,
    response_date TIMESTAMP NULL,
    helpful_count INT DEFAULT 0,
    flagged BOOLEAN DEFAULT 0,
    flag_reason TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (order_id) REFERENCES orders(order_id),
    FOREIGN KEY (reviewer_id) REFERENCES users(user_id),
    FOREIGN KEY (seller_id) REFERENCES users(user_id),
    
    INDEX idx_seller (seller_id, rating),
    INDEX idx_created (created_at),
    INDEX idx_flagged (flagged)
) ENGINE=InnoDB COMMENT='Seller reviews and ratings';

-- ============================================================================
-- TABLE 8: CONVERSATIONS
-- Purpose: Message thread containers
-- ============================================================================

CREATE TABLE conversations (
    conversation_id INT AUTO_INCREMENT PRIMARY KEY,
    buyer_id INT NOT NULL,
    seller_id INT NOT NULL,
    product_id INT NULL COMMENT 'Product being discussed',
    status ENUM('active', 'archived') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_message_at TIMESTAMP NULL,
    
    FOREIGN KEY (buyer_id) REFERENCES users(user_id),
    FOREIGN KEY (seller_id) REFERENCES users(user_id),
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE SET NULL,
    
    UNIQUE KEY unique_conversation (buyer_id, seller_id, product_id),
    INDEX idx_buyer (buyer_id, status),
    INDEX idx_seller (seller_id, status),
    INDEX idx_last_message (last_message_at)
) ENGINE=InnoDB COMMENT='Message conversation threads';

-- ============================================================================
-- TABLE 9: MESSAGES
-- Purpose: Individual chat messages
-- ============================================================================

CREATE TABLE messages (
    message_id INT AUTO_INCREMENT PRIMARY KEY,
    conversation_id INT NOT NULL,
    sender_id INT NOT NULL,
    message_text TEXT NOT NULL,
    attachment_path VARCHAR(255) NULL,
    read_status BOOLEAN DEFAULT 0,
    read_at TIMESTAMP NULL,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_by_sender BOOLEAN DEFAULT 0,
    deleted_by_receiver BOOLEAN DEFAULT 0,
    
    FOREIGN KEY (conversation_id) REFERENCES conversations(conversation_id) ON DELETE CASCADE,
    FOREIGN KEY (sender_id) REFERENCES users(user_id),
    
    INDEX idx_conversation (conversation_id, sent_at),
    INDEX idx_read (read_status)
) ENGINE=InnoDB COMMENT='Individual messages';

-- ============================================================================
-- TABLE 10: VERIFICATION_DOCUMENTS
-- Purpose: Seller identity verification uploads
-- ============================================================================

CREATE TABLE verification_documents (
    document_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE COMMENT 'One document set per seller',
    document_path VARCHAR(255) NOT NULL COMMENT 'Encrypted storage path',
    document_type ENUM('id_book', 'drivers_license', 'passport', 'business_reg') NOT NULL,
    verification_status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    rejection_reason TEXT NULL,
    reviewed_by INT NULL COMMENT 'Admin who reviewed',
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    reviewed_at TIMESTAMP NULL,
    
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (reviewed_by) REFERENCES users(user_id),
    
    INDEX idx_status (verification_status),
    INDEX idx_uploaded (uploaded_at)
) ENGINE=InnoDB COMMENT='Seller verification documents';

-- ============================================================================
-- TABLE 11: DISPUTES
-- Purpose: Buyer complaints about orders
-- ============================================================================

CREATE TABLE disputes (
    dispute_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL UNIQUE COMMENT 'One dispute per order',
    reported_by INT NOT NULL COMMENT 'Buyer filing dispute',
    dispute_reason ENUM('non_delivery', 'not_as_described', 'damaged', 'seller_unresponsive', 'other') NOT NULL,
    description TEXT NOT NULL,
    evidence_paths TEXT NULL COMMENT 'JSON array of file paths',
    status ENUM('open', 'investigating', 'resolved', 'closed') DEFAULT 'open',
    resolution_outcome ENUM('buyer_favour', 'seller_favour', 'mutual', 'insufficient') NULL,
    resolution_notes TEXT NULL,
    resolved_by INT NULL COMMENT 'Moderator who resolved',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    resolved_at TIMESTAMP NULL,
    
    FOREIGN KEY (order_id) REFERENCES orders(order_id),
    FOREIGN KEY (reported_by) REFERENCES users(user_id),
    FOREIGN KEY (resolved_by) REFERENCES users(user_id),
    
    INDEX idx_status (status),
    INDEX idx_created (created_at)
) ENGINE=InnoDB COMMENT='Order disputes and resolutions';

-- ============================================================================
-- TABLE 12: ADMIN_LOGS
-- Purpose: Audit trail of administrative actions
-- ============================================================================

CREATE TABLE admin_logs (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL COMMENT 'Admin performing action',
    action_type VARCHAR(50) NOT NULL COMMENT 'e.g., approve_seller, suspend_user',
    target_type VARCHAR(50) NOT NULL COMMENT 'e.g., user, product, dispute',
    target_id INT NOT NULL COMMENT 'ID of affected entity',
    action_details TEXT NULL COMMENT 'JSON with additional info',
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (admin_id) REFERENCES users(user_id),
    
    INDEX idx_admin (admin_id, timestamp),
    INDEX idx_target (target_type, target_id),
    INDEX idx_timestamp (timestamp)
) ENGINE=InnoDB COMMENT='Administrative action audit log';

-- ============================================================================
-- TABLE 13: TRANSLATIONS
-- Purpose: Multi-language UI text (11 South African languages)
-- ============================================================================

CREATE TABLE translations (
    translation_id INT AUTO_INCREMENT PRIMARY KEY,
    language_code VARCHAR(5) NOT NULL COMMENT 'en, af, zu, xh, st, nso, tn, ss, nr, ve, ts',
    translation_key VARCHAR(100) NOT NULL COMMENT 'e.g., btn_login, nav_home',
    translation_text TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY unique_translation (language_code, translation_key),
    INDEX idx_language (language_code)
) ENGINE=InnoDB COMMENT='Multi-language translations';

-- Insert sample English translations (others added later)
INSERT INTO translations (language_code, translation_key, translation_text) VALUES
('en', 'site_title', 'Street2Screen ZA'),
('en', 'site_tagline', 'Bringing Kasi To Your Screen'),
('en', 'btn_login', 'Login'),
('en', 'btn_register', 'Register'),
('en', 'btn_logout', 'Logout'),
('en', 'nav_home', 'Home'),
('en', 'nav_products', 'Products'),
('en', 'nav_sell', 'Sell'),
('en', 'nav_messages', 'Messages'),
('en', 'nav_account', 'My Account');

-- ============================================================================
-- TABLE 14: PASSWORD_RESETS
-- Purpose: Temporary tokens for password recovery
-- ============================================================================

CREATE TABLE password_resets (
    reset_id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL,
    reset_token VARCHAR(64) NOT NULL UNIQUE,
    token_expiry DATETIME NOT NULL COMMENT '24 hours from creation',
    used BOOLEAN DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_email (email, used),
    INDEX idx_expiry (token_expiry)
) ENGINE=InnoDB COMMENT='Password reset tokens';

-- ============================================================================
-- TABLE 15: SESSIONS (OPTIONAL)
-- Purpose: Track active user sessions
-- ============================================================================

CREATE TABLE sessions (
    session_id VARCHAR(128) PRIMARY KEY,
    user_id INT NULL,
    session_data TEXT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    
    INDEX idx_user (user_id),
    INDEX idx_activity (last_activity)
) ENGINE=InnoDB COMMENT='User session tracking';

-- ============================================================================
-- STORED PROCEDURES & TRIGGERS
-- ============================================================================

-- Trigger: Update product stock when order is placed
DELIMITER //
CREATE TRIGGER after_order_insert
AFTER INSERT ON orders
FOR EACH ROW
BEGIN
    UPDATE products
    SET stock_quantity = stock_quantity - NEW.quantity
    WHERE product_id = NEW.product_id;
    
    -- If stock reaches 0, mark as sold
    UPDATE products
    SET status = 'sold'
    WHERE product_id = NEW.product_id AND stock_quantity <= 0;
END//
DELIMITER ;

-- Trigger: Calculate platform fee when transaction created
DELIMITER //
CREATE TRIGGER before_transaction_insert
BEFORE INSERT ON transactions
FOR EACH ROW
BEGIN
    SET NEW.platform_fee = NEW.transaction_amount * 0.05;
    SET NEW.seller_payout = NEW.transaction_amount - NEW.platform_fee;
END//
DELIMITER ;

-- Trigger: Update conversation last_message_at when message sent
DELIMITER //
CREATE TRIGGER after_message_insert
AFTER INSERT ON messages
FOR EACH ROW
BEGIN
    UPDATE conversations
    SET last_message_at = NEW.sent_at
    WHERE conversation_id = NEW.conversation_id;
END//
DELIMITER ;

-- ============================================================================
-- VIEWS FOR COMMON QUERIES
-- ============================================================================

-- View: Active products with seller info
CREATE VIEW view_active_products AS
SELECT 
    p.product_id,
    p.product_name,
    p.description,
    p.price,
    p.stock_quantity,
    p.location,
    p.condition,
    p.view_count,
    p.created_at,
    c.category_name,
    u.full_name AS seller_name,
    u.user_id AS seller_id,
    u.township AS seller_township,
    (SELECT image_path FROM product_images WHERE product_id = p.product_id AND is_primary = 1 LIMIT 1) AS primary_image,
    (SELECT verification_status FROM verification_documents WHERE user_id = u.user_id LIMIT 1) AS seller_verified
FROM products p
JOIN users u ON p.seller_id = u.user_id
JOIN categories c ON p.category_id = c.category_id
WHERE p.status = 'active' AND u.account_status = 'active';

-- View: Seller ratings summary
CREATE VIEW view_seller_ratings AS
SELECT 
    u.user_id,
    u.full_name,
    COUNT(r.review_id) AS total_reviews,
    AVG(r.rating) AS average_rating,
    SUM(CASE WHEN r.rating = 5 THEN 1 ELSE 0 END) AS five_star_count,
    SUM(CASE WHEN r.rating = 4 THEN 1 ELSE 0 END) AS four_star_count,
    SUM(CASE WHEN r.rating = 3 THEN 1 ELSE 0 END) AS three_star_count,
    SUM(CASE WHEN r.rating = 2 THEN 1 ELSE 0 END) AS two_star_count,
    SUM(CASE WHEN r.rating = 1 THEN 1 ELSE 0 END) AS one_star_count
FROM users u
LEFT JOIN reviews r ON u.user_id = r.seller_id
WHERE u.user_type = 'seller'
GROUP BY u.user_id, u.full_name;

-- ============================================================================
-- CREATE DEFAULT ADMIN USER
-- ============================================================================

-- Password: Admin@2026! (hashed with bcrypt)
-- IMPORTANT: Change this password after first login!
INSERT INTO users (full_name, email, password_hash, user_type, email_verified, account_status, created_at) 
VALUES (
    'System Administrator',
    'admin@street2screen.co.za',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- Password: Admin@2026!
    'admin',
    1,
    'active',
    NOW()
);

-- ============================================================================
-- GRANT PERMISSIONS (for production)
-- ============================================================================

-- For InfinityFree deployment:
-- GRANT ALL PRIVILEGES ON if0_41132529_street2screen.* TO 'if0_41132529'@'localhost';
-- FLUSH PRIVILEGES;

-- ============================================================================
-- DATABASE SETUP COMPLETE
-- ============================================================================

-- Verify table creation
SELECT 
    TABLE_NAME,
    TABLE_ROWS,
    CREATE_TIME
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = 'street2screen_db'
ORDER BY TABLE_NAME;

-- Show all foreign keys
SELECT 
    CONSTRAINT_NAME,
    TABLE_NAME,
    COLUMN_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME
FROM information_schema.KEY_COLUMN_USAGE
WHERE REFERENCED_TABLE_SCHEMA = 'street2screen_db'
ORDER BY TABLE_NAME;

-- ============================================================================
-- NEXT STEPS:
-- 1. Run this script in XAMPP phpMyAdmin
-- 2. Verify all 15 tables created
-- 3. Test with sample data inserts
-- 4. Deploy to InfinityFree production database
-- ============================================================================
