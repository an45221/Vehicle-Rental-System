# Level 0 DFD - Vehicle Booking System

## Context Diagram (Level 0 Data Flow Diagram)

A Level 0 DFD represents the entire system as a single process and shows how it interacts with external entities (actors/users). It is also called a **Context Diagram**.

---

## 📊 Level 0 DFD - Visual Representation

```
                          ┌──────────────────────┐
                          │                      │
                          │      CUSTOMERS       │
                          │    (End Users)       │
                          │                      │
                          └──────────┬───────────┘
                                     │
                    ┌────────────────┼────────────────┐
                    │                │                │
         ┌──────────▼──┐   ┌────────▼─────────┐   ┌──┴───────────┐
         │ Login/Signup │   │ Booking Request  │   │ Profile/Review│
         │    Data      │   │ & Search Query   │   │     Data     │
         │              │   │                  │   │              │
         │ Search Data  │   │ Booking/Payment  │   │ Feedback     │
         │ Results      │   │      Data        │   │ Cancellation │
         │              │   │                  │   │    Reason    │
         └──────────────┘   └──────────────────┘   └──────────────┘
                    │                │                │
                    │                │                │
         ┌──────────┴─────────────────┴────────────────┴──────────┐
         │                                                         │
         │                                                         │
         │      ┌────────────────────────────────────┐            │
         │      │                                    │            │
         │      │    VEHICLE BOOKING SYSTEM          │            │
         │      │         (Context)                  │            │
         │      │                                    │            │
         │      │  • User Authentication             │            │
         │      │  • Vehicle Search & Booking        │            │
         │      │  • Payment Processing              │            │
         │      │  • Review Management               │            │
         │      │  • Booking Cancellation            │            │
         │      │  • Admin Management                │            │
         │      │                                    │            │
         │      └────────────────────────────────────┘            │
         │                                                         │
         │                                                         │
         └─────────────────────┬─────────────────────────────────┘
                               │
          ┌────────────────────┼────────────────────┐
          │                    │                    │
    ┌─────▼────────┐    ┌──────▼──────────┐  ┌────▼───────────┐
    │ Booking      │    │ Confirmation &  │  │ User Status    │
    │ Confirmation │    │ Notification    │  │ & Vehicle      │
    │ Receipt      │    │ Emails          │  │ Availability   │
    │              │    │                 │  │ Status         │
    │ Booking      │    │ OTP & Password  │  │                │
    │ Status       │    │ Reset Emails    │  │ Booking        │
    │ Updates      │    │                 │  │ Details        │
    └──────────────┘    └─────────────────┘  └────────────────┘
         │                    │                    │
         │                    │                    │
         ▼                    ▼                    ▼
    ┌──────────────┐    ┌─────────────────┐  ┌─────────────┐
    │  CUSTOMERS   │    │  EMAIL SERVICE  │  │   ADMIN     │
    │  (Response)  │    │  (Khalti/Esewa) │  │  PORTAL     │
    └──────────────┘    └─────────────────┘  └─────────────┘


                    ┌──────────────────┐
                    │                  │
                    │  PAYMENT GATEWAY │
                    │   (Khalti/Esewa) │
                    │                  │
                    └────────┬─────────┘
                             │
                    ┌────────┼────────┐
                    │                 │
         ┌──────────▼──┐    ┌────────▼────────┐
         │  Payment    │    │ Payment Success/│
         │   Request   │    │ Failure Status  │
         │             │    │                 │
         │ (Amount,    │    │ (Transaction ID,│
         │  Booking ID)│    │  Status)        │
         └─────────────┘    └─────────────────┘
                    │                 │
                    │                 │
                    └────────┬────────┘
                             │
                             ▼
         ┌──────────────────────────────────┐
         │   VEHICLE BOOKING SYSTEM         │
         │   (Payment Processing Module)    │
         └──────────────────────────────────┘
```

---

## 📋 Level 0 DFD - Components

### **1. Central Process (The System)**
```
Process Name: Vehicle Booking System
Process ID: 0
Description: A comprehensive web-based system for renting vehicles online
             with user authentication, vehicle search, booking, payment,
             reviews, and admin management capabilities
```

---

## 🔄 External Entities (Terminators)

### **Entity 1: CUSTOMERS (End Users)**
| Attribute | Description |
|-----------|-------------|
| **Role** | Primary users of the system |
| **Interactions** | Register, Login, Search vehicles, Book, Pay, Review, Cancel |
| **Data Sent to System** | User credentials, search queries, booking details, payment info, reviews, cancellation feedback |
| **Data Received** | Search results, booking confirmations, vehicle availability, receipt, booking status |

### **Entity 2: EMAIL SERVICE**
| Attribute | Description |
|-----------|-------------|
| **Role** | External email service for notifications |
| **Provider** | Gmail/PHPMailer |
| **Interactions** | Send notifications, OTP, password reset emails |
| **Data Sent to System** | Email delivery status/receipts |
| **Data Received** | Email content, recipient address, subject |

