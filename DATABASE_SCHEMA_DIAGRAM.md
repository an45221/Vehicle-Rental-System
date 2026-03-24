# Database Schema Diagram - Vehicle Booking System

## Visual Database Schema

Complete Entity-Relationship diagram with all tables, columns, and relationships.

---

## 📊 Full Schema Diagram

```
┌────────────────────────────────────────┐
│              USERS TABLE               │
├────────────────────────────────────────┤
│ 🔑 id (INT, PK, AUTO_INCREMENT)       │
│ 📝 fullname (VARCHAR 255, NOT NULL)   │
│ 👤 username (VARCHAR 100, UNIQUE)     │
│ 📧 email (VARCHAR 255, UNIQUE)        │
│ 🔐 password (VARCHAR 255, NOT NULL)   │
│ 🟢 status (VARCHAR 20, DEFAULT active)│
│ 🖼️  profile_image (VARCHAR 255, NULL) │
│ 📱 otp (VARCHAR 6, NULL)              │
│ ⏰ otp_expiry (TIMESTAMP, NULL)       │
│ 📅 created_at (TIMESTAMP)             │
│ 📅 updated_at (TIMESTAMP)             │
├────────────────────────────────────────┤
│ Indexes:                               │
│ • idx_username                         │
│ • idx_email                            │
│ • idx_status                           │
│ • idx_created_at                       │
└────────────────────────────────────────┘
        │
        │ 1:N (One user, many bookings)
        │
        ├──────────────────┐
        │                  │
        │                  ├──────────────────────────────────────────┐
        │                  │                                          │
        │                  │                                          │
┌───────▼──────────────────────────────────────────┐  ┌─────────────▼──────────────────────┐
│          BOOKINGS TABLE                          │  │      REVIEWS TABLE                 │
├────────────────────────────────────────────────┤  ├────────────────────────────────────┤
│ 🔑 id (INT, PK, AUTO_INCREMENT)                │  │ 🔑 id (INT, PK, AUTO_INCREMENT)   │
│ 🔗 user_id (INT, FK → users.id)                │  │ 🔗 booking_id (INT, FK, UNIQUE)   │
│ 🔗 vehicle_id (INT, FK → vehicles.id)          │  │ 🔗 user_id (INT, FK)              │
│ 📝 vehicle_name (VARCHAR 255)                  │  │ 🔗 vehicle_id (INT, FK)           │
│ 📍 pickup (VARCHAR 255)                        │  │ ⭐ rating (INT, CHECK 1-5)        │
│ 📍 drop_location (VARCHAR 255)                 │  │ 💬 comment (TEXT, NULL)           │
│ 📅 pickup_date (DATE)                          │  │ 📅 created_at (TIMESTAMP)         │
│ 📅 return_date (DATE)                          │  ├────────────────────────────────────┤
│ 💰 price (INT)                                 │  │ Indexes:                           │
│ 💳 payment_status (VARCHAR 20)                 │  │ • idx_vehicle_id                   │
│ ✅ booking_status (VARCHAR 20)                 │  │ • idx_user_id                      │
│ ❌ cancel_reason (VARCHAR 255, NULL)           │  │ • idx_rating                       │
│ ⏰ cancelled_at (TIMESTAMP, NULL)              │  │ • idx_created_at                   │
│ 📅 created_at (TIMESTAMP)                      │  ├────────────────────────────────────┤
│ 📅 updated_at (TIMESTAMP)                      │  │ Unique: booking_id (1 review/book)│
├────────────────────────────────────────────────┤  └────────────────────────────────────┘
│ Foreign Keys:                                  │
│ • user_id → users(id) ON DELETE CASCADE        │
│ • vehicle_id → vehicles(id) ON DELETE CASCADE  │
│                                                │
│ Indexes:                                       │
│ • idx_user_id                                  │
│ • idx_vehicle_id                               │
│ • idx_payment_status                           │
│ • idx_booking_status                           │
│ • idx_pickup_date                              │
│ • idx_return_date                              │
│ • idx_created_at                               │
└────────────────────────────────────────────────┘
        │
        │ 1:1 (One booking, max one cancellation feedback)
        │
        └──────────────────────────┐
                                   │
        ┌──────────────────────────▼──────────────────────────┐
        │    CANCELLATION_FEEDBACK TABLE                     │
        ├──────────────────────────────────────────────────┤
        │ 🔑 id (INT, PK, AUTO_INCREMENT)                  │
        │ 🔗 booking_id (INT, FK, UNIQUE)                  │
        │ 🔗 user_id (INT, FK)                             │
        │ 📝 reason (VARCHAR 255, NULL)                    │
        │ 💬 additional_comment (TEXT, NULL)               │
        │ 📅 created_at (TIMESTAMP)                        │
        │ 📅 updated_at (TIMESTAMP)                        │
        ├──────────────────────────────────────────────────┤
        │ Foreign Keys:                                    │
        │ • booking_id → bookings(id) ON DELETE CASCADE    │
        │ • user_id → users(id) ON DELETE CASCADE          │
        │                                                  │
        │ Indexes:                                         │
        │ • idx_user_id                                    │
        │ • idx_reason                                     │
        │ • idx_created_at                                 │
        │                                                  │
        │ Unique: booking_id (1 feedback per booking)      │
        └──────────────────────────────────────────────────┘


┌────────────────────────────────────────────────────────┐
│            VEHICLES TABLE                              │
├────────────────────────────────────────────────────────┤
│ 🔑 id (INT, PK, AUTO_INCREMENT)                       │
│ 📝 name (VARCHAR 255, NOT NULL)                       │
│ 🏷️  category (VARCHAR 100, NOT NULL)                  │
│ 💰 price (INT, NOT NULL)                              │
│ ⛽ fuel_type (VARCHAR 50, NOT NULL)                   │
│ 🎛️  transmission (VARCHAR 50, NOT NULL)               │
│ 👥 seating_capacity (INT, NOT NULL)                   │
│ 🟢 status (VARCHAR 20, DEFAULT available)             │
│ 🖼️  image (VARCHAR 255, NULL)                         │
│ ⭐ avg_rating (DECIMAL 3,2, DEFAULT 0.00)            │
│ 📊 total_reviews (INT, DEFAULT 0)                     │
│ 📅 created_at (TIMESTAMP)                             │
│ 📅 updated_at (TIMESTAMP)                             │
├────────────────────────────────────────────────────────┤
│ Indexes:                                               │
│ • idx_status                                           │
│ • idx_category                                         │
│ • idx_price                                            │
│ • idx_avg_rating                                       │
│ • idx_created_at                                       │
│                                                        │
│ Denormalized Fields:                                   │
│ • avg_rating (from reviews)                            │
│ • total_reviews (count of reviews)                     │
└────────────────────────────────────────────────────────┘
        │
        │ 1:N (One vehicle, many bookings)
        │
        └──────────────┬─────────────────────────────────┐
                       │                                 │
                       ├────────────────────────────────┤
                       │                                │
                       └──→ BOOKINGS TABLE (see above)  │
                                                        │
                       1:N (One vehicle, many reviews)  │
                       │                                │
                       └──→ REVIEWS TABLE (see above)


┌──────────────────────────────┐
│      ADMINS TABLE            │
├──────────────────────────────┤
│ 🔑 id (INT, PK, AUTO_INCREMENT)  │
│ 👤 username (VARCHAR 100, UNIQUE)│
│ 🔐 password (VARCHAR 255, NOT NULL) │
│ 📅 created_at (TIMESTAMP)    │
│ 📅 updated_at (TIMESTAMP)    │
├──────────────────────────────┤
│ Indexes:                     │
│ • idx_username               │
│ • idx_created_at             │
└──────────────────────────────┘
```

