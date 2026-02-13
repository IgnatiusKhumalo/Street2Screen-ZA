# ENHANCED ENTITY RELATIONSHIP DIAGRAM (EERD)
## Street2Screen ZA - Database Design

**Project:** Street2Screen ZA  
**Student:** Ignatius Mayibongwe Khumalo  
**Institution:** Eduvos Private Institution  
**Course:** ITECA3-12 Initial Project  
**Date:** February 2026

---

## 📊 DATABASE OVERVIEW

**Database Name:** street2screen_db (local) / if0_41132529_street2screen (production)  
**Total Tables:** 15  
**Relationships:** 23 foreign key constraints  
**Character Set:** utf8mb4 (supports all 11 SA languages + emoji)  
**Collation:** utf8mb4_unicode_ci

---

## 🗂️ ENTITY DESCRIPTIONS

### CORE ENTITIES (User-Facing)
1. **users** - All platform users (buyers, sellers, admins)
2. **products** - Product listings created by sellers
3. **product_images** - Product photos (3-5 per product)
4. **categories** - Product classification (5 fixed categories)
5. **orders** - Purchase transactions
6. **transactions** - Financial records with platform fees
7. **reviews** - Buyer ratings and feedback for sellers
8. **messages** - Individual chat messages
9. **conversations** - Message thread containers

### ADMINISTRATIVE ENTITIES
10. **verification_documents** - Seller ID uploads for verification
11. **disputes** - Buyer complaints about orders
12. **admin_logs** - Audit trail of admin actions

### SUPPORT ENTITIES
13. **translations** - Multi-language text (11 SA languages)
14. **sessions** - User session tracking (optional)
15. **password_resets** - Temporary tokens for password recovery

---

## 📋 COMPLETE ENTITY SPECIFICATIONS

### 1. USERS TABLE

```
┌────────────────────────────────────────────────────────────┐
│ TABLE: users                                                │
├────────────────────────────────────────────────────────────┤
│ PURPOSE: Store all user accounts (buyers, sellers, admins) │
├────────────────────────────────────────────────────────────┤
│ ATTRIBUTES:                                                 │
│                                                             │
│ PK  user_id             INT AUTO_INCREMENT                  │
│     full_name           VARCHAR(100) NOT NULL               │
│ UK  email               VARCHAR(100) NOT NULL UNIQUE        │
│     password_hash       VARCHAR(255) NOT NULL               │
│     user_type           ENUM('buyer','seller','moderator',  │
│                              'admin') DEFAULT 'buyer'       │
│     phone               VARCHAR(15) NULL                    │
│     address             TEXT NULL                           │
│     township            VARCHAR(100) NULL                   │
│     city                VARCHAR(100) NULL                   │
│     province            VARCHAR(50) NULL                    │
│     postal_code         VARCHAR(10) NULL                    │
│     profile_picture     VARCHAR(255) NULL                   │
│     email_verified      BOOLEAN DEFAULT 0                   │
│     verification_token  VARCHAR(64) NULL                    │
│     token_expiry        DATETIME NULL                       │
│     remember_token      VARCHAR(255) NULL                   │
│     remember_expiry     DATETIME NULL                       │
│     account_status      ENUM('active','suspended','deleted')│
│                         DEFAULT 'active'                    │
│     suspension_reason   TEXT NULL                           │
│     suspension_until    DATETIME NULL                       │
│     created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP │
│     updated_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP │
│                         ON UPDATE CURRENT_TIMESTAMP         │
│     last_login          TIMESTAMP NULL                      │
│                                                             │
│ INDEXES:                                                    │
│ • PRIMARY KEY (user_id)                                     │
│ • UNIQUE KEY (email)                                        │
│ • INDEX (user_type)                                         │
│ • INDEX (account_status)                                    │
│ • INDEX (email_verified)                                    │
└────────────────────────────────────────────────────────────┘

RELATIONSHIPS:
• users → products (1:M) - One seller creates many products
• users → orders (1:M as buyer) - One buyer places many orders
• users → orders (1:M as seller) - One seller receives many orders
• users → verification_documents (1:1) - Sellers upload verification
• users → reviews (1:M as reviewer) - Buyers write reviews
• users → reviews (1:M as seller) - Sellers receive reviews
• users → messages (1:M) - Users send messages
• users → conversations (1:M) - Users participate in conversations
• users → admin_logs (1:M) - Admins create log entries
```