### **Entity 3: PAYMENT GATEWAY**
| Attribute | Description |
|-----------|-------------|
| **Role** | External payment processor |
| **Provider** | Khalti / Esewa |
| **Interactions** | Payment processing, verification |
| **Data Sent to System** | Payment status, transaction ID, amount received |
| **Data Received** | Booking ID, amount, customer info |

### **Entity 4: ADMIN PORTAL**
| Attribute | Description |
|-----------|-------------|
| **Role** | System administrator interface |
| **Interactions** | Manage vehicles, bookings, users, view analytics |
| **Data Sent to System** | Vehicle data, booking approvals, user status updates |
| **Data Received** | System statistics, bookings data, user information |

---

## 🔁 Data Flows (Level 0)

### **Data Flow 1: Customer Authentication & Profile**
```
Source: CUSTOMERS
Destination: VEHICLE BOOKING SYSTEM
Data Items:
├── Login credentials (username, password)
├── Registration data (fullname, username, email, password)
├── Profile updates (name, email, profile image)
└── Password reset requests

Response Data:
├── Authentication confirmation
├── User profile data
├── OTP for verification
└── Password reset link
```

### **Data Flow 2: Vehicle Search & Availability**
```
Source: CUSTOMERS
Destination: VEHICLE BOOKING SYSTEM
Data Items:
├── Search criteria (pickup location, drop location, dates)
├── Vehicle category/filters
└── Price range preferences

Response Data:
├── Available vehicles list
├── Vehicle details (name, price, specs, ratings)
├── Vehicle images
└── Availability status
```

### **Data Flow 3: Booking & Reservation**
```
Source: CUSTOMERS
Destination: VEHICLE BOOKING SYSTEM
Data Items:
├── Booking request (vehicle_id, pickup_date, return_date)
├── Pickup location
├── Drop-off location
└── Customer details confirmation

Response Data:
├── Booking confirmation
├── Booking ID
├── Total price
└── Payment gateway redirect
```

### **Data Flow 4: Payment Processing**
```
Source: CUSTOMERS
Destination: PAYMENT GATEWAY
Data Items:
├── Amount to pay
├── Booking ID
└── Payment method choice

Gateway Response:
├── Payment status (success/failed)
├── Transaction ID
└── Amount processed
```

### **Data Flow 5: Payment Status to System**
```
Source: PAYMENT GATEWAY
Destination: VEHICLE BOOKING SYSTEM
Data Items:
├── Payment status (Paid/Unpaid)
├── Transaction ID
├── Amount
└── Booking ID reference

System Response:
├── Update booking status
├── Release/Block vehicle
└── Trigger confirmation email
```

### **Data Flow 6: Email Notifications**
```
Source: VEHICLE BOOKING SYSTEM
Destination: EMAIL SERVICE
Data Items:
├── Booking confirmation email
├── OTP for password reset
├── Payment success/failure notification
├── Booking cancellation confirmation
├── Review reminder email
└── Password reset link

Email Service Response:
├── Email sent status
├── Delivery confirmation
└── Bounce/Failure notifications
```

### **Data Flow 7: Booking Confirmation Back to Customer**
```
Source: VEHICLE BOOKING SYSTEM
Destination: CUSTOMERS
Data Items:
├── Booking receipt
├── Booking ID
├── Vehicle details
├── Pickup/Drop-off details
├── Total price paid
├── Booking status
└── Cancellation policy
```

### **Data Flow 8: Reviews & Feedback**
```
Source: CUSTOMERS
Destination: VEHICLE BOOKING SYSTEM
Data Items:
├── Star rating (1-5)
├── Review comment
├── Booking ID reference
└── Vehicle feedback

Response Data:
├── Review confirmation
├── Updated vehicle ratings
└── Average rating
```

### **Data Flow 9: Cancellation Request**
```
Source: CUSTOMERS
Destination: VEHICLE BOOKING SYSTEM
Data Items:
├── Booking ID
├── Cancellation reason
├── Additional comments
└── Refund details

Response Data:
├── Cancellation confirmation
├── Refund status
├── Refund amount
└── Cancellation receipt
```

### **Data Flow 10: Admin Management Data**
```
Source: ADMIN PORTAL
Destination: VEHICLE BOOKING SYSTEM
Data Items:
├── Vehicle data (add/edit/delete)
├── Vehicle image uploads
├── Booking approval/cancellation
├── User status updates (block/unblock)
├── User deletion requests
└── CSV export requests

Response Data:
├── Confirmation of changes
├── System statistics
├── Booking list
├── User list
├── Vehicle inventory
└── Revenue reports
```

### **Data Flow 11: Dashboard Analytics**
```
Source: VEHICLE BOOKING SYSTEM
Destination: ADMIN PORTAL
Data Items:
├── Total vehicle count
├── Total user count
├── Total booking count
├── Active bookings
├── Revenue data (monthly, total)
├── Pending payments
├── Vehicle status breakdown
├── Booking status breakdown
└── Recent bookings details
```

---

## 📊 Data Store References (Level 0)

While Level 0 DFD doesn't show individual data stores, the system maintains:

