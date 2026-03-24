# Vehicle Review System - Implementation Guide

## Overview
A comprehensive review and feedback system has been added to your vehicle booking application. This includes:
- Vehicle review display on search/booking pages
- User review submission after completed/cancelled bookings
- Cancellation feedback collection
- Rating statistics and distribution

## Setup Instructions

### 1. **Database Setup**
Run the database setup file to create required tables:

```
Visit: http://localhost/Project_Work_Kathford_College/database_setup.php
```

This will create:
- `reviews` table - stores vehicle reviews
- `cancellation_feedback` table - stores why users cancelled bookings
- Add columns to `vehicles` table for `avg_rating` and `total_reviews`

### 2. **File Structure**

#### New Files Created:
- **`database_setup.php`** - Database table creation script
- **`review_helper.php`** - Helper functions for review operations
- **`submit_review.php`** - Review submission form and display
- **`cancel_feedback.php`** - Cancellation feedback form
- **`save_cancellation_feedback.php`** - Save cancellation feedback to database

#### Modified Files:
- **`vehicle_profile.php`** - Added review display section with ratings
- **`mybooking.php`** - Added review buttons for completed/cancelled bookings
- **`cancel_booking.php`** - Redirects to feedback form after cancellation

## Features

### 1. **Review Display on Vehicle Profile**
When users view a vehicle after search, they can see:
- Average rating (star display)
- Total number of reviews
- Rating distribution bar chart (1-5 stars)
- List of recent reviews with comments
- Reviewer names and dates

**Files involved:**
- `vehicle_profile.php` - displays reviews
- `review_helper.php` - retrieves review data

**Location:** Bottom of vehicle details page after "Note" section

### 2. **Write/View Reviews**
After a booking is completed (status: "Completed") or cancelled (status: "Cancelled") and paid, users can:
- Write a new review with 1-5 star rating
- Add optional comments (up to 500 characters)
- View their existing reviews

**Files involved:**
- `submit_review.php` - Review form
- `review_helper.php` - Submit and retrieve reviews

**Access:**
- From "My Bookings" page → Click "⭐ WRITE REVIEW" button
- Completed/Paid bookings only

### 3. **Cancellation Feedback Collection**
When users cancel a booking, they're shown a feedback form asking:
- Why they cancelled (multiple choice options)
- Additional comments (optional)

**Files involved:**
- `cancel_feedback.php` - Feedback form
- `save_cancellation_feedback.php` - Save feedback data

**Workflow:**
1. User clicks "CANCEL" on active booking
2. User confirms cancellation
3. Feedback form appears
4. User can skip or submit feedback
5. Redirect to My Bookings

## Database Schema

### Reviews Table
```sql
CREATE TABLE reviews (
    id INT PRIMARY KEY AUTO_INCREMENT,
    booking_id INT NOT NULL (UNIQUE),
    user_id INT NOT NULL,
    vehicle_id INT NOT NULL,
    rating INT (1-5),
    comment TEXT,
    created_at TIMESTAMP,
    FOREIGN KEY references to bookings, users, vehicles
)
```

### Cancellation Feedback Table
```sql
CREATE TABLE cancellation_feedback (
    id INT PRIMARY KEY AUTO_INCREMENT,
    booking_id INT NOT NULL (UNIQUE),
    user_id INT NOT NULL,
    reason VARCHAR(255),
    additional_comment TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY references to bookings, users
)
```

### Vehicles Table (Modified)
```
Added columns:
- avg_rating DECIMAL(3,2) - Average rating for the vehicle
- total_reviews INT - Total number of reviews
```

## API/Helper Functions

### In `review_helper.php`:

1. **getVehicleReviews($conn, $vehicle_id, $limit = null)**
   - Returns all reviews for a vehicle

2. **getVehicleRatingStats($conn, $vehicle_id)**
   - Returns rating statistics (count, average, distribution)

3. **hasUserReviewedBooking($conn, $booking_id)**
   - Checks if user has already reviewed this booking

4. **submitReview($conn, $booking_id, $user_id, $vehicle_id, $rating, $comment)**
   - Submits a new review
   - Returns success/error message

5. **updateVehicleRating($conn, $vehicle_id)**
   - Updates vehicle's average rating

6. **getUserBookingReview($conn, $booking_id)**
   - Gets user's review for a specific booking

7. **generateStarRating($rating)**
   - Generates HTML star display

## User Flows

### Flow 1: View Reviews While Searching
```
Search for vehicle → Vehicle Profile → See Reviews Section
```

### Flow 2: Leave Review After Completed Booking
```
My Bookings → Completed Booking → Click "⭐ WRITE REVIEW" 
→ Fill Form → Submit → Success Message
```

### Flow 3: Leave Review After Cancelled Booking
```
My Bookings → Cancelled Booking → Click "⭐ WRITE REVIEW"
→ Fill Form → Submit → Success Message
```

### Flow 4: Provide Cancellation Feedback
```
My Bookings → Cancel Booking → Confirm → Feedback Form
→ Select Reason → Add Comments (Optional) → Submit/Skip
→ Redirect to My Bookings
```

## Styling & UI

### Color Scheme:
- Primary: `#ff8c00` (Orange) - Review buttons, review highlights
- Secondary: `#667eea` (Purple) - Feedback form gradient
- Stars: `#ffc107` (Yellow) - Filled stars

### Components:
- Star ratings (visual and interactive)
- Rating distribution bars
- Review cards with user info
- Responsive design (mobile-friendly)
- Smooth transitions and animations

## Testing Checklist

- [ ] Run database_setup.php to create tables
- [ ] Create/complete a booking and submit a review
- [ ] View reviews on vehicle profile page
- [ ] Cancel a booking and provide feedback
- [ ] Check that ratings update correctly
- [ ] Verify UI displays correctly on mobile
- [ ] Test all form validations

## Security Considerations

- All inputs are sanitized with `htmlspecialchars()`
- User authentication required for all review operations
- Reviews tied to user_id and booking_id
- Only users can review their own bookings
- SQL injection prevention with prepared statements

## Admin Panel Recommendations

For future enhancements, consider adding:
- Admin dashboard to view all reviews and feedback
- Report/flag inappropriate reviews
- Respond to reviews as admin
- Export feedback data as CSV
- View feedback analytics

## Troubleshooting

**Issue:** Review button not appearing
- Check booking status is "Completed" or "Cancelled"
- Check payment_status is "Paid"

**Issue:** Reviews not displaying
- Run database_setup.php
- Check vehicle_id is correct
- Verify database connection

**Issue:** Star rating not working
- Check JavaScript is enabled
- Verify submit_review.php is accessible
- Check browser console for errors

## Future Enhancements

1. **Email Notifications** - Notify users to leave reviews
2. **Photo Uploads** - Allow users to attach photos to reviews
3. **Helpful Votes** - Let users vote if review was helpful
4. **Admin Responses** - Allow admins to respond to reviews
5. **Review Moderation** - Approve reviews before publishing
6. **Rating Filters** - Filter vehicles by rating in search
7. **Detailed Analytics** - Review trends and patterns

---

For questions or issues, check the comments in the individual PHP files.
