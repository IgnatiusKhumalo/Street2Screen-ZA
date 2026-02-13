# DATA FLOW DIAGRAMS (DFD)
## Street2Screen ZA - System Processes

**Project:** Street2Screen ZA  
**Student:** Ignatius Mayibongwe Khumalo  
**Institution:** Eduvos Private Institution  
**Course:** ITECA3-12 Initial Project  
**Date:** February 2026

---

## 📋 WHAT ARE DATA FLOW DIAGRAMS?

**DFDs** show how **data moves through the system** across different **processes**. They decompose the system from high-level overview to detailed sub-processes.

**Levels:**
- **Level 0 (Context):** Entire system as one process ✅ (Already created)
- **Level 1:** Major system processes (7-9 main processes)
- **Level 2:** Detailed sub-processes for complex operations

**Notation:**
- **Circle/Bubble** = Process (transforms data)
- **Rectangle** = External Entity (source/destination of data)
- **Double Rectangle** = Data Store (database table)
- **Arrow** = Data Flow (labeled with data name)

---

# 📊 DFD LEVEL 1 - MAJOR PROCESSES

```
                            BUYERS                 SELLERS               ADMINS
                              │                       │                    │
                              ├───────────────────────┼────────────────────┤
                              │                       │                    │
                              ▼                       ▼                    ▼
                    ┌─────────────────────────────────────────────────────────┐
                    │                                                         │
┌───────────────────┼────────────────[ 1.0 USER MANAGEMENT ]────────────────┼───────────────────┐
│                   │  • Registration                                        │                   │
│                   │  • Login/Logout                                        │                   │
│                   │  • Email Verification                                  │                   │
│                   │  • Password Reset                                      │                   │
│                   │  • Profile Updates                                     │                   │
│                   └────────────┬───────────────────────────┬───────────────┘                   │
│                                │                           │                                   │
│                                ▼                           ▼                                   │
│                          ║ users ║                   ║ sessions ║                              │
│                                                                                                │
│   ┌────────────────────────────────────[ 2.0 PRODUCT MANAGEMENT ]──────────────────────────┐  │
│   │  • Create Product Listings                                                             │  │
│   │  • Update Product Details                                                              │  │
│   │  • Upload Product Images                                                               │  │
│   │  • Manage Stock                                                                        │  │
│   └──────────┬─────────────────────────────┬─────────────────────┬────────────────────────┘  │
│              │                             │                     │                           │
│              ▼                             ▼                     ▼                           │
│        ║ products ║                 ║ product_images ║      ║ categories ║                   │
│                                                                                                │
│   ┌────────────────────────────────[ 3.0 SEARCH & DISCOVERY ]──────────────────────────────┐ │
│   │  • Keyword Search                                                                       │ │
│   │  • Category Filtering                                                                   │ │
│   │  • Price Range Filtering                                                                │ │
│   │  • Location Filtering                                                                   │ │
│   │  • Sort Results                                                                         │ │
│   └──────────┬──────────────────────────────────────────────────────────────────────────────┘ │
│              │ (reads from)                                                                    │
│              ▼                                                                                 │
│        ║ products ║                                                                            │
│                                                                                                │
│   ┌────────────────────────────────[ 4.0 ORDER PROCESSING ]────────────────────────────────┐ │
│   │  • Add to Cart                                                                          │ │
│   │  • Checkout                                                                             │ │
│   │  • Create Order                                                                         │ │
│   │  • Update Order Status                                                                  │ │
│   │  • Track Delivery                                                                       │ │
│   └──────────┬────────────────────────────────┬─────────────────────────────────────────────┘ │
│              │                                │                                               │
│              ▼                                ▼                                               │
│        ║ orders ║                       ║ products ║                                          │
│              │                                                                                 │
│              │ (triggers)                                                                      │
│              ▼                                                                                 │
│   ┌────────────────────────────────[ 5.0 PAYMENT PROCESSING ]──────────────────────────────┐ │
│   │  • Initiate PayFast Payment                                                             │ │
│   │  • Receive IPN Callback                                                                 │ │
│   │  • Validate Payment                                                                     │ │
│   │  • Update Payment Status                                                                │ │
│   │  • Calculate Platform Fee                                                               │ │
│   │  • Record Transaction                                                                   │ │
│   └──────────┬────────────────────┬──────────────────────────────────────────────────────────┘ │
│              │                    │                          ↕                                │
│              ▼                    ▼                      [PayFast]                            │
│        ║ orders ║          ║ transactions ║                                                   │
│                                                                                                │
│   ┌────────────────────────────────[ 6.0 MESSAGING SYSTEM ]────────────────────────────────┐ │
│   │  • Create Conversation                                                                  │ │
│   │  • Send Message                                                                         │ │
│   │  • Receive Message                                                                      │ │
│   │  • Mark as Read                                                                         │ │
│   │  • Upload Attachments                                                                   │ │
│   └──────────┬────────────────────────────────┬──────────────────────────────────────────────┘ │
│              │                                │                          ↕                    │
│              ▼                                ▼                      [Brevo SMTP]             │
│      ║ conversations ║                  ║ messages ║                                          │
│                                                                                                │
│   ┌────────────────────────────────[ 7.0 REVIEW & RATING SYSTEM ]──────────────────────────┐ │
│   │  • Submit Review (verified purchase)                                                    │ │
│   │  • Calculate Average Rating                                                             │ │
│   │  • Post Seller Response                                                                 │ │
│   │  • Flag Inappropriate Reviews                                                           │ │
│   └──────────┬────────────────────────────────────────────────────────────────────────────────┘ │
│              │ (must have)                                                                     │
│              ▼                                                                                 │
│        ║ reviews ║ ← (linked to) ║ orders ║                                                   │
│                                                                                                │
│   ┌────────────────────────────────[ 8.0 SELLER VERIFICATION ]─────────────────────────────┐ │
│   │  • Upload Verification Documents                                                        │ │
│   │  • Admin Review Queue                                                                   │ │
│   │  • Approve/Reject Verification                                                          │ │
│   │  • Issue Verified Badge                                                                 │ │
│   │  • Send Notification                                                                    │ │
│   └──────────┬────────────────────────────────────────────────────────────────────────────────┘ │
│              │                                 ↕                                               │
│              ▼                             [Brevo SMTP]                                        │
│   ║ verification_documents ║                                                                  │
│                                                                                                │
│   ┌────────────────────────────────[ 9.0 DISPUTE MANAGEMENT ]──────────────────────────────┐ │
│   │  • File Dispute                                                                         │ │
│   │  • Upload Evidence                                                                      │ │
│   │  • Admin Investigation                                                                  │ │
│   │  • Resolution Decision                                                                  │ │
│   │  • Process Refund (if applicable)                                                       │ │
│   └──────────┬────────────────────────────────────────────────────────────────────────────────┘ │
│              │                                                                                 │
│              ▼                                                                                 │
│        ║ disputes ║ ← (linked to) ║ orders ║                                                  │
│                                                                                                │
│   ┌────────────────────────────────[ 10.0 ADMINISTRATION ]──────────────────────────────────┐ │
│   │  • User Account Management                                                              │ │
│   │  • Product Moderation                                                                   │ │
│   │  • Generate Reports                                                                     │ │
│   │  • System Configuration                                                                 │ │
│   │  • Audit Logging                                                                        │ │
│   └──────────┬────────────────────────────────────────────────────────────────────────────────┘ │
│              │                                                                                 │
│              ▼                                                                                 │
│        ║ admin_logs ║                                                                          │
│                                                                                                │
└────────────────────────────────────────────────────────────────────────────────────────────────┘
```

