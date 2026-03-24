<?php
/**
 * Submit Review
 * Handles review submission from user
 */

session_cache_limiter('private_no_expire');
session_start();
require 'config.php';
require 'review_helper.php';

// 🔐 USER AUTH
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_review') {
    $user_id = $_SESSION['user_id'];
    $booking_id = intval($_POST['booking_id'] ?? 0);
    $vehicle_id = intval($_POST['vehicle_id'] ?? 0);
    $rating = intval($_POST['rating'] ?? 0);
    $comment = trim($_POST['comment'] ?? '');

    if ($booking_id === 0 || $vehicle_id === 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid booking or vehicle ID']);
        exit;
    }

    // Verify booking belongs to user
    $stmt = $conn->prepare("SELECT id FROM bookings WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $booking_id, $user_id);
    $stmt->execute();
    if ($stmt->get_result()->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid booking']);
        exit;
    }

    // Submit review
    $result = submitReview($conn, $booking_id, $user_id, $vehicle_id, $rating, $comment);
    header('Content-Type: application/json');
    echo json_encode($result);
    exit;
}

// Handle regular form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $booking_id = intval($_POST['booking_id'] ?? 0);
    $vehicle_id = intval($_POST['vehicle_id'] ?? 0);
    $rating = intval($_POST['rating'] ?? 0);
    $comment = trim($_POST['comment'] ?? '');

    if ($booking_id === 0 || $vehicle_id === 0) {
        die("Invalid request");
    }

    // Verify booking belongs to user
    $stmt = $conn->prepare("SELECT id FROM bookings WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $booking_id, $user_id);
    $stmt->execute();
    if ($stmt->get_result()->num_rows === 0) {
        die("Invalid booking");
    }

    // Submit review
    $result = submitReview($conn, $booking_id, $user_id, $vehicle_id, $rating, $comment);
    
    if ($result['success']) {
        header("Location: mybooking.php?status=review_submitted");
    } else {
        header("Location: mybooking.php?status=review_error&message=" . urlencode($result['message']));
    }
    exit;
}

