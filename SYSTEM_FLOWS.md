# Vehicle Review System - User Flows & Diagrams

## 🎯 User Journey - Scenario 1: Viewing Reviews

```
User Home
    ↓
Search for Vehicle
(Specify pickup, dropoff, dates)
    ↓
View Search Results
(List of available vehicles)
    ↓
Click on Vehicle Details
    ↓
[VEHICLE PROFILE PAGE]
├─ Vehicle Details (Seats, Fuel, Transmission)
├─ Price Calculation
├─ "Confirm Book & Pay" Button
└─ ⭐ REVIEWS SECTION (NEW!)
   ├─ Average Rating (e.g., 4.5/5)
   ├─ Star Distribution
   │  ├─ 5 stars: 8 reviews
   │  ├─ 4 stars: 3 reviews
   │  ├─ 3 stars: 1 review
   │  └─ Total: 12 reviews
   └─ Recent Reviews
      ├─ "Rajeev Kumar - ⭐⭐⭐⭐⭐"
      │  "Great vehicle, very clean and comfortable!"
      │  (Mar 15, 2026)
      ├─ "Priya Singh - ⭐⭐⭐⭐"
      │  "Good experience, driver was helpful"
      │  (Mar 10, 2026)
      └─ ... (more reviews)
    ↓
Decision: Book or Continue Shopping
```

---

## 🎯 User Journey - Scenario 2: Writing a Review After Completed Booking

```
User Home
    ↓
Click "MY BOOKINGS"
    ↓
[MY BOOKINGS PAGE]
Shows list of all bookings
    ↓
Find Completed Booking
(Status: "Completed", Payment: "Paid")
    ↓
Click "⭐ WRITE REVIEW" Button (NEW!)
    ↓
[REVIEW SUBMISSION PAGE]
├─ Vehicle: Toyota Fortuner
├─ Booking Date: Mar 01, 2026
└─ Review Form:
   ├─ ⭐ Rating: Click to select 1-5 stars
   ├─ Comment: (Optional text - max 500 chars)
   │  "Great car! Very comfortable for long trips"
   └─ Buttons:
      ├─ [Submit Review] ← Click to submit
      └─ [Cancel] ← Go back
    ↓
Review Successfully Submitted!
    ↓
Redirect to My Bookings
    ↓
Users can now see their review on vehicle profile
when other users search and view details
```

---

## 🎯 User Journey - Scenario 3: Cancelling Booking & Providing Feedback

```
User Home
    ↓
Click "MY BOOKINGS"
    ↓
[MY BOOKINGS PAGE]
Shows all bookings
    ↓
Find Active Booking
(Status: "Active", Payment: "Unpaid/Paid")
    ↓
Click "CANCEL" Button
    ↓
Confirmation Modal:
"Are you sure you want to cancel this booking?"
    ↓
User Confirms
    ↓
Booking Status Changes to "Cancelled"
    ↓
[CANCELLATION FEEDBACK PAGE] (NEW!)
├─ Success Badge: "✅ Booking Cancelled"
├─ Message: "We'd Like Your Feedback"
├─ Vehicle: Toyota Fortuner
├─ Booking ID: SCPL-1234
│
└─ Feedback Form:
   ├─ Why did you cancel?
   │  └─ Select ONE reason (radio buttons):
   │     ├─ ○ Found a better price elsewhere
   │     ├─ ○ Inconvenient pickup/drop-off
   │     ├─ ○ Required vehicle not available
   │     ├─ ○ Poor customer service
   │     └─ ○ Other reason
   │
   ├─ Additional Comments (Optional)
   │  └─ "Price was too high compared to others"
   │     (Up to 500 characters)
   │
   └─ Buttons:
      ├─ [Submit Feedback] ← Save feedback
      └─ [Skip for Now] ← Skip feedback
    ↓
Feedback Saved to Database (if submitted)
or skipped
    ↓
Redirect to My Bookings
↓
View shows booking as "Cancelled"
```

---

## 📊 Database Architecture

