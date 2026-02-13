# CLASS RESPONSIBILITY COLLABORATOR (CRC) CARDS
## Street2Screen ZA - C2C E-Commerce Platform

**Project:** Street2Screen ZA  
**Student:** Ignatius Mayibongwe Khumalo  
**Institution:** Eduvos Private Institution  
**Course:** ITECA3-12 Initial Project  
**Date:** February 2026

---

## 📚 CRC CARDS OVERVIEW

CRC Cards identify the **Classes** (objects), their **Responsibilities** (what they do), and **Collaborators** (what they work with). These cards form the foundation of our object-oriented design.

**Total Classes:** 15 core classes

---

## 1️⃣ USER CLASS

```
┌─────────────────────────────────────────────────────────────┐
│ CLASS: User                                                  │
├─────────────────────────────────────────────────────────────┤
│ RESPONSIBILITIES:                                            │
│ • Register new user account                                  │
│ • Authenticate user credentials (login)                      │
│ • Verify email address via token                             │
│ • Update profile information                                 │
│ • Reset forgotten password                                   │
│ • Manage user session state                                  │
│ • Upload profile picture                                     │
│ • Change user type (buyer/seller)                            │
├─────────────────────────────────────────────────────────────┤
│ ATTRIBUTES:                                                  │
│ • userId (INT, PRIMARY KEY)                                  │
│ • fullName (VARCHAR 100)                                     │
│ • email (VARCHAR 100, UNIQUE)                                │
│ • passwordHash (VARCHAR 255)                                 │
│ • userType (ENUM: buyer, seller, moderator, admin)           │
│ • phone (VARCHAR 15)                                         │
│ • address (TEXT)                                             │
│ • profilePicture (VARCHAR 255)                               │
│ • emailVerified (BOOLEAN)                                    │
│ • verificationToken (VARCHAR 64)                             │
│ • tokenExpiry (DATETIME)                                     │
│ • createdAt (TIMESTAMP)                                      │
│ • lastLogin (TIMESTAMP)                                      │
├─────────────────────────────────────────────────────────────┤
│ METHODS:                                                     │
│ • register(): bool                                           │
│ • login(email, password): bool                               │
│ • logout(): void                                             │
│ • verifyEmail(token): bool                                   │
│ • updateProfile(data): bool                                  │
│ • resetPassword(email): bool                                 │
│ • uploadProfilePicture(file): string                         │
├─────────────────────────────────────────────────────────────┤
│ COLLABORATORS:                                               │
│ • Database (CRUD operations)                                 │
│ • EmailService (verification, password reset)                │
│ • Session (authentication state)                             │
│ • FileUploadHandler (profile picture)                        │
│ • VerificationDocument (for sellers)                         │
└─────────────────────────────────────────────────────────────┘
```

---

## 2️⃣ PRODUCT CLASS

```
┌─────────────────────────────────────────────────────────────┐
│ CLASS: Product                                               │
├─────────────────────────────────────────────────────────────┤
│ RESPONSIBILITIES:                                            │
│ • Create new product listing                                 │
│ • Update product details                                     │
│ • Delete product listing                                     │
│ • Search products by keyword                                 │
│ • Filter products by category, price, location               │
│ • Increment product view count                               │
│ • Manage stock quantity                                      │
│ • Validate product data                                      │
├─────────────────────────────────────────────────────────────┤
│ ATTRIBUTES:                                                  │
│ • productId (INT, PRIMARY KEY)                               │
│ • sellerId (INT, FOREIGN KEY → users.id)                     │
│ • productName (VARCHAR 100)                                  │
│ • categoryId (INT, FOREIGN KEY → categories.id)              │
│ • description (TEXT)                                         │
│ • price (DECIMAL 10,2)                                       │
│ • stockQuantity (INT)                                        │
│ • location (VARCHAR 100)                                     │
│ • condition (ENUM: new, like_new, good, fair)                │
│ • status (ENUM: active, sold, suspended)                     │
│ • viewCount (INT, DEFAULT 0)                                 │
│ • createdAt (TIMESTAMP)                                      │
│ • updatedAt (TIMESTAMP)                                      │
├─────────────────────────────────────────────────────────────┤
│ METHODS:                                                     │
│ • create(data): int                                          │
│ • update(productId, data): bool                              │
│ • delete(productId): bool                                    │
│ • search(keyword): array                                     │
│ • filter(criteria): array                                    │
│ • incrementViews(productId): void                            │
│ • updateStock(productId, quantity): bool                     │
│ • getById(productId): object                                 │
│ • getBySeller(sellerId): array                               │
├─────────────────────────────────────────────────────────────┤
│ COLLABORATORS:                                               │
│ • User (seller relationship)                                 │
│ • Category (classification)                                  │
│ • ProductImage (image management)                            │
│ • Order (purchase tracking)                                  │
│ • Database (storage)                                         │
└─────────────────────────────────────────────────────────────┘
```