---

## 🔗 Relationship Summary

### **Visual Relationship Map**

```
                    USERS (1) ──────────┐
                      │                 │
                      │ 1:N             │ 1:N
                      │                 │
        ┌─────────────┴────────┐        │
        │                      │        │
        │                      │        │
    BOOKINGS (1)          REVIEWS (N)   │
        │ │ 1:1                │        │
        │ │                    │        │
        │ └────┬───────────────┘        │
        │      │                        │
        │      └──→ VEHICLES (N) ◄──────┘
        │           │
        │ 1:1       │ 1:N
        │           │
        └──→ CANCELLATION_FEEDBACK (N)

    ADMINS (Standalone)
```

---

## 📋 Table-by-Table Details

### **1️⃣ USERS Table**

```
Column Name       │ Data Type      │ Constraint      │ Purpose
─────────────────┼────────────────┼─────────────────┼─────────────────────────
id                │ INT            │ PK, AUTO_INC    │ User unique identifier
fullname          │ VARCHAR(255)   │ NOT NULL        │ User's full name
username          │ VARCHAR(100)   │ UNIQUE, NOT NULL│ Login username
email             │ VARCHAR(255)   │ UNIQUE, NOT NULL│ User email address
password          │ VARCHAR(255)   │ NOT NULL        │ Hashed password
status            │ VARCHAR(20)    │ CHECK, DEFAULT  │ active/blocked
profile_image     │ VARCHAR(255)   │ NULLABLE        │ Profile picture file
otp               │ VARCHAR(6)     │ NULLABLE        │ One-time password
otp_expiry        │ TIMESTAMP      │ NULLABLE        │ OTP expiration
created_at        │ TIMESTAMP      │ DEFAULT NOW()   │ Account creation
updated_at        │ TIMESTAMP      │ DEFAULT NOW()   │ Last updated
```

