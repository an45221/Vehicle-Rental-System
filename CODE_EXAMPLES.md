# Code Examples & Developer Reference

## 🔧 Using Review Helper Functions

### Get Vehicle Reviews
```php
<?php
require 'config.php';
require 'review_helper.php';

$vehicle_id = 1;
$reviews = getVehicleReviews($conn, $vehicle_id, 5); // Get last 5 reviews

while ($review = $reviews->fetch_assoc()) {
    echo "User: " . htmlspecialchars($review['name']);
    echo "Rating: " . $review['rating'] . "/5";
    echo "Comment: " . htmlspecialchars($review['comment']);
    echo "Date: " . date('M d, Y', strtotime($review['created_at']));
}
?>
```

### Get Rating Statistics
```php
<?php
$vehicle_id = 1;
$stats = getVehicleRatingStats($conn, $vehicle_id);

echo "Average Rating: " . round($stats['avg_rating'], 1) . "/5";
echo "Total Reviews: " . $stats['total_reviews'];
echo "5-Star Reviews: " . $stats['five_star'];
echo "4-Star Reviews: " . $stats['four_star'];
// ... etc
?>
```

### Check if User Already Reviewed
```php
<?php
$booking_id = 123;

if (hasUserReviewedBooking($conn, $booking_id)) {
    echo "User has already reviewed this booking";
    $review = getUserBookingReview($conn, $booking_id);
    echo "Their rating was: " . $review['rating'];
} else {
    echo "User can write a review";
}
?>
```

### Submit a Review
```php
<?php
$booking_id = 123;
$user_id = 45;
$vehicle_id = 1;
$rating = 5;
$comment = "Great vehicle!";

$result = submitReview($conn, $booking_id, $user_id, $vehicle_id, $rating, $comment);

if ($result['success']) {
    echo "✅ " . $result['message']; // Review submitted successfully!
} else {
    echo "❌ " . $result['message']; // Error message
}
?>
```

### Generate Star Display
```php
<?php
$rating = 4.5;
echo generateStarRating($rating);
// Output: ★★★★☆ (4.5/5)
?>
```

---

## 📝 HTML/CSS Examples

### Display Reviews Section
```html
<?php
require 'review_helper.php';

$vehicle_id = 1;
$rating_stats = getVehicleRatingStats($conn, $vehicle_id);
$reviews = getVehicleReviews($conn, $vehicle_id, 5);
?>

<div class="reviews-section">
    <h3>📋 Customer Reviews</h3>
    
    <?php if ($rating_stats['total_reviews'] > 0): ?>
        <!-- Rating Summary -->
        <div class="rating-summary">
            <div class="rating-overview">
                <div class="average-rating">
                    <?= round($rating_stats['avg_rating'], 1) ?>
                </div>
                <?= generateStarRating($rating_stats['avg_rating']) ?>
                <div class="total-reviews">
                    Based on <?= $rating_stats['total_reviews'] ?> reviews
                </div>
            </div>

            <!-- Rating Distribution -->
            <div class="rating-distribution">
                <?php
                $total = $rating_stats['total_reviews'];
                for ($i = 5; $i >= 1; $i--) {
                    $count = $rating_stats[($i === 5 ? 'five' : ($i === 4 ? 'four' : ($i === 3 ? 'three' : ($i === 2 ? 'two' : 'one')))) . '_star'];
                    $percentage = ($total > 0) ? (($count / $total) * 100) : 0;
                ?>
                    <div class="rating-row">
                        <div class="rating-label"><?= $i ?> ★</div>
                        <div class="rating-bar">
                            <div class="rating-bar-fill" style="width: <?= $percentage ?>%"></div>
                        </div>
                        <div class="rating-count"><?= $count ?></div>
                    </div>
                <?php } ?>
            </div>
        </div>

        <!-- Reviews List -->
        <div class="reviews-list">
            <?php while ($review = $reviews->fetch_assoc()): ?>
                <div class="review-item">
                    <div class="review-header">
                        <div>
                            <div class="reviewer-name">
                                <?= htmlspecialchars($review['name']) ?>
                            </div>
                            <div class="star-rating">
                                <?= generateStarRating($review['rating']) ?>
                            </div>
                        </div>
                        <div class="review-date">
                            <?= date('M d, Y', strtotime($review['created_at'])) ?>
                        </div>
                    </div>
                    <?php if ($review['comment']): ?>
                        <div class="review-comment">
                            <?= htmlspecialchars($review['comment']) ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="no-reviews">
            <p>⭐ No reviews yet. Be the first to review this vehicle!</p>
        </div>
    <?php endif; ?>
</div>
```