---

## 3️⃣ PRODUCT IMAGE CLASS

```
┌─────────────────────────────────────────────────────────────┐
│ CLASS: ProductImage                                          │
├─────────────────────────────────────────────────────────────┤
│ RESPONSIBILITIES:                                            │
│ • Upload product images (3-5 per product)                    │
│ • Generate thumbnails (300x300px)                            │
│ • Generate detail images (800x800px)                         │
│ • Set primary display image                                  │
│ • Delete product images                                      │
│ • Validate image format and size                             │
├─────────────────────────────────────────────────────────────┤
│ ATTRIBUTES:                                                  │
│ • imageId (INT, PRIMARY KEY)                                 │
│ • productId (INT, FOREIGN KEY → products.id CASCADE DELETE)  │
│ • imagePath (VARCHAR 255)                                    │
│ • thumbnailPath (VARCHAR 255)                                │
│ • isPrimary (BOOLEAN, DEFAULT 0)                             │
│ • uploadedAt (TIMESTAMP)                                     │
├─────────────────────────────────────────────────────────────┤
│ METHODS:                                                     │
│ • upload(file, productId): bool                              │
│ • generateThumbnail(imagePath): string                       │
│ • setPrimary(imageId): bool                                  │
│ • delete(imageId): bool                                      │
│ • getByProduct(productId): array                             │
├─────────────────────────────────────────────────────────────┤
│ COLLABORATORS:                                               │
│ • Product (parent relationship)                              │
│ • FileUploadHandler (file processing)                        │
│ • ImageProcessor (GD library wrapper for resizing)           │
│ • Database (storage)                                         │
└─────────────────────────────────────────────────────────────┘
```

---

## 4️⃣ CATEGORY CLASS

```
┌─────────────────────────────────────────────────────────────┐
│ CLASS: Category                                              │
├─────────────────────────────────────────────────────────────┤
│ RESPONSIBILITIES:                                            │
│ • Retrieve all categories                                    │
│ • Get category details by ID                                 │
│ • Count products in category                                 │
│ • Manage category icons                                      │
├─────────────────────────────────────────────────────────────┤
│ ATTRIBUTES:                                                  │
│ • categoryId (INT, PRIMARY KEY)                              │
│ • categoryName (VARCHAR 50)                                  │
│ • description (TEXT)                                         │
│ • iconClass (VARCHAR 50 - Font Awesome class)                │
│ • displayOrder (INT, DEFAULT 0)                              │
├─────────────────────────────────────────────────────────────┤
│ FIXED CATEGORIES:                                            │
│ 1. Clothing & Fashion                                        │
│ 2. Electronics & Accessories                                 │
│ 3. Home & Kitchen                                            │
│ 4. Food & Drinks                                             │
│ 5. Handmade & Crafts                                         │
├─────────────────────────────────────────────────────────────┤
│ METHODS:                                                     │
│ • getAll(): array                                            │
│ • getById(categoryId): object                                │
│ • getProductCount(categoryId): int                           │
├─────────────────────────────────────────────────────────────┤
│ COLLABORATORS:                                               │
│ • Product (classification)                                   │
│ • Database (storage)                                         │
└─────────────────────────────────────────────────────────────┘
```

---

## 5️⃣ ORDER CLASS