---

### 2. PRODUCTS TABLE

```
┌────────────────────────────────────────────────────────────┐
│ TABLE: products                                             │
├────────────────────────────────────────────────────────────┤
│ PURPOSE: Store product listings created by sellers         │
├────────────────────────────────────────────────────────────┤
│ ATTRIBUTES:                                                 │
│                                                             │
│ PK  product_id          INT AUTO_INCREMENT                  │
│ FK  seller_id           INT NOT NULL                        │
│                         REFERENCES users(user_id)           │
│                         ON DELETE CASCADE                   │
│ FK  category_id         INT NOT NULL                        │
│                         REFERENCES categories(category_id)  │
│     product_name        VARCHAR(100) NOT NULL               │
│     description         TEXT NOT NULL                       │
│     price               DECIMAL(10,2) NOT NULL              │
│     stock_quantity      INT NOT NULL DEFAULT 1              │
│     location            VARCHAR(100) NOT NULL               │
│     condition           ENUM('new','like_new','good','fair')│
│                         NOT NULL                            │
│     status              ENUM('active','sold','suspended',   │
│                              'deleted') DEFAULT 'active'    │
│     view_count          INT DEFAULT 0                       │
│     featured            BOOLEAN DEFAULT 0                   │
│     featured_until      DATETIME NULL                       │
│     created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP │
│     updated_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP │
│                         ON UPDATE CURRENT_TIMESTAMP         │
│                                                             │
│ INDEXES:                                                    │
│ • PRIMARY KEY (product_id)                                  │
│ • FOREIGN KEY (seller_id) REFERENCES users(user_id)         │
│ • FOREIGN KEY (category_id) REFERENCES categories(...)      │
│ • INDEX (status)                                            │
│ • INDEX (category_id)                                       │
│ • INDEX (price)                                             │
│ • FULLTEXT INDEX (product_name, description)                │
└────────────────────────────────────────────────────────────┘

RELATIONSHIPS:
• products → users (M:1) - Many products belong to one seller
• products → categories (M:1) - Many products in one category
• products → product_images (1:M) - One product has many images
• products → orders (1:M) - One product can be ordered multiple times
• products → conversations (1:M) - Product discussed in conversations
```

---

### 3. PRODUCT_IMAGES TABLE

```
┌────────────────────────────────────────────────────────────┐
│ TABLE: product_images                                       │
├────────────────────────────────────────────────────────────┤
│ PURPOSE: Store product photos (3-5 images per product)     │
├────────────────────────────────────────────────────────────┤
│ ATTRIBUTES:                                                 │
│                                                             │
│ PK  image_id            INT AUTO_INCREMENT                  │
│ FK  product_id          INT NOT NULL                        │
│                         REFERENCES products(product_id)     │
│                         ON DELETE CASCADE                   │
│     image_path          VARCHAR(255) NOT NULL               │
│     thumbnail_path      VARCHAR(255) NULL                   │
│     is_primary          BOOLEAN DEFAULT 0                   │
│     display_order       INT DEFAULT 0                       │
│     uploaded_at         TIMESTAMP DEFAULT CURRENT_TIMESTAMP │
│                                                             │
│ INDEXES:                                                    │
│ • PRIMARY KEY (image_id)                                    │
│ • FOREIGN KEY (product_id) REFERENCES products(...)         │
│   ON DELETE CASCADE                                         │
│ • INDEX (product_id, is_primary)                            │
│ • INDEX (display_order)                                     │
│                                                             │
│ CONSTRAINTS:                                                │
│ • Only ONE is_primary=1 per product_id                      │
│ • Maximum 5 images per product_id                           │
└────────────────────────────────────────────────────────────┘

RELATIONSHIPS:
• product_images → products (M:1) - Images belong to product
```

---

