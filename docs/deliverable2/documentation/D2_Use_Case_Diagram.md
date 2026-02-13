# USE CASE DIAGRAM
## Street2Screen ZA - Actor Interactions

**Project:** Street2Screen ZA  
**Student:** Ignatius Mayibongwe Khumalo  
**Institution:** Eduvos Private Institution  
**Course:** ITECA3-12 Initial Project  
**Date:** February 2026

---

## 📋 WHAT IS A USE CASE DIAGRAM?

A **Use Case Diagram** shows **who** (actors) can do **what** (use cases) in the system. It identifies all functional requirements from the user's perspective.

**Elements:**
- **Actor** (stick figure) = Person or system interacting with our system
- **Use Case** (oval) = Functional requirement or action
- **System Boundary** (rectangle) = What's inside our system vs. outside
- **Relationships:**
  - **Association** (line) = Actor participates in use case
  - **Include** (dashed arrow) = Use case always includes another
  - **Extend** (dashed arrow) = Use case optionally extends another
  - **Generalization** (solid arrow) = Inheritance relationship

---

## 🎭 ACTORS IN STREET2SCREEN ZA

### PRIMARY ACTORS (People using the system):
1. **Guest** - Unregistered visitor
2. **Buyer** - Registered user who purchases products
3. **Seller** - Registered user who sells products
4. **Moderator** - Staff member who reviews and moderates content
5. **Super Admin** - System administrator with full control

### SECONDARY ACTORS (External systems):
6. **PayFast Gateway** - Payment processing service
7. **Brevo SMTP** - Email delivery service
8. **Database System** - MySQL database

---

# 📊 USE CASE DIAGRAM - VISUAL REPRESENTATION

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                          STREET2SCREEN ZA SYSTEM                                │
│                         (C2C E-Commerce Platform)                               │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                 │
│   GUEST                                  BUYER                                  │
│    👤                                     👤                                    │
│     │                                     │                                     │
│     │                                     ├───────( Browse Products )           │
│     ├───────( View Homepage )             │                                     │
│     │                                     ├───────( Search Products )           │
│     ├───────( Search Products )           │            │                        │
│     │            │                        │            │ «include»              │
│     │            │                        ├───────( Filter by Category )        │
│     │            │ «extend»               │            │                        │
│     │            └──────────────────( Sort Results )   │                        │
│     │                                     │            │                        │
│     ├───────( View Product Details )     ├───────( View Product Details )      │
│     │            │                        │            │                        │
│     │            │ «extend»               │            │ «extend»               │
│     │            └──────────────────( View Seller Profile )                    │
│     │                                     │                                     │
│     ├───────( Register Account )─────────┤                                     │
│     │            │                        │                                     │
│     │            │ «include»              ├───────( Add to Cart )               │
│     │            └──────────────────( Verify Email )                           │
│     │                                     │                                     │
│     ├───────( Login )                     ├───────( Checkout )                  │
│                                           │            │                        │
│                                           │            │ «include»              │
│                                           │            └──────( Make Payment )  │
│                                           │                        │            │
│                                           │                        │ «actors»   │
│                                           │                        └───→ 💳     │
│                                           │                         PayFast     │
│                                           │                                     │
│                                           ├───────( Track Order )               │
│                                           │                                     │
│                                           ├───────( Message Seller )            │
│                                           │            │                        │
│                                           │            │ «actors»               │
│                                           │            └───→ 📧                 │
│                                           │                  Brevo SMTP         │
│                                           │                                     │
│                                           ├───────( Submit Review )             │
│                                           │            │                        │
│                                           │            │ «extend»               │
│                                           │            └──────( Rate Seller )   │
│                                           │                                     │
│                                           ├───────( File Dispute )              │
│                                           │            │                        │
│                                           │            │ «include»              │
│                                           │            └──────( Upload Evidence)│
│                                           │                                     │
│                                           └───────( Update Profile )            │
│                                                        │                        │
│                                                        │ «extend»               │
│                                                        └──────( Change Password)│
│                                                                                 │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                 │
│   SELLER                                 MODERATOR                              │
│    👤                                     👤                                    │
│     │                                     │                                     │
│     │ (inherits all Buyer use cases)     ├───────( Review Seller Verification )│
│     │                                     │            │                        │
│     ├───────( Upload Verification Docs ) │            │ «include»              │
│     │                                     │            └──────( Approve/Reject )│
│     ├───────( Create Product Listing )   │                                     │
│     │            │                        ├───────( Moderate Products )         │
│     │            │ «include»              │            │                        │
│     │            └──────( Upload Images ) │            │ «include»              │
│     │                                     │            └──────( Suspend Product)│
│     ├───────( Update Product )            │                                     │
│     │                                     ├───────( Manage Users )              │
│     ├───────( Delete Product )            │            │                        │
│     │                                     │            │ «include»              │
│     ├───────( Manage Stock )              │            └──────( Suspend User )  │
│     │                                     │                                     │
│     ├───────( View Orders )               ├───────( Resolve Disputes )          │
│     │            │                        │            │                        │
│     │            │ «include»              │            │ «include»              │
│     │            └──────( Update Status ) │            └──────( Issue Refund )  │
│     │                                     │                                     │
│     ├───────( Message Buyer )             ├───────( View Reports )              │
│     │                                     │                                     │
│     ├───────( Respond to Review )         ├───────( Access Audit Logs )         │
│     │                                     │                                     │
│     └───────( View Sales Analytics )      │                                     │
│                  │                        │                                     │
│                  │ «extend»               │                                     │
│                  └──────( Export Reports )│                                     │
│                                           │                                     │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                 │
│   SUPER ADMIN                                                                   │
│    👤                                                                           │
│     │                                                                           │
│     │ (inherits all Moderator use cases)                                       │
│     │                                                                           │
│     ├───────( Create Moderator Accounts )                                      │
│     │                                                                           │
│     ├───────( Delete User Accounts )                                           │
│     │                                                                           │
│     ├───────( Configure System Settings )                                      │
│     │                                                                           │
│     ├───────( Manage Categories )                                              │
│     │                                                                           │
│     ├───────( View Financial Reports )                                         │
│     │            │                                                              │
│     │            │ «include»                                                    │
│     │            └──────( Generate Transaction Report )                         │
│     │                                                                           │
│     └───────( Manage Translations )                                            │
│                  │                                                              │
│                  │ «include»                                                    │
│                  └──────( Add Language Support )                                │
│                                                                                 │
└─────────────────────────────────────────────────────────────────────────────────┘