---

## 🔍 DFD LEVEL 1 - PROCESS DESCRIPTIONS

### Process 1.0: USER MANAGEMENT

**Inputs:**
- Registration data (from Buyers/Sellers)
- Login credentials (from all users)
- Email verification tokens
- Password reset requests

**Processing:**
- Validate input data
- Hash passwords (bcrypt)
- Generate verification tokens
- Send verification emails via Brevo
- Create/update user sessions
- Authenticate credentials

**Outputs:**
- User account records → users table
- Session tokens → sessions table
- Verification emails → Brevo SMTP
- Login success/failure messages → Users

**Data Stores:**
- users (read/write)
- sessions (read/write)
- password_resets (read/write)

---

### Process 2.0: PRODUCT MANAGEMENT

**Inputs:**
- Product details (from Sellers)
- Product images (3-5 per listing)
- Stock quantity updates
- Price changes

**Processing:**
- Validate product data
- Process image uploads
- Generate thumbnails (300x300px)
- Resize images (800x800px)
- Assign to category
- Set status (active/sold/suspended)

**Outputs:**
- Product records → products table
- Image records → product_images table
- Confirmation messages → Sellers

**Data Stores:**
- products (read/write)
- product_images (read/write)
- categories (read only)

---

### Process 3.0: SEARCH & DISCOVERY

**Inputs:**
- Search keywords (from Buyers)
- Filter criteria (category, price, location, condition)
- Sort preferences (newest, price, popular)
- Pagination parameters