### 4. CATEGORIES TABLE

```
┌────────────────────────────────────────────────────────────┐
│ TABLE: categories                                           │
├────────────────────────────────────────────────────────────┤
│ PURPOSE: Fixed product categories (5 categories)            │
├────────────────────────────────────────────────────────────┤
│ ATTRIBUTES:                                                 │
│                                                             │
│ PK  category_id         INT AUTO_INCREMENT                  │
│ UK  category_name       VARCHAR(50) NOT NULL UNIQUE         │
│     description         TEXT NULL                           │
│     icon_class          VARCHAR(50) NULL                    │
│                         (Font Awesome icon name)            │
│     display_order       INT DEFAULT 0                       │
│     active              BOOLEAN DEFAULT 1                   │
│                                                             │
│ FIXED DATA:                                                 │
│ 1 | Clothing & Fashion     | fa-tshirt                      │
│ 2 | Electronics & Access.. | fa-laptop                      │
│ 3 | Home & Kitchen         | fa-home                        │
│ 4 | Food & Drinks          | fa-utensils                    │
│ 5 | Handmade & Crafts      | fa-palette                     │
│                                                             │
│ INDEXES:                                                    │
│ • PRIMARY KEY (category_id)                                 │
│ • UNIQUE KEY (category_name)                                │
└────────────────────────────────────────────────────────────┘

RELATIONSHIPS:
• categories → products (1:M) - Category contains many products
```

---

### 5. ORDERS TABLE

```
┌────────────────────────────────────────────────────────────┐
│ TABLE: orders                                               │
├────────────────────────────────────────────────────────────┤
│ PURPOSE: Track all purchase transactions                   │
├────────────────────────────────────────────────────────────┤
│ ATTRIBUTES:                                                 │
│                                                             │
│ PK  order_id            INT AUTO_INCREMENT                  │
│ FK  buyer_id            INT NOT NULL                        │
│                         REFERENCES users(user_id)           │
│ FK  seller_id           INT NOT NULL                        │
│                         REFERENCES users(user_id)           │
│ FK  product_id          INT NOT NULL                        │
│                         REFERENCES products(product_id)     │
│     quantity            INT NOT NULL DEFAULT 1              │
│     unit_price          DECIMAL(10,2) NOT NULL              │
│     total_amount        DECIMAL(10,2) NOT NULL              │
│     payment_method      ENUM('payfast','cod','eft',         │
│                              'manual') DEFAULT 'payfast'    │
│     payment_status      ENUM('pending','paid','failed',     │
│                              'refunded') DEFAULT 'pending'  │
│     delivery_address    TEXT NOT NULL                       │
│     delivery_method     ENUM('collection','courier',        │
│                              'pudo') DEFAULT 'collection'   │
│     delivery_status     ENUM('pending','shipped',           │
│                              'delivered') DEFAULT 'pending' │
│     tracking_number     VARCHAR(100) NULL                   │
│     buyer_notes         TEXT NULL                           │
│     seller_notes        TEXT NULL                           │
│     order_date          TIMESTAMP DEFAULT CURRENT_TIMESTAMP │
│     payment_date        TIMESTAMP NULL                      │
│     shipped_date        TIMESTAMP NULL                      │
│     delivery_date       TIMESTAMP NULL                      │
│                                                             │
│ INDEXES:                                                    │
│ • PRIMARY KEY (order_id)                                    │
│ • FOREIGN KEY (buyer_id) REFERENCES users(user_id)          │
│ • FOREIGN KEY (seller_id) REFERENCES users(user_id)         │
│ • FOREIGN KEY (product_id) REFERENCES products(...)         │
│ • INDEX (payment_status)                                    │
│ • INDEX (delivery_status)                                   │
│ • INDEX (order_date)                                        │
└────────────────────────────────────────────────────────────┘

RELATIONSHIPS:
• orders → users (M:1 as buyer)
• orders → users (M:1 as seller)
• orders → products (M:1)
• orders → transactions (1:1)
• orders → reviews (1:0..1) - Order may have one review
• orders → disputes (1:0..1) - Order may have one dispute
```