```
┌─────────────────────────────────────────────────────────────┐
│ CLASS: Order                                                 │
├─────────────────────────────────────────────────────────────┤
│ RESPONSIBILITIES:                                            │
│ • Create new order                                           │
│ • Calculate total amount (price × quantity)                  │
│ • Update payment status                                      │
│ • Update delivery status                                     │
│ • Generate invoice/receipt                                   │
│ • Process refunds                                            │
│ • Track order history                                        │
├─────────────────────────────────────────────────────────────┤
│ ATTRIBUTES:                                                  │
│ • orderId (INT, PRIMARY KEY)                                 │
│ • buyerId (INT, FOREIGN KEY → users.id)                      │
│ • sellerId (INT, FOREIGN KEY → users.id)                     │
│ • productId (INT, FOREIGN KEY → products.id)                 │
│ • quantity (INT)                                             │
│ • totalAmount (DECIMAL 10,2)                                 │
│ • paymentMethod (ENUM: payfast, cod, eft)                    │
│ • paymentStatus (ENUM: pending, paid, failed, refunded)      │
│ • deliveryAddress (TEXT)                                     │
│ • deliveryStatus (ENUM: pending, shipped, delivered)         │
│ • orderDate (TIMESTAMP)                                      │
│ • paymentDate (TIMESTAMP NULL)                               │
│ • deliveryDate (TIMESTAMP NULL)                              │
├─────────────────────────────────────────────────────────────┤
│ METHODS:                                                     │
│ • create(data): int                                          │
│ • calculateTotal(price, quantity): decimal                   │
│ • updatePaymentStatus(orderId, status): bool                 │
│ • updateDeliveryStatus(orderId, status): bool                │
│ • generateInvoice(orderId): string                           │
│ • processRefund(orderId): bool                               │
│ • getByBuyer(buyerId): array                                 │
│ • getBySeller(sellerId): array                               │
├─────────────────────────────────────────────────────────────┤
│ COLLABORATORS:                                               │
│ • User (buyer and seller)                                    │
│ • Product (item purchased)                                   │
│ • Payment (transaction processing)                           │
│ • Transaction (financial records)                            │
│ • EmailService (order confirmations)                         │
│ • Database (storage)                                         │
└─────────────────────────────────────────────────────────────┘
```

---

## 6️⃣ PAYMENT CLASS

```
┌─────────────────────────────────────────────────────────────┐
│ CLASS: Payment                                               │
├─────────────────────────────────────────────────────────────┤
│ RESPONSIBILITIES:                                            │
│ • Initiate PayFast payment gateway                           │
│ • Validate IPN (Instant Payment Notification) callback       │
│ • Verify payment signature (MD5 hash)                        │
│ • Process payment confirmation                               │
│ • Handle payment failures                                    │
│ • Generate payment URLs                                      │
├─────────────────────────────────────────────────────────────┤
│ ATTRIBUTES:                                                  │
│ • paymentId (INT, PRIMARY KEY)                               │
│ • orderId (INT, FOREIGN KEY → orders.id)                     │
│ • payfastPaymentId (VARCHAR 100)                             │
│ • amount (DECIMAL 10,2)                                      │
│ • status (ENUM: pending, completed, failed, refunded)        │
│ • paymentDate (TIMESTAMP)                                    │
│ • ipnData (TEXT JSON)                                        │
├─────────────────────────────────────────────────────────────┤
│ METHODS:                                                     │
│ • initiatePayment(orderId): string (redirect URL)            │
│ • validateIPN(postData): bool                                │
│ • verifySignature(data, signature): bool                     │
│ • confirmPayment(orderId): bool                              │
│ • handleFailure(orderId, reason): void                       │
│ • generatePaymentForm(orderData): string                     │
├─────────────────────────────────────────────────────────────┤
│ COLLABORATORS:                                               │
│ • Order (payment subject)                                    │
│ • PayFastAPI (external gateway)                              │
│ • Transaction (record keeping)                               │
│ • EmailService (payment receipts)                            │
│ • Database (storage)                                         │
└─────────────────────────────────────────────────────────────┘
```

---

## 7️⃣ TRANSACTION CLASS