**Relationships:**
- 1:N → BOOKINGS (user can have many bookings)
- 1:N → REVIEWS (user can write many reviews)
- 1:N → CANCELLATION_FEEDBACK (user can provide many feedbacks)

---

### **2️⃣ VEHICLES Table**

```
Column Name       │ Data Type      │ Constraint      │ Purpose
─────────────────┼────────────────┼─────────────────┼──────────────────────
id                │ INT            │ PK, AUTO_INC    │ Vehicle unique ID
name              │ VARCHAR(255)   │ NOT NULL        │ Vehicle name
category          │ VARCHAR(100)   │ NOT NULL        │ SUV/Sedan/etc.
price             │ INT            │ NOT NULL        │ Daily rental price
fuel_type         │ VARCHAR(50)    │ NOT NULL        │ Petrol/Diesel/EV
transmission      │ VARCHAR(50)    │ NOT NULL        │ Manual/Automatic
seating_capacity  │ INT            │ NOT NULL        │ Number of seats
status            │ VARCHAR(20)    │ CHECK, DEFAULT  │ available/unavailable
image             │ VARCHAR(255)   │ NULLABLE        │ Vehicle image file
avg_rating        │ DECIMAL(3,2)   │ DEFAULT 0.00    │ Average rating (denorm)
total_reviews     │ INT            │ DEFAULT 0       │ Review count (denorm)
created_at        │ TIMESTAMP      │ DEFAULT NOW()   │ Creation time
updated_at        │ TIMESTAMP      │ DEFAULT NOW()   │ Last updated
```

**Relationships:**
- 1:N → BOOKINGS (vehicle can be booked many times)
- 1:N → REVIEWS (vehicle can have many reviews)

---

### **3️⃣ BOOKINGS Table**