---

### 6. TRANSACTIONS TABLE

```
┌────────────────────────────────────────────────────────────┐
│ TABLE: transactions                                         │
├────────────────────────────────────────────────────────────┤
│ PURPOSE: Financial records with platform fee calculations  │
├────────────────────────────────────────────────────────────┤
│ ATTRIBUTES:                                                 │
│                                                             │
│ PK  transaction_id      INT AUTO_INCREMENT                  │
│ FK  order_id            INT NOT NULL UNIQUE                 │
│                         REFERENCES orders(order_id)         │
│     payfast_payment_id  VARCHAR(100) NULL                   │
│     transaction_amount  DECIMAL(10,2) NOT NULL              │
│     platform_fee        DECIMAL(10,2) NOT NULL              │
│                         (5% of transaction_amount)          │
│     seller_payout       DECIMAL(10,2) NOT NULL              │
│                         (transaction_amount - platform_fee) │
│     transaction_date    TIMESTAMP DEFAULT CURRENT_TIMESTAMP │
│     payout_status       ENUM('pending','processed','failed')│
│                         DEFAULT 'pending'                   │
│     payout_date         TIMESTAMP NULL                      │
│     payout_reference    VARCHAR(100) NULL                   │
│                                                             │
│ INDEXES:                                                    │
│ • PRIMARY KEY (transaction_id)                              │
│ • UNIQUE KEY (order_id)                                     │
│ • INDEX (payfast_payment_id)                                │
│ • INDEX (payout_status)                                     │
│ • INDEX (transaction_date)                                  │
│                                                             │
│ CALCULATED FIELDS:                                          │
│ platform_fee = transaction_amount * 0.05                    │
│ seller_payout = transaction_amount - platform_fee           │
└────────────────────────────────────────────────────────────┘

RELATIONSHIPS:
• transactions → orders (1:1) - Each order has one transaction
```

---

### 7. REVIEWS TABLE

```
┌────────────────────────────────────────────────────────────┐
│ TABLE: reviews                                              │
├────────────────────────────────────────────────────────────┤
│ PURPOSE: Buyer ratings and feedback for sellers            │
├────────────────────────────────────────────────────────────┤
│ ATTRIBUTES:                                                 │
│                                                             │
│ PK  review_id           INT AUTO_INCREMENT                  │
│ FK  order_id            INT NOT NULL UNIQUE                 │
│                         REFERENCES orders(order_id)         │
│                         (ensures verified purchase)         │
│ FK  reviewer_id         INT NOT NULL                        │
│                         REFERENCES users(user_id) - buyer   │
│ FK  seller_id           INT NOT NULL                        │
│                         REFERENCES users(user_id)           │
│     rating              INT NOT NULL CHECK (rating >= 1     │
│                         AND rating <= 5)                    │
│     review_text         TEXT NOT NULL                       │
│     seller_response     TEXT NULL                           │
│     response_date       TIMESTAMP NULL                      │
│     helpful_count       INT DEFAULT 0                       │
│     flagged             BOOLEAN DEFAULT 0                   │
│     flag_reason         TEXT NULL                           │
│     created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP │
│     updated_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP │
│                         ON UPDATE CURRENT_TIMESTAMP         │
│                                                             │
│ INDEXES:                                                    │
│ • PRIMARY KEY (review_id)                                   │
│ • UNIQUE KEY (order_id) - One review per order              │
│ • FOREIGN KEY (reviewer_id) REFERENCES users(user_id)       │
│ • FOREIGN KEY (seller_id) REFERENCES users(user_id)         │
│ • INDEX (seller_id, rating)                                 │
│ • INDEX (created_at)                                        │
└────────────────────────────────────────────────────────────┘

RELATIONSHIPS:
• reviews → orders (1:1) - Review for specific order
• reviews → users (M:1 as reviewer)
• reviews → users (M:1 as seller receiving review)
```

---

### 8. MESSAGES TABLE