**Processing:**
- Execute FULLTEXT search on product_name, description
- Apply filters (WHERE clauses)
- Join with users, categories, product_images
- Calculate relevance scores
- Apply pagination (12 per page)
- Sort results

**Outputs:**
- Filtered product listings → Buyers
- Product counts → Buyers

**Data Stores:**
- products (read only)
- users (read only - for seller info)
- categories (read only)
- product_images (read only)
- verification_documents (read only - for verified badge)

---

### Process 4.0: ORDER PROCESSING

**Inputs:**
- Add to cart requests (from Buyers)
- Checkout submissions
- Delivery addresses
- Order updates (from Sellers)

**Processing:**
- Create order record
- Calculate total (price × quantity)
- Reduce stock quantity (trigger)
- Generate order confirmation
- Send notifications to buyer and seller
- Track delivery status updates

**Outputs:**
- Order records → orders table
- Stock updates → products table
- Email notifications → Brevo SMTP
- Order confirmations → Buyers and Sellers

**Data Stores:**
- orders (read/write)
- products (read/write - stock updates)
- users (read only - for buyer/seller info)

---

### Process 5.0: PAYMENT PROCESSING

**Inputs:**
- Payment initiation (from Buyers via Order Processing)
- IPN callbacks (from PayFast)
- Refund requests (from Dispute Management)

**Processing:**
- Generate PayFast payment form
- Redirect to PayFast gateway
- Validate IPN signature (MD5 hash)
- Verify payment amount matches order
- Calculate platform fee (5%)
- Calculate seller payout (amount - fee)
- Update payment status
- Create transaction record

**Outputs:**
- Payment redirect → Buyers (to PayFast)
- Transaction records → transactions table
- Payment status updates → orders table
- Payment receipts → Brevo SMTP

**Data Stores:**
- orders (read/write - payment status)
- transactions (write only)

---

### Process 6.0: MESSAGING SYSTEM

**Inputs:**
- New message text (from Buyers/Sellers)
- Image attachments
- Read receipts
- Archive requests

**Processing:**
- Create/retrieve conversation
- Validate participants (buyer, seller, product)
- Store message text
- Process image attachments
- Update last_message_at timestamp
- Mark messages as read
- Send email notification if recipient offline

**Outputs:**
- Message records → messages table
- Conversation updates → conversations table
- Email notifications → Brevo SMTP (if recipient offline)
- Message delivery confirmations → Senders

**Data Stores:**
- conversations (read/write)
- messages (read/write)
- users (read only - for participant validation)
- products (read only - for conversation context)

---

### Process 7.0: REVIEW & RATING SYSTEM

**Inputs:**
- Review submissions (from Buyers)
- Ratings (1-5 stars)
- Seller responses
- Flag requests (for inappropriate reviews)

**Processing:**
- Validate verified purchase (order_id must exist)
- Store review text and rating
- Calculate seller's average rating
- Count rating distribution (5-star, 4-star, etc.)
- Allow one seller response per review
- Flag reviews for admin review

**Outputs:**
- Review records → reviews table
- Average ratings → Calculated view (view_seller_ratings)
- Review notifications → Sellers via Brevo

**Data Stores:**
- reviews (read/write)
- orders (read only - for verification)
- users (read only - for reviewer/seller info)

---

### Process 8.0: SELLER VERIFICATION

**Inputs:**
- Verification documents (from Sellers) - PDF/JPG/PNG
- Admin approval/rejection (from Admins)
- Rejection reasons

**Processing:**
- Validate document format and size
- Store document securely (encrypted path)
- Add to admin review queue
- Admin reviews document
- Approve/reject with reason
- Send notification email
- Update seller's verified status

**Outputs:**
- Document records → verification_documents table
- Email notifications → Sellers via Brevo
- Verified badge status → products display

**Data Stores:**
- verification_documents (read/write)
- users (read only)
- admin_logs (write only - for audit)

---

### Process 9.0: DISPUTE MANAGEMENT

**Inputs:**
- Dispute reports (from Buyers)
- Evidence files (photos, screenshots)
- Admin investigation notes
- Resolution decisions

**Processing:**
- Create dispute record linked to order
- Store evidence file paths (JSON array)
- Update dispute status (open → investigating → resolved)
- Admin reviews evidence
- Make resolution decision (buyer/seller favour, mutual)
- Trigger refund if needed (via Payment Processing)
- Record resolution notes

**Outputs:**
- Dispute records → disputes table
- Refund triggers → Payment Processing (if applicable)
- Resolution notifications → Buyers, Sellers via Brevo

**Data Stores:**
- disputes (read/write)
- orders (read only - for dispute context)
- users (read only)
- admin_logs (write only)

