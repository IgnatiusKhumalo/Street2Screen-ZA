-- ============================================================================
-- Street2Screen ZA - CORRECTED FINAL DATABASE
-- Reconciled: Your Schema + All 100 PHP Files
-- Author: Ignatius Mayibongwe Khumalo
-- Date: February 17, 2026
-- ============================================================================

CREATE DATABASE IF NOT EXISTS street2screen_db
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE street2screen_db;

-- ============================================================================
-- TABLE 1: USERS
-- FIX: Added 'both' to user_type ENUM (used in register.php, header.php,
--      products/add.php, orders/sales.php, user/seller-dashboard.php)
-- ============================================================================

CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    user_type ENUM('buyer','seller','both','moderator','admin') NOT NULL DEFAULT 'buyer',
    phone VARCHAR(15) NULL,
    address TEXT NULL,
    township VARCHAR(100) NULL,
    city VARCHAR(100) NULL DEFAULT 'Johannesburg',
    province VARCHAR(50) NULL DEFAULT 'Gauteng',
    postal_code VARCHAR(10) NULL,
    profile_picture VARCHAR(255) NULL,
    email_verified BOOLEAN DEFAULT 0,
    verification_token VARCHAR(64) NULL,
    token_expiry DATETIME NULL,
    remember_token VARCHAR(255) NULL,
    remember_expiry DATETIME NULL,
    account_status ENUM('active','suspended','deleted') NOT NULL DEFAULT 'active',
    suspension_reason TEXT NULL,
    suspension_until DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,

    INDEX idx_user_type (user_type),
    INDEX idx_account_status (account_status),
    INDEX idx_email_verified (email_verified)
) ENGINE=InnoDB;

-- ============================================================================
-- TABLE 2: CATEGORIES
-- No changes - matches categories used in products/add.php, products/edit.php
-- ============================================================================

CREATE TABLE categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(50) NOT NULL UNIQUE,
    description TEXT NULL,
    icon_class VARCHAR(50) NULL,
    display_order INT DEFAULT 0,
    active BOOLEAN DEFAULT 1,

    INDEX idx_active (active)
) ENGINE=InnoDB;

INSERT INTO categories (category_id, category_name, description, icon_class, display_order) VALUES
(1,'Clothing & Fashion','Traditional and modern clothing, accessories, shoes','fa-tshirt',1),
(2,'Electronics & Accessories','Phones, laptops, chargers, headphones','fa-laptop',2),
(3,'Home & Kitchen','Furniture, appliances, kitchenware, decor','fa-home',3),
(4,'Food & Drinks','Traditional foods, snacks, beverages','fa-utensils',4),
(5,'Handmade & Crafts','Beadwork, art, handmade goods, crafts','fa-palette',5);

-- ============================================================================
-- TABLE 3: PRODUCTS
-- No changes - matches products/add.php, products/edit.php, admin/products.php
-- ============================================================================

CREATE TABLE products (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    seller_id INT NOT NULL,
    category_id INT NOT NULL,
    product_name VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    stock_quantity INT NOT NULL DEFAULT 1,
    location VARCHAR(100) NOT NULL,
    `condition` ENUM('new','like_new','good','fair') NOT NULL,
    status ENUM('active','sold','suspended','deleted') NOT NULL DEFAULT 'active',
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
) ENGINE=InnoDB;

-- ============================================================================
-- TABLE 4: PRODUCT_IMAGES
-- No changes - matches products/view.php, products/add.php
-- ============================================================================

CREATE TABLE product_images (
    image_id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    thumbnail_path VARCHAR(255) NULL,
    is_primary BOOLEAN DEFAULT 0,
    display_order INT DEFAULT 0,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE,

    INDEX idx_product (product_id),
    INDEX idx_primary (product_id, is_primary)
) ENGINE=InnoDB;

-- ============================================================================
-- TABLE 5: ORDERS
-- No changes - matches orders/checkout.php, orders/my-orders.php,
--             orders/order-details.php, orders/sales.php, admin/orders.php
-- ============================================================================

CREATE TABLE orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    buyer_id INT NOT NULL,
    seller_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    unit_price DECIMAL(10,2) NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('payfast','cod','eft','manual') NOT NULL DEFAULT 'payfast',
    payment_status ENUM('pending','paid','failed','refunded') NOT NULL DEFAULT 'pending',
    delivery_address TEXT NOT NULL,
    delivery_method ENUM('collection','courier','pudo') DEFAULT 'collection',
    delivery_status ENUM('pending','shipped','delivered') DEFAULT 'pending',
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
    INDEX idx_payment_status (payment_status),
    INDEX idx_delivery_status (delivery_status),
    INDEX idx_order_date (order_date)
) ENGINE=InnoDB;

-- ============================================================================
-- TABLE 6: TRANSACTIONS
-- FIX: ADDED - Was missing from FILE12 but needed for PayFast & admin/reports.php
-- ============================================================================