```
┌────────────────────────────────────────────────────────────┐
│ TABLE: messages                                             │
├────────────────────────────────────────────────────────────┤
│ PURPOSE: Individual chat messages between users            │
├────────────────────────────────────────────────────────────┤
│ ATTRIBUTES:                                                 │
│                                                             │
│ PK  message_id          INT AUTO_INCREMENT                  │
│ FK  conversation_id     INT NOT NULL                        │
│                         REFERENCES conversations(...)       │
│                         ON DELETE CASCADE                   │
│ FK  sender_id           INT NOT NULL                        │
│                         REFERENCES users(user_id)           │
│     message_text        TEXT NOT NULL                       │
│     attachment_path     VARCHAR(255) NULL                   │
│     read_status         BOOLEAN DEFAULT 0                   │
│     read_at             TIMESTAMP NULL                      │
│     sent_at             TIMESTAMP DEFAULT CURRENT_TIMESTAMP │
│     deleted_by_sender   BOOLEAN DEFAULT 0                   │
│     deleted_by_receiver BOOLEAN DEFAULT 0                   │
│                                                             │
│ INDEXES:                                                    │
│ • PRIMARY KEY (message_id)                                  │
│ • FOREIGN KEY (conversation_id) REFERENCES ...              │
│   ON DELETE CASCADE                                         │
│ • FOREIGN KEY (sender_id) REFERENCES users(user_id)         │
│ • INDEX (conversation_id, sent_at)                          │
│ • INDEX (read_status)                                       │
└────────────────────────────────────────────────────────────┘

RELATIONSHIPS:
• messages → conversations (M:1)
• messages → users (M:1 as sender)
```

---

### 9. CONVERSATIONS TABLE

```
┌────────────────────────────────────────────────────────────┐
│ TABLE: conversations                                        │
├────────────────────────────────────────────────────────────┤
│ PURPOSE: Message thread containers                         │
├────────────────────────────────────────────────────────────┤
│ ATTRIBUTES:                                                 │
│                                                             │
│ PK  conversation_id     INT AUTO_INCREMENT                  │
│ FK  buyer_id            INT NOT NULL                        │
│                         REFERENCES users(user_id)           │
│ FK  seller_id           INT NOT NULL                        │
│                         REFERENCES users(user_id)           │
│ FK  product_id          INT NULL                            │
│                         REFERENCES products(product_id)     │
│                         ON DELETE SET NULL                  │
│     status              ENUM('active','archived')           │
│                         DEFAULT 'active'                    │
│     created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP │
│     last_message_at     TIMESTAMP NULL                      │
│                                                             │
│ INDEXES:                                                    │
│ • PRIMARY KEY (conversation_id)                             │
│ • FOREIGN KEY (buyer_id) REFERENCES users(user_id)          │
│ • FOREIGN KEY (seller_id) REFERENCES users(user_id)         │
│ • FOREIGN KEY (product_id) REFERENCES products(...)         │
│ • INDEX (buyer_id, status)                                  │
│ • INDEX (seller_id, status)                                 │
│ • INDEX (last_message_at)                                   │
│                                                             │
│ UNIQUE CONSTRAINT:                                          │
│ • UNIQUE (buyer_id, seller_id, product_id)                  │
│   Prevents duplicate conversations                          │
└────────────────────────────────────────────────────────────┘

RELATIONSHIPS:
• conversations → users (M:1 as buyer)
• conversations → users (M:1 as seller)
• conversations → products (M:1)
• conversations → messages (1:M)
```

---

### 10. VERIFICATION_DOCUMENTS TABLE