```
┌─────────────────────────────────────────────────────────────┐
│ CLASS: Transaction                                           │
├─────────────────────────────────────────────────────────────┤
│ RESPONSIBILITIES:                                            │
│ • Record all financial transactions                          │
│ • Calculate platform fees (5% commission)                    │
│ • Calculate seller payouts                                   │
│ • Generate transaction reports                               │
│ • Track payout status                                        │
├─────────────────────────────────────────────────────────────┤
│ ATTRIBUTES:                                                  │
│ • transactionId (INT, PRIMARY KEY)                           │
│ • orderId (INT, FOREIGN KEY → orders.id)                     │
│ • payfastPaymentId (VARCHAR 100)                             │
│ • transactionAmount (DECIMAL 10,2)                           │
│ • platformFee (DECIMAL 10,2 - calculated 5%)                 │
│ • sellerPayout (DECIMAL 10,2 - amount minus fee)             │
│ • transactionDate (TIMESTAMP)                                │
│ • payoutDate (TIMESTAMP NULL)                                │
│ • payoutStatus (ENUM: pending, processed, failed)            │
├─────────────────────────────────────────────────────────────┤
│ METHODS:                                                     │
│ • create(orderData): int                                     │
│ • calculatePlatformFee(amount): decimal                      │
│ • calculateSellerPayout(amount, fee): decimal                │
│ • generateReport(sellerId, dateRange): array                 │
│ • processPayout(transactionId): bool                         │
├─────────────────────────────────────────────────────────────┤
│ COLLABORATORS:                                               │
│ • Order (transaction source)                                 │
│ • Payment (payment confirmation)                             │
│ • User (seller receiving payout)                             │
│ • Database (storage)                                         │
└─────────────────────────────────────────────────────────────┘
```

---

## 8️⃣ REVIEW CLASS

```
┌─────────────────────────────────────────────────────────────┐
│ CLASS: Review                                                │
├─────────────────────────────────────────────────────────────┤
│ RESPONSIBILITIES:                                            │
│ • Submit product/seller review                               │
│ • Calculate average rating for seller                        │
│ • Validate review authenticity (verified purchase)           │
│ • Allow seller response to review                            │
│ • Flag inappropriate reviews                                 │
├─────────────────────────────────────────────────────────────┤
│ ATTRIBUTES:                                                  │
│ • reviewId (INT, PRIMARY KEY)                                │
│ • orderId (INT, FOREIGN KEY → orders.id - ensures verified)  │
│ • reviewerId (INT, FOREIGN KEY → users.id)                   │
│ • sellerId (INT, FOREIGN KEY → users.id)                     │
│ • rating (INT 1-5)                                           │
│ • reviewText (TEXT)                                          │
│ • sellerResponse (TEXT NULL)                                 │
│ • responseDate (TIMESTAMP NULL)                              │
│ • createdAt (TIMESTAMP)                                      │
│ • helpfulCount (INT DEFAULT 0)                               │
│ • flagged (BOOLEAN DEFAULT 0)                                │
├─────────────────────────────────────────────────────────────┤
│ METHODS:                                                     │
│ • submit(data): int                                          │
│ • calculateAverageRating(sellerId): decimal                  │
│ • validatePurchase(buyerId, sellerId): bool                  │
│ • postResponse(reviewId, response): bool                     │
│ • flagReview(reviewId, reason): void                         │
│ • getBySeller(sellerId): array                               │
├─────────────────────────────────────────────────────────────┤
│ COLLABORATORS:                                               │
│ • Order (purchase verification)                              │
│ • User (reviewer and seller)                                 │
│ • Database (storage)                                         │
└─────────────────────────────────────────────────────────────┘
```

---

## 9️⃣ MESSAGE CLASS

```
┌─────────────────────────────────────────────────────────────┐
│ CLASS: Message                                               │
├─────────────────────────────────────────────────────────────┤
│ RESPONSIBILITIES:                                            │
│ • Send message between users                                 │
│ • Retrieve conversation messages                             │
│ • Mark message as read                                       │
│ • Upload image attachments                                   │
│ • Delete messages                                            │
├─────────────────────────────────────────────────────────────┤
│ ATTRIBUTES:                                                  │
│ • messageId (INT, PRIMARY KEY)                               │
│ • conversationId (INT, FOREIGN KEY → conversations.id)       │
│ • senderId (INT, FOREIGN KEY → users.id)                     │
│ • messageText (TEXT)                                         │
│ • attachmentPath (VARCHAR 255 NULL)                          │
│ • readStatus (BOOLEAN DEFAULT 0)                             │
│ • sentAt (TIMESTAMP)                                         │
├─────────────────────────────────────────────────────────────┤
│ METHODS:                                                     │
│ • send(conversationId, senderId, text): int                  │
│ • getConversationMessages(conversationId): array             │
│ • markAsRead(messageId): bool                                │
│ • uploadAttachment(file): string                             │
│ • delete(messageId): bool                                    │
├─────────────────────────────────────────────────────────────┤
│ COLLABORATORS:                                               │
│ • Conversation (parent container)                            │
│ • User (sender and recipient)                                │
│ • FileUploadHandler (attachments)                            │
│ • EmailService (new message notifications)                   │
│ • Database (storage)                                         │
└─────────────────────────────────────────────────────────────┘
```