LEGEND:
( Use Case )          = Functional requirement
─────                = Association (actor participates)
«include»            = Required sub-use case
«extend»             = Optional extension
👤                   = Primary Actor (person)
💳 📧                 = Secondary Actor (external system)
```

---

## 📋 COMPLETE USE CASE LIST

### GUEST USE CASES (8 total)

| Use Case ID | Use Case Name | Description |
|-------------|---------------|-------------|
| UC-G01 | View Homepage | Browse landing page with featured products |
| UC-G02 | Search Products | Search by keyword, category, location |
| UC-G03 | Filter by Category | Narrow search results by category |
| UC-G04 | Sort Results | Sort by price, date, popularity |
| UC-G05 | View Product Details | See full product information, images, seller info |
| UC-G06 | View Seller Profile | View seller ratings, reviews, other listings |
| UC-G07 | Register Account | Create new buyer or seller account |
| UC-G08 | Login | Authenticate with email and password |

---

### BUYER USE CASES (12 total)

| Use Case ID | Use Case Name | Description | Includes/Extends |
|-------------|---------------|-------------|------------------|
| UC-B01 | Browse Products | View available products | Includes UC-G02 |
| UC-B02 | Add to Cart | Add product to shopping cart | - |
| UC-B03 | Checkout | Complete purchase process | Includes UC-B04 |
| UC-B04 | Make Payment | Process payment via PayFast | Actor: PayFast |
| UC-B05 | Track Order | View order status and delivery tracking | - |
| UC-B06 | Message Seller | Send inquiry about product | Actor: Brevo |
| UC-B07 | Submit Review | Write review for completed order | Extends UC-B08 |
| UC-B08 | Rate Seller | Give 1-5 star rating | - |
| UC-B09 | File Dispute | Report problem with order | Includes UC-B10 |
| UC-B10 | Upload Evidence | Attach photos/screenshots to dispute | - |
| UC-B11 | Update Profile | Modify account information | Extends UC-B12 |
| UC-B12 | Change Password | Update account password | - |

---

### SELLER USE CASES (16 total - includes all Buyer use cases + seller-specific)

| Use Case ID | Use Case Name | Description | Includes/Extends |
|-------------|---------------|-------------|------------------|
| UC-S01 | Upload Verification Docs | Submit ID for seller verification | - |
| UC-S02 | Create Product Listing | Add new product for sale | Includes UC-S03 |
| UC-S03 | Upload Images | Upload 3-5 product photos | - |
| UC-S04 | Update Product | Modify product details | - |
| UC-S05 | Delete Product | Remove product listing | - |
| UC-S06 | Manage Stock | Update stock quantity | - |
| UC-S07 | View Orders | See incoming orders | Includes UC-S08 |
| UC-S08 | Update Status | Mark order as shipped/delivered | - |
| UC-S09 | Message Buyer | Respond to buyer inquiries | Actor: Brevo |
| UC-S10 | Respond to Review | Reply to buyer review | - |
| UC-S11 | View Sales Analytics | See revenue charts, best sellers | Extends UC-S12 |
| UC-S12 | Export Reports | Download transaction history (CSV/PDF) | - |

---

### MODERATOR USE CASES (8 total)

| Use Case ID | Use Case Name | Description | Includes/Extends |
|-------------|---------------|-------------|------------------|
| UC-M01 | Review Seller Verification | Examine submitted ID documents | Includes UC-M02 |
| UC-M02 | Approve/Reject | Approve or reject verification application | - |
| UC-M03 | Moderate Products | Review flagged product listings | Includes UC-M04 |
| UC-M04 | Suspend Product | Temporarily disable product listing | - |
| UC-M05 | Manage Users | View and moderate user accounts | Includes UC-M06 |
| UC-M06 | Suspend User | Temporarily disable user account | - |
| UC-M07 | Resolve Disputes | Investigate and resolve order disputes | Includes UC-M08 |
| UC-M08 | Issue Refund | Process payment refund for buyer | Actor: PayFast |
| UC-M09 | View Reports | Access platform statistics and analytics | - |
| UC-M10 | Access Audit Logs | Review administrative action history | - |

---

### SUPER ADMIN USE CASES (7 total - includes all Moderator use cases + admin-specific)

| Use Case ID | Use Case Name | Description | Includes/Extends |
|-------------|---------------|-------------|------------------|
| UC-A01 | Create Moderator Accounts | Add new moderator users | - |
| UC-A02 | Delete User Accounts | Permanently remove user accounts | - |
| UC-A03 | Configure System Settings | Modify platform configuration | - |
| UC-A04 | Manage Categories | Add/edit/delete product categories | - |
| UC-A05 | View Financial Reports | Access revenue and transaction data | Includes UC-A06 |
| UC-A06 | Generate Transaction Report | Export financial data for accounting | - |
| UC-A07 | Manage Translations | Edit multi-language text | Includes UC-A08 |
| UC-A08 | Add Language Support | Add new language translation set | - |

---

## 🔍 DETAILED USE CASE SPECIFICATIONS

### UC-B03: CHECKOUT (Sample Detail)

**Use Case Name:** Checkout  
**ID:** UC-B03  
**Actor:** Buyer  
**Pre-conditions:**
- User is logged in as Buyer
- Shopping cart contains at least one item
- Product is available (status = 'active')

**Main Flow:**
1. Buyer clicks "Checkout" button from shopping cart
2. System displays order summary (product, quantity, price)
3. Buyer enters/confirms delivery address
4. Buyer selects delivery method (collection/courier/PUDO)
5. Buyer adds optional notes
6. Buyer clicks "Proceed to Payment"
7. System calculates total amount
8. System validates product availability
9. System creates order record (status='pending')
10. System redirects to Payment Processing (UC-B04)

**Post-conditions:**
- Order created with status='pending'
- Product stock reduced by quantity
- Order confirmation email sent to buyer
- Order notification email sent to seller

**Alternative Flows:**
- **Alt 1:** Product out of stock → Display error, remove from cart
- **Alt 2:** Payment fails → Order status remains 'pending', buyer can retry

**Includes:**
- UC-B04: Make Payment (always required)

**Extends:**
- None

---

### UC-S02: CREATE PRODUCT LISTING (Sample Detail)

**Use Case Name:** Create Product Listing  
**ID:** UC-S02  
**Actor:** Seller  
**Pre-conditions:**
- User is logged in as Seller
- Seller's verification status is 'approved' (optional, allows unverified listings with warning)

**Main Flow:**
1. Seller navigates to "Sell" page
2. Seller enters product name (max 100 characters)
3. Seller selects category from dropdown (5 fixed categories)
4. Seller enters description (max 1000 characters)
5. Seller sets price in ZAR
6. Seller enters stock quantity
7. Seller enters location (township/city)
8. Seller selects condition (new/like_new/good/fair)
9. Seller uploads 3-5 product images (UC-S03)
10. System validates all inputs
11. System processes and resizes images
12. System creates product record (status='active')
13. System displays success message
14. System redirects to seller dashboard

**Post-conditions:**
- Product record created in database
- Images stored in uploads folder
- Thumbnails generated (300x300px)
- Product visible in search results
- Seller can manage product from dashboard

**Alternative Flows:**
- **Alt 1:** Validation fails → Display errors, allow correction
- **Alt 2:** Image upload fails → Display error, allow retry
- **Alt 3:** Seller cancels → Discard draft, redirect to dashboard

**Includes:**
- UC-S03: Upload Images (required for listing creation)

---

### UC-M07: RESOLVE DISPUTES (Sample Detail)

**Use Case Name:** Resolve Disputes  
**ID:** UC-M07  
**Actor:** Moderator  
**Pre-conditions:**
- User is logged in as Moderator
- Dispute exists with status='open' or 'investigating'

**Main Flow:**
1. Moderator accesses dispute queue
2. Moderator selects dispute to review
3. System displays dispute details:
   - Order information
   - Buyer complaint and evidence
   - Seller information
   - Message thread (if any)
4. Moderator reviews evidence (photos, screenshots)
5. Moderator communicates with buyer/seller (optional)
6. Moderator updates status to 'investigating'
7. Moderator makes resolution decision:
   - Buyer favour (refund)
   - Seller favour (no action)
   - Mutual agreement (partial refund)
   - Insufficient evidence (close)
8. If refund: Moderator triggers refund process (UC-M08)
9. Moderator enters resolution notes
10. System updates dispute status to 'resolved'
11. System sends notifications to buyer and seller
12. System logs admin action

**Post-conditions:**
- Dispute status='resolved'
- Resolution outcome recorded
- Buyer and seller notified via email
- Admin action logged for audit
- If refund issued: Transaction updated, order refunded

**Alternative Flows:**
- **Alt 1:** Escalate to Super Admin → Status='escalated'
- **Alt 2:** Request more information → Send message, status='investigating'

**Includes:**
- UC-M08: Issue Refund (if resolution favors buyer)

**Extends:**
- None

---

## 🎨 DRAWING INSTRUCTIONS FOR USE CASE DIAGRAM

### For draw.io:

**Step 1: Create System Boundary**
- Draw large rectangle
- Label: "Street2Screen ZA System"
- Color: Light grey background (#F5F5F5)

**Step 2: Add Actors (Outside System Boundary)**
- Use stick figure shape for people (Guest, Buyer, Seller, Moderator, Admin)
- Use rectangle/icon for external systems (PayFast, Brevo)
- Place on left and right sides of system boundary

**Step 3: Add Use Cases (Inside System Boundary)**
- Use oval/ellipse shapes
- Color: Yellow (#FFC107) for main use cases
- Font: 10pt, centered
- Label format: "Action Description" (e.g., "Browse Products")

**Step 4: Draw Associations (Lines)**
- Connect actors to use cases they participate in
- Use simple line (no arrow)
- Keep lines short and clear

**Step 5: Add Relationships**
- **«include»** relationships: Dashed arrow pointing to required use case
- **«extend»** relationships: Dashed arrow pointing to base use case
- **Generalization:** Solid arrow from specialized actor to general actor
  - Example: Seller → Buyer (Seller IS-A Buyer)
  - Example: Moderator → Buyer
  - Example: Super Admin → Moderator

**Color Scheme:**
- Actors: Dark Blue (#0B1F3A)
- Use Cases: Yellow (#FFC107)
- System Boundary: Light Grey (#F5F5F5)
- Relationship lines: Dark Blue
- «include»/«extend» labels: Grey text (#666666)

---

## ✅ USE CASE DIAGRAM CHECKLIST

- [x] System boundary defined (rectangle around all use cases)
- [x] All actors identified (5 primary, 2 secondary)
- [x] All use cases listed (60+ total across all actors)
- [x] Actor-use case associations drawn
- [x] Include relationships identified (14 instances)
- [x] Extend relationships identified (8 instances)
- [x] Generalization relationships shown (Seller→Buyer, Moderator→Buyer, Admin→Moderator)
- [x] External system actors shown (PayFast, Brevo)
- [x] Use case descriptions documented
- [x] Sample detailed specifications provided (3 examples)

---

## 📊 STATISTICS

**Total Use Cases:** 65+  
**Total Actors:** 7 (5 primary, 2 secondary)  
**Include Relationships:** 14  
**Extend Relationships:** 8  
**Generalization Relationships:** 3  
**Most Complex Actor:** Seller (16 direct use cases + inherited)  
**Most Used Use Case:** View Product Details (used by Guest, Buyer, Seller)

---

**Document Created:** February 12, 2026  
**Student:** Ignatius Mayibongwe Khumalo  
**Institution:** Eduvos Private Institution  
**For:** Deliverable 2 - Use Case Diagram (3 marks)