```
┌────────────────────────────────────────────────────────────┐
│ TABLE: verification_documents                               │
├────────────────────────────────────────────────────────────┤
│ PURPOSE: Seller identity verification uploads              │
├────────────────────────────────────────────────────────────┤
│ ATTRIBUTES:                                                 │
│                                                             │
│ PK  document_id         INT AUTO_INCREMENT                  │
│ FK  user_id             INT NOT NULL UNIQUE                 │
│                         REFERENCES users(user_id)           │
│                         (one document set per seller)       │
│     document_path       VARCHAR(255) NOT NULL               │
│                         (encrypted storage path)            │
│     document_type       ENUM('id_book','drivers_license',   │
│                              'passport','business_reg')     │
│                         NOT NULL                            │
│     verification_status ENUM('pending','approved',          │
│                              'rejected') DEFAULT 'pending'  │
│     rejection_reason    TEXT NULL                           │
│ FK  reviewed_by         INT NULL                            │
│                         REFERENCES users(user_id) - admin   │
│     uploaded_at         TIMESTAMP DEFAULT CURRENT_TIMESTAMP │
│     reviewed_at         TIMESTAMP NULL                      │
│                                                             │
│ INDEXES:                                                    │
│ • PRIMARY KEY (document_id)                                 │
│ • UNIQUE KEY (user_id) - One document set per user          │
│ • FOREIGN KEY (reviewed_by) REFERENCES users(user_id)       │
│ • INDEX (verification_status)                               │
│ • INDEX (uploaded_at)                                       │
└────────────────────────────────────────────────────────────┘

RELATIONSHIPS:
• verification_documents → users (1:1 for seller)
• verification_documents → users (M:1 for admin reviewer)
```

---

### 11. DISPUTES TABLE

```
┌────────────────────────────────────────────────────────────┐
│ TABLE: disputes                                             │
├────────────────────────────────────────────────────────────┤
│ PURPOSE: Buyer complaints about problematic orders         │
├────────────────────────────────────────────────────────────┤
│ ATTRIBUTES:                                                 │
│                                                             │
│ PK  dispute_id          INT AUTO_INCREMENT                  │
│ FK  order_id            INT NOT NULL UNIQUE                 │
│                         REFERENCES orders(order_id)         │
│ FK  reported_by         INT NOT NULL                        │
│                         REFERENCES users(user_id) - buyer   │
│     dispute_reason      ENUM('non_delivery',                │
│                              'not_as_described','damaged',  │
│                              'seller_unresponsive','other') │
│                         NOT NULL                            │
│     description         TEXT NOT NULL                       │
│     evidence_paths      TEXT NULL                           │
│                         (JSON array of file paths)          │
│     status              ENUM('open','investigating',        │
│                              'resolved','closed')           │
│                         DEFAULT 'open'                      │
│     resolution_outcome  ENUM('buyer_favour','seller_favour',│
│                              'mutual','insufficient') NULL  │
│     resolution_notes    TEXT NULL                           │
│ FK  resolved_by         INT NULL                            │
│                         REFERENCES users(user_id) - mod     │
│     created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP │
│     resolved_at         TIMESTAMP NULL                      │
│                                                             │
│ INDEXES:                                                    │
│ • PRIMARY KEY (dispute_id)                                  │
│ • UNIQUE KEY (order_id) - One dispute per order             │
│ • FOREIGN KEY (reported_by) REFERENCES users(user_id)       │
│ • FOREIGN KEY (resolved_by) REFERENCES users(user_id)       │
│ • INDEX (status)                                            │
│ • INDEX (created_at)                                        │
└────────────────────────────────────────────────────────────┘

RELATIONSHIPS:
• disputes → orders (1:1)
• disputes → users (M:1 as reporter)
• disputes → users (M:1 as resolver/moderator)
```

---

### 12. ADMIN_LOGS TABLE

```
┌────────────────────────────────────────────────────────────┐
│ TABLE: admin_logs                                           │
├────────────────────────────────────────────────────────────┤
│ PURPOSE: Audit trail of all administrative actions         │
├────────────────────────────────────────────────────────────┤
│ ATTRIBUTES:                                                 │
│                                                             │
│ PK  log_id              INT AUTO_INCREMENT                  │
│ FK  admin_id            INT NOT NULL                        │
│                         REFERENCES users(user_id)           │
│     action_type         VARCHAR(50) NOT NULL                │
│                         (approve_seller, suspend_user, etc) │
│     target_type         VARCHAR(50) NOT NULL                │
│                         (user, product, dispute, etc)       │
│     target_id           INT NOT NULL                        │
│                         (ID of affected entity)             │
│     action_details      TEXT NULL                           │
│                         (JSON with additional info)         │
│     ip_address          VARCHAR(45) NULL                    │
│     user_agent          VARCHAR(255) NULL                   │
│     timestamp           TIMESTAMP DEFAULT CURRENT_TIMESTAMP │
│                                                             │
│ INDEXES:                                                    │
│ • PRIMARY KEY (log_id)                                      │
│ • FOREIGN KEY (admin_id) REFERENCES users(user_id)          │
│ • INDEX (admin_id, timestamp)                               │
│ • INDEX (target_type, target_id)                            │
│ • INDEX (timestamp)                                         │
└────────────────────────────────────────────────────────────┘

RELATIONSHIPS:
• admin_logs → users (M:1 as admin performing action)
```