---

## 🔟 CONVERSATION CLASS

```
┌─────────────────────────────────────────────────────────────┐
│ CLASS: Conversation                                          │
├─────────────────────────────────────────────────────────────┤
│ RESPONSIBILITIES:                                            │
│ • Create conversation thread                                 │
│ • Retrieve user's conversations                              │
│ • Archive conversation                                       │
│ • Get unread message count                                   │
│ • Update last message timestamp                              │
├─────────────────────────────────────────────────────────────┤
│ ATTRIBUTES:                                                  │
│ • conversationId (INT, PRIMARY KEY)                          │
│ • buyerId (INT, FOREIGN KEY → users.id)                      │
│ • sellerId (INT, FOREIGN KEY → users.id)                     │
│ • productId (INT, FOREIGN KEY → products.id)                 │
│ • status (ENUM: active, archived)                            │
│ • createdAt (TIMESTAMP)                                      │
│ • lastMessageAt (TIMESTAMP)                                  │
├─────────────────────────────────────────────────────────────┤
│ METHODS:                                                     │
│ • create(buyerId, sellerId, productId): int                  │
│ • getUserConversations(userId): array                        │
│ • archive(conversationId): bool                              │
│ • getUnreadCount(conversationId, userId): int                │
│ • updateLastMessage(conversationId): void                    │
├─────────────────────────────────────────────────────────────┤
│ COLLABORATORS:                                               │
│ • Message (child messages)                                   │
│ • User (participants)                                        │
│ • Product (conversation subject)                             │
│ • Database (storage)                                         │
└─────────────────────────────────────────────────────────────┘
```

---

## 1️⃣1️⃣ VERIFICATION DOCUMENT CLASS

```
┌─────────────────────────────────────────────────────────────┐
│ CLASS: VerificationDocument                                  │
├─────────────────────────────────────────────────────────────┤
│ RESPONSIBILITIES:                                            │
│ • Upload seller verification documents                       │
│ • Validate document format (PDF, JPG, PNG)                   │
│ • Store document securely (encrypted)                        │
│ • Retrieve pending verifications                             │
│ • Update verification status                                 │
├─────────────────────────────────────────────────────────────┤
│ ATTRIBUTES:                                                  │
│ • documentId (INT, PRIMARY KEY)                              │
│ • userId (INT, FOREIGN KEY → users.id)                       │
│ • documentPath (VARCHAR 255 - encrypted storage)             │
│ • documentType (ENUM: id_book, drivers_license, passport,    │
│                       business_registration)                 │
│ • verificationStatus (ENUM: pending, approved, rejected)     │
│ • rejectionReason (TEXT NULL)                                │
│ • reviewedBy (INT, FOREIGN KEY → users.id NULL - admin)      │
│ • uploadedAt (TIMESTAMP)                                     │
│ • reviewedAt (TIMESTAMP NULL)                                │
├─────────────────────────────────────────────────────────────┤
│ METHODS:                                                     │
│ • upload(file, userId): int                                  │
│ • getPending(): array                                        │
│ • approve(documentId, adminId): bool                         │
│ • reject(documentId, adminId, reason): bool                  │
│ • getByUser(userId): object                                  │
├─────────────────────────────────────────────────────────────┤
│ COLLABORATORS:                                               │
│ • User (seller being verified)                               │
│ • Admin (reviewer)                                           │
│ • FileUploadHandler (document upload)                        │
│ • EmailService (approval/rejection notifications)            │
│ • Database (storage)                                         │
└─────────────────────────────────────────────────────────────┘
```

---

## 1️⃣2️⃣ DISPUTE CLASS