```
Data Stores (Conceptual):
├── D1: User Information
│   ├── Credentials
│   ├── Profile Data
│   └── Account Status
│
├── D2: Vehicle Inventory
│   ├── Vehicle Details
│   ├── Availability Status
│   └── Ratings/Reviews
│
├── D3: Booking Records
│   ├── Booking Details
│   ├── Payment Status
│   └── Booking Status
│
├── D4: Reviews & Feedback
│   ├── User Reviews
│   ├── Vehicle Ratings
│   └── Cancellation Feedback
│
└── D5: Admin Records
    ├── Admin Credentials
    └── Admin Activities Log
```

---

## 🔐 Data Security Considerations (Level 0)

```
Security Measures:
├── User Authentication
│   ├── Hashed password storage
│   ├── OTP verification
│   └── Session management
│
├── Payment Security
│   ├── Third-party payment gateway
│   ├── PCI-DSS compliance
│   └── Transaction logging
│
├── Data Protection
│   ├── SQL injection prevention
│   ├── XSS protection
│   ├── CSRF tokens
│   └── Input validation
│
└── Access Control
    ├── Admin authentication
    ├── Role-based access
    └── Activity logging
```

---

## 📈 System Scope (Level 0)

### **What's INSIDE the System Boundary**
✅ User authentication and management
✅ Vehicle search and browsing
✅ Booking creation and management
✅ Payment processing coordination
✅ Review and rating management
✅ Cancellation handling
✅ Admin dashboard and controls
✅ Email notification coordination
✅ Booking history and reports

### **What's OUTSIDE the System Boundary** (External Systems)
❌ Actual email sending (handled by Email Service)
❌ Payment processing (handled by Khalti/Esewa)
❌ User devices/browsers (handled by customers)
❌ Admin devices/interfaces (handled by admins)

---

## 🎯 Key Processes at Level 0

The entire system handles:

```
1. USER MANAGEMENT
   • Registration & Authentication
   • Profile Management
   • Password Reset
   • Account Status Control

2. VEHICLE MANAGEMENT
   • Vehicle Inventory
   • Availability Tracking
   • Rating & Review System
   • Image Management

3. BOOKING MANAGEMENT
   • Search & Availability Check
   • Booking Creation
   • Booking Modification
   • Booking Cancellation
   • Booking History

4. PAYMENT MANAGEMENT
   • Payment Gateway Integration
   • Transaction Recording
   • Payment Status Tracking
   • Refund Processing

5. ADMIN OPERATIONS
   • Vehicle CRUD Operations
   • Booking Approval/Cancellation
   • User Management
   • Analytics & Reporting

6. NOTIFICATION SYSTEM
   • Email Confirmation
   • OTP Generation
   • Payment Notifications
   • Cancellation Confirmations
```

---

## 📊 Interaction Summary Table

| External Entity | Data Sent to System | Data Received from System | Frequency |
|---|---|---|---|
| **CUSTOMERS** | Credentials, search queries, booking data, payments, reviews, cancellations | Search results, confirmations, receipts, booking status, vehicle info | Real-time |
| **EMAIL SERVICE** | Email content, recipient | Delivery confirmation, bounce status | Event-based |
| **PAYMENT GATEWAY** | Payment credentials, amount | Payment status, transaction ID | Per transaction |
| **ADMIN PORTAL** | Vehicle/booking/user updates | System stats, analytics, lists | Real-time |

---

## 🔄 Level 0 DFD Characteristics

✅ **Single Process**: Entire system shown as one bubble (0)
✅ **External Entities**: 4 main external actors identified
✅ **Data Flows**: 11 major data flows between system and entities
✅ **High-Level View**: Focuses on system boundaries
✅ **No Data Stores**: Level 0 doesn't show internal databases
✅ **Context Focus**: Shows overall system purpose and interactions

---

## 📌 DFD Level Hierarchy

```
Level 0 (Context Diagram)
│
├─── Shows: Entire system as one process
├─── Focus: External interactions & boundaries
├─── Detail: Minimal internal details
└─── Purpose: System overview & scope

                    ↓
                    
Level 1 (Decomposition)
│
├─── Shows: System broken into 5-6 major processes
├─── Focus: High-level subsystems
├─── Detail: Main functional areas
└─── Purpose: Functional decomposition

                    ↓
                    
Level 2 & 3 (Detailed)
│
├─── Shows: Individual processes & sub-processes
├─── Focus: Detailed operations & data flows
├─── Detail: Specific algorithms & logic
└─── Purpose: Detailed design & implementation
```

---

## 📋 Level 0 DFD Summary

| Aspect | Details |
|--------|---------|
| **System Name** | Vehicle Booking System |
| **Process ID** | 0 (Context) |
| **External Entities** | 4 (Customers, Email Service, Payment Gateway, Admin) |
| **Data Flows** | 11 major flows |
| **System Inputs** | User requests, search queries, bookings, payments, admin commands |
| **System Outputs** | Search results, confirmations, receipts, notifications, analytics |
| **Primary Functions** | Authentication, Search, Booking, Payment, Reviews, Admin Management |
| **External Systems** | Email (Gmail), Payment (Khalti/Esewa) |

---

*Level 0 DFD - Context Diagram for Vehicle Booking System*
*Provides high-level overview of system interactions with external entities*