```
┌─────────────────────────────────────────────────┐
│            VEHICLES TABLE                        │
├─────────────────────────────────────────────────┤
│ id          PRIMARY KEY                          │
│ vehicle_name                                    │
│ price       (price per day)                     │
│ seats, fuel_type, transmission                  │
│ image       (vehicle photo)                     │
│ status      ('available', 'booked', etc)        │
│ [NEW] avg_rating    DECIMAL(3,2) ← avg of all │
│ [NEW] total_reviews INT         ← count of all │
└─────────────────────────────────────────────────┘
            ↑              ↑
            │              │
            │ (1:N)        │ (1:N)
            │              │
    ┌───────────────┐      ┌──────────────────────┐
    │ REVIEWS       │      │ BOOKINGS             │
    │ TABLE [NEW]   │      │ TABLE (existing)     │
    ├───────────────┤      ├──────────────────────┤
    │ id            │      │ id                   │
    │ booking_id ───┼──→ booking_id              │
    │ user_id ──────┼──→ (link to users)         │
    │ vehicle_id ───┼──────┴→ vehicle_id         │
    │ rating        │        user_id             │
    │ comment       │        pickup_date         │
    │ created_at    │        return_date         │
    │ UNIQUE:       │        price, status       │
    │  (booking_id) │        payment_status      │
    └───────────────┘      └──────────────────────┘

    ┌────────────────────────┐
    │ CANCELLATION_FEEDBACK  │
    │ TABLE [NEW]            │
    ├────────────────────────┤
    │ id                     │
    │ booking_id ────→ (FK)  │
    │ user_id ────────→ (FK) │
    │ reason                 │
    │ additional_comment     │
    │ created_at             │
    │ updated_at             │
    │ UNIQUE:                │
    │  (booking_id)          │
    └────────────────────────┘

    ┌────────────┐
    │ USERS      │
    │ TABLE      │
    ├────────────┤
    │ id         │
    │ name       │
    │ email      │
    └────────────┘
```

---

## 🔄 Data Flow - Submitting a Review

```
USER SUBMITS REVIEW
        ↓
[submit_review.php - Form Processing]
├─ Validate:
│  ├─ User is logged in
│  ├─ Booking exists & belongs to user
│  ├─ Rating is 1-5
│  └─ Not already reviewed (UNIQUE constraint)
│
├─ INSERT into reviews table:
│  ├─ booking_id
│  ├─ user_id
│  ├─ vehicle_id
│  ├─ rating
│  └─ comment
│
├─ UPDATE vehicles table:
│  ├─ Recalculate avg_rating
│  └─ Update total_reviews count
│
└─ Return success message
        ↓
REVIEW NOW VISIBLE
        ↓
[Vehicle Profile Page]
├─ When OTHER users view vehicle:
│  └─ See new review in reviews section
│     with updated rating stats
│
└─ When SAME user views:
   └─ Can see their own review
      with "VIEW REVIEW" button
```

---

## 🔄 Data Flow - Cancelling & Feedback

```
USER CANCELS BOOKING
        ↓
[mybooking.php - Click CANCEL]
        ↓
[Modal Confirmation]
User confirms cancellation reason
        ↓
[cancel_booking.php]
├─ Verify booking belongs to user
├─ UPDATE bookings:
│  └─ Set status = 'cancelled'
├─ Release vehicle (if unpaid)
└─ REDIRECT to feedback form
        ↓
[cancel_feedback.php]
Show feedback form with options:
├─ Better price found
├─ Inconvenient location
├─ Vehicle not available
├─ Poor service
└─ Other
        ↓
User fills feedback (optional)
        ↓
User clicks "Submit" or "Skip"
        ↓
IF SUBMIT:
[save_cancellation_feedback.php]
├─ INSERT into cancellation_feedback:
│  ├─ booking_id
│  ├─ user_id
│  ├─ reason
│  ├─ additional_comment
│  └─ created_at
│
└─ REDIRECT to mybooking.php
        ↓
IF SKIP:
REDIRECT to mybooking.php directly
        ↓
Booking shows as "Cancelled"
Data available for admin analysis
```

---

## 🎨 Page Components - Vehicle Profile

