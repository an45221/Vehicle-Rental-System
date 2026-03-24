<?php
/**
 * Review Helper Functions
 * Handles all review-related operations
 */

/**
 * Get all reviews for a vehicle
 */
function getVehicleReviews($conn, $vehicle_id, $limit = null) {
    $query = "
        SELECT r.*, u.name, u.email
        FROM reviews r
        JOIN users u ON r.user_id = u.id
        WHERE r.vehicle_id = ?
        ORDER BY r.created_at DESC
    ";
    
    if ($limit) {
        $query .= " LIMIT " . intval($limit);
    }
    
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        error_log("Prepare failed in getVehicleReviews: " . $conn->error);
        return false;
    }
    $stmt->bind_param("i", $vehicle_id);
    $stmt->execute();
    return $stmt->get_result();
}

/**
 * Get vehicle rating statistics
 */
function getVehicleRatingStats($conn, $vehicle_id) {
    $query = "
        SELECT 
            COUNT(*) as total_reviews,
            AVG(rating) as avg_rating,
            SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as five_star,
            SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as four_star,
            SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as three_star,
            SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as two_star,
            SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as one_star
        FROM reviews
        WHERE vehicle_id = ?
    ";
    
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        error_log("Prepare failed in getVehicleRatingStats: " . $conn->error);
        return [
            'total_reviews' => 0,
            'avg_rating' => 0,
            'five_star' => 0,
            'four_star' => 0,
            'three_star' => 0,
            'two_star' => 0,
            'one_star' => 0
        ];
    }
    $stmt->bind_param("i", $vehicle_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    return $result;
}

/**
 * Check if user already reviewed this vehicle (for a specific booking)
 */
function hasUserReviewedBooking($conn, $booking_id) {
    $query = "SELECT id FROM reviews WHERE booking_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $booking_id);
    $stmt->execute();
    return $stmt->get_result()->num_rows > 0;
}

/**
 * Submit a review
 */
function submitReview($conn, $booking_id, $user_id, $vehicle_id, $rating, $comment = '') {
    // Check if review already exists for this booking
    if (hasUserReviewedBooking($conn, $booking_id)) {
        return ['success' => false, 'message' => 'You have already reviewed this booking.'];
    }
    
    // Validate rating
    $rating = intval($rating);
    if ($rating < 1 || $rating > 5) {
        return ['success' => false, 'message' => 'Invalid rating. Must be between 1 and 5.'];
    }
    
    $comment = trim($comment);
    
    try {
        $query = "
            INSERT INTO reviews (booking_id, user_id, vehicle_id, rating, comment)
            VALUES (?, ?, ?, ?, ?)
        ";
        
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            return ['success' => false, 'message' => 'Prepare failed: ' . $conn->error];
        }
        $stmt->bind_param("iiiis", $booking_id, $user_id, $vehicle_id, $rating, $comment);
        
        if ($stmt->execute()) {
            // Update vehicle's average rating
            updateVehicleRating($conn, $vehicle_id);
            return ['success' => true, 'message' => 'Review submitted successfully!'];
        } else {
            return ['success' => false, 'message' => 'Error submitting review: ' . $conn->error];
        }
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
}

/**
 * Update vehicle's average rating
 */
function updateVehicleRating($conn, $vehicle_id) {
    $query = "
        UPDATE vehicles
        SET avg_rating = (SELECT AVG(rating) FROM reviews WHERE vehicle_id = ?),
            total_reviews = (SELECT COUNT(*) FROM reviews WHERE vehicle_id = ?)
        WHERE id = ?
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iii", $vehicle_id, $vehicle_id, $vehicle_id);
    return $stmt->execute();
}

/**
 * Get user's review for a specific booking
 */
function getUserBookingReview($conn, $booking_id) {
    $query = "SELECT * FROM reviews WHERE booking_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $booking_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

/**
 * Generate star rating HTML
 */
function generateStarRating($rating) {
    $rating = floatval($rating);
    $html = '<div class="star-rating">';
    
    for ($i = 1; $i <= 5; $i++) {
        if ($i <= floor($rating)) {
            $html .= '<span class="star filled">★</span>';
        } elseif ($i - 1 < $rating && $i > $rating) {
            $html .= '<span class="star half">★</span>';
        } else {
            $html .= '<span class="star">☆</span>';
        }
    }
    
    $html .= '<span class="rating-value"> (' . round($rating, 1) . '/5)</span></div>';
    return $html;
}

?>