---

### Process 10.0: ADMINISTRATION

**Inputs:**
- Admin login credentials
- User management actions (suspend/unsuspend/delete)
- Product moderation actions (suspend/delete)
- Report generation requests
- System configuration changes

**Processing:**
- Authenticate admin credentials
- Check RBAC permissions (super_admin vs moderator)
- Execute administrative actions
- Log all actions with timestamp, IP, details
- Generate reports (transaction reports, user stats, etc.)
- Export reports (CSV, PDF)

**Outputs:**
- User account updates → users table
- Product status updates → products table
- Audit logs → admin_logs table
- Reports → Admins (download)

**Data Stores:**
- All tables (read access for reports)
- users, products (write access for moderation)
- admin_logs (write access for audit trail)

---

# 📊 DFD LEVEL 2 - DETAILED PROCESS: PAYMENT PROCESSING

Let's decompose **Process 5.0: Payment Processing** into detailed sub-processes:

```
┌─────────────────────────────────────────────────────────────────────────┐
│                   PROCESS 5.0: PAYMENT PROCESSING                       │
│                           (Detailed View)                               │
└─────────────────────────────────────────────────────────────────────────┘

BUYER (via Order Processing)
      │
      │ order_details
      ▼
┌──────────────────────────────┐
│  5.1 GENERATE PAYMENT FORM   │
│  • Get order details         │
│  • Build PayFast data array  │
│  • Generate signature (MD5)  │
│  • Create HTML form          │
└──────────┬───────────────────┘
           │ payment_form_html
           ▼
       [BUYER'S BROWSER]
           │ (redirects to)
           ▼
       [PayFast Gateway]
           │ (buyer completes payment)
           │
           │ IPN callback (POST)
           ▼
┌──────────────────────────────┐
│  5.2 VALIDATE IPN CALLBACK   │
│  • Receive POST data         │
│  • Verify POST from PayFast  │
│  • Check signature validity  │
│  • Confirm amounts match     │
└──────────┬───────────────────┘
           │ validation_result
           ├─── [INVALID] ──> Log error, reject
           │
           │ [VALID]
           ▼
┌──────────────────────────────┐
│  5.3 PROCESS PAYMENT         │
│  • Update order status       │
│  • Record PayFast ID         │
│  • Set payment_date          │
└──────────┬───────────────────┘
           │ payment_confirmed
           ▼
       ║ orders ║ (UPDATE payment_status='paid')
           │
           │ (triggers)
           ▼
┌──────────────────────────────┐
│  5.4 CREATE TRANSACTION      │
│  • Calculate platform fee    │
│    (transaction_amount * 5%) │
│  • Calculate seller payout   │
│    (amount - platform_fee)   │
│  • Store transaction record  │
└──────────┬───────────────────┘
           │ transaction_data
           ▼
       ║ transactions ║ (INSERT)
           │
           │ (triggers)
           ▼
┌──────────────────────────────┐
│  5.5 SEND CONFIRMATIONS      │
│  • Generate receipt (buyer)  │
│  • Generate notification     │
│    (seller)                  │
│  • Send via Brevo SMTP       │
└──────────┬───────────────────┘
           │ email_data
           ▼
       [Brevo SMTP]
           │
           ├──> BUYER (payment receipt)
           └──> SELLER (new order notification)
```

---

## 🔍 DFD LEVEL 2 - SUB-PROCESS DESCRIPTIONS

### Sub-Process 5.1: Generate Payment Form

**Inputs:**
- order_id
- total_amount
- buyer details (name, email)

**Processing Steps:**
1. Retrieve order details from database
2. Build PayFast data array:
   ```php
   $data = [
       'merchant_id' => PAYFAST_MERCHANT_ID,
       'merchant_key' => PAYFAST_MERCHANT_KEY,
       'return_url' => 'https://street2screen.../payment_success.php',
       'cancel_url' => 'https://street2screen.../payment_cancel.php',
       'notify_url' => 'https://street2screen.../payfast_ipn.php',
       'amount' => $order->total_amount,
       'item_name' => $order->product_name,
       'email_address' => $buyer->email
   ];
   ```
3. Generate MD5 signature for security validation
4. Create HTML form with hidden fields
5. Auto-submit form via JavaScript redirect

**Outputs:**
- HTML payment form sent to buyer's browser
- Redirect to PayFast gateway

---

### Sub-Process 5.2: Validate IPN Callback

**Inputs:**
- POST data from PayFast (IPN callback)
- $_POST['pf_payment_id']
- $_POST['amount_gross']
- $_POST['signature']