CREATE TABLE transactions (
    transaction_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL UNIQUE,
    payfast_payment_id VARCHAR(100) NULL,
    transaction_amount DECIMAL(10,2) NOT NULL,
    platform_fee DECIMAL(10,2) NOT NULL,
    seller_payout DECIMAL(10,2) NOT NULL,
    transaction_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    payout_status ENUM('pending','processed','failed') DEFAULT 'pending',
    payout_date TIMESTAMP NULL,
    payout_reference VARCHAR(100) NULL,

    FOREIGN KEY (order_id) REFERENCES orders(order_id),

    INDEX idx_payfast (payfast_payment_id),
    INDEX idx_payout_status (payout_status),
    INDEX idx_transaction_date (transaction_date)
) ENGINE=InnoDB;

-- ============================================================================
-- TABLE 7: REVIEWS
-- No changes - matches reviews/leave.php, reviews/view.php
-- ============================================================================

CREATE TABLE reviews (
    review_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL UNIQUE,
    reviewer_id INT NOT NULL,
    seller_id INT NOT NULL,
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
) ENGINE=InnoDB;

-- ============================================================================
-- TABLE 8: CONVERSATIONS
-- FIX: ADDED - Was missing from FILE12. Required by:
--      messages/inbox.php, messages/conversation.php, messages/send.php,
--      messages/search.php
-- ============================================================================

CREATE TABLE conversations (
    conversation_id INT AUTO_INCREMENT PRIMARY KEY,
    buyer_id INT NOT NULL,
    seller_id INT NOT NULL,
    product_id INT NULL,
    status ENUM('active','archived') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_message_at TIMESTAMP NULL,

    FOREIGN KEY (buyer_id) REFERENCES users(user_id),
    FOREIGN KEY (seller_id) REFERENCES users(user_id),
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE SET NULL,

    UNIQUE KEY unique_conversation (buyer_id, seller_id, product_id),
    INDEX idx_buyer (buyer_id, status),
    INDEX idx_seller (seller_id, status),
    INDEX idx_last_message (last_message_at)
) ENGINE=InnoDB;

-- ============================================================================
-- TABLE 9: MESSAGES
-- FIX: Changed conversation_id from VARCHAR string to INT FK -> conversations
--      Affects: messages/inbox.php, messages/conversation.php,
--               messages/send.php, messages/search.php
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
) ENGINE=InnoDB;

-- ============================================================================
-- TABLE 10: VERIFICATION_DOCUMENTS
-- No changes - matches admin/verify-documents.php, auth/register.php
-- ============================================================================

CREATE TABLE verification_documents (
    document_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    document_path VARCHAR(255) NOT NULL,
    document_type ENUM('id_book','drivers_license','passport','business_reg') NOT NULL,
    verification_status ENUM('pending','approved','rejected') DEFAULT 'pending',
    rejection_reason TEXT NULL,
    reviewed_by INT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    reviewed_at TIMESTAMP NULL,

    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (reviewed_by) REFERENCES users(user_id),

    INDEX idx_status (verification_status),
    INDEX idx_uploaded (uploaded_at)
) ENGINE=InnoDB;

-- ============================================================================
-- TABLE 11: DISPUTES
-- FIX: Merged dispute_evidence table INTO disputes.evidence_paths (JSON)
--      to match your schema. Affects: disputes/file.php, disputes/view.php,
--      disputes/my-disputes.php, admin/disputes.php, moderator/dashboard.php
-- ============================================================================

CREATE TABLE disputes (
    dispute_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL UNIQUE,
    reported_by INT NOT NULL,
    dispute_reason ENUM('non_delivery','not_as_described','damaged','seller_unresponsive','other') NOT NULL,
    description TEXT NOT NULL,
    evidence_paths TEXT NULL COMMENT 'JSON array of file paths',
    status ENUM('open','investigating','resolved','closed') DEFAULT 'open',
    resolution_outcome ENUM('buyer_favour','seller_favour','mutual','insufficient') NULL,
    resolution_notes TEXT NULL,
    resolved_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    resolved_at TIMESTAMP NULL,

    FOREIGN KEY (order_id) REFERENCES orders(order_id),
    FOREIGN KEY (reported_by) REFERENCES users(user_id),
    FOREIGN KEY (resolved_by) REFERENCES users(user_id),

    INDEX idx_status (status),
    INDEX idx_created (created_at)
) ENGINE=InnoDB;

-- ============================================================================
-- TABLE 12: ADMIN_LOGS
-- FIX: ADDED - Was missing from FILE12 but needed for audit trail.
--      Used by: admin/dashboard.php, admin/users.php,
--               admin/pending-approvals.php, admin/verify-documents.php
-- ============================================================================

CREATE TABLE admin_logs (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    action_type VARCHAR(50) NOT NULL,
    target_type VARCHAR(50) NOT NULL,
    target_id INT NOT NULL,
    action_details TEXT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (admin_id) REFERENCES users(user_id),

    INDEX idx_admin (admin_id, timestamp),
    INDEX idx_target (target_type, target_id),
    INDEX idx_timestamp (timestamp)
) ENGINE=InnoDB;

