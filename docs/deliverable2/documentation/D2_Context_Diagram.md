# CONTEXT DIAGRAM (DFD LEVEL 0)
## Street2Screen ZA - System Boundary

**Project:** Street2Screen ZA  
**Student:** Ignatius Mayibongwe Khumalo  
**Institution:** Eduvos Private Institution  
**Course:** ITECA3-12 Initial Project  
**Date:** February 2026

---

## 📋 WHAT IS A CONTEXT DIAGRAM?

A **Context Diagram** (also called **DFD Level 0**) shows the **entire system as a single process** and identifies all **external entities** that interact with it. It defines the **system boundary** - what's inside vs. outside the system.

---

## 🎯 STREET2SCREEN ZA CONTEXT DIAGRAM

```
┌─────────────────────────────────────────────────────────────────────┐
│                                                                     │
│                    EXTERNAL ENTITIES (OUTSIDE SYSTEM)               │
│                                                                     │
│  ┌──────────────┐        ┌──────────────┐        ┌──────────────┐ │
│  │   BUYERS     │        │   SELLERS    │        │    ADMINS    │ │
│  │  (Users)     │        │  (Users)     │        │ (Moderators) │ │
│  └──────┬───────┘        └──────┬───────┘        └──────┬───────┘ │
│         │                       │                       │         │
│         │                       │                       │         │
│    ┌────▼───────────────────────▼───────────────────────▼────┐   │
│    │                                                          │   │
│    │             STREET2SCREEN ZA PLATFORM                    │   │
│    │              (C2C E-Commerce System)                     │   │
│    │                                                          │   │
│    │  Core Functions:                                         │   │
│    │  • User Registration & Authentication                    │   │
│    │  • Product Listing Management                            │   │
│    │  • Search & Discovery                                    │   │
│    │  • Order Processing                                      │   │
│    │  • Payment Processing                                    │   │
│    │  • Messaging System                                      │   │
│    │  • Reviews & Ratings                                     │   │
│    │  • Seller Verification                                   │   │
│    │  • Dispute Resolution                                    │   │
│    │  • Multi-Language Support (11 SA Languages)              │   │
│    │                                                          │   │
│    └────┬───────────────────────┬───────────────────────┬────┘   │
│         │                       │                       │         │
│         │                       │                       │         │
│  ┌──────▼───────┐        ┌──────▼───────┐        ┌──────▼───────┐ │
│  │   PAYFAST    │        │    BREVO     │        │   DATABASE   │ │
│  │   PAYMENT    │        │     SMTP     │        │    MySQL     │ │
│  │   GATEWAY    │        │   SERVICE    │        │              │ │
│  └──────────────┘        └──────────────┘        └──────────────┘ │
│                                                                     │
└─────────────────────────────────────────────────────────────────────┘
```

---

## 📊 EXTERNAL ENTITIES & DATA FLOWS

### 1. BUYERS (External Entity)

**Data Flows TO System:**
- Registration details (name, email, password, address)
- Login credentials
- Product search queries
- Purchase orders
- Payment information
- Delivery addresses
- Reviews and ratings
- Messages to sellers
- Dispute reports

**Data Flows FROM System:**
- Registration confirmation emails
- Search results (product listings)
- Product details (images, descriptions, prices)
- Order confirmations
- Payment receipts
- Delivery tracking updates
- Messages from sellers
- Order status updates

---

### 2. SELLERS (External Entity)

**Data Flows TO System:**
- Registration details
- Verification documents (ID, business registration)
- Product listings (name, description, price, images)
- Stock quantity updates
- Order fulfillment confirmations
- Shipping/tracking details
- Messages to buyers
- Review responses

**Data Flows FROM System:**
- Verification approval/rejection
- New order notifications
- Payment confirmations
- Buyer messages
- Sales analytics/reports
- Platform fee deductions
- Review notifications

---

### 3. ADMINS/MODERATORS (External Entity)

**Data Flows TO System:**
- Login credentials (admin access)
- Seller verification approvals/rejections
- Product moderation actions (suspend/delete)
- User account actions (suspend/unsuspend)
- Dispute resolutions
- System configuration changes

**Data Flows FROM System:**
- Pending verification queue
- Flagged content alerts
- Dispute reports
- Platform statistics
- Audit logs
- Financial reports
- User activity reports

---