### Review Button in My Bookings
```html
<?php
require 'review_helper.php';

// ... loop through bookings ...
?>

<div class="booking-card">
    <!-- Booking details -->
    <div class="actions">
        <!-- Other buttons -->
        
        <?php 
        if (in_array($row['booking_status'], ['Completed', 'Cancelled']) 
            && $row['payment_status'] === 'Paid') {
            
            $has_review = hasUserReviewedBooking($conn, $row['id']);
        ?>
            <a class="btn btn-review" 
               href="submit_review.php?booking_id=<?= $row['id'] ?>"
               title="<?= $has_review ? 'View your review' : 'Write a review' ?>">
                <?= $has_review ? '⭐ VIEW REVIEW' : '⭐ WRITE REVIEW' ?>
            </a>
        <?php } ?>
    </div>
</div>
```

---

## 🎨 CSS Styling Examples

### Basic Review Card Styling
```css
.review-item {
    background-color: #fafafa;
    padding: 15px;
    border-radius: 8px;
    border-left: 4px solid #ff8c00;
    margin-bottom: 15px;
}

.reviewer-name {
    font-weight: 600;
    color: #333;
    font-size: 14px;
}

.review-comment {
    color: #555;
    font-size: 14px;
    line-height: 1.5;
    margin-top: 8px;
}

.star-rating {
    display: flex;
    align-items: center;
    gap: 2px;
}

.star {
    font-size: 14px;
    color: #ddd;
}

.star.filled {
    color: #ffc107;
}
```

### Rating Distribution Bar
```css
.rating-bar {
    height: 8px;
    background-color: #e0e0e0;
    border-radius: 4px;
    overflow: hidden;
}

.rating-bar-fill {
    height: 100%;
    background: linear-gradient(90deg, #ff8c00, #ffb347);
    border-radius: 4px;
    transition: width 0.3s ease;
}
```

### Interactive Star Rating
```css
.star-btn {
    background: none;
    border: none;
    cursor: pointer;
    color: #ddd;
    font-size: 32px;
    transition: all 0.2s ease;
    padding: 0;
}

.star-btn:hover,
.star-btn.active {
    color: #ffc107;
    transform: scale(1.2);
}
```

---

## 🔍 Database Queries

### Get Average Rating for Vehicle
```sql
SELECT 
    AVG(rating) as avg_rating,
    COUNT(*) as total_reviews
FROM reviews
WHERE vehicle_id = ?;
```

### Get All 5-Star Reviews
```sql
SELECT r.*, u.name
FROM reviews r
JOIN users u ON r.user_id = u.id
WHERE r.vehicle_id = ? AND r.rating = 5
ORDER BY r.created_at DESC;
```

### Count Reviews by Rating
```sql
SELECT 
    rating,
    COUNT(*) as count
FROM reviews
WHERE vehicle_id = ?
GROUP BY rating
ORDER BY rating DESC;
```

### Get User's Reviews
```sql
SELECT r.*, v.vehicle_name
FROM reviews r
JOIN vehicles v ON r.vehicle_id = v.id
WHERE r.user_id = ?
ORDER BY r.created_at DESC;
```

### Get Cancellation Feedback
```sql
SELECT 
    reason,
    COUNT(*) as count
FROM cancellation_feedback
GROUP BY reason
ORDER BY count DESC;
```

### Top Rated Vehicles
```sql
SELECT 
    v.id,
    v.vehicle_name,
    AVG(r.rating) as avg_rating,
    COUNT(r.id) as total_reviews
FROM vehicles v
LEFT JOIN reviews r ON v.id = r.vehicle_id
GROUP BY v.id
HAVING total_reviews > 0
ORDER BY avg_rating DESC
LIMIT 10;
```

---

## 🧪 JavaScript Examples

### Interactive Star Rating
```javascript
const stars = document.querySelectorAll('.star-btn');
const ratingInput = document.getElementById('rating');

stars.forEach(star => {
    star.addEventListener('click', (e) => {
        e.preventDefault();
        const rating = star.dataset.rating;
        ratingInput.value = rating;
        
        // Highlight selected stars
        stars.forEach(s => {
            if (s.dataset.rating <= rating) {
                s.classList.add('active');
            } else {
                s.classList.remove('active');
            }
        });
    });
});
```

### Character Counter
```javascript
const textarea = document.getElementById('comment');
const charCount = document.getElementById('charCount');
const maxChars = 500;

textarea.addEventListener('input', () => {
    let length = textarea.value.length;
    
    if (length > maxChars) {
        textarea.value = textarea.value.substring(0, maxChars);
        length = maxChars;
    }
    
    charCount.textContent = length;
});
```

### Form Validation
```javascript
document.getElementById('reviewForm').addEventListener('submit', (e) => {
    e.preventDefault();
    
    const rating = document.getElementById('rating').value;
    
    if (rating == 0) {
        alert('Please select a rating');
        return;
    }
    
    // Submit form via AJAX
    fetch('submit_review.php', {
        method: 'POST',
        body: new FormData(this)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            window.location.href = 'mybooking.php';
        } else {
            alert(data.message);
        }
    });
});
```