---

### 13. TRANSLATIONS TABLE

```
┌────────────────────────────────────────────────────────────┐
│ TABLE: translations                                         │
├────────────────────────────────────────────────────────────┤
│ PURPOSE: Multi-language UI text (11 SA languages)          │
├────────────────────────────────────────────────────────────┤
│ ATTRIBUTES:                                                 │
│                                                             │
│ PK  translation_id      INT AUTO_INCREMENT                  │
│     language_code       VARCHAR(5) NOT NULL                 │
│                         (en, af, zu, xh, st, nso, tn,       │
│                          ss, nr, ve, ts)                    │
│     translation_key     VARCHAR(100) NOT NULL               │
│                         (btn_login, nav_home, etc)          │
│     translation_text    TEXT NOT NULL                       │
│     created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP │
│     updated_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP │
│                         ON UPDATE CURRENT_TIMESTAMP         │
│                                                             │
│ INDEXES:                                                    │
│ • PRIMARY KEY (translation_id)                              │
│ • UNIQUE KEY (language_code, translation_key)               │
│ • INDEX (language_code)                                     │
│                                                             │
│ LANGUAGES:                                                  │
│ en  - English          | st  - Sesotho                      │
│ af  - Afrikaans        | nso - Sepedi (Northern Sotho)     │
│ zu  - isiZulu          | tn  - Setswana                     │
│ xh  - isiXhosa         | ss  - siSwati                      │
│ nr  - isiNdebele       | ve  - Tshivenda                    │
│ ts  - Xitsonga         |                                    │
└────────────────────────────────────────────────────────────┘

RELATIONSHIPS:
• None (standalone lookup table)
```

---

### 14. PASSWORD_RESETS TABLE

```
┌────────────────────────────────────────────────────────────┐
│ TABLE: password_resets                                      │
├────────────────────────────────────────────────────────────┤
│ PURPOSE: Temporary tokens for password recovery            │
├────────────────────────────────────────────────────────────┤
│ ATTRIBUTES:                                                 │
│                                                             │
│ PK  reset_id            INT AUTO_INCREMENT                  │
│     email               VARCHAR(100) NOT NULL               │
│     reset_token         VARCHAR(64) NOT NULL UNIQUE         │
│     token_expiry        DATETIME NOT NULL                   │
│                         (24 hours from creation)            │
│     used                BOOLEAN DEFAULT 0                   │
│     created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP │
│                                                             │
│ INDEXES:                                                    │
│ • PRIMARY KEY (reset_id)                                    │
│ • UNIQUE KEY (reset_token)                                  │
│ • INDEX (email, used)                                       │
│ • INDEX (token_expiry)                                      │
│                                                             │
│ CLEANUP:                                                    │
│ Delete expired tokens daily: WHERE token_expiry < NOW()     │
└────────────────────────────────────────────────────────────┘

RELATIONSHIPS:
• None (temporary lookup table)
```

---

### 15. SESSIONS TABLE (OPTIONAL)