**Processing Steps:**
1. Verify POST request came from PayFast IP address
2. Rebuild signature from POST data
3. Compare calculated signature with received signature
4. Verify amount matches original order total
5. Check payment status (COMPLETE, CANCELLED, FAILED)
6. Validate merchant_id and merchant_key match config

**Outputs:**
- Validation result: VALID or INVALID
- If invalid: Log error, send alert, reject payment
- If valid: Proceed to Process Payment

**Security Checks:**
```php
// 1. Verify PayFast IP
$validIPs = ['197.97.145.144', '41.74.179.194'];
if (!in_array($_SERVER['REMOTE_ADDR'], $validIPs)) {
    die('Invalid IP');
}

// 2. Verify signature
$signature = md5(http_build_query($postData));
if ($signature !== $_POST['signature']) {
    die('Invalid signature');
}

// 3. Verify amount
if ($_POST['amount_gross'] != $order->total_amount) {
    die('Amount mismatch');
}
```

---

### Sub-Process 5.3: Process Payment

**Inputs:**
- Validated IPN data
- order_id
- pf_payment_id

**Processing Steps:**
1. Retrieve order record from database
2. Update order fields:
   ```sql
   UPDATE orders SET
       payment_status = 'paid',
       payment_date = NOW()
   WHERE order_id = ?
   ```
3. Store PayFast payment ID for reference
4. Trigger stock quantity reduction (if not already done)
5. Update product status to 'sold' if stock reaches 0

**Outputs:**
- Updated order record in database
- Payment confirmation flag for next sub-process

---

### Sub-Process 5.4: Create Transaction

**Inputs:**
- order_id
- transaction_amount (from IPN)
- pf_payment_id

**Processing Steps:**
1. Calculate platform fee:
   ```php
   $platform_fee = $transaction_amount * 0.05; // 5%
   ```
2. Calculate seller payout:
   ```php
   $seller_payout = $transaction_amount - $platform_fee;
   ```
3. Insert transaction record:
   ```sql
   INSERT INTO transactions (
       order_id, payfast_payment_id, transaction_amount,
       platform_fee, seller_payout, transaction_date
   ) VALUES (?, ?, ?, ?, ?, NOW())
   ```

**Outputs:**
- Transaction record created
- Financial data stored for reporting

---

### Sub-Process 5.5: Send Confirmations

**Inputs:**
- order_id
- buyer_email
- seller_email
- transaction details

**Processing Steps:**
1. Generate buyer receipt email (HTML template):
   - Order summary
   - Payment amount
   - Transaction ID
   - Delivery address
   - Seller contact info

2. Generate seller notification email:
   - New order alert
   - Buyer details
   - Delivery address
   - Payout amount (after platform fee)

3. Send both emails via Brevo SMTP API

**Outputs:**
- Email sent to buyer (receipt)
- Email sent to seller (order notification)
- Email delivery status logged

---

## 📐 DRAWING INSTRUCTIONS FOR DFDs

### For draw.io:

**Shapes to Use:**
1. **Processes (Bubbles):** Use rounded rectangle or circle
   - Color: Yellow (#FFC107)
   - Font: Bold, 12pt
   - Label format: "1.0 PROCESS NAME"

2. **External Entities:** Use rectangle
   - Color: Dark Blue (#0B1F3A), white text
   - Label: BUYERS, SELLERS, ADMINS, PayFast, Brevo

3. **Data Stores:** Use open rectangle (left side open)
   - Color: Grey (#F2F2F2)
   - Label format: ║ table_name ║

4. **Data Flows:** Use arrows
   - Color: Dark Blue
   - Label: Describe data (e.g., "registration_data", "order_details")

**Layout Tips:**
- Arrange processes in logical workflow order (top to bottom or left to right)
- Group related processes together
- Keep data flows short and clear
- Avoid crossing arrows when possible
- Use different levels of detail for Level 1 vs Level 2

---

## ✅ DFD CHECKLIST

- [x] DFD Level 0 (Context Diagram) - Shows system boundary
- [x] DFD Level 1 - Shows 10 major processes
- [x] DFD Level 2 - Details Payment Processing (5 sub-processes)
- [x] All processes numbered (1.0, 2.0, etc.)
- [x] All data flows labeled with data description
- [x] All data stores identified (15 database tables)
- [x] All external entities shown (Buyers, Sellers, Admins, PayFast, Brevo)
- [x] Processes described in detail
- [x] Inputs/Outputs documented for each process

---

**Document Created:** February 12, 2026  
**Student:** Ignatius Mayibongwe Khumalo  
**Institution:** Eduvos Private Institution  
**For:** Deliverable 2 - DFD (3 marks)
