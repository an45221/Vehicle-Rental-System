<?php
session_cache_limiter('private_no_expire');
session_start();
require '../config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: manage_vehicles.php");
    exit();
}

$id = (int)$_GET['id'];

/* 1️⃣ CHECK IF VEHICLE HAS BOOKINGS */
$stmt = $conn->prepare("SELECT COUNT(*) FROM bookings WHERE vehicle_id = ?");
if (!$stmt) {
    die("SQL Error: " . $conn->error);
}

$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->bind_result($total);
$stmt->fetch();
$stmt->close();

if ($total > 0) {
    header("Location: manage_vehicles.php?error=booked");
    exit();
}

/* 2️⃣ GET VEHICLE IMAGE */
$stmt = $conn->prepare("SELECT image FROM vehicles WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: manage_vehicles.php");
    exit();
}

$vehicle = $result->fetch_assoc();
$imagePath = "../" . $vehicle['image'];
$stmt->close();

/* 3️⃣ DELETE VEHICLE */
$stmt = $conn->prepare("DELETE FROM vehicles WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    if (file_exists($imagePath)) {
        unlink($imagePath);
    }
    header("Location: manage_vehicles.php?success=deleted");
} else {
    die("Delete failed");
}
