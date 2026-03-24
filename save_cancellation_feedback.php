<?php
/**
 * Save Cancellation Feedback
 * Stores the feedback data from cancellation process
 */

session_cache_limiter('private_no_expire');
session_start();
require 'config.php';

// 🔐 USER AUTH
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: mybooking.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$booking_id = intval($_POST['booking_id'] ?? 0);
$cancel_feedback = trim($_POST['cancel_feedback'] ?? '');
$additional_feedback = trim($_POST['additional_feedback'] ?? '');

if ($booking_id === 0) {
    header("Location: mybooking.php");
    exit;
}

// Verify booking belongs to user
$stmt = $conn->prepare("SELECT id FROM bookings WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $booking_id, $user_id);
$stmt->execute();
if ($stmt->get_result()->num_rows === 0) {
    header("Location: mybooking.php");
    exit;
}

// Save feedback to a feedbacks table
$query = "
INSERT INTO cancellation_feedback
(booking_id, user_id, reason, additional_comment)
VALUES (?, ?, ?, ?)
";

$stmt = $conn->prepare($query);
$stmt->bind_param(
    "iiss",
    $booking_id,
    $user_id,
    $cancel_feedback,
    $additional_feedback
);

// Update bookings table with the feedback for admin visibility
$full_reason = $cancel_feedback;
if (!empty($additional_feedback)) {
    $full_reason .= " - Additional info: " . $additional_feedback;
}

$update_booking = $conn->prepare("UPDATE bookings SET cancel_reason = ? WHERE id = ?");
$update_booking->bind_param("si", $full_reason, $booking_id);
$update_booking->execute();

if ($stmt->execute()) {
    header("Location: mybooking.php?status=cancelled&feedback=submitted");
} else {
    // Still redirect even if feedback save fails - it's not critical
    header("Location: mybooking.php?status=cancelled");
}
exit;
?>
