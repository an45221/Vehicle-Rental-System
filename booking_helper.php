<?php
/**
 * Booking Helper Functions
 * Handles booking expiration and cleanup
 */

/**
 * Check and cleanup expired unpaid bookings
 * @param mysqli $conn Database connection
 * @param int $minutes Time limit in minutes (default: 30)
 */
function cleanupExpiredBookings($conn, $minutes = 30) {
    // Get expired bookings
    $query = "
        SELECT id, vehicle_id FROM bookings 
        WHERE payment_status = 'Unpaid' 
        AND booking_status = 'Active' 
        AND created_at IS NOT NULL
        AND TIMESTAMPDIFF(MINUTE, created_at, NOW()) > ?
    ";
    
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        return false;
    }
    
    $stmt->bind_param("i", $minutes);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $expired_bookings = [];
    while ($row = $result->fetch_assoc()) {
        $expired_bookings[] = $row;
    }
    
    // Cancel expired bookings and release vehicles
    foreach ($expired_bookings as $booking) {
        // Update booking status to Expired
        $update_booking = $conn->prepare("
            UPDATE bookings 
            SET booking_status = 'Expired', payment_status = 'Expired'
            WHERE id = ?
        ");
        
        if ($update_booking) {
            $update_booking->bind_param("i", $booking['id']);
            $update_booking->execute();
        }
        
        // Release vehicle back to available
        $release_vehicle = $conn->prepare("
            UPDATE vehicles 
            SET status = 'available'
            WHERE id = ?
        ");
        
        if ($release_vehicle) {
            $release_vehicle->bind_param("i", $booking['vehicle_id']);
            $release_vehicle->execute();
        }
    }
    
    return count($expired_bookings);
}

/**
 * Check if a specific booking is expired
 * @param mysqli $conn Database connection
 * @param int $booking_id Booking ID
 * @param int $minutes Time limit in minutes (default: 30)
 * @return bool
 */
function isBookingExpired($conn, $booking_id, $minutes = 30) {
    $query = "
        SELECT id FROM bookings 
        WHERE id = ? 
        AND payment_status = 'Unpaid' 
        AND booking_status = 'Active' 
        AND created_at IS NOT NULL
        AND TIMESTAMPDIFF(MINUTE, created_at, NOW()) > ?
    ";
    
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        return false;
    }
    
    $stmt->bind_param("ii", $booking_id, $minutes);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->num_rows > 0;
}

/**
 * Get remaining time for an unpaid booking in minutes
 * @param mysqli $conn Database connection
 * @param int $booking_id Booking ID
 * @param int $minutes Time limit in minutes (default: 30)
 * @return int|null Remaining minutes or null if booking is paid/expired
 */
function getRemainingTime($conn, $booking_id, $minutes = 30) {
    $query = "
        SELECT TIMESTAMPDIFF(MINUTE, created_at, DATE_ADD(created_at, INTERVAL ? MINUTE)) AS remaining
        FROM bookings 
        WHERE id = ? 
        AND payment_status = 'Unpaid' 
        AND booking_status = 'Active' 
        AND created_at IS NOT NULL
    ";
    
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        return null;
    }
    
    $stmt->bind_param("ii", $minutes, $booking_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $remaining = $row['remaining'];
        return $remaining > 0 ? $remaining : 0;
    }
    
    return null;
}

/**
 * Cancel an expired booking and release vehicle
 * @param mysqli $conn Database connection
 * @param int $booking_id Booking ID
 * @return bool
 */
function cancelExpiredBooking($conn, $booking_id) {
    // Get booking details
    $get_booking = $conn->prepare("SELECT vehicle_id FROM bookings WHERE id = ?");
    if (!$get_booking) {
        return false;
    }
    
    $get_booking->bind_param("i", $booking_id);
    $get_booking->execute();
    $booking = $get_booking->get_result()->fetch_assoc();
    
    if (!$booking) {
        return false;
    }
    
    $vehicle_id = $booking['vehicle_id'];
    
    // Update booking
    $update_booking = $conn->prepare("
        UPDATE bookings 
        SET booking_status = 'Expired', payment_status = 'Expired'
        WHERE id = ?
    ");
    
    if (!$update_booking) {
        return false;
    }
    
    $update_booking->bind_param("i", $booking_id);
    $update_booking->execute();
    
    // Release vehicle
    $release = $conn->prepare("UPDATE vehicles SET status = 'available' WHERE id = ?");
    if (!$release) {
        return false;
    }
    
    $release->bind_param("i", $vehicle_id);
    $release->execute();
    
    return true;
}
?>