```
┌─────────────────────────────────────────────────────────────┐
│ CLASS: Dispute                                               │
├─────────────────────────────────────────────────────────────┤
│ RESPONSIBILITIES:                                            │
│ • File dispute for problematic order                         │
│ • Upload evidence (photos, screenshots)                      │
│ • Update dispute status                                      │
│ • Record resolution outcome                                  │
│ • Escalate unresolved disputes                               │
├─────────────────────────────────────────────────────────────┤
│ ATTRIBUTES:                                                  │
│ • disputeId (INT, PRIMARY KEY)                               │
│ • orderId (INT, FOREIGN KEY → orders.id)                     │
│ • reportedBy (INT, FOREIGN KEY → users.id - buyer)           │
│ • disputeReason (ENUM: non_delivery, not_as_described,       │
│                        damaged, seller_unresponsive, other)  │
│ • description (TEXT)                                         │
│ • evidencePaths (TEXT - JSON array of file paths)            │
│ • status (ENUM: open, investigating, resolved, closed)       │
│ • resolutionNotes (TEXT NULL)                                │
│ • resolutionOutcome (ENUM: buyer_favour, seller_favour,      │
│                            mutual_agreement, insufficient)   │
│ • createdAt (TIMESTAMP)                                      │
│ • resolvedAt (TIMESTAMP NULL)                                │
│ • resolvedBy (INT, FOREIGN KEY → users.id NULL - moderator)  │
├─────────────────────────────────────────────────────────────┤
│ METHODS:                                                     │
│ • create(data): int                                          │
│ • uploadEvidence(files): array                               │
│ • updateStatus(disputeId, status): bool                      │
│ • resolve(disputeId, moderatorId, outcome, notes): bool      │
│ • escalate(disputeId): void                                  │
│ • getOpen(): array                                           │
├─────────────────────────────────────────────────────────────┤
│ COLLABORATORS:                                               │
│ • Order (disputed transaction)                               │
│ • User (buyer, seller, moderator)                            │
│ • Admin (dispute resolution)                                 │
│ • FileUploadHandler (evidence upload)                        │
│ • EmailService (notifications)                               │
│ • Database (storage)                                         │
└─────────────────────────────────────────────────────────────┘
```

---

## 1️⃣3️⃣ ADMIN CLASS

```
┌─────────────────────────────────────────────────────────────┐
│ CLASS: Admin                                                 │
├─────────────────────────────────────────────────────────────┤
│ RESPONSIBILITIES:                                            │
│ • Approve/reject seller verifications                        │
│ • Moderate product listings                                  │
│ • Manage user accounts (suspend, delete)                     │
│ • Resolve disputes                                           │
│ • Generate platform reports                                  │
│ • Configure system settings                                  │
│ • Log all administrative actions                             │
├─────────────────────────────────────────────────────────────┤
│ ATTRIBUTES:                                                  │
│ • adminId (INT, links to users.id)                           │
│ • role (ENUM: super_admin, moderator)                        │
│ • permissions (TEXT - JSON array)                            │
├─────────────────────────────────────────────────────────────┤
│ PERMISSIONS (RBAC):                                          │
│ SUPER ADMIN:                                                 │
│ • All moderator permissions                                  │
│ • Create/delete moderator accounts                           │
│ • Access financial reports                                   │
│ • Configure platform settings                                │
│ • Delete any user account                                    │
│                                                              │
│ MODERATOR:                                                   │
│ • Approve/reject seller verifications                        │
│ • Suspend/unsuspend products                                 │
│ • Suspend/unsuspend user accounts                            │
│ • Resolve disputes                                           │
│ • View reports (non-financial)                               │
├─────────────────────────────────────────────────────────────┤
│ METHODS:                                                     │
│ • approveSeller(documentId): bool                            │
│ • rejectSeller(documentId, reason): bool                     │
│ • moderateProduct(productId, action): bool                   │
│ • suspendUser(userId, reason, duration): bool                │
│ • deleteUser(userId): bool                                   │
│ • resolveDispute(disputeId, outcome): bool                   │
│ • generateReport(type, params): array                        │
│ • logAction(action, details): void                           │
│ • hasPermission(permission): bool                            │
├─────────────────────────────────────────────────────────────┤
│ COLLABORATORS:                                               │
│ • User (account management)                                  │
│ • Product (content moderation)                               │
│ • VerificationDocument (seller approval)                     │
│ • Dispute (resolution)                                       │
│ • AdminLog (audit trail)                                     │
│ • Database (storage)                                         │
└─────────────────────────────────────────────────────────────┘
```

---

## 1️⃣4️⃣ ADMIN LOG CLASS