```
Column Name       │ Data Type      │ Constraint      │ Purpose
─────────────────┼────────────────┼─────────────────┼──────────────────────
id                │ INT            │ PK, AUTO_INC    │ Booking unique ID
user_id           │ INT            │ FK → users.id   │ Which user booked
vehicle_id        │ INT            │ FK → vehicles.id│ Which vehicle
vehicle_name      │ VARCHAR(255)   │ NOT NULL        │ Vehicle name snapshot
pickup            │ VARCHAR(255)   │ NOT NULL        │ Pickup location
drop_location     │ VARCHAR(255)   │ NOT NULL        │ Drop-off location
pickup_date       │ DATE           │ NOT NULL        │ Start date
return_date       │ DATE           │ NOT NULL        │ End date
price             │ INT            │ NOT NULL        │ Total price
payment_status    │ VARCHAR(20)    │ CHECK, DEFAULT  │ Paid/Unpaid/Expired
booking_status    │ VARCHAR(20)    │ CHECK, DEFAULT  │ Active/Booked/etc.
cancel_reason     │ VARCHAR(255)   │ NULLABLE        │ Why cancelled?
cancelled_at      │ TIMESTAMP      │ NULLABLE        │ Cancellation time
created_at        │ TIMESTAMP      │ DEFAULT NOW()   │ Booking creation
updated_at        │ TIMESTAMP      │ DEFAULT NOW()   │ Last updated
```

**Relationships:**
- N:1 → USERS (via user_id)
- N:1 → VEHICLES (via vehicle_id)
- 1:1 → REVIEWS (zero or one review)
- 1:1 → CANCELLATION_FEEDBACK (zero or one feedback)

**Status Values:**
- **Booking Status**: Active, Booked, Confirmed, Completed, Cancelled, Expired
- **Payment Status**: Paid, Unpaid, Expired

---

### **4️⃣ REVIEWS Table**

```
Column Name       │ Data Type      │ Constraint      │ Purpose
─────────────────┼────────────────┼─────────────────┼──────────────────────
id                │ INT            │ PK, AUTO_INC    │ Review unique ID
booking_id        │ INT            │ FK, UNIQUE      │ Related booking
user_id           │ INT            │ FK → users.id   │ Who wrote review
vehicle_id        │ INT            │ FK → vehicles.id│ Which vehicle
rating            │ INT            │ CHECK 1-5       │ Star rating
comment           │ TEXT           │ NULLABLE        │ Review text
created_at        │ TIMESTAMP      │ DEFAULT NOW()   │ Review creation
```

**Constraints:**
- UNIQUE on booking_id (one review per booking maximum)
- CHECK rating between 1 and 5

**Relationships:**
- N:1 → USERS (user writes review)
- N:1 → VEHICLES (about vehicle)
- 1:1 ← BOOKINGS (one review per booking)

---

### **5️⃣ CANCELLATION_FEEDBACK Table**

```
Column Name       │ Data Type      │ Constraint      │ Purpose
─────────────────┼────────────────┼─────────────────┼──────────────────────
id                │ INT            │ PK, AUTO_INC    │ Feedback unique ID
booking_id        │ INT            │ FK, UNIQUE      │ Related booking
user_id           │ INT            │ FK → users.id   │ Who cancelled
reason            │ VARCHAR(255)   │ NULLABLE        │ Cancellation reason
additional_comment│ TEXT           │ NULLABLE        │ Extra feedback
created_at        │ TIMESTAMP      │ DEFAULT NOW()   │ Feedback creation
updated_at        │ TIMESTAMP      │ DEFAULT NOW()   │ Last updated
```

**Constraints:**
- UNIQUE on booking_id (one feedback per booking maximum)

**Relationships:**
- N:1 → USERS (user provides feedback)
- 1:1 ← BOOKINGS (feedback for booking)

---

### **6️⃣ ADMINS Table**

```
Column Name       │ Data Type      │ Constraint      │ Purpose
─────────────────┼────────────────┼─────────────────┼──────────────────────
id                │ INT            │ PK, AUTO_INC    │ Admin unique ID
username          │ VARCHAR(100)   │ UNIQUE, NOT NULL│ Admin login
password          │ VARCHAR(255)   │ NOT NULL        │ Hashed password
created_at        │ TIMESTAMP      │ DEFAULT NOW()   │ Admin creation
updated_at        │ TIMESTAMP      │ DEFAULT NOW()   │ Last updated
```