-- ============================================================================
-- TABLE 13: TRANSLATIONS
-- FIX: ADDED - Was missing from FILE12.
--      Used by: includes/Language.php, user/settings.php
-- ============================================================================

CREATE TABLE translations (
    translation_id INT AUTO_INCREMENT PRIMARY KEY,
    language_code VARCHAR(5) NOT NULL,
    translation_key VARCHAR(100) NOT NULL,
    translation_text TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY unique_translation (language_code, translation_key),
    INDEX idx_language (language_code)
) ENGINE=InnoDB;

INSERT INTO translations (language_code, translation_key, translation_text) VALUES
('en','site_title','Street2Screen ZA'),
('en','site_tagline','Bringing Kasi To Your Screen'),
('en','btn_login','Login'),
('en','btn_register','Register'),
('en','btn_logout','Logout'),
('en','nav_home','Home'),
('en','nav_products','Products'),
('en','nav_sell','Sell'),
('en','nav_messages','Messages'),
('en','nav_account','My Account'),
('af','site_title','Street2Screen ZA'),
('af','site_tagline','Bringing Kasi To Your Screen'),
('af','btn_login','Aanmeld'),
('af','btn_register','Registreer'),
('af','nav_home','Tuis'),
('zu','btn_login','Ngena'),
('zu','btn_register','Bhalisa'),
('zu','nav_home','Ikhaya'),
('xh','btn_login','Ngena'),
('xh','btn_register','Bhalisa');

-- ============================================================================
-- TABLE 14: PASSWORD_RESETS
-- No changes - matches auth/forgot-password.php, auth/reset-password.php
-- ============================================================================

CREATE TABLE password_resets (
    reset_id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL,
    reset_token VARCHAR(64) NOT NULL UNIQUE,
    token_expiry DATETIME NOT NULL,
    used BOOLEAN DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_email (email, used),
    INDEX idx_expiry (token_expiry)
) ENGINE=InnoDB;

-- ============================================================================
-- TABLE 15: SESSIONS
-- FIX: ADDED - Was missing from FILE12.
--      Supports session management in includes/Security.php
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
) ENGINE=InnoDB;

-- ============================================================================
-- TABLE 16: CART
-- FIX: ADDED BACK - Was in FILE12, missing from your schema.
--      Required by: orders/cart.php, orders/cart-add.php,
--                   orders/cart-remove.php, orders/cart-update.php,
--                   orders/checkout.php
-- ============================================================================

CREATE TABLE cart (
    cart_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT DEFAULT 1,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    UNIQUE KEY user_product (user_id, product_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE,

    INDEX idx_user (user_id)
) ENGINE=InnoDB;

-- ============================================================================
-- TABLE 17: FAVORITES
-- FIX: ADDED BACK - Was in FILE12, missing from your schema.
--      Required by: user/favorites.php
-- ============================================================================

CREATE TABLE favorites (
    favorite_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    UNIQUE KEY user_product (user_id, product_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE,

    INDEX idx_user (user_id)
) ENGINE=InnoDB;

-- ============================================================================
-- TRIGGERS
-- ============================================================================

DELIMITER //

-- Trigger 1: Auto reduce stock when order placed (from your schema)
CREATE TRIGGER after_order_insert
AFTER INSERT ON orders
FOR EACH ROW
BEGIN
    UPDATE products
    SET stock_quantity = stock_quantity - NEW.quantity
    WHERE product_id = NEW.product_id;

    UPDATE products
    SET status = 'sold'
    WHERE product_id = NEW.product_id AND stock_quantity <= 0;
END//

-- Trigger 2: Auto calculate platform fee (from your schema)
-- NOTE: PHP constant PLATFORM_COMMISSION = 0.10 (10%)
-- Your schema used 5% - CORRECTED to 10% to match config/database.php
CREATE TRIGGER before_transaction_insert
BEFORE INSERT ON transactions
FOR EACH ROW
BEGIN
    SET NEW.platform_fee = NEW.transaction_amount * 0.10;
    SET NEW.seller_payout = NEW.transaction_amount - NEW.platform_fee;
END//

-- Trigger 3: Update conversation last_message_at (from your schema)
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
-- VIEWS
-- ============================================================================

-- View 1: Active products (from your schema - matches products/index.php)
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

-- View 2: Seller ratings (from your schema - matches reviews/view.php)
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
WHERE u.user_type IN ('seller','both')
GROUP BY u.user_id, u.full_name;

-- ============================================================================
-- DEFAULT ADMIN USER
-- Password: Admin@2026! - CHANGE AFTER FIRST LOGIN!
-- ============================================================================

INSERT INTO users (full_name, email, password_hash, user_type, email_verified, account_status)
VALUES (
    'System Administrator',
    'admin@street2screen.co.za',
    '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'admin',
    1,
    'active'
);

-- ============================================================================
-- VERIFY SETUP
-- ============================================================================

SELECT TABLE_NAME, TABLE_ROWS, CREATE_TIME
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = 'street2screen_db'
ORDER BY TABLE_NAME;