```
┌────────────────────────────────────────────────────────────┐
│ TABLE: sessions                                             │
├────────────────────────────────────────────────────────────┤
│ PURPOSE: Track active user sessions (optional)             │
├────────────────────────────────────────────────────────────┤
│ ATTRIBUTES:                                                 │
│                                                             │
│ PK  session_id          VARCHAR(128) NOT NULL               │
│ FK  user_id             INT NULL                            │
│                         REFERENCES users(user_id)           │
│     session_data        TEXT NULL                           │
│     ip_address          VARCHAR(45) NULL                    │
│     user_agent          VARCHAR(255) NULL                   │
│     created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP │
│     last_activity       TIMESTAMP DEFAULT CURRENT_TIMESTAMP │
│                         ON UPDATE CURRENT_TIMESTAMP         │
│                                                             │
│ INDEXES:                                                    │
│ • PRIMARY KEY (session_id)                                  │
│ • FOREIGN KEY (user_id) REFERENCES users(user_id)           │
│   ON DELETE CASCADE                                         │
│ • INDEX (user_id)                                           │
│ • INDEX (last_activity)                                     │
│                                                             │
│ CLEANUP:                                                    │
│ Delete inactive sessions: WHERE last_activity < NOW() -     │
│ INTERVAL 1 HOUR                                             │
└────────────────────────────────────────────────────────────┘

RELATIONSHIPS:
• sessions → users (M:1)
```

---

## 🔗 COMPLETE RELATIONSHIP MAP

```
CARDINALITY LEGEND:
1   = Exactly one
0..1 = Zero or one
1..* = One to many
*   = Zero to many

RELATIONSHIPS:

users (1) ────creates───> (*) products
users (1) ────places────> (*) orders (as buyer)
users (1) ────receives──> (*) orders (as seller)
users (1) ────has───────> (0..1) verification_documents
users (1) ────writes────> (*) reviews (as reviewer)
users (1) ────receives──> (*) reviews (as seller)
users (1) ────sends─────> (*) messages
users (1) ────in────────> (*) conversations
users (1) ────performs──> (*) admin_logs (if admin)
users (1) ────reports───> (*) disputes
users (1) ────resolves──> (*) disputes (if moderator)

products (1) ─has───────> (3..5) product_images
products (*) ─belongs_to─> (1) categories
products (1) ─ordered_in─> (*) orders
products (1) ─discussed─> (*) conversations

categories (1) ─contains─> (*) products

orders (1) ──has────────> (1) transactions
orders (1) ──may_have───> (0..1) reviews
orders (1) ──may_have───> (0..1) disputes

conversations (1) ─has──> (*) messages

ALL FOREIGN KEYS:
• products.seller_id → users.user_id (ON DELETE CASCADE)
• products.category_id → categories.category_id
• product_images.product_id → products.product_id (ON DELETE CASCADE)
• orders.buyer_id → users.user_id
• orders.seller_id → users.user_id
• orders.product_id → products.product_id
• transactions.order_id → orders.order_id
• reviews.order_id → orders.order_id
• reviews.reviewer_id → users.user_id
• reviews.seller_id → users.user_id
• messages.conversation_id → conversations.conversation_id (ON DELETE CASCADE)
• messages.sender_id → users.user_id
• conversations.buyer_id → users.user_id
• conversations.seller_id → users.user_id
• conversations.product_id → products.product_id (ON DELETE SET NULL)
• verification_documents.user_id → users.user_id
• verification_documents.reviewed_by → users.user_id
• disputes.order_id → orders.order_id
• disputes.reported_by → users.user_id
• disputes.resolved_by → users.user_id
• admin_logs.admin_id → users.user_id
• sessions.user_id → users.user_id (ON DELETE CASCADE)
```

---

## 📊 DATABASE STATISTICS

**Total Tables:** 15  
**Total Columns:** ~180  
**Total Indexes:** ~60  
**Total Foreign Keys:** 23  
**Estimated Size (empty):** ~2MB  
**Estimated Size (10,000 users):** ~500MB  

---

## ✅ NEXT STEP

This EERD will be:
1. **Visualized in draw.io** (you'll import the diagram)
2. **Converted to SQL** (I'll create the schema file next)
3. **Implemented in XAMPP** (local database)
4. **Deployed to InfinityFree** (production database)

**Ready for me to create the actual SQL schema file?**

---

**Document Created:** February 12, 2026  
**Student:** Ignatius Mayibongwe Khumalo  
**Institution:** Eduvos Private Institution