```
┌─────────────────────────────────────────────────────────────┐
│ CLASS: AdminLog                                              │
├─────────────────────────────────────────────────────────────┤
│ RESPONSIBILITIES:                                            │
│ • Record all administrative actions                          │
│ • Track who did what and when                                │
│ • Provide audit trail for security                           │
│ • Enable accountability                                      │
├─────────────────────────────────────────────────────────────┤
│ ATTRIBUTES:                                                  │
│ • logId (INT, PRIMARY KEY)                                   │
│ • adminId (INT, FOREIGN KEY → users.id)                      │
│ • actionType (VARCHAR 50 - e.g., 'approve_seller',           │
│               'suspend_user', 'delete_product')              │
│ • targetType (VARCHAR 50 - e.g., 'user', 'product')          │
│ • targetId (INT - ID of affected entity)                     │
│ • actionDetails (TEXT - JSON with additional info)           │
│ • ipAddress (VARCHAR 45)                                     │
│ • timestamp (TIMESTAMP)                                      │
├─────────────────────────────────────────────────────────────┤
│ METHODS:                                                     │
│ • log(adminId, action, target, details): void                │
│ • getByAdmin(adminId): array                                 │
│ • getByTarget(targetType, targetId): array                   │
│ • getRecent(limit): array                                    │
├─────────────────────────────────────────────────────────────┤
│ COLLABORATORS:                                               │
│ • Admin (action performer)                                   │
│ • Database (storage)                                         │
└─────────────────────────────────────────────────────────────┘
```

---

## 1️⃣5️⃣ TRANSLATION CLASS

```
┌─────────────────────────────────────────────────────────────┐
│ CLASS: Translation                                           │
├─────────────────────────────────────────────────────────────┤
│ RESPONSIBILITIES:                                            │
│ • Retrieve translated text for UI elements                   │
│ • Support 11 South African languages                         │
│ • Manage translation keys and values                         │
│ • Cache translations for performance                         │
├─────────────────────────────────────────────────────────────┤
│ ATTRIBUTES:                                                  │
│ • translationId (INT, PRIMARY KEY)                           │
│ • languageCode (VARCHAR 5 - en, af, zu, xh, st, nso, tn,     │
│                 ss, nr, ve, ts)                              │
│ • translationKey (VARCHAR 100 - e.g., 'btn_login')           │
│ • translationText (TEXT - actual translated text)            │
│ • createdAt (TIMESTAMP)                                      │
│ • updatedAt (TIMESTAMP)                                      │
│ • UNIQUE KEY (languageCode, translationKey)                  │
├─────────────────────────────────────────────────────────────┤
│ SUPPORTED LANGUAGES:                                         │
│ • en - English                                               │
│ • af - Afrikaans                                             │
│ • zu - isiZulu                                               │
│ • xh - isiXhosa                                              │
│ • st - Sesotho                                               │
│ • nso - Sepedi                                               │
│ • tn - Setswana                                              │
│ • ss - siSwati                                               │
│ • nr - isiNdebele                                            │
│ • ve - Tshivenda                                             │
│ • ts - Xitsonga                                              │
├─────────────────────────────────────────────────────────────┤
│ METHODS:                                                     │
│ • translate(key, languageCode): string                       │
│ • getAllByLanguage(languageCode): array                      │
│ • setTranslation(key, language, text): bool                  │
│ • getAvailableLanguages(): array                             │
├─────────────────────────────────────────────────────────────┤
│ COLLABORATORS:                                               │
│ • Session (user language preference)                         │
│ • Database (storage)                                         │
└─────────────────────────────────────────────────────────────┘
```

---

## 📊 CRC CARD SUMMARY

**Total Classes:** 15  
**Total Collaborations:** 47 distinct interactions  
**Database Tables:** 15 core tables

### Class Relationships:
```
User ← has many → Product
User ← has many → Order (as buyer)
User ← has many → Order (as seller)
Product ← has many → ProductImage
Product ← belongs to → Category
Order ← has one → Payment
Order ← has one → Transaction
Order ← can have one → Review
Order ← can have one → Dispute
User ← has many → Message
Conversation ← has many → Message
User ← has one → VerificationDocument (if seller)
Admin ← has many → AdminLog
Translation ← groups by → Language
```

---

## ✅ NEXT STEPS

These CRC Cards directly map to:
1. **Database Tables** (EERD in next step)
2. **PHP Classes** (OOP structure)
3. **API Endpoints** (functionality)
4. **UI Components** (features)

**Ready for EERD (Enhanced Entity Relationship Diagram)?**

---

**Document Created:** February 12, 2026  
**Student:** Ignatius Mayibongwe Khumalo  
**Institution:** Eduvos Private Institution  
**Project:** Street2Screen ZA C2C E-Commerce Platform