### 4. PAYFAST PAYMENT GATEWAY (External System)

**Data Flows TO PayFast:**
- Payment initiation requests
- Order details (amount, merchant ID, item details)
- Return URLs (success/cancel)
- Buyer information

**Data Flows FROM PayFast:**
- IPN (Instant Payment Notification) callbacks
- Payment success/failure status
- Transaction IDs
- Payment confirmations

---

### 5. BREVO SMTP SERVICE (External System)

**Data Flows TO Brevo:**
- Email sending requests
- Recipient addresses
- Email content (HTML templates)
- Sender information

**Data Flows FROM Brevo:**
- Email delivery confirmations
- Bounce notifications
- Delivery status reports

---

### 6. MySQL DATABASE (Data Store)

**Data Flows TO Database:**
- User account data
- Product listings
- Order records
- Transaction records
- Messages
- Reviews
- Admin logs
- All CRUD operations

**Data Flows FROM Database:**
- User authentication results
- Product search results
- Order histories
- Transaction reports
- Analytics data
- All query results

---

## 🔄 SYSTEM BOUNDARY DEFINITION

### INSIDE THE SYSTEM (Our Responsibility):
✅ User authentication logic  
✅ Product listing management  
✅ Search algorithms  
✅ Order processing workflows  
✅ Payment integration logic  
✅ Messaging system  
✅ Review system  
✅ Admin panel  
✅ Multi-language translation  
✅ Business logic & validation  

### OUTSIDE THE SYSTEM (External Dependencies):
❌ PayFast payment processing servers  
❌ Brevo email delivery infrastructure  
❌ MySQL database engine  
❌ InfinityFree hosting servers  
❌ User devices (phones, tablets, computers)  
❌ Internet service providers  

---

## 📐 VISUAL REPRESENTATION DETAILS

### For draw.io / Visual Diagram:

**Shape Legend:**
- **Rounded Rectangle** (Blue #0B1F3A) = External Entities (Buyers, Sellers, Admins)
- **Oval/Circle** (Yellow #FFC107) = Central System (Street2Screen ZA)
- **Rectangle** (Grey #F2F2F2) = External Systems (PayFast, Brevo, Database)
- **Arrows** (Dark Blue) = Data Flows (labeled with data description)

**Layout:**
```
         BUYERS
            ↓
    SELLERS → [STREET2SCREEN ZA] ← ADMINS
            ↓
     PAYFAST ← → BREVO ← → DATABASE
```

**Colors:**
- Primary System: Yellow (#FFC107)
- External Entities (People): Dark Blue (#0B1F3A)
- External Systems (Tech): Grey (#F2F2F2)
- Data Flow Arrows: Dark Blue with labels

---

## ✅ CONTEXT DIAGRAM CHECKLIST

- [x] All external entities identified (6 total)
- [x] System boundary clearly defined
- [x] Data flows labeled with content description
- [x] Bidirectional flows shown where applicable
- [x] System purpose clearly stated
- [x] Inside vs. outside system distinguished

---

## 🎯 KEY INSIGHTS FROM CONTEXT DIAGRAM

1. **Central Hub:** Street2Screen ZA is the central processing system coordinating all interactions

2. **Three User Types:** Buyers, Sellers, and Admins have distinct data flows reflecting RBAC design

3. **Two External Services:** PayFast handles payments; Brevo handles emails - both critical dependencies

4. **Data Persistence:** MySQL database stores all system data

5. **Bidirectional Communication:** Most entities have two-way data flows (request/response pattern)

6. **Security Boundary:** Authentication and authorization happen at the system boundary (all external entities must authenticate)

---

## 📊 NEXT LEVEL: DFD LEVEL 1

The Context Diagram shows the **whole system as one process**. The next step (DFD Level 1) will **break down** the Street2Screen ZA system into **major sub-processes**:

1. User Management (registration, authentication)
2. Product Management (listings, search)
3. Order Processing (cart, checkout)
4. Payment Processing (PayFast integration)
5. Communication (messaging, notifications)
6. Review System
7. Administration (verification, moderation)

This will be created next in the DFD documentation.

---

**Document Created:** February 12, 2026  
**Student:** Ignatius Mayibongwe Khumalo  
**Institution:** Eduvos Private Institution  
**For:** Deliverable 2 - Design Diagrams (3 marks)