// GET request - show review form for a booking
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['booking_id'])) {
    $user_id = $_SESSION['user_id'];
    $booking_id = intval($_GET['booking_id']);

    // Get booking details
    $stmt = $conn->prepare("
        SELECT b.*, v.vehicle_name, v.id as vehicle_id
        FROM bookings b
        JOIN vehicles v ON b.vehicle_id = v.id
        WHERE b.id = ? AND b.user_id = ?
    ");
    $stmt->bind_param("ii", $booking_id, $user_id);
    $stmt->execute();
    $booking = $stmt->get_result()->fetch_assoc();

    if (!$booking) {
        die("Booking not found");
    }

    // Check if already reviewed
    $has_review = hasUserReviewedBooking($conn, $booking_id);
    if ($has_review) {
        $review = getUserBookingReview($conn, $booking_id);
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $has_review ? 'Your Review' : 'Write a Review' ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 600px;
            margin: 50px auto;
            background: white;
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        }

        h2 {
            color: #333;
            margin-bottom: 10px;
            font-size: 24px;
        }

        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }

        .booking-info {
            background: #f5f5f5;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 25px;
        }

        .booking-info p {
            margin: 5px 0;
            color: #555;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
            font-size: 14px;
        }

        .rating-input {
            display: flex;
            gap: 10px;
            font-size: 30px;
        }

        .star-btn {
            background: none;
            border: none;
            cursor: pointer;
            color: #ddd;
            transition: all 0.2s ease;
            padding: 0;
            font-size: 32px;
        }

        .star-btn:hover,
        .star-btn.active {
            color: #ffc107;
            transform: scale(1.2);
        }

        textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-family: inherit;
            font-size: 14px;
            resize: vertical;
            min-height: 120px;
            transition: border-color 0.3s ease;
        }

        textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .form-buttons {
            display: flex;
            gap: 10px;
            margin-top: 25px;
        }

        .btn {
            flex: 1;
            padding: 12px;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 15px;
        }

        .btn-submit {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }

        .btn-cancel {
            background: #f0f0f0;
            color: #333;
        }

        .btn-cancel:hover {
            background: #e0e0e0;
        }

        .char-count {
            text-align: right;
            font-size: 12px;
            color: #999;
            margin-top: 5px;
        }

        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
        }

        .info-text {
            font-size: 13px;
            color: #666;
            margin-top: 8px;
            line-height: 1.5;
        }

        @media (max-width: 600px) {
            .container {
                padding: 20px;
                margin: 20px auto;
            }

            h2 {
                font-size: 20px;
            }

            .star-btn {
                font-size: 26px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if ($has_review): ?>
            <h2>⭐ Your Review</h2>
            <p class="subtitle">You've already reviewed this booking</p>

            <div class="booking-info">
                <p><strong>Vehicle:</strong> <?= htmlspecialchars($booking['vehicle_name']) ?></p>
                <p><strong>Your Rating:</strong> 
                    <?php for ($i = 1; $i <= 5; $i++) {
                        echo $i <= $review['rating'] ? '<span style="color: #ffc107;">★</span>' : '<span style="color: #ddd;">☆</span>';
                    } ?>
                </p>
                <?php if ($review['comment']): ?>
                    <p><strong>Your Comment:</strong><br><?= htmlspecialchars($review['comment']) ?></p>
                <?php endif; ?>
                <p><small style="color: #999;">Reviewed on <?= date('M d, Y', strtotime($review['created_at'])) ?></small></p>
            </div>

            <div class="form-buttons">
                <a href="mybooking.php" class="btn btn-cancel">Back to Bookings</a>
            </div>
        <?php else: ?>
            <h2>⭐ Write a Review</h2>
            <p class="subtitle">Share your experience with this vehicle</p>

            <div class="booking-info">
                <p><strong>Vehicle:</strong> <?= htmlspecialchars($booking['vehicle_name']) ?></p>
                <p><strong>Booking Date:</strong> <?= date('M d, Y', strtotime($booking['pickup_date'])) ?></p>
            </div>

            <form id="reviewForm" method="POST">
                <input type="hidden" name="booking_id" value="<?= $booking_id ?>">
                <input type="hidden" name="vehicle_id" value="<?= $booking['vehicle_id'] ?>">

                <div class="form-group">
                    <label>How would you rate this vehicle? *</label>
                    <div class="rating-input" id="ratingInput">
                        <button type="button" class="star-btn" data-rating="1" title="1 star">★</button>
                        <button type="button" class="star-btn" data-rating="2" title="2 stars">★</button>
                        <button type="button" class="star-btn" data-rating="3" title="3 stars">★</button>
                        <button type="button" class="star-btn" data-rating="4" title="4 stars">★</button>
                        <button type="button" class="star-btn" data-rating="5" title="5 stars">★</button>
                    </div>
                    <input type="hidden" id="rating" name="rating" value="0">
                    <div class="info-text">Click a star to rate</div>
                </div>

                <div class="form-group">
                    <label for="comment">Your Experience (Optional)</label>
                    <textarea id="comment" name="comment" placeholder="Share your experience with this vehicle. What did you like or dislike?"></textarea>
                    <div class="char-count"><span id="charCount">0</span>/500</div>
                </div>

                <div class="form-buttons">
                    <button type="submit" class="btn btn-submit">Submit Review</button>
                    <a href="mybooking.php" class="btn btn-cancel">Cancel</a>
                </div>
            </form>
        <?php endif; ?>
    </div>

    <script>
        if (document.getElementById('reviewForm')) {
            const stars = document.querySelectorAll('.star-btn');
            const ratingInput = document.getElementById('rating');
            const commentInput = document.getElementById('comment');
            const charCount = document.getElementById('charCount');

            stars.forEach(star => {
                star.addEventListener('click', (e) => {
                    e.preventDefault();
                    const rating = star.dataset.rating;
                    ratingInput.value = rating;
                    
                    stars.forEach(s => {
                        if (s.dataset.rating <= rating) {
                            s.classList.add('active');
                        } else {
                            s.classList.remove('active');
                        }
                    });
                });
            });

            commentInput.addEventListener('input', () => {
                charCount.textContent = commentInput.value.length;
                if (commentInput.value.length > 500) {
                    commentInput.value = commentInput.value.substring(0, 500);
                    charCount.textContent = '500';
                }
            });

            document.getElementById('reviewForm').addEventListener('submit', (e) => {
                e.preventDefault();
                
                if (ratingInput.value == 0) {
                    alert('Please select a rating');
                    return;
                }

                const formData = new FormData(this);
                formData.append('action', 'submit_review');

                fetch('submit_review.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        window.location.href = 'mybooking.php?status=review_submitted';
                    } else {
                        alert(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error submitting review');
                });
            });
        }
    </script>
</body>
</html>
