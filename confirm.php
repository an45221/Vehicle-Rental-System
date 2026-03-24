<?php
session_cache_limiter('private_no_expire');
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$vehicle_id   = (int) $_POST['vehicle_id'];
$vehicle_name = $_POST['vehicle_name'];
$price        = (int) $_POST['price'];
$pickup       = $_POST['pickup'];
$drop         = $_POST['drop_location'];
$pickup_date  = $_POST['pickup_date'];
$return_date  = $_POST['return_date'];

/* 🔒 Check vehicle availability */
$check = $conn->prepare("SELECT status FROM vehicles WHERE id = ?");
if (!$check) die("Prepare failed: " . $conn->error);

$check->bind_param("i", $vehicle_id);
$check->execute();
$vehicle = $check->get_result()->fetch_assoc();

if (!$vehicle || $vehicle['status'] !== 'available') {
    die("Vehicle already booked.");
}

/* 🔁 TRANSACTION */
$conn->begin_transaction();

try {

    $stmt = $conn->prepare("
        INSERT INTO bookings
        (user_id, vehicle_id, vehicle_name, pickup, drop_location, pickup_date, return_date, price, payment_status, booking_status, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Unpaid', 'Active', NOW())
    ");

    if (!$stmt) {
        throw new Exception($conn->error);
    }

    $stmt->bind_param(
        "iisssssi",
        $user_id,
        $vehicle_id,
        $vehicle_name,
        $pickup,
        $drop,
        $pickup_date,
        $return_date,
        $price
    );

    $stmt->execute();

    $update = $conn->prepare("UPDATE vehicles SET status='unavailable' WHERE id=?");
    if (!$update) throw new Exception($conn->error);

    $update->bind_param("i", $vehicle_id);
    $update->execute();

    $conn->commit();

    header("Location: payment.php?booking_id=" . $stmt->insert_id);
    exit;

} catch (Exception $e) {
    $conn->rollback();
    die("Booking failed: " . $e->getMessage());
}