### AJAX Review Submission
```javascript
function submitReview() {
    const booking_id = document.getElementById('booking_id').value;
    const rating = document.getElementById('rating').value;
    const comment = document.getElementById('comment').value;
    
    const formData = new FormData();
    formData.append('action', 'submit_review');
    formData.append('booking_id', booking_id);
    formData.append('rating', rating);
    formData.append('comment', comment);
    
    fetch('submit_review.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showSuccess('Review submitted!');
            setTimeout(() => {
                window.location.href = 'mybooking.php';
            }, 2000);
        } else {
            showError(data.message);
        }
    });
}
```

---

## 📊 Admin Query Examples

### Get Review Statistics
```sql
SELECT 
    v.vehicle_name,
    COUNT(r.id) as total_reviews,
    AVG(r.rating) as avg_rating,
    MIN(r.created_at) as first_review,
    MAX(r.created_at) as last_review
FROM vehicles v
LEFT JOIN reviews r ON v.id = r.vehicle_id
GROUP BY v.id
ORDER BY avg_rating DESC;
```

### Get Cancellation Reasons
```sql
SELECT 
    reason,
    COUNT(*) as count,
    ROUND(COUNT(*) * 100.0 / 
        (SELECT COUNT(*) FROM cancellation_feedback), 2) as percentage
FROM cancellation_feedback
GROUP BY reason
ORDER BY count DESC;
```

### Get Recent Feedback
```sql
SELECT 
    cf.id,
    cf.booking_id,
    u.name as user_name,
    v.vehicle_name,
    cf.reason,
    cf.additional_comment,
    cf.created_at
FROM cancellation_feedback cf
JOIN users u ON cf.user_id = u.id
JOIN bookings b ON cf.booking_id = b.id
JOIN vehicles v ON b.vehicle_id = v.id
ORDER BY cf.created_at DESC
LIMIT 20;
```

---

## 🚀 Complete Implementation Example

```php
<?php
// Complete review system implementation example
session_start();
require 'config.php';
require 'review_helper.php';

// Only logged-in users
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$vehicle_id = intval($_GET['vehicle_id'] ?? 0);
if ($vehicle_id === 0) die("Invalid vehicle");

// Get vehicle details
$vehicle = $conn->query("SELECT * FROM vehicles WHERE id = $vehicle_id")->fetch_assoc();

// Get reviews and stats
$stats = getVehicleRatingStats($conn, $vehicle_id);
$reviews = getVehicleReviews($conn, $vehicle_id, 10);

// Check if current user can review any booking for this vehicle
$can_review = false;
$review_booking_id = null;

$user_id = $_SESSION['user_id'];
$booking_stmt = $conn->prepare("
    SELECT id FROM bookings 
    WHERE user_id = ? AND vehicle_id = ? 
    AND booking_status IN ('Completed', 'Cancelled')
    AND payment_status = 'Paid'
    AND id NOT IN (SELECT booking_id FROM reviews)
    LIMIT 1
");
$booking_stmt->bind_param("ii", $user_id, $vehicle_id);
$booking_stmt->execute();
$booking_result = $booking_stmt->get_result();

if ($booking_result->num_rows > 0) {
    $booking = $booking_result->fetch_assoc();
    $can_review = true;
    $review_booking_id = $booking['id'];
}
?>

<!DOCTYPE html>
<html>
<head>
    <title><?= htmlspecialchars($vehicle['vehicle_name']) ?> - Reviews</title>
</head>
<body>
    <h1><?= htmlspecialchars($vehicle['vehicle_name']) ?></h1>
    
    <?php if ($stats['total_reviews'] > 0): ?>
        <div class="rating-summary">
            <h3>Average Rating: <?= round($stats['avg_rating'], 1) ?>/5</h3>
            <p>Based on <?= $stats['total_reviews'] ?> reviews</p>
        </div>
        
        <div class="reviews">
            <?php while ($r = $reviews->fetch_assoc()): ?>
                <div class="review">
                    <h4><?= htmlspecialchars($r['name']) ?></h4>
                    <p><?= generateStarRating($r['rating']) ?></p>
                    <p><?= htmlspecialchars($r['comment']) ?></p>
                    <small><?= $r['created_at'] ?></small>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <p>No reviews yet</p>
    <?php endif; ?>
    
    <?php if ($can_review): ?>
        <a href="submit_review.php?booking_id=<?= $review_booking_id ?>">
            Write a Review
        </a>
    <?php endif; ?>
</body>
</html>
```

---

## 📚 API Reference Summary

| Function | Purpose | Returns |
|----------|---------|---------|
| `getVehicleReviews()` | Get reviews for vehicle | MySQLi Result |
| `getVehicleRatingStats()` | Get rating stats | Array with counts |
| `hasUserReviewedBooking()` | Check if reviewed | Boolean |
| `submitReview()` | Add review | Array with success status |
| `updateVehicleRating()` | Recalculate rating | Boolean |
| `getUserBookingReview()` | Get specific review | Array or null |
| `generateStarRating()` | HTML star display | HTML string |

---

That covers most common use cases! Refer back to these examples when implementing new features. 🎉
