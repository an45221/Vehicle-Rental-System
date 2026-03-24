<?php
session_cache_limiter('private_no_expire');
session_start();
require 'config.php';
require 'booking_helper.php';
require 'review_helper.php';

// 🔐 USER AUTH
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$booking_id = $_GET['booking_id'] ?? $_POST['booking_id'] ?? null;
$cancel_reason = $_POST['cancel_reason'] ?? 'User requested cancellation';

if (!$booking_id) {
    die("Booking ID not provided");
}

$booking_id = (int) $booking_id;

$conn->begin_transaction();

try {

    // Get booking info
    $stmt = $conn->prepare("
        SELECT vehicle_id, payment_status 
        FROM bookings 
        WHERE id = ? AND user_id = ?
    ");
    $stmt->bind_param("ii", $booking_id, $user_id);
    $stmt->execute();
    $booking = $stmt->get_result()->fetch_assoc();

    if (!$booking) {
        throw new Exception("Booking not found");
    }

    // Cancel booking
    $cancel = $conn->prepare("
        UPDATE bookings
        SET booking_status = 'cancelled',
            cancel_reason = ?,
            cancelled_at = NOW()
        WHERE id = ?
    ");
    $cancel->bind_param("si", $cancel_reason, $booking_id);
    $cancel->execute();

    // ✅ RELEASE VEHICLE (THIS WAS MISSING)
    if ($booking['payment_status'] === 'Unpaid' && $booking['vehicle_id']) {
        $release = $conn->prepare("
            UPDATE vehicles 
            SET status = 'available'
            WHERE id = ?
        ");
        $release->bind_param("i", $booking['vehicle_id']);
        $release->execute();
    }

    $conn->commit();
    header("Location: cancel_feedback.php?booking_id=" . $booking_id);
    exit;

} catch (Exception $e) {
    $conn->rollback();
    die("Cancellation failed: " . $e->getMessage());
}
?>