**Relationships:**
- Standalone (no FK relationships)
- Manages: VEHICLES, BOOKINGS, USERS (via application logic)

---

## 🔄 Relationship Details

### **Relationship 1: USERS → BOOKINGS (1:N)**
```
One user can have many bookings
Type: One-to-Many
FK: bookings.user_id → users.id
Action on Delete: CASCADE (delete user → delete all user bookings)
Cardinality: [1] ---< [N]
Example: User #5 has bookings #12, #45, #67
```

### **Relationship 2: VEHICLES → BOOKINGS (1:N)**
```
One vehicle can be booked many times
Type: One-to-Many
FK: bookings.vehicle_id → vehicles.id
Action on Delete: CASCADE (delete vehicle → delete all vehicle bookings)
Cardinality: [1] ---< [N]
Example: Vehicle #3 has bookings #10, #20, #30
```

### **Relationship 3: USERS → REVIEWS (1:N)**
```
One user can write many reviews
Type: One-to-Many
FK: reviews.user_id → users.id
Action on Delete: CASCADE
Cardinality: [1] ---< [N]
Example: User #2 wrote reviews on vehicles #1, #3, #5
```

### **Relationship 4: VEHICLES → REVIEWS (1:N)**
```
One vehicle can have many reviews
Type: One-to-Many
FK: reviews.vehicle_id → vehicles.id
Action on Delete: CASCADE
Cardinality: [1] ---< [N]
Example: Vehicle #1 has 50 reviews from different users
```

### **Relationship 5: BOOKINGS → REVIEWS (1:1)**
```
One booking can have max one review (or none)
Type: One-to-One
FK: reviews.booking_id → bookings.id (UNIQUE)
Action on Delete: CASCADE
Cardinality: [1] ---- [0..1]
Example: Booking #45 has exactly one review OR no review
```

### **Relationship 6: USERS → CANCELLATION_FEEDBACK (1:N)**
```
One user can provide many cancellation feedbacks
Type: One-to-Many
FK: cancellation_feedback.user_id → users.id
Action on Delete: CASCADE
Cardinality: [1] ---< [N]
Example: User #7 cancelled and provided feedback 3 times
```

### **Relationship 7: BOOKINGS → CANCELLATION_FEEDBACK (1:1)**
```
One booking can have max one feedback (or none)
Type: One-to-One
FK: cancellation_feedback.booking_id → bookings.id (UNIQUE)
Action on Delete: CASCADE
Cardinality: [1] ---- [0..1]
Example: Booking #60 has feedback OR no feedback
```

---

## 📊 Data Type Reference

| Data Type | Usage | Range/Size |
|---|---|---|
| **INT** | IDs, counts, prices, capacity | -2,147,483,648 to 2,147,483,647 |
| **VARCHAR(n)** | Names, emails, usernames | Up to n characters |
| **DATE** | Dates without time | 'YYYY-MM-DD' |
| **TIMESTAMP** | Date and time | Full date-time with timezone |
| **TEXT** | Long text, comments | Up to 65,535 characters |
| **DECIMAL(m,n)** | Precise decimals (ratings) | m = total digits, n = decimal places |
| **CHAR(n)** | Fixed-length strings | Exactly n characters |

---

## 🔑 Key Constraints Reference

| Constraint | Symbol | Meaning | Example |
|---|---|---|---|
| **PRIMARY KEY** | 🔑 | Unique identifier for each row | `id INT PK` |
| **FOREIGN KEY** | 🔗 | Reference to another table | `user_id INT FK → users.id` |
| **UNIQUE** | ⚡ | All values must be different | `email VARCHAR UNIQUE` |
| **NOT NULL** | ✓ | Field is required | `name VARCHAR NOT NULL` |
| **CHECK** | ✔️ | Value must meet condition | `status CHECK IN ('active', 'blocked')` |
| **DEFAULT** | 📌 | Default value if not provided | `status DEFAULT 'active'` |
| **AUTO_INCREMENT** | 🔄 | Automatically increment | `id INT AUTO_INCREMENT` |