```
┌─────────────────────────────────────────────────┐
│  VEHICLE PROFILE PAGE - vehicle_profile.php     │
├─────────────────────────────────────────────────┤
│                                                  │
│  ╔═══════════════════════════════════════════╗  │
│  ║ Vehicle Details                           ║  │
│  ║ ═════════════════════════════════════════║  │
│  ║ [Image] │ Seats, Fuel, Transmission      ║  │
│  ║         │ Price per Day, Total Days      ║  │
│  ║         │ [Confirm Book & Pay] Button    ║  │
│  ╚═══════════════════════════════════════════╝  │
│                                                  │
│  ┌─────────────────────────────────────────┐   │
│  │ Note about fuel charges                 │   │
│  └─────────────────────────────────────────┘   │
│                                                  │
│  ╔═══════════════════════════════════════════╗  │
│  ║ 📋 CUSTOMER REVIEWS (NEW!)                ║  │
│  ║ ═════════════════════════════════════════║  │
│  ║                                           ║  │
│  ║ ┌─── RATING SUMMARY ───────────────────┐ ║  │
│  ║ │ 4.5           │ 5 ★: 8               │ ║  │
│  ║ │ ⭐⭐⭐⭐☆   │ 4 ★: 3   ┌──────────┐│ ║  │
│  ║ │ Based on 12   │ 3 ★: 1   │████████ ││ ║  │
│  ║ │ reviews       │ 2 ★: 0   │████     ││ ║  │
│  ║ │               │ 1 ★: 0   │█        ││ ║  │
│  ║ │               └──────────┘          │ ║  │
│  ║ └─────────────────────────────────────┘ ║  │
│  ║                                           ║  │
│  ║ ┌─── RECENT REVIEWS ────────────────────┐ ║  │
│  ║ │ ┌───────────────────────────────────┐│ ║  │
│  ║ │ │ Rajeev Kumar         Mar 15, 2026││ ║  │
│  ║ │ │ ⭐⭐⭐⭐⭐             ││ ║  │
│  ║ │ │ "Great vehicle, very clean and   ││ ║  │
│  ║ │ │  comfortable for highway travel" ││ ║  │
│  ║ │ └───────────────────────────────────┘│ ║  │
│  ║ │                                        │ ║  │
│  ║ │ ┌───────────────────────────────────┐│ ║  │
│  ║ │ │ Priya Singh          Mar 10, 2026││ ║  │
│  ║ │ │ ⭐⭐⭐⭐☆             ││ ║  │
│  ║ │ │ "Good experience, driver was     ││ ║  │
│  ║ │ │  helpful and courteous"          ││ ║  │
│  ║ │ └───────────────────────────────────┘│ ║  │
│  ║ │ ... more reviews ...                  │ ║  │
│  ║ └─────────────────────────────────────┘ ║  │
│  ║                                           ║  │
│  ╚═══════════════════════════════════════════╝  │
│                                                  │
└─────────────────────────────────────────────────┘
```

---

## 🎨 Page Components - My Bookings

```
┌────────────────────────────────────────────────┐
│ MY BOOKINGS PAGE - mybooking.php               │
├────────────────────────────────────────────────┤
│                                                 │
│ ╔══════════════════════════════════════════╗   │
│ ║ Vehicle: Toyota Fortuner                ║   │
│ ║                                          ║   │
│ ║ Booking ID: SCPL-1234                   ║   │
│ ║ Start Date: 2026-03-01                  ║   │
│ ║ End Date: 2026-03-05                    ║   │
│ ║ Total Amount: NPR 15,000                ║   │
│ ║                                          ║   │
│ ║ From: Kathmandu │ To: Pokhara           ║   │
│ ║ Payment: Paid   │ Status: Completed      ║   │
│ ║                                          ║   │
│ ║ Actions:                                 ║   │
│ ║ [CANCEL] [PAY] [⭐ WRITE REVIEW] (NEW!) ║   │
│ ║                 └─ Shows only for:       ║   │
│ ║                    Completed/Cancelled   ║   │
│ ║                    AND Paid bookings     ║   │
│ ║                                          ║   │
│ ║ OR [⭐ VIEW REVIEW] (if already reviewed)║   │
│ ╚══════════════════════════════════════════╝   │
│                                                 │
│ [Similar card for next booking...]             │
│                                                 │
└────────────────────────────────────────────────┘
```

---

## 📱 Mobile Responsive Design

```
Desktop (720px+):
┌─────────────────────────────────┐
│ Rating: 4.5        │ Distribution │
│ Stars              │ Bars (5 ★)   │
│                    │ Bars (4 ★)   │
│                    │ Bars (3 ★)   │
└─────────────────────────────────┘

Mobile (<720px):
┌───────────────────┐
│ Rating: 4.5       │
│ Stars             │
│ Distribution:     │
│ 5★: 8             │
│ 4★: 3             │
│ 3★: 1             │
└───────────────────┘
```

---

## 🔐 Security Layers

```
REVIEW SUBMISSION:
    ↓
[Authentication Check]
Is user logged in? 
    ↓ NO → Redirect to login
    ↓ YES ↓
[Ownership Verification]
Does booking belong to this user?
    ↓ NO → Error message
    ↓ YES ↓
[Duplicate Prevention]
Has user already reviewed this booking?
    ↓ YES → Show existing review
    ↓ NO ↓
[Data Validation]
Is rating 1-5? Is comment ≤500 chars?
    ↓ INVALID → Show error
    ↓ VALID ↓
[Sanitization]
Escape special characters
    ↓
[SQL Injection Prevention]
Use prepared statements
    ↓
[Insert to Database]
Review saved successfully!
```

---

## 📈 Business Analytics Potential

```
REVIEW DATA:
├─ Which vehicles get highest ratings?
├─ Common complaints in feedback?
├─ Which cancellation reasons most common?
├─ Seasonal trends in reviews?
└─ Customer satisfaction by vehicle type?

CANCELLATION FEEDBACK DATA:
├─ Price competitiveness analysis
├─ Location convenience issues
├─ Vehicle availability problems
├─ Service quality feedback
└─ Identify improvement areas
```

This is a complete, production-ready review system! 🎉
