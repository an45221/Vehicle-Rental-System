<?php
session_cache_limiter('private_no_expire');
session_start();
require '../config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}

$booking_id = (int) $_GET['id'];
$action     = $_GET['action'];

$conn->begin_transaction();

try {

    // 🔎 Get booking info
    $stmt = $conn->prepare("
        SELECT vehicle_id, payment_status 
        FROM bookings 
        WHERE id = ?
    ");
    $stmt->bind_param("i", $booking_id);
    $stmt->execute();
    $booking = $stmt->get_result()->fetch_assoc();

    if (!$booking) {
        throw new Exception("Booking not found");
    }

    if ($action === 'approve') {

        $updateBooking = $conn->prepare("
            UPDATE bookings 
            SET booking_status = 'approved'
            WHERE id = ?
        ");
        $updateBooking->bind_param("i", $booking_id);
        $updateBooking->execute();

    } elseif ($action === 'cancel') {

        // 1️⃣ Cancel booking
        $cancelBooking = $conn->prepare("
            UPDATE bookings 
            SET booking_status = 'cancelled'
            WHERE id = ?
        ");
        $cancelBooking->bind_param("i", $booking_id);
        $cancelBooking->execute();

        // 2️⃣ RELEASE VEHICLE ONLY IF UNPAID
        if ($booking['payment_status'] === 'Unpaid' && $booking['vehicle_id']) {

            $releaseVehicle = $conn->prepare("
                UPDATE vehicles 
                SET status = 'available'
                WHERE id = ?
            ");
            $releaseVehicle->bind_param("i", $booking['vehicle_id']);
            $releaseVehicle->execute();
        }
    }

    $conn->commit();
    header("Location: manage_bookings.php");
    exit();

} catch (Exception $e) {
    $conn->rollback();
    die("Action failed: " . $e->getMessage());
}