---

## 🔐 Integrity Constraints

### **Cascade Delete Rules**

```
When USERS record is deleted:
├─ CASCADE → Delete from BOOKINGS (user_id)
│   ├─ CASCADE → Delete from REVIEWS (booking_id)
│   └─ CASCADE → Delete from CANCELLATION_FEEDBACK (booking_id)
├─ CASCADE → Delete from REVIEWS (user_id)
└─ CASCADE → Delete from CANCELLATION_FEEDBACK (user_id)

When VEHICLES record is deleted:
├─ CASCADE → Delete from BOOKINGS (vehicle_id)
│   ├─ CASCADE → Delete from REVIEWS (booking_id)
│   └─ CASCADE → Delete from CANCELLATION_FEEDBACK (booking_id)
└─ CASCADE → Delete from REVIEWS (vehicle_id)

When BOOKINGS record is deleted:
├─ CASCADE → Delete from REVIEWS (booking_id)
└─ CASCADE → Delete from CANCELLATION_FEEDBACK (booking_id)
```

---

## 📈 Index Strategy

### **Indexes on Foreign Keys** (for JOIN performance)
```
BOOKINGS.user_id
BOOKINGS.vehicle_id
REVIEWS.user_id
REVIEWS.vehicle_id
REVIEWS.booking_id
CANCELLATION_FEEDBACK.user_id
CANCELLATION_FEEDBACK.booking_id
```

### **Indexes on Search Fields** (for WHERE clauses)
```
USERS.username
USERS.email
USERS.status
VEHICLES.status
VEHICLES.category
VEHICLES.avg_rating
BOOKINGS.payment_status
BOOKINGS.booking_status
```

### **Indexes on Date Fields** (for sorting)
```
USERS.created_at
VEHICLES.created_at
BOOKINGS.pickup_date
BOOKINGS.return_date
BOOKINGS.created_at
REVIEWS.created_at
CANCELLATION_FEEDBACK.created_at
```

---

## 📊 Denormalization Strategy

### **Denormalized Fields**

```
vehicles.avg_rating
├─ Stores: AVG(reviews.rating) WHERE vehicle_id = X
├─ Updated: When review added/deleted
├─ Reason: Quick vehicle listing display
└─ Query: SELECT avg_rating FROM vehicles (no JOIN needed)

vehicles.total_reviews
├─ Stores: COUNT(*) FROM reviews WHERE vehicle_id = X
├─ Updated: When review added/deleted
├─ Reason: Show review count on listings
└─ Query: SELECT total_reviews FROM vehicles (no JOIN needed)

bookings.vehicle_name
├─ Stores: Snapshot of vehicles.name at booking time
├─ Updated: Never (historical snapshot)
├─ Reason: Keep booking record even if vehicle name changes
└─ Prevents: Update anomalies on historical data
```

---

## 🎯 Schema Design Principles Applied

✅ **Normalization**: 3NF (Third Normal Form)
✅ **Entity Integrity**: Primary keys on all tables
✅ **Referential Integrity**: Foreign keys with CASCADE DELETE
✅ **Domain Integrity**: CHECK constraints on status/rating fields
✅ **User-Defined Integrity**: Unique constraints on username/email
✅ **Denormalization**: For performance on frequently accessed fields
✅ **Indexing**: Strategic indexes on FK, search, and sort fields
✅ **Scalability**: InnoDB engine, proper data types, composite indexes

---

## 📌 Quick Reference

### **Table Count**: 6
### **Total Columns**: ~70
### **Primary Keys**: 6 (one per table)
### **Foreign Keys**: 7 (for relationships)
### **Unique Constraints**: 8
### **Check Constraints**: 5
### **Indexes**: 30+

---

*Database Schema Diagram - Vehicle Booking System*
*Complete visual and textual representation of database structure*
